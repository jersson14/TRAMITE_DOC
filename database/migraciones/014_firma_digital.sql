-- ============================================================================
-- 014 - Firma digital de documentos del trámite
--
-- Cada firma queda registrada con los datos del certificado y la huella SHA-256
-- del archivo antes y después de firmar. El certificado (.pfx) y su contraseña
-- NUNCA se guardan: se usan en memoria durante la firma y se descartan.
--
-- La firma se agrega al PDF por actualización incremental (librería ddn/sapp):
-- el contenido original queda intacto y la firma cubre todo el archivo. Un PDF
-- ya firmado puede recibir más firmas sin invalidar las anteriores.
--
-- firma_codigo es el código corto que va impreso en el sello (con QR) y permite
-- verificar la firma en validar_firma.php.
-- ============================================================================

CREATE TABLE IF NOT EXISTS firma (
  firma_id          BIGINT        NOT NULL AUTO_INCREMENT,
  firma_codigo      CHAR(10)      NOT NULL,
  documento_id      CHAR(12)      NOT NULL,
  anexo_id          BIGINT        NULL COMMENT 'Archivo firmado resultante (documento_anexo)',
  firma_orden       INT           NOT NULL DEFAULT 1 COMMENT 'Número de esta firma dentro del PDF',
  archivo_origen    VARCHAR(255)  NOT NULL,
  archivo_firmado   VARCHAR(255)  NOT NULL,
  hash_origen       CHAR(64)      NOT NULL,
  hash_firmado      CHAR(64)      NOT NULL,
  metodo            ENUM('PFX', 'FIRMA_PERU') NOT NULL DEFAULT 'PFX',
  motivo            VARCHAR(150)  NULL,
  firmante_nombre   VARCHAR(200)  NOT NULL,
  firmante_dni      VARCHAR(15)   NULL,
  cert_emisor       VARCHAR(255)  NULL,
  cert_serie        VARCHAR(80)   NULL,
  cert_desde        DATETIME      NULL,
  cert_hasta        DATETIME      NULL,
  cert_huella       CHAR(64)      NULL COMMENT 'SHA-256 del certificado',
  cert_autofirmado  TINYINT(1)    NOT NULL DEFAULT 0,
  usuario_id        INT           NULL,
  area_id           INT           NULL,
  firma_fecha       DATETIME      NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (firma_id),
  UNIQUE KEY idx_firma_codigo (firma_codigo),
  KEY idx_firma_documento (documento_id),
  KEY idx_firma_anexo (anexo_id),
  CONSTRAINT fk_firma_documento FOREIGN KEY (documento_id) REFERENCES documento (documento_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
