<?php
    require_once 'model_conexion.php';

    class Modelo_Bitacora extends conexionBD{

        /**
         * Lista los movimientos de la bitácora, del más reciente al más antiguo.
         * Las fechas y la acción son opcionales; el tope evita traer años enteros
         * de golpe a la pantalla.
         */
        public function Listar_Bitacora($desde = '', $hasta = '', $accion = '', $tope = 1000){
            $c = conexionBD::conexionPDO();

            $sql = "SELECT
                        bit_id,
                        DATE_FORMAT(bit_fecha, '%d-%m-%Y %H:%i:%s') AS fecha,
                        bit_fecha,
                        IFNULL(bit_usuario, 'anónimo') AS usuario,
                        IFNULL(bit_rol, '') AS rol,
                        bit_accion,
                        IFNULL(bit_entidad, '') AS entidad,
                        IFNULL(bit_entidad_id, '') AS entidad_id,
                        IFNULL(bit_detalle, '') AS detalle,
                        IFNULL(bit_ip, '') AS ip
                    FROM bitacora
                    WHERE 1 = 1";
            $parametros = array();

            if($desde !== ''){
                $sql .= " AND bit_fecha >= ?";
                $parametros[] = $desde . ' 00:00:00';
            }
            if($hasta !== ''){
                $sql .= " AND bit_fecha <= ?";
                $parametros[] = $hasta . ' 23:59:59';
            }
            if($accion !== ''){
                $sql .= " AND bit_accion = ?";
                $parametros[] = $accion;
            }

            $sql .= " ORDER BY bit_id DESC LIMIT " . (int) $tope;

            $query = $c->prepare($sql);
            $query->execute($parametros);
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);

            $arreglo = array();
            foreach($resultado as $fila){
                $arreglo["data"][] = $fila;
            }
            return $arreglo;
        }

        /** Acciones distintas ya registradas, para llenar el filtro. */
        public function Listar_Acciones(){
            $c = conexionBD::conexionPDO();
            $query = $c->query("SELECT DISTINCT bit_accion FROM bitacora ORDER BY bit_accion");
            return $query->fetchAll(PDO::FETCH_COLUMN);
        }
    }
