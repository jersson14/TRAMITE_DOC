<?php
    require_once 'model_conexion.php';

    class Modelo_Empresa extends conexionBD{
        

        public function Listar_Empresa(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_EMPRESA()";
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Modificar_Empresa($id,$nom,$email,$cod,$tel,$dir){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_EMPRESA(?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$nom);
            $query ->bindParam(3,$email);
            $query ->bindParam(4,$cod);
            $query ->bindParam(5,$tel);
            $query ->bindParam(6,$dir);
            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
        /** Horario de atención para la recepción de documentos (lunes a viernes). */
        public function Modificar_Horario_Recepcion($id, $inicio, $fin){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("UPDATE empresa SET emp_hora_inicio = ?, emp_hora_fin = ? WHERE empresa_id = ?");
            return $query->execute([$inicio . ':00', $fin . ':00', $id]) ? 1 : 0;
        }

        /** Horario de cada institución, para completar el listado (el procedimiento no lo trae). */
        public function Horarios_Recepcion(){
            $c = conexionBD::conexionPDO();
            $query = $c->query("SELECT empresa_id, emp_hora_inicio, emp_hora_fin FROM empresa");
            $mapa = array();
            foreach($query->fetchAll(PDO::FETCH_ASSOC) as $fila){
                $mapa[$fila['empresa_id']] = $fila;
            }
            return $mapa;
        }

        public function Modificar_foto_empresa($id,$ruta){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_MODIFICAR_EMPRESA_FOTO(?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$id);
            $query ->bindParam(2,$ruta);

            $resul = $query->execute();
            if($resul){
                return 1;
            }else{
                return 0;
            }
            conexionBD::cerrar_conexion();
        }
    }




?>