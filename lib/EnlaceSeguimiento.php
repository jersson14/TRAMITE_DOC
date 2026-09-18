<?php
/**
 * Enlace firmado de seguimiento: lo que va en el QR del ticket y del cargo.
 *
 * Los códigos de trámite son correlativos (D0000041, D0000042...), así que no
 * bastan para mostrar un trámite sin el DNI: cualquiera podría recorrerlos.
 * El QR lleva además una firma (HMAC) del código hecha con una clave que solo
 * conoce el servidor. Quien escanea el ticket tiene la firma y ve el trámite al
 * instante; quien solo conoce o adivina el código sigue necesitando el DNI.
 *
 * La clave se crea sola la primera vez en config/clave_enlaces.php (fuera del
 * repositorio). Si se borra o se cambia, los QR ya impresos dejan de abrir el
 * trámite directo, pero siguen llevando a la consulta con código y DNI.
 */
class EnlaceSeguimiento
{
    const ARCHIVO_CLAVE = __DIR__ . '/../config/clave_enlaces.php';
    const LARGO = 20; // caracteres hex del token: 80 bits, imposible de adivinar con el límite de intentos

    /** Token del trámite para el QR. */
    public static function token(string $codigo): string
    {
        return substr(hash_hmac('sha256', strtoupper(trim($codigo)), self::clave()), 0, self::LARGO);
    }

    /** ¿El token corresponde a este código? */
    public static function valido(string $codigo, string $token): bool
    {
        $token = strtolower(trim($token));
        return strlen($token) === self::LARGO && hash_equals(self::token($codigo), $token);
    }

    private static function clave(): string
    {
        static $clave = null;
        if ($clave !== null) {
            return $clave;
        }
        if (!is_file(self::ARCHIVO_CLAVE)) {
            // Modo 'x': si dos peticiones llegan a la vez, solo una crea el archivo
            // y ambas terminan usando la misma clave.
            $f = @fopen(self::ARCHIVO_CLAVE, 'x');
            if ($f) {
                fwrite($f, "<?php\n// Clave de los enlaces de seguimiento (QR). Generada automáticamente: no compartir ni versionar.\nreturn '"
                    . bin2hex(random_bytes(32)) . "';\n");
                fclose($f);
            }
        }
        $clave = is_file(self::ARCHIVO_CLAVE) ? (string) (require self::ARCHIVO_CLAVE) : '';
        if (strlen($clave) < 32) {
            throw new RuntimeException('No se pudo leer ni crear la clave de enlaces en config/clave_enlaces.php.');
        }
        return $clave;
    }
}
