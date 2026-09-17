<?php
/**
 * Endpoint del asistente del chat.
 *
 * Con la IA configurada, traduce la pregunta a una consulta SELECT sobre la
 * base (AsistenteBD + ConsultaSegura) y devuelve la respuesta redactada junto
 * con las filas, para que el chat las muestre en una tabla. Sin clave de IA
 * responde con las consultas fijas de siempre (Modelo_Chat), para que el chat
 * siga sirviendo aunque no haya servicio.
 *
 * Toda consulta queda en la bitácora: el asistente puede leer datos de
 * expedientes, así que debe ser auditable.
 */
require_once __DIR__ . '/../_guard.php';
require_once __DIR__ . '/../../lib/Bitacora.php';
require_once __DIR__ . '/../../lib/AsistenteBD.php';
require_once __DIR__ . '/../../lib/Configuracion.php';
require_once __DIR__ . '/../../model/model_chat.php';

header('Content-Type: application/json; charset=utf-8');

$pregunta = trim((string) ($_POST['message'] ?? ''));
if ($pregunta === '') {
    Seguridad::responderError(422, 'Escribe tu pregunta.');
}
if (mb_strlen($pregunta) > 500) {
    Seguridad::responderError(422, 'La pregunta es demasiado larga.');
}

if (!limiteDeConsultas()) {
    Seguridad::responderError(429, 'Has hecho muchas consultas seguidas. Espera un momento.');
}

$esAdmin = ($_SESSION['S_ROL'] ?? '') === Seguridad::ROL_ADMIN;
$areaId = (int) ($_SESSION['S_IDAREA'] ?? 0);
$areaNombre = (string) ($_SESSION['S_AREA'] ?? 'sin área');
$rol = (string) ($_SESSION['S_ROL'] ?? 'Usuario');

$asistente = new AsistenteBD($esAdmin, $areaId, $areaNombre, $rol);

// El asistente inteligente puede estar apagado o sin clave: entonces, lo básico
if (Configuracion::obtener('ia_activo') !== '1' || !$asistente->disponible()) {
    echo json_encode([
        'success' => true,
        'modo'    => 'basico',
        'message' => respuestaBasica($pregunta, $areaId, $areaNombre),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $r = $asistente->responder($pregunta);
    Bitacora::registrar('CONSULTA_ASISTENTE', 'chat', Configuracion::obtener('ia_proveedor'), mb_substr($pregunta, 0, 150)
        . ($r['sql'] ? ' · ' . mb_substr($r['sql'], 0, 300) : ' · sin consulta'));

    echo json_encode([
        'success'   => true,
        'modo'      => 'ia',
        'message'   => $r['mensaje'],
        'tabla'     => $r['tabla'],
        'total'     => $r['total'],
        'recortada' => $r['recortada'],
        // El SQL se muestra solo al administrador, para que pueda revisarlo
        'sql'       => $esAdmin ? $r['sql'] : null,
    ], JSON_UNESCAPED_UNICODE);
} catch (RuntimeException $e) {
    // La consulta generada no pasó la validación: se le dice al usuario
    error_log('[CHAT] ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    // Falló el servicio de IA (clave, cuota, red): se responde con lo básico
    error_log('[CHAT] servicio de IA: ' . $e->getMessage());
    echo json_encode([
        'success' => true,
        'modo'    => 'basico',
        'aviso'   => 'El asistente inteligente no está disponible ahora mismo; respondo con las consultas básicas.',
        'message' => respuestaBasica($pregunta, $areaId, $areaNombre),
    ], JSON_UNESCAPED_UNICODE);
}

/** Tope de consultas por minuto y por día, contadas en la sesión. */
function limiteDeConsultas(): bool
{
    $porMinuto = Configuracion::entero('ia_limite_minuto', 10);
    $porDia = Configuracion::entero('ia_limite_dia', 100);

    $ahora = time();
    $conteo = $_SESSION['S_CHAT_CONTEO'] ?? ['minuto' => 0, 'desde' => $ahora, 'dia' => 0, 'fecha' => date('Y-m-d')];

    if ($conteo['fecha'] !== date('Y-m-d')) {
        $conteo['dia'] = 0;
        $conteo['fecha'] = date('Y-m-d');
    }
    if ($ahora - $conteo['desde'] >= 60) {
        $conteo['minuto'] = 0;
        $conteo['desde'] = $ahora;
    }
    $conteo['minuto']++;
    $conteo['dia']++;
    $_SESSION['S_CHAT_CONTEO'] = $conteo;

    return $conteo['minuto'] <= $porMinuto && $conteo['dia'] <= $porDia;
}

/**
 * Respuestas sin IA: las consultas fijas que ya existían.
 * Sirven cuando el asistente está apagado, sin clave o el proveedor falla.
 */
function respuestaBasica(string $pregunta, int $areaId, string $areaNombre): string
{
    $modelo = new Modelo_Chat();
    $texto = mb_strtolower($pregunta);

    if (preg_match('/\b([A-Z]{3}-\d{4}-\d{6}|D\d{7}|\d{3,})\b/i', $pregunta, $m)) {
        $documento = $modelo->buscar_expediente($m[1]);
        if ($documento) {
            return "**Expediente {$documento['doc_nrodocumento']}**\n"
                . "- Asunto: {$documento['doc_asunto']}\n"
                . "- Remitente: {$documento['remitente']}\n"
                . "- Estado: {$documento['doc_estatus']}\n"
                . "- Área actual: " . ($documento['area_actual'] ?? '—') . "\n"
                . "- Registrado: {$documento['fecha_registro']}";
        }
        return 'No encontré ese expediente.';
    }

    if (preg_match('/pendiente|por atender|sin atender/', $texto) && $areaId) {
        $pendientes = $modelo->listar_pendientes($areaId);
        $total = count($pendientes ?: []);
        if (!$total) {
            return "No hay trámites pendientes en $areaNombre.";
        }
        $lista = '';
        foreach (array_slice($pendientes, 0, 10) as $p) {
            $lista .= "- {$p['doc_asunto']} (de {$p['remitente']})\n";
        }
        return "**$total trámite(s) pendiente(s) en $areaNombre**\n" . $lista;
    }

    if (preg_match('/estad[ií]stica|resumen|cu[aá]nto|total/', $texto) && $areaId) {
        $e = $modelo->estadisticas_area($areaId);
        if ($e) {
            return "**Resumen de $areaNombre**\n"
                . '- Total: ' . ($e['total'] ?? 0) . "\n"
                . '- Pendientes: ' . ($e['pendientes'] ?? 0) . "\n"
                . '- Aceptados: ' . ($e['aceptados'] ?? 0) . "\n"
                . '- Finalizados: ' . ($e['finalizados'] ?? 0) . "\n"
                . '- Rechazados: ' . ($e['rechazados'] ?? 0);
        }
    }

    return "El asistente inteligente no está configurado, así que solo puedo responder consultas "
        . "básicas: pendientes de tu área, un resumen de estados o la búsqueda de un expediente por su número.";
}
