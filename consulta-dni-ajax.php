<?php
/**
 * Consulta de DNI para autocompletar el nombre del remitente.
 *
 * La usan el portal ciudadano (registrar.php, sin sesión) y el registro de mesa
 * de partes. Por eso es pública, y por eso:
 *   - tiene límite de consultas por IP: cada consulta cuesta saldo del token, y
 *     antes cualquiera podía usar este archivo como proxy gratuito;
 *   - valida el DNI antes de enviarlo (antes se pegaba tal cual a la URL);
 *   - el token sale del panel de configuración, no del código (antes estaba
 *     escrito aquí y se subió al repositorio).
 *
 * Si hay datos, devuelve el JSON de apis.net.pe tal cual (las pantallas leen
 * nombres, apellidoPaterno y apellidoMaterno). Si no, un 422 con el motivo.
 */
require_once __DIR__ . '/lib/Seguridad.php';
require_once __DIR__ . '/lib/ConsultaDni.php';
Seguridad::iniciarSesion();

header('Content-Type: application/json; charset=utf-8');

// 20 consultas cada 10 minutos por IP: de sobra para registrar, no para abusar
$clave = 'consulta_dni|' . Seguridad::ipCliente();
if (Seguridad::esperaIntentos($clave, 20, 600) > 0) {
    Seguridad::responderError(429, 'Demasiadas consultas de DNI seguidas. Espere unos minutos o escriba los datos a mano.');
}
Seguridad::registrarIntento($clave, 600);

$dni = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));

if (!ConsultaDni::disponible()) {
    Seguridad::responderError(422, 'La consulta automática de DNI no está disponible. Escriba los datos a mano.');
}

try {
    echo json_encode(ConsultaDni::consultar($dni), JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    Seguridad::responderError(422, $e->getMessage());
}
