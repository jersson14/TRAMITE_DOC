-- ============================================================================
-- Revertir 006 (parcial, a propósito)
--
-- Deshace: copias en la bandeja de recibidos, anexos en el historial y la tabla
-- movimiento_anexo.
--
-- NO deshace la corrección de SP_MODIFICAR_TRAMITE_ESTATUS ni de
-- SP_RECHAZAR_TRAMITE: sus versiones anteriores aceptaban varios trámites a la
-- vez y rechazaban todos los pendientes del sistema. Tampoco vacía el archivo
-- que se completó en las copias, que era un dato faltante y no un cambio.
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS SP_LISTAR_TRAMITE_SEGUIMIENTO$$
CREATE PROCEDURE SP_LISTAR_TRAMITE_SEGUIMIENTO(IN ID CHAR(12))
BEGIN
  SELECT
    movimiento.movimiento_id,
    movimiento.documento_id,
    movimiento.area_origen_id,
    COALESCE(ao.area_nombre, 'EXTERNO') AS area_origen_nombre,
    ad.area_nombre AS area_destino_nombre,
    DATE_FORMAT(movimiento.mov_fecharegistro, '%d-%m-%Y - %H:%i:%s %p') AS fecha_formateada,
    movimiento.mov_fecharegistro,
    movimiento.mov_descripcion,
    movimiento.mov_estatus,
    movimiento.mov_archivo,
    movimiento.mov_acciones
  FROM movimiento
  LEFT JOIN area ao ON movimiento.area_origen_id = ao.area_cod
  INNER JOIN area ad ON movimiento.areadestino_id = ad.area_cod
  WHERE movimiento.documento_id = ID
  ORDER BY movimiento.mov_fecharegistro ASC;
END$$

DROP PROCEDURE IF EXISTS SP_LISTAR_TRAMITE_AREA$$
CREATE PROCEDURE SP_LISTAR_TRAMITE_AREA(IN IDUSUARIO INT)
BEGIN
  DECLARE v_area INT;
  SELECT area_id INTO v_area FROM usuario WHERE usu_id = IDUSUARIO;

  SELECT
    documento.documento_id, documento.doc_expediente, documento.doc_dniremitente,
    CONCAT_WS(' ', documento.doc_nombreremitente, documento.doc_apepatremitente, documento.doc_apematremitente) AS REMITENTE,
    documento.doc_nombreremitente, documento.doc_apepatremitente, documento.doc_apematremitente,
    documento.tipodocumento_id, tipo_documento.tipodo_descripcion, documento.doc_estatus,
    documento.doc_nrodocumento, documento.doc_celularremitente, documento.doc_emailremitente,
    documento.doc_direccionremitente, documento.doc_representacion, documento.doc_ruc,
    documento.doc_empresa, documento.doc_folio, documento.doc_archivo, documento.doc_asunto,
    documento.doc_fecharegistro,
    DATE_FORMAT(documento.doc_fecharegistro, '%d-%m-%Y - %H:%i:%s %p') AS fecha_formateada,
    documento.area_origen, documento.area_destino, documento.area_id, documento.dias_pasados,
    documento.dias_respuesta, documento.acciones, documento.doc_observaciones,
    origen.area_nombre AS origen, destino.area_nombre AS destino
  FROM documento
  INNER JOIN tipo_documento ON documento.tipodocumento_id = tipo_documento.tipodocumento_id
  INNER JOIN area AS origen ON documento.area_origen = origen.area_cod
  INNER JOIN area AS destino ON documento.area_destino = destino.area_cod
  WHERE documento.area_destino = v_area
  ORDER BY documento.doc_fecharegistro DESC;
END$$

DELIMITER ;

DROP TABLE IF EXISTS movimiento_anexo;
