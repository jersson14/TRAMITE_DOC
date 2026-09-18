<?php
/**
 * Prueba la consulta de DNI con el token del formulario (o el guardado) antes de
 * guardarlo (migración 027).
 *
 * Por defecto consulta el DNI del propio administrador (el de su ficha de
 * empleado): así la prueba no consulta datos de un tercero sin motivo.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/ConsultaDni.php';
require_once __DIR__ . '/../../model/model_firma.php';

header('Content-Type: application/json; charset=utf-8');

$dni = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));
if ($dni === '') {
    $ficha = (new Modelo_Firma())->Datos_Usuario(Seguridad::usuarioId());
    $dni = $ficha ? preg_replace('/\D/', '', (string) $ficha['emple_nrodocumento']) : '';
}
$token = trim((string) ($_POST['token'] ?? ''));

try {
    $r = ConsultaDni::consultar($dni, $token !== '' ? $token : null);
} catch (RuntimeException $e) {
    // Un DNI no encontrado igual prueba que el token y la conexión funcionan
    if (strpos($e->getMessage(), 'No se encontró') === 0) {
        echo json_encode(['status' => 'ok', 'encontrado' => false,
            'mensaje' => 'El servicio respondió y aceptó el token (ese DNI no está en el padrón).'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    Seguridad::responderError(422, $e->getMessage());
}

echo json_encode([
    'status'     => 'ok',
    'encontrado' => true,
    'nombre'     => trim(($r['nombres'] ?? '') . ' ' . ($r['apellidoPaterno'] ?? '') . ' ' . ($r['apellidoMaterno'] ?? '')),
], JSON_UNESCAPED_UNICODE);
