-- ============================================================================
-- 008 - Fechas legibles en español
--
-- Las fechas mezclaban reloj de 24 horas con "PM" ("22:07:08 PM") y el mes salía
-- en inglés ("15 de September del 2026"), también en la consulta pública del
-- ciudadano. SP_LISTAR_TIPO_DOCUMENTO usaba %h:%m:%s, que pone el MES donde van
-- los minutos. Ahora:
--   con hora ..... 15/09/2026 22:07
--   larga ........ 15 de septiembre de 2026, 22:07
-- El nombre del mes en español depende de lc_time_names, que la conexión fija
-- en 'es_PE' (model/model_conexion.php y view/MPDF/conexion.php).
--
-- Además, SP_CARGAR_EXPEDIENTE (selector del rastreo de un área) devuelve el
-- número de expediente, como ÚLTIMA columna para no correr posiciones.
--
-- Solo se tocan cadenas de formato; ninguna pantalla desarma estas fechas.
-- Procedimientos afectados (19):
--   SP_CARGAR_EXPEDIENTE: "%d-%m-%Y - %H:%i:%s %p" x1, + doc_expediente al final
--   SP_CARGAR_SEGUIMIENTO_TRAMITE: "%d de %M del %Y - %H:%i:%s %p" x1
--   SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_CARGAR_TICKET: "%d de %M del %Y - %H:%i:%s %p" x1
--   SP_LISTAR_ANEXOS: "%d-%m-%Y %H:%i" x1
--   SP_LISTAR_AREA: "%d-%m-%Y - %H:%i:%s" x1
--   SP_LISTAR_NOTIFICACION_TRAMITE: "%d-%m-%Y - %H:%i:%s" x1
--   SP_LISTAR_TIPO_DOCUMENTO: "%d-%m-%Y  %h:%m:%s" x1
--   SP_LISTAR_TRAMITE: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA1: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA_ESTADO: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA_ESTADO_TA: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA_FECHAS: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA_FECHAS_TA: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA_TIPO_DOC: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_LISTAR_TRAMITE_SEGUIMIENTO: "%d-%m-%Y - %H:%i:%s %p" x1
--   SP_TRAER_DATOS_EXPEDIENTE_ADMIN: "%d-%m-%Y - %H:%i:%s %p" x1
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `SP_CARGAR_EXPEDIENTE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_EXPEDIENTE`(IN `ID` INT)
BEGIN
    DECLARE IDAREA INT;

    
    SELECT area_id 
    INTO IDAREA 
    FROM usuario 
    WHERE usu_id = ID;

    
    SELECT
        d.documento_id, 
        d.doc_dniremitente, 
        CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS REMITENTE, 
        d.doc_nombreremitente, 
        d.doc_apepatremitente, 
        d.doc_apematremitente, 
        d.tipodocumento_id, 
        td.tipodo_descripcion, 
        d.doc_estatus, 
        d.doc_nrodocumento, 
        d.doc_celularremitente, 
        d.doc_emailremitente, 
        d.doc_direccionremitente, 
        d.doc_representacion, 
        d.doc_ruc, 
        d.doc_empresa, 
        d.doc_folio, 
        d.doc_archivo, 
        d.doc_asunto, 
        d.doc_fecharegistro, 
        DATE_FORMAT(d.doc_fecharegistro, "%d/%m/%Y %H:%i") AS fecha_formateada,
        d.area_origen, 
        d.area_destino, 
        d.area_id,
        d.dias_pasados,
        d.dias_respuesta, 	
        d.acciones,
        d.doc_observaciones,
        origen.area_nombre AS origen, 
        destino.area_nombre AS destino,
        d.doc_expediente
    FROM documento d
    INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
    INNER JOIN area AS origen ON d.area_origen = origen.area_cod
    INNER JOIN area AS destino ON d.area_destino = destino.area_cod
    WHERE d.area_destino = IDAREA OR d.area_origen = IDAREA
    ORDER BY d.doc_fecharegistro DESC;
