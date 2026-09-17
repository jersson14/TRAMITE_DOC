<?php
/**
 * Lo único que el asistente necesita de un proveedor de IA.
 *
 * El sistema puede trabajar con ChatGPT (OpenAI) o con Gemini (Google) según
 * lo que el administrador elija en el panel; el resto del código no distingue
 * uno de otro. Quien implemente esto debe lanzar RuntimeException con un
 * mensaje en español cuando el servicio falle (clave inválida, cuota agotada,
 * red caída), porque ese texto se le muestra a quien administra.
 */
interface ClienteIA
{
    /** ¿Está configurado para poder llamarse? (clave presente, etc.) */
    public function disponible(): bool;

    /** Nombre del proveedor y modelo, para mostrarlo y registrarlo. */
    public function descripcion(): string;

    /** Devuelve el texto generado, o cadena vacía si el modelo no respondió. */
    public function generarTexto(string $prompt, ?float $temperatura = null, ?int $maxTokens = null): string;
}
