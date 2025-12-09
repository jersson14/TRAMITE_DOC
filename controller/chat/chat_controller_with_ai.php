<?php
/**
 * Controlador de Chat - VERSIÓN CON IA (GEMINI)
 * Usa la API de Gemini para generar respuestas inteligentes
 * 
 * INSTRUCCIONES PARA ACTIVAR:
 * 1. Asegúrate de tener una API key válida en config/gemini_config.php
 * 2. Renombra este archivo a: chat_controller.php
 * 3. Renombra el actual chat_controller.php a: chat_controller_mock.php (respaldo)
 */

session_start();

require_once __DIR__ . '/../../lib/GeminiClient.php';
require_once __DIR__ . '/../../model/model_chat.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar sesión
    if (!isset($_SESSION['S_ID'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Sesión no válida. Por favor, inicia sesión nuevamente.'
        ]);
        exit;
    }
    
    // Obtener mensaje del usuario
    $user_message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($user_message)) {
        echo json_encode([
            'success' => false,
            'error' => 'Por favor, escribe un mensaje.'
        ]);
        exit;
    }
    
    // Obtener información del usuario de la sesión
    $user_id = $_SESSION['S_ID'];
    $user_role = isset($_SESSION['S_ROL']) ? $_SESSION['S_ROL'] : 'Usuario';
    $user_area_id = isset($_SESSION['S_IDAREA']) ? $_SESSION['S_IDAREA'] : null;
    
    // Inicializar el modelo de chat
    $chat_model = new Modelo_Chat();
    
    // Analizar mensaje y obtener datos
    $context_data = analyzeAndFetchData($user_message, $chat_model, $user_area_id);
    
    // Inicializar cliente Gemini
    $gemini = new GeminiClient();
    
    // Generar respuesta con IA
    $response = $gemini->chat($user_message, $context_data);
    
    // Devolver respuesta (mapear 'response' a 'message')
    echo json_encode([
        'success' => true,
        'message' => $response['response'] ?? $response['message'] ?? 'No se pudo generar una respuesta.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

/**
 * Analizar mensaje y obtener datos relevantes
 */
function analyzeAndFetchData($message, $chat_model, $area_id) {
    $message_lower = strtolower($message);
    $context = [];
    
    // Detectar búsqueda de expediente
    if (preg_match('/(?:busca|buscar|expediente|documento|tramite)\s+(?:el\s+)?(?:expediente\s+)?([A-Z0-9]+)/i', $message, $matches)) {
        $numero = $matches[1];
        $documento = $chat_model->buscar_expediente($numero);
        if ($documento) {
            $context['documento'] = $documento;
            
            // Si encuentra documento, obtener seguimiento automáticamente
            $seguimiento = $chat_model->obtener_seguimiento($documento['documento_id']);
            if ($seguimiento) {
                $context['seguimiento'] = $seguimiento;
            }
        }
    }
    
    // Detectar solicitud de seguimiento explícita
    if (preg_match('/seguimiento|historial|rastreo/i', $message)) {
        if (isset($context['documento'])) {
            // Ya se obtuvo arriba
        } else {
            // Buscar número de expediente en el mensaje
            if (preg_match('/([A-Z0-9]+)/i', $message, $matches)) {
                $numero = $matches[1];
                $documento = $chat_model->buscar_expediente($numero);
                if ($documento) {
                    $seguimiento = $chat_model->obtener_seguimiento($documento['documento_id']);
                    if ($seguimiento) {
                        $context['seguimiento'] = $seguimiento;
                        $context['documento'] = $documento;
                    }
                }
            }
        }
    }
    
    // Detectar solicitud de pendientes
    if (preg_match('/pendiente|por\s+atender|sin\s+atender/i', $message) && $area_id) {
        $pendientes = $chat_model->listar_pendientes($area_id);
        if ($pendientes) {
            $context['documentos_pendientes'] = $pendientes;
            $context['total_pendientes'] = count($pendientes);
        }
    }
    
    // Detectar solicitud de estadísticas
    if (preg_match('/estadistica|resumen|total|cuantos/i', $message) && $area_id) {
        $stats = $chat_model->estadisticas_area($area_id);
        if ($stats) {
            $context['estadisticas'] = $stats;
        }
    }
    
    // Detectar búsqueda por remitente
    if (preg_match('/(?:de|remitente|enviado\s+por)\s+([a-záéíóúñ\s]+)/i', $message, $matches)) {
        $nombre = trim($matches[1]);
        if (strlen($nombre) > 2) {
            $documentos = $chat_model->buscar_por_remitente($nombre, $area_id);
            if ($documentos) {
                $context['documentos'] = $documentos;
                $context['total_encontrados'] = count($documentos);
            }
        }
    }
    
    return $context;
}

?>
