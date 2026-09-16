<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require '../../model/model_usuario.php';
    Seguridad::iniciarSesion();
    $MU = new Modelo_Usuario();//Instaciamos
    $codigo = strtoupper(htmlspecialchars($_POST['codigo'] ?? '',ENT_QUOTES,'UTF-8'));

    // Sin sesión, el detalle solo se entrega a quien conoce el código y el DNI del remitente
    if (!Seguridad::autenticado()) {
        $dni = strtoupper(htmlspecialchars($_POST['dni'] ?? '',ENT_QUOTES,'UTF-8'));
        if (count($MU->Cargar_Select_Datos_Seguimiento($codigo, $dni)) === 0) {
            echo json_encode([]);
            exit;
        }
    }

    $consulta = $MU->Traer_Datos_Detalle_Seguimiento($codigo);
    echo json_encode($consulta);
?>
