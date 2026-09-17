<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    // El usuario sale de la sesión: antes se aceptaba cualquier id enviado por el navegador
    $id = Seguridad::usuarioId();
    $consulta = $MTRA->Cargar_Select_Expediente($id);
    echo json_encode($consulta);
 
?>