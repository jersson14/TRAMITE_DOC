<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_cargo.php';
    $MCA = new Modelo_Cargo();//Instaciamos
    $consulta = $MCA->Cargar_Select_Cargp();
    echo json_encode($consulta);
 
?>
