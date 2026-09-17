<?php
/**
 * Indicadores del tablero, para el administrador y para el personal de área.
 *
 * Los plazos se calculan con lib/Plazos.php, el mismo cálculo del semáforo de
 * las bandejas: días hábiles, sin sábados, domingos ni feriados. Así el tablero
 * y las bandejas nunca muestran cifras distintas.
 *
 * El administrador ve toda la institución; el personal de área, solo lo que
 * pasó por su área, con las mismas reglas de su bandeja de recibidos (lo que le
 * derivaron como responsable, en copia o para atender).
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../model/model_conexion.php';
require_once __DIR__ . '/../../model/model_feriado.php';

header('Content-Type: application/json; charset=utf-8');

$esAdmin = ($_SESSION['S_ROL'] ?? '') === Seguridad::ROL_ADMIN;
$area = (int) ($_SESSION['S_IDAREA'] ?? 0);
$pdo = (new conexionBD())->conexionPDO();

/** Ejecuta una consulta con los parámetros dados y devuelve todas las filas. */
$consultar = function (string $sql, array $parametros = []) use ($pdo): array {
    $query = $pdo->prepare($sql);
    $query->execute($parametros);
    return $query->fetchAll(PDO::FETCH_ASSOC);
};
/** Primer valor de la primera fila (para los conteos). */
$contar = function (string $sql, array $parametros = []) use ($pdo): int {
    $query = $pdo->prepare($sql);
    $query->execute($parametros);
    return (int) $query->fetchColumn();
};

