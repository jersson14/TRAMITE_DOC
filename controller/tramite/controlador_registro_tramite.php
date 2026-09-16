<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    require '../../utilitario/class_notificacion.php';
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

    $ruta='controller/tramite/documentos/'.$nombrearchivo;
    $consulta = $MTR->Registrar_Tramite($dni,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$arp,
    $ard,$tip,$ndo,$asu,$ruta,$fol,$idusu,$acc,$obs,$tre,$copias);
    if ($consulta) {
        // ✉️ NOTIFICACIÓN: registra quién envió, de qué área viene ($arp), y a qué área llegó ($ard)
        $remitente_nombre = trim("$nom $apt $apm");
        $NTF->notificarRegistro($ard, $arp, $consulta, $tip, $asu, $remitente_nombre);
        Bitacora::registrar(Bitacora::REGISTRO_TRAMITE, 'documento', $consulta, 'asunto: ' . $asu);
        echo $consulta;
    }
?>