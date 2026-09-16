<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $estatus = strtoupper(htmlspecialchars($_POST['estatus'],ENT_QUOTES,'UTF-8'));


    $consulta = $MTRA->Modificar_Estatus_Tramite($id,$estatus);
    Bitacora::registrar(Bitacora::CAMBIO_ESTADO, 'documento', $id, 'nuevo estado: ' . $estatus);
    echo $consulta;
?>