END$$

DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE`(IN `NUMERO` VARCHAR(12), IN `DNI` VARCHAR(8))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M de %Y, %H:%i") as fecha_formateada,
        documento.doc_nrodocumento,
	documento.doc_expediente

FROM
	documento
WHERE documento.documento_id=NUMERO AND documento.doc_dniremitente=DNI
ORDER BY doc_fecharegistro ASC$$

DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE`(IN `NUMERO` VARCHAR(12))
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
        movimiento.mov_archivo
    FROM movimiento
    LEFT JOIN area ao ON movimiento.area_origen_id = ao.area_cod
    INNER JOIN area ad ON movimiento.areadestino_id = ad.area_cod
    LEFT JOIN area ON movimiento.areadestino_id = area.area_cod
    WHERE movimiento.documento_id = NUMERO
    ORDER BY movimiento.mov_fecharegistro ASC;
END$$

DROP PROCEDURE IF EXISTS `SP_CARGAR_TICKET`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_TICKET`(IN `NUMERO` VARCHAR(12))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M de %Y, %H:%i") as fecha_formateada,
	documento.doc_expediente

FROM
	documento
WHERE documento.documento_id=NUMERO$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_ANEXOS`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_ANEXOS`(IN P_DOCUMENTO CHAR(12))
BEGIN
  SELECT
    documento_anexo.anexo_id,
    documento_anexo.documento_id,
    documento_anexo.anexo_nombre,
    documento_anexo.anexo_ruta,
    documento_anexo.anexo_bytes,
    documento_anexo.anexo_fecha,
    DATE_FORMAT(documento_anexo.anexo_fecha, '%d/%m/%Y %H:%i') AS anexo_fecha_texto,
    documento_anexo.usuario_id,
    usuario.usu_usuario
  FROM documento_anexo
  LEFT JOIN usuario ON usuario.usu_id = documento_anexo.usuario_id
  WHERE documento_anexo.documento_id = P_DOCUMENTO
  ORDER BY documento_anexo.anexo_id;
END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_AREA`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_AREA`()
SELECT
	area.area_cod, 
	area.area_nombre,
	date_format(area_fecha_registro, "%d/%m/%Y %H:%i") as fecha_formateada,
	area.area_fecha_registro, 
	area.area_estado
FROM
	area
	ORDER BY area_nombre asc$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_NOTIFICACION_TRAMITE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_NOTIFICACION_TRAMITE`(IN `IDAREA` INT)
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id, 
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod


		WHERE area_destino=IDAREA AND doc_estatus="PENDIENTE"$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TIPO_DOCUMENTO`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TIPO_DOCUMENTO`()
SELECT
	tipo_documento.tipodocumento_id, 
	tipo_documento.tipodo_descripcion, 
	tipo_documento.tipodo_estado,
	tipo_documento.requisitos,
	DATE_FORMAT(tipodo_feregistro, "%d/%m/%Y %H:%i")as fecha_tipo,
	tipo_documento.tipodo_feregistro
