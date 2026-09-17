<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_feriado.php';

    header('Content-Type: application/json; charset=utf-8');

    $MF = new Modelo_Feriado();
    $anio = (int) ($_POST['anio'] ?? date('Y'));
    if ($anio < 2000 || $anio > 2100) {
        $anio = (int) date('Y');
    }

    $respuesta = $MF->Listar($anio);
    $respuesta['anio'] = $anio;
    $respuesta['anios'] = array_map('intval', $MF->Anios());
    echo json_encode($respuesta);