// --- Trámites en curso, con su plazo en días hábiles ---
// Para un área son los que tiene en su bandeja: como responsable, en copia o
// para atender, igual que en Recibidos.
$condicionArea = 'd.area_destino = ? OR EXISTS (SELECT 1 FROM movimiento m
                      WHERE m.documento_id = d.documento_id AND m.areadestino_id = ?
                        AND m.mov_tipo IN (\'COPIA\', \'ATENCION\'))';
$enCurso = $consultar(
    "SELECT d.documento_id, d.doc_expediente, d.doc_estatus, d.doc_asunto, d.doc_fecharegistro,
            d.doc_fecharecepcion, d.dias_respuesta, d.area_destino, a.area_nombre AS area
       FROM documento d
       LEFT JOIN area a ON a.area_cod = d.area_destino
      WHERE d.doc_estatus IN ('PENDIENTE', 'ACEPTADO')"
    . ($esAdmin ? '' : " AND ($condicionArea)"),
    $esAdmin ? [] : [$area, $area]
);
Plazos::agregar($enCurso);

$contadores = ['en_curso' => count($enCurso), 'vencidos' => 0, 'por_vencer' => 0, 'sin_plazo' => 0];
$areas = [];
foreach ($enCurso as $fila) {
    $nombre = $fila['area'] ?: 'Sin área';
    if (!isset($areas[$nombre])) {
        $areas[$nombre] = ['area' => $nombre, 'en_curso' => 0, 'vencidos' => 0, 'por_vencer' => 0, 'mas_atrasado' => 0];
    }
    $areas[$nombre]['en_curso']++;
    if ($fila['plazo_semaforo'] === Plazos::ROJO) {
        $contadores['vencidos']++;
        $areas[$nombre]['vencidos']++;
        $areas[$nombre]['mas_atrasado'] = max($areas[$nombre]['mas_atrasado'], abs((int) $fila['plazo_restante']));
    } elseif ($fila['plazo_semaforo'] === Plazos::AMBAR) {
        $contadores['por_vencer']++;
        $areas[$nombre]['por_vencer']++;
    } elseif ($fila['plazo_semaforo'] === Plazos::SIN_PLAZO) {
        $contadores['sin_plazo']++;
    }
}
usort($areas, fn($x, $y) => [$y['vencidos'], $y['en_curso']] <=> [$x['vencidos'], $x['en_curso']]);

// Los cinco más atrasados, para saber por dónde empezar
$vencidos = array_values(array_filter($enCurso, fn($f) => $f['plazo_semaforo'] === Plazos::ROJO));
usort($vencidos, fn($x, $y) => (int) $x['plazo_restante'] <=> (int) $y['plazo_restante']);
$masAtrasados = array_map(fn($f) => [
    'expediente' => $f['doc_expediente'] ?: $f['documento_id'],
    'asunto'     => mb_substr((string) $f['doc_asunto'], 0, 80),
    'area'       => $f['area'] ?: 'Sin área',
    'dias'       => abs((int) $f['plazo_restante']),
    'limite'     => $f['plazo_limite'],
], array_slice($vencidos, 0, 5));

// --- Entradas y salidas ---
if ($esAdmin) {
    $registros = $consultar(
        "SELECT SUM(DATE(doc_fecharegistro) = CURDATE()) AS hoy,
                SUM(doc_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes,
                SUM(doc_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
                    AND doc_fecharegistro < DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes_anterior,
                SUM(doc_estatus = 'FINALIZADO') AS finalizados
           FROM documento"
    )[0];
    $finalizadosMes = $contar(
        "SELECT COUNT(DISTINCT documento_id) FROM movimiento
          WHERE mov_estatus = 'FINALIZADO' AND mov_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')"
    );
    $despachados = null;
} else {
    // Para un área, entra lo que le derivan y sale lo que ella deriva
    $registros = $consultar(
        "SELECT SUM(DATE(mov_fecharegistro) = CURDATE()) AS hoy,
                SUM(mov_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes,
                SUM(mov_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
                    AND mov_fecharegistro < DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes_anterior,
                0 AS finalizados
           FROM movimiento WHERE areadestino_id = ?",
        [$area]
    )[0];
    $finalizadosMes = $contar(
        "SELECT COUNT(DISTINCT m.documento_id) FROM movimiento m
          WHERE m.mov_estatus = 'FINALIZADO' AND m.areadestino_id = ?
            AND m.mov_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')",
        [$area]
    );
    $despachados = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE area_origen_id = ? AND mov_tipo = 'PRINCIPAL'
            AND mov_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')",
        [$area]
    );
}

// --- Lo que requiere acción, con su enlace al módulo ---
$pendientes = [];
if ($esAdmin) {
    $sinAcuse = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE mov_tipo = 'PRINCIPAL' AND mov_recibido_fecha IS NULL AND mov_estatus = 'PENDIENTE'"
    );
    $atenciones = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE mov_tipo = 'ATENCION' AND mov_respuesta IS NULL"
    );
    $pendientes[] = ['texto' => 'Trámites vencidos', 'total' => $contadores['vencidos'], 'ruta' => 'movimientos', 'urgente' => true];
    $pendientes[] = ['texto' => 'Envíos sin acuse de recepción', 'total' => $sinAcuse, 'ruta' => 'movimientos', 'urgente' => false];
    $pendientes[] = ['texto' => 'Atenciones sin responder', 'total' => $atenciones, 'ruta' => 'movimientos', 'urgente' => false];
    $pendientes[] = ['texto' => 'Sin plazo de respuesta definido', 'total' => $contadores['sin_plazo'], 'ruta' => 'movimientos', 'urgente' => false];
} else {
    $porAceptar = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE areadestino_id = ? AND mov_tipo = 'PRINCIPAL' AND mov_estatus = 'PENDIENTE'",
        [$area]
    );
    $copias = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE areadestino_id = ? AND mov_tipo = 'COPIA' AND mov_recibido_fecha IS NULL",
        [$area]
    );
    $atenciones = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE areadestino_id = ? AND mov_tipo = 'ATENCION' AND mov_respuesta IS NULL",
        [$area]
    );
    $esperandoAcuse = $contar(
        "SELECT COUNT(*) FROM movimiento
          WHERE area_origen_id = ? AND mov_tipo = 'PRINCIPAL' AND mov_recibido_fecha IS NULL
            AND mov_estatus = 'PENDIENTE'",
        [$area]
    );
    $pendientes[] = ['texto' => 'Por aceptar en su bandeja', 'total' => $porAceptar, 'ruta' => 'recibidos', 'urgente' => true];
    $pendientes[] = ['texto' => 'Trámites vencidos', 'total' => $contadores['vencidos'], 'ruta' => 'recibidos', 'urgente' => true];
    $pendientes[] = ['texto' => 'Atenciones por responder', 'total' => $atenciones, 'ruta' => 'recibidos', 'urgente' => false];
    $pendientes[] = ['texto' => 'Copias sin confirmar lectura', 'total' => $copias, 'ruta' => 'recibidos', 'urgente' => false];
    $pendientes[] = ['texto' => 'Sus envíos esperando acuse', 'total' => $esperandoAcuse, 'ruta' => 'enviados', 'urgente' => false];
}

