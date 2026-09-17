<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite_area.php';
    require '../../model/model_tramite.php';

    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));

    // Solo trámites que pasaron por el área del usuario (o cualquiera, para el administrador)
    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)
        || (!Seguridad::esAdmin() && !(new Modelo_Tramite())->Area_Puede_Ver($id, Seguridad::areaId()))) {
        echo json_encode([]);
        exit;
    }

    $consulta = $MTRA->TraerDatosExpediente($id);
    echo json_encode($consulta);
?>
