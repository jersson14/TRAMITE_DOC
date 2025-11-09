<?php
    require '../../model/model_tramite.php';
    $MTRA = new Modelo_Tramite();//Instaciamos
    $consulta = $MTRA->Cargar_Select_DNI();
    echo json_encode($consulta);
 
?>
