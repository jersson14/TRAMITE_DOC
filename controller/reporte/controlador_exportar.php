<?php
/**
 * Exportación de reportes a PDF, Excel y CSV (lib/Exportador.php).
 *
 * El archivo reproduce la lista que muestra la pantalla: los filtros (fechas,
 * área, estado o tipo de documento) son opcionales y, si no se eligen, se
 * exporta la lista completa. Antes exigía un rango de fechas, así que al
 * exportar sin filtrar el archivo salía sin filas.
 *
 * La consulta se arma aquí y no con los procedimientos de las pantallas porque
 * aquellos usan BETWEEN con fechas sin hora: dejaban fuera los trámites
 * registrados el mismo día final después de las 00:00.
 *
 * Un área solo puede exportar su propia información: el área sale de la sesión.
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/Exportador.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../lib/ReportePlazos.php';
require_once __DIR__ . '/../../model/model_conexion.php';

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

if ($desde !== '' && !fechaValida($desde)) {
    Seguridad::responderError(422, 'La fecha inicial no es válida.');
}
if ($hasta !== '' && !fechaValida($hasta)) {
    Seguridad::responderError(422, 'La fecha final no es válida.');
}
if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
    Seguridad::responderError(422, 'La fecha inicial no puede ser posterior a la final.');
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

/**
 * Trámites con los filtros elegidos. $condiciones son pares SQL => valor que se
 * agregan al WHERE; el área del usuario se aplica siempre que no sea administrador.
 */
