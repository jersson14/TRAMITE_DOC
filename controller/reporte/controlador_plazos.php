<?php
/**
 * Datos del reporte de plazos y productividad por área (pantalla).
 * El área de un usuario que no es administrador sale de su sesión.
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/ReportePlazos.php';

header('Content-Type: application/json; charset=utf-8');

$desde = trim((string) ($_POST['desde'] ?? ''));
$hasta = trim((string) ($_POST['hasta'] ?? ''));

$valida = function (string $fecha): bool {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
};
if (!$valida($desde) || !$valida($hasta) || $desde > $hasta) {
    Seguridad::responderError(422, 'Elija un rango de fechas válido (la fecha inicial no puede ser posterior a la final).');
}

$area = Seguridad::esAdmin() ? (int) ($_POST['area'] ?? 0) : Seguridad::areaId();
echo json_encode(ReportePlazos::calcular($desde, $hasta, $area > 0 ? $area : null));
