-- =====================================================================
-- 001 - Unificar collation de la base de datos y de los procedimientos
-- Problema: tablas en utf8mb4_unicode_ci y parámetros de procedimientos en
-- utf8mb4_general_ci provocan "Illegal mix of collations" (seguimiento,
-- detalle de expediente, eliminar trámite, etc.).
-- Solución: collation por defecto utf8mb4_unicode_ci y recrear los
-- procedimientos para que sus parámetros la hereden.
-- =====================================================================
ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: sistema_tramite
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping routines for database 'sistema_tramite'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_DNI_UL` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_DNI_UL`(IN `ID` INT)
SELECT
	usuario.usu_id, 
	empleado.emple_nrodocumento,
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat,
	CONCAT_WS(' ',empleado.emple_nombre,empleado.emple_apepat,empleado.emple_apemat) AS USUARIO,
	area.area_nombre, 
	usuario.usu_usuario, 
	usuario.usu_contra, 
	usuario.usu_feccreacion, 
	usuario.usu_fecupdate, 
	usuario.empleado_id, 
	usuario.usu_observacion, 
	usuario.usu_estatus, 
	usuario.area_id, 
	usuario.usu_rol, 
	usuario.empresa_id, 
	area.area_cod,
	empleado.empl_fotoperfil
FROM
	usuario
	INNER JOIN
	area
	ON 
		usuario.area_id = area.area_cod
	INNER JOIN
	empleado
	ON 
		usuario.empleado_id = empleado.empleado_id
	where usuario.usu_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_EXPEDIENTE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_EXPEDIENTE`(IN `ID` INT)
BEGIN
    DECLARE IDAREA INT;

    -- Obtener el área del usuario
    SELECT area_id 
    INTO IDAREA 
    FROM usuario 
    WHERE usu_id = ID;

    -- Listar documentos donde mi área es origen o destino
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
        DATE_FORMAT(d.doc_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") AS fecha_formateada,
        d.area_origen, 
        d.area_destino, 
        d.area_id,
        d.dias_pasados,
        d.dias_respuesta, 	
        d.acciones,
        d.doc_observaciones,
        origen.area_nombre AS origen, 
        destino.area_nombre AS destino
    FROM documento d
    INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
    INNER JOIN area AS origen ON d.area_origen = origen.area_cod
    INNER JOIN area AS destino ON d.area_destino = destino.area_cod
    WHERE d.area_destino = IDAREA OR d.area_origen = IDAREA
    ORDER BY d.doc_fecharegistro DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE`(IN `NUMERO` VARCHAR(12), IN `DNI` VARCHAR(8))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M del %Y - %H:%i:%s %p") as fecha_formateada,
        documento.doc_nrodocumento
FROM
	documento
WHERE documento.documento_id=NUMERO AND documento.doc_dniremitente=DNI
ORDER BY doc_fecharegistro ASC ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE`(IN `NUMERO` VARCHAR(12))
BEGIN
    SELECT DISTINCT
        movimiento.movimiento_id, 
        movimiento.documento_id,
        area.area_cod,
        COALESCE(ao.area_nombre, 'EXTERNO') AS area_origen_nombre,
        ad.area_nombre AS area_destino_nombre,
        DATE_FORMAT(movimiento.mov_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") AS fecha_formateada,
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SELECT_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_AREA`()
SELECT
	area.area_cod, 
	area.area_nombre, 
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat,
	CONCAT_WS(' ',empleado.emple_nombre,empleado.emple_apepat,empleado.emple_apemat) AS ENCARGADO

FROM
	area
	INNER JOIN
	usuario
	ON 
		area.area_cod = usuario.area_id
	INNER JOIN
	empleado
	ON 
		usuario.empleado_id = empleado.empleado_id
WHERE area.area_estado="ACTIVO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SELECT_AREA_SOLO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_AREA_SOLO`()
SELECT
area.area_cod,area.area_nombre
FROM area
WHERE area_estado="ACTIVO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SELECT_DNI` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_DNI`()
SELECT
	empleado.empleado_id, 
	empleado.emple_nrodocumento, 
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat, 
	CONCAT_WS(' ',empleado.emple_nombre,empleado.emple_apepat,empleado.emple_apemat) AS EMPLEADO, 
	empleado.emple_movil, 
	empleado.emple_email, 
	empleado.emple_direccion, 
	area.area_cod, 
	area.area_nombre, 
	usuario.usu_id, 
	usuario.area_id, 
	usuario.usu_rol
FROM
	area
	INNER JOIN
	usuario
	ON 
		area.area_cod = usuario.area_id
	INNER JOIN
	empleado
	ON 
		empleado.empleado_id = usuario.empleado_id
	WHERE empleado.emple_estatus="ACTIVO" and area.area_estado="ACTIVO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SELECT_EMPLEADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_EMPLEADO`()
