<?php
    require '../../model/model_horario.php';
    $MH = new Modelo_Horario();//Instaciamos
    $consulta = $MH->TraerDatos();
    echo json_encode($consulta);

?>