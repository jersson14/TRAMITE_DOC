<?php
/**
 * Controlador de Chat - VERSIÓN DE PRUEBA SIN IA
 * Simula respuestas para probar la funcionalidad
 */

session_start();

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
    $user_area_name = isset($_SESSION['S_AREA']) ? $_SESSION['S_AREA'] : 'Sin área';
    
    // Inicializar el modelo de chat
    $chat_model = new Modelo_Chat();
    
    // Analizar mensaje y obtener datos
    $context_data = analyzeAndFetchData($user_message, $chat_model, $user_area_id);
    
    // GENERAR RESPUESTA SIMULADA (sin usar IA)
    $response_message = generateMockResponse($user_message, $context_data, $user_area_name);
    
    // Devolver respuesta
    echo json_encode([
        'success' => true,
        'message' => $response_message
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ]);
}

/**
 * Generar respuesta simulada basada en los datos
 */
function generateMockResponse($message, $context_data, $user_area) {
    $message_lower = strtolower($message);
    
    // Respuesta para búsqueda de expediente
    if (isset($context_data['documento']) && $context_data['documento']) {
        $doc = $context_data['documento'];
        return "📄 **Expediente encontrado: {$doc['documento_id']}**\n\n" .
               "**Información del documento:**\n" .
               "- Tipo: {$doc['tipo_documento']}\n" .
               "- Asunto: {$doc['doc_asunto']}\n" .
               "- Remitente: {$doc['remitente']}\n" .
               "- Estado: {$doc['doc_estatus']}\n" .
               "- Fecha: {$doc['fecha_registro']}\n" .
               "- Área actual: {$doc['area_actual']}\n" .
               "- Días transcurridos: {$doc['dias_pasados']} días\n\n" .
               "¿Necesitas ver el seguimiento de este expediente?";
    }
    
    // Respuesta para seguimiento
    if (isset($context_data['seguimiento']) && !empty($context_data['seguimiento'])) {
        $response = "📋 **Seguimiento del expediente:**\n\n";
        foreach ($context_data['seguimiento'] as $idx => $seg) {
            $num = $idx + 1;
            $response .= "$num. {$seg['fecha_movimiento']} - {$seg['area_destino']}\n";
            $response .= "   Estado: {$seg['mov_estatus']}\n";
            if (!empty($seg['mov_descripcion'])) {
                $response .= "   Descripción: {$seg['mov_descripcion']}\n";
            }
            $response .= "\n";
        }
        return $response;
    }
    
    // Respuesta para pendientes
    if (isset($context_data['documentos_pendientes']) && !empty($context_data['documentos_pendientes'])) {
        $total = count($context_data['documentos_pendientes']);
        $response = "📌 **Tienes $total documentos pendientes en $user_area:**\n\n";
        
        foreach ($context_data['documentos_pendientes'] as $idx => $doc) {
            $num = $idx + 1;
            $response .= "$num. Exp. {$doc['documento_id']} - {$doc['tipo_documento']}\n";
            $response .= "   De: {$doc['remitente']}\n";
            $response .= "   Asunto: {$doc['doc_asunto']}\n";
            if (isset($doc['dias_pasados'])) {
                $response .= "   Antigüedad: {$doc['dias_pasados']} días\n";
            }
            $response .= "\n";
        }
        
        return $response;
    }
    
    // Respuesta para estadísticas
    if (isset($context_data['estadisticas'])) {
        $stats = $context_data['estadisticas'];
        return "📊 **Estadísticas de $user_area:**\n\n" .
               "- Total de documentos: {$stats['total']}\n" .
               "- Pendientes: {$stats['pendientes']}\n" .
               "- Aceptados: {$stats['aceptados']}\n" .
               "- Finalizados: {$stats['finalizados']}\n" .
               "- Rechazados: {$stats['rechazados']}\n\n" .
               "¿Necesitas más información sobre algún documento específico?";
    }
    
    // Respuesta para búsqueda por remitente
    if (isset($context_data['documentos']) && !empty($context_data['documentos'])) {
        $total = count($context_data['documentos']);
        $response = "🔍 **Encontré $total documentos:**\n\n";
        
        foreach ($context_data['documentos'] as $idx => $doc) {
            $num = $idx + 1;
            $response .= "$num. Exp. {$doc['documento_id']} - {$doc['tipo_documento']}\n";
            $response .= "   Asunto: {$doc['doc_asunto']}\n";
            $response .= "   Estado: {$doc['doc_estatus']}\n";
            $response .= "   Fecha: {$doc['fecha_registro']}\n\n";
        }
        
        return $response;
    }
    
    // Saludos
    if (strpos($message_lower, 'hola') !== false || strpos($message_lower, 'buenos') !== false) {
        return "¡Hola! 👋 Soy tu asistente virtual de SISTRAMITEDOC.\n\n" .
               "Puedo ayudarte con:\n" .
               "- Buscar expedientes por número\n" .
               "- Ver documentos pendientes\n" .
               "- Consultar estadísticas\n" .
               "- Ver seguimiento de documentos\n\n" .
               "¿En qué puedo ayudarte?";
    }
    
    // Ayuda
    if (strpos($message_lower, 'ayuda') !== false || strpos($message_lower, 'qué puedes') !== false) {
        return "🤖 **Puedo ayudarte con:**\n\n" .
               "1. **Buscar expediente:** \"Busca el expediente 0112\"\n" .
               "2. **Ver pendientes:** \"¿Cuántos documentos tengo pendientes?\"\n" .
               "3. **Estadísticas:** \"Muéstrame las estadísticas\"\n" .
               "4. **Seguimiento:** \"Seguimiento del expediente D0000033\"\n" .
               "5. **Por remitente:** \"Documentos de Juan Pérez\"\n\n" .
               "¿Qué consulta necesitas hacer?";
    }
    
    // Respuesta por defecto
    return "No encontré información para tu consulta.\n\n" .
           "Intenta con:\n" .
           "- \"Busca el expediente [número]\"\n" .
           "- \"¿Cuántos documentos tengo pendientes?\"\n" .
           "- \"Muéstrame las estadísticas\"\n" .
           "- \"Seguimiento del expediente [número]\"";
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
        }
    }
    
    // Detectar solicitud de seguimiento
    if (preg_match('/seguimiento|historial|rastreo/i', $message) && isset($context['documento'])) {
        $seguimiento = $chat_model->obtener_seguimiento($context['documento']['documento_id']);
        if ($seguimiento) {
            $context['seguimiento'] = $seguimiento;
        }
    }
    
    // Detectar solicitud de pendientes
    if (preg_match('/pendiente|por\s+atender|sin\s+atender/i', $message) && $area_id) {
        $pendientes = $chat_model->listar_pendientes($area_id);
        if ($pendientes) {
            $context['documentos_pendientes'] = $pendientes;
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
            }
        }
    }
    
    return $context;
}

?>
