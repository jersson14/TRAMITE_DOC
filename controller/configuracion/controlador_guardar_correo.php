<?php
/**
 * Guarda la configuración del correo saliente (SMTP) desde el panel (migración 027).
 *
 * La contraseña solo se sobrescribe si se envía una nueva: la pantalla muestra la
 * guardada enmascarada, así que un guardado normal no debe borrarla.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../lib/Configuracion.php';

header('Content-Type: application/json; charset=utf-8');

$host = trim((string) ($_POST['host'] ?? ''));
$puerto = (int) ($_POST['puerto'] ?? 0);
$seguridad = (string) ($_POST['seguridad'] ?? '');
$usuario = trim((string) ($_POST['usuario'] ?? ''));
$nombre = trim((string) ($_POST['nombre'] ?? ''));
$correo = trim((string) ($_POST['correo'] ?? ''));

if ($host === '' || !preg_match('/^[A-Za-z0-9.\-]+$/', $host) || mb_strlen($host) > 120) {
    Seguridad::responderError(422, 'Indique el servidor SMTP (por ejemplo smtp.gmail.com).');
}
if ($puerto < 1 || $puerto > 65535) {
    Seguridad::responderError(422, 'El puerto debe estar entre 1 y 65535 (lo usual es 465 o 587).');
}
if (!in_array($seguridad, ['ssl', 'tls', ''], true)) {
    Seguridad::responderError(422, 'Elija el tipo de cifrado.');
}
if ($usuario !== '' && mb_strlen($usuario) > 150) {
    Seguridad::responderError(422, 'El usuario es demasiado largo.');
}
if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    Seguridad::responderError(422, 'Indique un correo de remitente válido.');
}
if ($nombre === '' || mb_strlen($nombre) > 120) {
    Seguridad::responderError(422, 'Indique el nombre que verá quien reciba el correo.');
}

$valores = [
    'smtp_activo'           => !empty($_POST['activo']) ? '1' : '0',
    'smtp_host'             => $host,
    'smtp_puerto'           => (string) $puerto,
    'smtp_seguridad'        => $seguridad,
    'smtp_usuario'          => $usuario,
    'smtp_remitente_nombre' => $nombre,
    'smtp_remitente_correo' => $correo,
];

// La contraseña solo cambia si se escribe una nueva (o se pide quitarla)
$clave = (string) ($_POST['clave'] ?? '');
$quitar = !empty($_POST['quitar_clave']);
if ($quitar) {
    $valores['smtp_clave'] = '';
} elseif ($clave !== '') {
    if (mb_strlen($clave) > 200) {
        Seguridad::responderError(422, 'La contraseña es demasiado larga.');
    }
    $valores['smtp_clave'] = $clave;
}

Configuracion::guardar($valores);

// En la bitácora queda qué se cambió, nunca la contraseña
Bitacora::registrar(Bitacora::MODIFICO, 'configuracion', 'correo',
    $host . ':' . $puerto . ($seguridad !== '' ? ' ' . $seguridad : '') . ' · remitente ' . $correo
    . ' · ' . ($valores['smtp_activo'] === '1' ? 'activo' : 'apagado')
    . ($quitar ? ' · contraseña quitada' : ($clave !== '' ? ' · contraseña actualizada' : '')));

echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
