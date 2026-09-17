<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_feriado.php';

    header('Content-Type: application/json; charset=utf-8');

    $MF = new Modelo_Feriado();
    $fecha = trim((string) ($_POST['fecha'] ?? ''));

    $valida = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$valida || $valida->format('Y-m-d') !== $fecha) {
        Seguridad::responderError(422, 'Indique una fecha válida.');
    }
    if (!$MF->Eliminar($fecha)) {
        Seguridad::responderError(404, 'Ese día no está registrado como feriado.');
    }

    Bitacora::registrar(Bitacora::ELIMINO, 'feriado', $fecha, 'feriado eliminado');
    echo json_encode(['status' => 'ok']);
