<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
require __DIR__ . '/_acceso.php';
require __DIR__ . '/_datos.php';

// Ticket del trámite: lo que el ciudadano se lleva para hacer seguimiento.
$i = $institucion;
$t = $tramite;

$contacto = array_filter([
    $i['telefono'] !== '' ? 'Tel. ' . pdf_e($i['telefono']) : '',
    $i['email'] !== '' ? pdf_e(strtolower($i['email'])) : '',
]);

$filas = [
    ['Remitente', $t['remitente']],
    ['Área de origen', $t['origen']],
    ['Área de destino', $t['destino']],
    ['Tipo de documento', $t['tipo']],
    ['N° de documento', $t['doc_nrodocumento']],
    ['Folios', $t['doc_folio']],
    ['Registrado', pdf_fecha_larga($t['doc_fecharegistro'])],
];
if ($totalAnexos > 0) {
    $filas[] = ['Archivos', 'Documento principal + ' . $totalAnexos . ($totalAnexos === 1 ? ' anexo' : ' anexos')];
}

$filas[] = ['Asunto', $t['doc_asunto']];

// Rótulo y valor en la misma fila: así el ticket cabe en una sola hoja
$htmlFilas = '<table class="datos">';
foreach ($filas as $f) {
    $htmlFilas .= '<tr><td class="etiqueta">' . pdf_e($f[0]) . '</td>'
        . '<td class="valor">' . pdf_e($f[1]) . '</td></tr>';
}
$htmlFilas .= '</table>';

$html = '
<style>
    @page { margin: 5mm 6mm 5mm 6mm; }
    body { font-family: dejavusans, sans-serif; font-size: 8.5pt; color: #2D3748; }
    .centro { text-align: center; }
    .logo { max-height: 15mm; max-width: 58mm; }
    .institucion { font-size: 10pt; font-weight: bold; color: #1E3A5F; margin-top: 2mm; }
    .subtitulo { font-size: 7.5pt; color: #718096; margin-top: 0.5mm; }
    .linea { border-top: 0.6mm solid #1E3A5F; margin: 3mm 0; }
    .expediente { background: #1E3A5F; color: #FFFFFF; text-align: center; padding: 3mm 2mm; border-radius: 2mm; }
    .expediente .rotulo { font-size: 6.5pt; letter-spacing: 0.4pt; }
    .expediente .numero { font-size: 14pt; font-weight: bold; margin-top: 0.5mm; }
    .codigo { border: 0.3mm solid #CBD5E0; text-align: center; padding: 2mm; margin-top: 2mm; border-radius: 2mm; }
    .codigo .rotulo { font-size: 6.5pt; color: #718096; }
    .codigo .numero { font-size: 11pt; font-weight: bold; color: #1E3A5F; }
    .codigo .dni { font-size: 7.5pt; color: #4A5568; margin-top: 0.5mm; }
    .datos { width: 100%; border-collapse: collapse; margin-top: 2.5mm; }
    .datos td { padding: 0.9mm 0; vertical-align: top; border-bottom: 0.2mm solid #EDF2F7; }
    .etiqueta { width: 34%; font-size: 6.5pt; color: #718096; padding-right: 1.5mm; }
    .valor { font-size: 8pt; color: #1A202C; }
    .qr { text-align: center; margin-top: 1mm; }
    .qr-texto { font-size: 7pt; color: #4A5568; margin-top: 1mm; }
    .corte { border-top: 0.3mm dashed #CBD5E0; margin: 2.5mm 0; }
    .pie { text-align: center; font-size: 6.5pt; color: #718096; }
</style>

<div class="centro">
    <img class="logo" src="' . pdf_e($logoPdf) . '">
    <div class="institucion">' . pdf_e($i['razon']) . '</div>
    <div class="subtitulo">Ticket de trámite documentario</div>
</div>

<div class="linea"></div>

<div class="expediente">
    <div class="rotulo">N° DE EXPEDIENTE</div>
    <div class="numero">' . pdf_e($t['doc_expediente'] ?: $t['documento_id']) . '</div>
</div>

<div class="codigo">
    <div class="rotulo">CÓDIGO DE SEGUIMIENTO</div>
    <div class="numero">' . pdf_e($t['documento_id']) . '</div>
    <div class="dni">DNI del remitente: ' . pdf_e($t['doc_dniremitente']) . '</div>
</div>

' . $htmlFilas . '


<div class="corte"></div>

<div class="qr">
    <barcode code="' . pdf_e($urlConsulta) . '" type="QR" size="0.85" error="M" disableborder="1" />
    <div class="qr-texto">Escanee para ver el estado y el recorrido<br>de su trámite. No comparta este código.</div>
</div>

<div class="corte"></div>

<div class="pie">
    ' . ($i['direccion'] !== '' ? pdf_e($i['direccion']) . '<br>' : '') . implode(' · ', $contacto) . '<br>
    Emitido el ' . pdf_e(pdf_fecha_larga(date('Y-m-d H:i:s'))) . '. Conserve este ticket.
</div>';

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [80, 210]]);
$mpdf->SetTitle('Ticket ' . ($t['doc_expediente'] ?: $t['documento_id']));
$mpdf->SetAuthor($i['razon']);
$mpdf->WriteHTML($html);
$mpdf->Output('Ticket_' . $t['documento_id'] . '.pdf', 'I');
