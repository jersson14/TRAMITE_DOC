-- ============================================================================
-- 010 - Acuse de recepción
--
-- Hasta ahora "Aceptar" solo cambiaba el estado del trámite: no quedaba quién lo
-- recibió ni cuándo, y las áreas que recibían copia no tenían cómo dejar
-- constancia de que la vieron. El acuse se guarda por ENVÍO (movimiento):
--   - El área de destino da acuse al aceptar.
--   - Un área en copia da acuse con "Confirmar recepción", sin tocar el estado.
--
-- Las columnas nuevas de los procedimientos van AL FINAL: varias pantallas leen
-- los resultados por posición (ver migración 007).
-- Generada a partir de los procedimientos vigentes.
-- ============================================================================

ALTER TABLE movimiento
  ADD COLUMN IF NOT EXISTS mov_recibido_fecha DATETIME NULL,
  ADD COLUMN IF NOT EXISTS mov_recibido_usuario INT NULL;

DELIMITER $$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_SEGUIMIENTO`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_SEGUIMIENTO`(IN ID CHAR(12))
BEGIN
  SET SESSION group_concat_max_len = 65535;

  SELECT
    movimiento.movimiento_id,
    movimiento.documento_id,
    movimiento.area_origen_id,
    COALESCE(ao.area_nombre, 'EXTERNO') AS area_origen_nombre,
    ad.area_nombre AS area_destino_nombre,
    DATE_FORMAT(movimiento.mov_fecharegistro, '%d/%m/%Y %H:%i') AS fecha_formateada,
    movimiento.mov_fecharegistro,
    movimiento.mov_descripcion,
    movimiento.mov_estatus,
    movimiento.mov_archivo,
    movimiento.mov_acciones,
    (SELECT GROUP_CONCAT(
              CONCAT(da.anexo_ruta, CHAR(9 USING utf8mb4), da.anexo_nombre)
              ORDER BY da.anexo_id SEPARATOR '\n')
       FROM movimiento_anexo ma
       INNER JOIN documento_anexo da ON da.anexo_id = ma.anexo_id
      WHERE ma.movimiento_id = movimiento.movimiento_id) AS anexos,
    DATE_FORMAT(movimiento.mov_recibido_fecha, '%d/%m/%Y %H:%i') AS recibido_fecha,
    (SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario) FROM usuario u LEFT JOIN empleado e ON e.empleado_id = u.empleado_id WHERE u.usu_id = movimiento.mov_recibido_usuario) AS recibido_por
  FROM movimiento
  LEFT JOIN area ao ON movimiento.area_origen_id = ao.area_cod
  INNER JOIN area ad ON movimiento.areadestino_id = ad.area_cod
  WHERE movimiento.documento_id = ID
  ORDER BY movimiento.mov_fecharegistro ASC, movimiento.movimiento_id ASC;
END$$

DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE`(IN NUMERO VARCHAR(12))
BEGIN
    SELECT DISTINCT
        movimiento.movimiento_id,
        movimiento.documento_id,
        area.area_cod,
        COALESCE(ao.area_nombre, 'EXTERNO') AS area_origen_nombre,
        ad.area_nombre AS area_destino_nombre,
        DATE_FORMAT(movimiento.mov_fecharegistro, "%d/%m/%Y %H:%i") AS fecha_formateada,
        movimiento.mov_fecharegistro,
        movimiento.mov_descripcion,
        movimiento.mov_estatus,
        movimiento.mov_acciones,
        movimiento.mov_archivo,
    DATE_FORMAT(movimiento.mov_recibido_fecha, '%d/%m/%Y %H:%i') AS recibido_fecha
  FROM movimiento
    LEFT JOIN area ao ON movimiento.area_origen_id = ao.area_cod
    INNER JOIN area ad ON movimiento.areadestino_id = ad.area_cod
    LEFT JOIN area ON movimiento.areadestino_id = area.area_cod
    WHERE movimiento.documento_id = NUMERO
    ORDER BY movimiento.mov_fecharegistro ASC, movimiento.movimiento_id ASC;
END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA`(IN IDUSUARIO INT)
BEGIN
  DECLARE v_area INT;
  SELECT area_id INTO v_area FROM usuario WHERE usu_id = IDUSUARIO;

  SELECT
    documento.documento_id,
    documento.doc_dniremitente,
    CONCAT_WS(' ', documento.doc_nombreremitente, documento.doc_apepatremitente, documento.doc_apematremitente) AS REMITENTE,
    documento.doc_nombreremitente,
    documento.doc_apepatremitente,
    documento.doc_apematremitente,
    documento.tipodocumento_id,
    tipo_documento.tipodo_descripcion,
    documento.doc_estatus,
    documento.doc_nrodocumento,
    documento.doc_celularremitente,
    documento.doc_emailremitente,
    documento.doc_direccionremitente,
    documento.doc_representacion,
    documento.doc_ruc,
    documento.doc_empresa,
    documento.doc_folio,
    documento.doc_archivo,
    documento.doc_asunto,
    documento.doc_fecharegistro,
    DATE_FORMAT(documento.doc_fecharegistro, '%d/%m/%Y %H:%i') AS fecha_formateada,
    documento.area_origen,
    documento.area_destino,
    documento.area_id,
    documento.dias_pasados,
    documento.dias_respuesta,
    documento.acciones,
    documento.doc_observaciones,
    origen.area_nombre AS origen,
    destino.area_nombre AS destino,
    CASE WHEN documento.area_destino = v_area THEN 0 ELSE 1 END AS es_copia,
	documento.doc_expediente,
    (SELECT DATE_FORMAT(MAX(m2.mov_recibido_fecha), '%d/%m/%Y %H:%i') FROM movimiento m2 WHERE m2.documento_id = documento.documento_id   AND m2.areadestino_id = v_area   AND (CASE WHEN documento.area_destino = v_area             THEN m2.mov_descripcion NOT LIKE 'COPIA - %'             ELSE m2.mov_descripcion LIKE 'COPIA - %' END)) AS acuse_fecha
  FROM documento
  INNER JOIN tipo_documento ON documento.tipodocumento_id = tipo_documento.tipodocumento_id
  INNER JOIN area AS origen ON documento.area_origen = origen.area_cod
  INNER JOIN area AS destino ON documento.area_destino = destino.area_cod
  WHERE documento.area_destino = v_area
     OR EXISTS (SELECT 1 FROM movimiento m
                 WHERE m.documento_id = documento.documento_id
                   AND m.areadestino_id = v_area
                   AND m.mov_descripcion LIKE 'COPIA - %')
  ORDER BY documento.doc_fecharegistro DESC;
END$$

DELIMITER ;
