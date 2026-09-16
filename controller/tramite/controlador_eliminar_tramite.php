<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $consulta = $MTR->Eliminar_Tramite($id);
    Bitacora::registrar(Bitacora::ELIMINO_TRAMITE, 'documento', $id, 'eliminación del expediente');
    echo $consulta;



?>