<?php
     require '../../model/model_horario.php';
    $MH = new Modelo_Horario();//Instaciamos
    $id = htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8');
    $consulta = $MH->TraerHora($id);
    echo json_encode($consulta);

?>