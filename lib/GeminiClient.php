<?php
/**
 * Cliente para Google Gemini API
 * Actualizado para Gemini 2.5 Flash
 */

require_once __DIR__ . '/../config/gemini_config.php';

class GeminiClient {
    
    private $api_key;
    private $api_url;
    private $model;
    
    public function __construct() {
        $this->api_key = GEMINI_API_KEY;
        $this->model = GEMINI_MODEL; // gemini-2.0-flash-exp
        
        // URL para Gemini 1.5 Flash
        $this->api_url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent?key=' . $this->api_key;
    }
    
    /**
     * Verifica si la API está configurada
     */
    public function isConfigured() {
        return !empty($this->api_key) && $this->api_key !== 'TU_API_KEY_AQUI';
    }
    
    /**
     * Envía una consulta al chat de Gemini
     */
    public function chat($user_message, $context_data = [], $user_role = 'Usuario', $user_area = 'Sin área') {
        
        // Construir el contexto
        $context_text = $this->buildContext($context_data, $user_role, $user_area);
        
        // Construir el prompt completo
        $full_prompt = SYSTEM_PROMPT . "\n\n" . 
                       "Contexto del usuario:\n" .
                       "- Rol: $user_role\n" .
                       "- Área: $user_area\n\n";
        
        if (!empty($context_text)) {
            $full_prompt .= "Datos relevantes encontrados:\n$context_text\n\n";
        }
        
        $full_prompt .= "Consulta del usuario: $user_message\n\n" .
                       "Respuesta:";
        
        // Preparar el payload para Gemini
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $full_prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => GEMINI_TEMPERATURE,
                'maxOutputTokens' => GEMINI_MAX_TOKENS,
                'topP' => 0.95,
                'topK' => 40
            ]
        ];
        
        // Realizar la petición a la API
        try {
            $response = $this->makeRequest($payload);
            return $response;
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Realiza la petición HTTP a la API de Gemini
     */
    private function makeRequest($payload) {
        
        $ch = curl_init($this->api_url);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        
        curl_close($ch);
        
        // Verificar errores de cURL
        if ($curl_error) {
            throw new Exception("Error de conexión: $curl_error");
        }
        
        // Decodificar respuesta
        $result = json_decode($response, true);
        
        // Log de errores para el administrador
        if ($http_code !== 200) {
            error_log("GEMINI API ERROR ($http_code): " . ($response ?: 'Empty response'));
            
            $error_message = isset($result['error']['message']) 
                ? $result['error']['message'] 
                : 'Error desconocido';
            
            // Detectar error de cuota excedida
            if ($http_code === 429 || strpos($error_message, 'quota') !== false || strpos($error_message, 'RESOURCE_EXHAUSTED') !== false) {
                throw new Exception("Se ha excedido el límite de consultas. Por favor, intenta nuevamente en unos minutos.");
            }
            
            // Detectar error de modelo no encontrado
            if ($http_code === 404 || strpos($error_message, 'not found') !== false) {
                throw new Exception("El servicio de IA no está disponible temporalmente (404 Not Found).");
            }
            
            // Detectar error de API key
            if ($http_code === 401 || $http_code === 403) {
                throw new Exception("Error de autenticación: Verifica tu API Key en gemini_config.php");
            }
            
            throw new Exception("Error de sincronización con la IA ($http_code).");
        }
        
        // Extraer el texto de la respuesta
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_response = $result['candidates'][0]['content']['parts'][0]['text'];
            
            return [
                'success' => true,
                'response' => $ai_response,
                'model' => $this->model
            ];
        } else {
            throw new Exception('Respuesta de API inválida: ' . json_encode($result));
        }
    }
    
    /**
     * Construye el texto de contexto a partir de los datos
     */
    private function buildContext($context_data, $user_role, $user_area) {
        if (empty($context_data)) {
            return '';
        }
        
        $context_text = '';
        
        // Documento específico
        if (isset($context_data['documento'])) {
            $doc = $context_data['documento'];
            $context_text .= "EXPEDIENTE ENCONTRADO:\n";
            $context_text .= "- Número: {$doc['documento_id']}\n";
            $context_text .= "- Tipo: {$doc['tipo_documento']}\n";
            $context_text .= "- Asunto: {$doc['doc_asunto']}\n";
            $context_text .= "- Remitente: {$doc['remitente']}\n";
            $context_text .= "- DNI: {$doc['doc_dniremitente']}\n";
            $context_text .= "- Fecha: {$doc['fecha_registro']}\n";
            $context_text .= "- Nro. Documento: {$doc['doc_nrodocumento']}\n";
            $context_text .= "- Folio: {$doc['doc_folio']}\n";
            
            if (isset($doc['area_actual'])) {
                $context_text .= "- Área actual: {$doc['area_actual']}\n";
            }
            
            if (isset($doc['area_origen'])) {
                $context_text .= "- Área origen: {$doc['area_origen']}\n";
            }
            
            if (isset($doc['doc_estatus'])) {
                $context_text .= "- Estado: {$doc['doc_estatus']}\n";
            }
            
            if (isset($doc['dias_pasados'])) {
                $context_text .= "- Días transcurridos: {$doc['dias_pasados']}\n";
            }
        }
        
        // Seguimiento
        if (isset($context_data['seguimiento']) && !empty($context_data['seguimiento'])) {
            $context_text .= "\nSEGUIMIENTO DEL EXPEDIENTE:\n";
            foreach ($context_data['seguimiento'] as $idx => $seg) {
                $num = $idx + 1;
                $context_text .= "$num. {$seg['fecha_movimiento']} - {$seg['area_destino']}\n";
                $context_text .= "   Acción: {$seg['mov_estatus']}\n";
                if (!empty($seg['mov_descripcion'])) {
                    $context_text .= "   Descripción: {$seg['mov_descripcion']}\n";
                }
            }
        }
        
        // Lista de documentos
        if (isset($context_data['documentos']) && !empty($context_data['documentos'])) {
            $total = $context_data['total_encontrados'] ?? count($context_data['documentos']);
            $context_text .= "\nDOCUMENTOS ENCONTRADOS ($total):\n";
            
            $max_show = 5; // Mostrar máximo 5
            $docs = array_slice($context_data['documentos'], 0, $max_show);
            
            foreach ($docs as $idx => $doc) {
                $num = $idx + 1;
                $context_text .= "$num. Exp. {$doc['documento_id']} - {$doc['tipo_documento']}\n";
                $context_text .= "   Asunto: {$doc['doc_asunto']}\n";
                $context_text .= "   Remitente: {$doc['remitente']}\n";
                $context_text .= "   Fecha: {$doc['fecha_registro']}\n";
                $context_text .= "   Estado: {$doc['doc_estatus']}\n";
            }
            
            if ($total > $max_show) {
                $restantes = $total - $max_show;
                $context_text .= "... y $restantes más\n";
            }
        }
        
        // Pendientes
        if (isset($context_data['documentos_pendientes']) && !empty($context_data['documentos_pendientes'])) {
            $total = $context_data['total_pendientes'] ?? count($context_data['documentos_pendientes']);
            $context_text .= "\nDOCUMENTOS PENDIENTES ($total):\n";
            
            foreach ($context_data['documentos_pendientes'] as $idx => $doc) {
                $num = $idx + 1;
                $context_text .= "$num. Exp. {$doc['documento_id']} - {$doc['tipo_documento']}\n";
                $context_text .= "   De: {$doc['remitente']}\n";
                $context_text .= "   Asunto: {$doc['doc_asunto']}\n";
                if (isset($doc['dias_pasados'])) {
                    $context_text .= "   Días de antigüedad: {$doc['dias_pasados']}\n";
                }
            }
        }
        
        // Estadísticas
        if (isset($context_data['estadisticas'])) {
            $stats = $context_data['estadisticas'];
            $context_text .= "\nESTADÍSTICAS DEL ÁREA:\n";
            $context_text .= "- Total de documentos: " . ($stats['total'] ?? 0) . "\n";
            $context_text .= "- Pendientes: " . ($stats['pendientes'] ?? 0) . "\n";
            $context_text .= "- Aceptados: " . ($stats['aceptados'] ?? 0) . "\n";
            $context_text .= "- Finalizados: " . ($stats['finalizados'] ?? 0) . "\n";
            $context_text .= "- Rechazados: " . ($stats['rechazados'] ?? 0) . "\n";
        }
        
        // Mensaje personalizado
        if (isset($context_data['mensaje'])) {
            $context_text .= "\n" . $context_data['mensaje'] . "\n";
        }
        
        return $context_text;
    }
}

?>