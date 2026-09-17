<?php
    require_once 'model_conexion.php';

    /**
     * Comunicados dirigidos, con vigencia y acuse de lectura (migración 018).
     *
     * Un comunicado vigente (estado NUEVO y dentro de sus fechas) se muestra como
     * alerta al entrar al sistema a quien corresponde, hasta que confirme haberlo
     * leído. Las consultas van escritas aquí, no en procedimientos, porque el
     * filtro por destinatario y por lecturas cambia según quién consulta.
     */
    class Modelo_Comunicados extends conexionBD{

        const DESTINOS = ['TODOS', 'ADMINISTRADORES', 'SECRETARIAS', 'AREAS'];

        /** Listado para el administrador, con destinatarios y cuántos lo leyeron. */
        public function Listar_Comunicados(){
            $c = conexionBD::conexionPDO();
            $sql = "SELECT c.id_comunicado, c.titulo, c.descripcion, c.enlace, c.estado,
                           c.fecha_registro, DATE_FORMAT(c.fecha_registro, '%d/%m/%Y') AS fecha_formateada,
                           c.com_destino, c.com_desde, c.com_hasta, c.com_imagen,
                           DATE_FORMAT(c.com_desde, '%d/%m/%Y') AS desde_texto,
                           DATE_FORMAT(c.com_hasta, '%d/%m/%Y') AS hasta_texto,
                           (SELECT COUNT(*) FROM comunicado_leido l WHERE l.id_comunicado = c.id_comunicado) AS leidos,
                           (SELECT GROUP_CONCAT(a.area_nombre ORDER BY a.area_nombre SEPARATOR ', ')
                              FROM comunicado_area ca INNER JOIN area a ON a.area_cod = ca.area_cod
                             WHERE ca.id_comunicado = c.id_comunicado) AS areas,
                           (SELECT GROUP_CONCAT(ca.area_cod) FROM comunicado_area ca
                             WHERE ca.id_comunicado = c.id_comunicado) AS areas_id
                      FROM comunicados c
                     ORDER BY c.fecha_registro DESC, c.id_comunicado DESC";
            $arreglo = array('data' => array());
            foreach($c->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $fila){
                $arreglo['data'][] = $fila;
            }
            return $arreglo;
        }

        /**
         * Condición SQL que decide si un comunicado le corresponde a un usuario.
         * Se usa igual en la alerta, en las notificaciones y en el tablero.
         */
        private function condicionDestino(bool $esAdmin): string
        {
            return "(c.com_destino = 'TODOS'
                     OR (c.com_destino = 'ADMINISTRADORES' AND " . ($esAdmin ? '1' : '0') . ")
                     OR (c.com_destino = 'SECRETARIAS' AND " . ($esAdmin ? '0' : '1') . ")
                     OR (c.com_destino = 'AREAS' AND EXISTS (
                            SELECT 1 FROM comunicado_area ca
                             WHERE ca.id_comunicado = c.id_comunicado AND ca.area_cod = ?)))";
        }

        /**
         * Comunicados vigentes para un usuario.
         * $soloPendientes deja fuera los que ya confirmó leer.
         */
        public function Para_Usuario(int $usuarioId, int $areaId, bool $esAdmin, bool $soloPendientes = false): array
        {
            $c = conexionBD::conexionPDO();
            $sql = "SELECT c.id_comunicado, c.titulo, c.descripcion, c.enlace, c.com_destino, c.com_imagen,
                           DATE_FORMAT(c.fecha_registro, '%d/%m/%Y') AS fecha,
                           DATE_FORMAT(c.com_hasta, '%d/%m/%Y') AS hasta,
                           (SELECT COUNT(*) FROM comunicado_leido l
                             WHERE l.id_comunicado = c.id_comunicado AND l.usuario_id = ?) AS leido
                      FROM comunicados c
                     WHERE c.estado = 'NUEVO'
                       AND (c.com_desde IS NULL OR c.com_desde <= CURDATE())
                       AND (c.com_hasta IS NULL OR c.com_hasta >= CURDATE())
                       AND " . $this->condicionDestino($esAdmin);
            $parametros = [$usuarioId, $areaId];

            if ($soloPendientes) {
                $sql .= " AND NOT EXISTS (SELECT 1 FROM comunicado_leido l2
                                           WHERE l2.id_comunicado = c.id_comunicado AND l2.usuario_id = ?)";
                $parametros[] = $usuarioId;
            }
            $sql .= " ORDER BY c.fecha_registro DESC, c.id_comunicado DESC";

            $query = $c->prepare($sql);
            $query->execute($parametros);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        /** Deja constancia de que el usuario leyó el comunicado. */
        public function Marcar_Leido(int $comunicadoId, int $usuarioId): bool
        {
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("INSERT IGNORE INTO comunicado_leido (id_comunicado, usuario_id) VALUES (?,?)");
            return $query->execute([$comunicadoId, $usuarioId]);
        }

        /** Quiénes confirmaron la lectura de un comunicado. */
        public function Lecturas(int $comunicadoId): array
        {
            $c = conexionBD::conexionPDO();
            $query = $c->prepare(
                "SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat, e.emple_apemat)), ''), u.usu_usuario) AS persona,
                        a.area_nombre AS area, DATE_FORMAT(l.fecha, '%d/%m/%Y %H:%i') AS fecha
                   FROM comunicado_leido l
                   INNER JOIN usuario u ON u.usu_id = l.usuario_id
                   LEFT JOIN empleado e ON e.empleado_id = u.empleado_id
                   LEFT JOIN area a ON a.area_cod = u.area_id
                  WHERE l.id_comunicado = ?
                  ORDER BY l.fecha DESC"
            );
            $query->execute([$comunicadoId]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        /** Crea el comunicado con sus destinatarios. Devuelve su id. */
        public function Registrar_Comunicado($titulo, $descri, $idusu, $enlace, $destino, $desde, $hasta, array $areas, $imagen = null)
        {
            $c = conexionBD::conexionPDO();
            $c->beginTransaction();
            try {
                $query = $c->prepare(
                    "INSERT INTO comunicados (titulo, descripcion, enlace, fecha_registro, id_usuario, estado,
                                              com_destino, com_desde, com_hasta, com_imagen)
                     VALUES (?,?,?,CURDATE(),?, 'NUEVO', ?,?,?,?)"
                );
                $query->execute([$titulo, $descri, $enlace, $idusu ?: null, $destino, $desde, $hasta, $imagen]);
                $id = (int) $c->lastInsertId();
                $this->guardarAreas($c, $id, $destino, $areas);
                $c->commit();
                return $id;
            } catch (Throwable $e) {
                $c->rollBack();
                throw $e;
            }
        }

        /** Actualiza el comunicado y sus destinatarios. */
        public function Modificar_Comunicado($id, $titulo, $descri, $enlace, $destino, $desde, $hasta, array $areas, $estado, $imagen = null)
        {
            $c = conexionBD::conexionPDO();
            $c->beginTransaction();
            try {
                $query = $c->prepare(
                    "UPDATE comunicados
                        SET titulo = ?, descripcion = ?, enlace = ?, com_destino = ?, com_desde = ?, com_hasta = ?,
                            estado = ?, com_imagen = ?
                      WHERE id_comunicado = ?"
                );
                $query->execute([$titulo, $descri, $enlace, $destino, $desde, $hasta, $estado, $imagen, $id]);
                $c->prepare("DELETE FROM comunicado_area WHERE id_comunicado = ?")->execute([$id]);
                $this->guardarAreas($c, (int) $id, $destino, $areas);
                $c->commit();
                return 1;
            } catch (Throwable $e) {
                $c->rollBack();
                throw $e;
            }
        }

        /** Áreas destinatarias; solo se guardan cuando el destino es AREAS. */
        private function guardarAreas(PDO $c, int $id, string $destino, array $areas): void
        {
            if ($destino !== 'AREAS' || !$areas) {
                return;
            }
            $query = $c->prepare("INSERT IGNORE INTO comunicado_area (id_comunicado, area_cod) VALUES (?,?)");
            foreach ($areas as $area) {
                $query->execute([$id, (int) $area]);
            }
        }

        /** Ruta de la imagen que tiene guardada un comunicado. */
        public function Imagen_De($id): ?string
        {
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT com_imagen FROM comunicados WHERE id_comunicado = ?");
            $query->execute([$id]);
            $ruta = $query->fetchColumn();
            return $ruta ?: null;
        }

        /** Archiva o reactiva un comunicado. */
        public function Cambiar_Estado($id, string $estado): int
        {
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("UPDATE comunicados SET estado = ? WHERE id_comunicado = ?");
            $query->execute([$estado, $id]);
            return $query->rowCount();
        }
    }
