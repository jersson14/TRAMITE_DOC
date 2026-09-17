<?php
/**
 * Comunicados del tablero: solo los vigentes dirigidos a quien está conectado.
 * Antes la tabla del tablero mostraba todos, incluso los archivados y los que
 * no le correspondían.
 */
require_once __DIR__ . '/../_guard.php';
require '../../model/model_comunicados.php';

header('Content-Type: application/json; charset=utf-8');

$MC = new Modelo_Comunicados();
$comunicados = $MC->Para_Usuario(Seguridad::usuarioId(), Seguridad::areaId(), Seguridad::esAdmin());

$filas = array_map(function ($c) {
    return [
        'titulo'           => $c['titulo'],
        'descripcion'      => $c['descripcion'],
        'fecha_formateada' => $c['fecha'],
        'enlace'           => $c['enlace'],
        // Para el tablero: si la persona ya lo confirmó o sigue pendiente
        'estado'           => ((int) $c['leido'] > 0) ? 'LEIDO' : 'PENDIENTE',
    ];
}, $comunicados);

echo json_encode(['data' => $filas]);
