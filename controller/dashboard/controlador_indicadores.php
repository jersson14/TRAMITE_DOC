<?php
/**
 * Indicadores del tablero del administrador.
 *
 * Usa lib/Plazos.php, el mismo cálculo del semáforo de las bandejas: días
 * hábiles, sin sábados, domingos ni feriados. Así el tablero y las bandejas
 * nunca muestran cifras distintas.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../model/model_conexion.php';
require '../../model/model_feriado.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = (new conexionBD())->conexionPDO();

// --- Trámites en curso, con su plazo en días hábiles ---
$enCurso = $pdo->query(
    "SELECT d.documento_id, d.doc_expediente, d.doc_estatus, d.doc_asunto, d.doc_fecharegistro,
            d.doc_fecharecepcion, d.dias_respuesta, d.area_destino, a.area_nombre AS area
       FROM documento d
       LEFT JOIN area a ON a.area_cod = d.area_destino
      WHERE d.doc_estatus IN ('PENDIENTE', 'ACEPTADO')"
)->fetchAll(PDO::FETCH_ASSOC);
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

// --- Registros y finalizados ---
$registros = $pdo->query(
    "SELECT SUM(DATE(doc_fecharegistro) = CURDATE()) AS hoy,
            SUM(doc_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes,
            SUM(doc_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 1 MONTH, '%Y-%m-01')
                AND doc_fecharegistro < DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes_anterior,
            SUM(doc_estatus = 'FINALIZADO') AS finalizados,
            SUM(doc_estatus = 'RECHAZADO') AS rechazados
       FROM documento"
)->fetch(PDO::FETCH_ASSOC);

$finalizadosMes = (int) $pdo->query(
    "SELECT COUNT(DISTINCT documento_id) FROM movimiento
      WHERE mov_estatus = 'FINALIZADO' AND mov_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')"
)->fetchColumn();

// --- Tiempo de atención: días hábiles entre la presentación y el cierre ---
$cerrados = $pdo->query(
    "SELECT d.documento_id, COALESCE(d.doc_fecharecepcion, d.doc_fecharegistro) AS inicio,
            (SELECT MAX(m.mov_fecharegistro) FROM movimiento m
              WHERE m.documento_id = d.documento_id AND m.mov_estatus IN ('FINALIZADO', 'RECHAZADO')) AS fin
       FROM documento d
      WHERE d.doc_estatus IN ('FINALIZADO', 'RECHAZADO')
        AND d.doc_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01')"
)->fetchAll(PDO::FETCH_ASSOC);

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

// --- Registros por mes (últimos 6 meses) y tipos más frecuentes del año ---
$porMes = $pdo->query(
    "SELECT DATE_FORMAT(doc_fecharegistro, '%m/%Y') AS mes, COUNT(*) AS total
       FROM documento
      WHERE doc_fecharegistro >= DATE_FORMAT(NOW() - INTERVAL 5 MONTH, '%Y-%m-01')
      GROUP BY YEAR(doc_fecharegistro), MONTH(doc_fecharegistro)
      ORDER BY YEAR(doc_fecharegistro), MONTH(doc_fecharegistro)"
)->fetchAll(PDO::FETCH_ASSOC);

$porTipo = $pdo->query(
    "SELECT td.tipodo_descripcion AS tipo, COUNT(*) AS total
       FROM documento d INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
      WHERE YEAR(d.doc_fecharegistro) = YEAR(CURDATE())
      GROUP BY td.tipodo_descripcion ORDER BY total DESC LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

// --- Aviso: feriados del año siguiente sin cargar (los plazos los necesitan) ---
$MF = new Modelo_Feriado();
$anioSiguiente = (int) date('Y') + 1;
$avisos = [];
if ($MF->Faltan_Del_Anio((int) date('Y')) === 0) {
    $avisos[] = ['tipo' => 'peligro', 'texto' => 'No hay feriados cargados para ' . date('Y') . ': los plazos se están contando solo sin sábados y domingos.'];
} elseif ($MF->Faltan_Del_Anio($anioSiguiente) === 0 && (int) date('n') >= 10) {
    $avisos[] = ['tipo' => 'aviso', 'texto' => 'Aún no hay feriados cargados para ' . $anioSiguiente . '. Cárguelos en la pantalla de Feriados.'];
}

echo json_encode([
    'contadores' => $contadores + [
        'registrados_hoy'  => (int) $registros['hoy'],
        'registrados_mes'  => (int) $registros['mes'],
        'mes_anterior'     => (int) $registros['mes_anterior'],
        'finalizados_mes'  => $finalizadosMes,
        'finalizados'      => (int) $registros['finalizados'],
        'rechazados'       => (int) $registros['rechazados'],
    ],
    'areas'         => $areas,
    'mas_atrasados' => $masAtrasados,
    'atencion'      => $atencion,
    'por_mes'       => $porMes,
    'por_tipo'      => $porTipo,
    'avisos'        => $avisos,
]);
