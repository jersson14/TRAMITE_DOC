<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
require __DIR__ . '/_acceso.php';
require __DIR__ . '/_datos.php';

// Hoja de envío con el recorrido completo del trámite, generada desde el sistema.
$i = $institucion;
$t = $tramite;

/** Fila rótulo/valor de las tablas de datos. */
function fila_dato(string $rotulo, $valor, bool $destacado = false): string
{
    $v = trim((string) $valor) === '' ? '<span class="vacio">—</span>' : pdf_e($valor);
    return '<tr><td class="rotulo">' . pdf_e($rotulo) . '</td><td class="valor' . ($destacado ? ' destacado' : '') . '">' . $v . '</td></tr>';
}

$representacion = trim((string) $t['doc_representacion']);
$filasRemitente = fila_dato('Remitente', $t['remitente'])
    . fila_dato('DNI', $t['doc_dniremitente'])
    . fila_dato('Celular', $t['doc_celularremitente'])
    . fila_dato('Correo', $t['doc_emailremitente'])
    . fila_dato('Dirección', $t['doc_direccionremitente']);
if ($representacion !== '' && stripos($representacion, 'propio') === false) {
    $filasRemitente .= fila_dato('En representación', $representacion);
    if (trim((string) $t['doc_empresa']) !== '') {
        $filasRemitente .= fila_dato('Entidad', $t['doc_empresa'] . ($t['doc_ruc'] ? ' (RUC ' . $t['doc_ruc'] . ')' : ''));
    }
}

$filasDocumento = fila_dato('N° de expediente', $t['doc_expediente'] ?: $t['documento_id'], true)
    . fila_dato('Código de seguimiento', $t['documento_id'])
    . fila_dato('N° de documento', $t['doc_nrodocumento'])
    . fila_dato('Tipo de documento', $t['tipo'])
    . fila_dato('Folios', $t['doc_folio'])
    . fila_dato('Registrado', pdf_fecha_larga($t['doc_fecharegistro']))
    . fila_dato('Archivos', 'Documento principal' . ($totalAnexos ? ' + ' . $totalAnexos . ($totalAnexos === 1 ? ' anexo' : ' anexos') : ''));

$filasRecorrido = '';
foreach ($movimientos as $n => $m) {
    $esCopia = ($m['mov_tipo'] ?? '') === 'COPIA';
    $esAtencion = ($m['mov_tipo'] ?? '') === 'ATENCION';
    $descripcion = preg_replace('/^(COPIA|ATENCIÓN) - /u', '', (string) $m['mov_descripcion']);
    $filasRecorrido .= '<tr' . ($esCopia ? ' class="copia"' : '') . '>'
        . '<td class="centro">' . ($n + 1) . '</td>'
        . '<td>' . pdf_e($m['origen']) . '<span class="flecha"> → </span><strong>' . pdf_e($m['destino']) . '</strong>'
        . ($esCopia ? '<br><span class="marca-copia">COPIA · para conocimiento</span>' : '')
        . ($esAtencion ? '<br><span class="marca-copia">ATENCIÓN · debe responder'
            . ($m['mov_plazo_dias'] ? ' en ' . (int) $m['mov_plazo_dias'] . ' días hábiles' : '')
            . ($m['mov_respuesta_fecha'] ? ' · respondió ' . pdf_e(pdf_fecha_corta($m['mov_respuesta_fecha'])) : '')
            . '</span>' : '') . '</td>'
        . '<td class="centro">' . pdf_e(pdf_fecha_corta($m['mov_fecharegistro'])) . '</td>'
        . '<td>' . pdf_e($m['mov_acciones']) . '</td>'
        . '<td>' . pdf_e($descripcion) . ((int) $m['anexos'] > 0 ? '<br><span class="anexos">' . (int) $m['anexos'] . ' anexo(s)</span>' : '') . '</td>'
        . '<td class="centro"><span class="estado" style="background:' . pdf_color_estado($m['mov_estatus']) . ';">' . pdf_e($m['mov_estatus']) . '</span>'
        . ($m['recibido_fecha']
            ? '<br><span class="anexos">Recibido ' . pdf_e(pdf_fecha_corta($m['recibido_fecha'])) . ($m['recibido_por'] ? '<br>' . pdf_e($m['recibido_por']) : '') . '</span>'
            : '')
        . '</td>'
        . '</tr>';
}
if ($filasRecorrido === '') {
    $filasRecorrido = '<tr><td colspan="6" class="centro vacio">Sin movimientos registrados.</td></tr>';
}

