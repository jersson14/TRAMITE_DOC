<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Correlativo.php';
    require_once __DIR__ . '/../../model/model_conexion.php';

    header('Content-Type: application/json; charset=utf-8');

    /*
     * Próximo número de documento de un área (migración 026).
     *
     * Solo MIRA el siguiente número, no lo gasta: el formulario lo muestra mientras
     * el usuario llena el resto. El número se gasta recién al guardar, y solo si el
     * usuario lo dejó tal cual (ver Correlativo::consumir en el registro).
     */
    Seguridad::exigirPermiso('registrar');

    $area = (int) ($_POST['area'] ?? 0);
    $tipo = (int) ($_POST['tipo'] ?? 0);
    if ($area < 1 || $tipo < 1) {
        Seguridad::responderError(422, 'Indique el área y el tipo de documento.');
    }

    $pdo = (new conexionBD())->conexionPDO();
    $datos = Correlativo::datosArea($pdo, $area);
    if (!$datos) {
        Seguridad::responderError(422, 'El área no existe.');
    }

    $anio = (int) date('Y');
    $numero = Correlativo::siguiente($pdo, $area, $tipo, $anio);

    echo json_encode([
        'status'  => 'ok',
        'numero'  => Correlativo::formatear($numero, $anio, $datos['sigla']),
        'sigla'   => $datos['sigla'],
        'area'    => $datos['nombre'],
    ], JSON_UNESCAPED_UNICODE);
