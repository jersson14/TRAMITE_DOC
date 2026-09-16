<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_usuario.php';
    $MU = new Modelo_Usuario();// Instanciamos
    $idarea = Seguridad::areaId();
    $consulta = $MU->Listar_notificacion_tramite($idarea);
    echo json_encode($consulta);
 
?>
