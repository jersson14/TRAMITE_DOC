<?php
/**
 * Validación pública de firmas digitales (código impreso en el sello y QR).
 *
 * No muestra ni entrega el documento: solo confirma quién firmó, cuándo y si el
 * archivo que guarda la entidad sigue íntegro. Quien tenga una copia puede
 * subirla para comprobar que es la misma que se firmó (el archivo no se guarda).
 */
require_once __DIR__ . '/lib/Seguridad.php';
require_once __DIR__ . '/lib/Institucion.php';
require_once __DIR__ . '/lib/FirmaDigital.php';
require_once __DIR__ . '/model/model_firma.php';

header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

$e = [Seguridad::class, 'e'];
$institucion = Institucion::datos();
$codigo = strtoupper(trim((string) ($_REQUEST['codigo'] ?? '')));
$codigo = preg_replace('/[^A-F0-9]/', '', $codigo);
$error = '';
$firma = null;
$resultado = null;
$comparacion = null;

/** Oculta parte del DNI en una página pública: 55151151 -> 55****51 */
function dniOculto(?string $dni): string
{
    return $dni && strlen($dni) >= 8 ? substr($dni, 0, 2) . '****' . substr($dni, -2) : '';
}

/** Primeros bytes del archivo que cubre la firma n.° $orden (la revisión firmada). */
function revisionFirmada(string $pdf, int $orden): ?string
{
    preg_match_all('/\/ByteRange\s*\[\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*\]/', $pdf, $r, PREG_SET_ORDER);
    if (!isset($r[$orden - 1])) {
        return null;
    }
    return substr($pdf, 0, (int) $r[$orden - 1][3] + (int) $r[$orden - 1][4]);
}

if ($codigo !== '') {
    $clave = 'validar_firma_' . Seguridad::ipCliente();
    if (Seguridad::esperaIntentos($clave, 40, 600) > 0) {
        $error = 'Demasiadas consultas seguidas. Intente nuevamente en unos minutos.';
    } elseif (strlen($codigo) !== 10) {
        $error = 'El código de verificación tiene 10 caracteres (letras A-F y números).';
    } else {
        Seguridad::registrarIntento($clave, 600);
        $modelo = new Modelo_Firma();
        $firma = $modelo->Buscar_Por_Codigo($codigo);
        if (!$firma) {
            $error = 'No existe una firma registrada con el código ' . $codigo . '.';
        }
    }
}

if ($firma) {
    $ruta = $firma['anexo_ruta'] ?: $firma['archivo_firmado'];
    $completa = realpath(__DIR__ . '/' . $ruta);
    $raiz = realpath(__DIR__);
    $pdf = ($completa && strpos($completa, $raiz . DIRECTORY_SEPARATOR) === 0 && is_file($completa)) ? file_get_contents($completa) : null;

    $resultado = ['archivo' => false, 'valida' => false, 'integra' => false, 'total' => 0];
    if ($pdf !== null) {
        $resultado['archivo'] = true;
        $verificacion = FirmaDigital::verificar($pdf);
        $resultado['total'] = count($verificacion);
        $orden = (int) $firma['firma_orden'];
        $resultado['valida'] = !empty($verificacion[$orden - 1]['valida']);
        $revision = revisionFirmada($pdf, $orden);
        // La revisión que cubre esta firma es idéntica a la que se registró al firmar
        $resultado['integra'] = $revision !== null && hash_equals($firma['hash_firmado'], hash('sha256', $revision));
        $resultado['todas_validas'] = !in_array(false, array_column($verificacion, 'valida'), true);
    }

    $firmantes = $firma['anexo_id'] ? $modelo->Firmas_Del_Anexo($firma['anexo_id']) : [];

    // Comparar una copia que tenga el ciudadano o la entidad
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_FILES['copia']) && ($_FILES['copia']['error'] ?? 4) !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['copia'];
        if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            $comparacion = ['tipo' => 'error', 'texto' => 'No se pudo recibir el archivo. Intente nuevamente.'];
        } elseif ($f['size'] > 30 * 1048576) {
            $comparacion = ['tipo' => 'error', 'texto' => 'El archivo no debe pesar más de 30 MB.'];
        } else {
            $copia = file_get_contents($f['tmp_name']);
            @unlink($f['tmp_name']);
            $revisionOriginal = $pdf !== null ? revisionFirmada($pdf, (int) $firma['firma_orden']) : null;
            $revisionCopia = $revisionOriginal !== null ? substr($copia, 0, strlen($revisionOriginal)) : null;
            if (strncmp($copia, '%PDF', 4) !== 0) {
                $comparacion = ['tipo' => 'error', 'texto' => 'El archivo no es un PDF.'];
            } elseif ($pdf !== null && hash_equals(hash('sha256', $pdf), hash('sha256', $copia))) {
                $comparacion = ['tipo' => 'ok', 'texto' => 'La copia es idéntica al documento firmado que conserva la entidad.'];
            } elseif ($revisionCopia !== null && hash_equals($firma['hash_firmado'], hash('sha256', $revisionCopia))) {
                $comparacion = ['tipo' => 'ok', 'texto' => 'La copia contiene íntegra esta firma. Puede ser una versión anterior o posterior con otras firmas.'];
            } else {
                $comparacion = ['tipo' => 'mal', 'texto' => 'La copia NO corresponde al documento firmado con este código, o fue modificada después de la firma.'];
            }
        }
    }
}

