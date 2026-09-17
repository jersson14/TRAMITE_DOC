<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_comunicados.php';
    require_once __DIR__ . '/_datos_comunicado.php';

    header('Content-Type: application/json; charset=utf-8');

    $MC = new Modelo_Comunicados();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        Seguridad::responderError(422, 'Comunicado no válido.');
    }
    $estado = strtoupper(trim((string) ($_POST['estado'] ?? 'NUEVO')));
    if (!in_array($estado, ['NUEVO', 'PASADO'], true)) {
        Seguridad::responderError(422, 'Estado no válido.');
    }

    $datos = datosComunicado();
    $MC->Modificar_Comunicado($id, $datos['titulo'], $datos['descripcion'], $datos['enlace'],
        $datos['destino'], $datos['desde'], $datos['hasta'], $datos['areas'], $estado);

    Bitacora::registrar(Bitacora::MODIFICO, 'comunicado', (string) $id,
        mb_substr($datos['titulo'], 0, 100) . ' · para ' . $datos['destino'] . ' · ' . $estado);

    echo json_encode(['status' => 'ok']);
