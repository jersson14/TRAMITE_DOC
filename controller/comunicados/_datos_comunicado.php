<?php
/**
 * Valida los datos de un comunicado (los usan el registro y la modificación).
 * El destino decide a quién se le mostrará la alerta; con AREAS hace falta al
 * menos un área.
 */
function datosComunicado(): array
{
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $descripcion = trim((string) ($_POST['descri'] ?? ''));
    $enlace = trim((string) ($_POST['enlace'] ?? ''));
    $destino = strtoupper(trim((string) ($_POST['destino'] ?? 'TODOS')));
    $desde = trim((string) ($_POST['desde'] ?? ''));
    $hasta = trim((string) ($_POST['hasta'] ?? ''));
    $areas = array_filter(array_map('intval', (array) ($_POST['areas'] ?? [])));

    if (mb_strlen($titulo) < 4) {
        Seguridad::responderError(422, 'Escriba el título del comunicado.');
    }
    if (mb_strlen($titulo) > 1000) {
        Seguridad::responderError(422, 'El título es demasiado largo.');
    }
    if (mb_strlen($descripcion) < 4) {
        Seguridad::responderError(422, 'Escriba el contenido del comunicado.');
    }
    if ($enlace !== '' && !preg_match('#^https?://#i', $enlace)) {
        Seguridad::responderError(422, 'El enlace debe empezar con http:// o https://');
    }
    if (mb_strlen($enlace) > 10000) {
        Seguridad::responderError(422, 'El enlace es demasiado largo.');
    }
    if (!in_array($destino, Modelo_Comunicados::DESTINOS, true)) {
        Seguridad::responderError(422, 'Elija a quién va dirigido el comunicado.');
    }
    if ($destino === 'AREAS' && !$areas) {
        Seguridad::responderError(422, 'Elija al menos un área destinataria.');
    }

    $fecha = function (string $valor): ?string {
        if ($valor === '') {
            return null;
        }
        $d = DateTime::createFromFormat('Y-m-d', $valor);
        if (!$d || $d->format('Y-m-d') !== $valor) {
            Seguridad::responderError(422, 'Las fechas de vigencia no son válidas.');
        }
        return $valor;
    };
    $desde = $fecha($desde);
    $hasta = $fecha($hasta);
    if ($desde && $hasta && $desde > $hasta) {
        Seguridad::responderError(422, 'La vigencia no puede terminar antes de empezar.');
    }

    return [
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'enlace'      => $enlace !== '' ? $enlace : null,
        'destino'     => $destino,
        'desde'       => $desde,
        'hasta'       => $hasta,
        'areas'       => $areas,
    ];
}