function listarTramites(PDO $pdo, string $desde, string $hasta, array $condiciones, bool $esAdmin, int $areaUsuario): array
{
    // Las pantallas de un área muestran al abrirse solo sus trámites recibidos y,
    // al buscar, también los que envió. El archivo sigue el mismo criterio para
    // que coincida con lo que se ve.
    $hayFiltros = $desde !== '' || $hasta !== '' || $condiciones !== [];
    $where = ['1 = 1'];
    $parametros = [];

    if ($desde !== '') {
        $where[] = 'd.doc_fecharegistro >= ?';
        $parametros[] = $desde . ' 00:00:00';
    }
    if ($hasta !== '') {
        // Hasta el final del día: con BETWEEN por fecha se perdían los del mismo día
        $where[] = 'd.doc_fecharegistro <= ?';
        $parametros[] = $hasta . ' 23:59:59';
    }
    foreach ($condiciones as $sql => $valor) {
        $where[] = $sql;
        $parametros[] = $valor;
    }
    if (!$esAdmin) {
        if ($hayFiltros) {
            $where[] = '(d.area_origen = ? OR d.area_destino = ?)';
            $parametros[] = $areaUsuario;
            $parametros[] = $areaUsuario;
        } else {
            // Igual que la bandeja de Recibidos: lo que está en el área y lo que llegó
            // en copia o con atención pedida
            $where[] = '(d.area_destino = ? OR EXISTS (SELECT 1 FROM movimiento m
                            WHERE m.documento_id = d.documento_id AND m.areadestino_id = ?
                              AND m.mov_tipo IN (\'COPIA\', \'ATENCION\')))';
            $parametros[] = $areaUsuario;
            $parametros[] = $areaUsuario;
        }
    }

    $consulta = $pdo->prepare(
        "SELECT d.documento_id, d.doc_expediente, d.doc_nrodocumento, d.doc_asunto, d.doc_estatus,
                d.doc_dniremitente, d.doc_folio,
                DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y %H:%i') AS fecha_formateada,
                CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS REMITENTE,
                td.tipodo_descripcion,
                COALESCE(o.area_nombre, 'MESA DE PARTES VIRTUAL') AS origen,
                COALESCE(dst.area_nombre, '—') AS destino
           FROM documento d
           INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
           LEFT JOIN area o ON o.area_cod = d.area_origen
           LEFT JOIN area dst ON dst.area_cod = d.area_destino
          WHERE " . implode(' AND ', $where) . "
          ORDER BY d.doc_fecharegistro DESC"
    );
    $consulta->execute($parametros);
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

$fechas = [
    'Desde' => $desde !== '' ? date('d/m/Y', strtotime($desde)) : 'Sin límite',
    'Hasta' => $hasta !== '' ? date('d/m/Y', strtotime($hasta)) : 'Sin límite',
];
$areaUsuario = Seguridad::areaId();

if ($reporte === 'fecha_area') {
    $area = $esAdmin ? (int) ($_POST['area'] ?? 0) : $areaUsuario;
    // En el reporte del administrador el área es la localización actual del trámite
    $condiciones = ($esAdmin && $area > 0) ? ['d.area_destino = ?' => $area] : [];
    $filas = listarTramites($pdo, $desde, $hasta, $condiciones, $esAdmin, $areaUsuario);
    Exportador::entregar($formato, [
        'titulo'   => 'Trámites por fecha y área',
        'archivo'  => 'tramites_por_area',
        'filtros'  => $fechas + ['Área' => $esAdmin ? nombreArea($pdo, $area) : nombreArea($pdo, $areaUsuario)],
        'resumen'  => resumenEstados($filas),
        'columnas' => columnasTramites(),
        'filas'    => $filas,
    ]);
}

if ($reporte === 'fecha_estado') {
    $estado = strtoupper(trim((string) ($_POST['estado'] ?? '')));
    if ($estado !== '' && !in_array($estado, ['PENDIENTE', 'ACEPTADO', 'FINALIZADO', 'RECHAZADO'], true)) {
        Seguridad::responderError(422, 'El estado elegido no es válido.');
    }
    $filas = listarTramites($pdo, $desde, $hasta, $estado !== '' ? ['d.doc_estatus = ?' => $estado] : [], $esAdmin, $areaUsuario);
    Exportador::entregar($formato, [
        'titulo'   => 'Trámites por fecha y estado',
        'archivo'  => 'tramites_por_estado',
        'filtros'  => $fechas + [
            'Estado' => $estado !== '' ? $estado : 'Todos',
            'Área'   => $esAdmin ? 'Todas' : nombreArea($pdo, $areaUsuario),
        ],
        'resumen'  => resumenEstados($filas),
        'columnas' => columnasTramites(),
        'filas'    => $filas,
    ]);
}

if ($reporte === 'fecha_tipodoc') {
    $tipo = (int) ($_POST['tipodoc'] ?? 0);
    $filas = listarTramites($pdo, $desde, $hasta, $tipo > 0 ? ['d.tipodocumento_id = ?' => $tipo] : [], $esAdmin, $areaUsuario);
    $nombreTipo = 'Todos';
    if ($tipo > 0) {
        $consulta = $pdo->prepare('SELECT tipodo_descripcion FROM tipo_documento WHERE tipodocumento_id = ?');
        $consulta->execute([$tipo]);
        $nombreTipo = (string) ($consulta->fetchColumn() ?: 'Todos');
    }
    Exportador::entregar($formato, [
        'titulo'   => 'Trámites por fecha y tipo de documento',
        'archivo'  => 'tramites_por_tipo',
        'filtros'  => $fechas + [
            'Tipo de documento' => $nombreTipo,
            'Área'              => $esAdmin ? 'Todas' : nombreArea($pdo, $areaUsuario),
        ],
        'resumen'  => resumenEstados($filas),
        'columnas' => columnasTramites(),
        'filas'    => $filas,
    ]);
}

if ($reporte === 'plazos') {
    // El reporte de plazos sí necesita un período: mide lo ocurrido en él
    if ($desde === '' || $hasta === '') {
        Seguridad::responderError(422, 'Elija el rango de fechas del reporte.');
    }
    $area = $esAdmin ? (int) ($_POST['area'] ?? 0) : $areaUsuario;
    $datos = ReportePlazos::calcular($desde, $hasta, $area > 0 ? $area : null);
    $t = $datos['totales'];

    Exportador::entregar($formato, [
        'titulo'   => 'Plazos y productividad por área',
        'archivo'  => 'plazos_por_area',
        'filtros'  => $fechas + ['Área' => nombreArea($pdo, $area)],
        'resumen'  => [
            'Recibidos'        => $t['recibidos'],
            'Despachados'      => $t['despachados'],
            'En curso hoy'     => $t['en_curso'],
            'Vencidos hoy'     => $t['vencidos'],
            'Dentro del plazo' => $t['cumplimiento'] === null ? 'sin datos' : $t['cumplimiento'] . '%',
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
                ['clave' => 'expediente', 'titulo' => 'N° Expediente',  'ancho' => 12],
                ['clave' => 'asunto',     'titulo' => 'Asunto',         'ancho' => 26],
                ['clave' => 'remitente',  'titulo' => 'Remitente',      'ancho' => 18],
                ['clave' => 'area',       'titulo' => 'Área',           'ancho' => 14],
                ['clave' => 'estado',     'titulo' => 'Estado',         'ancho' => 9, 'alineacion' => 'centro'],
                ['clave' => 'limite',     'titulo' => 'Venció el',      'ancho' => 9, 'alineacion' => 'centro'],
                ['clave' => 'atraso',     'titulo' => 'Días de atraso', 'ancho' => 9, 'alineacion' => 'centro', 'numero' => true],
            ],
            'filas'    => $datos['vencidos'],
        ]],
    ]);
}

Seguridad::responderError(422, 'Reporte no válido.');
