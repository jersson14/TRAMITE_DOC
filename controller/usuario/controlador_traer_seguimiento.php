<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require '../../model/model_usuario.php';
    Seguridad::iniciarSesion();

    // Consulta pública: se limita por IP para impedir probar DNIs de forma masiva
    if (!Seguridad::autenticado()) {
        $clave = 'seguimiento|' . Seguridad::ipCliente();
        if (Seguridad::esperaIntentos($clave, 30, 600) > 0) {
            Seguridad::responderError(429, 'Demasiadas consultas. Espere unos minutos e intente nuevamente.');
        }
        Seguridad::registrarIntento($clave, 600);
    }

    $MU = new Modelo_Usuario();//Instaciamos
    $numero = strtoupper(htmlspecialchars($_POST['numero'] ?? '',ENT_QUOTES,'UTF-8'));
    $dni = strtoupper(htmlspecialchars($_POST['dni'] ?? '',ENT_QUOTES,'UTF-8'));

    $consulta = $MU->Cargar_Select_Datos_Seguimiento($numero,$dni);
    echo json_encode($consulta);
?>
