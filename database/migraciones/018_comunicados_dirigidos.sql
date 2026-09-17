-- ============================================================================
-- 018 - Comunicados dirigidos y con acuse de lectura
--
-- Los comunicados dejan de ser un listado que nadie mira: se muestran como
-- alerta al entrar al sistema, solo a quien corresponde, y queda constancia de
-- quién los leyó.
--
--  com_destino: a quién va dirigido
--      TODOS           todo el personal
--      ADMINISTRADORES solo los administradores
--      SECRETARIAS     solo el personal de área (rol Secretario (a))
--      AREAS           solo las áreas listadas en comunicado_area
--  com_desde / com_hasta: vigencia opcional (NULL = sin límite). Fuera de esas
--      fechas el comunicado no se muestra, aunque su estado sea NUEVO.
--  comunicado_area: áreas destinatarias cuando com_destino = 'AREAS'.
--  comunicado_leido: quién confirmó haberlo leído y cuándo. Mientras no lo
--      confirme, la alerta vuelve a aparecer.
--
-- El estado NUEVO/PASADO que ya existía sigue indicando si está vigente o
-- archivado. Columnas al final de la tabla: ningún procedimiento usa SELECT *.
-- ============================================================================

ALTER TABLE comunicados
  ADD COLUMN IF NOT EXISTS com_destino ENUM('TODOS', 'ADMINISTRADORES', 'SECRETARIAS', 'AREAS')
      NOT NULL DEFAULT 'TODOS',
  ADD COLUMN IF NOT EXISTS com_desde DATE NULL COMMENT 'Vigente desde (NULL = sin límite)',
  ADD COLUMN IF NOT EXISTS com_hasta DATE NULL COMMENT 'Vigente hasta (NULL = sin límite)';

CREATE TABLE IF NOT EXISTS comunicado_area (
  id_comunicado INT NOT NULL,
  area_cod      INT NOT NULL,
  PRIMARY KEY (id_comunicado, area_cod),
  KEY idx_comarea_area (area_cod),
  CONSTRAINT fk_comarea_comunicado FOREIGN KEY (id_comunicado) REFERENCES comunicados (id_comunicado)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comarea_area FOREIGN KEY (area_cod) REFERENCES area (area_cod)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS comunicado_leido (
  id_comunicado INT      NOT NULL,
  usuario_id    INT      NOT NULL,
  fecha         DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id_comunicado, usuario_id),
  KEY idx_comleido_usuario (usuario_id),
  CONSTRAINT fk_comleido_comunicado FOREIGN KEY (id_comunicado) REFERENCES comunicados (id_comunicado)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_comleido_usuario FOREIGN KEY (usuario_id) REFERENCES usuario (usu_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
