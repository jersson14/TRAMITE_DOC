<?php
/**
 * =====================================================
 * CONFIGURACIÓN SMTP - HOSTINGER
 * =====================================================
 */

define('EMAIL_HOST',       'smtp.hostinger.com');
define('EMAIL_PORT',        465);
define('EMAIL_SECURE',     'ssl');                        // Puerto 465 usa SSL
define('EMAIL_USERNAME',   'uteaperu@gradosapp.fun');
define('EMAIL_PASSWORD',   'Miranda1407.');
define('EMAIL_FROM_NAME',  'Sistema de Trámite Documentario');
define('EMAIL_FROM_EMAIL', 'uteaperu@gradosapp.fun');

// Activa o desactiva el envío de correos globalmente
define('EMAIL_ENABLED', true);
