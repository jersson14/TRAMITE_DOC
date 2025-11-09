<?php
    require_once 'model_conexion.php';

    class Modelo_Cargo extends conexionBD{
        

        public function Listar_Cargos(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_CARGOS()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_cargo($area,$cargo){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_CARGO(?,?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$area);
            $query ->bindParam(2,$cargo);

            $resultado = $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_cargo($id,$cargo,$descrip,$esta){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_CARGO(?,?,?,?)";
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$cargo);
            $query ->bindParam(3,$descrip);
            $query ->bindParam(4,$esta);
            $resultado = $query->execute();
            if($row = $query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Cargar_Select_Cargp(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_CARGO()";
            $arreglo = array();
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