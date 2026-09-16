-- ============================================================================
-- 007 - El expediente pasa al final de las columnas
--
-- La 004 agregó documento.doc_expediente como SEGUNDA columna de estos
-- procedimientos. Las tablas leen las columnas por nombre y no lo notaron, pero
-- varias pantallas las leen por POSICIÓN (data[0][1], data[0][2]...) y todo se
-- corrió una casilla:
--   - Rastreo interno: en el campo DNI se cargaba el Nº de expediente, así que
--     la búsqueda nunca encontraba el trámite.
--   - Consulta pública del ciudadano (seguimiento.php): "Remitente" mostraba el
--     DNI y la fecha mostraba el nombre del mes.
-- Con el expediente al final, las lecturas por nombre siguen igual y las lecturas
-- por posición recuperan su orden original.
--
-- Generada a partir de los procedimientos vigentes; solo se mueve una columna.
-- Procedimientos afectados (17):
--   SP_CARGAR_SEGUIMIENTO_TRAMITE (1)
--   SP_CARGAR_TICKET (1)
--   SP_LISTAR_NOTIFICACION_TRAMITE (1)
--   SP_LISTAR_TRAMITE (1)
--   SP_LISTAR_TRAMITE_AREA (1)
--   SP_LISTAR_TRAMITE_AREA1 (1)
--   SP_LISTAR_TRAMITE_AREA_BUSCAR (1)
--   SP_LISTAR_TRAMITE_AREA_ESTADO (1)
--   SP_LISTAR_TRAMITE_AREA_ESTADO_TA (1)
--   SP_LISTAR_TRAMITE_AREA_FECHAS (1)
--   SP_LISTAR_TRAMITE_AREA_FECHAS_TA (1)
--   SP_LISTAR_TRAMITE_AREA_TIPO_DOC (1)
--   SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA (1)
--   SP_LISTAR_TRAMITE_ESTADO (1)
--   SP_TOTAL_DOCUMENTOS_PENDIENTES2 (1)
--   SP_TRAER_DATOS_EXPEDIENTE (1)
--   SP_TRAER_DATOS_EXPEDIENTE_ADMIN (1)
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE`(IN `NUMERO` VARCHAR(12), IN `DNI` VARCHAR(8))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M del %Y - %H:%i:%s %p") as fecha_formateada,
        documento.doc_nrodocumento,
	documento.doc_expediente

FROM
	documento
WHERE documento.documento_id=NUMERO AND documento.doc_dniremitente=DNI
ORDER BY doc_fecharegistro ASC$$

DROP PROCEDURE IF EXISTS `SP_CARGAR_TICKET`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_TICKET`(IN `NUMERO` VARCHAR(12))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M del %Y - %H:%i:%s %p") as fecha_formateada,
	documento.doc_expediente

FROM
	documento
WHERE documento.documento_id=NUMERO$$

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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s") as fecha_formateada,
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
		date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,

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
    DATE_FORMAT(documento.doc_fecharegistro, '%d-%m-%Y - %H:%i:%s %p') AS fecha_formateada,
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
        DATE_FORMAT(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") AS fecha_formateada,
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

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_BUSCAR`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_BUSCAR`(IN `IDAREA` INT, IN `ESTADO` VARCHAR(20))
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
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	documento.dias_pasados,
	acciones,
	doc_observaciones, 	
	documento.dias_respuesta,
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
	WHERE area_origen=IDAREA AND documento.doc_estatus=ESTADO
				ORDER BY doc_fecharegistro desc$$

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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
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
	date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
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

DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_ESTADO`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_ESTADO`(IN `ESTADO` VARCHAR(20))
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
	documento.area_origen, 
	documento.area_destino, 
	documento.area_id,
	documento.dias_pasados,
		documento.dias_respuesta, 	

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
		WHERE doc_estatus=ESTADO
		ORDER BY doc_fecharegistro DESC$$

DROP PROCEDURE IF EXISTS `SP_TOTAL_DOCUMENTOS_PENDIENTES2`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_PENDIENTES2`(IN `IDUSUARIO` INT)
BEGIN
DECLARE IDAREA INT;
SET @IDAREA :=(SELECT area_id FROM usuario WHERE usu_id=IDUSUARIO);
SELECT
COUNT(documento.documento_id)as total,
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
		WHERE documento.area_destino=@IDAREA;
		END$$

DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS_EXPEDIENTE`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE`(IN `ID` CHAR(12))
SELECT
	documento.documento_id,
	documento.doc_dniremitente,
	documento.doc_nombreremitente,
	documento.doc_apepatremitente,
	documento.doc_apematremitente,
	documento.doc_celularremitente,
	documento.doc_expediente

FROM
	documento 
WHERE
	documento.documento_id=ID$$

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
		date_format(doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,

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
