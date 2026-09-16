<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    //DATOS DE REMITENTE//
    $documentoFinal = strtoupper(htmlspecialchars($_POST['documentoFinal'],ENT_QUOTES,'UTF-8'));
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

    // Anexos: se validan antes de registrar para no dejar el trámite a medias.
    try {
        $anexos = Seguridad::guardarArchivos('anexos', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ANX');
    } catch (RuntimeException $e) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
        Seguridad::responderError(422, $e->getMessage());
    }

    $ruta='controller/tramite/documentos/'.$nombrearchivo;
    $consulta = $MTR->Registrar_Tramite_ul($documentoFinal,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$arp,
    $ard,$tip,$ndo,$asu,$ruta,$fol,$idusu,$acc,$obs,$tre,$copias);
    if ($consulta) {
        // Trámite nuevo: todos sus movimientos (principal y copias) son de este envío.
        $idsAnexos = $MTR->Registrar_Anexos($consulta, $anexos, 'controller/tramite/documentos', $idusu);
        $MTR->Vincular_Anexos($consulta, $idsAnexos, 0);
        Bitacora::registrar(Bitacora::REGISTRO_TRAMITE, 'documento', $consulta,
            'asunto: ' . $asu . (count($anexos) ? ' · ' . count($anexos) . ' anexo(s)' : ''));
        echo $consulta;
    }
?>