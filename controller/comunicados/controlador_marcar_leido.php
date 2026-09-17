<?php
/** Registra que la persona leyó el comunicado, para que la alerta no vuelva a salir. */
require_once __DIR__ . '/../_guard.php';
require '../../model/model_comunicados.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    Seguridad::responderError(422, 'Comunicado no válido.');
}

$MC = new Modelo_Comunicados();
// Solo se puede confirmar un comunicado que le corresponde a esa persona
$permitidos = array_column($MC->Para_Usuario(Seguridad::usuarioId(), Seguridad::areaId(), Seguridad::esAdmin()), 'id_comunicado');
if (!in_array($id, array_map('intval', $permitidos), true)) {
    Seguridad::responderError(403, 'Ese comunicado no está dirigido a usted.');
}

$MC->Marcar_Leido($id, Seguridad::usuarioId());
echo json_encode(['status' => 'ok']);
