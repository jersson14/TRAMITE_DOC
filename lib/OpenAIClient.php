<?php
/**
 * Cliente de OpenAI (ChatGPT) para el asistente del sistema.
 *
 * La clave y el modelo salen de la configuración que el administrador edita en
 * el panel, no de un archivo del servidor. Se usa la API de conversaciones
 * (/v1/chat/completions), que admite gpt-4o y los demás modelos de chat.
 */
require_once __DIR__ . '/ClienteIA.php';
require_once __DIR__ . '/Configuracion.php';

class OpenAIClient implements ClienteIA
{
    const URL = 'https://api.openai.com/v1/chat/completions';
    const SEGUNDOS_ESPERA = 45;

    private $clave;
    private $modelo;

    public function __construct(?string $clave = null, ?string $modelo = null)
    {
        $this->clave = $clave !== null ? $clave : Configuracion::obtener('ia_clave');
        $this->modelo = $modelo !== null && $modelo !== '' ? $modelo : Configuracion::obtener('ia_modelo');
        if ($this->modelo === '') {
            $this->modelo = 'gpt-4o';
        }
    }

    public function disponible(): bool
    {
        return trim($this->clave) !== '';
    }

    public function descripcion(): string
    {
        return 'ChatGPT (OpenAI) · ' . $this->modelo;
    }

    public function generarTexto(string $prompt, ?float $temperatura = null, ?int $maxTokens = null): string
    {
        if (!$this->disponible()) {
            throw new RuntimeException('Falta la clave de OpenAI en la configuración del sistema.');
        }

        $cuerpo = [
            'model' => $this->modelo,
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => $temperatura !== null ? $temperatura : 0.3,
            'max_tokens' => $maxTokens !== null ? $maxTokens : 900,
        ];

        $ch = curl_init(self::URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->clave,
            ],
            CURLOPT_POSTFIELDS => json_encode($cuerpo, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => self::SEGUNDOS_ESPERA,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $respuesta = curl_exec($ch);
        $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorRed = curl_error($ch);

        if ($errorRed !== '') {
            throw new RuntimeException('No se pudo conectar con OpenAI: ' . $errorRed);
        }

        $datos = json_decode((string) $respuesta, true);

        if ($codigo !== 200) {
            $mensaje = $datos['error']['message'] ?? 'respuesta inesperada';
            // La clave nunca debe aparecer en un log ni en pantalla
            error_log('[OPENAI] HTTP ' . $codigo . ': ' . $mensaje);
            throw new RuntimeException($this->explicar($codigo, $mensaje));
        }

        return trim((string) ($datos['choices'][0]['message']['content'] ?? ''));
    }

    /** Traduce el error de la API a algo que el administrador pueda accionar. */
    private function explicar(int $codigo, string $mensaje): string
    {
        if ($codigo === 401) {
            return 'OpenAI rechazó la clave (401). Revise la clave configurada.';
        }
        if ($codigo === 403) {
            return 'OpenAI no permite usar ese modelo con esta clave (403).';
        }
        if ($codigo === 404) {
            return 'El modelo "' . $this->modelo . '" no existe o no está disponible para esta clave (404).';
        }
        if ($codigo === 429) {
            return 'Se agotó la cuota o hay demasiadas consultas seguidas (429). ' . $mensaje;
        }
        if ($codigo >= 500) {
            return 'OpenAI está con problemas en este momento (' . $codigo . '). Intente más tarde.';
        }
        return 'OpenAI devolvió un error (' . $codigo . '): ' . $mensaje;
    }
}
