<?php
    require '../../model/model_tramite.php';
    $MTRA = new Modelo_Tramite();//Instaciamos
    $estados = htmlspecialchars($_POST['estados'],ENT_QUOTES,'UTF-8');
    $consulta = $MTRA->Listar_Tramite_Estado($estados);
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
