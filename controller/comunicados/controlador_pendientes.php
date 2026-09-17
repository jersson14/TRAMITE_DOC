<?php
/**
 * Comunicados que le toca ver a quien está conectado y que aún no confirmó leer.
 * Los muestra js/console_comunicados_alerta.js como alerta al entrar al sistema.
 */
require_once __DIR__ . '/../_guard.php';
require '../../model/model_comunicados.php';

header('Content-Type: application/json; charset=utf-8');

$MC = new Modelo_Comunicados();
$pendientes = $MC->Para_Usuario(Seguridad::usuarioId(), Seguridad::areaId(), Seguridad::esAdmin(), true);

echo json_encode(['total' => count($pendientes), 'items' => $pendientes]);