SELECT
	empleado.empleado_id, 
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat,
	CONCAT_WS(' ',emple_nombre,emple_apepat,emple_apemat)
FROM
	empleado
	WHERE empleado.emple_estatus="ACTIVO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SELECT_TIPO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_TIPO`()
SELECT
	tipo_documento.tipodocumento_id, 
	tipo_documento.tipodo_descripcion
FROM
	tipo_documento
	
WHERE tipo_documento.tipodo_estado='ACTIVO' ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_TICKET` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_TICKET`(IN `NUMERO` VARCHAR(12))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M del %Y - %H:%i:%s %p") as fecha_formateada
FROM
	documento
WHERE documento.documento_id=NUMERO ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_ELIMINAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_ELIMINAR_TRAMITE`(IN `ID` CHAR(12))
BEGIN
DELETE FROM movimiento WHERE documento_id=ID;
DELETE FROM documento WHERE documento_id=ID;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_AREA`()
SELECT
	area.area_cod, 
	area.area_nombre,
	date_format(area_fecha_registro, "%d-%m-%Y - %H:%i:%s") as fecha_formateada,
	area.area_fecha_registro, 
	area.area_estado
FROM
	area
	ORDER BY area_nombre asc ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_COMUNICADOS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_COMUNICADOS`()
SELECT
	comunicados.id_comunicado, 
	comunicados.titulo, 
	comunicados.descripcion, 
	date_format(fecha_registro, "%d-%m-%Y") as fecha_formateada,
	comunicados.fecha_registro, 
	comunicados.id_usuario,
	comunicados.estado,
	comunicados.enlace
FROM
	comunicados
	ORDER BY comunicados.fecha_registro DESC ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_COMUNICADOS2` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_COMUNICADOS2`()
SELECT
	comunicados.id_comunicado, 
	comunicados.titulo, 
	comunicados.descripcion, 
	date_format(fecha_registro, "%d-%m-%Y") as fecha_formateada,
	comunicados.fecha_registro, 
	comunicados.id_usuario,
	comunicados.estado,
	comunicados.enlace
FROM
	comunicados
	ORDER BY comunicados.fecha_registro DESC LIMIT 4 ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_EMPLEADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_EMPLEADO`()
SELECT
	empleado.empleado_id, 
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat, 
	empleado.emple_fechanacimiento, 
	empleado.emple_nrodocumento, 
	empleado.emple_movil,
	empleado.empl_modalidad,
	empleado.emple_email, 
	empleado.emple_estatus, 
	empleado.emple_direccion, 
	empleado.empl_fotoperfil,
	CONCAT_WS(' ',emple_nombre,emple_apepat,emple_apemat)as empleado
FROM
	empleado ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_EMPRESA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_EMPRESA`()
SELECT empresa_id,emp_razon,emp_email,emp_cod,emp_telefono,emp_direccion,emp_logo
FROM empresa ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_HORA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_HORA`()
SELECT
	id_horario,
	hora_inicio, 
	hora_fin, 
	usuario.usu_id, 
	usuario.usu_usuario
FROM
	horario
	INNER JOIN
	usuario
	ON 
		horario.usu_id = usuario.usu_id ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_NOTIFICACION_COMUNICADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_NOTIFICACION_COMUNICADO`()
SELECT
	comunicados.id_comunicado, 
	comunicados.titulo, 
	comunicados.descripcion, 
	comunicados.enlace,
	date_format(fecha_registro, "%d-%m-%Y") as fecha_formateada,
	comunicados.fecha_registro, 
	comunicados.id_usuario, 
	comunicados.estado
FROM
	comunicados
	where estado='NUEVO' and fecha_registro=CURDATE() ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_NOTIFICACION_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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


		WHERE area_destino=IDAREA AND doc_estatus="PENDIENTE" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TIPO_DOCUMENTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TIPO_DOCUMENTO`()
SELECT
	tipo_documento.tipodocumento_id, 
	tipo_documento.tipodo_descripcion, 
	tipo_documento.tipodo_estado,
	tipo_documento.requisitos,
	DATE_FORMAT(tipodo_feregistro, "%d-%m-%Y  %h:%m:%s")as fecha_tipo,
	tipo_documento.tipodo_feregistro
