-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 23-08-2025 a las 04:32:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_tramite`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_DNI_UL` (IN `ID` INT)   SELECT
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
	where usuario.usu_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_EXPEDIENTE` (IN `ID` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE` (IN `NUMERO` VARCHAR(12), IN `DNI` VARCHAR(8))   SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M del %Y - %H:%i:%s %p") as fecha_formateada,
        documento.doc_nrodocumento
FROM
	documento
WHERE documento.documento_id=NUMERO AND documento.doc_dniremitente=DNI
ORDER BY doc_fecharegistro ASC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE` (IN `NUMERO` VARCHAR(12))   SELECT DISTINCT
	movimiento.movimiento_id, 
	movimiento.documento_id,
	area.area_cod, 
	area.area_nombre,
	date_format(mov_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
	movimiento.mov_fecharegistro, 
	movimiento.mov_descripcion, 
	movimiento.mov_estatus
FROM
	movimiento
	INNER JOIN
	area
	ON 
		movimiento.areadestino_id = area.area_cod

	WHERE movimiento.documento_id=NUMERO
	ORDER BY mov_fecharegistro ASC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_AREA` ()   SELECT
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
WHERE area.area_estado="ACTIVO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_AREA_SOLO` ()   SELECT
area.area_cod,area.area_nombre
FROM area
WHERE area_estado="ACTIVO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_DNI` ()   SELECT
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
	WHERE empleado.emple_estatus="ACTIVO" and area.area_estado="ACTIVO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_EMPLEADO` ()   SELECT
	empleado.empleado_id, 
	empleado.emple_nombre, 
	empleado.emple_apepat, 
	empleado.emple_apemat,
	CONCAT_WS(' ',emple_nombre,emple_apepat,emple_apemat)
FROM
	empleado
	WHERE empleado.emple_estatus="ACTIVO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_SELECT_TIPO` ()   SELECT
	tipo_documento.tipodocumento_id, 
	tipo_documento.tipodo_descripcion
FROM
	tipo_documento
	
WHERE tipo_documento.tipodo_estado='ACTIVO'$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_CARGAR_TICKET` (IN `NUMERO` VARCHAR(12))   SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M del %Y - %H:%i:%s %p") as fecha_formateada
FROM
	documento
WHERE documento.documento_id=NUMERO$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_ELIMINAR_TRAMITE` (IN `ID` CHAR(12))   BEGIN
DELETE FROM movimiento WHERE documento_id=ID;
DELETE FROM documento WHERE documento_id=ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_AREA` ()   SELECT
	area.area_cod, 
	area.area_nombre,
	date_format(area_fecha_registro, "%d-%m-%Y - %H:%i:%s") as fecha_formateada,
	area.area_fecha_registro, 
	area.area_estado
FROM
	area
	ORDER BY area_nombre asc$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_COMUNICADOS` ()   SELECT
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
	ORDER BY comunicados.fecha_registro DESC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_COMUNICADOS2` ()   SELECT
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
	ORDER BY comunicados.fecha_registro DESC LIMIT 4$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_EMPLEADO` ()   SELECT
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
	empleado$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_EMPRESA` ()   SELECT empresa_id,emp_razon,emp_email,emp_cod,emp_telefono,emp_direccion,emp_logo
FROM empresa$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_HORA` ()   SELECT
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
		horario.usu_id = usuario.usu_id$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_NOTIFICACION_COMUNICADO` ()   SELECT
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
	where estado='NUEVO' and fecha_registro=CURDATE()$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_NOTIFICACION_TRAMITE` (IN `IDAREA` INT)   SELECT
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


		WHERE area_destino=IDAREA AND doc_estatus="PENDIENTE"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TIPO_DOCUMENTO` ()   SELECT
	tipo_documento.tipodocumento_id, 
	tipo_documento.tipodo_descripcion, 
	tipo_documento.tipodo_estado,
	tipo_documento.requisitos,
	DATE_FORMAT(tipodo_feregistro, "%d-%m-%Y  %h:%m:%s")as fecha_tipo,
	tipo_documento.tipodo_feregistro
FROM
	tipo_documento$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TOTAL_DOC ACPETADO` (IN `AREA` INT, IN `TIPOUSU` VARCHAR(255))   BEGIN
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
		END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TOTAL_DOC_PENDIENTE` ()   SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="PENDIENTE"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TOTAL_EMPLEADO` ()   select count(empleado_id) as total from empleado$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAE_REQUISITO` (IN `ID` INT)   SELECT 
	tipo_documento.tipodo_descripcion,
	tipo_documento.requisitos
FROM
	tipo_documento
	
	WHERE tipo_documento.tipodocumento_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAE_REQUISITO_DNI` (IN `DNI` CHAR(12))   SELECT
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
	WHERE 	empleado.emple_nrodocumento=DNI$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE` ()   SELECT
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
		ORDER BY doc_fecharegistro desc$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA` (IN `IDUSUARIO` INT)   BEGIN
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
		END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA1` (IN `IDUSUARIO` INT)   BEGIN
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
		WHERE documento.area_origen=@IDAREA OR documento.area_destino=@IDAREA
		ORDER BY doc_fecharegistro desc;
		END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_BUSCAR` (IN `IDAREA` INT, IN `ESTADO` VARCHAR(20))   SELECT
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
				ORDER BY doc_fecharegistro desc$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_ESTADO` (IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `ESTADO` VARCHAR(20))   BEGIN
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
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_ESTADO_TA` (IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `ESTADO` VARCHAR(20), IN `IDAREA` INT)   BEGIN
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
	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_FECHAS` (IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `IDAREA` INT)   BEGIN
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

	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_FECHAS_TA` (IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `IDAREA` INT)   BEGIN
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

	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_TIPO_DOC` (IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `TIPO` INT)   BEGIN
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

	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA` (IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `TIPO_DOC` INT, IN `IDAREA` INT)   BEGIN
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

	END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_ESTADO` (IN `ESTADO` VARCHAR(20))   SELECT
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
		ORDER BY doc_fecharegistro DESC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_TRAMITE_SEGUIMIENTO` (IN `ID` CHAR(12))   SELECT
	movimiento.movimiento_id, 
	movimiento.documento_id, 
	movimiento.area_origen_id, 
	area.area_nombre,
	date_format(mov_fecharegistro, "%d-%m-%Y - %H:%i:%s %p") as fecha_formateada,
 
	movimiento.mov_fecharegistro, 
	movimiento.mov_descripcion,
	movimiento.mov_estatus,
	movimiento.mov_archivo,
	movimiento.mov_acciones
FROM
	movimiento
	INNER JOIN
	area
	ON 
		movimiento.area_origen_id = area.area_cod
	WHERE movimiento.documento_id = ID
		ORDER BY mov_fecharegistro ASC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_LISTAR_USUARIO` ()   SELECT
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
		usuario.area_id = area.area_cod$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_AREA` (IN `ID` INT, IN `NAREA` VARCHAR(255), IN `ESTADO` VARCHAR(20))   BEGIN
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

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_COMUNICADO` (IN `ID` INT, IN `TITULO` VARCHAR(255), IN `DESCRIPCION` TEXT, IN `ENLACE` VARCHAR(255))   UPDATE comunicados SET
titulo=TITULO,
descripcion=DESCRIPCION,
enlace=ENLACE
WHERE id_comunicado=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPLEADO` (IN `ID` INT, IN `NDOCUMENTO` CHAR(12), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(100), IN `APEMAT` VARCHAR(100), IN `FECHA` DATE, IN `MOVIL` CHAR(9), IN `MODALIDAD` VARCHAR(255), IN `DIRECCION` VARCHAR(255), IN `EMAIL` VARCHAR(255), IN `ESTADO` VARCHAR(20))   BEGIN
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



