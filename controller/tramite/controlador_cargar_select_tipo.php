<?php
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    $consulta = $MTR->Cargar_Select_Tipo();
    echo json_encode($consulta);
 
?>
