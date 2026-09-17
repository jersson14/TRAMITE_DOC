<?php
/**
 * Notificaciones de la barra superior (js/console_notificaciones.js).
 *
 * Devuelve dos grupos:
 *  - comunicados: los comunicados vigentes (estado NUEVO), para todos.
 *  - pendientes: lo que le toca atender a quien está conectado. Un área ve sus
 *    envíos por aceptar, las copias que aún no confirmó, las atenciones que
 *    debe responder y sus trámites vencidos. El administrador ve el total de
 *    vencidos de la institución y los trámites sin acuse de recepción.
 *
 * Antes cada notificación se leía por posición del resultado de un SP (data[i][24]),
 * lo que se rompía al agregar columnas; aquí cada dato va con su nombre.
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../model/model_conexion.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = (new conexionBD())->conexionPDO();
$area = Seguridad::areaId();
$esAdmin = Seguridad::esAdmin();
$LIMITE = 6;

// --- Comunicados vigentes dirigidos a esta persona (migración 018) ---
require_once __DIR__ . '/../../model/model_comunicados.php';
$comunicados = array_slice(
    (new Modelo_Comunicados())->Para_Usuario(Seguridad::usuarioId(), $area, $esAdmin),
    0,
    $LIMITE
);
// La insignia cuenta solo los que aún no confirmó leer
$sinLeer = count(array_filter($comunicados, fn($c) => (int) $c['leido'] === 0));

$grupos = [];

if (!$esAdmin) {
    // Envíos que el área todavía no acepta ni rechaza
    $consulta = $pdo->prepare(
        "SELECT d.documento_id, d.doc_expediente, d.doc_asunto,
                DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y %H:%i') AS fecha,
                COALESCE(o.area_nombre, 'MESA DE PARTES VIRTUAL') AS origen
           FROM documento d
           LEFT JOIN area o ON o.area_cod = d.area_origen
          WHERE d.area_destino = ? AND d.doc_estatus = 'PENDIENTE'
          ORDER BY d.doc_fecharegistro DESC"
    );
    $consulta->execute([$area]);
    $porAceptar = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Copias recibidas sin confirmar la recepción
    $consulta = $pdo->prepare(
        "SELECT m.documento_id, d.doc_expediente, d.doc_asunto,
                DATE_FORMAT(m.mov_fecharegistro, '%d/%m/%Y %H:%i') AS fecha,
                COALESCE(o.area_nombre, 'MESA DE PARTES') AS origen
           FROM movimiento m
           INNER JOIN documento d ON d.documento_id = m.documento_id
           LEFT JOIN area o ON o.area_cod = m.area_origen_id
          WHERE m.mov_tipo = 'COPIA' AND m.areadestino_id = ? AND m.mov_recibido_fecha IS NULL
          ORDER BY m.mov_fecharegistro DESC"
    );
    $consulta->execute([$area]);
    $copias = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Atenciones pedidas al área que siguen sin respuesta
    $consulta = $pdo->prepare(
        "SELECT m.documento_id, d.doc_expediente, d.doc_asunto, m.mov_plazo_dias AS plazo,
                DATE_FORMAT(m.mov_fecharegistro, '%d/%m/%Y %H:%i') AS fecha,
                COALESCE(o.area_nombre, 'MESA DE PARTES') AS origen
           FROM movimiento m
           INNER JOIN documento d ON d.documento_id = m.documento_id
           LEFT JOIN area o ON o.area_cod = m.area_origen_id
          WHERE m.mov_tipo = 'ATENCION' AND m.areadestino_id = ? AND m.mov_respuesta_fecha IS NULL
          ORDER BY m.mov_fecharegistro DESC"
    );
    $consulta->execute([$area]);
    $atenciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

    // Vencidos del área, con el mismo cálculo del semáforo (días hábiles)
    $consulta = $pdo->prepare(
        "SELECT d.documento_id, d.doc_expediente, d.doc_asunto, d.doc_estatus,
                d.doc_fecharegistro, d.doc_fecharecepcion, d.dias_respuesta
           FROM documento d
          WHERE d.area_destino = ? AND d.doc_estatus IN ('PENDIENTE', 'ACEPTADO')"
    );
    $consulta->execute([$area]);
    $enCurso = $consulta->fetchAll(PDO::FETCH_ASSOC);
    Plazos::agregar($enCurso);
    $vencidos = array_values(array_filter($enCurso, fn($f) => $f['plazo_semaforo'] === Plazos::ROJO));
    usort($vencidos, fn($x, $y) => (int) $x['plazo_restante'] <=> (int) $y['plazo_restante']);

    $grupos[] = [
        'clave' => 'por_aceptar', 'titulo' => 'Por aceptar', 'icono' => 'fas fa-inbox',
        'color' => '#B45309', 'ruta' => 'recibidos', 'total' => count($porAceptar),
        'items' => array_map(fn($f) => [
            'titulo'  => $f['doc_expediente'] ?: $f['documento_id'],
            'detalle' => $f['doc_asunto'],
            'pie'     => 'De ' . $f['origen'] . ' · ' . $f['fecha'],
        ], array_slice($porAceptar, 0, $LIMITE)),
    ];
    $grupos[] = [
        'clave' => 'copias', 'titulo' => 'Copias por confirmar', 'icono' => 'fas fa-copy',
        'color' => '#2C5282', 'ruta' => 'recibidos', 'total' => count($copias),
        'items' => array_map(fn($f) => [
            'titulo'  => $f['doc_expediente'] ?: $f['documento_id'],
            'detalle' => $f['doc_asunto'],
            'pie'     => 'De ' . $f['origen'] . ' · ' . $f['fecha'],
        ], array_slice($copias, 0, $LIMITE)),
    ];
    $grupos[] = [
        'clave' => 'atenciones', 'titulo' => 'Atenciones por responder', 'icono' => 'fas fa-tasks',
        'color' => '#1E3A5F', 'ruta' => 'recibidos', 'total' => count($atenciones),
        'items' => array_map(fn($f) => [
            'titulo'  => $f['doc_expediente'] ?: $f['documento_id'],
            'detalle' => $f['doc_asunto'],
            'pie'     => 'Pedida por ' . $f['origen'] . ' · plazo ' . (int) $f['plazo'] . ' día(s)',
        ], array_slice($atenciones, 0, $LIMITE)),
    ];
    $grupos[] = [
        'clave' => 'vencidos', 'titulo' => 'Con plazo vencido', 'icono' => 'fas fa-exclamation-circle',
        'color' => '#B91C1C', 'ruta' => 'recibidos', 'total' => count($vencidos), 'informativo' => true,
        'items' => array_map(fn($f) => [
            'titulo'  => $f['doc_expediente'] ?: $f['documento_id'],
            'detalle' => $f['doc_asunto'],
            'pie'     => 'Venció el ' . $f['plazo_limite'] . ' · ' . abs((int) $f['plazo_restante']) . ' día(s) de atraso',
        ], array_slice($vencidos, 0, $LIMITE)),
    ];
} else {
    // Administrador: vencidos de toda la institución y envíos sin acuse de recepción
    $enCurso = $pdo->query(
        "SELECT d.documento_id, d.doc_expediente, d.doc_asunto, d.doc_estatus, d.doc_fecharegistro,
                d.doc_fecharecepcion, d.dias_respuesta, a.area_nombre AS area
           FROM documento d
           LEFT JOIN area a ON a.area_cod = d.area_destino
          WHERE d.doc_estatus IN ('PENDIENTE', 'ACEPTADO')"
    )->fetchAll(PDO::FETCH_ASSOC);
    Plazos::agregar($enCurso);
    $vencidos = array_values(array_filter($enCurso, fn($f) => $f['plazo_semaforo'] === Plazos::ROJO));
    usort($vencidos, fn($x, $y) => (int) $x['plazo_restante'] <=> (int) $y['plazo_restante']);

    $sinAcuse = $pdo->query(
        "SELECT m.documento_id, d.doc_expediente, d.doc_asunto,
                DATE_FORMAT(m.mov_fecharegistro, '%d/%m/%Y') AS fecha,
                ad.area_nombre AS destino, DATEDIFF(NOW(), m.mov_fecharegistro) AS dias
           FROM movimiento m
           INNER JOIN documento d ON d.documento_id = m.documento_id
           LEFT JOIN area ad ON ad.area_cod = m.areadestino_id
          WHERE m.mov_recibido_fecha IS NULL AND m.mov_estatus = 'PENDIENTE'
            AND m.mov_fecharegistro < NOW() - INTERVAL 2 DAY
          ORDER BY m.mov_fecharegistro"
    )->fetchAll(PDO::FETCH_ASSOC);

    $grupos[] = [
        'clave' => 'vencidos', 'titulo' => 'Trámites vencidos', 'icono' => 'fas fa-exclamation-circle',
        'color' => '#B91C1C', 'ruta' => 'movimientos', 'total' => count($vencidos),
        'items' => array_map(fn($f) => [
            'titulo'  => $f['doc_expediente'] ?: $f['documento_id'],
            'detalle' => $f['doc_asunto'],
            'pie'     => ($f['area'] ?: 'Sin área') . ' · ' . abs((int) $f['plazo_restante']) . ' día(s) de atraso',
        ], array_slice($vencidos, 0, $LIMITE)),
    ];
    $grupos[] = [
        'clave' => 'sin_acuse', 'titulo' => 'Sin acuse de recepción', 'icono' => 'fas fa-inbox',
        'color' => '#B45309', 'ruta' => 'movimientos', 'total' => count($sinAcuse),
        'items' => array_map(fn($f) => [
            'titulo'  => $f['doc_expediente'] ?: $f['documento_id'],
            'detalle' => $f['doc_asunto'],
            'pie'     => 'Enviado a ' . ($f['destino'] ?: '—') . ' el ' . $f['fecha'] . ' · ' . (int) $f['dias'] . ' día(s) sin confirmar',
        ], array_slice($sinAcuse, 0, $LIMITE)),
    ];
}

// El contador de la campana solo cuenta lo que exige una acción (los vencidos ya están contados en los otros grupos)
$porAtender = 0;
foreach ($grupos as $g) {
    if (empty($g['informativo'])) {
        $porAtender += $g['total'];
    }
}

echo json_encode([
    'comunicados' => ['total' => $sinLeer, 'items' => $comunicados],
    'grupos'      => $grupos,
    'por_atender' => $porAtender,
    'es_admin'    => $esAdmin,
]);
