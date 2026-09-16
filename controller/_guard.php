<?php
// Protege un controlador interno: exige sesión activa y token CSRF válido.
require_once __DIR__ . '/../lib/Seguridad.php';
Seguridad::requiereLogin();
Seguridad::verificarCsrf();
