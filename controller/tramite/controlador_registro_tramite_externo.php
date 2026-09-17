<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/Institucion.php';
    require_once __DIR__ . '/../../lib/Plazos.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require '../../model/model_tramite.php';
    require '../../utilitario/class_notificacion.php';
    Seguridad::iniciarSesion();

    // Si el envío supera post_max_size, PHP descarta todos los datos: se avisa en vez de pedir "adjunte el documento"
    $limitePost = ini_get('post_max_size');
    $bytesPost = (int) $limitePost * (stripos($limitePost, 'G') !== false ? 1073741824 : (stripos($limitePost, 'M') !== false ? 1048576 : (stripos($limitePost, 'K') !== false ? 1024 : 1)));
    if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $bytesPost) {
        Seguridad::responderError(422, 'Los archivos superan el tamaño permitido. El documento y los anexos no deben pasar de 35 MB en total.');
    }

    // Registro público: máximo 10 trámites por hora desde una misma IP
    $clave = 'registro_externo|' . Seguridad::ipCliente();
    if (Seguridad::esperaIntentos($clave, 10, 3600) > 0) {
        Seguridad::responderError(429, 'Se alcanzó el límite de registros por hora. Intente más tarde.');
    }

    $MTR = new Modelo_Tramite();
    $NTF = new Notificacion();

    //DATOS DE REMITENTE//
    $dni = strtoupper(htmlspecialchars($_POST['dni'],ENT_QUOTES,'UTF-8'));
    $nom = strtoupper(htmlspecialchars($_POST['nom'],ENT_QUOTES,'UTF-8'));
    $apt = strtoupper(htmlspecialchars($_POST['apt'],ENT_QUOTES,'UTF-8'));
    $apm = strtoupper(htmlspecialchars($_POST['apm'],ENT_QUOTES,'UTF-8'));
    $cel = strtoupper(htmlspecialchars($_POST['cel'],ENT_QUOTES,'UTF-8'));
    $ema = htmlspecialchars($_POST['ema'],ENT_QUOTES,'UTF-8');   // Email del ciudadano (sin strtoupper)
    $dir = strtoupper(htmlspecialchars($_POST['dir'],ENT_QUOTES,'UTF-8'));
    $vpresentacion = strtoupper(htmlspecialchars($_POST['vpresentacion'],ENT_QUOTES,'UTF-8'));
    $ruc = strtoupper(htmlspecialchars($_POST['ruc'],ENT_QUOTES,'UTF-8'));
    $raz = strtoupper(htmlspecialchars($_POST['raz'],ENT_QUOTES,'UTF-8'));

    //DATOS DEL DOCUMENTO //
    $tip = strtoupper(htmlspecialchars($_POST['tip'],ENT_QUOTES,'UTF-8'));   // tipodocumento_id
    $ndo = strtoupper(htmlspecialchars($_POST['ndo'],ENT_QUOTES,'UTF-8'));   // nro de documento
    $asu = strtoupper(htmlspecialchars($_POST['asu'],ENT_QUOTES,'UTF-8'));
    $fol = strtoupper(htmlspecialchars($_POST['fol'],ENT_QUOTES,'UTF-8'));

    // El nombre y el tipo del archivo los determina el servidor, no el navegador
    try {
        $nombrearchivo = Seguridad::guardarArchivo('achivoobj', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ARCH');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }
    if (!$nombrearchivo) {
        Seguridad::responderError(422, 'Adjunte el documento en formato PDF.');
    }

    // Anexos del ciudadano (recaudos). Se validan antes de registrar.
    try {
        $anexos = Seguridad::guardarArchivos('anexos', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ANX');
    } catch (RuntimeException $e) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
        Seguridad::responderError(422, $e->getMessage());
    }

    $ruta='controller/tramite/documentos/'.$nombrearchivo;
    $consulta = $MTR->Registrar_Tramite_Externo($dni,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$tip,$ndo,$asu,$ruta,$fol);
    if ($consulta) {
        $idsAnexos = $MTR->Registrar_Anexos($consulta, $anexos, 'controller/tramite/documentos', 0);
        $MTR->Vincular_Anexos($consulta, $idsAnexos, 0);
        Seguridad::registrarIntento($clave, 3600);

        // Fecha de presentación según el horario de atención: fuera de horario, fin de
        // semana o feriado cuenta como presentado el siguiente día hábil (los plazos corren desde ahí)
        $pdo = (new conexionBD())->conexionPDO();
        $datos = $pdo->prepare("SELECT doc_expediente, doc_fecharegistro FROM documento WHERE documento_id = ?");
        $datos->execute([$consulta]);
        $registro = $datos->fetch(PDO::FETCH_ASSOC) ?: ['doc_expediente' => null, 'doc_fecharegistro' => date('Y-m-d H:i:s')];
        $institucion = Institucion::datos();
        $momentoRegistro = new DateTimeImmutable($registro['doc_fecharegistro']);
        $presentado = Plazos::recepcionEfectiva($momentoRegistro, $institucion['hora_inicio'], $institucion['hora_fin']);
        $pdo->prepare("UPDATE documento SET doc_fecharecepcion = ? WHERE documento_id = ?")
            ->execute([$presentado->format('Y-m-d H:i:s'), $consulta]);
        $fueraDeHorario = $presentado->format('Y-m-d H:i') !== $momentoRegistro->format('Y-m-d H:i');

        // URL base del sistema, calculada desde donde está instalado
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $url_base  = $protocolo . '://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)), '/');

        // ✉️ 1. Correo al CIUDADANO: expediente, código y enlace de consulta
        $nombre_ciudadano = trim("$nom $apt $apm");
        $NTF->notificarCiudadano($ema, $nombre_ciudadano, $consulta, $tip, $ndo, $asu, 1, $url_base, [
            'expediente' => $registro['doc_expediente'],
            'recibido'   => $momentoRegistro->format('d/m/Y H:i'),
            'presentado' => $fueraDeHorario ? $presentado->format('d/m/Y H:i') : null,
        ]);

        // ✉️ 2. Correo a MESA DE PARTES: aviso de nuevo documento externo recibido
        // Ajusta $area_mesa_partes_id si el ID de Mesa de Partes es diferente en tu BD
        $area_mesa_partes_id = 1;
        $NTF->notificarRegistro($area_mesa_partes_id, 0, $consulta, $tip, $asu, $nombre_ciudadano);

        // Registro ciudadano: no hay sesión, así que se deja constancia del DNI declarado.
        // Queda constancia de con qué firma llegó el documento del ciudadano. El
        // trámite es EXTERNO (lo fija SP_REGISTRAR_TRAMITE_EXTERNO): no se firma
        // aquí, solo se comprueba lo que trae.
        $detalleFirma = '';
        $completa = __DIR__ . '/documentos/' . $nombrearchivo;
        if (is_file($completa)) {
            try {
                $detalleFirma = ' · ' . FirmaDigital::resumenTexto(
                    FirmaDigital::resumenFirmas((string) file_get_contents($completa)));
            } catch (Throwable $e) {
                error_log('[FIRMA] resumen al registrar (externo): ' . $e->getMessage());
            }
        }

        Bitacora::registrar(Bitacora::REGISTRO_TRAMITE, 'documento', $consulta,
            'mesa de partes virtual · asunto: ' . $asu . $detalleFirma, 'ciudadano DNI ' . $dni);

        // Datos para la pantalla de confirmación y el cargo de recepción
        $consultaCargo = 'codigo=' . rawurlencode($consulta) . '&dni=' . rawurlencode($dni);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'     => 'ok',
            'codigo'     => $consulta,
            'expediente' => $registro['doc_expediente'],
            'fecha'      => $momentoRegistro->format('d/m/Y H:i'),
            'presentado' => $fueraDeHorario ? $presentado->format('d/m/Y H:i') : null,
            'horario'    => $institucion['hora_inicio'] . ' a ' . $institucion['hora_fin'],
            'correo'     => $ema,
            'archivos'   => 1 + count($anexos),
            'cargo'      => 'view/MPDF/REPORTE/cargo_recepcion.php?' . $consultaCargo,
            'ticket'     => 'view/MPDF/REPORTE/ticket_tramite.php?' . $consultaCargo,
            'seguimiento'=> 'seguimiento.php?codigo=' . rawurlencode($registro['doc_expediente'] ?: $consulta),
        ]);
    } else {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
        foreach ($anexos as $anexo) {
            Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $anexo['nombre']);
        }
        Seguridad::responderError(500, 'No se pudo registrar el trámite. Intente nuevamente.');
    }
?>