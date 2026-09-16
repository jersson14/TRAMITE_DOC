<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    $consulta = $MTR->listar_total_docpendientes();
    echo json_encode($consulta);

?>