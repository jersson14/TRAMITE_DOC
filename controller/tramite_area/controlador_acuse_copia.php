<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();

    header('Content-Type: application/json; charset=utf-8');

    // Un área que recibió el trámite en copia confirma que lo recibió.
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }

    $resultado = $MTR->Registrar_Acuse_Copia($id, Seguridad::areaId(), Seguridad::usuarioId());
    if ($resultado === -1) {
        Seguridad::responderError(403, 'Su área no recibió copia ni pedido de atención de este trámite.');
    }

    if ($resultado > 0) {
        Bitacora::registrar(Bitacora::RECEPCION, 'documento', $id, 'acuse del área que recibió copia o pedido de atención');
    }

    // 0 = ya tenía acuse: no es un error, simplemente no cambia nada.
    echo json_encode(['status' => 'ok', 'registrados' => $resultado]);
