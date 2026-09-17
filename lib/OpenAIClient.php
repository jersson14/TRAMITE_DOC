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
    /** Intentos ante fallos pasajeros del proveedor. */
    const INTENTOS = 3;

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

    /**
     * Modelos de conversación de la cuenta, del más nuevo al más antiguo.
     * Se descartan los que no sirven para esto (audio, imágenes, embeddings) y
     * las variantes con fecha, que solo alargan la lista.
     */
    public function modelos(): array
    {
        if (!$this->disponible()) {
            throw new RuntimeException('Falta la clave de OpenAI para consultar los modelos.');
        }

        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->clave],
            CURLOPT_TIMEOUT => 30,
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
            throw new RuntimeException($this->explicar($codigo, $datos['error']['message'] ?? ''));
        }

        $utiles = [];
        foreach ($datos['data'] ?? [] as $modelo) {
            $id = (string) ($modelo['id'] ?? '');
            if (!preg_match('/^(gpt-[45]|o[134])/i', $id)) {
                continue;
            }
            // Fuera lo que no conversa y las variantes fechadas (gpt-4o-2024-08-06)
            if (preg_match('/audio|tts|transcribe|image|realtime|embedding|moderation|search|codex|instruct|diarize|\d{4}-\d{2}-\d{2}/i', $id)) {
                continue;
            }
            $utiles[] = $id;
        }

        // Primero los gpt-* del más nuevo al más antiguo, después la serie o*
        $gpt = array_values(array_filter($utiles, function (string $id): bool {
            return stripos($id, 'gpt') === 0;
        }));
        $otros = array_values(array_diff($utiles, $gpt));
        usort($gpt, function (string $a, string $b): int {
            return version_compare($b, $a);
        });
        rsort($otros, SORT_NATURAL);
        return array_merge($gpt, $otros);
    }

    public function generarTexto(string $prompt, ?float $temperatura = null, ?int $maxTokens = null): string
    {
        if (!$this->disponible()) {
            throw new RuntimeException('Falta la clave de OpenAI en la configuración del sistema.');
        }

        $limite = $maxTokens !== null ? $maxTokens : 900;
        $cuerpo = ['model' => $this->modelo, 'messages' => [['role' => 'user', 'content' => $prompt]]];

        if ($this->esDeRazonamiento()) {
            // Los modelos gpt-5 y o* rechazan max_tokens y solo admiten
            // temperature 1; además gastan tokens razonando antes de responder,
            // así que el límite se amplía o la respuesta llega vacía.
            $cuerpo['max_completion_tokens'] = max(600, $limite * 3);
        } else {
            $cuerpo['temperature'] = $temperatura !== null ? $temperatura : 0.3;
            $cuerpo['max_tokens'] = $limite;
        }

        return $this->pedir($cuerpo);
    }

    /** ¿El modelo elegido es de la familia que razona antes de responder? */
    private function esDeRazonamiento(): bool
    {
        return (bool) preg_match('/^(gpt-5|o1|o3|o4)/i', $this->modelo);
    }

    /**
     * Hace la llamada, con dos tolerancias aprendidas en pruebas reales:
     *
     * - El borde de red de OpenAI (Cloudflare) devuelve de vez en cuando un 404
     *   con el cuerpo vacío, sin que el modelo tenga nada que ver. Eso, y los
     *   errores 5xx, se reintentan antes de darlos por fallo.
     * - Si la API objeta un parámetro que enviamos, se quita y se reintenta:
     *   los modelos nuevos cambiaron max_tokens por max_completion_tokens y
     *   fijaron la temperatura.
     */
    private function pedir(array $cuerpo, bool $adaptado = false): string
    {
        for ($intento = 1; $intento <= self::INTENTOS; $intento++) {
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
            $respuesta = (string) curl_exec($ch);
            $codigo = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $errorRed = curl_error($ch);

            if ($errorRed !== '') {
                if ($intento < self::INTENTOS) {
                    usleep(500000);
                    continue;
                }
                throw new RuntimeException('No se pudo conectar con OpenAI: ' . $errorRed);
            }

            $datos = json_decode($respuesta, true);
            if ($codigo === 200) {
                return trim((string) ($datos['choices'][0]['message']['content'] ?? ''));
            }

            $mensaje = (string) ($datos['error']['message'] ?? '');
            // La clave nunca debe aparecer en un log ni en pantalla
            error_log('[OPENAI] HTTP ' . $codigo . ' (intento ' . $intento . '): '
                . ($mensaje !== '' ? $mensaje : 'sin detalle'));

            // Fallo pasajero: cuerpo vacío o error del servidor
            if (($mensaje === '' || $codigo >= 500) && $intento < self::INTENTOS) {
                usleep(600000);
                continue;
            }

            $sobrante = $this->parametroObjetado($mensaje, $cuerpo);
            if (!$adaptado && $sobrante !== null) {
                unset($cuerpo[$sobrante]);
                if ($sobrante === 'max_tokens') {
                    $cuerpo['max_completion_tokens'] = 1800;
                }
                return $this->pedir($cuerpo, true);
            }
            throw new RuntimeException($this->explicar($codigo, $mensaje));
        }

        throw new RuntimeException('OpenAI no respondió después de ' . self::INTENTOS . ' intentos.');
    }

    /**
     * Si el error se queja de un parámetro que enviamos, devuelve cuál.
     * Los modelos nuevos cambian estas reglas (max_tokens pasó a
     * max_completion_tokens, temperature quedó fija en 1), y así el sistema se
     * adapta sin tener que listar cada modelo.
     */
    private function parametroObjetado(string $mensaje, array $cuerpo): ?string
    {
        if (!preg_match('/unsupported (?:parameter|value)[:\s]+.{0,3}([a-z_]+)/i', $mensaje, $m)) {
            return null;
        }
        $parametro = strtolower($m[1]);
        return array_key_exists($parametro, $cuerpo) ? $parametro : null;
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
            // Un 404 sin explicación no es culpa del modelo: es el borde de red
            return $mensaje === ''
                ? 'OpenAI respondió 404 sin detalle, lo que suele ser un fallo momentáneo de su red. Vuelva a intentar.'
                : 'El modelo "' . $this->modelo . '" no está disponible para esta clave (404): ' . $mensaje;
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
