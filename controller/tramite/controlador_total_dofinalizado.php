<?php
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    $consulta = $MTR->listar_total_docfinalizado();
    echo json_encode($consulta);

?>