$estadoOk = $resultado && $resultado['valida'] && $resultado['integra'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validar firma digital | <?= $e($institucion['sigla']) ?></title>
    <link rel="icon" href="<?= $e($institucion['logo']) ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --azul: #1E3A5F; --azul-2: #16304E; --texto: #2D3748; --suave: #718096; --borde: #E2E8F0;
                --verde: #15803D; --rojo: #B91C1C; --ambar: #B45309; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #F4F6F9; color: var(--texto); }
        header { background: var(--azul); color: #fff; }
        .barra { max-width: 820px; margin: 0 auto; padding: 1rem 16px; display: flex; align-items: center; gap: .85rem; }
        .barra img { width: 46px; height: 46px; object-fit: contain; background: #fff; border-radius: 8px; padding: 4px; }
        .barra b { display: block; font-size: 1rem; }
        .barra small { opacity: .85; }
        main { max-width: 820px; margin: 0 auto; padding: 1.5rem 16px 3rem; }
        h1 { font-size: 1.35rem; margin: 0 0 .35rem; color: var(--azul); }
        .intro { color: var(--suave); margin: 0 0 1.25rem; font-size: .92rem; }
        .tarjeta { background: #fff; border: 1px solid var(--borde); border-radius: 10px; padding: 1.25rem; margin-bottom: 1rem; }
        form.buscar { display: flex; gap: .5rem; flex-wrap: wrap; }
        input[type=text] { flex: 1 1 220px; font: inherit; font-size: 1.05rem; letter-spacing: .08em; text-transform: uppercase;
                           padding: .65rem .8rem; border: 1px solid #CBD5E0; border-radius: 6px; }
        input[type=text]:focus, input[type=file]:focus { outline: 2px solid var(--azul); outline-offset: 1px; }
        button { font: inherit; font-weight: 600; background: var(--azul); color: #fff; border: 0; border-radius: 6px; padding: .65rem 1.1rem; cursor: pointer; }
        button:hover { background: var(--azul-2); }
        .estado { display: flex; gap: .85rem; align-items: flex-start; border-radius: 10px; padding: 1rem 1.1rem; margin-bottom: 1rem; border: 1px solid; }
        .estado i { font-size: 1.6rem; margin-top: .1rem; }
        .estado b { display: block; font-size: 1.05rem; }
        .estado.ok { background: #F0FDF4; border-color: var(--verde); color: #14532D; } .estado.ok i { color: var(--verde); }
        .estado.mal { background: #FEF2F2; border-color: var(--rojo); color: #7F1D1D; } .estado.mal i { color: var(--rojo); }
        .estado.aviso { background: #FFFBEB; border-color: var(--ambar); color: #78350F; } .estado.aviso i { color: var(--ambar); }
        dl { display: grid; grid-template-columns: 180px 1fr; gap: .55rem 1rem; margin: 0; font-size: .92rem; }
        dt { color: var(--suave); } dd { margin: 0; font-weight: 500; overflow-wrap: anywhere; }
        h2 { font-size: 1rem; margin: 0 0 .85rem; color: var(--azul); }
        table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        th, td { text-align: left; padding: .5rem .4rem; border-bottom: 1px solid var(--borde); }
        th { color: var(--suave); font-weight: 600; white-space: nowrap; }
        tr.actual td { background: #EFF4F9; font-weight: 600; }
        .nota { font-size: .8rem; color: var(--suave); line-height: 1.5; }
        .error { color: var(--rojo); font-weight: 600; margin-top: .75rem; }
        @media (max-width: 576px) { dl { grid-template-columns: 1fr; gap: .1rem; } dd { margin-bottom: .55rem; } }
    </style>
</head>
<body>
<header>
    <div class="barra">
        <img src="<?= $e($institucion['logo']) ?>" alt="">
        <div><b><?= $e($institucion['razon']) ?></b><small>Validación de firma digital</small></div>
    </div>
</header>
<main>
    <h1>Validar firma digital</h1>
    <p class="intro">Ingrese el código de verificación impreso en el sello de firma o escanee su código QR.</p>

    <div class="tarjeta">
        <form class="buscar" method="get" action="validar_firma.php">
            <label for="codigo" class="sr-only" style="position:absolute;left:-9999px;">Código de verificación</label>
            <input type="text" id="codigo" name="codigo" maxlength="10" placeholder="Ej.: 9F4C8FC8DA" value="<?= $e($codigo) ?>" autocomplete="off" required>
            <button type="submit"><i class="fas fa-search"></i> Validar</button>
        </form>
        <?php if ($error): ?><div class="error"><i class="fas fa-exclamation-circle"></i> <?= $e($error) ?></div><?php endif; ?>
    </div>

    <?php if ($firma): ?>
        <?php if (!$resultado['archivo']): ?>
            <div class="estado mal"><i class="fas fa-times-circle"></i><div><b>No se pudo comprobar la firma</b>
                La firma está registrada, pero el archivo firmado ya no se encuentra en el servidor de la entidad.</div></div>
        <?php elseif ($estadoOk): ?>
            <div class="estado ok"><i class="fas fa-check-circle"></i><div><b>Firma válida</b>
                El documento firmado se conserva íntegro: no ha sido modificado desde que se firmó.</div></div>
        <?php else: ?>
            <div class="estado mal"><i class="fas fa-times-circle"></i><div><b>Firma no válida</b>
                El archivo que conserva la entidad no coincide con el que se firmó o su firma no supera la verificación criptográfica.</div></div>
        <?php endif; ?>

        <?php if ((int) $firma['cert_autofirmado'] === 1): ?>
            <div class="estado aviso"><i class="fas fa-exclamation-triangle"></i><div><b>Certificado no acreditado</b>
                La firma se hizo con un certificado autofirmado. Garantiza la integridad del documento, pero no fue emitido por una
                entidad de certificación acreditada ante INDECOPI, por lo que no equivale a una firma digital con plena validez legal.</div></div>
        <?php endif; ?>

        <div class="tarjeta">
            <h2>Datos de la firma</h2>
            <dl>
                <dt>Firmado por</dt><dd><?= $e($firma['firmante_nombre']) ?></dd>
                <?php if ($firma['firmante_dni']): ?><dt>DNI</dt><dd><?= $e(dniOculto($firma['firmante_dni'])) ?></dd><?php endif; ?>
                <dt>Fecha y hora</dt><dd><?= $e($firma['fecha_texto']) ?></dd>
                <?php if ($firma['motivo']): ?><dt>Motivo</dt><dd><?= $e($firma['motivo']) ?></dd><?php endif; ?>
                <?php if ($firma['area_nombre']): ?><dt>Área</dt><dd><?= $e($firma['area_nombre']) ?></dd><?php endif; ?>
                <dt>Expediente</dt><dd><?= $e($firma['doc_expediente'] ?: $firma['documento_id']) ?></dd>
                <dt>Documento</dt><dd><?= $e($firma['anexo_nombre'] ?: 'Documento del trámite') ?></dd>
                <dt>Emisor del certificado</dt><dd><?= $e($firma['cert_emisor'] ?: '—') ?></dd>
                <dt>Certificado vigente hasta</dt><dd><?= $e($firma['cert_hasta_texto']) ?></dd>
                <dt>Código</dt><dd><?= $e($firma['firma_codigo']) ?></dd>
            </dl>
        </div>

        <?php if (count($firmantes) > 1): ?>
            <div class="tarjeta">
                <h2>Firmas del documento (<?= count($firmantes) ?>)</h2>
                <table>
                    <thead><tr><th>N.°</th><th>Firmante</th><th>Fecha</th><th>Motivo</th></tr></thead>
                    <tbody>
                    <?php foreach ($firmantes as $f): ?>
                        <tr class="<?= $f['firma_codigo'] === $firma['firma_codigo'] ? 'actual' : '' ?>">
                            <td><?= (int) $f['firma_orden'] ?></td>
                            <td><?= $e($f['firmante_nombre']) ?></td>
                            <td><?= $e($f['fecha_texto']) ?></td>
                            <td><?= $e($f['motivo'] ?: '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="tarjeta">
            <h2>¿Tiene una copia del documento?</h2>
            <p class="nota" style="margin-top:0;">Súbala para comprobar que es la misma que se firmó. El archivo solo se compara; no se guarda.</p>
            <form method="post" enctype="multipart/form-data" action="validar_firma.php?codigo=<?= $e($firma['firma_codigo']) ?>" class="buscar">
                <label for="copia" style="position:absolute;left:-9999px;">Copia en PDF</label>
                <input type="file" id="copia" name="copia" accept="application/pdf,.pdf" required style="flex:1 1 220px;">
                <button type="submit"><i class="fas fa-balance-scale"></i> Comparar</button>
            </form>
            <?php if ($comparacion): ?>
                <div class="estado <?= $comparacion['tipo'] === 'ok' ? 'ok' : 'mal' ?>" style="margin:1rem 0 0;">
                    <i class="fas <?= $comparacion['tipo'] === 'ok' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                    <div><?= $e($comparacion['texto']) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <p class="nota">Esta página comprueba la firma con la copia que conserva la entidad. Si el certificado fue emitido por una entidad
            acreditada, también puede verificar el PDF en Adobe Acrobat Reader (panel «Firmas»).</p>
    <?php endif; ?>
</main>
</body>
</html>
