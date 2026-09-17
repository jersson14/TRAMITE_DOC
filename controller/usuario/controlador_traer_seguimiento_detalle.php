<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require '../../model/model_usuario.php';
    require '../../model/model_tramite.php';
    Seguridad::iniciarSesion();
    $MU = new Modelo_Usuario();//Instaciamos
    $codigo = strtoupper(htmlspecialchars($_POST['codigo'] ?? '',ENT_QUOTES,'UTF-8'));
    $dni = strtoupper(htmlspecialchars($_POST['dni'] ?? '',ENT_QUOTES,'UTF-8'));

    // El detalle se entrega a quien conoce el código y el DNI del remitente (ciudadano o
    // personal que lo atiende), al administrador, o al área por la que pasó el trámite.
    $conoceDatos = $dni !== '' && count($MU->Cargar_Select_Datos_Seguimiento($codigo, $dni)) > 0;
    $autorizado = $conoceDatos
        || (Seguridad::autenticado() && (Seguridad::esAdmin() || (new Modelo_Tramite())->Area_Puede_Ver($codigo, Seguridad::areaId())));
    if (!$autorizado) {
        echo json_encode([]);
        exit;
    }

    $consulta = $MU->Traer_Datos_Detalle_Seguimiento($codigo);
    echo json_encode($consulta);
?>
