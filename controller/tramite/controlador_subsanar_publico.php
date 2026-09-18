<?php
/**
 * El ciudadano subsana una observación desde el portal público (migración 025).
 *
 * No hay sesión: el ciudadano se identifica con su N° de expediente (o código de
 * seguimiento) y el DNI del remitente, el mismo par que usa para consultar en
 * seguimiento.php. Sube lo que le pidieron y el trámite vuelve a la bandeja del
 * área, en el estado en que estaba antes de observarlo.
 *
 * Es una subida pública, así que:
 *   - tiene límite de intentos por IP;
 *   - responde lo mismo si el trámite no existe o si el DNI no coincide, para
 *     no revelar qué dato falló;
 *   - valida el PDF por su contenido real, no por lo que diga el navegador.
 *
 * Si el ciudadano subsana después del plazo se acepta igual, pero queda anotado
 * como "fuera de plazo": decidir si igual se continúa es del área, no del sistema.
 */
require_once __DIR__ . '/../../lib/Seguridad.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../model/model_conexion.php';
require_once __DIR__ . '/../../model/model_tramite.php';
require_once __DIR__ . '/../../model/model_observacion.php';
require_once __DIR__ . '/../../utilitario/class_notificacion.php';
Seguridad::iniciarSesion();

header('Content-Type: application/json; charset=utf-8');

// Subida pública: 10 intentos cada 10 minutos por IP
$clave = 'subsanar_publico|' . Seguridad::ipCliente();
if (Seguridad::esperaIntentos($clave, 10, 600) > 0) {
    Seguridad::responderError(429, 'Demasiados intentos seguidos. Espere unos minutos e intente nuevamente.');
}
Seguridad::registrarIntento($clave, 600);

$numero = trim(preg_replace('/[^A-Z0-9]+/', '-', strtoupper((string) ($_POST['numero'] ?? ''))), '-');
$dni = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));
$texto = trim((string) ($_POST['texto'] ?? ''));

if ($numero === '' || strlen($dni) !== 8) {
    Seguridad::responderError(422, 'Ingrese su N° de expediente y su DNI de 8 dígitos.');
}
if (mb_strlen($texto) > 2000) {
    Seguridad::responderError(422, 'El comentario no debe superar los 2000 caracteres.');
}

// Mismos formatos que la consulta pública
if (preg_match('/^D\d{7}$/', $numero)) {
    $campo = 'documento_id';
} elseif (preg_match('/^(?:EXP-?)?(\d{4})(?:-(\d{1,6})|(\d{6}))$/', $numero, $m)) {
    $m[2] = $m[2] !== '' ? $m[2] : $m[3];
    $campo = 'doc_expediente';
    $numero = sprintf('EXP-%s-%06d', $m[1], (int) $m[2]);
} else {
    Seguridad::responderError(422, 'Escriba el N° de expediente como EXP-2026-000123 o el código como D0000123.');
}

$pdo = (new conexionBD())->conexionPDO();
$q = $pdo->prepare("SELECT documento_id, doc_expediente FROM documento
                     WHERE $campo = ? AND doc_dniremitente = ? LIMIT 1");
$q->execute([$numero, $dni]);
$tramite = $q->fetch(PDO::FETCH_ASSOC);

$MOB = new Modelo_Observacion();
$observacion = $tramite ? $MOB->Pendiente($tramite['documento_id']) : null;

if (!$tramite || !$observacion) {
    // Mismo mensaje si no existe, si el DNI no coincide o si no hay nada que subsanar
    Seguridad::responderError(422, 'No encontramos una observación pendiente para ese expediente y DNI.');
}

// Lo que falta tiene que llegar como PDF: sin archivo no hay subsanación
try {
    $archivo = Seguridad::guardarArchivo('archivo', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'SUB');
} catch (RuntimeException $e) {
    Seguridad::responderError(422, $e->getMessage());
}
if (!$archivo) {
    Seguridad::responderError(422, 'Adjunte en PDF el documento que le pidieron.');
}
$ruta = 'controller/tramite/documentos/' . $archivo;
$original = (string) ($_FILES['archivo']['name'] ?? 'subsanacion.pdf');
$original = mb_substr(preg_replace('/[^\p{L}\p{N} ._()\-]/u', '', basename($original)), 0, 150) ?: 'subsanacion.pdf';

try {
    $documentoId = $MOB->Subsanar($observacion['observacion_id'], $texto !== '' ? $texto : null,
                                  $ruta, $original, Seguridad::ipCliente());
} catch (RuntimeException $e) {
    Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $archivo);
    Seguridad::responderError(422, $e->getMessage());
}

// El documento subsanado entra a los archivos del trámite, para que el área lo
// vea en el mismo panel que el resto (y lo pueda verificar o descargar).
$MTR = new Modelo_Tramite();
$MTR->Registrar_Anexos($documentoId, [[
    'nombre'   => $archivo,
    'original' => 'Subsanación - ' . $original,
    'bytes'    => (int) filesize(__DIR__ . '/documentos/' . $archivo),
]], 'controller/tramite/documentos', 0);

$fueraDePlazo = (int) $observacion['vencida'] === 1;
Bitacora::registrar(Bitacora::SUBSANACION, 'documento', $documentoId,
    'el ciudadano subsanó la observación' . ($fueraDePlazo ? ' · FUERA DE PLAZO (vencía el ' . $observacion['limite_texto'] . ')' : '')
    . ($texto !== '' ? ' · ' . mb_substr($texto, 0, 120) : ''),
    'ciudadano DNI ' . $dni);

if (!empty($observacion['area_id'])) {
    (new Notificacion())->notificarSubsanacion((int) $observacion['area_id'], $tramite['doc_expediente'], $texto);
}

echo json_encode([
    'status'         => 'ok',
    'expediente'     => $tramite['doc_expediente'],
    'fuera_de_plazo' => $fueraDePlazo,
]);
