<?php
require_once __DIR__ . '/Configuracion.php';

/**
 * Consulta de DNI en RENIEC a través de apis.net.pe (migración 027).
 *
 * El sistema no se conecta a RENIEC directamente: usa apis.net.pe, un servicio
 * de terceros que consulta el padrón y cobra por consulta con un token.
 *
 * Antes el token estaba escrito en el código fuente (y subido al repositorio),
 * la consulta desactivaba la verificación SSL, no tenía tiempo límite y pasaba
 * el DNI sin validar a la URL. Ahora el token sale del panel de configuración,
 * el DNI se valida y la conexión se verifica.
 */
class ConsultaDni
{
    const URL = 'https://api.apis.net.pe/v2/reniec/dni?numero=';

    /** ¿Está habilitada y con token? */
    public static function disponible(): bool
    {
        return Configuracion::obtener('dni_activo') === '1' && Configuracion::obtener('dni_token') !== '';
    }

    /**
     * Consulta un DNI. Devuelve el cuerpo tal como responde apis.net.pe (las
     * pantallas ya leen ese formato: nombres, apellidoPaterno, apellidoMaterno).
     *
     * $token permite probar uno nuevo desde el panel antes de guardarlo.
     *
     * @throws RuntimeException con un mensaje para el usuario.
     */
    public static function consultar(string $dni, ?string $token = null): array
    {
        if (!preg_match('/^\d{8}$/', $dni)) {
            throw new RuntimeException('El DNI debe tener 8 dígitos.');
        }
        $token = $token ?? Configuracion::obtener('dni_token');
        if ($token === '') {
            throw new RuntimeException('La consulta de DNI no está configurada. El administrador debe cargar el token en Configuración.');
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => self::URL . $dni,
            CURLOPT_RETURNTRANSFER => true,
            // Antes estaba desactivado: cualquiera en el camino podía suplantar al servicio
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            // Antes era 0 (sin límite): una respuesta lenta colgaba la pantalla
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_MAXREDIRS      => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
            ],
        ]);
        $cuerpo = curl_exec($curl);
        $error = curl_error($curl);
        $codigo = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($cuerpo === false) {
            error_log('[DNI] ' . $error);
            throw new RuntimeException('No se pudo conectar con el servicio de consulta de DNI. Intente nuevamente.');
        }
        if ($codigo === 401 || $codigo === 403) {
            throw new RuntimeException('El servicio rechazó el token. El administrador debe revisarlo en Configuración.');
        }
        if ($codigo === 404 || $codigo === 422) {
            throw new RuntimeException('No se encontró ese DNI.');
        }
        if ($codigo === 429) {
            throw new RuntimeException('Se alcanzó el límite de consultas del servicio. Intente más tarde.');
        }
        $datos = json_decode((string) $cuerpo, true);
        if ($codigo !== 200 || !is_array($datos)) {
            error_log('[DNI] respuesta ' . $codigo . ': ' . mb_substr((string) $cuerpo, 0, 200));
            throw new RuntimeException('El servicio de consulta de DNI respondió con un error.');
        }
        return $datos;
    }
}
