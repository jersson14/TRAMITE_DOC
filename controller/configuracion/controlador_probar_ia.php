<?php
/**
 * Prueba la conexión con el proveedor de IA sin guardar nada.
 *
 * Sirve para que el administrador sepa si la clave y el modelo funcionan antes
 * de dejar el asistente encendido. Si en el formulario no escribió una clave
 * nueva, se prueba con la que ya está guardada.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/FabricaIA.php';

header('Content-Type: application/json; charset=utf-8');

$proveedor = trim((string) ($_POST['proveedor'] ?? ''));
if (!array_key_exists($proveedor, Configuracion::PROVEEDORES)) {
    Seguridad::responderError(422, 'Elija un proveedor de IA válido.');
}
$modelo = trim((string) ($_POST['modelo'] ?? ''));
$clave = trim((string) ($_POST['clave'] ?? ''));

$cliente = FabricaIA::cliente($proveedor, $clave !== '' ? $clave : null, $modelo !== '' ? $modelo : null);

if (!$cliente->disponible()) {
    echo json_encode([
        'status'  => 'error',
        'mensaje' => 'Falta la clave del proveedor: escríbala arriba y vuelva a probar.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$inicio = microtime(true);
try {
    // Pregunta mínima: interesa que responda, no qué responde
    $texto = $cliente->generarTexto('Responde solamente con la palabra LISTO.', 0.0, 20);
    $ms = (int) round((microtime(true) - $inicio) * 1000);
    echo json_encode([
        'status'    => 'ok',
        'proveedor' => $cliente->descripcion(),
        'respuesta' => mb_substr($texto, 0, 60),
        'ms'        => $ms,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    error_log('[IA PRUEBA] ' . $e->getMessage());
    echo json_encode([
        'status'    => 'error',
        'proveedor' => $cliente->descripcion(),
        'mensaje'   => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
