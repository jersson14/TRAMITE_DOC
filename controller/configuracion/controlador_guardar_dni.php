<?php
/**
 * Guarda la configuración de la consulta de DNI (apis.net.pe) desde el panel
 * (migración 027). Antes el token estaba escrito en el código fuente.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../lib/Configuracion.php';

header('Content-Type: application/json; charset=utf-8');

$valores = ['dni_activo' => !empty($_POST['activo']) ? '1' : '0'];

$token = trim((string) ($_POST['token'] ?? ''));
$quitar = !empty($_POST['quitar_token']);
if ($quitar) {
    $valores['dni_token'] = '';
} elseif ($token !== '') {
    if (mb_strlen($token) < 20 || mb_strlen($token) > 300 || preg_match('/\s/', $token)) {
        Seguridad::responderError(422, 'El token no parece válido: revise que lo haya copiado completo.');
    }
    $valores['dni_token'] = $token;
}

Configuracion::guardar($valores);

Bitacora::registrar(Bitacora::MODIFICO, 'configuracion', 'consulta_dni',
    ($valores['dni_activo'] === '1' ? 'activa' : 'apagada')
    . ($quitar ? ' · token quitado' : ($token !== '' ? ' · token actualizado' : '')));

echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
