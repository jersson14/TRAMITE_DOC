<?php
    require '../../model/model_tramite_area.php';
    require '../../utilitario/class_notificacion.php';
    $MTRA = new Modelo_TramiteArea();
    $NTF  = new Notificacion();

    //DATOS DE DERIVACIÓN//
    $iddo = strtoupper(htmlspecialchars($_POST['iddo'],ENT_QUOTES,'UTF-8'));
    $orig = strtoupper(htmlspecialchars($_POST['orig'],ENT_QUOTES,'UTF-8'));
    $dest = strtoupper(htmlspecialchars($_POST['dest'],ENT_QUOTES,'UTF-8'));
    $desc = strtoupper(htmlspecialchars($_POST['desc'],ENT_QUOTES,'UTF-8'));
    $idusu = strtoupper(htmlspecialchars($_POST['idusu'],ENT_QUOTES,'UTF-8'));
    $tipo = strtoupper(htmlspecialchars($_POST['tipo'],ENT_QUOTES,'UTF-8'));
    $acc = strtoupper(htmlspecialchars($_POST['acc'],ENT_QUOTES,'UTF-8'));
    $nombrearchivo = strtoupper(htmlspecialchars($_POST['nombrearchivo'],ENT_QUOTES,'UTF-8'));
    
    // Datos extra para el correo (opcionales, se envían si el JS los incluye)
    $asunto_doc   = isset($_POST['asunto'])   ? htmlspecialchars($_POST['asunto'],ENT_QUOTES,'UTF-8')   : '';
    $tipo_doc_txt = isset($_POST['tipo_doc']) ? htmlspecialchars($_POST['tipo_doc'],ENT_QUOTES,'UTF-8') : '';
    $area_orig_txt = isset($_POST['area_orig_txt']) ? htmlspecialchars($_POST['area_orig_txt'],ENT_QUOTES,'UTF-8') : '';
    $area_dest_txt = isset($_POST['area_dest_txt']) ? htmlspecialchars($_POST['area_dest_txt'],ENT_QUOTES,'UTF-8') : '';

    // Recibir copias
    $copias = isset($_POST['copias']) ? json_decode($_POST['copias'], true) : [];
    
    if($nombrearchivo!=""){
        $ruta='controller/tramite_area/documentos/'.$nombrearchivo;
    }else{
        $ruta='';
    }
    
    // Registrar la derivación principal
    $consulta = $MTRA->Registrar_Deri($iddo,$orig,$dest,$desc,$idusu,$ruta,$tipo,$acc);
    
    if($consulta==1){
        // Subir archivo si existe
        if($nombrearchivo!=""){
            if(move_uploaded_file($_FILES['achivoobj']['tmp_name'],"documentos/".$nombrearchivo));
        }
        
        // Registrar copias si existen
        if(!empty($copias)){
            foreach($copias as $area_copia){
                $MTRA->Registrar_Copia($iddo, $orig, $area_copia, $desc, $idusu, $ruta, $acc);
            }
        }
        
        // ✉️ NOTIFICACIÓN: la clase obtiene automáticamente nombres de área y de usuario
        $NTF->notificarDerivacion($dest, $orig, $idusu, $iddo, '', $desc, $tipo);
        
        // ✉️ NOTIFICACIÓN a áreas que reciben copias
        if(!empty($copias)){
            foreach($copias as $area_copia){
                $NTF->notificarDerivacion($area_copia, $orig, $idusu, $iddo, '', 'COPIA - ' . $desc, $tipo);
            }
        }

        echo $consulta;
    }
?>