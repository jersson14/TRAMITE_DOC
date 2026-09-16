<?php
    require_once __DIR__ . '/../_guard.php';
    // Iniciar buffer de salida para capturar cualquier output no deseado
    ob_start();
    
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $idareas = Seguridad::usuarioId();
    
    try {
        $consulta = $MTRA->Listar_Tramite_Areas($idareas);
        
        // Limpiar cualquier salida previa
        ob_end_clean();
        
        // Establecer header JSON
        header('Content-Type: application/json; charset=utf-8');
        
        if($consulta){
            echo json_encode($consulta);
        }else{
            echo json_encode([
                "data" => []
            ]);
        }
    } catch (Exception $e) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            "error" => $e->getMessage(),
            "data" => []
        ]);
    }
?>
