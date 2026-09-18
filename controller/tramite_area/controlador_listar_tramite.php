<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Plazos.php';
    require '../../model/model_tramite_area.php';
    $MTRA = new Modelo_TramiteArea();//Instaciamos
    $idusuario = Seguridad::usuarioId();
    $consulta = $MTRA->Listar_Tramite($idusuario);

    if($consulta){
            // Semáforo en días hábiles, calculado al momento (ver lib/Plazos.php)
            Plazos::agregar($consulta['data']);

            /*
             * La procedencia se agrega aquí y no en SP_LISTAR_TRAMITE_AREA: las
             * pantallas leen varios procedimientos por posición y agregarle una
             * columna ya rompió vistas antes. La bandeja la usa para ofrecer
             * "Observar" solo en trámites externos (migración 025).
             */
            $ids = array_values(array_unique(array_filter(array_column($consulta['data'], 'documento_id'))));
            if ($ids) {
                $pdo = (new conexionBD())->conexionPDO();
                $marcas = implode(',', array_fill(0, count($ids), '?'));
                $q = $pdo->prepare("SELECT documento_id, doc_procedencia FROM documento WHERE documento_id IN ($marcas)");
                $q->execute($ids);
                $procedencia = $q->fetchAll(PDO::FETCH_KEY_PAIR);
                foreach ($consulta['data'] as &$fila) {
                    $fila['procedencia'] = $procedencia[$fila['documento_id']] ?? 'INTERNO';
                }
                unset($fila);
            }
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
