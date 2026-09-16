<?php
/**
 * Utilidades de seguridad compartidas: sesión, control de acceso por rol,
 * token CSRF, validación de archivos subidos y límite de intentos.
 */
date_default_timezone_set('America/Lima');

class Seguridad
{
    const ROL_ADMIN = 'Administrador';
    const ROL_SECRETARIO = 'Secretario (a)';

    const INACTIVIDAD_MAX = 7200; // segundos sin actividad antes de cerrar la sesión
    const NOMBRE_SESION = 'SISTRAMITE_SID';

    const MIME_PDF = ['application/pdf' => 'pdf'];
    const MIME_IMAGEN = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function iniciarSesion(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name(self::NOMBRE_SESION);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        if (isset($_SESSION['S_ID'])) {
            $ultima = $_SESSION['S_ULTIMA_ACTIVIDAD'] ?? time();
            if (time() - $ultima > self::INACTIVIDAD_MAX) {
                self::cerrarSesion();
                session_start();
                return;
            }
            $_SESSION['S_ULTIMA_ACTIVIDAD'] = time();
        }
    }

    public static function cerrarSesion(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION = [];
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        session_destroy();
    }

    /** Guarda en sesión los datos del usuario autenticado (fila de SP_VERIFICAR_USUARIO). */
    public static function establecerUsuario(array $u): void
    {
        session_regenerate_id(true);
        $_SESSION['S_ID']             = $u['usu_id'];
        $_SESSION['S_USU']            = $u['usu_usuario'];
        $_SESSION['S_IDAREA']         = $u['area_id'];
        $_SESSION['S_ROL']            = $u['usu_rol'];
        $_SESSION['S_AREA']           = $u['area_nombre'];
        $_SESSION['S_APELLIDOS']      = $u['emple_nombre'];
        $_SESSION['S_NOMBRE']         = $u['USUARIO'];
        $_SESSION['S_FOTO']           = $u['empl_fotoperfil'];
        $_SESSION['S_FOTO_EMPRESA']   = $u['emp_logo'];
        $_SESSION['S_RAZON']          = $u['emp_razon'];
        $_SESSION['S_ULTIMA_ACTIVIDAD'] = time();
        unset($_SESSION['S_CSRF']);
    }

    public static function autenticado(): bool
    {
        return isset($_SESSION['S_ID']);
    }

    public static function usuarioId(): int
    {
        return (int) ($_SESSION['S_ID'] ?? 0);
    }

    public static function areaId(): int
    {
        return (int) ($_SESSION['S_IDAREA'] ?? 0);
    }

    public static function rol(): string
    {
        return (string) ($_SESSION['S_ROL'] ?? '');
    }

    public static function esAdmin(): bool
    {
        return self::rol() === self::ROL_ADMIN;
    }

    /** Para controladores AJAX: responde JSON 401/403 si no hay sesión o el rol no está permitido. */
    public static function requiereLogin(array $roles = []): void
    {
        self::iniciarSesion();
        if (!self::autenticado()) {
            self::responderError(401, 'Su sesión expiró. Inicie sesión nuevamente.');
        }
        if ($roles && !in_array(self::rol(), $roles, true)) {
            self::responderError(403, 'No tiene permisos para realizar esta operación.');
        }
    }

    /** Para vistas parciales cargadas dentro del panel: muestra un aviso en lugar del contenido. */
    public static function requiereVista(array $roles = []): void
    {
        self::iniciarSesion();
        if (!self::autenticado()) {
            http_response_code(401);
            echo '<div class="alert alert-warning m-4">Su sesión expiró. <a href="../index.php">Inicie sesión nuevamente</a>.</div>';
            exit;
        }
        if ($roles && !in_array(self::rol(), $roles, true)) {
            http_response_code(403);
            echo '<div class="alert alert-danger m-4">No tiene permisos para acceder a esta sección.</div>';
            exit;
        }
    }

    public static function responderError(int $codigo, string $mensaje): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'codigo' => $codigo, 'mensaje' => $mensaje]);
        exit;
    }

    public static function tokenCsrf(): string
    {
        if (empty($_SESSION['S_CSRF'])) {
            $_SESSION['S_CSRF'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['S_CSRF'];
    }

    public static function verificarCsrf(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? '');
        if (!is_string($token) || !hash_equals(self::tokenCsrf(), $token)) {
            self::responderError(403, 'El token de seguridad no es válido. Recargue la página.');
        }
    }

    public static function e($valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Valida y guarda un archivo subido con un nombre generado por el servidor.
     * Devuelve el nombre guardado, o null si no se envió archivo.
     * @throws RuntimeException si el archivo no es válido.
     */
    public static function guardarArchivo(string $campo, string $directorio, array $mimes, int $maxBytes, string $prefijo): ?string
    {
        if (empty($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $archivo = $_FILES[$campo];
        if ($archivo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'])) {
            throw new RuntimeException('No se pudo recibir el archivo.');
        }
        if ($archivo['size'] > $maxBytes) {
            throw new RuntimeException('El archivo supera el tamaño máximo de ' . round($maxBytes / 1048576) . ' MB.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!isset($mimes[$mime])) {
            throw new RuntimeException('Tipo de archivo no permitido.');
        }
        if (!is_dir($directorio)) {
            mkdir($directorio, 0775, true);
        }
        $nombre = $prefijo . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $mimes[$mime];
        if (!move_uploaded_file($archivo['tmp_name'], rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . $nombre)) {
            throw new RuntimeException('No se pudo guardar el archivo.');
        }
        return $nombre;
    }

    /** Borra un archivo solo si está dentro del directorio indicado (evita rutas manipuladas). */
    public static function borrarArchivoEn(string $directorio, string $rutaRelativa, array $protegidos = []): void
    {
        $nombre = basename($rutaRelativa);
        if ($nombre === '' || in_array($nombre, $protegidos, true)) {
            return;
        }
        $base = realpath($directorio);
        $destino = realpath($directorio . DIRECTORY_SEPARATOR . $nombre);
        if ($base && $destino && strpos($destino, $base . DIRECTORY_SEPARATOR) === 0 && is_file($destino)) {
            unlink($destino);
        }
    }

    public static function ipCliente(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /** Segundos que faltan para permitir otro intento (0 si está permitido). */
    public static function esperaIntentos(string $clave, int $maximo, int $ventana): int
    {
        $registro = self::leerIntentos($clave);
        $recientes = array_filter($registro, fn($t) => $t > time() - $ventana);
        if (count($recientes) < $maximo) {
            return 0;
        }
        return max(1, min($recientes) + $ventana - time());
    }

    public static function registrarIntento(string $clave, int $ventana): void
    {
        $registro = array_values(array_filter(self::leerIntentos($clave), fn($t) => $t > time() - $ventana));
        $registro[] = time();
        file_put_contents(self::archivoIntentos($clave), json_encode($registro), LOCK_EX);
    }

    public static function limpiarIntentos(string $clave): void
    {
        $archivo = self::archivoIntentos($clave);
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }

    private static function leerIntentos(string $clave): array
    {
        $archivo = self::archivoIntentos($clave);
        if (!is_file($archivo)) {
            return [];
        }
        $datos = json_decode((string) file_get_contents($archivo), true);
        return is_array($datos) ? $datos : [];
    }

    private static function archivoIntentos(string $clave): string
    {
        $dir = __DIR__ . '/../storage/intentos';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir . '/' . hash('sha256', $clave) . '.json';
    }
}
