<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();

    $id2 = strtoupper(trim((string) ($_POST['id2'] ?? '')));
    $desc2 = htmlspecialchars(trim((string) ($_POST['desc2'] ?? '')), ENT_QUOTES, 'UTF-8');
    $loc = (int) ($_POST['loc'] ?? 0);

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id2)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if ($desc2 === '') {
        Seguridad::responderError(422, 'Indique el motivo del rechazo.');
    }

    // Rechazar cierra el expediente igual que finalizarlo, así que exige el mismo
    // permiso (migración 024). Sin esto un Especialista podía cerrar un trámite
    // rechazándolo. Para algo incompleto de un ciudadano, lo que corresponde es
    // observarlo, no rechazarlo.
    Seguridad::exigirPermiso('finalizar');

    // Solo el área de destino (o el administrador) puede rechazar el trámite.
    if (!Seguridad::esAdmin() && !$MTR->Es_Area_Destino($id2, Seguridad::areaId())) {
        Seguridad::responderError(403, 'Solo el área de destino puede rechazar este trámite.');
    }

    $consulta = $MTR->Rechazar_Tramite($id2, $desc2, $loc);
    Bitacora::registrar(Bitacora::CAMBIO_ESTADO, 'documento', $id2, 'RECHAZADO: ' . $desc2);
    echo $consulta;
