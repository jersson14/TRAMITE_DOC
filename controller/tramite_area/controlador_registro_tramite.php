<?php
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    //DATOS DE REMITENTE//
    $iddo = strtoupper(htmlspecialchars($_POST['iddo'],ENT_QUOTES,'UTF-8'));
    $orig = strtoupper(htmlspecialchars($_POST['orig'],ENT_QUOTES,'UTF-8'));
    $dest = strtoupper(htmlspecialchars($_POST['dest'],ENT_QUOTES,'UTF-8'));
    $desc = strtoupper(htmlspecialchars($_POST['desc'],ENT_QUOTES,'UTF-8'));
    $idusu = strtoupper(htmlspecialchars($_POST['idusu'],ENT_QUOTES,'UTF-8'));
    $tipo = strtoupper(htmlspecialchars($_POST['tipo'],ENT_QUOTES,'UTF-8'));
    $acc = strtoupper(htmlspecialchars($_POST['acc'],ENT_QUOTES,'UTF-8'));
    $nombrearchivo = strtoupper(htmlspecialchars($_POST['nombrearchivo'],ENT_QUOTES,'UTF-8'));
    
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
        
        echo $consulta;
    }


?>