<?php
/**
 * Modelos que admite la clave, para que el panel los ofrezca en una lista.
 *
 * Escribir el nombre del modelo a mano es frágil: los proveedores los retiran
 * (a gemini-1.5-flash le pasó) y cada cuenta tiene acceso a unos distintos, así
 * que se pregunta al proveedor en vez de suponer.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/FabricaIA.php';

header('Content-Type: application/json; charset=utf-8');

$proveedor = trim((string) ($_POST['proveedor'] ?? ''));
if (!array_key_exists($proveedor, Configuracion::PROVEEDORES)) {
    Seguridad::responderError(422, 'Elija un proveedor de IA válido.');
}
// Si no escribieron una clave nueva, se usa la guardada
$clave = trim((string) ($_POST['clave'] ?? ''));

try {
    $modelos = FabricaIA::cliente($proveedor, $clave !== '' ? $clave : null)->modelos();
    echo json_encode([
        'status'  => 'ok',
        'modelos' => $modelos,
        'sugerido' => Configuracion::PROVEEDORES[$proveedor][1],
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[IA MODELOS] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
