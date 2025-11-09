<?php
    require '../../model/model_horario.php';
    $MH = new Modelo_Horario();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $horai = strtoupper(htmlspecialchars($_POST['horai'],ENT_QUOTES,'UTF-8'));
    $horaf = strtoupper(htmlspecialchars($_POST['horaf'],ENT_QUOTES,'UTF-8'));

    $consulta = $MH->Modificar_Horario($id,$horai,$horaf);
    echo $consulta;



?>