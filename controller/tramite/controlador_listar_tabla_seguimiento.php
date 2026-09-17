<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite.php';
    $MTRA = new Modelo_Tramite();//Instaciamos
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    // Antes cualquier sesión veía el historial de cualquier trámite cambiando el código.
    // Un área solo ve los trámites que pasaron por ella (origen, destino, copia o atención).
    if (!Seguridad::esAdmin() && !$MTRA->Area_Puede_Ver($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'No tiene acceso al historial de este trámite.');
    }

    $consulta = $MTRA->Listar_Tramite_Seguimiento($id);
    if($consulta){
        echo json_encode($consulta);
    }else{
        echo '{
            "sEcho": 1,
            "iTotalRecords": "0",
            "iTotalDisplayRecords": "0",
            "aaData": []
        }';
    }
?>
