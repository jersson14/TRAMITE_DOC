<?php
/**
 * Guarda los ajustes del asistente desde el panel.
 *
 * La clave solo se sobrescribe si se envía una nueva: la pantalla muestra la
 * guardada enmascarada, así que un guardado normal no debe borrarla. Para
 * quitarla hay que pedirlo explícitamente.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../lib/Configuracion.php';

header('Content-Type: application/json; charset=utf-8');

$proveedor = trim((string) ($_POST['proveedor'] ?? ''));
if (!array_key_exists($proveedor, Configuracion::PROVEEDORES)) {
    Seguridad::responderError(422, 'Elija un proveedor de IA válido.');
}

$modelo = trim((string) ($_POST['modelo'] ?? ''));
if ($modelo === '' || mb_strlen($modelo) > 60 || !preg_match('/^[A-Za-z0-9._\-]+$/', $modelo)) {
    Seguridad::responderError(422, 'Indique el nombre del modelo (por ejemplo gpt-4o).');
}

$minuto = (int) ($_POST['limite_minuto'] ?? 10);
$dia = (int) ($_POST['limite_dia'] ?? 100);
if ($minuto < 1 || $minuto > 120 || $dia < 1 || $dia > 5000 || $dia < $minuto) {
    Seguridad::responderError(422, 'Revise los límites: entre 1 y 120 por minuto, y hasta 5000 por día.');
}

$valores = [
    'ia_activo'        => !empty($_POST['activo']) ? '1' : '0',
    'ia_proveedor'     => $proveedor,
    'ia_modelo'        => $modelo,
    'ia_limite_minuto' => (string) $minuto,
    'ia_limite_dia'    => (string) $dia,
];

$clave = trim((string) ($_POST['clave'] ?? ''));
$quitar = !empty($_POST['quitar_clave']);
if ($quitar) {
    $valores['ia_clave'] = '';
} elseif ($clave !== '') {
    if (mb_strlen($clave) < 20 || mb_strlen($clave) > 300 || preg_match('/\s/', $clave)) {
        Seguridad::responderError(422, 'La clave no parece válida: revise que la haya copiado completa.');
    }
    $valores['ia_clave'] = $clave;
}

Configuracion::guardar($valores);

// En la bitácora queda qué se cambió, nunca la clave
Bitacora::registrar(Bitacora::MODIFICO, 'configuracion', 'asistente',
    'proveedor ' . $proveedor . ' · modelo ' . $modelo
    . ' · ' . ($valores['ia_activo'] === '1' ? 'activo' : 'apagado')
    . ($quitar ? ' · clave quitada' : ($clave !== '' ? ' · clave actualizada' : '')));

echo json_encode(['status' => 'ok'], JSON_UNESCAPED_UNICODE);
