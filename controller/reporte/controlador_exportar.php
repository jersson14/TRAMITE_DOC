<?php
/**
 * Exportación de reportes a PDF, Excel y CSV (lib/Exportador.php).
 *
 * Recibe el reporte y los mismos filtros que la pantalla, vuelve a consultar en
 * el servidor y devuelve el archivo con todos los resultados, no solo la página
 * visible. Un área solo puede exportar su propia información: el área sale de la
 * sesión, no de lo que manda el navegador.
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/Exportador.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../lib/ReportePlazos.php';
require_once __DIR__ . '/../../model/model_conexion.php';
require '../../model/model_tramite.php';
require '../../model/model_tramite_area.php';

$reporte = strtolower(trim((string) ($_POST['reporte'] ?? '')));
$formato = strtolower(trim((string) ($_POST['formato'] ?? 'pdf')));
$desde = trim((string) ($_POST['desde'] ?? ''));
$hasta = trim((string) ($_POST['hasta'] ?? ''));
$esAdmin = Seguridad::esAdmin();

/** Valida una fecha Y-m-d. */
function fechaValida(string $fecha): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

if (!fechaValida($desde) || !fechaValida($hasta) || $desde > $hasta) {
    Seguridad::responderError(422, 'Elija un rango de fechas válido (la fecha inicial no puede ser posterior a la final).');
}

$pdo = (new conexionBD())->conexionPDO();

/** Nombre de un área, para mostrarlo en los filtros del archivo. */
function nombreArea(PDO $pdo, $id): string
{
    if ((int) $id <= 0) {
        return 'Todas';
    }
    $consulta = $pdo->prepare('SELECT area_nombre FROM area WHERE area_cod = ?');
    $consulta->execute([(int) $id]);
    return (string) ($consulta->fetchColumn() ?: 'Todas');
}

/** Columnas del listado de trámites, iguales en los tres reportes por fecha. */
function columnasTramites(): array
{
    return [
        ['clave' => 'doc_expediente',      'titulo' => 'N° Expediente', 'ancho' => 11],
        ['clave' => 'documento_id',        'titulo' => 'Código',        'ancho' => 7],
        ['clave' => 'doc_nrodocumento',    'titulo' => 'N° Documento',  'ancho' => 8],
        ['clave' => 'tipodo_descripcion',  'titulo' => 'Tipo',          'ancho' => 9],
        ['clave' => 'fecha_formateada',    'titulo' => 'Registrado',    'ancho' => 9],
        ['clave' => 'doc_dniremitente',    'titulo' => 'DNI',           'ancho' => 6],
        ['clave' => 'REMITENTE',           'titulo' => 'Remitente',     'ancho' => 14],
        ['clave' => 'doc_asunto',          'titulo' => 'Asunto',        'ancho' => 16],
        ['clave' => 'origen',              'titulo' => 'Área origen',   'ancho' => 9],
        ['clave' => 'destino',             'titulo' => 'Localización',  'ancho' => 9],
        ['clave' => 'doc_estatus',         'titulo' => 'Estado',        'ancho' => 7, 'alineacion' => 'centro'],
    ];
}

/** Cuántos trámites hay por estado, para el resumen del encabezado. */
function resumenEstados(array $filas): array
{
    $resumen = ['Total de trámites' => count($filas)];
    foreach (['PENDIENTE', 'ACEPTADO', 'FINALIZADO', 'RECHAZADO'] as $estado) {
        $total = count(array_filter($filas, fn($f) => ($f['doc_estatus'] ?? '') === $estado));
        if ($total > 0) {
            $resumen[ucfirst(strtolower($estado)) . 's'] = $total;
        }
    }
    return $resumen;
}

$fechas = ['Desde' => date('d/m/Y', strtotime($desde)), 'Hasta' => date('d/m/Y', strtotime($hasta))];

if ($reporte === 'fecha_area') {
    $area = $esAdmin ? (int) ($_POST['area'] ?? 0) : Seguridad::areaId();
    if ($esAdmin) {
        $consulta = (new Modelo_Tramite())->Listar_Tramite_Fecha_Area($desde, $hasta, $area);
    } else {
        $consulta = (new Modelo_TramiteArea())->Listar_Tramite_Fecha_Area($desde, $hasta, $area);
    }
    $filas = $consulta['data'] ?? [];
    Exportador::entregar($formato, [
        'titulo'   => 'Trámites por fecha y área',
        'archivo'  => 'tramites_por_area',
        'filtros'  => $fechas + ['Área' => nombreArea($pdo, $area)],
        'resumen'  => resumenEstados($filas),
        'columnas' => columnasTramites(),
        'filas'    => $filas,
    ]);
}

if ($reporte === 'fecha_estado') {
    $estado = strtoupper(trim((string) ($_POST['estado'] ?? '')));
    if (!in_array($estado, ['PENDIENTE', 'ACEPTADO', 'FINALIZADO', 'RECHAZADO'], true)) {
        Seguridad::responderError(422, 'Elija un estado válido.');
    }
    if ($esAdmin) {
        $consulta = (new Modelo_Tramite())->Listar_Tramite_Fecha_Estado($desde, $hasta, $estado);
        $area = 0;
    } else {
        $area = Seguridad::areaId();
        $consulta = (new Modelo_TramiteArea())->Listar_Tramite_Fecha_Estado($desde, $hasta, $estado, $area);
    }
    $filas = $consulta['data'] ?? [];
    Exportador::entregar($formato, [
        'titulo'   => 'Trámites por fecha y estado',
        'archivo'  => 'tramites_por_estado',
        'filtros'  => $fechas + ['Estado' => $estado, 'Área' => nombreArea($pdo, $area)],
        'resumen'  => ['Total de trámites' => count($filas)],
        'columnas' => columnasTramites(),
        'filas'    => $filas,
    ]);
}

