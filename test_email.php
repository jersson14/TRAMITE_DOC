<?php
/**
 * =====================================================
 * SCRIPT DE PRUEBA DE CORREO
 * =====================================================
 * Ejecuta este archivo desde el navegador para verificar
 * que la configuración SMTP funciona correctamente.
 *
 * URL: http://localhost/SISTRAMITEDOC/test_email.php
 *
 * ⚠️ ELIMINA ESTE ARCHIVO EN PRODUCCIÓN
 * =====================================================
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config_email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------- DESTINATARIO DE PRUEBA ----------
$correo_prueba = 'jersson14071996@gmail.com'; // ← cambia si quieres probar con otro correo
// --------------------------------------------

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = EMAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USERNAME;
    $mail->Password   = EMAIL_PASSWORD;
    $mail->SMTPSecure = EMAIL_SECURE;
    $mail->Port       = EMAIL_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(EMAIL_FROM_EMAIL, EMAIL_FROM_NAME);
    $mail->addAddress($correo_prueba);

    $mail->isHTML(true);
    $mail->Subject = '✅ Prueba de Correo - Sistema Trámite';
    $mail->Body    = '<h2 style="color:#1e3a5f;">¡La configuración SMTP funciona correctamente!</h2>
                      <p>Si ves este mensaje, el sistema de notificaciones está listo.</p>
                      <p>Servidor: <strong>' . EMAIL_HOST . '</strong></p>
                      <p>Puerto: <strong>' . EMAIL_PORT . '</strong></p>';
    $mail->AltBody = 'La configuración SMTP funciona correctamente.';

    $mail->send();
    echo '<div style="font-family:sans-serif;max-width:500px;margin:50px auto;padding:30px;background:#e8f5e9;border-radius:10px;border-left:5px solid #27ae60">';
    echo '  <h2 style="color:#1e7e34;margin:0 0 10px">✅ ¡Correo enviado correctamente!</h2>';
    echo '  <p style="margin:0">Se envió un correo de prueba a: <strong>' . htmlspecialchars($correo_prueba) . '</strong></p>';
    echo '</div>';
} catch (Exception $e) {
    echo '<div style="font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;background:#fdecea;border-radius:10px;border-left:5px solid #c62828">';
    echo '  <h2 style="color:#c62828;margin:0 0 10px">❌ Error al enviar el correo</h2>';
    echo '  <p style="margin:0 0 10px"><strong>Mensaje:</strong> ' . htmlspecialchars($mail->ErrorInfo) . '</p>';
    echo '  <p style="margin:0;font-size:13px;color:#666">Verifica las credenciales en <code>config/config_email.php</code></p>';
    echo '</div>';
}
