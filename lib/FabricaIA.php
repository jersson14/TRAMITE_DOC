<?php
/**
 * Entrega el cliente de IA que el administrador configuró en el panel.
 *
 * El resto del sistema pide el cliente aquí y no sabe si detrás está ChatGPT o
 * Gemini; cambiar de proveedor es cambiar un ajuste, no tocar código.
 */
require_once __DIR__ . '/Configuracion.php';
require_once __DIR__ . '/ClienteIA.php';
require_once __DIR__ . '/OpenAIClient.php';
require_once __DIR__ . '/GeminiClient.php';

class FabricaIA
{
    /**
     * @param string|null $proveedor fuerza uno (para la prueba de conexión del panel)
     * @param string|null $clave     clave a usar en vez de la guardada
     * @param string|null $modelo    modelo a usar en vez del guardado
     */
    public static function cliente(?string $proveedor = null, ?string $clave = null, ?string $modelo = null): ClienteIA
    {
        $elegido = $proveedor !== null && $proveedor !== '' ? $proveedor : Configuracion::obtener('ia_proveedor');
        if ($elegido === 'gemini') {
            return new GeminiClient($clave, $modelo);
        }
        // OpenAI es el proveedor por defecto
        return new OpenAIClient($clave, $modelo);
    }

    /** ¿El asistente inteligente está activo y con clave? */
    public static function activo(): bool
    {
        if (Configuracion::obtener('ia_activo') !== '1') {
            return false;
        }
        try {
            return self::cliente()->disponible();
        } catch (Throwable $e) {
            error_log('[IA] ' . $e->getMessage());
            return false;
        }
    }
}
