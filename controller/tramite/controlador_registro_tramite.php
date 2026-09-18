<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/Correlativo.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require '../../utilitario/class_notificacion.php';
    Seguridad::exigirPermiso('registrar');

    $MTR = new Modelo_Tramite();
    $NTF = new Notificacion();

    //DATOS DE REMITENTE//
    $dni = strtoupper(htmlspecialchars($_POST['dni'],ENT_QUOTES,'UTF-8'));
    $nom = strtoupper(htmlspecialchars($_POST['nom'],ENT_QUOTES,'UTF-8'));
    $apt = strtoupper(htmlspecialchars($_POST['apt'],ENT_QUOTES,'UTF-8'));
    $apm = strtoupper(htmlspecialchars($_POST['apm'],ENT_QUOTES,'UTF-8'));
    $cel = strtoupper(htmlspecialchars($_POST['cel'],ENT_QUOTES,'UTF-8'));
    $ema = htmlspecialchars($_POST['ema'],ENT_QUOTES,'UTF-8');
    $dir = strtoupper(htmlspecialchars($_POST['dir'],ENT_QUOTES,'UTF-8'));
    $vpresentacion = strtoupper(htmlspecialchars($_POST['vpresentacion'],ENT_QUOTES,'UTF-8'));
    $ruc = strtoupper(htmlspecialchars($_POST['ruc'],ENT_QUOTES,'UTF-8'));
    $raz = strtoupper(htmlspecialchars($_POST['raz'],ENT_QUOTES,'UTF-8'));

    //DATOS DEL DOCUMENTO //
    $arp = strtoupper(htmlspecialchars($_POST['arp'],ENT_QUOTES,'UTF-8'));
    $ard = strtoupper(htmlspecialchars($_POST['ard'],ENT_QUOTES,'UTF-8'));
    $tip = strtoupper(htmlspecialchars($_POST['tip'],ENT_QUOTES,'UTF-8'));
    $ndo = strtoupper(htmlspecialchars($_POST['ndo'],ENT_QUOTES,'UTF-8'));
    $asu = strtoupper(htmlspecialchars($_POST['asu'],ENT_QUOTES,'UTF-8'));
    $fol = strtoupper(htmlspecialchars($_POST['fol'],ENT_QUOTES,'UTF-8'));
    $idusu = Seguridad::usuarioId();
    $acc = strtoupper(htmlspecialchars($_POST['acc'],ENT_QUOTES,'UTF-8'));
    $obs = strtoupper(htmlspecialchars($_POST['obs'],ENT_QUOTES,'UTF-8'));
    $tre = strtoupper(htmlspecialchars($_POST['tre'],ENT_QUOTES,'UTF-8'));

    // Recibir y decodificar las copias
    $copias = array();
    if(isset($_POST['copias']) && !empty($_POST['copias'])){
        $copias = json_decode($_POST['copias'], true);
        if(!is_array($copias)){
            $copias = array();
        }
    }

    // El nombre y el tipo del archivo los determina el servidor, no el navegador
    try {
        $nombrearchivo = Seguridad::guardarArchivo('achivoobj', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ARCH');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }

    // Los anexos se validan y guardan antes de registrar: si uno no sirve, se
    // avisa sin haber creado el trámite a medias.
    try {
        $anexos = Seguridad::guardarArchivos('anexos', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ANX');
    } catch (RuntimeException $e) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
        Seguridad::responderError(422, $e->getMessage());
    }

    $ruta='controller/tramite/documentos/'.$nombrearchivo;
    $consulta = $MTR->Registrar_Tramite($dni,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$arp,
    $ard,$tip,$ndo,$asu,$ruta,$fol,$idusu,$acc,$obs,$tre,$copias);
    if ($consulta) {
        // Trámite nuevo: todos sus movimientos (principal y copias) son de este envío.
        $idsAnexos = $MTR->Registrar_Anexos($consulta, $anexos, 'controller/tramite/documentos', $idusu);
        $MTR->Vincular_Anexos($consulta, $idsAnexos, 0);
        // ✉️ NOTIFICACIÓN: registra quién envió, de qué área viene ($arp), y a qué área llegó ($ard)
        $remitente_nombre = trim("$nom $apt $apm");
        $NTF->notificarRegistro($ard, $arp, $consulta, $tip, $asu, $remitente_nombre);
        // Procedencia según el remitente: con cuenta de usuario es personal de la
        // entidad (INTERNO, se firma aquí); sin cuenta es de fuera (EXTERNO, solo se
        // verifica). Esta pantalla no tiene casilla de trámite externo. Migración 021.
        $MFI = new Modelo_Firma();
        $procedencia = $MFI->Procedencia_De_Registro(
            $dni, false, $MFI->Remitente_Es_Juridica($ruc, $raz, $vpresentacion)
        );
        $MFI->Marcar_Procedencia($consulta, $procedencia);

        /*
         * Número del documento (migración 026). Solo para documentos internos: uno
         * externo ya trae el número que le puso quien lo envía.
         *
         * Si el usuario dejó el número automático, se GASTA aquí de forma atómica y
         * se usa ese, aunque en pantalla hubiera visto otro (si alguien registró a la
         * vez, a cada uno le toca uno distinto). Si lo cambió a mano, se respeta y no
         * se gasta ningún número de la secuencia.
         *
         * Siempre se reescribe desde aquí porque el procedimiento de registro recibe
         * el número en 15 caracteres y un número completo no cabe.
         */
        $numeroFinal = $ndo;
        if ($procedencia === 'INTERNO' && ($_POST['correlativo_auto'] ?? '0') === '1') {
            $pdoNum = (new conexionBD())->conexionPDO();
            $areaNum = Correlativo::datosArea($pdoNum, (int) $arp);
            if ($areaNum) {
                $anioNum = (int) date('Y');
                $numeroFinal = Correlativo::formatear(
                    Correlativo::consumir($pdoNum, (int) $arp, (int) $tip, $anioNum), $anioNum, $areaNum['sigla']
                );
            }
        }
        $MTR->Actualizar_Nro_Documento($consulta, mb_substr($numeroFinal, 0, 80));


        // Queda constancia de con qué firma llegó el documento
        $detalleFirma = '';
        $completa = __DIR__ . '/documentos/' . $nombrearchivo;
        if (is_file($completa)) {
            try {
                $detalleFirma = ' · ' . FirmaDigital::resumenTexto(
                    FirmaDigital::resumenFirmas((string) file_get_contents($completa)));
            } catch (Throwable $e) {
                error_log('[FIRMA] resumen al registrar: ' . $e->getMessage());
            }
        }

        Bitacora::registrar(Bitacora::REGISTRO_TRAMITE, 'documento', $consulta,
            'asunto: ' . $asu . ' · ' . strtolower($procedencia) . $detalleFirma);
        echo $consulta;
    }
?>