FROM
	tipo_documento ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TOTAL_DOC ACPETADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TOTAL_DOC ACPETADO`(IN `AREA` INT, IN `TIPOUSU` VARCHAR(255))
BEGIN
SET @ROL:=TIPOUSU;
IF @ROL='Secretario (a)' then 
SELECT

count(*)

FROM
	area
	INNER JOIN
	usuario
	ON 
		area.area_cod = usuario.area_id
	INNER JOIN
	documento
	ON 
		area.area_cod = documento.area_destino AND
		area.area_cod = documento.area_id AND
		area.area_cod = documento.area_origen
		where documento.area_origen = AREA and documento.doc_estatus="PENDIENTE";
		
		SELECT

count(*)

FROM
	area
	INNER JOIN
	usuario
	ON 
		area.area_cod = usuario.area_id
	INNER JOIN
	documento
	ON 
		area.area_cod = documento.area_destino AND
		area.area_cod = documento.area_id AND
		area.area_cod = documento.area_origen;
		END IF;
		END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TOTAL_DOC_PENDIENTE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TOTAL_DOC_PENDIENTE`()
SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="PENDIENTE" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TOTAL_EMPLEADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TOTAL_EMPLEADO`()
select count(empleado_id) as total from empleado ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAE_REQUISITO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAE_REQUISITO`(IN `ID` INT)
SELECT 
	tipo_documento.tipodo_descripcion,
	tipo_documento.requisitos
FROM
	tipo_documento
	
	WHERE tipo_documento.tipodocumento_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAE_REQUISITO_DNI` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAE_REQUISITO_DNI`(IN `DNI` CHAR(12))
SELECT
	empleado.empleado_id,
	empleado.emple_nrodocumento, 	
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat,
	CONCAT_WS(' ',empleado.emple_nombre,empleado.emple_apepat,empleado.emple_apemat) AS EMPLEADO, 	
	empleado.emple_movil, 
	empleado.emple_email, 
	empleado.emple_direccion
FROM
	empleado
	WHERE 	empleado.emple_nrodocumento=DNI ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
		ORDER BY doc_fecharegistro desc ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA`(IN `IDUSUARIO` INT)
BEGIN
DECLARE IDAREA INT;
SET @IDAREA :=(SELECT area_id FROM usuario WHERE usu_id=IDUSUARIO);
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
	documento.dias_respuesta, 	
 		acciones,
	doc_observaciones,
	origen.area_nombre as origen, 
	destino.area_nombre as destino
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
		WHERE documento.area_destino=@IDAREA
				ORDER BY doc_fecharegistro desc;
		END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA1` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
        destino.area_nombre AS destino
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_BUSCAR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
				ORDER BY doc_fecharegistro desc ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_ESTADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_ESTADO_TA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_FECHAS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_FECHAS_TA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_TIPO_DOC` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_ESTADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
		ORDER BY doc_fecharegistro DESC ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_SEGUIMIENTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_SEGUIMIENTO`(IN `ID` CHAR(12))
