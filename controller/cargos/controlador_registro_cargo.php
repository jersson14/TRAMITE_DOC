<?php
    require '../../model/model_cargo.php';
    $MCA = new Modelo_Cargo();//Instaciamos
    $area = strtoupper(htmlspecialchars($_POST['area'],ENT_QUOTES,'UTF-8'));
    $cargo = strtoupper(htmlspecialchars($_POST['cargo'],ENT_QUOTES,'UTF-8'));

    $consulta = $MCA->Registrar_cargo($area,$cargo);
    echo $consulta;



?>