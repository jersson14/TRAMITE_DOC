<?php
    // Archivo temporal para depurar la respuesta del controlador
    require '../../model/model_tramite_area.php';
    
    // Capturar cualquier salida antes del JSON
    ob_start();
    
    $MTRA = new Modelo_TramiteArea();
    $idareas = isset($_POST['idareas']) ? $_POST['idareas'] : 1; // Usar 1 como prueba
    
    try {
        $consulta = $MTRA->Listar_Tramite_Areas($idareas);
        
        // Limpiar cualquier salida previa
        ob_end_clean();
        
        // Establecer header JSON
        header('Content-Type: application/json');
        
        if($consulta){
            echo json_encode($consulta);
        } else {
            echo json_encode([
                "data" => []
            ]);
        }
    } catch (Exception $e) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            "error" => $e->getMessage(),
            "data" => []
        ]);
    }
?>
