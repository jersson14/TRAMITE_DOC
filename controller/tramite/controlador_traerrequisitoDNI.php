<?php
    require '../../model/model_tramite.php';

    $MTRA = new Modelo_Tramite();//Instaciamos
    $id = htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8');
    $consulta = $MTRA->TraerRequisitosDNI($id);
    echo json_encode($consulta);
   
?>