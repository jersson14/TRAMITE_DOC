<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite.php';
    $MTRA = new Modelo_Tramite();//Instaciamos
    $consulta = $MTRA->Cargar_Select_DNI();
    echo json_encode($consulta);
 
?>