END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPLEADO_FOTO` (IN `ID` INT, IN `RUTA` VARCHAR(255))   UPDATE empleado SET
empl_fotoperfil=RUTA
WHERE empleado_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPRESA` (IN `ID` INT, IN `NOMBRE` VARCHAR(250), IN `EMAIL` VARCHAR(250), IN `COD` VARCHAR(10), IN `TELEFONO` VARCHAR(20), IN `DIRECCION` VARCHAR(250))   UPDATE empresa SET
	emp_razon=NOMBRE,
	emp_email=EMAIL,
	emp_cod=COD,
	emp_telefono=TELEFONO,
	emp_direccion=DIRECCION
	WHERE empresa_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_EMPRESA_FOTO` (IN `ID` INT, IN `RUTA` VARCHAR(255))   UPDATE empresa SET
emp_logo=RUTA
WHERE empresa_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_HORARIO` (IN `ID` INT, IN `HORAINICIO` VARCHAR(20), IN `HORAFIN` VARCHAR(20))   UPDATE horario SET
hora_inicio=HORAINICIO, hora_fin=HORAFIN
where id_horario=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_TIPO_DOCUMENTO` (IN `ID` INT, IN `NTIPO` VARCHAR(255), IN `ESTADO` VARCHAR(20), IN `REQUISITOS` VARCHAR(255))   BEGIN
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

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_TRAMITE_ESTATUS` (IN `ID` INT, IN `ESTATUS` VARCHAR(50))   UPDATE documento SET
documento.doc_estatus=ESTATUS,
documento.dias_pasados=''
WHERE documento.doc_nrodocumento=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_USUARIO` (IN `ID` VARCHAR(255), IN `IDEMPLEADO` INT, IN `IDAREA` INT, IN `ROL` VARCHAR(25))   UPDATE usuario SET
usuario.empleado_id = IDEMPLEADO,
usuario.area_id=IDAREA,
usuario.usu_rol=ROL
WHERE usuario.usu_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_USUARIO_CONTRA` (IN `ID` INT, IN `CONTRA` VARCHAR(250))   UPDATE usuario SET
usuario.usu_contra=CONTRA
WHERE usuario.usu_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_MODIFICAR_USUARIO_ESTATUS` (IN `ID` INT, IN `ESTATUS` VARCHAR(20))   UPDATE usuario SET
usuario.usu_estatus=ESTATUS
WHERE usuario.usu_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_RECHAZAR_TRAMITE` (IN `ID` INT, IN `DESCRIPCION` VARCHAR(255), IN `LOCAL1` INT)   BEGIN
UPDATE movimiento SET 
mov_estatus ='RECHAZADO',
mov_descripcion=DESCRIPCION
WHERE documento_id=ID AND mov_estatus='PENDIENTE';
UPDATE documento SET 
doc_estatus='RECHAZADO',
documento.dias_pasados='',
documento.area_destino=LOCAL1
WHERE documento_id=ID AND doc_estatus='PENDIENTE';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_AREA` (IN `NAREA` VARCHAR(255))   BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM area where area_nombre=NAREA);
IF @CANTIDAD = 0 THEN
INSERT INTO area(area_nombre,area_fecha_registro)VALUE(NAREA,NOW());
SELECT 1;
ELSE
SELECT 2;

END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_COMUNICADOS` (IN `TITULO` VARCHAR(255), IN `DESCRIPCION` TEXT, IN `IDUSU` INT, IN `ENLACE` VARCHAR(255))   BEGIN
DECLARE ID INT;
SET ID:=(SELECT id_comunicado FROM comunicados where fecha_registro<CURDATE() AND estado='NUEVO');

INSERT INTO comunicados(titulo,descripcion,fecha_registro,id_usuario,estado,enlace)VALUES(TITULO,DESCRIPCION,NOW(),IDUSU,'NUEVO',ENLACE);


UPDATE comunicados
set estado='PASADO'
WHERE id_comunicado=ID;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_EMPLEADO` (IN `NDOCUMENTO` CHAR(12), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(100), IN `APEMAT` VARCHAR(100), IN `FECHA` DATE, IN `MOVIL` CHAR(9), `MODALIDAD` VARCHAR(255), IN `DIRECCION` VARCHAR(255), IN `EMAIL` VARCHAR(255))   BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM empleado WHERE emple_nrodocumento=NDOCUMENTO);
IF @CANTIDAD=0 THEN
INSERT INTO empleado(emple_nrodocumento,emple_nombre,emple_apepat,emple_apemat,emple_fechanacimiento,emple_movil,empl_modalidad,emple_direccion,emple_email,emple_feccreacion,emple_estatus,empl_fotoperfil)VALUES(NDOCUMENTO,NOMBRE,APEPAT,APEMAT,FECHA,MOVIL,MODALIDAD,DIRECCION,EMAIL,CURDATE(),'ACTIVO','controller/empleado/FOTOS/usuario.png');
SELECT 1;
ELSE
SELECT 2;

END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TIPO_DOCUMENTO` (IN `TIPODOC` VARCHAR(255), IN `REQUISITOS` VARCHAR(255))   BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM tipo_documento where tipodo_descripcion=TIPODOC);
IF @CANTIDAD = 0 THEN
INSERT INTO tipo_documento(tipodo_descripcion,tipodo_estado,requisitos,tipodo_feregistro)VALUE(TIPODOC,'ACTIVO',REQUISITOS,NOW());
SELECT 1;
ELSE
SELECT 2;

END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE` (IN `DNI` CHAR(8), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(50), IN `APEMAT` VARCHAR(50), IN `CEL` CHAR(9), IN `EMAIL` VARCHAR(150), IN `DIRECCION` VARCHAR(255), IN `REPRESENTACION` VARCHAR(50), IN `RUC` CHAR(12), IN `RAZON` VARCHAR(255), IN `AREAPRINCIPAL` INT, IN `AREADESTINO` INT, IN `TIPO` INT, IN `NRODOCUMENTO` VARCHAR(15), IN `ASUNTO` VARCHAR(255), IN `RUTA` VARCHAR(255), IN `FOLIO` INT, IN `IDUSUARIO` INT, IN `ACCION` VARCHAR(255), IN `OBSERVA` VARCHAR(255), IN `RESPU` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE_DERIVAR` (IN `ID` CHAR(15), IN `ORIGEN` INT, IN `DESTINO` INT, IN `DESCRIPCION` VARCHAR(255), IN `IDUSUARIO` INT, IN `RUTA` VARCHAR(255), IN `TIPO` VARCHAR(255), IN `ACCION` VARCHAR(255))   BEGIN
DECLARE IDMOVIMENTO INT;
SET @IDMOVIMENTO:=(select movimiento_id from movimiento where mov_estatus='PENDIENTE' AND documento_id=ID);
IF TIPO = "FINALIZAR" THEN

UPDATE movimiento SET
mov_estatus='DERIVADO'
where movimiento_id=@IDMOVIMENTO;
UPDATE documento SET
area_origen=ORIGEN,
area_destino=ORIGEN,
doc_estatus='FINALIZADO',
documento.dias_pasados=''
WHERE documento_id=ID;
INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones) VALUES(ID,ORIGEN,ORIGEN,NOW(),DESCRIPCION,'FINALIZADO',IDUSUARIO,RUTA,ACCION);

ELSE

UPDATE movimiento SET
mov_estatus='DERIVADO'
where movimiento_id=@IDMOVIMENTO;
UPDATE documento SET
area_origen=ORIGEN,
area_destino=DESTINO,
doc_estatus='PENDIENTE',
documento.dias_pasados=0
WHERE documento_id=ID;
INSERT INTO movimiento(documento_id,area_origen_id,areadestino_id,mov_fecharegistro,mov_descripcion,mov_estatus,usuario_id,mov_archivo,mov_acciones) VALUES(ID,ORIGEN,DESTINO,NOW(),DESCRIPCION,'PENDIENTE',IDUSUARIO,RUTA,ACCION);

