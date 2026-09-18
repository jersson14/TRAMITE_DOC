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

        // Correo saliente (migración 027). Vacíos = se usa config/config_email.php,
        // para que una instalación que ya funcionaba no deje de enviar.
        'smtp_activo'           => '',
        'smtp_host'             => '',
        'smtp_puerto'           => '',
        'smtp_seguridad'        => '',
        'smtp_usuario'          => '',
        'smtp_clave'            => '',
        'smtp_remitente_nombre' => '',
        'smtp_remitente_correo' => '',

        // Consulta de DNI (RENIEC) a través de apis.net.pe (migración 027)
        'dni_activo' => '1',
        'dni_token'  => '',
    ];

    /** Ajustes que son secretos: nunca salen enteros del servidor. */
    const SECRETOS = ['ia_clave', 'smtp_clave', 'dni_token'];

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
    public static function claveEnmascarada(string $clave = 'ia_clave'): string
    {
        $valor = self::obtener($clave);
        if ($valor === '') {
            return '';
        }
        return str_repeat('•', 8) . mb_substr($valor, -4);
    }

    /**
     * Configuración de correo que se usa de verdad.
     *
     * Lo que se guardó en el panel manda. Si el panel todavía no se llenó, se usa
     * config/config_email.php (si existe), para que una instalación que ya enviaba
     * correos no deje de hacerlo al actualizar. El origen se informa para que la
     * pantalla pueda decir de dónde sale la configuración.
     */
    public static function smtp(): array
    {
        if (self::obtener('smtp_host') !== '') {
            return [
                'origen'    => 'panel',
                'activo'    => self::obtener('smtp_activo') === '1',
                'host'      => self::obtener('smtp_host'),
                'puerto'    => self::entero('smtp_puerto', 465),
                'seguridad' => self::obtener('smtp_seguridad'),
                'usuario'   => self::obtener('smtp_usuario'),
                'clave'     => self::obtener('smtp_clave'),
                'nombre'    => self::obtener('smtp_remitente_nombre'),
                'correo'    => self::obtener('smtp_remitente_correo'),
            ];
        }

        $archivo = __DIR__ . '/../config/config_email.php';
        if (is_file($archivo)) {
            require_once $archivo;
        }
        if (defined('EMAIL_HOST')) {
            return [
                'origen'    => 'archivo',
                'activo'    => defined('EMAIL_ENABLED') ? (bool) EMAIL_ENABLED : true,
                'host'      => (string) EMAIL_HOST,
                'puerto'    => defined('EMAIL_PORT') ? (int) EMAIL_PORT : 465,
                'seguridad' => defined('EMAIL_SECURE') ? (string) EMAIL_SECURE : 'ssl',
                'usuario'   => defined('EMAIL_USERNAME') ? (string) EMAIL_USERNAME : '',
                'clave'     => defined('EMAIL_PASSWORD') ? (string) EMAIL_PASSWORD : '',
                'nombre'    => defined('EMAIL_FROM_NAME') ? (string) EMAIL_FROM_NAME : '',
                'correo'    => defined('EMAIL_FROM_EMAIL') ? (string) EMAIL_FROM_EMAIL : '',
            ];
        }

        return ['origen' => 'ninguno', 'activo' => false, 'host' => '', 'puerto' => 465, 'seguridad' => 'ssl',
                'usuario' => '', 'clave' => '', 'nombre' => '', 'correo' => ''];
    }

    /** ¿Se envían correos? Tiene que estar activo y tener servidor configurado. */
    public static function correoActivo(): bool
    {
        $smtp = self::smtp();
        return $smtp['activo'] && $smtp['host'] !== '';
    }
}
