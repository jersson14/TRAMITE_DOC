<?php
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $estados = strtoupper(htmlspecialchars($_POST['estados'],ENT_QUOTES,'UTF-8'));

    $consulta = $MTRA->Listar_Tramite_Areas_Buscar($id,$estados);

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
