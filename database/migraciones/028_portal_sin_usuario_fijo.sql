-- ============================================================================
-- 028 - El trámite del portal no se atribuye a un usuario fijo
--
-- SP_REGISTRAR_TRAMITE_EXTERNO grababa el primer movimiento de cada trámite que
-- un ciudadano presenta por la mesa de partes virtual con usuario_id = 3. Ese id
-- era el de un usuario concreto de la instalación original, así que:
--
--   1. Todo lo que llegaba por el portal quedaba en el historial como si lo
--      hubiera registrado esa persona, cuando lo presentó el ciudadano.
--   2. En una instalación nueva, sin un usuario con id 3, el portal fallaba al
--      registrar por la clave foránea movimiento.usuario_id -> usuario.usu_id.
--
-- Ahora el movimiento queda con usuario_id NULL: el ciudadano no es un usuario
-- del sistema. Quién lo presentó ya consta en el documento (DNI y nombre del
-- remitente) y en la bitácora ("ciudadano DNI ..."). La columna ya admitía NULL.
--
-- No se reescriben los movimientos anteriores: es historial de auditoría.
--
-- Supuesto que se mantiene: el área con area_cod = 1 es MESA DE PARTES, que es
-- donde entra lo del portal. El instalador (database/instalacion) la crea así.
-- ============================================================================

SET NAMES utf8mb4;

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

  -- usuario_id NULL: lo presentó un ciudadano, no un usuario del sistema
  INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,
    mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)
  VALUES(v_cod,1,1,NOW(),ASUNTO,'PENDIENTE',NULL,RUTA,'--REVISAR--');
END$$
DELIMITER ;
