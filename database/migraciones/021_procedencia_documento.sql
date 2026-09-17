-- ============================================================================
-- 021 - Procedencia del documento (interno / externo) y firma en la salida
--
-- Motivo: el sistema no distinguía un trámite que la entidad produce de uno que
-- le llega de afuera. SP_REGISTRAR_TRAMITE_EXTERNO grababa area_origen=1 y
-- area_destino=1 (mesa de partes), exactamente igual que un registro interno
-- hecho por mesa de partes, así que no había forma de saber quién era el autor
-- del PDF.
--
-- Esa distinción importa para la firma digital:
--   - INTERNO: el documento lo redacta la entidad. Debe firmarlo el responsable
--     ANTES de enviarlo. El sistema ofrece "Firmar".
--   - EXTERNO: el documento lo trae el ciudadano u otra entidad. Firmarlo sería
--     atribuirse autoría ajena. El sistema solo ofrece "Verificar firma".
--
-- Los trámites ya registrados quedan como INTERNO (valor por defecto): es la
-- decisión tomada al aplicar esta migración, y se puede corregir a mano con
--   UPDATE documento SET doc_procedencia='EXTERNO' WHERE documento_id IN (...);
--
-- La columna va al final de la tabla a propósito: varios SP y listados hacen
-- SELECT con columnas posicionales y se rompen si se inserta en medio.
-- ============================================================================

-- Idempotente: si la columna ya existe, no hace nada.
SET @existe := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento' AND COLUMN_NAME = 'doc_procedencia'
);
SET @sql := IF(@existe = 0,
  "ALTER TABLE documento ADD COLUMN doc_procedencia ENUM('INTERNO','EXTERNO') NOT NULL DEFAULT 'INTERNO' COMMENT 'INTERNO: lo produce la entidad (se firma). EXTERNO: llega de afuera (solo se verifica)'",
  "DO 0");
PREPARE p FROM @sql; EXECUTE p; DEALLOCATE PREPARE p;

-- ---------------------------------------------------------------------------
-- El portal público siempre registra documentos externos.
-- Mismo cuerpo que antes; lo único que cambia es doc_procedencia='EXTERNO'.
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_REGISTRAR_TRAMITE_EXTERNO;

DELIMITER $$
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
    dias_pasados,acciones,doc_observaciones,dias_respuesta,doc_procedencia)
  VALUES(v_cod,v_expediente,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,
    RUC,RAZON,1,1,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,v_num,
    0,'--REVISAR--','','','EXTERNO');

  SELECT v_cod AS codigo, v_expediente AS expediente;

  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(v_cod,1,1,NOW(),ASUNTO,'PENDIENTE',3,RUTA,'--REVISAR--');
END$$
DELIMITER ;
