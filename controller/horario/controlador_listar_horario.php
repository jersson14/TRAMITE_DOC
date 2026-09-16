<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_horario.php';
    $MH = new Modelo_Horario();//Instaciamos
    $consulta = $MH->Listar_Horario();
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
