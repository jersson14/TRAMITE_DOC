<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_dashboard.php';
    header('Content-Type: application/json; charset=utf-8');

    $MD = new Modelo_Dashboard();
    $respuesta = ['area' => $MD->Resumen_Area(Seguridad::areaId())];
    if (Seguridad::esAdmin()) {
        $respuesta['institucion'] = $MD->Resumen_Global();
    }
    echo json_encode($respuesta);
?>
