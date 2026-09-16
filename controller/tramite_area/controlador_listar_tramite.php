<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Plazos.php';
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $idusuario = Seguridad::usuarioId();
    $consulta = $MTRA->Listar_Tramite($idusuario);

    if($consulta){
            // Semáforo en días hábiles, calculado al momento (ver lib/Plazos.php)
            Plazos::agregar($consulta['data']);
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
