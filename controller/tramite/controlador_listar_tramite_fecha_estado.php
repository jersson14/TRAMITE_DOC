<?php
    require '../../model/model_tramite.php';
    $MTRA = new Modelo_Tramite();//Instaciamos
    $fechainicio = htmlspecialchars($_POST['fechainicio'],ENT_QUOTES,'UTF-8');
    $fechafin = htmlspecialchars($_POST['fechafin'],ENT_QUOTES,'UTF-8');
    $estado = htmlspecialchars($_POST['estado'],ENT_QUOTES,'UTF-8');

    $consulta = $MTRA->Listar_Tramite_Fecha_Estado($fechainicio,$fechafin,$estado);
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
