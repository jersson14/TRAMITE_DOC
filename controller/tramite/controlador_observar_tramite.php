<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/Plazos.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';
    require '../../model/model_observacion.php';
    require '../../utilitario/class_notificacion.php';

    header('Content-Type: application/json; charset=utf-8');

    /*
     * El área observa un trámite para que el ciudadano lo subsane (migración 025).
     *
     * En vez de rechazar algo incompleto, se le indica al ciudadano qué falta y se
     * le da un plazo en días hábiles. El trámite queda OBSERVADO; cuando el
     * ciudadano sube lo que falta desde el portal, vuelve a su estado anterior y
     * el área lo retoma donde lo dejó.
     *
     * Solo se observan trámites EXTERNOS: la subsanación la hace el ciudadano
     * desde el portal público, con su N° de expediente y DNI.
     */

    Seguridad::exigirPermiso('observar');

    $MTR = new Modelo_Tramite();
    $MFI = new Modelo_Firma();
    $MOB = new Modelo_Observacion();

    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    $motivo = trim((string) ($_POST['motivo'] ?? ''));
    $plazo = (int) ($_POST['plazo'] ?? 0);

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if (mb_strlen($motivo) < 10) {
        Seguridad::responderError(422, 'Explique al ciudadano qué debe corregir o completar (mínimo 10 caracteres).');
    }
    if (mb_strlen($motivo) > 2000) {
        Seguridad::responderError(422, 'La observación no debe superar los 2000 caracteres.');
    }
    if ($plazo < 1 || $plazo > 30) {
        Seguridad::responderError(422, 'El plazo para subsanar debe estar entre 1 y 30 días hábiles.');
    }
    if (!Seguridad::esAdmin() && !$MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'Ese trámite no pasó por su área.');
    }
    if ($MFI->Procedencia($id) !== 'EXTERNO') {
        Seguridad::responderError(422, 'Solo se observan trámites externos: la subsanación la hace el ciudadano desde el portal. '
            . 'Un documento interno se corrige derivándolo o respondiendo.');
    }

    // El plazo corre en días hábiles, descontando fines de semana y feriados
    $limite = Plazos::sumarHabiles(new DateTimeImmutable('today'), $plazo);

    try {
        $observacionId = $MOB->Observar($id, $motivo, $plazo, $limite->format('Y-m-d'),
                                        Seguridad::usuarioId() ?: null, Seguridad::areaId() ?: null);
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }

    $limiteTexto = $limite->format('d/m/Y');
    Bitacora::registrar(Bitacora::OBSERVACION, 'documento', $id,
        'plazo ' . $plazo . ' día(s) hábil(es), hasta el ' . $limiteTexto . ' · ' . mb_substr($motivo, 0, 120));

    // Aviso al ciudadano, si dejó correo. Que falle el correo no deshace la observación.
    $ciudadano = $MTR->Traer_Datos_Ciudadano($id);
    $avisado = false;
    if ($ciudadano && !empty($ciudadano['email'])) {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $raizWeb = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)), '/');
        $urlBase = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $raizWeb;
        $avisado = (new Notificacion())->notificarObservacion(
            $ciudadano['email'], $ciudadano['nombre'], $ciudadano['expediente'], $motivo, $limiteTexto, $urlBase
        );
    }

    echo json_encode([
        'status'         => 'ok',
        'observacion_id' => $observacionId,
        'limite'         => $limiteTexto,
        'plazo'          => $plazo,
        // Para que la pantalla diga si hay que avisarle al ciudadano por otro medio
        'avisado'        => (bool) $avisado,
        'tiene_correo'   => (bool) ($ciudadano && !empty($ciudadano['email'])),
    ]);
