<?php
    require '../../model/model_tramite_area.php';

    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $id = htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8');
    $consulta = $MTRA->TraerDatosExpediente($id);
    echo json_encode($consulta);
   
?>