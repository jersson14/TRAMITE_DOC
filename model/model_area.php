<?php
    require_once 'model_conexion.php';

    class Modelo_Area extends conexionBD{
        

        public function Listar_Area(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_AREA()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_Area($area){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_AREA(?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$area);
            $resultado = $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Area($id,$area,$esta){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_AREA(?,?,?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$area);
            $query ->bindParam(3,$esta);
            $resultado = $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        /**
         * Sigla del área para numerar sus documentos (migración 026). Se guarda aquí
         * y no en SP_REGISTRAR_AREA / SP_MODIFICAR_AREA para no rehacer esos
         * procedimientos. Vacía = el sistema la deduce del nombre.
         * Con $id null se busca el área por su nombre (recién registrada).
         */
        public function Guardar_Sigla($id, $nombre, $sigla){
            $sigla = mb_strtoupper(trim((string) $sigla));
            $sigla = $sigla === '' ? null : mb_substr($sigla, 0, 20);
            $c = conexionBD::conexionPDO();
            if ($id) {
                $q = $c->prepare("UPDATE area SET area_sigla = ? WHERE area_cod = ?");
                $q->execute([$sigla, $id]);
            } else {
                $q = $c->prepare("UPDATE area SET area_sigla = ? WHERE area_nombre = ? ORDER BY area_cod DESC LIMIT 1");
                $q->execute([$sigla, $nombre]);
            }
            return $q->rowCount();
        }

        /** Siglas guardadas, por área: [area_cod => sigla]. */
        public function Siglas(){
            $c = conexionBD::conexionPDO();
            return $c->query("SELECT area_cod, area_sigla FROM area")->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        public function Cargar_Select_Area(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SELECT_AREA()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
    }




?>