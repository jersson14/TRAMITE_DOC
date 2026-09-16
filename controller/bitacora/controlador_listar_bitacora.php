<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_bitacora.php';

    $MB = new Modelo_Bitacora();

    $desde  = trim((string) ($_POST['desde'] ?? ''));
    $hasta  = trim((string) ($_POST['hasta'] ?? ''));
    $accion = strtoupper(trim((string) ($_POST['accion'] ?? '')));

    // Solo se aceptan fechas con formato AAAA-MM-DD y acciones en mayúsculas.
    if ($desde !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) { $desde = ''; }
    if ($hasta !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) { $hasta = ''; }
    if ($accion !== '' && !preg_match('/^[A-Z_]{1,60}$/', $accion)) { $accion = ''; }

    header('Content-Type: application/json; charset=utf-8');
    $resultado = $MB->Listar_Bitacora($desde, $hasta, $accion);
    echo json_encode(empty($resultado) ? ['data' => []] : $resultado);
