<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_area.php';
    $MA = new Modelo_Area();//Instaciamos
    $consulta = $MA->Listar_Area();
    if($consulta){
        // Sigla para numerar documentos (migración 026): la guardada, o la que el
        // sistema deduce del nombre si no hay. Se agrega aquí para no tocar el SP.
        require_once __DIR__ . '/../../lib/Correlativo.php';
        $siglas = $MA->Siglas();
        foreach ($consulta['data'] as &$fila) {
            $guardada = $siglas[$fila['area_cod']] ?? null;
            $fila['area_sigla'] = (string) $guardada;
            $fila['sigla_efectiva'] = Correlativo::siglaArea((string) $fila['area_nombre'], $guardada);
        }
        unset($fila);
        echo json_encode($consulta);
    }else{
        echo '{
            "sEcho": 1,
            "iTotalRecords": "0",
            "iTotalDisplayRecords": "0",
            "aaData": []
        }';
    }
?>
