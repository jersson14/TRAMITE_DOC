<?php
    require '../../model/model_cargo.php';
    $MCA = new Modelo_Cargo();//Instaciamos
    $consulta = $MCA->Listar_Cargos();
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
