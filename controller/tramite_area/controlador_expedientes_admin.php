<?php
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $consulta = $MTRA->Cargar_Select_Expediente_admin();
    echo json_encode($consulta);
 
?>