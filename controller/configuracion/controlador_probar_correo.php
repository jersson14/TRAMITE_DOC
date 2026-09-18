<?php
/**
 * Envía un correo de prueba (migración 027). Reemplaza a test_email.php, que era
 * un archivo público: cualquiera podía abrirlo y hacer que el sistema enviara correos.
 *
 * Prueba lo que está en el formulario, aunque todavía no se haya guardado: así el
 * administrador comprueba que funciona ANTES de guardar. La contraseña, si no se
 * escribe una nueva, es la ya guardada.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../lib/Configuracion.php';
require_once __DIR__ . '/../../utilitario/class_notificacion.php';

header('Content-Type: application/json; charset=utf-8');

$destino = trim((string) ($_POST['destino'] ?? ''));
if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
    Seguridad::responderError(422, 'Indique a qué correo enviar la prueba.');
}

$actual = Configuracion::smtp();
$clave = (string) ($_POST['clave'] ?? '');
$smtp = [
    'host'      => trim((string) ($_POST['host'] ?? '')),
    'puerto'    => (int) ($_POST['puerto'] ?? 0),
    'seguridad' => (string) ($_POST['seguridad'] ?? ''),
    'usuario'   => trim((string) ($_POST['usuario'] ?? '')),
    'clave'     => $clave !== '' ? $clave : $actual['clave'],
    'nombre'    => trim((string) ($_POST['nombre'] ?? '')),
    'correo'    => trim((string) ($_POST['correo'] ?? '')),
];
if ($smtp['host'] === '' || $smtp['puerto'] < 1) {
    Seguridad::responderError(422, 'Complete el servidor y el puerto antes de probar.');
}

try {
    $mail = Notificacion::mailerDesde($smtp);
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo del sistema de trámite';
    $mail->Body = '<p>Este es un correo de prueba enviado desde <b>Configuración</b>.</p>'
                . '<p>Si lo está leyendo, el correo saliente funciona: el sistema puede avisar '
                . 'a los ciudadanos y a las áreas.</p>';
    $mail->AltBody = "Correo de prueba enviado desde Configuración. El correo saliente funciona.";
    $mail->addAddress($destino);
    $mail->send();
} catch (Throwable $e) {
    // El mensaje de PHPMailer dice qué falló (autenticación, conexión, puerto)
    $motivo = isset($mail) && $mail->ErrorInfo ? $mail->ErrorInfo : $e->getMessage();
    error_log('[CORREO PRUEBA] ' . $motivo);
    Seguridad::responderError(422, 'No se pudo enviar: ' . $motivo);
}

Bitacora::registrar(Bitacora::MODIFICO, 'configuracion', 'correo', 'correo de prueba enviado a ' . $destino);
echo json_encode(['status' => 'ok', 'destino' => $destino], JSON_UNESCAPED_UNICODE);
