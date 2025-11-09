<?php
    require '../../model/model_cargo.php';
    $MCA = new Modelo_Cargo();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $cargo = strtoupper(htmlspecialchars($_POST['cargo'],ENT_QUOTES,'UTF-8'));
    $descrip = strtoupper(htmlspecialchars($_POST['descrip'],ENT_QUOTES,'UTF-8'));
    $esta = strtoupper(htmlspecialchars($_POST['esta'],ENT_QUOTES,'UTF-8'));

    $consulta = $MCA->Modificar_cargo($id,$cargo,$descrip,$esta);
    echo $consulta;



?>