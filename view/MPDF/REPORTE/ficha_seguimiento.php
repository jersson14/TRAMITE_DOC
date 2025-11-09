<?php
require_once  __DIR__ . '/../vendor/autoload.php';
require_once '../conexion.php';
$codigo = $_GET['codigo'];
$html="";
$consulta="SELECT
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

  $resultado=$mysqli->query($consulta);
  
  while($filas=$resultado->fetch_assoc()){
    $fecha = setlocale(LC_TIME, "spanish");					    
        $fecharegistro = date("d-m-Y - h:i:sa", strtotime($filas['doc_fecharegistro']));
    
    $html.='
    <div style="font-family:arial">
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead>
    <tr>
    <td align="left"> <img src="../../../img/emusap.jpg" alt="Girl in a jacket" width="150" height="60" align="left"></td>
    </tr>
    </thead>
    </table><br>
    <div margin: 0;><h2  style="text-align:center;float:center;width:100%; margin: 0;font-family:Cooper Black; color:black;font-size:17px">
        <u>HOJA DE ENVÍO DE TRÁMITE GENERAL</u></h2>
    </div><br>
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead align="left">
    <tr>
    <td align="left"><b>Remitente: </b><span>'.utf8_encode($filas['REMITENTE']).'</span></td>
    <td align="left"><b>Área Orig.: </b><span>'.utf8_encode($filas['origen']).'</span></td>
    <td align="left"><b>N° Documento: </b>'.utf8_encode($filas['documento_id']).'</td>
    </tr>
    </thead>
    </table>
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead align="left">
    <tr>
    <td align="left"><b>Área Dest.: </b><span>'.utf8_encode($filas['destino']).'</span></td>
    <td align="left"><b>N° Expediente: </b>'.utf8_encode($filas['doc_nrodocumento']).'</td>
    <td align="left"><b>N° Folios: </b>'.utf8_encode($filas['doc_folio']).'</td>

    </tr>
    </thead>
    </table>
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead align="left">
    <tr>
    <td align="left"><b>Tipo Documento: </b>'.utf8_encode($filas['tipodo_descripcion']).'</td>
    </tr>
    </thead>
    </table>
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead align="left">
    <tr>
    <td align="left"><b>Fecha de Registro: </b><span>'.$fecharegistro.'</span></td>
    </tr>
    </thead>
    </table>
    
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead align="left">
    <tr>
    <td align="left"><b>Asunto: </b><span>'.utf8_encode($filas['doc_asunto']).'</span></td>

    </tr>
    </thead>
    </table>
    <table style="  font-size: 12px;width:100%;border-collapse:collapse; border-color:#FF0080; margin: 0;" >
    <thead align="left">
    <tr>
    <td align="left"><b>Observaciones: </b><span>'.utf8_encode($filas['doc_observaciones']).'</span></td>
    </tr>
    </thead>
    </table>
    </div>
    <div style="float:right;widht:100%; text-align:center">
    <img src="../../../cuadro_seguimiento.jpg" alt="Girl in a jacket" width="100%" height="93%">
</div>

';
}
$mpdf = new \Mpdf\Mpdf(
['mode' => 'UTF-8','format' => [190, 245]]
);
$mpdf->WriteHTML($html);
$mpdf->Output();

?>