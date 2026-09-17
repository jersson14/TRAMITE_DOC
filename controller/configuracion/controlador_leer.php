<?php
/**
 * Lo que necesita la pantalla de configuración: datos de la institución y
 * ajustes del asistente. La clave de la API sale enmascarada; el valor real
 * nunca viaja al navegador.
 */
require_once __DIR__ . '/../_guard_admin.php';
require_once __DIR__ . '/../../lib/Configuracion.php';
require_once __DIR__ . '/../../lib/Institucion.php';
require_once __DIR__ . '/../../model/model_conexion.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = (new conexionBD())->conexionPDO();
$empresa = $pdo->query('SELECT * FROM empresa ORDER BY empresa_id LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];

$logo = (string) ($empresa['emp_logo'] ?? '');
$logoExiste = $logo !== '' && is_file(dirname(__DIR__, 2) . '/' . $logo);

echo json_encode([
    'status' => 'ok',
    'institucion' => [
        'id'          => (int) ($empresa['empresa_id'] ?? 0),
        'razon'       => (string) ($empresa['emp_razon'] ?? ''),
        'sigla'       => (string) ($empresa['emp_sigla'] ?? ''),
        'email'       => (string) ($empresa['emp_email'] ?? ''),
        'codigo'      => (string) ($empresa['emp_cod'] ?? ''),
        'telefono'    => (string) ($empresa['emp_telefono'] ?? ''),
        'direccion'   => (string) ($empresa['emp_direccion'] ?? ''),
        'color'       => (string) ($empresa['emp_color'] ?? Institucion::COLOR_DEFECTO),
        'logo'        => $logo,
        // El logo guardado puede apuntar a un archivo borrado: la pantalla lo avisa
        'logo_existe' => $logoExiste,
        'logo_usado'  => Institucion::datos()['logo'],
        'hora_inicio' => substr((string) ($empresa['emp_hora_inicio'] ?? '08:00:00'), 0, 5),
        'hora_fin'    => substr((string) ($empresa['emp_hora_fin'] ?? '16:30:00'), 0, 5),
    ],
    'asistente' => [
        'activo'         => Configuracion::obtener('ia_activo') === '1',
        'proveedor'      => Configuracion::obtener('ia_proveedor'),
        'modelo'         => Configuracion::obtener('ia_modelo'),
        'clave'          => Configuracion::claveEnmascarada(),
        'tiene_clave'    => Configuracion::obtener('ia_clave') !== '',
        'limite_minuto'  => Configuracion::entero('ia_limite_minuto', 10),
        'limite_dia'     => Configuracion::entero('ia_limite_dia', 100),
        'proveedores'    => Configuracion::PROVEEDORES,
    ],
], JSON_UNESCAPED_UNICODE);
