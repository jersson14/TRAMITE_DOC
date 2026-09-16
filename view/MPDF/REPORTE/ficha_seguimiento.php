<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
require __DIR__ . '/_acceso.php';
require __DIR__ . '/_datos.php';

// Hoja de envío para llenar a mano: acompaña al documento físico y cada área
// anota destino, acción, fecha, responsable y firma.
$i = $institucion;
$t = $tramite;

/** Celda rótulo + valor de la cabecera de datos. */
function dato(string $rotulo, $valor): string
{
    $v = trim((string) $valor) === '' ? '—' : pdf_e($valor);
    return '<td class="dato"><span class="rotulo">' . pdf_e($rotulo) . '</span><br>' . $v . '</td>';
}

// Cuadrícula vacía (antes era una imagen pegada; como tabla se imprime nítida)
$filasCuadricula = str_repeat(
    '<tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>',
    8
);

// Leyenda de acciones, en tres columnas como en el formato original
$acciones = ['Acción', 'Tramitar', 'Revisar', 'V°B°', 'Coordinar', 'Conocimiento', 'Proyecto',
             'Consolidar', 'Seguimiento', 'Dar respuesta', 'Difundir', 'Archivo', 'Evaluar',
             'Preparar respuesta', 'Opinión', 'Corregir', 'Informe', 'Asistir'];
$columnas = ['', '', ''];
foreach ($acciones as $n => $accion) {
    $columnas[min(2, intdiv($n, 7))] .= '<div>' . ($n + 1) . '. ' . pdf_e($accion) . '</div>';
}

$html = '
<style>
    body { font-family: dejavusans, sans-serif; font-size: 8.5pt; color: #1A202C; }
    .cabecera { width: 100%; border-bottom: 0.7mm solid #1E3A5F; padding-bottom: 2mm; }
    .cabecera td { vertical-align: middle; }
    .logo { max-height: 15mm; max-width: 50mm; }
    .institucion { font-size: 11pt; font-weight: bold; color: #1E3A5F; text-align: right; }
    .titulo { font-size: 10pt; font-weight: bold; text-align: right; margin-top: 0.8mm; }
    .expediente { font-size: 12pt; font-weight: bold; color: #1E3A5F; text-align: right; margin-top: 0.8mm; }
    .datos { width: 100%; border-collapse: collapse; margin-top: 3mm; }
    .dato { padding: 1.2mm 1.5mm; border-bottom: 0.2mm solid #E2E8F0; vertical-align: top; font-size: 8pt; }
    .rotulo { font-size: 6.5pt; color: #718096; }
    .cuadricula { width: 100%; border-collapse: collapse; margin-top: 4mm; }
    .cuadricula th { border: 0.4mm solid #1A202C; background: #EFF4F9; font-size: 7.5pt; padding: 1.5mm; }
    .cuadricula td { border: 0.4mm solid #1A202C; height: 13mm; }
    .leyenda { width: 100%; margin-top: 3mm; font-size: 7.5pt; }
    .leyenda td { vertical-align: top; width: 33%; }
    .leyenda-titulo { font-size: 7pt; font-weight: bold; color: #4A5568; margin-top: 3mm; }
    .pie { margin-top: 3mm; font-size: 6.5pt; color: #718096; text-align: center; }
</style>

<table class="cabecera"><tr>
    <td style="width:40%;"><img class="logo" src="' . pdf_e($logoPdf) . '"></td>
    <td style="width:60%;">
        <div class="institucion">' . pdf_e($i['razon']) . '</div>
        <div class="titulo">HOJA DE ENVÍO DE TRÁMITE</div>
        <div class="expediente">' . pdf_e($t['doc_expediente'] ?: $t['documento_id']) . '</div>
    </td>
</tr></table>

<table class="datos">
    <tr>' . dato('Remitente', $t['remitente']) . dato('Código de seguimiento', $t['documento_id']) . dato('Registrado', pdf_fecha_corta($t['doc_fecharegistro'])) . '</tr>
    <tr>' . dato('Área de origen', $t['origen']) . dato('Área de destino', $t['destino']) . dato('Tipo de documento', $t['tipo']) . '</tr>
    <tr>' . dato('N° de documento', $t['doc_nrodocumento']) . dato('Folios', $t['doc_folio']) . dato('Acciones solicitadas', $t['acciones']) . '</tr>
    <tr><td class="dato" colspan="3"><span class="rotulo">Asunto</span><br>' . pdf_e($t['doc_asunto']) . '</td></tr>
    <tr><td class="dato" colspan="3"><span class="rotulo">Observaciones</span><br>' . (trim((string) $t['doc_observaciones']) === '' ? '—' : pdf_e($t['doc_observaciones'])) . '</td></tr>
</table>

<table class="cuadricula">
    <thead><tr>
        <th style="width:20%;">DESTINO</th>
        <th style="width:11%;">ACCIONES</th>
        <th style="width:11%;">FECHA</th>
        <th style="width:21%;">RESPONSABLE</th>
        <th style="width:15%;">FIRMA</th>
        <th style="width:22%;">OBSERVACIONES</th>
    </tr></thead>
    <tbody>' . $filasCuadricula . '</tbody>
</table>

<div class="leyenda-titulo">ACCIONES (anote el número en la columna ACCIONES)</div>
<table class="leyenda"><tr>
    <td>' . $columnas[0] . '</td><td>' . $columnas[1] . '</td><td>' . $columnas[2] . '</td>
</tr></table>

<div class="pie">' . pdf_e($i['razon']) . ' · Emitido el ' . pdf_e(pdf_fecha_corta(date('Y-m-d H:i:s'))) . '</div>';

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => [190, 245],
    'margin_top' => 8,
    'margin_bottom' => 8,
    'margin_left' => 10,
    'margin_right' => 10,
]);
$mpdf->SetTitle('Hoja de envío ' . ($t['doc_expediente'] ?: $t['documento_id']));
$mpdf->SetAuthor($i['razon']);
$mpdf->WriteHTML($html);
$mpdf->Output('Hoja_envio_manual_' . $t['documento_id'] . '.pdf', 'I');
