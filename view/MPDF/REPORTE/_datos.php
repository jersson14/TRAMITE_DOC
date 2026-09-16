<?php
/**
 * Datos comunes de los reportes PDF del trámite (ticket y hojas de envío).
 *
 * Requiere $mysqli (../conexion.php) y $codigo ya validado (_acceso.php).
 * Deja disponibles:
 *   $institucion  datos de la entidad (nombre, logo, contacto)
 *   $tramite      el trámite con sus áreas y tipo de documento
 *   $movimientos  el recorrido, del primero al último, con origen y destino
 *   $totalAnexos  cantidad de anexos registrados
 *   $urlConsulta  dirección pública para consultar el trámite (va en el QR)
 */
// Solo se usa incluido desde un reporte que ya validó el acceso.
if (!isset($mysqli, $codigo)) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../../../lib/Institucion.php';

$institucion = Institucion::datos();
$raizSistema = realpath(__DIR__ . '/../../..');

/** Texto seguro para insertar en el HTML del PDF. */
function pdf_e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** "15/09/2026 22:07" */
function pdf_fecha_corta($valor): string
{
    $t = strtotime((string) $valor);
    return $t ? date('d/m/Y H:i', $t) : '';
}

/** "15 de septiembre de 2026, 22:07" (sin depender del idioma del servidor) */
function pdf_fecha_larga($valor): string
{
    $t = strtotime((string) $valor);
    if (!$t) {
        return '';
    }
    $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
              'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return date('j', $t) . ' de ' . $meses[(int) date('n', $t) - 1] . ' de ' . date('Y, H:i', $t);
}

/** Color de cada estado, con la paleta institucional (ámbar solo para pendiente). */
function pdf_color_estado($estado): string
{
    switch (strtoupper((string) $estado)) {
        case 'ACEPTADO':   return '#15803D';
        case 'FINALIZADO': return '#2C5282';
        case 'DERIVADO':   return '#1E3A5F';
        case 'RECHAZADO':  return '#B91C1C';
        default:           return '#B45309';
    }
}

// Logo: ruta de archivo absoluta, que es lo que mPDF lee de forma fiable
$logoPdf = $raizSistema . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $institucion['logo']);

// --- Trámite ---
$consulta = $mysqli->prepare(
    "SELECT d.documento_id, d.doc_expediente, d.doc_nrodocumento, d.doc_folio, d.doc_asunto,
            d.doc_observaciones, d.acciones, d.doc_estatus, d.doc_fecharegistro, d.dias_respuesta,
            d.doc_dniremitente, d.doc_celularremitente, d.doc_emailremitente, d.doc_direccionremitente,
            d.doc_representacion, d.doc_ruc, d.doc_empresa,
            CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS remitente,
            td.tipodo_descripcion AS tipo, origen.area_nombre AS origen, destino.area_nombre AS destino
       FROM documento d
       INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
       LEFT JOIN area origen ON origen.area_cod = d.area_origen
       LEFT JOIN area destino ON destino.area_cod = d.area_destino
      WHERE d.documento_id = ?"
);
$consulta->bind_param('s', $codigo);
$consulta->execute();
$tramite = $consulta->get_result()->fetch_assoc();

if (!$tramite) {
    http_response_code(404);
    exit('No se encontró el trámite ' . pdf_e($codigo) . '.');
}

// --- Recorrido: cada envío con su origen y su destino ---
$consulta = $mysqli->prepare(
    "SELECT m.movimiento_id, m.mov_fecharegistro, m.mov_descripcion, m.mov_estatus, m.mov_acciones,
            m.mov_tipo, m.mov_plazo_dias, m.mov_respuesta_fecha,
            COALESCE(ao.area_nombre, 'EXTERNO') AS origen, ad.area_nombre AS destino,
            (SELECT COUNT(*) FROM movimiento_anexo ma WHERE ma.movimiento_id = m.movimiento_id) AS anexos,
            m.mov_recibido_fecha AS recibido_fecha,
            (SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario)
               FROM usuario u LEFT JOIN empleado e ON e.empleado_id = u.empleado_id
              WHERE u.usu_id = m.mov_recibido_usuario) AS recibido_por
       FROM movimiento m
       LEFT JOIN area ao ON ao.area_cod = m.area_origen_id
       LEFT JOIN area ad ON ad.area_cod = m.areadestino_id
      WHERE m.documento_id = ?
      ORDER BY m.mov_fecharegistro ASC, m.movimiento_id ASC"
);
$consulta->bind_param('s', $codigo);
$consulta->execute();
$movimientos = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);

$consulta = $mysqli->prepare('SELECT COUNT(*) FROM documento_anexo WHERE documento_id = ?');
$consulta->bind_param('s', $codigo);
$consulta->execute();
$totalAnexos = (int) $consulta->get_result()->fetch_row()[0];

// --- Dirección pública de consulta, calculada desde donde está instalado el sistema ---
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$raizWeb = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 4)), '/');
$urlConsulta = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $raizWeb
    . '/seguimiento.php?codigo=' . rawurlencode($tramite['documento_id']);
