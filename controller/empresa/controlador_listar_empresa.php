<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_empresa.php';
    $ME = new Modelo_Empresa();//Instaciamos
    $consulta = $ME->Listar_Empresa();
    if($consulta){
        $horarios = $ME->Horarios_Recepcion();
        foreach ($consulta['data'] as &$fila) {
            $fila['emp_hora_inicio'] = $horarios[$fila['empresa_id']]['emp_hora_inicio'] ?? null;
            $fila['emp_hora_fin'] = $horarios[$fila['empresa_id']]['emp_hora_fin'] ?? null;
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
