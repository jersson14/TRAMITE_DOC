<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
require __DIR__ . '/_acceso.php';
require __DIR__ . '/_datos.php';

// Cargo de recepción (A4): constancia de lo que la entidad recibió, cuándo y con qué archivos.
// La huella SHA-256 de cada archivo permite demostrar después que no fue cambiado.
$i = $institucion;
$t = $tramite;

/** Tamaño legible: 1,2 MB */
function cargo_tamano(int $bytes): string
{
    if ($bytes <= 0) {
        return '—';
    }
    if ($bytes < 1048576) {
        return max(1, (int) round($bytes / 1024)) . ' KB';
    }
    return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
}

/** Tamaño y huella del archivo guardado, solo dentro de la carpeta del sistema. */
function cargo_archivo(string $raiz, string $ruta): array
{
    $completa = realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta));
    if (!$completa || strpos($completa, $raiz . DIRECTORY_SEPARATOR) !== 0 || !is_file($completa)) {
        return ['bytes' => 0, 'huella' => ''];
    }
    return ['bytes' => filesize($completa), 'huella' => hash_file('sha256', $completa)];
}

$archivos = [];
$consulta = $mysqli->prepare('SELECT doc_archivo FROM documento WHERE documento_id = ?');
$consulta->bind_param('s', $codigo);
$consulta->execute();
$principal = (string) ($consulta->get_result()->fetch_row()[0] ?? '');
if ($principal !== '') {
    $archivos[] = ['nombre' => 'Documento principal (PDF)', 'tipo' => 'Principal'] + cargo_archivo($raizSistema, $principal);
}
// Solo los anexos recibidos al registrar (vinculados al primer envío), no los que se agregaron después
$consulta = $mysqli->prepare(
    'SELECT a.anexo_nombre, a.anexo_ruta
       FROM documento_anexo a
       INNER JOIN movimiento_anexo ma ON ma.anexo_id = a.anexo_id
      WHERE a.documento_id = ?
        AND ma.movimiento_id = (SELECT MIN(m.movimiento_id) FROM movimiento m WHERE m.documento_id = ?)
      ORDER BY a.anexo_id'
);
$consulta->bind_param('ss', $codigo, $codigo);
$consulta->execute();
foreach ($consulta->get_result()->fetch_all(MYSQLI_ASSOC) as $a) {
    $archivos[] = ['nombre' => $a['anexo_nombre'], 'tipo' => 'Anexo'] + cargo_archivo($raizSistema, $a['anexo_ruta']);
}

$filasArchivos = '';
foreach ($archivos as $n => $a) {
    $filasArchivos .= '<tr>'
        . '<td class="num">' . ($n + 1) . '</td>'
        . '<td>' . pdf_e($a['nombre']) . '<div class="tipo">' . pdf_e($a['tipo']) . '</div></td>'
        . '<td class="der">' . pdf_e(cargo_tamano((int) $a['bytes'])) . '</td>'
        . '<td class="huella">' . ($a['huella'] !== '' ? pdf_e(implode(' ', str_split($a['huella'], 16))) : 'Archivo no disponible') . '</td>'
        . '</tr>';
}
if ($filasArchivos === '') {
    $filasArchivos = '<tr><td colspan="4" class="vacio">No se registraron archivos.</td></tr>';
}

$representacion = $t['doc_representacion'];
if ($t['doc_ruc'] !== '' || $t['doc_empresa'] !== '') {
    $representacion .= ' · ' . trim($t['doc_empresa'] . ($t['doc_ruc'] !== '' ? ' (RUC ' . $t['doc_ruc'] . ')' : ''));
}

$fila = function (string $rotulo, $valor): string {
    return '<tr><td class="rotulo">' . pdf_e($rotulo) . '</td><td class="valor">' . pdf_e($valor !== '' && $valor !== null ? $valor : '—') . '</td></tr>';
};

$contacto = array_filter([
    $i['telefono'] !== '' ? 'Tel. ' . $i['telefono'] : '',
    $i['email'] !== '' ? strtolower($i['email']) : '',
]);

