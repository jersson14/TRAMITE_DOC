<?php
    require_once __DIR__ . '/../_guard.php';
   require_once __DIR__ . '/../../lib/Bitacora.php';
   require '../../model/model_tramite.php';
   $MTR = new Modelo_Tramite();//Instaciamos
    $id2 = strtoupper(htmlspecialchars($_POST['id2'],ENT_QUOTES,'UTF-8'));
    $desc2 = htmlspecialchars($_POST['desc2'],ENT_QUOTES,'UTF-8');
    $loc = htmlspecialchars($_POST['loc'],ENT_QUOTES,'UTF-8');

    $consulta = $MTR->Rechazar_Tramite($id2,$desc2,$loc);
    Bitacora::registrar(Bitacora::CAMBIO_ESTADO, 'documento', $id2, 'RECHAZADO: ' . $desc2);
    echo $consulta;



?>