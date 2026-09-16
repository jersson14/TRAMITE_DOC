-- ============================================================================
-- 005 - Varios archivos por trámite (anexos)
--
-- Hasta ahora cada trámite guardaba un único archivo en documento.doc_archivo.
-- En la práctica un expediente llega con el documento principal más sus
-- recaudos (DNI, partida, planos), y había que juntarlo todo en un solo PDF.
--
-- Se conserva doc_archivo como DOCUMENTO PRINCIPAL para no romper nada de lo
-- que ya existe (reportes, seguimiento, ticket) y los demás van a esta tabla.
--
-- Reversible: ver 005_anexos_multiples_revertir.sql
-- ============================================================================

CREATE TABLE IF NOT EXISTS documento_anexo (
  anexo_id      BIGINT       NOT NULL AUTO_INCREMENT,
  documento_id  CHAR(12)     NOT NULL,
  anexo_nombre  VARCHAR(200) NOT NULL COMMENT 'Nombre con el que el usuario subió el archivo',
  anexo_ruta    VARCHAR(255) NOT NULL COMMENT 'Ruta relativa dentro del sistema',
  anexo_bytes   INT          NOT NULL DEFAULT 0,
  anexo_fecha   DATETIME     NOT NULL DEFAULT current_timestamp(),
  usuario_id    INT          NULL,
  PRIMARY KEY (anexo_id),
  KEY idx_anexo_documento (documento_id),
  CONSTRAINT fk_anexo_documento
    FOREIGN KEY (documento_id) REFERENCES documento (documento_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Listado de anexos de un trámite, del más antiguo al más reciente.
DELIMITER $$

DROP PROCEDURE IF EXISTS SP_LISTAR_ANEXOS$$
CREATE PROCEDURE SP_LISTAR_ANEXOS(IN P_DOCUMENTO CHAR(12))
BEGIN
  SELECT
    documento_anexo.anexo_id,
    documento_anexo.documento_id,
    documento_anexo.anexo_nombre,
    documento_anexo.anexo_ruta,
    documento_anexo.anexo_bytes,
    documento_anexo.anexo_fecha,
    DATE_FORMAT(documento_anexo.anexo_fecha, '%d-%m-%Y %H:%i') AS anexo_fecha_texto,
    documento_anexo.usuario_id,
    usuario.usu_usuario
  FROM documento_anexo
  LEFT JOIN usuario ON usuario.usu_id = documento_anexo.usuario_id
  WHERE documento_anexo.documento_id = P_DOCUMENTO
  ORDER BY documento_anexo.anexo_id;
END$$

DELIMITER ;