$html = '
<style>
    body { font-family: dejavusans, sans-serif; font-size: 8.5pt; color: #2D3748; }
    .cabecera { width: 100%; border-bottom: 0.7mm solid #1E3A5F; padding-bottom: 2.5mm; }
    .cabecera td { vertical-align: middle; }
    .logo { max-height: 17mm; max-width: 55mm; }
    .institucion { font-size: 12pt; font-weight: bold; color: #1E3A5F; text-align: right; }
    .titulo { font-size: 10pt; font-weight: bold; color: #2D3748; text-align: right; margin-top: 1mm; }
    .subtitulo { font-size: 7.5pt; color: #718096; text-align: right; }
    .bloque { margin-top: 4mm; }
    .bloque-titulo { background: #1E3A5F; color: #FFFFFF; font-size: 8pt; font-weight: bold; padding: 1.5mm 2.5mm; }
    .datos { width: 100%; border-collapse: collapse; }
    .datos td { padding: 1.4mm 2.5mm; border-bottom: 0.2mm solid #E2E8F0; vertical-align: top; }
    .rotulo { width: 34%; color: #718096; font-size: 7.5pt; }
    .valor { color: #1A202C; }
    .destacado { font-weight: bold; color: #1E3A5F; font-size: 9.5pt; }
    .vacio { color: #A0AEC0; }
    .recorrido { width: 100%; border-collapse: collapse; }
    .recorrido th { background: #EFF4F9; color: #1E3A5F; font-size: 7pt; text-align: left; padding: 1.8mm 2mm; border-bottom: 0.4mm solid #1E3A5F; }
    .recorrido td { font-size: 7.5pt; padding: 1.8mm 2mm; border-bottom: 0.2mm solid #E2E8F0; vertical-align: top; }
    .recorrido tr.copia td { background: #FAFBFC; color: #4A5568; }
    .centro { text-align: center; }
    .flecha { color: #A0AEC0; }
    .marca-copia { font-size: 6.5pt; color: #2C5282; font-weight: bold; }
    .anexos { font-size: 6.5pt; color: #718096; }
    .estado { color: #FFFFFF; font-size: 6.5pt; font-weight: bold; padding: 0.6mm 1.5mm; }
    .pie { margin-top: 5mm; padding-top: 2mm; border-top: 0.2mm solid #E2E8F0; font-size: 6.5pt; color: #718096; text-align: center; }
</style>

<table class="cabecera"><tr>
    <td style="width:40%;"><img class="logo" src="' . pdf_e($logoPdf) . '"></td>
    <td style="width:60%;">
        <div class="institucion">' . pdf_e($i['razon']) . '</div>
        <div class="titulo">HOJA DE ENVÍO DE TRÁMITE</div>
        <div class="subtitulo">Expediente ' . pdf_e($t['doc_expediente'] ?: $t['documento_id']) . ' · Estado actual: ' . pdf_e($t['doc_estatus']) . '</div>
    </td>
</tr></table>

<table style="width:100%;" class="bloque"><tr>
    <td style="width:49%; vertical-align:top;">
        <table class="datos"><tr><td colspan="2" class="bloque-titulo">DATOS DEL DOCUMENTO</td></tr>' . $filasDocumento . '</table>
    </td>
    <td style="width:2%;"></td>
    <td style="width:49%; vertical-align:top;">
        <table class="datos"><tr><td colspan="2" class="bloque-titulo">DATOS DEL REMITENTE</td></tr>' . $filasRemitente . '</table>
    </td>
</tr></table>

<div class="bloque">
    <div class="bloque-titulo">ÁREAS Y ASUNTO</div>
    <table class="datos">
        ' . fila_dato('Área de origen', $t['origen']) . '
        ' . fila_dato('Área de destino actual', $t['destino']) . '
        ' . fila_dato('Acciones solicitadas', $t['acciones']) . '
        ' . fila_dato('Asunto', $t['doc_asunto']) . '
        ' . fila_dato('Observaciones', $t['doc_observaciones']) . '
    </table>
</div>

<div class="bloque">
    <div class="bloque-titulo">RECORRIDO DEL TRÁMITE</div>
    <table class="recorrido">
        <thead><tr>
            <th style="width:5%;" class="centro">#</th>
            <th style="width:30%;">ORIGEN → DESTINO</th>
            <th style="width:14%;" class="centro">FECHA</th>
            <th style="width:13%;">ACCIONES</th>
            <th style="width:25%;">DESCRIPCIÓN</th>
            <th style="width:13%;" class="centro">ESTADO</th>
        </tr></thead>
        <tbody>' . $filasRecorrido . '</tbody>
    </table>
</div>

<div class="pie">
    <strong>' . pdf_e($i['razon']) . '</strong>'
    . ($i['direccion'] !== '' ? ' · ' . pdf_e($i['direccion']) : '')
    . ($i['telefono'] !== '' ? ' · Tel. ' . pdf_e($i['telefono']) : '') . '<br>
    Emitido el ' . pdf_e(pdf_fecha_larga(date('Y-m-d H:i:s'))) . '. Puede verificar este trámite en ' . pdf_e($urlConsulta) . '
</div>';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'Letter',
    'margin_top' => 10,
    'margin_bottom' => 10,
    'margin_left' => 12,
    'margin_right' => 12,
]);
$mpdf->SetTitle('Hoja de envío ' . ($t['doc_expediente'] ?: $t['documento_id']));
$mpdf->SetAuthor($i['razon']);
$mpdf->WriteHTML($html);
$mpdf->Output('Hoja_envio_' . $t['documento_id'] . '.pdf', 'I');