FROM
	tipo_documento$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE`()
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
		date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,

	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	documento.dias_pasados,
	dias_respuesta,
	acciones,
	doc_observaciones,
	dias_respuesta,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
		ORDER BY doc_fecharegistro desc$$

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
	documento.doc_expediente
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

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA1`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA1`(IN `IDUSUARIO` INT)
BEGIN
    DECLARE IDAREA INT;
    SET @IDAREA := (SELECT area_id FROM usuario WHERE usu_id = IDUSUARIO);
    
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
        DATE_FORMAT(doc_fecharegistro, "%d/%m/%Y %H:%i") AS fecha_formateada,
        documento.area_origen, 
        documento.area_destino, 
        documento.area_id,
        documento.dias_pasados,
        documento.dias_respuesta,
        documento.acciones,
        documento.doc_observaciones,
        origen.area_nombre AS origen, 
        destino.area_nombre AS destino,
	documento.doc_expediente
 FROM
        documento
        INNER JOIN tipo_documento ON documento.tipodocumento_id = tipo_documento.tipodocumento_id
        INNER JOIN area AS origen ON documento.area_origen = origen.area_cod
        INNER JOIN area AS destino ON documento.area_destino = destino.area_cod
    WHERE 
        documento.documento_id IN (
            SELECT d.documento_id 
            FROM documento d
            WHERE d.area_origen = @IDAREA OR d.area_destino = @IDAREA
            
            UNION
            
            SELECT documento_id
            FROM movimiento
            WHERE area_origen = @IDAREA OR area_destino = @IDAREA
        )
    ORDER BY documento.doc_fecharegistro DESC;
END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_ESTADO`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_ESTADO`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `ESTADO` VARCHAR(20))
BEGIN
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
	WHERE doc_fecharegistro BETWEEN FINICIO AND FFIN AND doc_estatus=ESTADO
			ORDER BY doc_fecharegistro desc;
	END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_ESTADO_TA`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_ESTADO_TA`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `ESTADO` VARCHAR(20), IN `IDAREA` INT)
BEGIN
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
	WHERE doc_fecharegistro BETWEEN FINICIO AND FFIN AND doc_estatus=ESTADO
	HAVING area_origen=IDAREA or area_destino=IDAREA
			ORDER BY doc_fecharegistro desc;
	END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_FECHAS`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_FECHAS`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `IDAREA` INT)
BEGIN
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
	WHERE doc_fecharegistro BETWEEN FINICIO AND FFIN
HAVING	area_destino=IDAREA
			ORDER BY doc_fecharegistro desc;

	END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_FECHAS_TA`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_FECHAS_TA`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `IDAREA` INT)
BEGIN
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
	WHERE doc_fecharegistro BETWEEN FINICIO AND FFIN
		HAVING area_origen=IDAREA or area_destino=IDAREA

				ORDER BY doc_fecharegistro desc;

	END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_TIPO_DOC`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_TIPO_DOC`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `TIPO` INT)
BEGIN
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
	WHERE doc_fecharegistro BETWEEN FINICIO AND FFIN AND documento.tipodocumento_id=TIPO
				ORDER BY doc_fecharegistro desc;

	END$$

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `TIPO_DOC` INT, IN `IDAREA` INT)
BEGIN
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
	date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
	WHERE doc_fecharegistro BETWEEN FINICIO AND FFIN AND documento.tipodocumento_id=TIPO_DOC
	HAVING area_origen=IDAREA or area_destino=IDAREA
				ORDER BY doc_fecharegistro desc;

	END$$

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
      WHERE ma.movimiento_id = movimiento.movimiento_id) AS anexos
  FROM movimiento
  LEFT JOIN area ao ON movimiento.area_origen_id = ao.area_cod
  INNER JOIN area ad ON movimiento.areadestino_id = ad.area_cod
  WHERE movimiento.documento_id = ID
  ORDER BY movimiento.mov_fecharegistro ASC, movimiento.movimiento_id ASC;
END$$

DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS_EXPEDIENTE_ADMIN`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE_ADMIN`()
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente) AS REMITENTE, 
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
		date_format(doc_fecharegistro, "%d/%m/%Y %H:%i") as fecha_formateada,

	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	documento.dias_pasados,
	dias_respuesta,
	acciones,
	doc_observaciones,
	dias_respuesta,
	origen.area_nombre as origen, 
	destino.area_nombre as destino,
	documento.doc_expediente

FROM
	documento
	INNER JOIN
	tipo_documento
	ON 
		documento.tipodocumento_id = tipo_documento.tipodocumento_id
	INNER JOIN
	area AS origen
	ON 
		documento.area_origen = origen.area_cod
	INNER JOIN
	area AS destino
	ON 
		documento.area_destino = destino.area_cod
		ORDER BY doc_fecharegistro desc$$

DELIMITER ;
