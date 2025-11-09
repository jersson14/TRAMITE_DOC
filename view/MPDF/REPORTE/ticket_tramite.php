<?php
require_once  __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
$codigo = $_GET['codigo'];
$query ="SELECT
empresa.empresa_id, 
empresa.emp_razon, 
empresa.emp_email, 
empresa.emp_cod, 
empresa.emp_telefono, 
empresa.emp_direccion, 
empresa.emp_logo
FROM
empresa";
date_default_timezone_set('America/Lima');
$html="";
$resultado = $mysqli->query($query);
$query2="SELECT
documento.documento_id, 
documento.doc_dniremitente, 
CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
documento.doc_nombreremitente, 
documento.doc_apepatremitente, 
documento.doc_apematremitente, 
documento.tipodocumento_id, 
tipo_documento.tipodo_descripcion, 
documento.doc_estatus, 
documento.doc_nrodocumento, 
documento.doc_celularremitente, 
documento.doc_emailremitente, 
documento.doc_direccionremitente, 
documento.doc_representacion, 
documento.doc_ruc, 
documento.doc_empresa, 
documento.doc_folio, 
documento.doc_archivo, 
documento.doc_asunto, 
documento.doc_fecharegistro,
documento.area_origen, 
documento.area_destino, 
documento.area_id,
documento.dias_pasados,
dias_respuesta,
acciones,
doc_observaciones,
dias_respuesta,
origen.area_nombre as origen, 
destino.area_nombre as destino
FROM
documento
INNER JOIN
tipo_documento
ON 
    documento.tipodocumento_id = tipo_documento.tipodocumento_id
INNER JOIN
area AS origen
ON 
    documento.area_origen = origen.area_cod
INNER JOIN
area AS destino
ON 
    documento.area_destino = destino.area_cod
WHERE
documento.documento_id = '".$codigo."'";
$resultado2 = $mysqli->query($query2);
$razon      = "";
$telefono   = "";
$email      = "";
$codigo     = "";
$logo       = "";
$direccion  = "";
while($row2 = $resultado->fetch_assoc()){
    $razon      = $row2['emp_razon'];
    $cod_cel    = $row2['emp_cod'];
    $telefono   = $row2['emp_telefono'];
    $email      = utf8_encode($row2['emp_email']);
    $logo       = $row2['emp_logo'];
    $direccion  = $row2['emp_direccion'];
}

while($row = $resultado2->fetch_assoc()){
    $html.='
    <style>
        @page{
            margin: 8mm;
            margin-header: 0mm;
            margin-footer: 0mm;
            odd-footer-name: html_myFooter1;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }
        
        .header img {
            border: 2px solid #667eea;
            padding: 5px;
            border-radius: 8px;
            width: 90%;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: bold;
            color: #667eea;
            text-transform: uppercase;
            margin-top: 8px;
            letter-spacing: 0.5px;
        }
        
        .ticket-title {
            font-size: 9px;
            color: #718096;
            font-style: italic;
            margin-top: 3px;
        }
        
        .tracking-code {
            background: #667eea;
            color: white;
            padding: 8px;
            text-align: center;
            border-radius: 5px;
            margin: 10px 0;
        }
        
        .tracking-code .label {
            font-size: 8px;
            font-weight: normal;
            margin-bottom: 3px;
        }
        
        .tracking-code .code {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .info-section {
            margin: 8px 0;
            padding: 6px;
            background: #f7fafc;
            border-left: 3px solid #667eea;
            border-radius: 3px;
        }
        
        .info-row {
            margin: 5px 0;
            font-size: 9px;
            line-height: 1.4;
        }
        
        .info-label {
            font-weight: bold;
            color: #4a5568;
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .info-value {
            color: #2d3748;
            margin-top: 2px;
        }
        
        .divider {
            border-top: 1px dashed #cbd5e0;
            margin: 10px 0;
        }
        
        .qr-section {
            text-align: center;
            margin: 12px 0;
            padding: 10px;
            background: #f7fafc;
            border-radius: 5px;
        }
        
        .qr-label {
            font-size: 8px;
            color: #718096;
            margin-top: 5px;
            font-style: italic;
        }
        
        .footer-info {
            text-align: center;
            font-size: 8px;
            color: #718096;
            line-height: 1.5;
            margin-top: 5px;
        }
        
        .footer-info strong {
            color: #4a5568;
        }
    </style>
    
    <!-- Header -->
    <div class="header">
        <img src="../../../img/emusap.jpg" alt="Logo">
        <div class="company-name">EPS EMUSAP ABANCAY SA</div>
        <div class="ticket-title">Ticket de Trámite Documentario</div>
    </div>
    
    <!-- Tracking Code -->
    <div class="tracking-code">
        <div class="label">CÓDIGO DE SEGUIMIENTO</div>
        <div class="code">'.$row['documento_id'].'</div>
    </div>
    
    <!-- DNI Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">DNI Remitente</span>
            <div class="info-value"><strong>'.$row['doc_dniremitente'].'</strong></div>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <!-- Document Details -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Remitente</span>
            <div class="info-value">'.utf8_encode($row['REMITENTE']).'</div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Oficina Destino</span>
            <div class="info-value">'.$row['origen'].'</div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Nro. de Registro</span>
            <div class="info-value">'.$row['doc_nrodocumento'].'</div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Fecha y Hora</span>
            <div class="info-value">'.date('d/m/Y - H:i:s', strtotime($row['doc_fecharegistro'])).'</div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Tipo de Documento</span>
            <div class="info-value">'.utf8_encode($row['tipodo_descripcion']).'</div>
        </div>
        
        <div class="info-row">
            <span class="info-label">Asunto</span>
            <div class="info-value">'.utf8_encode($row['doc_asunto']).'</div>
        </div>
    </div>
    
    <div class="divider"></div>
    
    <!-- QR Code Section -->
    <div class="qr-section">
        <barcode code="http://localhost:8080/SISTRAMITEDOC/seguimiento.php" type="QR" class="barcode" size="1.0" disableborder="1"/>
        <div class="qr-label">Escanea para seguimiento en línea</div>
    </div>
    
    <div class="divider"></div>
    
    <htmlpagefooter name="myFooter1">
        <div style="margin-top: 15px;"></div>
        <div class="footer-info">
            <strong>Contacto</strong><br>
            Tel: '.$telefono.'<br>
            Email: '.utf8_encode($email).'<br>
            '.utf8_encode($direccion).'
        </div>
        <div class="footer-info" style="margin-top: 8px; font-size: 7px; color: #a0aec0;">
            Documento generado el '.date('d/m/Y H:i:s').'<br>
            Conserve este ticket para seguimiento
        </div>
    </htmlpagefooter>
    ';
}

$mpdf = new \Mpdf\Mpdf(
    ['mode' => 'UTF-8','format' => [80,200]]
);
$mpdf->WriteHTML($html);
$mpdf->Output();
?>