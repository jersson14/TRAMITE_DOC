<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite_area.php';
    require '../../model/model_tramite.php';
    $MTRA = new Modelo_TramiteArea();
    $MTR  = new Modelo_Tramite();

    // Antes llegaba el Nº de documento del ciudadano, que se repite entre trámites
    // y hacía que aceptar uno aceptara varios. Ahora llega el código del trámite.
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    $estatus = strtoupper(trim((string) ($_POST['estatus'] ?? '')));

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if (!in_array($estatus, ['ACEPTADO', 'FINALIZADO'], true)) {
        Seguridad::responderError(422, 'Estado no permitido.');
    }

    // Este endpoint también finaliza. Era un segundo camino para cerrar el
    // expediente que se saltaba el control por rol de la derivación (migración
    // 024). Aceptar es solo acusar recibo y lo puede hacer cualquiera del área.
    if ($estatus === 'FINALIZADO') {
        Seguridad::exigirPermiso('finalizar');
    }

    // Solo el área a la que va dirigido puede aceptarlo; un área que lo recibió en
    // copia lo ve para conocimiento, pero no decide sobre él.
    if (!Seguridad::esAdmin() && !$MTR->Es_Area_Destino($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'Solo el área de destino puede cambiar el estado de este trámite.');
    }

    $consulta = $MTRA->Modificar_Estatus_Tramite($id, $estatus);
    Bitacora::registrar(Bitacora::CAMBIO_ESTADO, 'documento', $id, 'nuevo estado: ' . $estatus);

    // Aceptar es recibir: queda el acuse de quién lo recibió y cuándo.
    if ($consulta && $estatus === 'ACEPTADO' && $MTR->Registrar_Acuse_Destino($id, Seguridad::usuarioId()) > 0) {
        Bitacora::registrar(Bitacora::RECEPCION, 'documento', $id, 'acuse del área de destino');
    }
    echo $consulta;
