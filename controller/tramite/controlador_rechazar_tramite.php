<?php
   require '../../model/model_tramite.php';
   $MTR = new Modelo_Tramite();//Instaciamos
    $id2 = strtoupper(htmlspecialchars($_POST['id2'],ENT_QUOTES,'UTF-8'));
    $desc2 = htmlspecialchars($_POST['desc2'],ENT_QUOTES,'UTF-8');
    $loc = htmlspecialchars($_POST['loc'],ENT_QUOTES,'UTF-8');

    $consulta = $MTR->Rechazar_Tramite($id2,$desc2,$loc);
    echo $consulta;



?>