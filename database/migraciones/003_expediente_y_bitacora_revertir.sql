-- ============================================================================
-- Revertir 003 - Deja la base como estaba antes del expediente y la bitácora.
-- Ojo: borra la bitácora acumulada y los números de expediente asignados.
-- ============================================================================

ALTER TABLE documento DROP INDEX IF EXISTS idx_doc_expediente;
ALTER TABLE documento DROP COLUMN IF EXISTS doc_expediente;

DROP TABLE IF EXISTS bitacora;
DROP TABLE IF EXISTS correlativo;

DELIMITER $$

DROP PROCEDURE IF EXISTS SP_SIGUIENTE_CORRELATIVO$$

-- Se restaura el cálculo original del código de seguimiento.
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
  SET @cantidad :=(SELECT IFNULL(MAX(doc_ncorrelativo),0) FROM documento);
  SET @cod := CONCAT('D', LPAD(@cantidad + 1, 7, '0'));
  INSERT INTO documento(documento_id,doc_dniremitente,doc_nombreremitente,doc_apepatremitente,
    doc_apematremitente,doc_celularremitente,doc_emailremitente,doc_direccionremitente,
    doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,tipodocumento_id,
    doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,dias_pasados,
    acciones,doc_observaciones,dias_respuesta)
  VALUES(@cod,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,RUC,RAZON,
    AREAPRINCIPAL,AREADESTINO,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,(@cantidad+1),0,
    ACCION,OBSERVA,RESPU);
  SELECT @cod;
  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(@cod,AREAPRINCIPAL,AREADESTINO,NOW(),ASUNTO,'PENDIENTE',IDUSUARIO,RUTA,ACCION);
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
  SET @cantidad :=(SELECT IFNULL(MAX(doc_ncorrelativo),0) FROM documento);
  SET @cod := CONCAT('D', LPAD(@cantidad + 1, 7, '0'));
  INSERT INTO documento(documento_id,doc_dniremitente,doc_nombreremitente,doc_apepatremitente,
    doc_apematremitente,doc_celularremitente,doc_emailremitente,doc_direccionremitente,
    doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,tipodocumento_id,
    doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,dias_pasados,
    acciones,doc_observaciones,dias_respuesta)
  VALUES(@cod,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,RUC,RAZON,
    AREAPRINCIPAL,AREADESTINO,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,(@cantidad+1),0,
    ACCION,OBSERVA,RESPU);
  SELECT @cod;
  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(@cod,AREAPRINCIPAL,AREADESTINO,NOW(),ASUNTO,'PENDIENTE',IDUSUARIO,RUTA,ACCION);
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
  SET @cantidad :=(SELECT IFNULL(MAX(doc_ncorrelativo),0) FROM documento);
  SET @cod := CONCAT('D', LPAD(@cantidad + 1, 7, '0'));
  INSERT INTO documento(documento_id,doc_dniremitente,doc_nombreremitente,doc_apepatremitente,
    doc_apematremitente,doc_celularremitente,doc_emailremitente,doc_direccionremitente,
    doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,tipodocumento_id,
    doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,dias_pasados,
    acciones,doc_observaciones,dias_respuesta)
  VALUES(@cod,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,RUC,RAZON,
    1,1,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,(@cantidad+1),0,'--REVISAR--','','');
  SELECT @cod;
  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(@cod,1,1,NOW(),ASUNTO,'PENDIENTE',3,RUTA,'--REVISAR--');
END$$

DELIMITER ;
