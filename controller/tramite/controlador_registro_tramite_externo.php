<?php
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
    $ema = htmlspecialchars($_POST['ema'],ENT_QUOTES,'UTF-8');   // Email del ciudadano (sin strtoupper)
    $dir = strtoupper(htmlspecialchars($_POST['dir'],ENT_QUOTES,'UTF-8'));
    $vpresentacion = strtoupper(htmlspecialchars($_POST['vpresentacion'],ENT_QUOTES,'UTF-8'));
    $ruc = strtoupper(htmlspecialchars($_POST['ruc'],ENT_QUOTES,'UTF-8'));
    $raz = strtoupper(htmlspecialchars($_POST['raz'],ENT_QUOTES,'UTF-8'));

    //DATOS DEL DOCUMENTO //
    $tip = strtoupper(htmlspecialchars($_POST['tip'],ENT_QUOTES,'UTF-8'));   // tipodocumento_id
    $ndo = strtoupper(htmlspecialchars($_POST['ndo'],ENT_QUOTES,'UTF-8'));   // nro de documento
    $asu = strtoupper(htmlspecialchars($_POST['asu'],ENT_QUOTES,'UTF-8'));
    $nombrearchivo = strtoupper(htmlspecialchars($_POST['nombrearchivo'],ENT_QUOTES,'UTF-8'));
    $fol = strtoupper(htmlspecialchars($_POST['fol'],ENT_QUOTES,'UTF-8'));

    $ruta='controller/tramite/documentos/'.$nombrearchivo;
    $consulta = $MTR->Registrar_Tramite_Externo($dni,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$tip,$ndo,$asu,$ruta,$fol);
    if ($consulta) {
        if($nombrearchivo!=""){
            move_uploaded_file($_FILES['achivoobj']['tmp_name'],"documentos/".$nombrearchivo);
        }

        // URL base del sistema — ajusta si el dominio cambia
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $url_base  = $protocolo . '://' . $_SERVER['HTTP_HOST'] . '/SISTRAMITEDOC';

        // ✉️ 1. Correo al CIUDADANO: código de seguimiento + link de rastreo
        $nombre_ciudadano = trim("$nom $apt $apm");
        $NTF->notificarCiudadano($ema, $nombre_ciudadano, $consulta, $tip, $ndo, $asu, 1, $url_base);

        // ✉️ 2. Correo a MESA DE PARTES: aviso de nuevo documento externo recibido
        // Ajusta $area_mesa_partes_id si el ID de Mesa de Partes es diferente en tu BD
        $area_mesa_partes_id = 1;
        $NTF->notificarRegistro($area_mesa_partes_id, 0, $consulta, $tip, $asu, $nombre_ciudadano);

        echo $consulta;
    }
?>