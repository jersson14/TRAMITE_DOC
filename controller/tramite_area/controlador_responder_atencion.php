<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';
    $MTR = new Modelo_Tramite();
    $MFI = new Modelo_Firma();

    header('Content-Type: application/json; charset=utf-8');

    /*
     * Un área a la que se le pidió atención registra su respuesta.
     *
     * El informe que el área adjunta lo produce la entidad, así que debe salir
     * FIRMADO. La firma ocurre aquí, antes de guardar: el PDF que queda en el
     * expediente y que verá la otra área ya lleva el sello y la firma, en vez de
     * enviarse primero y firmarse después desde el panel de archivos.
     *
     * La firma es opcional por decisión de la entidad: si el usuario no adjunta su
     * certificado la respuesta se envía igual, pero el sistema avisa y deja
     * constancia en la bitácora de que el informe salió sin firma.
     *
     * El certificado y su contraseña solo existen en memoria durante la firma.
     */
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    $texto = trim((string) ($_POST['respuesta'] ?? ''));
    $motivo = trim((string) ($_POST['motivo'] ?? ''));
    $clave = (string) ($_POST['clave'] ?? '');
    unset($_POST['clave']);

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if (mb_strlen($texto) < 3) {
        Seguridad::responderError(422, 'Escriba la respuesta de su área.');
    }
    if (mb_strlen($texto) > 4000) {
        Seguridad::responderError(422, 'La respuesta no debe superar los 4000 caracteres.');
    }
    if (mb_strlen($motivo) > 150) {
        Seguridad::responderError(422, 'El motivo no debe superar los 150 caracteres.');
    }

    // Archivo opcional (informe, opinión técnica)
    try {
        $archivo = Seguridad::guardarArchivo('archivo', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'RESP');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }
    $ruta = $archivo ? 'controller/tramite_area/documentos/' . $archivo : null;

    // --- Firma del informe, antes de enviarlo ---
    // Responder no exige rol; firmar sí (migración 024)
    $quiereFirmar = !empty($_FILES['certificado']['name']);
    if ($quiereFirmar) {
        Seguridad::exigirPermiso('firmar');
    }
    $firma = null;
    $codigo = null;
    $pdf = '';

    if ($quiereFirmar && !$archivo) {
        Seguridad::responderError(422, 'Adjunte el informe en PDF que va a firmar, o envíe la respuesta sin firma.');
    }

    if ($quiereFirmar) {
        $completa = __DIR__ . '/documentos/' . $archivo;
        $pdf = (string) file_get_contents($completa);

        $usuario = $MFI->Datos_Usuario(Seguridad::usuarioId());
        $codigo = $MFI->Codigo_Libre();
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $raizWeb = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)), '/');
        $urlValidar = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $raizWeb . '/validar_firma.php?codigo=' . $codigo;

        try {
            $firma = FirmaDigital::firmarConCertificadoSubido(
                $pdf, $_FILES['certificado'] ?? null, $clave,
                $usuario ? (string) $usuario['emple_nrodocumento'] : null,
                $motivo !== '' ? $motivo : 'Informe de atención',
                $codigo, $urlValidar, 'Abancay, Apurímac'
            );
        } catch (RuntimeException $e) {
            // La respuesta no se registra: el usuario corrige y vuelve a enviar
            Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $archivo);
            Seguridad::responderError(422, $e->getMessage());
        } catch (Throwable $e) {
            Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $archivo);
            error_log('[FIRMA] respuesta de atención: ' . $e->getMessage());
            Seguridad::responderError(422, 'No se pudo firmar el informe. Puede estar protegido con contraseña o tener un formato no compatible.');
        }
        $clave = null;

        // Lo que se guarda es ya el PDF firmado: nunca circula una versión sin firma
        if (file_put_contents($completa, $firma['pdf']) === false) {
            Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $archivo);
            Seguridad::responderError(500, 'No se pudo guardar el informe firmado.');
        }
    }

    $resultado = $MTR->Responder_Atencion($id, Seguridad::areaId(), Seguridad::usuarioId(), $texto, $ruta);

    if ($resultado !== 1 && $archivo) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $archivo);
    }
    if ($resultado === -1) {
        Seguridad::responderError(403, 'A su área no se le pidió atención en este trámite.');
    }
    if ($resultado === 0) {
        Seguridad::responderError(422, 'Su área ya respondió esta atención.');
    }

    // La firma se registra recién cuando la respuesta quedó guardada, para no dejar
    // un código de verificación apuntando a una respuesta que no existe.
    if ($firma) {
        try {
            $MFI->Registrar_Firma_Archivo([
                'codigo'           => $codigo,
                'documento_id'     => $id,
                'orden'            => $firma['orden'],
                'archivo_origen'   => $ruta,
                'archivo_firmado'  => $ruta,
                'hash_origen'      => hash('sha256', $pdf),
                'hash_firmado'     => hash('sha256', $firma['pdf']),
                'motivo'           => $motivo !== '' ? $motivo : null,
                'firmante_nombre'  => mb_substr($firma['cert']['nombre'], 0, 200),
                'firmante_dni'     => $firma['cert']['dni'],
                'cert_emisor'      => mb_substr((string) $firma['cert']['emisor'], 0, 255),
                'cert_serie'       => mb_substr((string) $firma['cert']['serie'], 0, 80),
                'cert_desde'       => $firma['cert']['desde'],
                'cert_hasta'       => $firma['cert']['hasta'],
                'cert_huella'      => $firma['cert']['huella'],
                'cert_autofirmado' => $firma['cert']['autofirmado'],
                'usuario_id'       => Seguridad::usuarioId() ?: null,
                'area_id'          => Seguridad::areaId() ?: null,
            ]);
        } catch (Throwable $e) {
            // La respuesta ya está enviada y el PDF firmado: no se deshace nada,
            // pero sin registro el código del sello no se podrá validar en línea.
            error_log('[FIRMA] no se registró la firma de la respuesta: ' . $e->getMessage());
        }
    }

    Bitacora::registrar(Bitacora::ATENCION_RESPONDIDA, 'documento', $id,
        mb_substr($texto, 0, 120) . ($ruta ? ' · con archivo' : '') .
        ($firma ? ' · informe firmado por ' . $firma['cert']['nombre'] . ' · código ' . $codigo
                : ($archivo ? ' · informe SIN firma digital' : '')));

    echo json_encode([
        'status'      => 'ok',
        'firmado'     => (bool) $firma,
        'codigo'      => $codigo,
        'firmante'    => $firma ? $firma['cert']['nombre'] : null,
        'autofirmado' => $firma ? $firma['cert']['autofirmado'] : false,
        // Lo usa la pantalla para avisar: salió un informe propio sin firmar
        'sin_firma'   => (bool) ($archivo && !$firma),
    ]);
