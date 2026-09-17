<?php
/**
 * El recorrido de un trámite, para dibujarlo como diagrama de flujo.
 *
 * Devuelve el trámite y sus movimientos con los campos por nombre (nunca por
 * posición: los procedimientos cambian de columnas y eso ya rompió pantallas
 * antes), señalando para cada envío quién lo hizo, si dieron acuse y, en las
 * atenciones, si respondieron.
 *
 * Lo ve el administrador o un área por la que pasó el trámite, la misma regla
 * del historial.
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/Plazos.php';
require_once __DIR__ . '/../../lib/OrigenTramite.php';
require_once __DIR__ . '/../../model/model_conexion.php';
require_once __DIR__ . '/../../model/model_tramite.php';

header('Content-Type: application/json; charset=utf-8');

$documento = strtoupper(trim((string) ($_POST['documento'] ?? '')));
if ($documento === '') {
    Seguridad::responderError(422, 'Indique el trámite a mostrar.');
}

if (!Seguridad::esAdmin() && !(new Modelo_Tramite())->Area_Puede_Ver($documento, Seguridad::areaId())) {
    Seguridad::responderError(403, 'Ese trámite no pasó por su área.');
}

$pdo = (new conexionBD())->conexionPDO();

// doc_procedencia llegó con la migración 021; se comprueba para no fallar en una
// instalación que todavía no la tenga.
$hayProcedencia = (bool) $pdo->query(
    "SELECT COUNT(*) FROM information_schema.COLUMNS
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento' AND COLUMN_NAME = 'doc_procedencia'"
)->fetchColumn();

$consulta = $pdo->prepare(
    "SELECT d.documento_id, d.doc_expediente, d.doc_nrodocumento, d.doc_asunto, d.doc_estatus,
            d.doc_folio, d.dias_respuesta, d.doc_fecharegistro, d.doc_fecharecepcion,
            d.doc_dniremitente, d.doc_ruc, d.doc_empresa, d.doc_representacion,
            " . ($hayProcedencia ? 'd.doc_procedencia,' : "'' AS doc_procedencia,") . "
            DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y %H:%i') AS fecha_registro,
            DATE_FORMAT(d.doc_fecharecepcion, '%d/%m/%Y %H:%i') AS fecha_presentado,
            CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS remitente,
            td.tipodo_descripcion AS tipo,
            origen.area_nombre AS area_registro, destino.area_nombre AS area_actual
       FROM documento d
       INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
       LEFT JOIN area origen ON origen.area_cod = d.area_id
       LEFT JOIN area destino ON destino.area_cod = d.area_destino
      WHERE d.documento_id = ?
      LIMIT 1"
);
$consulta->execute([$documento]);
$tramite = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$tramite) {
    echo json_encode(['encontrado' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

// El semáforo de plazos, el mismo de las bandejas
$filas = [$tramite];
Plazos::agregar($filas);
$tramite = $filas[0];

$consulta = $pdo->prepare(
    "SELECT m.movimiento_id, m.mov_tipo AS tipo, m.mov_estatus AS estado,
            m.mov_descripcion AS descripcion, m.mov_plazo_dias AS plazo,
            m.mov_respuesta AS respuesta,
            -- Un envío de un área a sí misma es el registro de un documento que
            -- llegó de fuera: el ciudadano lo presentó ahí, no lo derivó nadie.
            (m.area_origen_id = m.areadestino_id) AS es_recepcion,
            DATE_FORMAT(m.mov_fecharegistro, '%d/%m/%Y %H:%i') AS fecha,
            DATE_FORMAT(m.mov_recibido_fecha, '%d/%m/%Y %H:%i') AS recibido,
            DATE_FORMAT(m.mov_respuesta_fecha, '%d/%m/%Y %H:%i') AS respondido,
            COALESCE(ao.area_nombre, 'MESA DE PARTES VIRTUAL') AS origen,
            ad.area_nombre AS destino,
            COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario) AS persona
       FROM movimiento m
       LEFT JOIN area ao ON ao.area_cod = m.area_origen_id
       LEFT JOIN area ad ON ad.area_cod = m.areadestino_id
       LEFT JOIN usuario u ON u.usu_id = m.usuario_id
       LEFT JOIN empleado e ON e.empleado_id = u.empleado_id
      WHERE m.documento_id = ?
      ORDER BY m.movimiento_id"
);
$consulta->execute([$documento]);
$movimientos = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Dónde se recibió el documento. Un primer envío de un área a sí misma ES la
// recepción, no una derivación: mostrarlo como "MESA DE PARTES -> MESA DE PARTES"
// no decía nada. De dónde VIENE lo resuelve OrigenTramite más abajo.
$primerPrincipal = null;
foreach ($movimientos as $m) {
    if ($m['tipo'] === 'PRINCIPAL') {
        $primerPrincipal = $m;
        break;
    }
}
$esPortal = !empty($tramite['doc_fecharecepcion']);
$areaRecepcion = $primerPrincipal ? $primerPrincipal['origen'] : ($tramite['area_registro'] ?: 'MESA DE PARTES');

// El rótulo del punto de partida (interno con su oficina, entidad externa o
// ciudadano) lo resuelve OrigenTramite, compartido con el detalle de movimientos
// y el tablero, para que las tres pantallas digan lo mismo.
$origen = OrigenTramite::describir(OrigenTramite::consultar($pdo, $documento));

echo json_encode([
    'encontrado' => true,
    'tramite' => [
        'documento_id'  => $tramite['documento_id'],
        'expediente'    => $tramite['doc_expediente'] ?: $tramite['documento_id'],
        'nro_documento' => $tramite['doc_nrodocumento'],
        'asunto'        => $tramite['doc_asunto'],
        'estado'        => $tramite['doc_estatus'],
        'folios'        => $tramite['doc_folio'],
        'tipo'          => $tramite['tipo'],
        'remitente'     => trim((string) $tramite['remitente']),
        'dni'           => $tramite['doc_dniremitente'],
        'fecha_registro' => $tramite['fecha_registro'],
        'fecha_presentado' => $tramite['fecha_presentado'],
        // El documento llega de una persona: por el portal o en el mostrador
        'procedencia'   => $esPortal ? 'PORTAL' : 'PRESENCIAL',
        // Cómo se rotula el punto de partida del recorrido: interno (con su
        // oficina), entidad externa (con su razón social) o ciudadano.
        'origen'        => $origen,
        'area_recepcion' => $areaRecepcion,
        'area_registro' => $tramite['area_registro'] ?: 'MESA DE PARTES',
        'area_actual'   => $tramite['area_actual'],
        'dias_respuesta' => $tramite['dias_respuesta'],
        'plazo_limite'   => $tramite['plazo_limite'] ?? null,
        'plazo_restante' => $tramite['plazo_restante'] ?? null,
        'plazo_semaforo' => $tramite['plazo_semaforo'] ?? null,
    ],
    'movimientos' => $movimientos,
], JSON_UNESCAPED_UNICODE);