// --- Trámites detenidos: en curso y sin ningún movimiento en 10 días hábiles ---
$ultimos = $consultar(
    "SELECT d.documento_id, d.doc_expediente, d.doc_asunto, a.area_nombre AS area,
            (SELECT MAX(m.mov_fecharegistro) FROM movimiento m WHERE m.documento_id = d.documento_id) AS ultimo
       FROM documento d
       LEFT JOIN area a ON a.area_cod = d.area_destino
      WHERE d.doc_estatus IN ('PENDIENTE', 'ACEPTADO')"
    . ($esAdmin ? '' : " AND ($condicionArea)"),
    $esAdmin ? [] : [$area, $area]
);
$hoy = new DateTimeImmutable('today');
$detenidos = [];
foreach ($ultimos as $fila) {
    if (!$fila['ultimo']) {
        continue;
    }
    try {
        $desde = (new DateTimeImmutable($fila['ultimo']))->setTime(0, 0);
    } catch (Throwable $e) {
        continue;
    }
    $habiles = Plazos::habilesEntre($desde, $hoy);
    if ($habiles >= 10) {
        $detenidos[] = [
            'expediente' => $fila['doc_expediente'] ?: $fila['documento_id'],
            'asunto'     => mb_substr((string) $fila['doc_asunto'], 0, 60),
            'area'       => $fila['area'] ?: 'Sin área',
            'dias'       => $habiles,
        ];
    }
}
usort($detenidos, fn($x, $y) => $y['dias'] <=> $x['dias']);
$contadores['detenidos'] = count($detenidos);
$detenidos = array_slice($detenidos, 0, 5);

// --- Movimientos recientes: qué está pasando ahora en el sistema ---
$actividad = $consultar(
    "SELECT m.mov_fecharegistro AS fecha, m.mov_tipo AS tipo, m.mov_estatus AS estado,
            d.doc_expediente AS expediente, d.documento_id,
            ao.area_nombre AS origen, ad.area_nombre AS destino,
            -- Un envío de un área a sí misma es el ingreso del documento. De quién
            -- viene lo dice doc_procedencia (migración 021/022): puede ser un área
            -- de la entidad, no siempre un ciudadano.
            d.doc_procedencia AS procedencia,
            -- Del PRIMER movimiento: documento.area_origen se sobrescribe en cada
            -- derivación y ya no dice de dónde nació el trámite.
            (SELECT ao2.area_nombre FROM movimiento m2
               LEFT JOIN area ao2 ON ao2.area_cod = m2.area_origen_id
              WHERE m2.documento_id = d.documento_id
              ORDER BY m2.movimiento_id LIMIT 1) AS area_procedencia,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario) AS persona
       FROM movimiento m
       INNER JOIN documento d ON d.documento_id = m.documento_id
       LEFT JOIN area ao ON ao.area_cod = m.area_origen_id
       LEFT JOIN area ad ON ad.area_cod = m.areadestino_id
       LEFT JOIN usuario u ON u.usu_id = m.usuario_id
       LEFT JOIN empleado e ON e.empleado_id = u.empleado_id"
    . ($esAdmin ? '' : ' WHERE m.areadestino_id = ? OR m.area_origen_id = ?') .
    " ORDER BY m.movimiento_id DESC LIMIT 8",
    $esAdmin ? [] : [$area, $area]
);

// --- Tiempo de atención: días hábiles entre la presentación y el cierre ---
$cerrados = $consultar(
    "SELECT d.documento_id, COALESCE(d.doc_fecharecepcion, d.doc_fecharegistro) AS inicio,
            (SELECT MAX(m.mov_fecharegistro) FROM movimiento m
              WHERE m.documento_id = d.documento_id AND m.mov_estatus IN ('FINALIZADO', 'RECHAZADO')) AS fin
       FROM documento d
      WHERE d.doc_estatus IN ('FINALIZADO', 'RECHAZADO')
        AND d.doc_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01')"
    . ($esAdmin ? '' : " AND ($condicionArea)"),
    $esAdmin ? [] : [$area, $area]
);

