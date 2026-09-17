<?php
/**
 * Ajustes del sistema que el administrador cambia desde el panel (migración 020).
 *
 * Solo se admiten las claves declaradas en CLAVES: así un formulario alterado
 * no puede escribir ajustes que no existen, y cada clave tiene su valor por
 * defecto para cuando la tabla todavía no lo tiene.
 */
require_once __DIR__ . '/../model/model_conexion.php';

class Configuracion
{
    /** clave => valor por defecto. */
    const CLAVES = [
        'ia_activo'        => '1',
        'ia_proveedor'     => 'openai',
        'ia_modelo'        => 'gpt-4.1-mini',
        'ia_clave'         => '',
        'ia_limite_minuto' => '10',
        'ia_limite_dia'    => '100',
    ];

    /** Proveedores admitidos: clave => [nombre visible, modelo sugerido]. */
    const PROVEEDORES = [
        'openai' => ['ChatGPT (OpenAI)', 'gpt-4.1-mini'],
        'gemini' => ['Gemini (Google)', 'gemini-2.5-flash'],
    ];

    private static $cache;

    /** Todos los ajustes, con los valores por defecto de los que falten. */
    public static function todo(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $valores = self::CLAVES;
        try {
            $pdo = (new conexionBD())->conexionPDO();
            foreach ($pdo->query('SELECT conf_clave, conf_valor FROM configuracion') as $fila) {
                if (array_key_exists($fila['conf_clave'], $valores)) {
                    $valores[$fila['conf_clave']] = (string) $fila['conf_valor'];
                }
            }
        } catch (Throwable $e) {
            // Sin la tabla (migración no aplicada) se trabaja con los valores por defecto
            error_log('[CONFIGURACION] ' . $e->getMessage());
        }
        return self::$cache = $valores;
    }

    public static function obtener(string $clave): string
    {
        $todo = self::todo();
        return $todo[$clave] ?? '';
    }

    public static function entero(string $clave, int $porDefecto): int
    {
        $valor = self::obtener($clave);
        return $valor === '' ? $porDefecto : (int) $valor;
    }

    /** Guarda solo las claves conocidas. Devuelve cuántas se escribieron. */
    public static function guardar(array $valores): int
    {
        $pdo = (new conexionBD())->conexionPDO();
        $consulta = $pdo->prepare(
            'INSERT INTO configuracion (conf_clave, conf_valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE conf_valor = VALUES(conf_valor)'
        );
        $escritas = 0;
        foreach ($valores as $clave => $valor) {
            if (!array_key_exists($clave, self::CLAVES)) {
                continue;
            }
            $consulta->execute([$clave, (string) $valor]);
            $escritas++;
        }
        self::$cache = null;
        return $escritas;
    }

    /** La clave de la API nunca sale entera del servidor: solo sus últimos 4. */
    public static function claveEnmascarada(): string
    {
        $clave = self::obtener('ia_clave');
        if ($clave === '') {
            return '';
        }
        return str_repeat('•', 8) . mb_substr($clave, -4);
    }
}
