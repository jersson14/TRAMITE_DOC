<?php
require_once  __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
$codigo = $_GET['codigo'];
$html = "";

$consulta = "SELECT
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
documento.dias_respuesta,
documento.acciones,
documento.doc_observaciones,
documento.dias_respuesta,
origen.area_nombre AS origen,
destino.area_nombre AS destino,
empresa.emp_logo
FROM
documento
INNER JOIN tipo_documento ON documento.tipodocumento_id = tipo_documento.tipodocumento_id
INNER JOIN area AS origen ON documento.area_origen = origen.area_cod
INNER JOIN area AS destino ON documento.area_destino = destino.area_cod ,
empresa
WHERE
documento_id = '".$codigo."'";

$resultado = $mysqli->query($consulta);

while($filas = $resultado->fetch_assoc()) {
    $fecha = setlocale(LC_TIME, "spanish");
    $fecharegistro = date("d-m-Y - h:i:sa", strtotime($filas['doc_fecharegistro']));
    
    $html .= '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: "Arial", "Helvetica", sans-serif;
                font-size: 9px;
                color: #333;
                line-height: 1.3;
            }
            
            .container {
                padding: 15px;
            }
            
            /* Header Section */
            .header {
                text-align: center;
                margin-bottom: 12px;
                padding-bottom: 8px;
                border-bottom: 2px solid #667eea;
            }
            
            .company-name {
                font-size: 16px;
                font-weight: bold;
                color: #667eea;
                margin-bottom: 3px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .document-title {
                font-size: 12px;
                font-weight: bold;
                color: #2d3748;
                text-transform: uppercase;
                margin: 3px 0;
            }
            
            .document-subtitle {
                font-size: 8px;
                color: #718096;
                font-style: italic;
            }
            
            /* Info Grid */
            .info-section {
                margin-bottom: 10px;
            }
            
            .section-title {
                background: #667eea;
                color: white;
                padding: 4px 8px;
                font-weight: bold;
                font-size: 9px;
                text-transform: uppercase;
                margin-bottom: 5px;
            }
            
            .info-grid {
                display: table;
                width: 100%;
                border: 1px solid #e2e8f0;
            }
            
            .info-row {
                display: table-row;
            }
            
            .info-cell {
                display: table-cell;
                padding: 3px 6px;
                border-bottom: 1px solid #f0f0f0;
                vertical-align: top;
            }
            
            .info-cell:first-child {
                width: 22%;
                background: #f7fafc;
                font-weight: bold;
                color: #4a5568;
            }
            
            .info-cell-full {
                display: table-cell;
                padding: 3px 6px;
                border-bottom: 1px solid #f0f0f0;
                vertical-align: top;
            }
            
            .two-column {
                display: table;
                width: 100%;
            }
            
            .column {
                display: table-cell;
                width: 50%;
                padding-right: 5px;
            }
            
            .column:last-child {
                padding-right: 0;
                padding-left: 5px;
            }
            
            /* Table Styles */
            .tracking-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 5px;
                border: 1px solid #e2e8f0;
            }
            
            .tracking-table thead {
                background: #667eea;
                color: white;
            }
            
            .tracking-table th {
                padding: 4px 3px;
                text-align: center;
                font-weight: bold;
                font-size: 8px;
                text-transform: uppercase;
                border: 1px solid #5568d3;
            }
            
            .tracking-table tbody tr {
                border-bottom: 1px solid #e2e8f0;
            }
            
            .tracking-table tbody tr:nth-child(even) {
                background: #f7fafc;
            }
            
            .tracking-table td {
                padding: 3px 3px;
                text-align: center;
                font-size: 7.5px;
                border: 1px solid #e2e8f0;
                vertical-align: middle;
            }
            
            .status-badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 3px;
                font-weight: bold;
                font-size: 7px;
                text-transform: uppercase;
            }
            
            .status-derivado {
                background: #667eea;
                color: white;
            }
            
            .status-rechazado {
                background: #f5576c;
                color: white;
            }
            
            .status-finalizado {
                background: #10b981;
                color: white;
            }
            
            .status-pendiente {
                background: #fbbf24;
                color: white;
            }
            
            /* Footer */
            .footer {
                margin-top: 10px;
                padding-top: 6px;
                border-top: 1px solid #e2e8f0;
                text-align: center;
                font-size: 7px;
                color: #718096;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="company-name">EPS EMUSAP ABANCAY SA</div>
                <div class="document-title">HOJA DE ENVÍO DE TRÁMITE</div>
                <div class="document-subtitle">Sistema de Trámite Documentario Web</div>
            </div>
            
            <!-- Two Column Layout -->
            <div class="two-column">
                <!-- Left Column -->
                <div class="column">
                    <!-- Información del Remitente -->
                    <div class="info-section">
                        <div class="section-title">INFORMACIÓN DEL REMITENTE</div>
                        <table class="info-grid">
                            <tr class="info-row">
                                <td class="info-cell">Remitente</td>
                                <td class="info-cell">'.utf8_encode($filas['REMITENTE']).'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">DNI</td>
                                <td class="info-cell">'.$filas['doc_dniremitente'].'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">Celular</td>
                                <td class="info-cell">'.$filas['doc_celularremitente'].'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">Email</td>
                                <td class="info-cell">'.$filas['doc_emailremitente'].'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">Dirección</td>
                                <td class="info-cell">'.utf8_encode($filas['doc_direccionremitente']).'</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="column">
                    <!-- Detalles del Documento -->
                    <div class="info-section">
                        <div class="section-title">DETALLES DEL DOCUMENTO</div>
                        <table class="info-grid">
                            <tr class="info-row">
                                <td class="info-cell">N° Documento</td>
                                <td class="info-cell"><strong>'.$filas['documento_id'].'</strong></td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">N° Expediente</td>
                                <td class="info-cell">'.$filas['doc_nrodocumento'].'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">N° Folios</td>
                                <td class="info-cell">'.$filas['doc_folio'].'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">Tipo Documento</td>
                                <td class="info-cell">'.utf8_encode($filas['tipodo_descripcion']).'</td>
                            </tr>
                            <tr class="info-row">
                                <td class="info-cell">Fecha Registro</td>
                                <td class="info-cell">'.$fecharegistro.'</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Full Width Sections -->
            <div class="info-section">
                <div class="section-title">ÁREAS Y ASUNTO</div>
                <table class="info-grid">
                    <tr class="info-row">
                        <td class="info-cell">Área Origen</td>
                        <td class="info-cell" style="width: 28%;">'.utf8_encode($filas['origen']).'</td>
                        <td class="info-cell">Área Destino</td>
                        <td class="info-cell" style="width: 28%;">'.utf8_encode($filas['destino']).'</td>
                    </tr>
                    <tr class="info-row">
                        <td class="info-cell">Asunto</td>
                        <td class="info-cell" colspan="3">'.utf8_encode($filas['doc_asunto']).'</td>
                    </tr>
                    <tr class="info-row">
                        <td class="info-cell">Observaciones</td>
                        <td class="info-cell" colspan="3">'.utf8_encode($filas['doc_observaciones']).'</td>
                    </tr>
                </table>
            </div>
            
            <!-- Timeline Section -->
            <div class="info-section">
                <div class="section-title">SEGUIMIENTO Y TRAZABILIDAD</div>
                <table class="tracking-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">ÁREA / OFICINA</th>
                            <th style="width: 12%;">ACCIÓN</th>
                            <th style="width: 18%;">FECHA Y HORA</th>
                            <th style="width: 33%;">DESCRIPCIÓN</th>
                            <th style="width: 12%;">ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>';
    
    $consultamovi = "SELECT 
    movimiento.movimiento_id, 
    movimiento.documento_id,
    area.area_cod, 
    area.area_nombre,
    movimiento.mov_fecharegistro, 
    movimiento.mov_descripcion, 
    movimiento.mov_estatus,
    movimiento.mov_acciones
    FROM movimiento
    INNER JOIN area ON movimiento.area_origen_id = area.area_cod
    WHERE movimiento.documento_id = '".$codigo."'
    ORDER BY movimiento.mov_fecharegistro ASC";
    
    $resultadomovi = $mysqli->query($consultamovi);
    
    while($filamovi = $resultadomovi->fetch_assoc()) {
        $statusClass = 'status-pendiente';
        $statusText = $filamovi['mov_estatus'];
        
        switch(strtoupper($filamovi['mov_estatus'])) {
            case 'DERIVADO':
                $statusClass = 'status-derivado';
                break;
            case 'RECHAZADO':
                $statusClass = 'status-rechazado';
                break;
            case 'FINALIZADO':
                $statusClass = 'status-finalizado';
                break;
            default:
                $statusClass = 'status-pendiente';
        }
        
        $html .= '<tr>
            <td style="font-weight: 600; text-align: left; padding-left: 4px;">'.utf8_encode($filamovi['area_nombre']).'</td>
            <td>'.$filamovi['mov_acciones'].'</td>
            <td style="font-size: 7px;">'.date("d/m/Y H:i", strtotime($filamovi['mov_fecharegistro'])).'</td>
            <td style="text-align: left; padding-left: 4px;">'.utf8_encode($filamovi['mov_descripcion']).'</td>
            <td><span class="status-badge '.$statusClass.'">'.$statusText.'</span></td>
        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <div><strong>EPS EMUSAP ABANCAY SA</strong> | Central: (083) 321117 | www.emusapabancay.gob.pe</div>
                <div style="margin-top: 2px;">Documento generado el '.date("d/m/Y H:i:s").' | Validez oficial verificable en el sistema</div>
            </div>
        </div>
    </body>
    </html>';
}

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'Letter',
    'margin_top' => 8,
    'margin_bottom' => 8,
    'margin_left' => 10,
    'margin_right' => 10
]);

$mpdf->SetTitle('Seguimiento de Trámite - '.$codigo);
$mpdf->SetAuthor('EPS EMUSAP ABANCAY');
$mpdf->WriteHTML($html);
$mpdf->Output('Seguimiento_'.$codigo.'.pdf', 'I');
?>