$dias = [];
foreach ($cerrados as $fila) {
    if (!$fila['fin']) {
        continue;
    }
    try {
        $inicio = (new DateTimeImmutable($fila['inicio']))->setTime(0, 0);
        $fin = (new DateTimeImmutable($fila['fin']))->setTime(0, 0);
    } catch (Throwable $e) {
        continue;
    }
    if ($fin >= $inicio) {
        $dias[] = Plazos::habilesEntre($inicio, $fin);
    }
}
sort($dias);
$atencion = [
    'documentos' => count($dias),
    'promedio'   => $dias ? round(array_sum($dias) / count($dias), 1) : null,
    'mediana'    => $dias ? $dias[intdiv(count($dias), 2)] : null,
    'maximo'     => $dias ? end($dias) : null,
];

// --- Entradas por mes (últimos 6) y tipos más frecuentes del año ---
$porMes = $esAdmin
    ? $consultar(
        "SELECT DATE_FORMAT(doc_fecharegistro, '%m/%Y') AS mes, COUNT(*) AS total
           FROM documento
          WHERE doc_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 5 MONTH, '%Y-%m-01')
          GROUP BY YEAR(doc_fecharegistro), MONTH(doc_fecharegistro)
          ORDER BY YEAR(doc_fecharegistro), MONTH(doc_fecharegistro)"
    )
    : $consultar(
        "SELECT DATE_FORMAT(mov_fecharegistro, '%m/%Y') AS mes, COUNT(*) AS total
           FROM movimiento
          WHERE areadestino_id = ? AND mov_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 5 MONTH, '%Y-%m-01')
          GROUP BY YEAR(mov_fecharegistro), MONTH(mov_fecharegistro)
          ORDER BY YEAR(mov_fecharegistro), MONTH(mov_fecharegistro)",
        [$area]
    );

$porTipo = $consultar(
    "SELECT td.tipodo_descripcion AS tipo, COUNT(*) AS total
       FROM documento d INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
      WHERE YEAR(d.doc_fecharegistro) = YEAR(CURDATE())"
    . ($esAdmin ? '' : " AND ($condicionArea)") .
    " GROUP BY td.tipodo_descripcion ORDER BY total DESC LIMIT 5",
    $esAdmin ? [] : [$area, $area]
);

// --- Avisos del sistema (solo al administrador, que es quien puede resolverlos) ---
$avisos = [];
if ($esAdmin) {
    $MF = new Modelo_Feriado();
    $anioSiguiente = (int) date('Y') + 1;
    if ($MF->Faltan_Del_Anio((int) date('Y')) === 0) {
        $avisos[] = ['tipo' => 'peligro', 'texto' => 'No hay feriados cargados para ' . date('Y') . ': los plazos se están contando solo sin sábados y domingos.'];
    } elseif ($MF->Faltan_Del_Anio($anioSiguiente) === 0 && (int) date('n') >= 10) {
        $avisos[] = ['tipo' => 'aviso', 'texto' => 'Aún no hay feriados cargados para ' . $anioSiguiente . '. Cárguelos en la pantalla de Feriados.'];
    }
}

echo json_encode([
    'rol'  => $esAdmin ? 'admin' : 'area',
    'area' => $esAdmin ? null : ($_SESSION['S_AREA'] ?? ''),
    'contadores' => $contadores + [
        'registrados_hoy' => (int) $registros['hoy'],
        'registrados_mes' => (int) $registros['mes'],
        'mes_anterior'    => (int) $registros['mes_anterior'],
        'finalizados_mes' => $finalizadosMes,
        'finalizados'     => (int) $registros['finalizados'],
        'despachados_mes' => $despachados,
    ],
    'areas'         => $areas,
    'mas_atrasados' => $masAtrasados,
    'detenidos'     => $detenidos,
    'pendientes'    => $pendientes,
    'actividad'     => $actividad,
    'atencion'      => $atencion,
    'por_mes'       => $porMes,
    'por_tipo'      => $porTipo,
    'avisos'        => $avisos,
], JSON_UNESCAPED_UNICODE);