BEGIN
    SELECT
        movimiento.movimiento_id, 
        movimiento.documento_id, 
        movimiento.area_origen_id,
        COALESCE(ao.area_nombre, 'EXTERNO') AS area_origen_nombre,
        ad.area_nombre AS area_destino_nombre,
        DATE_FORMAT(movimiento.mov_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") AS fecha_formateada,
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_USUARIO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_USUARIO`()
SELECT
	usuario.usu_id, 
	usuario.usu_usuario, 
	usuario.empleado_id, 
	usuario.usu_observacion, 
	usuario.usu_estatus, 
	usuario.area_id, 
	usuario.usu_rol, 
	usuario.empresa_id, 
	CONCAT_WS(' ',empleado.emple_nombre,empleado.emple_apepat,empleado.emple_apemat) AS nempleado, 
	area.area_nombre, 
	area.area_cod
FROM
	usuario
	INNER JOIN
	empleado
	ON 
		usuario.empleado_id = empleado.empleado_id
	INNER JOIN
	area
	ON 
		usuario.area_id = area.area_cod ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_AREA`(IN `ID` INT, IN `NAREA` VARCHAR(255), IN `ESTADO` VARCHAR(20))
BEGIN
DECLARE AREAACTUAL VARCHAR(255);
DECLARE CANTIDAD INT;
SET @AREAACTUAL:=(SELECT area_nombre FROM area WHERE area_cod=ID);
IF @AREAACTUAL = NAREA THEN
	UPDATE area SET
	area_estado=ESTADO,
	area_nombre=NAREA
	WHERE area_cod=ID;
	SELECT 1;
ELSE
SET @CANTIDAD:=(SELECT COUNT(*) FROM area WHERE area_nombre=NAREA);
	IF @CANTIDAD=0 THEN
		UPDATE area SET
		area_estado=ESTADO,
		area_nombre=NAREA
		WHERE area_cod=ID;
		SELECT 1;	
	ELSE
		SELECT 2;	
	END IF;
END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_COMUNICADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_COMUNICADO`(IN `ID` INT, IN `TITULO` VARCHAR(255), IN `DESCRIPCION` TEXT, IN `ENLACE` VARCHAR(255))
UPDATE comunicados SET
titulo=TITULO,
descripcion=DESCRIPCION,
enlace=ENLACE
WHERE id_comunicado=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_EMPLEADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPLEADO`(IN `ID` INT, IN `NDOCUMENTO` CHAR(12), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(100), IN `APEMAT` VARCHAR(100), IN `FECHA` DATE, IN `MOVIL` CHAR(9), IN `MODALIDAD` VARCHAR(255), IN `DIRECCION` VARCHAR(255), IN `EMAIL` VARCHAR(255), IN `ESTADO` VARCHAR(20))
BEGIN
DECLARE NDOCUMENTOACTUAL CHAR(12);
DECLARE CANTIDAD INT;
SET @NDOCUMENTOACTUAL:=(SELECT emple_nrodocumento FROM empleado WHERE empleado_id=ID);
IF @NDOCUMENTOACTUAL = NDOCUMENTO THEN
	UPDATE empleado SET
	emple_nrodocumento=NDOCUMENTO,
	emple_nombre=NOMBRE,
	emple_apepat=APEPAT,
	emple_apemat=APEMAT,
	emple_fechanacimiento=FECHA,
	emple_movil=MOVIL,
	empl_modalidad=MODALIDAD,
	emple_direccion=DIRECCION,
	emple_email=EMAIL,
	 emple_estatus=ESTADO
	WHERE empleado_id=ID;
	SELECT 1;
ELSE
SET @CANTIDAD:=(SELECT COUNT(*) FROM empleado WHERE emple_nrodocumento=NDOCUMENTO);
IF @CANTIDAD=0 THEN
UPDATE empleado SET
	emple_nrodocumento=NDOCUMENTO,
	emple_nombre=NOMBRE,
	emple_apepat=APEPAT,
	emple_apemat=APEMAT,
	emple_fechanacimiento=FECHA,
	emple_movil=MOVIL,
	empl_modalidad=MODALIDAD,
	emple_direccion=DIRECCION,
	emple_email=EMAIL,
	 emple_estatus=ESTADO
	WHERE empleado_id=ID;
	SELECT 1;
ELSE
SELECT 2;

END IF;

END IF;



END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_EMPLEADO_FOTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPLEADO_FOTO`(IN `ID` INT, IN `RUTA` VARCHAR(255))
UPDATE empleado SET
empl_fotoperfil=RUTA
WHERE empleado_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_EMPRESA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPRESA`(IN `ID` INT, IN `NOMBRE` VARCHAR(250), IN `EMAIL` VARCHAR(250), IN `COD` VARCHAR(10), IN `TELEFONO` VARCHAR(20), IN `DIRECCION` VARCHAR(250))
UPDATE empresa SET
	emp_razon=NOMBRE,
	emp_email=EMAIL,
	emp_cod=COD,
	emp_telefono=TELEFONO,
	emp_direccion=DIRECCION
	WHERE empresa_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_EMPRESA_FOTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPRESA_FOTO`(IN `ID` INT, IN `RUTA` VARCHAR(255))
UPDATE empresa SET
emp_logo=RUTA
WHERE empresa_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_HORARIO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_HORARIO`(IN `ID` INT, IN `HORAINICIO` VARCHAR(20), IN `HORAFIN` VARCHAR(20))
UPDATE horario SET
hora_inicio=HORAINICIO, hora_fin=HORAFIN
where id_horario=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_TIPO_DOCUMENTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_TIPO_DOCUMENTO`(IN `ID` INT, IN `NTIPO` VARCHAR(255), IN `ESTADO` VARCHAR(20), IN `REQUISITOS` VARCHAR(255))
BEGIN
DECLARE TIPOACTUAL VARCHAR(255);
DECLARE CANTIDAD INT;
SET @TIPOACTUAL:=(SELECT tipodo_descripcion FROM tipo_documento WHERE tipodocumento_id=ID);
IF @TIPOACTUAL = NTIPO THEN
		UPDATE tipo_documento SET
		tipodo_descripcion=NTIPO,
		tipodo_estado=ESTADO,
		requisitos=REQUISITOS
		WHERE tipodocumento_id=ID;
SELECT 1;
ELSE
	SET @CANTIDAD:=(SELECT COUNT(*) FROM tipo_documento WHERE tipodo_descripcion=NTIPO);
	IF @CANTIDAD=0 THEN
		UPDATE tipo_documento SET
		tipodo_descripcion=NTIPO,
		tipodo_estado=ESTADO,
		requisitos=REQUISITOS
		WHERE tipodocumento_id=ID;
		SELECT 1;	
	ELSE
		SELECT 2;	
	END IF;

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_TRAMITE_ESTATUS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_TRAMITE_ESTATUS`(IN `ID` INT, IN `ESTATUS` VARCHAR(50))
UPDATE documento SET
documento.doc_estatus=ESTATUS,
documento.dias_pasados=''
WHERE documento.doc_nrodocumento=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_USUARIO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_USUARIO`(IN `ID` VARCHAR(255), IN `IDEMPLEADO` INT, IN `IDAREA` INT, IN `ROL` VARCHAR(25))
UPDATE usuario SET
usuario.empleado_id = IDEMPLEADO,
usuario.area_id=IDAREA,
usuario.usu_rol=ROL
WHERE usuario.usu_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_USUARIO_CONTRA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_USUARIO_CONTRA`(IN `ID` INT, IN `CONTRA` VARCHAR(250))
UPDATE usuario SET
usuario.usu_contra=CONTRA
WHERE usuario.usu_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_USUARIO_ESTATUS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_USUARIO_ESTATUS`(IN `ID` INT, IN `ESTATUS` VARCHAR(20))
UPDATE usuario SET
usuario.usu_estatus=ESTATUS
WHERE usuario.usu_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_RECHAZAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_RECHAZAR_TRAMITE`(IN `ID` INT, IN `DESCRIPCION` VARCHAR(255), IN `LOCAL1` INT)
BEGIN
UPDATE movimiento SET 
mov_estatus ='RECHAZADO',
mov_descripcion=DESCRIPCION
WHERE documento_id=ID AND mov_estatus='PENDIENTE';
UPDATE documento SET 
doc_estatus='RECHAZADO',
documento.dias_pasados='',
documento.area_destino=LOCAL1
WHERE documento_id=ID AND doc_estatus='PENDIENTE';
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_AREA`(IN `NAREA` VARCHAR(255))
BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM area where area_nombre=NAREA);
IF @CANTIDAD = 0 THEN
INSERT INTO area(area_nombre,area_fecha_registro)VALUE(NAREA,NOW());
SELECT 1;
ELSE
SELECT 2;

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_COMUNICADOS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_COMUNICADOS`(IN `TITULO` VARCHAR(255), IN `DESCRIPCION` TEXT, IN `IDUSU` INT, IN `ENLACE` VARCHAR(255))
BEGIN
DECLARE ID INT;
SET ID:=(SELECT id_comunicado FROM comunicados where fecha_registro<CURDATE() AND estado='NUEVO');

INSERT INTO comunicados(titulo,descripcion,fecha_registro,id_usuario,estado,enlace)VALUES(TITULO,DESCRIPCION,NOW(),IDUSU,'NUEVO',ENLACE);


UPDATE comunicados
set estado='PASADO'
WHERE id_comunicado=ID;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_EMPLEADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_EMPLEADO`(IN `NDOCUMENTO` CHAR(12), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(100), IN `APEMAT` VARCHAR(100), IN `FECHA` DATE, IN `MOVIL` CHAR(9), `MODALIDAD` VARCHAR(255), IN `DIRECCION` VARCHAR(255), IN `EMAIL` VARCHAR(255))
BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM empleado WHERE emple_nrodocumento=NDOCUMENTO);
IF @CANTIDAD=0 THEN
INSERT INTO empleado(emple_nrodocumento,emple_nombre,emple_apepat,emple_apemat,emple_fechanacimiento,emple_movil,empl_modalidad,emple_direccion,emple_email,emple_feccreacion,emple_estatus,empl_fotoperfil)VALUES(NDOCUMENTO,NOMBRE,APEPAT,APEMAT,FECHA,MOVIL,MODALIDAD,DIRECCION,EMAIL,CURDATE(),'ACTIVO','controller/empleado/FOTOS/usuario.png');
SELECT 1;
ELSE
SELECT 2;

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TIPO_DOCUMENTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TIPO_DOCUMENTO`(IN `TIPODOC` VARCHAR(255), IN `REQUISITOS` VARCHAR(255))
BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM tipo_documento where tipodo_descripcion=TIPODOC);
IF @CANTIDAD = 0 THEN
INSERT INTO tipo_documento(tipodo_descripcion,tipodo_estado,requisitos,tipodo_feregistro)VALUE(TIPODOC,'ACTIVO',REQUISITOS,NOW());
SELECT 1;
ELSE
SELECT 2;