END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE_EXTERNO` (IN `DNI` CHAR(8), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(50), IN `APEMAT` VARCHAR(50), IN `CEL` CHAR(9), IN `EMAIL` VARCHAR(150), IN `DIRECCION` VARCHAR(255), IN `REPRESENTACION` VARCHAR(50), IN `RUC` CHAR(12), IN `RAZON` VARCHAR(255), IN `TIPO` INT, IN `NRODOCUMENTO` VARCHAR(15), IN `ASUNTO` VARCHAR(255), IN `RUTA` VARCHAR(255), IN `FOLIO` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_TRAMITE_UL` (IN `DNI` CHAR(8), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(50), IN `APEMAT` VARCHAR(50), IN `CEL` CHAR(9), IN `EMAIL` VARCHAR(150), IN `DIRECCION` VARCHAR(255), IN `REPRESENTACION` VARCHAR(50), IN `RUC` CHAR(12), IN `RAZON` VARCHAR(255), IN `AREAPRINCIPAL` INT, IN `AREADESTINO` INT, IN `TIPO` INT, IN `NRODOCUMENTO` VARCHAR(15), IN `ASUNTO` VARCHAR(255), IN `RUTA` VARCHAR(255), IN `FOLIO` INT, IN `IDUSUARIO` INT, IN `ACCION` VARCHAR(255), IN `OBSERVA` VARCHAR(255), IN `RESPU` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_REGISTRAR_USUARIO` (IN `USU` VARCHAR(255), IN `CONTRA` VARCHAR(255), IN `IDEMPLEADO` INT, IN `IDAREA` INT, IN `ROL` VARCHAR(25))   BEGIN
DECLARE CANTIDAD INT;
SET @CANTIDAD:=(SELECT COUNT(*) FROM usuario WHERE usu_usuario=USU);
IF @CANTIDAD=0 THEN
INSERT INTO usuario(usu_usuario,usu_contra,empleado_id,area_id,usu_rol,usu_feccreacion,usu_estatus,empresa_id)VALUES(USU,CONTRA,IDEMPLEADO,IDAREA,ROL,CURDATE(),'ACTIVO',2);
SELECT 1;
ELSE
SELECT 2;
END IF;


END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_ACEPTADOS` ()   SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="ACEPTADO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_ACEPTADOS_AREA` (IN `IDAREA` INT)   SELECT count(*)as totaldocpend FROM documento where doc_estatus="PENDIENTE" and area_origen=IDAREA$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_FINALIZADO` ()   SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="FINALIZADO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TOTAL_DOCUMENTOS_PENDIENTES2` (IN `IDUSUARIO` INT)   BEGIN
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
		END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS` ()   SELECT 
id_horario,
hora_inicio,
hora_fin

FROM horario$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS2` (IN `ID` INT)   SELECT id_horario,hora_inicio,hora_fin
FROM horario
WHERE id_horario=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE` (IN `ID` CHAR(12))   SELECT
	documento.documento_id,
	documento.doc_dniremitente,
	documento.doc_nombreremitente,
	documento.doc_apepatremitente,
	documento.doc_apematremitente,
	documento.doc_celularremitente 
FROM
	documento 
WHERE
	documento.documento_id=ID$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE_ADMIN` ()   SELECT
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
		ORDER BY doc_fecharegistro desc$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_TRAER_WIDGET` (IN `IDAREA` INT)   SELECT
	(select COUNT(*) FROM documento WHERE doc_estatus="PENDIENTE" AND area_origen=IDAREA),
	(select COUNT(*) FROM documento WHERE doc_estatus="ACEPTADO" AND area_origen=IDAREA),
	(select COUNT(*) FROM documento WHERE doc_estatus="FINALIZADO" AND area_origen=IDAREA)
FROM
	documento LIMIT 1$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SP_VERIFICAR_USUARIO` (IN `USU` VARCHAR(255))   SELECT
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
	where usuario.usu_usuario = BINARY USU$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `area_cod` int(11) NOT NULL COMMENT 'Codigo auto-incrementado del movimiento del area',
  `area_nombre` varchar(50) NOT NULL COMMENT 'nombre del area',
  `area_fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'fecha del registro del movimiento',
  `area_estado` enum('ACTIVO','INACTIVO') NOT NULL COMMENT 'estado del area'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Entidad Area' ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`area_cod`, `area_nombre`, `area_fecha_registro`, `area_estado`) VALUES
(1, 'MESA DE PARTES', '2023-08-25 14:08:00', 'ACTIVO'),
(2, 'SISTEMA WEB', '2023-08-25 14:08:07', 'INACTIVO'),
(3, 'CONTABILIDAD', '2023-08-25 18:30:22', 'ACTIVO'),
(4, 'PATRIMONIO', '2023-08-25 18:30:29', 'ACTIVO'),
(5, 'ESTADISTICA', '2023-08-25 18:30:37', 'ACTIVO'),
(6, 'EPIDEMIOLOGIA', '2023-08-25 18:30:57', 'ACTIVO'),
(7, 'DIRECCION GENERAL', '2023-08-25 18:31:09', 'ACTIVO'),
(8, 'RECURSOS HUMANOS', '2023-08-25 18:32:09', 'ACTIVO'),
(9, 'PLANIFICACION', '2023-09-18 20:43:13', 'ACTIVO'),
(10, 'PANIFICACIÓN', '2024-03-19 17:22:10', 'ACTIVO'),
(11, 'PLANEAMIENTO', '2024-03-19 21:34:33', 'ACTIVO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id_comunicado` int(11) NOT NULL,
  `titulo` varchar(1000) NOT NULL,
  `descripcion` text NOT NULL,
  `enlace` varchar(10000) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `estado` enum('NUEVO','PASADO') NOT NULL,
  `com_correlativo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id_comunicado`, `titulo`, `descripcion`, `enlace`, `fecha_registro`, `id_usuario`, `estado`, `com_correlativo`) VALUES
(1, 'AUTORIDADES REFUERZAN ATENCIÓN DE SALUD MENTAL EN APURÍMAC', 'Como parte del plan de fortalecimiento de los servicios de salud mental, el Ministerio de Salud (Minsa) y el Gobierno Regional de Apurímac a través de la Dirección Regional de Salud, inauguró en la Provincia de Aymaraes-Chalhuanca el Centro de Salud Mental Comunitaria “Señor de Animas” siendo uno de los nueve servicios especializado en Salud mental y adicciones en la región Apurímac.', 'https://www.diresaapurimac.gob.pe/web/noticias/autoridades-refuerzan-atencion-de-salud-mental-en-apurimac/', '2023-08-26', 2, 'PASADO', NULL),
(2, '¡HISTÓRICO! SE INICIA CONSTRUCCIÓN DE MODERNO HOSPITAL DE TAMBOBAMBA CON MÁS DE S/ 113 MILLONES', 'Entregan terreno a empresa constructora para dar inicio del mega proyecto.\nGracias a la gestión del Gobernador Percy Godoy Medina y la Dirección Regional de Salud Apurímac, el Programa Nacional de Inversiones en Salud (Pronis) del MINSA entregó el terreno a la empresa contratista china Gezhoura Group Company Limited Sucursal Perú para dar inicio a la construcción del anhelado proyecto del Hospital Tambobamba II-1 en la provincia de Cotabambas.', 'https://www.diresaapurimac.gob.pe/web/noticias/historico-se-inicia-construccion-de-moderno-hospital-de-tambobamba-con-mas-de-s-113-millones/', '2023-08-28', 2, 'PASADO', NULL),
(3, 'DIRESA APURÍMAC Y DEFENSORÍA DE PUEBLO SUMAN ESFUERZOS PARA MEJORAR LA CALIDAD DE PRESTACIÓN DE SERVICIOS EN SALUD A NIVEL REGIONAL', 'Con el propósito de sumar esfuerzos conjuntos en beneficio de la población, la DIRESA Apurímac y la Defensoría del Pueblo iniciaron reuniones de trabajo con miras a mejorar la calidad de la prestación de servicios en salud a nivel regional.\n\nCon la participación del gerente de Desarrollo Social del GORE Apurímac, Elio Vidal Robles, y del director regional de Salud, Roger Peralta Montes, el encuentro de coordinación contribuyó a unificar criterios y compartir responsabilidades institucionales para fortalecer acciones conjuntas a fin de avanzar de manera óptima en la práctica sanitaria en el primer nivel de atención.', 'https://www.diresaapurimac.gob.pe/web/noticias/diresa-apurimac-y-defensoria-de-pueblo-suman-esfuerzos-para-mejorar-la-calidad-de-prestacion-de-servicios-en-salud-a-nivel-regional/', '2023-11-10', 2, 'NUEVO', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

CREATE TABLE `documento` (
  `documento_id` char(12) NOT NULL,
  `doc_dniremitente` char(8) NOT NULL,
  `doc_nombreremitente` varchar(150) NOT NULL,
  `doc_apepatremitente` varchar(50) NOT NULL,
  `doc_apematremitente` varchar(50) NOT NULL,
  `doc_celularremitente` char(9) NOT NULL,
  `doc_emailremitente` varchar(150) NOT NULL,
  `doc_direccionremitente` varchar(255) NOT NULL,
  `doc_representacion` varchar(50) NOT NULL,
  `doc_ruc` char(12) NOT NULL,
  `doc_empresa` varchar(255) NOT NULL,
  `tipodocumento_id` int(11) NOT NULL,
  `doc_nrodocumento` varchar(15) NOT NULL DEFAULT '',
  `doc_folio` int(11) NOT NULL,
  `doc_asunto` varchar(255) NOT NULL,
  `doc_archivo` varchar(255) NOT NULL,
  `doc_fecharegistro` datetime DEFAULT current_timestamp(),
  `area_id` int(11) DEFAULT 1,
  `doc_estatus` enum('PENDIENTE','RECHAZADO','ACEPTADO','FINALIZADO','SIN RESPUESTA') NOT NULL,
  `area_origen` int(11) NOT NULL DEFAULT 0,
  `area_destino` int(11) DEFAULT NULL,
  `doc_ncorrelativo` int(11) DEFAULT NULL,
  `dias_pasados` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `acciones` varchar(255) DEFAULT NULL,
  `doc_observaciones` varchar(255) DEFAULT NULL,
  `dias_respuesta` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `documento`
--

INSERT INTO `documento` (`documento_id`, `doc_dniremitente`, `doc_nombreremitente`, `doc_apepatremitente`, `doc_apematremitente`, `doc_celularremitente`, `doc_emailremitente`, `doc_direccionremitente`, `doc_representacion`, `doc_ruc`, `doc_empresa`, `tipodocumento_id`, `doc_nrodocumento`, `doc_folio`, `doc_asunto`, `doc_archivo`, `doc_fecharegistro`, `area_id`, `doc_estatus`, `area_origen`, `area_destino`, `doc_ncorrelativo`, `dias_pasados`, `id_empleado`, `acciones`, `doc_observaciones`, `dias_respuesta`) VALUES
('D0000001', '26626266', 'ANDREA', 'SANCHEZ', 'JIMENEZ', '966226262', 'andrea12@gmail.com', 'AV. DIAZ BARCENAS N° 323', 'A NOMBRE PROPIO', '', '', 3, '0012', 32, 'COMUNICAR A LOS JEFES DE AREA', 'controller/tramite/documentos/ARCH11-2-2024-10-633.PDF', '2024-02-11 10:10:54', 1, 'ACEPTADO', 7, 7, 1, 0, NULL, '-3. REVISAR-ONON', '', 0),
('D0000002', '15511515', 'LUIS', 'CAMACHO', 'VELARDE', '926622656', 'luis2112@gmail.com', 'JR. HUANCAVELICA N° 323', 'A NOMBRE PROPIO', '', '', 10, '123', 21, 'SE ENVIA CARTA NOTARIAL', 'controller/tramite/documentos/ARCH11-2-2024-10-221.PDF', '2024-02-11 10:13:40', 1, 'FINALIZADO', 6, 6, 2, 0, NULL, '4. -V° B°-ONON', '', 1),
('D0000005', '26626266', 'ANDREA', 'SANCHEZ', 'JIMENEZ', '966226262', 'andrea12@gmail.com', 'AV. DIAZ BARCENAS N° 323', 'A NOMBRE PROPIO', '', '', 1, '1222', 2, 'REVISAR FROMATO DE POI', 'controller/tramite/documentos/ARCH11-2-2024-12-978.PDF', '2024-02-11 12:12:02', 1, 'FINALIZADO', 7, 7, 5, 0, NULL, '-3. REVISAR-4. -V° B°--5. COORDINAR--9. SEGUIMIENTO--10. DAR RESPUESTA-ONON', '', 0),
('D0000006', '41239943', 'ELIAS', 'CARRIÓN', 'UMERES', '935951872', 'eliascar888@hotmail.com', 'AV 28 DE ABRIL N° 234', 'A NOMBRE PROPIO', '', '', 3, '0052', 2, 'SE HACE ENTREGA DEL REPORTE DE EPIDEMIOLOGIA', 'controller/tramite/documentos/ARCH19-3-2024-10-778.PDF', '2024-03-19 10:55:35', 1, 'FINALIZADO', 1, 1, 6, 0, NULL, '-1. ACCIÓN--2. TRAMITAR-ONON', '', 1),
('D0000008', '56526262', 'JOSE LUIS', 'CAMACHO', 'VALENCIA', '926226262', 'jose122@gmail.com', 'JR. CANADA N° 552', 'A NOMBRE PROPIO', '', '', 3, '0004', 1, 'SOLICITO COPIA DE HISTORIA CLÍNICA', 'controller/tramite/documentos/ARCH19-3-2024-11-957.PDF', '2024-03-19 11:30:25', 1, 'PENDIENTE', 4, 7, 8, 521, NULL, '--REVISAR--', '', 0),
('D0000010', '12121212', 'JIMENES', 'PERALTA', 'DAVALOS', '926622662', 'jimena2112@gmail.com', 'AV. CANADA N° 233', 'A NOMBRE PROPIO', '', '', 2, '2522', 3, 'OFICIO PARA REUNIÓN URGENTE EN LA REGIÓN', 'controller/tramite/documentos/ARCH19-3-2024-17-62.PDF', '2024-03-19 17:09:52', 1, 'PENDIENTE', 5, 7, 10, 521, NULL, '--REVISAR--', '', 0),
('D0000011', '15511515', 'LUIS', 'CAMACHO', 'VELARDE', '926622656', 'luis2112@gmail.com', 'JR. HUANCAVELICA N° 323', 'A NOMBRE PROPIO', '', '', 3, '0112', 5, 'REVISAR EXPEDIENTE', 'controller/tramite/documentos/ARCH19-3-2024-17-410.PDF', '2024-03-19 17:14:13', 1, 'FINALIZADO', 1, 1, 11, 0, NULL, '-1. ACCIÓN--2. TRAMITAR-ONON', '', 0),
('D0000015', '72646121', 'JERSSON JORGE', 'CORILLA', 'MIRANDA', '975455454', '', 'JR. CANADA N° 23', 'A NOMBRE PROPIO', '', '', 1, '12', 2, 'SDFDS', 'controller/tramite/documentos/ARCH22-8-2025-19-528.PDF', '2025-08-22 19:33:33', 1, 'ACEPTADO', 1, 5, 15, 0, NULL, '-1. ACCIÓN--2. TRAMITAR-ONONON', '', 1),
('D0000017', '26626266', 'ANDREA', 'SANCHEZ', 'JIMENEZ', '966226262', 'andrea12@gmail.com', 'AV. DIAZ BARCENAS N° 323', 'A NOMBRE PROPIO', '', '', 4, '12', 2, 'SADSD', 'controller/tramite/documentos/ARCH22-8-2025-20-203.PDF', '2025-08-22 20:01:57', 1, 'PENDIENTE', 3, 5, 17, 0, NULL, '-2. TRAMITAR--3. REVISAR-ONON', '', 2),
('D0000018', '26626266', 'ANDREA', 'SANCHEZ', 'JIMENEZ', '966226262', 'andrea12@gmail.com', 'AV. DIAZ BARCENAS N° 323', 'PERSONA JURíDICA', '10726461215', 'SAC SAFDS', 2, '123', 1, 'DSF', 'controller/tramite/documentos/ARCH22-8-2025-20-732.PDF', '2025-08-22 20:06:28', 1, 'PENDIENTE', 3, 6, 18, 0, NULL, 'ONON', '', 2),
('D0000019', '26626266', 'ANDREA', 'SANCHEZ', 'JIMENEZ', '966226262', 'andrea12@gmail.com', 'AV. DIAZ BARCENAS N° 323', 'PERSONA JURIDICA', '10726461215', 'SDFSDF', 3, '21', 2, 'SADF', 'controller/tramite/documentos/ARCH22-8-2025-20-315.PDF', '2025-08-22 20:13:15', 1, 'PENDIENTE', 3, 5, 19, 0, NULL, '-3. REVISAR-4. -V° B°-ONON', '', 2),
('D0000020', '26626266', 'ANDREA', 'SANCHEZ', 'JIMENEZ', '966226262', 'andrea12@gmail.com', 'AV. DIAZ BARCENAS N° 323', 'A NOMBRE PROPIO', '', '', 17, '122', 33, 'SADFSD', 'controller/tramite/documentos/ARCH22-8-2025-20-475.PDF', '2025-08-22 20:17:07', 1, 'ACEPTADO', 3, 7, 20, 0, NULL, 'ONON', '', 2),
('D0000021', '55151151', 'JOSE', 'SANCHEZ', 'MEDINA', '926262625', 'jose21@gmail.com', 'JR. CHALHUANCA N° 222', 'A NOMBRE PROPIO', '', '', 2, '21', 2, 'SDFDS', 'controller/tramite/documentos/ARCH22-8-2025-20-389.PDF', '2025-08-22 20:36:31', 1, 'PENDIENTE', 1, 3, 21, 0, NULL, '-3. REVISAR-4. -V° B°-ONON', '', 3),
('D0000022', '55151151', 'JOSE', 'SANCHEZ', 'MEDINA', '926262625', 'jose21@gmail.com', 'JR. CHALHUANCA N° 222', 'A NOMBRE PROPIO', '', '', 20, '23', 444, 'SDFDS', 'controller/tramite/documentos/ARCH22-8-2025-20-606.PDF', '2025-08-22 20:37:19', 1, 'PENDIENTE', 1, 7, 22, 0, NULL, '4. -V° B°-ONON', '', 23),
('D0000023', '41239943', 'ELIAS', 'CARRIÓN', 'UMERES', '935951872', 'eliascar888@hotmail.com', 'AV 28 DE ABRIL N° 234', 'PERSONA JURíDICA', '322323232', 'HOLAAA', 34, '23', 2, 'SDF', 'controller/tramite/documentos/ARCH22-8-2025-21-575.PDF', '2025-08-22 21:14:57', 1, 'PENDIENTE', 6, 7, 23, 0, NULL, '4. -V° B°--5. COORDINAR-ONON', '', 2),
('D0000024', '55151151', 'JOSE', 'SANCHEZ', 'MEDINA', '926262625', 'jose21@gmail.com', 'JR. CHALHUANCA N° 222', 'PERSONA JURíDICA', '23323', 'SDFDDSF', 3, '12', 12, 'SADSDF', 'controller/tramite/documentos/ARCH22-8-2025-21-935.PDF', '2025-08-22 21:30:20', 1, 'PENDIENTE', 1, 1, 24, 0, NULL, '-1. ACCIÓN--2. TRAMITAR-ONON', '', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `empleado_id` int(11) NOT NULL,
  `emple_nombre` varchar(150) DEFAULT NULL,
  `emple_apepat` varchar(100) DEFAULT NULL,
  `emple_apemat` varchar(100) DEFAULT NULL,
  `emple_feccreacion` date DEFAULT NULL,
  `emple_fechanacimiento` date DEFAULT NULL,
  `emple_nrodocumento` char(12) DEFAULT NULL,
  `emple_movil` char(9) DEFAULT NULL,
  `empl_modalidad` varchar(255) DEFAULT NULL,
  `emple_email` varchar(250) DEFAULT NULL,
  `emple_estatus` enum('ACTIVO','INACTIVO') NOT NULL,
  `emple_direccion` varchar(255) DEFAULT NULL,
  `empl_fotoperfil` varchar(255) NOT NULL DEFAULT 'Fotos/admin.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`empleado_id`, `emple_nombre`, `emple_apepat`, `emple_apemat`, `emple_feccreacion`, `emple_fechanacimiento`, `emple_nrodocumento`, `emple_movil`, `empl_modalidad`, `emple_email`, `emple_estatus`, `emple_direccion`, `empl_fotoperfil`) VALUES
(1, 'JERSSON JORGE', 'CORILLA', 'MIRANDA', '2023-03-01', '2023-03-31', '234234', '918654046', 'Funcionario', 'jersson14071996@gmail.com', 'ACTIVO', 'JR. NICOLAS DE PIEROLA Nº 105', 'controller/empleado/FOTOS/IMG28-8-2023-11-64.jpg'),
(2, 'JOSE', 'SANCHEZ', 'MEDINA', '2023-08-25', '1985-01-24', '55151151', '926262625', 'CAS', 'jose21@gmail.com', 'ACTIVO', 'JR. CHALHUANCA N° 222', 'controller/empleado/FOTOS/IMG28-8-2023-11-195.png'),
(3, 'ANDREA', 'SANCHEZ', 'JIMENEZ', '2023-08-25', '1995-01-24', '26626266', '966226262', 'CAS', 'andrea12@gmail.com', 'ACTIVO', 'AV. DIAZ BARCENAS N° 323', 'controller/empleado/FOTOS/IMG28-8-2023-11-271.jpeg'),
(4, 'LUIS', 'CAMACHO', 'VELARDE', '2023-08-25', '1998-01-24', '15511515', '926622656', 'Servidor Público', 'luis2112@gmail.com', 'ACTIVO', 'JR. HUANCAVELICA N° 323', 'controller/empleado/FOTOS/IMG21-10-2023-15-225.png'),
(5, 'JUAN CARLOS', 'MEDINA', 'SANCHEZ', '2023-08-27', '2000-07-25', '62262626', '926161616', 'Practicante', 'juanca2@gmail.com', 'ACTIVO', 'JR. CUSCO N° 323', 'controller/empleado/FOTOS/IMG8-11-2023-9-146.png'),
(6, 'CELIA', 'MIRANDA', 'MUNGUIA', '2023-08-29', '1972-01-24', '09747535', '988505521', 'Tercero', 'cmirandam@utea.edu.pe', 'ACTIVO', 'JR NICOLAS DE PIEROLA N° 105', 'controller/empleado/FOTOS/IMG29-8-2023-16-940.jpg'),
(7, 'WILFREDO', 'CARRIÓN', 'UMERES', '2023-09-07', '1995-05-11', '31044054', '952541551', 'CAS', 'willy22@gmail.co', 'ACTIVO', 'AV 28 DE ABRIL 234', 'controller/empleado/FOTOS/IMG7-9-2023-18-768.jpg'),
(8, 'ELIAS', 'CARRIÓN', 'UMERES', '2023-09-10', '1985-05-25', '41239943', '935951872', 'Tercero', 'eliascar888@hotmail.com', 'ACTIVO', 'AV 28 DE ABRIL N° 234', 'controller/empleado/FOTOS/IMG10-9-2023-11-957.jpg'),
(9, 'PEDRO', 'JIMENEZ', 'SANCHEZ', '2023-10-22', '1980-08-14', '51151515', '915151551', 'CAS', 'pedro3@gmail.com', 'ACTIVO', 'AV. CANADA S/N', 'controller/empleado/FOTOS/usuario.png'),
(10, 'JOSUE', 'SANCHEZ', 'PERALTA', '2023-10-22', '1990-01-15', '65611661', '915615515', 'Servidor Público', 'josue21@gmail.com', 'ACTIVO', 'JR. CUSCO N° 455', 'controller/empleado/FOTOS/IMG11-2-2024-11-772.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `empresa_id` int(11) NOT NULL,
  `emp_razon` varchar(250) NOT NULL,
  `emp_email` varchar(250) NOT NULL,
  `emp_cod` varchar(10) NOT NULL,
  `emp_telefono` varchar(20) NOT NULL,
  `emp_direccion` varchar(250) NOT NULL,
  `emp_logo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`empresa_id`, `emp_razon`, `emp_email`, `emp_cod`, `emp_telefono`, `emp_direccion`, `emp_logo`) VALUES
(2, 'DIRESA ABANCAY', 'DIRESA123@GMAIL.COM', '14521', '952652145', 'AV. DANIEL ALCIDES CARRION S/N ABANCAY APURIMAC', 'controller/empresa/FOTOS/IMG22-10-2023-11-977.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento`
--

CREATE TABLE `movimiento` (
  `movimiento_id` int(11) NOT NULL,
  `documento_id` char(12) NOT NULL,
  `area_origen_id` int(11) DEFAULT NULL,
  `areadestino_id` int(11) NOT NULL,
  `mov_fecharegistro` datetime DEFAULT current_timestamp(),
  `mov_descripcion` varchar(255) NOT NULL,
  `mov_estatus` enum('PENDIENTE','CONFORME','INCOFORME','ACEPTADO','DERIVADO','FINALIZADO','RECHAZADO') DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `mov_archivo` varchar(255) DEFAULT NULL,
  `mov_descripcion_original` varchar(255) DEFAULT NULL,
  `mov_acciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `movimiento`
--

INSERT INTO `movimiento` (`movimiento_id`, `documento_id`, `area_origen_id`, `areadestino_id`, `mov_fecharegistro`, `mov_descripcion`, `mov_estatus`, `usuario_id`, `mov_archivo`, `mov_descripcion_original`, `mov_acciones`) VALUES
(1, 'D0000001', 3, 1, '2024-02-11 10:10:55', 'COMUNICAR A LOS JEFES DE AREA', 'DERIVADO', 2, 'controller/tramite/documentos/ARCH11-2-2024-10-633.PDF', NULL, '-3. REVISAR-ONON'),
(2, 'D0000002', 5, 3, '2024-02-11 10:13:40', 'SE ENVIA CARTA NOTARIAL', 'DERIVADO', 2, 'controller/tramite/documentos/ARCH11-2-2024-10-221.PDF', NULL, '4. -V° B°-ONON'),
(3, 'D0000001', 1, 7, '2024-02-11 10:28:04', 'DAR VISTO BUENO POR DIRECTOR', 'DERIVADO', 3, 'controller/tramite_area/documentos/ARCH11-2-2024-10-751.PDF', NULL, '4. -V° B°-'),
(4, 'D0000002', 3, 6, '2024-02-11 10:30:52', 'REVISAR Y REALIZAR SEGUIMIENTO DE DOCUMENTO', 'DERIVADO', 4, 'controller/tramite_area/documentos/ARCH11-2-2024-10-779.PDF', NULL, '-3. REVISAR--9. SEGUIMIENTO-'),
(5, 'D0000002', 6, 6, '2024-02-11 10:38:13', 'SE REALIZARA UN INFORME DE CONFORMIDAD', 'FINALIZADO', 8, '', NULL, '-17. INFORME-'),
(6, 'D0000001', 7, 7, '2024-02-11 10:40:24', 'SE REALIZAR AUN INFORME', 'FINALIZADO', 9, '', NULL, '-17. INFORME-'),
(9, 'D0000005', 3, 7, '2024-02-11 12:12:02', 'REVISAR FROMATO DE POI', 'DERIVADO', 2, 'controller/tramite/documentos/ARCH11-2-2024-12-978.PDF', NULL, '-3. REVISAR-4. -V° B°--5. COORDINAR--9. SEGUIMIENTO--10. DAR RESPUESTA-ONON'),
(14, 'D0000005', 7, 7, '2024-03-19 10:50:51', 'SE FINALIZO EL DOCUMENTO CORRECTAMENTE', 'FINALIZADO', 9, '', NULL, '4. -V° B°--6. CONOCIMIENTO-'),
(15, 'D0000002', 6, 6, '2024-03-19 10:51:55', 'SE FINALIZO EL DOCUMENTO', 'FINALIZADO', 8, '', NULL, '4. -V° B°--6. CONOCIMIENTO-'),
(16, 'D0000006', 6, 1, '2024-03-19 10:55:35', 'SE HACE ENTREGA DEL REPORTE DE EPIDEMIOLOGIA', 'DERIVADO', 8, 'controller/tramite/documentos/ARCH19-3-2024-10-778.PDF', NULL, '-1. ACCIÓN--2. TRAMITAR-ONON'),
(17, 'D0000006', 1, 1, '2024-03-19 10:57:05', 'SE RECEPCIONÓ CORRECTAMENTE EL DOCUMENTO', 'FINALIZADO', 3, 'controller/tramite_area/documentos/ARCH19-3-2024-10-914.', NULL, '4. -V° B°--6. CONOCIMIENTO-'),
(19, 'D0000008', 1, 1, '2024-03-19 11:30:26', 'SOLICITO COPIA DE HISTORIA CLÍNICA', 'DERIVADO', 3, 'controller/tramite/documentos/ARCH19-3-2024-11-957.PDF', NULL, '--REVISAR--'),
(21, 'D0000010', 1, 1, '2024-03-19 17:09:52', 'OFICIO PARA REUNIÓN URGENTE EN LA REGIÓN', 'DERIVADO', 3, 'controller/tramite/documentos/ARCH19-3-2024-17-62.PDF', NULL, '--REVISAR--'),
(22, 'D0000011', 5, 1, '2024-03-19 17:14:13', 'REVISAR EXPEDIENTE', 'DERIVADO', 2, 'controller/tramite/documentos/ARCH19-3-2024-17-410.PDF', NULL, '-1. ACCIÓN--2. TRAMITAR-ONON'),
(23, 'D0000011', 1, 1, '2024-03-19 17:17:12', '', 'FINALIZADO', 3, 'controller/tramite_area/documentos/ARCH19-3-2024-17-788.', NULL, '4. -V° B°--6. CONOCIMIENTO-'),
(29, 'D0000015', 1, 1, '2025-08-22 19:33:33', 'SDFDS', 'DERIVADO', 3, 'controller/tramite/documentos/ARCH22-8-2025-19-528.PDF', NULL, '-1. ACCIÓN--2. TRAMITAR-ONONON'),
(30, 'D0000010', 1, 3, '2025-08-22 19:34:04', '', 'DERIVADO', 3, 'controller/tramite_area/documentos/ARCH22-8-2025-19-320.PDF', NULL, '-3. REVISAR-4. -V° B°-'),
(31, 'D0000015', 1, 6, '2025-08-22 19:34:40', '', 'DERIVADO', 3, 'controller/tramite_area/documentos/ARCH22-8-2025-19-465.PDF', NULL, '-1. ACCIÓN--2. TRAMITAR-'),
(32, 'D0000015', 1, 5, '2025-08-22 19:34:52', '', 'PENDIENTE', 3, 'controller/tramite_area/documentos/ARCH22-8-2025-19-273.PDF', NULL, '-1. ACCIÓN--2. TRAMITAR-'),
(33, 'D0000010', 3, 5, '2025-08-22 19:36:58', '', 'DERIVADO', 4, 'controller/tramite_area/documentos/ARCH22-8-2025-19-777.PDF', NULL, '-3. REVISAR-'),
(37, 'D0000010', 5, 7, '2025-08-22 20:00:59', '', 'PENDIENTE', 5, 'controller/tramite_area/documentos/ARCH22-8-2025-20-26.PDF', NULL, '-3. REVISAR-4. -V° B°-'),
(38, 'D0000017', 1, 3, '2025-08-22 20:01:57', 'SADSD', 'DERIVADO', 2, 'controller/tramite/documentos/ARCH22-8-2025-20-203.PDF', NULL, '-2. TRAMITAR--3. REVISAR-ONON'),
(39, 'D0000018', 3, 6, '2025-08-22 20:06:28', 'DSF', 'PENDIENTE', 2, 'controller/tramite/documentos/ARCH22-8-2025-20-732.PDF', NULL, 'ONON'),
(40, 'D0000019', 3, 5, '2025-08-22 20:13:15', 'SADF', 'PENDIENTE', 4, 'controller/tramite/documentos/ARCH22-8-2025-20-315.PDF', NULL, '-3. REVISAR-4. -V° B°-ONON'),
(41, 'D0000020', 3, 7, '2025-08-22 20:17:07', 'SADFSD', 'PENDIENTE', 4, 'controller/tramite/documentos/ARCH22-8-2025-20-475.PDF', NULL, 'ONON'),
(42, 'D0000017', 3, 5, '2025-08-22 20:23:00', '', 'PENDIENTE', 4, '', NULL, '-3. REVISAR-4. -V° B°-'),
(46, 'D0000008', 1, 4, '2025-08-22 20:28:09', '', 'DERIVADO', 3, '', NULL, '-2. TRAMITAR--3. REVISAR-4. -V° B°-'),
(48, 'D0000008', 4, 7, '2025-08-22 20:29:47', '', 'PENDIENTE', 6, '', NULL, '-1. ACCIÓN--2. TRAMITAR-'),
(50, 'D0000021', 1, 3, '2025-08-22 20:36:31', 'SDFDS', 'PENDIENTE', 3, 'controller/tramite/documentos/ARCH22-8-2025-20-389.PDF', NULL, '-3. REVISAR-4. -V° B°-ONON'),
(51, 'D0000022', 1, 7, '2025-08-22 20:37:19', 'SDFDS', 'PENDIENTE', 3, 'controller/tramite/documentos/ARCH22-8-2025-20-606.PDF', NULL, '4. -V° B°-ONON'),
(52, 'D0000023', 6, 7, '2025-08-22 21:14:57', 'SDF', 'PENDIENTE', 8, 'controller/tramite/documentos/ARCH22-8-2025-21-575.PDF', NULL, '4. -V° B°--5. COORDINAR-ONON'),
(53, 'D0000024', 1, 1, '2025-08-22 21:30:20', 'SADSDF', 'PENDIENTE', 3, 'controller/tramite/documentos/ARCH22-8-2025-21-935.PDF', NULL, '-1. ACCIÓN--2. TRAMITAR-ONON');

--
-- Disparadores `movimiento`
--
DELIMITER $$
CREATE TRIGGER `actualizar` BEFORE INSERT ON `movimiento` FOR EACH ROW UPDATE
documento
set
dias_pasados=DATEDIFF(CURDATE(),doc_fecharegistro)
WHERE documento.doc_estatus="PENDIENTE"
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE `tipo_documento` (
  `tipodocumento_id` int(11) NOT NULL COMMENT 'Codigo auto-incrementado del tipo documento',
  `tipodo_descripcion` varchar(50) NOT NULL COMMENT 'Descripcion del  tipo documento',
  `tipodo_estado` enum('ACTIVO','INACTIVO') NOT NULL COMMENT 'estado del tipo de documento',
  `requisitos` varchar(255) DEFAULT NULL,
  `tipodo_feregistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Entidad Documento' ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`tipodocumento_id`, `tipodo_descripcion`, `tipodo_estado`, `requisitos`, `tipodo_feregistro`) VALUES
(1, 'CARTA', 'ACTIVO', '- DNI, PARTIDA DE NACIMIENTO', '2023-03-22 15:26:16'),
(2, 'OFICIO', 'ACTIVO', '- DNI', '2023-04-16 10:29:36'),
(3, 'SOLICITUD', 'ACTIVO', '- DNI', '2023-04-16 10:29:43'),
(4, 'MEMORANDUM', 'ACTIVO', 'NINGUNO', '2023-04-18 16:55:14'),
(5, 'RECIBO', 'ACTIVO', '-DNI\n-BOLETA SUNAT', '2023-04-18 22:25:36'),
(6, 'CARTA CIRCULAR', 'ACTIVO', 'DNI', '2023-04-19 16:21:14'),
(7, 'CARTA DE PRESENTACIÒN', 'ACTIVO', '-CV\n-CARTA DE PRESENTACION', '2023-04-19 16:21:29'),
(8, 'CARTA DE REQUERIMIENTO', 'ACTIVO', '-REQUERIMIENTO\n-SOLICITUD', '2023-04-19 16:21:39'),
(9, 'CARTA MULTIPLE', 'ACTIVO', NULL, '2023-04-19 16:21:48'),
(10, 'CARTA NOTARIAL', 'ACTIVO', NULL, '2023-04-19 16:21:57'),
(11, 'CÉDULA DE NOTIFICACIÓN', 'ACTIVO', NULL, '2023-04-19 16:22:14'),
(12, 'CONTRATO', 'ACTIVO', NULL, '2023-04-19 16:22:47'),
(13, 'CONVENIO', 'ACTIVO', NULL, '2023-04-19 16:22:51'),
(14, 'DOCUMENTO CONFIDENCIAL', 'ACTIVO', '- DNI\n- PARTIDA NACIMIENTO\n- TITULO PROFESIONAL', '2023-04-19 16:23:04'),
(15, 'DOCUMENTO VIA FAX', 'ACTIVO', NULL, '2023-04-19 16:23:17'),
(16, 'DOCUMENTO VIA EMAIL', 'ACTIVO', NULL, '2023-04-19 16:23:32'),
(17, 'EXHORTO', 'ACTIVO', NULL, '2023-04-19 16:23:42'),
(18, 'INFORME', 'ACTIVO', NULL, '2023-04-19 16:23:45'),
(19, 'INFORME CONFIDENCIAL', 'ACTIVO', NULL, '2023-04-19 16:23:58'),
(20, 'DOCUMENTO DE SUPERVISIÓN', 'ACTIVO', NULL, '2023-04-19 16:24:51'),
(21, 'INFORME TÉCNICO', 'ACTIVO', NULL, '2023-04-19 16:24:56'),
(22, 'MEMORANDUM MULTIPLE', 'ACTIVO', NULL, '2023-04-19 16:25:06'),
(23, 'MEMORIAL', 'ACTIVO', NULL, '2023-04-19 16:25:26'),
(24, 'NOTIFICACIONES JUDICIALES', 'ACTIVO', NULL, '2023-04-19 16:25:32'),
(25, 'OFICIO CIRCULAR', 'ACTIVO', NULL, '2023-04-19 16:25:42'),
(26, 'OFICIO MULTIPLE', 'ACTIVO', NULL, '2023-04-19 16:25:47'),
(27, 'PETITORIO', 'ACTIVO', NULL, '2023-04-19 16:25:58'),
(28, 'PRAES', 'ACTIVO', NULL, '2023-04-19 16:26:05'),
(29, 'PROVEIDO', 'ACTIVO', NULL, '2023-04-19 16:26:09'),
(30, 'RESOLUCIONES', 'ACTIVO', NULL, '2023-04-19 16:26:18'),
(31, 'ADENDA', 'ACTIVO', NULL, '2023-04-19 16:26:33'),
(32, 'ARCHIVADO', 'ACTIVO', NULL, '2023-04-19 16:26:44'),
(33, 'OTROS', 'ACTIVO', NULL, '2023-04-19 16:26:50'),
(34, 'OFERTA', 'ACTIVO', 'DNI', '2023-04-22 20:40:02'),
(35, 'TESTER', 'ACTIVO', 'DNI', '2023-04-23 12:05:43');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usu_id` int(11) NOT NULL,
  `usu_usuario` varchar(250) DEFAULT '',
  `usu_contra` varchar(250) DEFAULT NULL,
  `usu_feccreacion` date DEFAULT NULL,
  `usu_fecupdate` date DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `usu_observacion` varchar(250) DEFAULT NULL,
  `usu_estatus` enum('ACTIVO','INACTIVO') NOT NULL,
  `area_id` int(11) DEFAULT NULL,
  `usu_rol` enum('Secretario (a)','Administrador') NOT NULL,
  `empresa_id` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci ROW_FORMAT=DYNAMIC;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usu_id`, `usu_usuario`, `usu_contra`, `usu_feccreacion`, `usu_fecupdate`, `empleado_id`, `usu_observacion`, `usu_estatus`, `area_id`, `usu_rol`, `empresa_id`) VALUES
(2, 'jersson', '$2y$12$OFdwUIOo./CC.vnSX.73LeKctoIi.kB632x0q42O9cB.gJMdFu5iC', '2023-08-25', NULL, 1, NULL, 'ACTIVO', 2, 'Administrador', 2),
(3, 'JOSE', '$2y$12$lvoj5Xdta18f0FvBD/Kv/OKNzoNwgi4EyWFt3cnIp4pH7YEv9uk6G', '2023-08-25', NULL, 2, NULL, 'ACTIVO', 1, 'Secretario (a)', 2),
(4, 'ANDREA', '$2y$12$J3VwFNFSAjqdo0yuYdWL6e.kDmetlR9QVjCf5EtKuIzIRM/aDdmFC', '2023-08-25', NULL, 3, NULL, 'ACTIVO', 3, 'Secretario (a)', 2),
(5, 'LUIS', '$2y$12$p.qLHsx8sRemyzO..tXey..eEIQcprHXlhjHtYgtHDBzEj2QDF232', '2023-08-25', NULL, 4, NULL, 'ACTIVO', 5, 'Secretario (a)', 2),
(6, 'JUAN', '$2y$12$arbO9/L.m8Z.TnXdFsfdDOaTVGSVGb/P/FUVMLCciB5t0WdKQF7tG', '2023-08-27', NULL, 5, NULL, 'ACTIVO', 4, 'Secretario (a)', 2),
(7, 'WILLY', '$2y$12$KX8EUu4Qz0a7lsUclaT4IOZr9dNR/QV3nKUyOq2tx.LcJne1G2Hba', '2023-09-07', NULL, 7, NULL, 'INACTIVO', 4, 'Administrador', 2),
(8, 'eliascar', '$2y$12$wdPTnurFDab5l.0lT7VTg.unBYNeYT/3DXkzbGNX2GENiSvAuJ3F2', '2023-09-10', NULL, 8, NULL, 'ACTIVO', 6, 'Secretario (a)', 2),
(9, 'jose123', '$2y$12$wwmyjKhHZq.i.r7Nw8vmE.WJKc3D0UD6bZdEufl4MRmzmcJkouaAG', '2024-02-11', NULL, 10, NULL, 'ACTIVO', 7, 'Secretario (a)', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`area_cod`) USING BTREE,
  ADD UNIQUE KEY `unico` (`area_nombre`) USING BTREE;

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id_comunicado`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`documento_id`) USING BTREE,
  ADD KEY `tipodocumento_id` (`tipodocumento_id`) USING BTREE,
  ADD KEY `area_id` (`area_id`),
  ADD KEY `area_origen` (`area_origen`),
  ADD KEY `area_destino` (`area_destino`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `doc_nrodocumento` (`doc_nrodocumento`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`empleado_id`) USING BTREE;

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`empresa_id`) USING BTREE;

--
-- Indices de la tabla `movimiento`
--
ALTER TABLE `movimiento`
  ADD PRIMARY KEY (`movimiento_id`) USING BTREE,
  ADD KEY `area_origen_id` (`area_origen_id`) USING BTREE,
  ADD KEY `areadestino_id` (`areadestino_id`) USING BTREE,
  ADD KEY `usuario_id` (`usuario_id`) USING BTREE,
  ADD KEY `documento_id` (`documento_id`) USING BTREE;

--
-- Indices de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  ADD PRIMARY KEY (`tipodocumento_id`) USING BTREE;

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usu_id`) USING BTREE,
  ADD KEY `empleado_id` (`empleado_id`) USING BTREE,
  ADD KEY `area_id` (`area_id`) USING BTREE,
  ADD KEY `empresa_id` (`empresa_id`) USING BTREE;

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `area`
--
ALTER TABLE `area`
  MODIFY `area_cod` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Codigo auto-incrementado del movimiento del area', AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id_comunicado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `empleado_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `empresa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `movimiento`
--
ALTER TABLE `movimiento`
  MODIFY `movimiento_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  MODIFY `tipodocumento_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Codigo auto-incrementado del tipo documento', AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD CONSTRAINT `comunicados_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`usu_id`);

--
-- Filtros para la tabla `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`tipodocumento_id`) REFERENCES `tipo_documento` (`tipodocumento_id`),
  ADD CONSTRAINT `documento_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_cod`),
  ADD CONSTRAINT `documento_ibfk_3` FOREIGN KEY (`area_origen`) REFERENCES `area` (`area_cod`),
  ADD CONSTRAINT `documento_ibfk_4` FOREIGN KEY (`area_destino`) REFERENCES `area` (`area_cod`),
  ADD CONSTRAINT `documento_ibfk_5` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`empleado_id`);

--
-- Filtros para la tabla `movimiento`
--
ALTER TABLE `movimiento`
  ADD CONSTRAINT `movimiento_ibfk_1` FOREIGN KEY (`area_origen_id`) REFERENCES `area` (`area_cod`),
  ADD CONSTRAINT `movimiento_ibfk_2` FOREIGN KEY (`areadestino_id`) REFERENCES `area` (`area_cod`),
  ADD CONSTRAINT `movimiento_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usu_id`),
  ADD CONSTRAINT `movimiento_ibfk_4` FOREIGN KEY (`documento_id`) REFERENCES `documento` (`documento_id`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`empleado_id`),
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_cod`),
  ADD CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`empresa_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
