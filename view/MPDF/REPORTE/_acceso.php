<?php
// Valida el código del trámite y que quien pide el reporte pueda verlo:
// personal con sesión activa, o el ciudadano que conoce el código y el DNI del remitente.
// Requiere que $mysqli ya esté definido (../conexion.php). Deja validado $codigo.
require_once __DIR__ . '/../../../lib/Seguridad.php';
Seguridad::iniciarSesion();

$codigo = strtoupper(trim((string) ($_GET['codigo'] ?? '')));
if (!preg_match('/^[A-Z0-9-]{1,20}$/', $codigo)) {
    http_response_code(400);
    exit('Código de trámite no válido.');
}

if (!Seguridad::autenticado()) {
    $dni = trim((string) ($_GET['dni'] ?? ''));
    $consulta = $mysqli->prepare('SELECT 1 FROM documento WHERE documento_id = ? AND doc_dniremitente = ?');
    $consulta->bind_param('ss', $codigo, $dni);
    $consulta->execute();
    if (!$consulta->get_result()->fetch_row()) {
        http_response_code(403);
        exit('No tiene acceso a este documento. Verifique el código del trámite y el DNI del remitente.');
    }
}
