<?php
    require_once 'model_conexion.php';

    /**
     * Feriados y días no laborables (tabla feriado, migraciones 012 y 017).
     * lib/Plazos.php los descuenta al contar plazos en días hábiles.
     */
    class Modelo_Feriado extends conexionBD{

        /** Feriados de un año, del primero al último. */
        public function Listar($anio){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare(
                "SELECT fecha, DATE_FORMAT(fecha, '%d/%m/%Y') AS fecha_texto,
                        DAYNAME(fecha) AS dia, DAYOFWEEK(fecha) AS dia_numero,
                        descripcion, tipo
                   FROM feriado
                  WHERE YEAR(fecha) = ?
                  ORDER BY fecha"
            );
            $query->execute([$anio]);
            $arreglo = array('data' => array());
            foreach($query->fetchAll(PDO::FETCH_ASSOC) as $fila){
                // Sábado (7) y domingo (1) ya no son hábiles: el feriado no cambia nada
                $fila['fin_de_semana'] = in_array((int) $fila['dia_numero'], [1, 7], true);
                $arreglo['data'][] = $fila;
            }
            return $arreglo;
        }

        /** Años que ya tienen feriados cargados, del más reciente al más antiguo. */
        public function Anios(){
            $c = conexionBD::conexionPDO();
            return $c->query("SELECT DISTINCT YEAR(fecha) AS anio FROM feriado ORDER BY anio DESC")
                     ->fetchAll(PDO::FETCH_COLUMN);
        }

        /** Agrega o actualiza un feriado (la fecha es la clave). */
        public function Guardar($fecha, $descripcion, $tipo){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare(
                "INSERT INTO feriado (fecha, descripcion, tipo) VALUES (?,?,?)
                 ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), tipo = VALUES(tipo)"
            );
            return $query->execute([$fecha, $descripcion, $tipo]) ? 1 : 0;
        }

        public function Eliminar($fecha){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("DELETE FROM feriado WHERE fecha = ?");
            $query->execute([$fecha]);
            return $query->rowCount();
        }

        /**
         * Carga los feriados nacionales de un año. No toca los que ya existen,
         * así que no borra descripciones editadas a mano. Devuelve cuántos agregó.
         */
        public function Cargar_Nacionales($anio, array $feriados){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("INSERT IGNORE INTO feriado (fecha, descripcion, tipo) VALUES (?,?,'NACIONAL')");
            $agregados = 0;
            foreach($feriados as $fecha => $descripcion){
                $query->execute([$fecha, $descripcion]);
                $agregados += $query->rowCount();
            }
            return $agregados;
        }

        /** Cuántos feriados tiene cada uno de los próximos años; sirve para avisar en el tablero. */
        public function Faltan_Del_Anio($anio){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT COUNT(*) FROM feriado WHERE YEAR(fecha) = ?");
            $query->execute([$anio]);
            return (int) $query->fetchColumn();
        }
    }
