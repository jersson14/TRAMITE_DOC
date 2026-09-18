<?php
/**
 * Guarda el aviso de privacidad que se muestra en el portal ciudadano.
 * Vacío (o «restablecer») vuelve al texto base de lib/AvisoPrivacidad.php.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../lib/AvisoPrivacidad.php';

header('Content-Type: application/json; charset=utf-8');

$texto = !empty($_POST['restablecer']) ? '' : trim(str_replace("\r\n", "\n", (string) ($_POST['texto'] ?? '')));

if (mb_strlen($texto) > AvisoPrivacidad::LARGO_MAXIMO) {
    Seguridad::responderError(422, 'El aviso no debe pasar de ' . AvisoPrivacidad::LARGO_MAXIMO . ' caracteres.');
}
if ($texto !== '' && mb_strlen($texto) < 200) {
    Seguridad::responderError(422, 'El aviso es demasiado corto: debe indicar quién trata los datos, para qué, con quién se comparten, cuánto se guardan y cómo ejercer los derechos.');
}
// Si pegaron el texto base sin cambios, se guarda vacío: así sigue los datos de la institución
if ($texto === AvisoPrivacidad::textoBase()) {
    $texto = '';
}

Configuracion::guardar(['privacidad_texto' => $texto]);

Bitacora::registrar(Bitacora::MODIFICO, 'configuracion', 'aviso_privacidad',
    $texto === '' ? 'texto base' : 'texto propio (' . mb_strlen($texto) . ' caracteres)');

echo json_encode(['status' => 'ok', 'propio' => $texto !== ''], JSON_UNESCAPED_UNICODE);
