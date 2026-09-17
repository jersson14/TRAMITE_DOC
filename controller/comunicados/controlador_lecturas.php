<?php
/** Quiénes confirmaron haber leído un comunicado (para el administrador). */
require_once __DIR__ . '/../_guard_admin.php';
require '../../model/model_comunicados.php';

header('Content-Type: application/json; charset=utf-8');

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    Seguridad::responderError(422, 'Comunicado no válido.');
}

echo json_encode(['data' => (new Modelo_Comunicados())->Lecturas($id)]);
