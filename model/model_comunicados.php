<?php
    require_once 'model_conexion.php';

    class Modelo_Comunicados extends conexionBD{
        

        public function Listar_Comunicados(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_COMUNICADOS()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Listar_Comunicados2(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_COMUNICADOS2()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_Comunicado($titulo,$descri,$idusu,$enlace){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_COMUNICADOS(?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$titulo);
            $query ->bindParam(2,$descri);
            $query ->bindParam(3,$idusu);
            $query ->bindParam(4,$enlace);
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Comunicado($id,$titulo,$descri,$enlace){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_COMUNICADO(?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$titulo);
            $query ->bindParam(3,$descri);
            $query ->bindParam(4,$enlace);
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        public function Listar_notificacion(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_NOTIFICACION_COMUNICADO()";
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