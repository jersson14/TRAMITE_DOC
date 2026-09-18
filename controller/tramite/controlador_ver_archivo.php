<?php
/**
 * Entrega un archivo de un trámite a quien puede verlo.
 *
 * Las carpetas de documentos están cerradas al acceso web (su .htaccess niega
 * todo): antes, quien conociera la dirección de un PDF lo descargaba sin iniciar
 * sesión, y los nombres antiguos (ARCH<fecha>-<hora>-<n>.PDF) se podían adivinar.
 * Ahora cada archivo pasa por aquí, que exige sesión y aplica la misma regla que
 * el listado de archivos: el administrador ve todo; el resto, solo los trámites
 * que pasaron por su área.
 *
 * Es un enlace (GET) que se abre en otra pestaña, por eso no pide token CSRF:
 * solo lee, no cambia nada.
 *
 * Uso: controlador_ver_archivo.php?ruta=controller/tramite/documentos/X.pdf
 */
require_once __DIR__ . '/../../lib/Seguridad.php';
require_once __DIR__ . '/../../model/model_tramite.php';

Seguridad::iniciarSesion();

function negarArchivo(int $codigo, string $mensaje): void
{
    http_response_code($codigo);
    header('Content-Type: text/plain; charset=utf-8');
    echo $mensaje;
    exit;
}

if (!Seguridad::autenticado()) {
    negarArchivo(401, 'Su sesión expiró. Inicie sesión nuevamente para ver el archivo.');
}

$ruta = str_replace('\\', '/', trim((string) ($_GET['ruta'] ?? '')));

// Solo las dos carpetas de documentos de trámites, y nada que salga de ellas.
if (!preg_match('#^controller/(tramite|tramite_area)/documentos/[^/]+$#', $ruta)) {
    negarArchivo(404, 'Archivo no encontrado.');
}
$raiz = realpath(__DIR__ . '/../..');
$completa = realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta));
$carpetas = [
    realpath($raiz . '/controller/tramite/documentos'),
    realpath($raiz . '/controller/tramite_area/documentos'),
];
if (!$completa || !is_file($completa) || !in_array(dirname($completa), $carpetas, true)) {
    negarArchivo(404, 'Archivo no encontrado.');
}

// Un archivo que ningún trámite referencia no se entrega a nadie.
$MTR = new Modelo_Tramite();
$documentos = $MTR->Documentos_De_Archivo($ruta);
if (!$documentos) {
    negarArchivo(404, 'Archivo no encontrado.');
}
if (!Seguridad::esAdmin()) {
    $puede = false;
    foreach ($documentos as $id) {
        if ($MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
            $puede = true;
            break;
        }
    }
    if (!$puede) {
        negarArchivo(403, 'No tiene acceso a este archivo.');
    }
}

$tipos = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
$extension = strtolower(pathinfo($completa, PATHINFO_EXTENSION));
$tipo = $tipos[$extension] ?? 'application/octet-stream';
$nombre = basename($completa);

header('Content-Type: ' . $tipo);
header('Content-Length: ' . filesize($completa));
header('Content-Disposition: ' . (isset($_GET['descargar']) ? 'attachment' : 'inline') . '; filename="' . $nombre . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($completa);
