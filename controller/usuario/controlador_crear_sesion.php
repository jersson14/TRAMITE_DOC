<?php
    // Obsoleto: la sesión se crea en el servidor al validar las credenciales
    // (controlador_iniciar_sesion.php). Nunca a partir de datos enviados por el navegador.
    http_response_code(410);
