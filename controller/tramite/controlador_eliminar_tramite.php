<?php
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $consulta = $MTR->Eliminar_Tramite($id);
    echo $consulta;



?>