$html = '
<style>
    @page { margin: 14mm 16mm 16mm 16mm; }
    body { font-family: dejavusans, sans-serif; font-size: 9pt; color: #2D3748; }
    .cabecera td { vertical-align: middle; }
    .logo { max-height: 18mm; max-width: 70mm; }
    .institucion { font-size: 11pt; font-weight: bold; color: #1E3A5F; text-align: right; }
    .canal { font-size: 8pt; color: #718096; text-align: right; }
    .titulo { margin-top: 6mm; padding: 3mm 0; border-top: 0.7mm solid #1E3A5F; border-bottom: 0.3mm solid #CBD5E0; }
    .titulo h1 { font-size: 15pt; color: #1E3A5F; margin: 0; }
    .titulo .sub { font-size: 8.5pt; color: #4A5568; }
    .claves { width: 100%; margin-top: 4mm; border-collapse: separate; border-spacing: 0; }
    .clave { border: 0.3mm solid #CBD5E0; padding: 3mm; text-align: center; }
    .clave.fuerte { background: #1E3A5F; color: #FFFFFF; border-color: #1E3A5F; }
    .clave .r { font-size: 6.5pt; letter-spacing: 0.4pt; }
    .clave .v { font-size: 13pt; font-weight: bold; margin-top: 1mm; }
    .clave .v2 { font-size: 10.5pt; font-weight: bold; color: #1E3A5F; margin-top: 1mm; }
    h2 { font-size: 9.5pt; color: #1E3A5F; margin: 6mm 0 1.5mm 0; text-transform: uppercase; letter-spacing: 0.3pt; }
    .datos { width: 100%; border-collapse: collapse; }
    .datos td { padding: 1.4mm 0; border-bottom: 0.2mm solid #EDF2F7; vertical-align: top; }
    .rotulo { width: 32%; font-size: 8pt; color: #718096; }
    .valor { font-size: 9pt; color: #1A202C; }
    .archivos { width: 100%; border-collapse: collapse; }
    .archivos th { background: #EFF4F9; color: #1E3A5F; font-size: 7.5pt; text-align: left; padding: 1.8mm; }
    .archivos td { border-bottom: 0.2mm solid #E2E8F0; padding: 1.8mm; font-size: 8.5pt; vertical-align: top; }
    .archivos .num { width: 6%; color: #718096; }
    .archivos .der { width: 12%; text-align: right; white-space: nowrap; }
    .archivos .tipo { font-size: 7pt; color: #718096; }
    .archivos .huella { width: 36%; font-family: dejavusansmono, monospace; font-size: 6.5pt; color: #4A5568; }
    .archivos .vacio { color: #718096; text-align: center; }
    .nota { margin-top: 5mm; padding: 3mm; background: #F7FAFC; border-left: 0.8mm solid #2C5282; font-size: 7.8pt; color: #4A5568; }
    .final td { vertical-align: top; }
    .qr-texto { font-size: 7.5pt; color: #4A5568; }
</style>

<table class="cabecera" width="100%"><tr>
    <td width="45%"><img class="logo" src="' . pdf_e($logoPdf) . '"></td>
    <td width="55%">
        <div class="institucion">' . pdf_e($i['razon']) . '</div>
        <div class="canal">Mesa de Partes Virtual</div>
    </td>
</tr></table>

<div class="titulo">
    <h1>CARGO DE RECEPCIÓN</h1>
    <div class="sub">Constancia de que la entidad recibió el documento y los archivos detallados.</div>
</div>

<table class="claves"><tr>
    <td class="clave fuerte" width="38%"><span style="font-size: 6.5pt;">N° DE EXPEDIENTE</span><br><span style="font-size: 14pt; font-weight: bold;">' . pdf_e($t['doc_expediente'] ?: $t['documento_id']) . '</span></td>
    <td width="2%"></td>
    <td class="clave" width="28%"><span style="font-size: 6.5pt; color: #718096;">CÓDIGO DE SEGUIMIENTO</span><br><span style="font-size: 11pt; font-weight: bold; color: #1E3A5F;">' . pdf_e($t['documento_id']) . '</span></td>
    <td width="2%"></td>
    <td class="clave" width="30%"><span style="font-size: 6.5pt; color: #718096;">RECIBIDO EL</span><br><span style="font-size: 11pt; font-weight: bold; color: #1E3A5F;">' . pdf_e(pdf_fecha_corta($t['doc_fecharegistro'])) . '</span></td>
</tr></table>

<h2>Remitente</h2>
<table class="datos">'
    . $fila('Nombre', $t['remitente'])
    . $fila('DNI', $t['doc_dniremitente'])
    . $fila('Presenta', $representacion)
    . $fila('Correo electrónico', strtolower($t['doc_emailremitente']))
    . $fila('Celular', $t['doc_celularremitente'])
. '</table>

<h2>Documento</h2>
<table class="datos">'
    . $fila('Tipo', $t['tipo'])
    . $fila('N° de documento', $t['doc_nrodocumento'])
    . $fila('Folios', $t['doc_folio'])
    . $fila('Asunto', $t['doc_asunto'])
    . $fila('Área de destino', $t['destino'])
. '</table>

<h2>Archivos recibidos</h2>
<table class="archivos">
    <tr><th>N°</th><th>Archivo</th><th class="der">Tamaño</th><th>Huella SHA-256</th></tr>
    ' . $filasArchivos . '
</table>

<div class="nota">
    La huella SHA-256 identifica de forma única cada archivo tal como fue recibido: si el archivo se modifica, la huella cambia.
    La recepción no implica conformidad con el contenido; el documento será evaluado por el área competente.
</div>

<table class="final" width="100%" style="margin-top: 6mm;"><tr>
    <td width="24%"><barcode code="' . pdf_e($urlConsulta) . '" type="QR" size="1" error="M" disableborder="1" /></td>
    <td width="76%" class="qr-texto">
        <b>Consulte el estado de su trámite</b> escaneando el código QR o en la opción «Seguimiento de Trámite»
        de la Mesa de Partes Virtual, con su N° de expediente y el DNI del remitente.<br><br>
        ' . ($i['direccion'] !== '' ? pdf_e($i['direccion']) . '<br>' : '') . pdf_e(implode(' · ', $contacto)) . '<br>
        Emitido el ' . pdf_e(pdf_fecha_larga(date('Y-m-d H:i:s'))) . '.
    </td>
</tr></table>';

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->SetTitle('Cargo de recepción ' . ($t['doc_expediente'] ?: $t['documento_id']));
$mpdf->SetAuthor($i['razon']);
$mpdf->WriteHTML($html);
$descargar = isset($_GET['descargar']);
$mpdf->Output('Cargo_' . ($t['doc_expediente'] ?: $t['documento_id']) . '.pdf', $descargar ? 'D' : 'I');
