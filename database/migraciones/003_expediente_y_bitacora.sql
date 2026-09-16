-- ============================================================================
-- 003 - Número oficial de expediente y bitácora de auditoría
--
-- Qué resuelve:
--  1. El sistema solo tenía un código interno de seguimiento (D0000041). Ahora
--     cada trámite recibe además un número oficial de expediente con correlativo
--     anual: EXP-2026-000001. El código de seguimiento se conserva intacto para
--     no romper los QR, los tickets ya impresos ni la consulta pública.
--  2. El correlativo se calculaba con MAX(doc_ncorrelativo)+1 sin bloqueo: dos
--     registros simultáneos podían tomar el mismo número y chocar en la clave
--     primaria. Ahora se toma de un contador atómico.
--  3. No había forma de saber quién hizo qué. Se agrega la bitácora.
--
-- Reversible: ver 003_expediente_y_bitacora_revertir.sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. Contador atómico de correlativos
--    corr_anio = 0 para contadores globales (código de seguimiento).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS correlativo (
  corr_anio   INT         NOT NULL,
  corr_tipo   VARCHAR(10) NOT NULL,
  corr_ultimo INT         NOT NULL DEFAULT 0,
  PRIMARY KEY (corr_anio, corr_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 2. Bitácora de auditoría
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bitacora (
  bit_id         BIGINT       NOT NULL AUTO_INCREMENT,
  bit_fecha      DATETIME     NOT NULL DEFAULT current_timestamp(),
  usuario_id     INT          NULL,
  bit_usuario    VARCHAR(150) NULL,
  bit_rol        VARCHAR(50)  NULL,
  bit_accion     VARCHAR(60)  NOT NULL,
  bit_entidad    VARCHAR(40)  NULL,
  bit_entidad_id VARCHAR(40)  NULL,
  bit_detalle    VARCHAR(500) NULL,
  bit_ip         VARCHAR(45)  NULL,
  PRIMARY KEY (bit_id),
  KEY idx_bit_fecha (bit_fecha),
  KEY idx_bit_entidad (bit_entidad, bit_entidad_id),
  KEY idx_bit_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- 3. Número oficial de expediente en el documento
-- ----------------------------------------------------------------------------
ALTER TABLE documento
  ADD COLUMN IF NOT EXISTS doc_expediente VARCHAR(20) NULL AFTER documento_id;

ALTER TABLE documento
  ADD UNIQUE INDEX IF NOT EXISTS idx_doc_expediente (doc_expediente);

-- ----------------------------------------------------------------------------
-- 4. Asignar expediente a los trámites ya registrados, respetando el orden
--    real de llegada dentro de cada año.
-- ----------------------------------------------------------------------------
UPDATE documento d
INNER JOIN (
  SELECT documento_id,
         CONCAT('EXP-', anio, '-', LPAD(fila, 6, '0')) AS expediente
  FROM (
    SELECT documento_id,
           IFNULL(YEAR(doc_fecharegistro), YEAR(CURDATE())) AS anio,
           ROW_NUMBER() OVER (
             PARTITION BY IFNULL(YEAR(doc_fecharegistro), YEAR(CURDATE()))
             ORDER BY doc_fecharegistro, doc_ncorrelativo, documento_id
           ) AS fila
    FROM documento
  ) AS ordenado
) AS x ON x.documento_id = d.documento_id
SET d.doc_expediente = x.expediente
WHERE d.doc_expediente IS NULL;

-- ----------------------------------------------------------------------------
-- 5. Sembrar los contadores con lo ya existente
-- ----------------------------------------------------------------------------
INSERT INTO correlativo (corr_anio, corr_tipo, corr_ultimo)
SELECT 0, 'DOC', IFNULL(MAX(doc_ncorrelativo), 0) FROM documento
ON DUPLICATE KEY UPDATE corr_ultimo = GREATEST(corr_ultimo, VALUES(corr_ultimo));

INSERT INTO correlativo (corr_anio, corr_tipo, corr_ultimo)
SELECT IFNULL(YEAR(doc_fecharegistro), YEAR(CURDATE())), 'EXP', COUNT(*)
FROM documento
GROUP BY IFNULL(YEAR(doc_fecharegistro), YEAR(CURDATE()))
ON DUPLICATE KEY UPDATE corr_ultimo = GREATEST(corr_ultimo, VALUES(corr_ultimo));

-- ----------------------------------------------------------------------------
-- 6. Procedimientos de registro: correlativo atómico + expediente
--    Se conserva la firma y el orden de resultados; la primera columna sigue
--    siendo el código de seguimiento, que es lo que lee el PHP.
-- ----------------------------------------------------------------------------
DELIMITER $$

DROP PROCEDURE IF EXISTS SP_SIGUIENTE_CORRELATIVO$$
CREATE PROCEDURE SP_SIGUIENTE_CORRELATIVO(
  IN  p_anio INT,
  IN  p_tipo VARCHAR(10),
  OUT p_num  INT
)
BEGIN
  -- LAST_INSERT_ID(expr) guarda el valor por sesión: dos registros simultáneos
  -- obtienen números distintos sin necesidad de bloquear la tabla.
  INSERT INTO correlativo (corr_anio, corr_tipo, corr_ultimo)
  VALUES (p_anio, p_tipo, LAST_INSERT_ID(1))
  ON DUPLICATE KEY UPDATE corr_ultimo = LAST_INSERT_ID(corr_ultimo + 1);
  SET p_num = LAST_INSERT_ID();
END$$

DROP PROCEDURE IF EXISTS SP_REGISTRAR_TRAMITE$$
CREATE PROCEDURE SP_REGISTRAR_TRAMITE(
  IN DNI char(8), IN NOMBRE varchar(150), IN APEPAT varchar(50), IN APEMAT varchar(50),
  IN CEL char(9), IN EMAIL varchar(150), IN DIRECCION varchar(255),
  IN REPRESENTACION varchar(50), IN RUC char(12), IN RAZON varchar(255),
  IN AREAPRINCIPAL int(11), IN AREADESTINO int(11), IN TIPO int(11),
  IN NRODOCUMENTO varchar(15), IN ASUNTO varchar(255), IN RUTA varchar(255),
  IN FOLIO int(11), IN IDUSUARIO int(11), IN ACCION varchar(255),
  IN OBSERVA varchar(255), IN RESPU int(11)
)
BEGIN
  DECLARE v_anio INT;
  DECLARE v_num INT;
  DECLARE v_exp INT;
  DECLARE v_cod CHAR(12);
  DECLARE v_expediente VARCHAR(20);

  SET v_anio = YEAR(CURDATE());
  CALL SP_SIGUIENTE_CORRELATIVO(0, 'DOC', v_num);
  CALL SP_SIGUIENTE_CORRELATIVO(v_anio, 'EXP', v_exp);

  SET v_cod = CONCAT('D', LPAD(v_num, 7, '0'));
  SET v_expediente = CONCAT('EXP-', v_anio, '-', LPAD(v_exp, 6, '0'));

  INSERT INTO documento(documento_id,doc_expediente,doc_dniremitente,doc_nombreremitente,
    doc_apepatremitente,doc_apematremitente,doc_celularremitente,doc_emailremitente,
    doc_direccionremitente,doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,
    tipodocumento_id,doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,
    dias_pasados,acciones,doc_observaciones,dias_respuesta)
  VALUES(v_cod,v_expediente,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,
    RUC,RAZON,AREAPRINCIPAL,AREADESTINO,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,v_num,
    0,ACCION,OBSERVA,RESPU);

  SELECT v_cod AS codigo, v_expediente AS expediente;

  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(v_cod,AREAPRINCIPAL,AREADESTINO,NOW(),ASUNTO,'PENDIENTE',IDUSUARIO,RUTA,ACCION);
END$$

DROP PROCEDURE IF EXISTS SP_REGISTRAR_TRAMITE_UL$$
CREATE PROCEDURE SP_REGISTRAR_TRAMITE_UL(
  IN DNI char(8), IN NOMBRE varchar(150), IN APEPAT varchar(50), IN APEMAT varchar(50),
  IN CEL char(9), IN EMAIL varchar(150), IN DIRECCION varchar(255),
  IN REPRESENTACION varchar(50), IN RUC char(12), IN RAZON varchar(255),
  IN AREAPRINCIPAL int(11), IN AREADESTINO int(11), IN TIPO int(11),
  IN NRODOCUMENTO varchar(15), IN ASUNTO varchar(255), IN RUTA varchar(255),
  IN FOLIO int(11), IN IDUSUARIO int(11), IN ACCION varchar(255),
  IN OBSERVA varchar(255), IN RESPU int(11)
)
BEGIN
  DECLARE v_anio INT;
  DECLARE v_num INT;
  DECLARE v_exp INT;
  DECLARE v_cod CHAR(12);
  DECLARE v_expediente VARCHAR(20);

  SET v_anio = YEAR(CURDATE());
  CALL SP_SIGUIENTE_CORRELATIVO(0, 'DOC', v_num);
  CALL SP_SIGUIENTE_CORRELATIVO(v_anio, 'EXP', v_exp);

  SET v_cod = CONCAT('D', LPAD(v_num, 7, '0'));
  SET v_expediente = CONCAT('EXP-', v_anio, '-', LPAD(v_exp, 6, '0'));

  INSERT INTO documento(documento_id,doc_expediente,doc_dniremitente,doc_nombreremitente,
    doc_apepatremitente,doc_apematremitente,doc_celularremitente,doc_emailremitente,
    doc_direccionremitente,doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,
    tipodocumento_id,doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,
    dias_pasados,acciones,doc_observaciones,dias_respuesta)
  VALUES(v_cod,v_expediente,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,
    RUC,RAZON,AREAPRINCIPAL,AREADESTINO,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,v_num,
    0,ACCION,OBSERVA,RESPU);

  SELECT v_cod AS codigo, v_expediente AS expediente;

  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(v_cod,AREAPRINCIPAL,AREADESTINO,NOW(),ASUNTO,'PENDIENTE',IDUSUARIO,RUTA,ACCION);
END$$

DROP PROCEDURE IF EXISTS SP_REGISTRAR_TRAMITE_EXTERNO$$
CREATE PROCEDURE SP_REGISTRAR_TRAMITE_EXTERNO(
  IN DNI char(8), IN NOMBRE varchar(150), IN APEPAT varchar(50), IN APEMAT varchar(50),
  IN CEL char(9), IN EMAIL varchar(150), IN DIRECCION varchar(255),
  IN REPRESENTACION varchar(50), IN RUC char(12), IN RAZON varchar(255),
  IN TIPO int(11), IN NRODOCUMENTO varchar(15), IN ASUNTO varchar(255),
  IN RUTA varchar(255), IN FOLIO int(11)
)
BEGIN
  DECLARE v_anio INT;
  DECLARE v_num INT;
  DECLARE v_exp INT;
  DECLARE v_cod CHAR(12);
  DECLARE v_expediente VARCHAR(20);

  SET v_anio = YEAR(CURDATE());
  CALL SP_SIGUIENTE_CORRELATIVO(0, 'DOC', v_num);
  CALL SP_SIGUIENTE_CORRELATIVO(v_anio, 'EXP', v_exp);

  SET v_cod = CONCAT('D', LPAD(v_num, 7, '0'));
  SET v_expediente = CONCAT('EXP-', v_anio, '-', LPAD(v_exp, 6, '0'));

  INSERT INTO documento(documento_id,doc_expediente,doc_dniremitente,doc_nombreremitente,
    doc_apepatremitente,doc_apematremitente,doc_celularremitente,doc_emailremitente,
    doc_direccionremitente,doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,
    tipodocumento_id,doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,
    dias_pasados,acciones,doc_observaciones,dias_respuesta)
  VALUES(v_cod,v_expediente,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,
    RUC,RAZON,1,1,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,v_num,
    0,'--REVISAR--','','');

  SELECT v_cod AS codigo, v_expediente AS expediente;

  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(v_cod,1,1,NOW(),ASUNTO,'PENDIENTE',3,RUTA,'--REVISAR--');
END$$

DELIMITER ;
