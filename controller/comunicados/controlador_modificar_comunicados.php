<?php
    require '../../model/model_comunicados.php';
    $MC = new Modelo_Comunicados();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $titulo = htmlspecialchars($_POST['titulo'],ENT_QUOTES,'UTF-8');
    $descri = htmlspecialchars($_POST['descri'],ENT_QUOTES,'UTF-8');
    $enlace = htmlspecialchars($_POST['enlace'],ENT_QUOTES,'UTF-8');

    $consulta = $MC->Modificar_Comunicado($id,$titulo,$descri,$enlace);
    echo $consulta;



?>