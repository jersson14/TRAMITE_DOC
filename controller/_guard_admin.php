<?php
// Protege un controlador de administración: exige rol Administrador y token CSRF válido.
require_once __DIR__ . '/../lib/Seguridad.php';
Seguridad::requiereLogin([Seguridad::ROL_ADMIN]);
Seguridad::verificarCsrf();
