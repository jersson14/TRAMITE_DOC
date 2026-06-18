<?php
/**
 * =====================================================
 * CONFIGURACIÓN SMTP - EJEMPLO
 * Copia este archivo como config_email.php y completa
 * con tus credenciales reales. Ese archivo NO se sube
 * al repositorio (ver .gitignore).
 * =====================================================
 */

define('EMAIL_HOST',       'smtp.tu-proveedor.com');
define('EMAIL_PORT',        465);
define('EMAIL_SECURE',     'ssl');                        // Puerto 465 usa SSL
define('EMAIL_USERNAME',   'usuario@tu-dominio.com');
define('EMAIL_PASSWORD',   'tu_password');
define('EMAIL_FROM_NAME',  'Sistema de Trámite Documentario');
define('EMAIL_FROM_EMAIL', 'usuario@tu-dominio.com');

// Activa o desactiva el envío de correos globalmente
define('EMAIL_ENABLED', true);