END IF;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE`(IN `DNI` CHAR(8), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(50), IN `APEMAT` VARCHAR(50), IN `CEL` CHAR(9), IN `EMAIL` VARCHAR(150), IN `DIRECCION` VARCHAR(255), IN `REPRESENTACION` VARCHAR(50), IN `RUC` CHAR(12), IN `RAZON` VARCHAR(255), IN `AREAPRINCIPAL` INT, IN `AREADESTINO` INT, IN `TIPO` INT, IN `NRODOCUMENTO` VARCHAR(15), IN `ASUNTO` VARCHAR(255), IN `RUTA` VARCHAR(255), IN `FOLIO` INT, IN `IDUSUARIO` INT, IN `ACCION` VARCHAR(255), IN `OBSERVA` VARCHAR(255), IN `RESPU` INT)
BEGIN
DECLARE cantidad INT;
DECLARE cod char(12);
SET @cantidad :=(SELECT IFNULL(MAX(doc_ncorrelativo),0) FROM documento );
IF @cantidad >= 1 AND @cantidad <= 8 THEN
SET @cod :=(SELECT CONCAT('D000000',(@cantidad+1)));
ELSEIF @cantidad >= 9 AND @cantidad <= 98 THEN
SET @cod :=(SELECT CONCAT('D00000',(@cantidad+1)));
ELSEIF @cantidad >= 99 AND @cantidad <= 998 THEN
SET @cod :=(SELECT CONCAT('D0000',(@cantidad+1)));
ELSEIF @cantidad >= 999 AND @cantidad <= 9998 THEN
SET @cod :=(SELECT CONCAT('D000',(@cantidad+1)));
ELSEIF @cantidad >= 9999 AND @cantidad <= 99998 THEN
SET @cod :=(SELECT CONCAT('D00',(@cantidad+1)));
ELSEIF @cantidad >= 99999 AND @cantidad <= 999998 THEN
SET @cod :=(SELECT CONCAT('D0',(@cantidad+1)));
ELSEIF @cantidad >= 999999 THEN
SET @cod :=(SELECT CONCAT('D',(@cantidad+1)));
ELSE
SET @cod :=(SELECT CONCAT('D0000001'));
END IF;
INSERT INTO documento(documento_id,doc_dniremitente,doc_nombreremitente,doc_apepatremitente,doc_apematremitente,doc_celularremitente,doc_emailremitente,doc_direccionremitente,doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,tipodocumento_id,doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,dias_pasados,acciones,doc_observaciones,dias_respuesta) VALUES(@cod,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,RUC,RAZON,AREAPRINCIPAL,AREADESTINO,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,(@cantidad+1),0,ACCION,OBSERVA,RESPU);
SELECT @cod;
INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)VALUES(@cod,AREAPRINCIPAL,AREADESTINO,NOW(),ASUNTO,'PENDIENTE',IDUSUARIO,RUTA,ACCION);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE_DERIVAR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE_DERIVAR`(IN `ID` CHAR(15), IN `ORIGEN` INT, IN `DESTINO` INT, IN `DESCRIPCION` VARCHAR(255), IN `IDUSUARIO` INT, IN `RUTA` VARCHAR(255), IN `TIPO` VARCHAR(255), IN `ACCION` VARCHAR(255))
BEGIN
    DECLARE IDMOVIMENTO INT;
    
    -- CORRECCIÓN: Agregado ORDER BY y LIMIT 1 para obtener solo el último movimiento pendiente
    SET @IDMOVIMENTO:=(
        SELECT movimiento_id 
        FROM movimiento 
        WHERE mov_estatus='PENDIENTE' 
        AND documento_id=ID 
        ORDER BY movimiento_id DESC 
        LIMIT 1
    );
    
    IF TIPO = "FINALIZAR" THEN
        UPDATE movimiento SET
            mov_estatus='DERIVADO'
        WHERE movimiento_id=@IDMOVIMENTO;
        
        UPDATE documento SET
            area_origen=ORIGEN,
            area_destino=ORIGEN,
            doc_estatus='FINALIZADO',
            documento.dias_pasados=''
        WHERE documento_id=ID;
        
        INSERT INTO movimiento(
            documento_id,
            area_origen_id,
            areadestino_id,
            mov_fecharegistro,
            mov_descripcion,
            mov_estatus,
            usuario_id,
            mov_archivo,
            mov_acciones
        ) VALUES(
            ID,
            ORIGEN,
            ORIGEN,
            NOW(),
            DESCRIPCION,
            'FINALIZADO',
            IDUSUARIO,
            RUTA,
            ACCION
        );
    ELSE
        UPDATE movimiento SET
            mov_estatus='DERIVADO'
        WHERE movimiento_id=@IDMOVIMENTO;
        
        UPDATE documento SET
            area_origen=ORIGEN,
            area_destino=DESTINO,
            doc_estatus='PENDIENTE',
            documento.dias_pasados=0
        WHERE documento_id=ID;
        
        INSERT INTO movimiento(
            documento_id,
            area_origen_id,
            areadestino_id,
            mov_fecharegistro,
            mov_descripcion,
            mov_estatus,
            usuario_id,
            mov_archivo,
            mov_acciones
        ) VALUES(
            ID,
            ORIGEN,
            DESTINO,
            NOW(),
            DESCRIPCION,
            'PENDIENTE',
            IDUSUARIO,
            RUTA,
            ACCION
        );
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE_EXTERNO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE_EXTERNO`(IN `DNI` CHAR(8), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(50), IN `APEMAT` VARCHAR(50), IN `CEL` CHAR(9), IN `EMAIL` VARCHAR(150), IN `DIRECCION` VARCHAR(255), IN `REPRESENTACION` VARCHAR(50), IN `RUC` CHAR(12), IN `RAZON` VARCHAR(255), IN `TIPO` INT, IN `NRODOCUMENTO` VARCHAR(15), IN `ASUNTO` VARCHAR(255), IN `RUTA` VARCHAR(255), IN `FOLIO` INT)
BEGIN
DECLARE cantidad INT;
DECLARE cod char(12);
SET @cantidad :=(SELECT IFNULL(MAX(doc_ncorrelativo),0) FROM documento );
IF @cantidad >= 1 AND @cantidad <= 8 THEN
SET @cod :=(SELECT CONCAT('D000000',(@cantidad+1)));
ELSEIF @cantidad >= 9 AND @cantidad <= 98 THEN
SET @cod :=(SELECT CONCAT('D00000',(@cantidad+1)));
ELSEIF @cantidad >= 99 AND @cantidad <= 998 THEN
SET @cod :=(SELECT CONCAT('D0000',(@cantidad+1)));
ELSEIF @cantidad >= 999 AND @cantidad <= 9998 THEN
SET @cod :=(SELECT CONCAT('D000',(@cantidad+1)));
ELSEIF @cantidad >= 9999 AND @cantidad <= 99998 THEN
SET @cod :=(SELECT CONCAT('D00',(@cantidad+1)));
ELSEIF @cantidad >= 99999 AND @cantidad <= 999998 THEN
SET @cod :=(SELECT CONCAT('D0',(@cantidad+1)));
ELSEIF @cantidad >= 999999 THEN
SET @cod :=(SELECT CONCAT('D',(@cantidad+1)));
ELSE
SET @cod :=(SELECT CONCAT('D0000001'));
END IF;
INSERT INTO documento(documento_id,doc_dniremitente,doc_nombreremitente,doc_apepatremitente,doc_apematremitente,doc_celularremitente,doc_emailremitente,doc_direccionremitente,doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,tipodocumento_id,doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,dias_pasados,acciones,doc_observaciones,dias_respuesta) VALUES(@cod,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,RUC,RAZON,1,1,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,(@cantidad+1),0,'--REVISAR--','','');
SELECT @cod;
INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)VALUES(@cod,1,1,NOW(),ASUNTO,'PENDIENTE',3,RUTA,'--REVISAR--');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE_UL` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE_UL`(IN `DNI` CHAR(8), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(50), IN `APEMAT` VARCHAR(50), IN `CEL` CHAR(9), IN `EMAIL` VARCHAR(150), IN `DIRECCION` VARCHAR(255), IN `REPRESENTACION` VARCHAR(50), IN `RUC` CHAR(12), IN `RAZON` VARCHAR(255), IN `AREAPRINCIPAL` INT, IN `AREADESTINO` INT, IN `TIPO` INT, IN `NRODOCUMENTO` VARCHAR(15), IN `ASUNTO` VARCHAR(255), IN `RUTA` VARCHAR(255), IN `FOLIO` INT, IN `IDUSUARIO` INT, IN `ACCION` VARCHAR(255), IN `OBSERVA` VARCHAR(255), IN `RESPU` INT)
BEGIN
DECLARE cantidad INT;
DECLARE cod char(12);
SET @cantidad :=(SELECT IFNULL(MAX(doc_ncorrelativo),0) FROM documento );
IF @cantidad >= 1 AND @cantidad <= 8 THEN
SET @cod :=(SELECT CONCAT('D000000',(@cantidad+1)));
ELSEIF @cantidad >= 9 AND @cantidad <= 98 THEN
SET @cod :=(SELECT CONCAT('D00000',(@cantidad+1)));
ELSEIF @cantidad >= 99 AND @cantidad <= 998 THEN
SET @cod :=(SELECT CONCAT('D0000',(@cantidad+1)));
ELSEIF @cantidad >= 999 AND @cantidad <= 9998 THEN
SET @cod :=(SELECT CONCAT('D000',(@cantidad+1)));
ELSEIF @cantidad >= 9999 AND @cantidad <= 99998 THEN
SET @cod :=(SELECT CONCAT('D00',(@cantidad+1)));
ELSEIF @cantidad >= 99999 AND @cantidad <= 999998 THEN
SET @cod :=(SELECT CONCAT('D0',(@cantidad+1)));
ELSEIF @cantidad >= 999999 THEN
SET @cod :=(SELECT CONCAT('D',(@cantidad+1)));
ELSE
SET @cod :=(SELECT CONCAT('D0000001'));
END IF;
INSERT INTO documento(documento_id,doc_dniremitente,doc_nombreremitente,doc_apepatremitente,doc_apematremitente,doc_celularremitente,doc_emailremitente,doc_direccionremitente,doc_representacion,doc_ruc,doc_empresa,area_origen,area_destino,tipodocumento_id,doc_nrodocumento,doc_asunto,doc_archivo,doc_folio,doc_ncorrelativo,dias_pasados,acciones,doc_observaciones,dias_respuesta) VALUES(@cod,DNI,NOMBRE,APEPAT,APEMAT,CEL,EMAIL,DIRECCION,REPRESENTACION,RUC,RAZON,AREAPRINCIPAL,AREADESTINO,TIPO,NRODOCUMENTO,ASUNTO,RUTA,FOLIO,(@cantidad+1),0,ACCION,OBSERVA,RESPU);
SELECT @cod;
INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones)VALUES(@cod,AREAPRINCIPAL,AREADESTINO,NOW(),ASUNTO,'PENDIENTE',IDUSUARIO,RUTA,ACCION);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_USUARIO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_USUARIO`(IN `USU` VARCHAR(255), IN `CONTRA` VARCHAR(255), IN `IDEMPLEADO` INT, IN `IDAREA` INT, IN `ROL` VARCHAR(25))
BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM usuario WHERE usu_usuario=USU);
IF @CANTIDAD=0 THEN
INSERT INTO usuario(usu_usuario,usu_contra,empleado_id,area_id,usu_rol,usu_feccreacion,usu_estatus,empresa_id)VALUES(USU,CONTRA,IDEMPLEADO,IDAREA,ROL,CURDATE(),'ACTIVO',2);
SELECT 1;
ELSE
SELECT 2;
END IF;


