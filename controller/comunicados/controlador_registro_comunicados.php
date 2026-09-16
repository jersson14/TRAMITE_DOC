<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_comunicados.php';
    $MC = new Modelo_Comunicados();//Instaciamos
    $titulo = strtoupper(htmlspecialchars($_POST['titulo'],ENT_QUOTES,'UTF-8'));
    $descri = htmlspecialchars($_POST['descri'],ENT_QUOTES,'UTF-8');
    $idusu = Seguridad::usuarioId();
    $enlace = htmlspecialchars($_POST['enlace'],ENT_QUOTES,'UTF-8');
    
    $consulta = $MC->Registrar_Comunicado($titulo,$descri,$idusu,$enlace);
    echo $consulta;



?>