if ($reporte === 'fecha_tipodoc') {
    $tipo = (int) ($_POST['tipodoc'] ?? 0);
    if ($tipo <= 0) {
        Seguridad::responderError(422, 'Elija un tipo de documento.');
    }
    if ($esAdmin) {
        $consulta = (new Modelo_Tramite())->Listar_Tramite_Fecha_Tipodoc($desde, $hasta, $tipo);
        $area = 0;
    } else {
        $area = Seguridad::areaId();
        $consulta = (new Modelo_TramiteArea())->Listar_Tramite_Fecha_TipoDoc($desde, $hasta, $tipo, $area);
    }
    $filas = $consulta['data'] ?? [];
    $nombreTipo = $pdo->prepare('SELECT tipodo_descripcion FROM tipo_documento WHERE tipodocumento_id = ?');
    $nombreTipo->execute([$tipo]);
    Exportador::entregar($formato, [
        'titulo'   => 'Trámites por fecha y tipo de documento',
        'archivo'  => 'tramites_por_tipo',
        'filtros'  => $fechas + ['Tipo de documento' => (string) ($nombreTipo->fetchColumn() ?: '—'), 'Área' => nombreArea($pdo, $area)],
        'resumen'  => resumenEstados($filas),
        'columnas' => columnasTramites(),
        'filas'    => $filas,
    ]);
}

if ($reporte === 'plazos') {
    $area = $esAdmin ? (int) ($_POST['area'] ?? 0) : Seguridad::areaId();
    $datos = ReportePlazos::calcular($desde, $hasta, $area > 0 ? $area : null);
    $t = $datos['totales'];

    Exportador::entregar($formato, [
        'titulo'   => 'Plazos y productividad por área',
        'archivo'  => 'plazos_por_area',
        'filtros'  => $fechas + ['Área' => nombreArea($pdo, $area)],
        'resumen'  => [
            'Recibidos'          => $t['recibidos'],
            'Despachados'        => $t['despachados'],
            'En curso hoy'       => $t['en_curso'],
            'Vencidos hoy'       => $t['vencidos'],
            'Dentro del plazo'   => $t['cumplimiento'] === null ? 'sin datos' : $t['cumplimiento'] . '%',
        ],
        'subtitulo' => 'Resumen por área',
        'columnas' => [
            ['clave' => 'area',         'titulo' => 'Área',              'ancho' => 20],
            ['clave' => 'recibidos',    'titulo' => 'Recibidos',         'ancho' => 9, 'alineacion' => 'centro', 'numero' => true],
            ['clave' => 'despachados',  'titulo' => 'Despachados',       'ancho' => 9, 'alineacion' => 'centro', 'numero' => true],
            ['clave' => 'promedio',     'titulo' => 'Días promedio',     'ancho' => 9, 'alineacion' => 'centro',
             'formato' => fn($v) => $v === null ? '—' : $v],
            ['clave' => 'maximo',       'titulo' => 'Días máximo',       'ancho' => 8, 'alineacion' => 'centro',
             'formato' => fn($v) => $v === null ? '—' : $v],
            ['clave' => 'cumplimiento', 'titulo' => 'Dentro del plazo',  'ancho' => 10, 'alineacion' => 'centro',
             'formato' => fn($v) => $v === null ? 'sin plazo' : $v . '%'],
            ['clave' => 'en_curso',     'titulo' => 'En curso hoy',      'ancho' => 9, 'alineacion' => 'centro', 'numero' => true],
            ['clave' => 'vencidos',     'titulo' => 'Vencidos hoy',      'ancho' => 9, 'alineacion' => 'centro', 'numero' => true],
        ],
        'filas'    => $datos['areas'],
        'bloques'  => [[
            'titulo'   => 'Trámites con plazo vencido (' . count($datos['vencidos']) . ')',
            'columnas' => [
                ['clave' => 'expediente', 'titulo' => 'N° Expediente', 'ancho' => 12],
                ['clave' => 'asunto',     'titulo' => 'Asunto',        'ancho' => 26],
                ['clave' => 'remitente',  'titulo' => 'Remitente',     'ancho' => 18],
                ['clave' => 'area',       'titulo' => 'Área',          'ancho' => 14],
                ['clave' => 'estado',     'titulo' => 'Estado',        'ancho' => 9, 'alineacion' => 'centro'],
                ['clave' => 'limite',     'titulo' => 'Venció el',     'ancho' => 9, 'alineacion' => 'centro'],
                ['clave' => 'atraso',     'titulo' => 'Días de atraso', 'ancho' => 9, 'alineacion' => 'centro', 'numero' => true],
            ],
            'filas'    => $datos['vencidos'],
        ]],
    ]);
}

Seguridad::responderError(422, 'Reporte no válido.');