END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TOTAL_DOCUMENTOS_ACEPTADOS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_ACEPTADOS`()
SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="ACEPTADO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TOTAL_DOCUMENTOS_ACEPTADOS_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_ACEPTADOS_AREA`(IN `IDAREA` INT)
SELECT count(*)as totaldocpend FROM documento where doc_estatus="PENDIENTE" and area_origen=IDAREA ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TOTAL_DOCUMENTOS_FINALIZADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_FINALIZADO`()
SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="FINALIZADO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TOTAL_DOCUMENTOS_PENDIENTES2` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
		END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS`()
SELECT 
id_horario,
hora_inicio,
hora_fin

FROM horario ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS2` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS2`(IN `ID` INT)
SELECT id_horario,hora_inicio,hora_fin
FROM horario
WHERE id_horario=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS_EXPEDIENTE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE`(IN `ID` CHAR(12))
SELECT
	documento.documento_id,
	documento.doc_dniremitente,
	documento.doc_nombreremitente,
	documento.doc_apepatremitente,
	documento.doc_apematremitente,
	documento.doc_celularremitente 
FROM
	documento 
WHERE
	documento.documento_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS_EXPEDIENTE_ADMIN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
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
	destino.area_nombre as destino
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
		ORDER BY doc_fecharegistro desc ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_WIDGET` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_WIDGET`(IN `IDAREA` INT)
