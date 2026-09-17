<?php
/**
 * Consulta pública de un trámite (seguimiento.php).
 *
 * Se busca con el N° de expediente (EXP-2026-000006, o 2026-6) o con el código de
 * seguimiento (D0000041), siempre junto con el DNI del remitente. Solo devuelve
 * lo que el ciudadano necesita: estado, área actual, plazo y el recorrido del
 * envío principal; no incluye nombres de funcionarios ni archivos internos.
 */
require_once __DIR__ . '/../../lib/Seguridad.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../model/model_conexion.php';
Seguridad::iniciarSesion();

header('Content-Type: application/json; charset=utf-8');

$clave = 'consulta_publica|' . Seguridad::ipCliente();
if (Seguridad::esperaIntentos($clave, 30, 600) > 0) {
    Seguridad::responderError(429, 'Demasiadas consultas seguidas. Espere unos minutos e intente nuevamente.');
}
Seguridad::registrarIntento($clave, 600);

// "exp 2026 000006", "EXP-2026-000006" y "EXP2026000006" se tratan igual
$numero = trim(preg_replace('/[^A-Z0-9]+/', '-', strtoupper((string) ($_POST['numero'] ?? ''))), '-');
$dni = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));

if ($numero === '' || $dni === '') {
    Seguridad::responderError(422, 'Ingrese el N° de expediente (o código de seguimiento) y el DNI del remitente.');
}
if (strlen($dni) !== 8) {
    Seguridad::responderError(422, 'El DNI debe tener 8 dígitos.');
}

// Formatos aceptados
if (preg_match('/^D\d{7}$/', $numero)) {
    $campo = 'documento_id';
} elseif (preg_match('/^(?:EXP-?)?(\d{4})(?:-(\d{1,6})|(\d{6}))$/', $numero, $m)) {
    $m[2] = $m[2] !== '' ? $m[2] : $m[3];
    $campo = 'doc_expediente';
    $numero = sprintf('EXP-%s-%06d', $m[1], (int) $m[2]);
} else {
    Seguridad::responderError(422, 'Escriba el N° de expediente como EXP-2026-000123 o el código de seguimiento como D0000123.');
}

$pdo = (new conexionBD())->conexionPDO();
$consulta = $pdo->prepare(
    "SELECT d.documento_id, d.doc_expediente, d.doc_nrodocumento, d.doc_folio, d.doc_asunto, d.doc_estatus,
            d.doc_fecharegistro, d.dias_respuesta, d.doc_dniremitente,
            DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y %H:%i') AS fecha_registro,
            CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS remitente,
            td.tipodo_descripcion AS tipo, destino.area_nombre AS area_actual
       FROM documento d
       INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
       LEFT JOIN area destino ON destino.area_cod = d.area_destino
      WHERE d.$campo = ? AND d.doc_dniremitente = ?
      LIMIT 1"
);
$consulta->execute([$numero, $dni]);
$tramite = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$tramite) {
    // Mismo mensaje si no existe o si el DNI no coincide: no revela qué dato falló
    echo json_encode(['encontrado' => false, 'mensaje' => 'No encontramos un trámite con ese número y DNI. Verifique los datos del cargo de recepción.']);
    exit;
}

$filas = [$tramite];
Plazos::agregar($filas);
$tramite = $filas[0];

$consulta = $pdo->prepare(
    "SELECT DATE_FORMAT(m.mov_fecharegistro, '%d/%m/%Y %H:%i') AS fecha, m.mov_estatus AS estado,
            m.mov_descripcion AS descripcion,
            COALESCE(ao.area_nombre, 'MESA DE PARTES VIRTUAL') AS origen, ad.area_nombre AS destino,
            DATE_FORMAT(m.mov_recibido_fecha, '%d/%m/%Y %H:%i') AS recibido
       FROM movimiento m
       LEFT JOIN area ao ON ao.area_cod = m.area_origen_id
       LEFT JOIN area ad ON ad.area_cod = m.areadestino_id
      WHERE m.documento_id = ? AND m.mov_tipo = 'PRINCIPAL'
      ORDER BY m.mov_fecharegistro ASC, m.movimiento_id ASC"
);
$consulta->execute([$tramite['documento_id']]);
$movimientos = $consulta->fetchAll(PDO::FETCH_ASSOC);

$consulta = $pdo->prepare("SELECT COUNT(*) FROM documento_anexo WHERE documento_id = ?");
$consulta->execute([$tramite['documento_id']]);
$anexos = (int) $consulta->fetchColumn();

$textosEstado = [
    'PENDIENTE'  => 'Recibido, en espera de atención',
    'ACEPTADO'   => 'En atención',
    'FINALIZADO' => 'Atendido y finalizado',
    'RECHAZADO'  => 'Observado / rechazado',
];

echo json_encode([
    'encontrado' => true,
    'tramite' => [
        'codigo'         => $tramite['documento_id'],
        'expediente'     => $tramite['doc_expediente'],
        'tipo'           => $tramite['tipo'],
        'numero'         => $tramite['doc_nrodocumento'],
        'folios'         => (int) $tramite['doc_folio'],
        'asunto'         => $tramite['doc_asunto'],
        'remitente'      => $tramite['remitente'],
        'fecha_registro' => $tramite['fecha_registro'],
        'estado'         => $tramite['doc_estatus'],
        'estado_texto'   => $textosEstado[$tramite['doc_estatus']] ?? $tramite['doc_estatus'],
        'area_actual'    => $tramite['area_actual'],
        'plazo_dias'     => (int) $tramite['dias_respuesta'],
        'plazo_limite'   => $tramite['plazo_limite'],
        'plazo_restante' => $tramite['plazo_restante'],
        'plazo_semaforo' => $tramite['plazo_semaforo'],
        'anexos'         => $anexos,
    ],
    'movimientos' => $movimientos,
    'cargo' => 'view/MPDF/REPORTE/cargo_recepcion.php?codigo=' . rawurlencode($tramite['documento_id']) . '&dni=' . rawurlencode($dni),
    'hoja'  => 'view/MPDF/REPORTE/ficha_seguimiento_automatico.php?codigo=' . rawurlencode($tramite['documento_id']) . '&dni=' . rawurlencode($dni),
]);
