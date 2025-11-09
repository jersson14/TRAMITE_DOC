<?php
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $idareas = strtoupper(htmlspecialchars($_POST['idareas'],ENT_QUOTES,'UTF-8'));
    $consulta = $MTRA->Listar_Tramite_Areas($idareas);

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