SELECT
	(select COUNT(*) FROM documento WHERE doc_estatus="PENDIENTE" AND area_origen=IDAREA),
	(select COUNT(*) FROM documento WHERE doc_estatus="ACEPTADO" AND area_origen=IDAREA),
	(select COUNT(*) FROM documento WHERE doc_estatus="FINALIZADO" AND area_origen=IDAREA)
FROM
	documento LIMIT 1 ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_VERIFICAR_USUARIO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_VERIFICAR_USUARIO`(IN `USU` VARCHAR(255))
SELECT
	usuario.usu_id, 
	usuario.usu_usuario, 
	usuario.usu_contra, 
	usuario.usu_feccreacion, 
	usuario.usu_fecupdate, 
	usuario.empleado_id, 
	usuario.usu_observacion, 
	usuario.usu_estatus, 
	usuario.area_id, 
	usuario.usu_rol, 
	usuario.empresa_id, 
	area.area_nombre, 
	area.area_cod, 
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat, 
	CONCAT_WS(' ',empleado.emple_nombre,empleado.emple_apepat,empleado.emple_apemat) AS USUARIO, 
	empleado.empl_fotoperfil, 
	empresa.emp_logo,
	empresa.emp_razon
FROM
	usuario
	INNER JOIN
	area
	ON 
		usuario.area_id = area.area_cod
	INNER JOIN
	empleado
	ON 
		usuario.empleado_id = empleado.empleado_id
	INNER JOIN
	empresa
	ON 
		usuario.empresa_id = empresa.empresa_id
	where usuario.usu_usuario = BINARY USU ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15 17:26:16
