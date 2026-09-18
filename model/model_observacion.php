<?php
    require_once 'model_conexion.php';

    /**
     * Observaciones de un trámite y su subsanación por el ciudadano (migración 025).
     *
     * Observar deja el trámite en OBSERVADO y guarda su estado anterior; subsanar
     * lo devuelve a ese estado. Ambas operaciones van en transacción: nunca debe
     * quedar un trámite OBSERVADO sin su observación abierta, ni al revés.
     */
    class Modelo_Observacion extends conexionBD{

        /**
         * Observa el trámite. Devuelve el id de la observación.
         * @throws RuntimeException con un mensaje para el usuario.
         */
        public function Observar($documento_id, $motivo, $plazo_dias, $fecha_limite, $usuario_id, $area_id){
            $c = conexionBD::conexionPDO();
            $c->beginTransaction();
            try {
                // FOR UPDATE: dos personas observando a la vez no deben pisarse
                $q = $c->prepare("SELECT doc_estatus FROM documento WHERE documento_id = ? FOR UPDATE");
                $q->execute([$documento_id]);
                $estado = $q->fetchColumn();
                if ($estado === false) {
                    throw new RuntimeException('El trámite no existe.');
                }
                if ($estado === 'OBSERVADO') {
                    throw new RuntimeException('El trámite ya está observado y espera la subsanación del ciudadano.');
                }
                if (in_array($estado, ['FINALIZADO', 'RECHAZADO'], true)) {
                    throw new RuntimeException('No se puede observar un trámite ' . strtolower($estado) . '.');
                }

                $q = $c->prepare("INSERT INTO observacion (documento_id, obs_motivo, obs_plazo_dias, obs_fecha_limite,
                                                           obs_estado_anterior, usuario_id, area_id)
                                  VALUES (?,?,?,?,?,?,?)");
                $q->execute([$documento_id, $motivo, $plazo_dias, $fecha_limite, $estado, $usuario_id, $area_id]);
                $id = (int) $c->lastInsertId();

                $c->prepare("UPDATE documento SET doc_estatus = 'OBSERVADO' WHERE documento_id = ?")
                  ->execute([$documento_id]);

                $c->commit();
                return $id;
            } catch (Throwable $e) {
                $c->rollBack();
                throw $e;
            }
        }

        /** La observación abierta del trámite, o null. */
        public function Pendiente($documento_id){
            $c = conexionBD::conexionPDO();
            $q = $c->prepare("SELECT o.*, a.area_nombre,
                                     DATE_FORMAT(o.obs_fecha, '%d/%m/%Y %H:%i') AS fecha_texto,
                                     DATE_FORMAT(o.obs_fecha_limite, '%d/%m/%Y') AS limite_texto,
                                     (o.obs_fecha_limite < CURDATE()) AS vencida
                              FROM observacion o
                              LEFT JOIN area a ON a.area_cod = o.area_id
                              WHERE o.documento_id = ? AND o.obs_estado = 'PENDIENTE'
                              ORDER BY o.observacion_id DESC LIMIT 1");
            $q->execute([$documento_id]);
            return $q->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        /**
         * Registra la subsanación y devuelve el trámite a su estado anterior.
         * @throws RuntimeException con un mensaje para el ciudadano.
         */
        public function Subsanar($observacion_id, $texto, $ruta, $nombre_archivo, $ip){
            $c = conexionBD::conexionPDO();
            $c->beginTransaction();
            try {
                $q = $c->prepare("SELECT documento_id, obs_estado, obs_estado_anterior
                                  FROM observacion WHERE observacion_id = ? FOR UPDATE");
                $q->execute([$observacion_id]);
                $obs = $q->fetch(PDO::FETCH_ASSOC);
                if (!$obs || $obs['obs_estado'] !== 'PENDIENTE') {
                    throw new RuntimeException('Esta observación ya fue subsanada.');
                }

                $c->prepare("UPDATE observacion
                                SET obs_estado = 'SUBSANADO', sub_fecha = NOW(), sub_texto = ?,
                                    sub_archivo = ?, sub_nombre_archivo = ?, sub_ip = ?
                              WHERE observacion_id = ?")
                  ->execute([$texto, $ruta, $nombre_archivo, $ip, $observacion_id]);

                // Vuelve a como estaba: el área retoma el trámite donde lo dejó
                $c->prepare("UPDATE documento SET doc_estatus = ? WHERE documento_id = ? AND doc_estatus = 'OBSERVADO'")
                  ->execute([$obs['obs_estado_anterior'], $obs['documento_id']]);

                $c->commit();
                return $obs['documento_id'];
            } catch (Throwable $e) {
                $c->rollBack();
                throw $e;
            }
        }

        /** Todas las observaciones del trámite, de la más reciente a la más antigua. */
        public function Historial($documento_id){
            $c = conexionBD::conexionPDO();
            $q = $c->prepare("SELECT o.observacion_id, o.obs_motivo, o.obs_plazo_dias, o.obs_estado,
                                     o.sub_texto, o.sub_archivo, o.sub_nombre_archivo, a.area_nombre,
                                     DATE_FORMAT(o.obs_fecha, '%d/%m/%Y %H:%i') AS fecha_texto,
                                     DATE_FORMAT(o.obs_fecha_limite, '%d/%m/%Y') AS limite_texto,
                                     DATE_FORMAT(o.sub_fecha, '%d/%m/%Y %H:%i') AS subsanado_texto,
                                     (o.obs_estado = 'PENDIENTE' AND o.obs_fecha_limite < CURDATE()) AS vencida
                              FROM observacion o
                              LEFT JOIN area a ON a.area_cod = o.area_id
                              WHERE o.documento_id = ?
                              ORDER BY o.observacion_id DESC");
            $q->execute([$documento_id]);
            return $q->fetchAll(PDO::FETCH_ASSOC);
        }
    }
