<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_feriado.php';

    header('Content-Type: application/json; charset=utf-8');

    $MF = new Modelo_Feriado();
    $fecha = trim((string) ($_POST['fecha'] ?? ''));
    $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
    $tipo = strtoupper(trim((string) ($_POST['tipo'] ?? 'NACIONAL')));

    $valida = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$valida || $valida->format('Y-m-d') !== $fecha) {
        Seguridad::responderError(422, 'Indique una fecha válida.');
    }
    if ((int) $valida->format('Y') < 2000 || (int) $valida->format('Y') > 2100) {
        Seguridad::responderError(422, 'El año debe estar entre 2000 y 2100.');
    }
    if (mb_strlen($descripcion) < 3) {
        Seguridad::responderError(422, 'Escriba el motivo del feriado (por ejemplo, Fiestas Patrias).');
    }
    if (mb_strlen($descripcion) > 120) {
        Seguridad::responderError(422, 'El motivo no debe superar los 120 caracteres.');
    }
    if (!in_array($tipo, ['NACIONAL', 'REGIONAL', 'NO_LABORABLE'], true)) {
        Seguridad::responderError(422, 'Tipo de feriado no válido.');
    }

    if (!$MF->Guardar($fecha, $descripcion, $tipo)) {
        Seguridad::responderError(500, 'No se pudo guardar el feriado.');
    }

    Bitacora::registrar(Bitacora::REGISTRO, 'feriado', $fecha, $descripcion . ' · ' . $tipo);
    echo json_encode(['status' => 'ok']);
