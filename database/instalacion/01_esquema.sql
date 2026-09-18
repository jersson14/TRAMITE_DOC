-- ============================================================================
-- SISTRAMITEDOC - Esquema de la base de datos (instalación nueva)
--
-- Estructura completa, con todas las migraciones hasta la 028 ya incluidas:
-- tablas, procedimientos almacenados y el disparador. NO contiene datos.
--
-- Uso (ver docs/04-instalacion-y-despliegue.md):
--   1. Crear la base:  CREATE DATABASE sistema_tramite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
--   2. Este archivo:   01_esquema.sql
--   3. Datos iniciales: 02_datos_iniciales.sql
--
-- En una instalación NUEVA no hace falta correr database/migraciones: ya están
-- aplicadas aquí. Las migraciones sirven para ACTUALIZAR una instalación anterior.
--
-- Generado desde la base de desarrollo con mysqldump --no-data. Se quitaron las
-- cláusulas DEFINER (fallarían en un servidor con otro usuario) y los contadores
-- AUTO_INCREMENT.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `area` (
  `area_cod` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Codigo auto-incrementado del movimiento del area',
  `area_nombre` varchar(50) NOT NULL COMMENT 'nombre del area',
  `area_fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'fecha del registro del movimiento',
  `area_estado` enum('ACTIVO','INACTIVO') NOT NULL COMMENT 'estado del area',
  `area_sigla` varchar(20) DEFAULT NULL COMMENT 'Sigla para numerar documentos: OGA, RRHH, MP...',
  PRIMARY KEY (`area_cod`) USING BTREE,
  UNIQUE KEY `unico` (`area_nombre`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='Entidad Area';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `bit_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `bit_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `bit_usuario` varchar(150) DEFAULT NULL,
  `bit_rol` varchar(50) DEFAULT NULL,
  `bit_accion` varchar(60) NOT NULL,
  `bit_entidad` varchar(40) DEFAULT NULL,
  `bit_entidad_id` varchar(40) DEFAULT NULL,
  `bit_detalle` varchar(500) DEFAULT NULL,
  `bit_ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`bit_id`),
  KEY `idx_bit_fecha` (`bit_fecha`),
  KEY `idx_bit_entidad` (`bit_entidad`,`bit_entidad_id`),
  KEY `idx_bit_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comunicado_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comunicado_area` (
  `id_comunicado` int(11) NOT NULL,
  `area_cod` int(11) NOT NULL,
  PRIMARY KEY (`id_comunicado`,`area_cod`),
  KEY `idx_comarea_area` (`area_cod`),
  CONSTRAINT `fk_comarea_area` FOREIGN KEY (`area_cod`) REFERENCES `area` (`area_cod`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comarea_comunicado` FOREIGN KEY (`id_comunicado`) REFERENCES `comunicados` (`id_comunicado`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comunicado_leido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comunicado_leido` (
  `id_comunicado` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_comunicado`,`usuario_id`),
  KEY `idx_comleido_usuario` (`usuario_id`),
  CONSTRAINT `fk_comleido_comunicado` FOREIGN KEY (`id_comunicado`) REFERENCES `comunicados` (`id_comunicado`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comleido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usu_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `comunicados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comunicados` (
  `id_comunicado` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(1000) NOT NULL,
  `descripcion` text NOT NULL,
  `enlace` varchar(10000) DEFAULT NULL,
  `fecha_registro` date NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `estado` enum('NUEVO','PASADO') NOT NULL,
  `com_correlativo` int(11) DEFAULT NULL,
  `com_destino` enum('TODOS','ADMINISTRADORES','SECRETARIAS','AREAS') NOT NULL DEFAULT 'TODOS',
  `com_desde` date DEFAULT NULL COMMENT 'Vigente desde (NULL = sin límite)',
  `com_hasta` date DEFAULT NULL COMMENT 'Vigente hasta (NULL = sin límite)',
  `com_imagen` varchar(255) DEFAULT NULL COMMENT 'Ruta relativa de la imagen del comunicado',
  PRIMARY KEY (`id_comunicado`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `comunicados_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`usu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion` (
  `conf_clave` varchar(60) NOT NULL,
  `conf_valor` text DEFAULT NULL,
  `conf_actualizado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`conf_clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `correlativo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `correlativo` (
  `corr_anio` int(11) NOT NULL,
  `corr_tipo` varchar(10) NOT NULL,
  `corr_ultimo` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`corr_anio`,`corr_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documento` (
  `documento_id` char(12) NOT NULL,
  `doc_expediente` varchar(20) DEFAULT NULL,
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
  `doc_nrodocumento` varchar(80) NOT NULL,
  `doc_folio` int(11) NOT NULL,
  `doc_asunto` varchar(255) NOT NULL,
  `doc_archivo` varchar(255) NOT NULL,
  `doc_fecharegistro` datetime DEFAULT current_timestamp(),
  `area_id` int(11) DEFAULT 1,
  `doc_estatus` enum('PENDIENTE','RECHAZADO','ACEPTADO','FINALIZADO','SIN RESPUESTA','OBSERVADO') NOT NULL,
  `area_origen` int(11) NOT NULL DEFAULT 0,
  `area_destino` int(11) DEFAULT NULL,
  `doc_ncorrelativo` int(11) DEFAULT NULL,
  `dias_pasados` int(11) DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `acciones` varchar(255) DEFAULT NULL,
  `doc_observaciones` varchar(255) DEFAULT NULL,
  `dias_respuesta` int(11) DEFAULT NULL,
  `doc_fecharecepcion` datetime DEFAULT NULL COMMENT 'Fecha en que se considera presentado (horario de atención)',
  `doc_procedencia` enum('INTERNO','EXTERNO') NOT NULL DEFAULT 'INTERNO' COMMENT 'INTERNO: lo produce la entidad (se firma). EXTERNO: llega de afuera (solo se verifica)',
  PRIMARY KEY (`documento_id`) USING BTREE,
  UNIQUE KEY `idx_doc_expediente` (`doc_expediente`),
  KEY `tipodocumento_id` (`tipodocumento_id`) USING BTREE,
  KEY `area_id` (`area_id`),
  KEY `area_origen` (`area_origen`),
  KEY `area_destino` (`area_destino`),
  KEY `id_empleado` (`id_empleado`),
  KEY `doc_nrodocumento` (`doc_nrodocumento`),
  CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`tipodocumento_id`) REFERENCES `tipo_documento` (`tipodocumento_id`),
  CONSTRAINT `documento_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_cod`),
  CONSTRAINT `documento_ibfk_3` FOREIGN KEY (`area_origen`) REFERENCES `area` (`area_cod`),
  CONSTRAINT `documento_ibfk_4` FOREIGN KEY (`area_destino`) REFERENCES `area` (`area_cod`),
  CONSTRAINT `documento_ibfk_5` FOREIGN KEY (`id_empleado`) REFERENCES `empleado` (`empleado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `documento_anexo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documento_anexo` (
  `anexo_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `documento_id` char(12) NOT NULL,
  `anexo_nombre` varchar(200) NOT NULL COMMENT 'Nombre con el que el usuario subió el archivo',
  `anexo_ruta` varchar(255) NOT NULL COMMENT 'Ruta relativa dentro del sistema',
  `anexo_bytes` int(11) NOT NULL DEFAULT 0,
  `anexo_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`anexo_id`),
  KEY `idx_anexo_documento` (`documento_id`),
  CONSTRAINT `fk_anexo_documento` FOREIGN KEY (`documento_id`) REFERENCES `documento` (`documento_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empleado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empleado` (
  `empleado_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `empl_fotoperfil` varchar(255) NOT NULL DEFAULT 'Fotos/admin.png',
  PRIMARY KEY (`empleado_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresa` (
  `empresa_id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_razon` varchar(250) NOT NULL,
  `emp_sigla` varchar(60) DEFAULT NULL,
  `emp_email` varchar(250) NOT NULL,
  `emp_cod` varchar(10) NOT NULL,
  `emp_telefono` varchar(20) NOT NULL,
  `emp_direccion` varchar(250) NOT NULL,
  `emp_logo` varchar(255) NOT NULL,
  `emp_color` char(7) NOT NULL DEFAULT '#1F2358',
  `emp_hora_inicio` time NOT NULL DEFAULT '08:00:00',
  `emp_hora_fin` time NOT NULL DEFAULT '16:30:00',
  PRIMARY KEY (`empresa_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `feriado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `feriado` (
  `fecha` date NOT NULL,
  `descripcion` varchar(120) NOT NULL,
  `tipo` enum('NACIONAL','REGIONAL','NO_LABORABLE') NOT NULL DEFAULT 'NACIONAL',
  PRIMARY KEY (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `firma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `firma` (
  `firma_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `firma_codigo` char(10) NOT NULL,
  `documento_id` char(12) NOT NULL,
  `anexo_id` bigint(20) DEFAULT NULL COMMENT 'Archivo firmado resultante (documento_anexo)',
  `firma_orden` int(11) NOT NULL DEFAULT 1 COMMENT 'Número de esta firma dentro del PDF',
  `archivo_origen` varchar(255) NOT NULL,
  `archivo_firmado` varchar(255) NOT NULL,
  `hash_origen` char(64) NOT NULL,
  `hash_firmado` char(64) NOT NULL,
  `metodo` enum('PFX','FIRMA_PERU') NOT NULL DEFAULT 'PFX',
  `motivo` varchar(150) DEFAULT NULL,
  `firmante_nombre` varchar(200) NOT NULL,
  `firmante_dni` varchar(15) DEFAULT NULL,
  `cert_emisor` varchar(255) DEFAULT NULL,
  `cert_serie` varchar(80) DEFAULT NULL,
  `cert_desde` datetime DEFAULT NULL,
  `cert_hasta` datetime DEFAULT NULL,
  `cert_huella` char(64) DEFAULT NULL COMMENT 'SHA-256 del certificado',
  `cert_autofirmado` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `firma_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`firma_id`),
  UNIQUE KEY `idx_firma_codigo` (`firma_codigo`),
  KEY `idx_firma_documento` (`documento_id`),
  KEY `idx_firma_anexo` (`anexo_id`),
  CONSTRAINT `fk_firma_documento` FOREIGN KEY (`documento_id`) REFERENCES `documento` (`documento_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `movimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimiento` (
  `movimiento_id` int(11) NOT NULL AUTO_INCREMENT,
  `documento_id` char(12) NOT NULL,
  `area_origen_id` int(11) DEFAULT NULL,
  `areadestino_id` int(11) NOT NULL,
  `mov_fecharegistro` datetime DEFAULT current_timestamp(),
  `mov_descripcion` varchar(255) NOT NULL,
  `mov_estatus` enum('PENDIENTE','CONFORME','INCOFORME','ACEPTADO','DERIVADO','FINALIZADO','RECHAZADO','ATENDIDO') DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `mov_archivo` varchar(255) DEFAULT NULL,
  `mov_descripcion_original` varchar(255) DEFAULT NULL,
  `mov_acciones` varchar(255) DEFAULT NULL,
  `mov_recibido_fecha` datetime DEFAULT NULL,
  `mov_recibido_usuario` int(11) DEFAULT NULL,
  `mov_tipo` enum('PRINCIPAL','COPIA','ATENCION') NOT NULL DEFAULT 'PRINCIPAL',
  `mov_plazo_dias` int(11) DEFAULT NULL,
  `mov_respuesta` text DEFAULT NULL,
  `mov_respuesta_fecha` datetime DEFAULT NULL,
  `mov_respuesta_usuario` int(11) DEFAULT NULL,
  `mov_respuesta_archivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`movimiento_id`) USING BTREE,
  KEY `area_origen_id` (`area_origen_id`) USING BTREE,
  KEY `areadestino_id` (`areadestino_id`) USING BTREE,
  KEY `usuario_id` (`usuario_id`) USING BTREE,
  KEY `documento_id` (`documento_id`) USING BTREE,
  CONSTRAINT `movimiento_ibfk_1` FOREIGN KEY (`area_origen_id`) REFERENCES `area` (`area_cod`),
  CONSTRAINT `movimiento_ibfk_2` FOREIGN KEY (`areadestino_id`) REFERENCES `area` (`area_cod`),
  CONSTRAINT `movimiento_ibfk_3` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usu_id`),
  CONSTRAINT `movimiento_ibfk_4` FOREIGN KEY (`documento_id`) REFERENCES `documento` (`documento_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
ALTER DATABASE `sistema_tramite` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50003 TRIGGER `actualizar` BEFORE INSERT ON `movimiento` FOR EACH ROW UPDATE

documento

set

dias_pasados=DATEDIFF(CURDATE(),doc_fecharegistro)

WHERE documento.doc_estatus="PENDIENTE" */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `sistema_tramite` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
DROP TABLE IF EXISTS `movimiento_anexo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimiento_anexo` (
  `movimiento_id` int(11) NOT NULL,
  `anexo_id` bigint(20) NOT NULL,
  PRIMARY KEY (`movimiento_id`,`anexo_id`),
  KEY `idx_movanexo_anexo` (`anexo_id`),
  CONSTRAINT `fk_movanexo_anexo` FOREIGN KEY (`anexo_id`) REFERENCES `documento_anexo` (`anexo_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_movanexo_movimiento` FOREIGN KEY (`movimiento_id`) REFERENCES `movimiento` (`movimiento_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `observacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `observacion` (
  `observacion_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `documento_id` char(12) NOT NULL,
  `obs_motivo` text NOT NULL COMMENT 'Qué le falta o qué debe corregir el ciudadano',
  `obs_plazo_dias` int(11) NOT NULL COMMENT 'Plazo en días hábiles',
  `obs_fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `obs_fecha_limite` date NOT NULL,
  `obs_estado` enum('PENDIENTE','SUBSANADO') NOT NULL DEFAULT 'PENDIENTE',
  `obs_estado_anterior` varchar(20) NOT NULL COMMENT 'Estado del trámite antes de observarlo; se restituye al subsanar',
  `usuario_id` int(11) DEFAULT NULL,
  `area_id` int(11) DEFAULT NULL,
  `sub_fecha` datetime DEFAULT NULL,
  `sub_texto` text DEFAULT NULL COMMENT 'Comentario del ciudadano al subsanar',
  `sub_archivo` varchar(255) DEFAULT NULL,
  `sub_nombre_archivo` varchar(200) DEFAULT NULL,
  `sub_ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`observacion_id`),
  KEY `idx_obs_documento` (`documento_id`),
  KEY `idx_obs_pendientes` (`obs_estado`,`obs_fecha_limite`),
  CONSTRAINT `fk_obs_documento` FOREIGN KEY (`documento_id`) REFERENCES `documento` (`documento_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tipo_documento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipo_documento` (
  `tipodocumento_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Codigo auto-incrementado del tipo documento',
  `tipodo_descripcion` varchar(50) NOT NULL COMMENT 'Descripcion del  tipo documento',
  `tipodo_estado` enum('ACTIVO','INACTIVO') NOT NULL COMMENT 'estado del tipo de documento',
  `requisitos` varchar(255) DEFAULT NULL,
  `tipodo_feregistro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`tipodocumento_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='Entidad Documento';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `usu_id` int(11) NOT NULL AUTO_INCREMENT,
  `usu_usuario` varchar(250) DEFAULT '',
  `usu_contra` varchar(250) DEFAULT NULL,
  `usu_feccreacion` date DEFAULT NULL,
  `usu_fecupdate` date DEFAULT NULL,
  `empleado_id` int(11) DEFAULT NULL,
  `usu_observacion` varchar(250) DEFAULT NULL,
  `usu_estatus` enum('ACTIVO','INACTIVO') NOT NULL,
  `area_id` int(11) DEFAULT NULL,
  `usu_rol` enum('Secretario (a)','Administrador','Mesa de Partes','Jefe de Área','Especialista') NOT NULL,
  `empresa_id` int(11) DEFAULT 1,
  PRIMARY KEY (`usu_id`) USING BTREE,
  KEY `empleado_id` (`empleado_id`) USING BTREE,
  KEY `area_id` (`area_id`) USING BTREE,
  KEY `empresa_id` (`empresa_id`) USING BTREE,
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleado` (`empleado_id`),
  CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`area_id`) REFERENCES `area` (`area_cod`),
  CONSTRAINT `usuario_ibfk_3` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
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
CREATE PROCEDURE `SP_CARGAR_DNI_UL`(IN `ID` INT)
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_EXPEDIENTE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_CARGAR_EXPEDIENTE`(IN `ID` INT)
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE`(IN `NUMERO` VARCHAR(12), IN `DNI` VARCHAR(8))
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
ORDER BY doc_fecharegistro ASC ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE`(IN NUMERO VARCHAR(12))
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
CREATE PROCEDURE `SP_CARGAR_SELECT_AREA`()
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
CREATE PROCEDURE `SP_CARGAR_SELECT_AREA_SOLO`()
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
CREATE PROCEDURE `SP_CARGAR_SELECT_DNI`()
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
CREATE PROCEDURE `SP_CARGAR_SELECT_EMPLEADO`()
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
CREATE PROCEDURE `SP_CARGAR_SELECT_TIPO`()
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_CARGAR_TICKET` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_CARGAR_TICKET`(IN `NUMERO` VARCHAR(12))
SELECT
	documento.documento_id, 
	documento.doc_dniremitente, 
	CONCAT_WS(' ',documento.doc_nombreremitente,documento.doc_apepatremitente,documento.doc_apematremitente),
	MONTHNAME(documento.doc_fecharegistro) AS FECHA,
		date_format(doc_fecharegistro, "%d de %M de %Y, %H:%i") as fecha_formateada,
	documento.doc_expediente

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
CREATE PROCEDURE `SP_ELIMINAR_TRAMITE`(IN `ID` CHAR(12))
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_ANEXOS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_ANEXOS`(IN P_DOCUMENTO CHAR(12))
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_AREA`()
SELECT
	area.area_cod, 
	area.area_nombre,
	date_format(area_fecha_registro, "%d/%m/%Y %H:%i") as fecha_formateada,
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
CREATE PROCEDURE `SP_LISTAR_COMUNICADOS`()
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
CREATE PROCEDURE `SP_LISTAR_COMUNICADOS2`()
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
CREATE PROCEDURE `SP_LISTAR_EMPLEADO`()
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
CREATE PROCEDURE `SP_LISTAR_EMPRESA`()
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
CREATE PROCEDURE `SP_LISTAR_HORA`()
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
CREATE PROCEDURE `SP_LISTAR_NOTIFICACION_COMUNICADO`()
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_NOTIFICACION_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_NOTIFICACION_TRAMITE`(IN `IDAREA` INT)
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


		WHERE area_destino=IDAREA AND doc_estatus="PENDIENTE" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TIPO_DOCUMENTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TIPO_DOCUMENTO`()
SELECT
	tipo_documento.tipodocumento_id, 
	tipo_documento.tipodo_descripcion, 
	tipo_documento.tipodo_estado,
	tipo_documento.requisitos,
	DATE_FORMAT(tipodo_feregistro, "%d/%m/%Y %H:%i")as fecha_tipo,
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
CREATE PROCEDURE `SP_LISTAR_TOTAL_DOC ACPETADO`(IN `AREA` INT, IN `TIPOUSU` VARCHAR(255))
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
CREATE PROCEDURE `SP_LISTAR_TOTAL_DOC_PENDIENTE`()
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
CREATE PROCEDURE `SP_LISTAR_TOTAL_EMPLEADO`()
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
CREATE PROCEDURE `SP_LISTAR_TRAE_REQUISITO`(IN `ID` INT)
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
CREATE PROCEDURE `SP_LISTAR_TRAE_REQUISITO_DNI`(IN `DNI` CHAR(12))
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE`()
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
		ORDER BY doc_fecharegistro desc ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA`(IN IDUSUARIO INT)
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
    CASE WHEN documento.area_destino = v_area THEN 0
         WHEN EXISTS (SELECT 1 FROM movimiento ma WHERE ma.documento_id = documento.documento_id
                        AND ma.areadestino_id = v_area AND ma.mov_tipo = 'ATENCION') THEN 0
         ELSE 1 END AS es_copia,
    documento.doc_expediente,
    (SELECT DATE_FORMAT(MAX(m2.mov_recibido_fecha), '%d/%m/%Y %H:%i')
       FROM movimiento m2
      WHERE m2.documento_id = documento.documento_id
        AND m2.areadestino_id = v_area
        AND m2.mov_tipo = (CASE WHEN documento.area_destino = v_area THEN 'PRINCIPAL'
                                WHEN EXISTS (SELECT 1 FROM movimiento mb WHERE mb.documento_id = documento.documento_id
                                               AND mb.areadestino_id = v_area AND mb.mov_tipo = 'ATENCION') THEN 'ATENCION'
                                ELSE 'COPIA' END)) AS acuse_fecha,
    CASE WHEN documento.area_destino <> v_area
          AND EXISTS (SELECT 1 FROM movimiento mc WHERE mc.documento_id = documento.documento_id
                        AND mc.areadestino_id = v_area AND mc.mov_tipo = 'ATENCION') THEN 1 ELSE 0 END AS es_atencion,
    (SELECT md.mov_fecharegistro FROM movimiento md
      WHERE md.documento_id = documento.documento_id AND md.areadestino_id = v_area AND md.mov_tipo = 'ATENCION'
      ORDER BY md.movimiento_id DESC LIMIT 1) AS atencion_fecha,
    (SELECT md.mov_plazo_dias FROM movimiento md
      WHERE md.documento_id = documento.documento_id AND md.areadestino_id = v_area AND md.mov_tipo = 'ATENCION'
      ORDER BY md.movimiento_id DESC LIMIT 1) AS atencion_plazo,
    (SELECT DATE_FORMAT(md.mov_respuesta_fecha, '%d/%m/%Y %H:%i') FROM movimiento md
      WHERE md.documento_id = documento.documento_id AND md.areadestino_id = v_area AND md.mov_tipo = 'ATENCION'
      ORDER BY md.movimiento_id DESC LIMIT 1) AS atencion_respondida
  FROM documento
  INNER JOIN tipo_documento ON documento.tipodocumento_id = tipo_documento.tipodocumento_id
  INNER JOIN area AS origen ON documento.area_origen = origen.area_cod
  INNER JOIN area AS destino ON documento.area_destino = destino.area_cod
  WHERE documento.area_destino = v_area
     OR EXISTS (SELECT 1 FROM movimiento m
                 WHERE m.documento_id = documento.documento_id
                   AND m.areadestino_id = v_area
                   AND m.mov_tipo IN ('COPIA', 'ATENCION'))
  ORDER BY documento.doc_fecharegistro DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA1` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA1`(IN IDUSUARIO INT)
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
        documento.doc_expediente
    FROM documento
        INNER JOIN tipo_documento ON documento.tipodocumento_id = tipo_documento.tipodocumento_id
        INNER JOIN area AS origen ON documento.area_origen = origen.area_cod
        INNER JOIN area AS destino ON documento.area_destino = destino.area_cod
    WHERE documento.area_origen = v_area
       OR documento.documento_id IN (
            SELECT m.documento_id
              FROM movimiento m
             WHERE m.area_origen_id = v_area
               AND m.areadestino_id <> v_area
       )
    ORDER BY documento.doc_fecharegistro DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_BUSCAR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_BUSCAR`(IN `IDAREA` INT, IN `ESTADO` VARCHAR(20))
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
				ORDER BY doc_fecharegistro desc ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_ESTADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_ESTADO`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `ESTADO` VARCHAR(20))
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
	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_ESTADO_TA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_ESTADO_TA`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `ESTADO` VARCHAR(20), IN `IDAREA` INT)
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
	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_FECHAS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_FECHAS`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `IDAREA` INT)
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_FECHAS_TA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_FECHAS_TA`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `IDAREA` INT)
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_TIPO_DOC` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_TIPO_DOC`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `TIPO` INT)
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_AREA_TIPO_DOC_TA`(IN `FINICIO` DATETIME, IN `FFIN` DATETIME, IN `TIPO_DOC` INT, IN `IDAREA` INT)
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

	END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_ESTADO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_ESTADO`(IN `ESTADO` VARCHAR(20))
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
		ORDER BY doc_fecharegistro DESC ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_LISTAR_TRAMITE_SEGUIMIENTO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_LISTAR_TRAMITE_SEGUIMIENTO`(IN ID CHAR(12))
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
    (SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario) FROM usuario u LEFT JOIN empleado e ON e.empleado_id = u.empleado_id WHERE u.usu_id = movimiento.mov_recibido_usuario) AS recibido_por,
    movimiento.mov_tipo,
    movimiento.mov_plazo_dias,
    movimiento.mov_respuesta,
    DATE_FORMAT(movimiento.mov_respuesta_fecha, '%d/%m/%Y %H:%i') AS respuesta_fecha,
    movimiento.mov_respuesta_archivo,
    (SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario)
       FROM usuario u LEFT JOIN empleado e ON e.empleado_id = u.empleado_id
      WHERE u.usu_id = movimiento.mov_respuesta_usuario) AS respuesta_por
  FROM movimiento
  LEFT JOIN area ao ON movimiento.area_origen_id = ao.area_cod
  INNER JOIN area ad ON movimiento.areadestino_id = ad.area_cod
  WHERE movimiento.documento_id = ID
  ORDER BY movimiento.mov_fecharegistro ASC, movimiento.movimiento_id ASC;
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
CREATE PROCEDURE `SP_LISTAR_USUARIO`()
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
CREATE PROCEDURE `SP_MODIFICAR_AREA`(IN `ID` INT, IN `NAREA` VARCHAR(255), IN `ESTADO` VARCHAR(20))
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
CREATE PROCEDURE `SP_MODIFICAR_COMUNICADO`(IN `ID` INT, IN `TITULO` VARCHAR(255), IN `DESCRIPCION` TEXT, IN `ENLACE` VARCHAR(255))
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
CREATE PROCEDURE `SP_MODIFICAR_EMPLEADO`(IN `ID` INT, IN `NDOCUMENTO` CHAR(12), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(100), IN `APEMAT` VARCHAR(100), IN `FECHA` DATE, IN `MOVIL` CHAR(9), IN `MODALIDAD` VARCHAR(255), IN `DIRECCION` VARCHAR(255), IN `EMAIL` VARCHAR(255), IN `ESTADO` VARCHAR(20))
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
CREATE PROCEDURE `SP_MODIFICAR_EMPLEADO_FOTO`(IN `ID` INT, IN `RUTA` VARCHAR(255))
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
CREATE PROCEDURE `SP_MODIFICAR_EMPRESA`(IN `ID` INT, IN `NOMBRE` VARCHAR(250), IN `EMAIL` VARCHAR(250), IN `COD` VARCHAR(10), IN `TELEFONO` VARCHAR(20), IN `DIRECCION` VARCHAR(250))
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
CREATE PROCEDURE `SP_MODIFICAR_EMPRESA_FOTO`(IN `ID` INT, IN `RUTA` VARCHAR(255))
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
CREATE PROCEDURE `SP_MODIFICAR_HORARIO`(IN `ID` INT, IN `HORAINICIO` VARCHAR(20), IN `HORAFIN` VARCHAR(20))
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
CREATE PROCEDURE `SP_MODIFICAR_TIPO_DOCUMENTO`(IN `ID` INT, IN `NTIPO` VARCHAR(255), IN `ESTADO` VARCHAR(20), IN `REQUISITOS` VARCHAR(255))
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_MODIFICAR_TRAMITE_ESTATUS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_MODIFICAR_TRAMITE_ESTATUS`(IN ID CHAR(12), IN ESTATUS VARCHAR(50))
BEGIN
  UPDATE documento
     SET doc_estatus = ESTATUS,
         dias_pasados = 0
   WHERE documento_id = ID;
END ;;
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
CREATE PROCEDURE `SP_MODIFICAR_USUARIO`(IN `ID` VARCHAR(255), IN `IDEMPLEADO` INT, IN `IDAREA` INT, IN `ROL` VARCHAR(25))
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
CREATE PROCEDURE `SP_MODIFICAR_USUARIO_CONTRA`(IN `ID` INT, IN `CONTRA` VARCHAR(250))
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
CREATE PROCEDURE `SP_MODIFICAR_USUARIO_ESTATUS`(IN `ID` INT, IN `ESTATUS` VARCHAR(20))
UPDATE usuario SET
usuario.usu_estatus=ESTATUS
WHERE usuario.usu_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_RECHAZAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_RECHAZAR_TRAMITE`(IN ID CHAR(12), IN DESCRIPCION VARCHAR(255), IN LOCAL1 INT)
BEGIN
  UPDATE movimiento
     SET mov_estatus = 'RECHAZADO', mov_descripcion = DESCRIPCION
   WHERE documento_id = ID AND mov_estatus = 'PENDIENTE' AND mov_tipo = 'PRINCIPAL';

  UPDATE documento
     SET doc_estatus = 'RECHAZADO', dias_pasados = 0, area_destino = LOCAL1
   WHERE documento_id = ID AND doc_estatus = 'PENDIENTE';
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
CREATE PROCEDURE `SP_REGISTRAR_AREA`(IN `NAREA` VARCHAR(255))
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
CREATE PROCEDURE `SP_REGISTRAR_COMUNICADOS`(IN `TITULO` VARCHAR(255), IN `DESCRIPCION` TEXT, IN `IDUSU` INT, IN `ENLACE` VARCHAR(255))
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
CREATE PROCEDURE `SP_REGISTRAR_EMPLEADO`(IN `NDOCUMENTO` CHAR(12), IN `NOMBRE` VARCHAR(150), IN `APEPAT` VARCHAR(100), IN `APEMAT` VARCHAR(100), IN `FECHA` DATE, IN `MOVIL` CHAR(9), `MODALIDAD` VARCHAR(255), IN `DIRECCION` VARCHAR(255), IN `EMAIL` VARCHAR(255))
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
CREATE PROCEDURE `SP_REGISTRAR_TIPO_DOCUMENTO`(IN `TIPODOC` VARCHAR(255), IN `REQUISITOS` VARCHAR(255))
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_REGISTRAR_TRAMITE`(
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
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE_DERIVAR` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_REGISTRAR_TRAMITE_DERIVAR`(
  IN ID CHAR(15), IN ORIGEN INT(11), IN DESTINO INT(11), IN DESCRIPCION VARCHAR(255),
  IN IDUSUARIO INT(11), IN RUTA VARCHAR(255), IN TIPO VARCHAR(255), IN ACCION VARCHAR(255)
)
BEGIN
  DECLARE v_mov INT DEFAULT NULL;

  SELECT m.movimiento_id INTO v_mov
    FROM movimiento m
    INNER JOIN documento d ON d.documento_id = m.documento_id
   WHERE m.documento_id = ID
     AND m.mov_tipo = 'PRINCIPAL'
     AND m.mov_estatus IN ('PENDIENTE', 'ACEPTADO')
     AND m.areadestino_id = d.area_destino
   ORDER BY m.movimiento_id DESC
   LIMIT 1;

  IF v_mov IS NULL THEN
    SELECT movimiento_id INTO v_mov
      FROM movimiento
     WHERE documento_id = ID
       AND mov_tipo = 'PRINCIPAL'
       AND mov_estatus IN ('PENDIENTE', 'ACEPTADO')
     ORDER BY movimiento_id DESC
     LIMIT 1;
  END IF;

  IF TIPO = 'FINALIZAR' THEN
    UPDATE movimiento SET mov_estatus = 'DERIVADO' WHERE movimiento_id = v_mov;
    UPDATE documento SET area_origen = ORIGEN, area_destino = ORIGEN, doc_estatus = 'FINALIZADO', dias_pasados = 0
     WHERE documento_id = ID;
    INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_fecharegistro,
                            mov_descripcion, mov_estatus, usuario_id, mov_archivo, mov_acciones, mov_tipo)
    VALUES (ID, ORIGEN, ORIGEN, NOW(), DESCRIPCION, 'FINALIZADO', IDUSUARIO, RUTA, ACCION, 'PRINCIPAL');
  ELSE
    UPDATE movimiento SET mov_estatus = 'DERIVADO' WHERE movimiento_id = v_mov;
    UPDATE documento SET area_origen = ORIGEN, area_destino = DESTINO, doc_estatus = 'PENDIENTE', dias_pasados = 0
     WHERE documento_id = ID;
    INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_fecharegistro,
                            mov_descripcion, mov_estatus, usuario_id, mov_archivo, mov_acciones, mov_tipo)
    VALUES (ID, ORIGEN, DESTINO, NOW(), DESCRIPCION, 'PENDIENTE', IDUSUARIO, RUTA, ACCION, 'PRINCIPAL');
  END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE_EXTERNO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_REGISTRAR_TRAMITE_EXTERNO`(
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
  VALUES(v_cod,1,1,NOW(),ASUNTO,'PENDIENTE',NULL,RUTA,'--REVISAR--');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_REGISTRAR_TRAMITE_UL` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_REGISTRAR_TRAMITE_UL`(
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
CREATE PROCEDURE `SP_REGISTRAR_USUARIO`(IN `USU` VARCHAR(255), IN `CONTRA` VARCHAR(255), IN `IDEMPLEADO` INT, IN `IDAREA` INT, IN `ROL` VARCHAR(25))
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
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_SIGUIENTE_CORRELATIVO` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_SIGUIENTE_CORRELATIVO`(
  IN  p_anio INT,
  IN  p_tipo VARCHAR(10),
  OUT p_num  INT
)
BEGIN
  
  
  INSERT INTO correlativo (corr_anio, corr_tipo, corr_ultimo)
  VALUES (p_anio, p_tipo, LAST_INSERT_ID(1))
  ON DUPLICATE KEY UPDATE corr_ultimo = LAST_INSERT_ID(corr_ultimo + 1);
  SET p_num = LAST_INSERT_ID();
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
CREATE PROCEDURE `SP_TOTAL_DOCUMENTOS_ACEPTADOS`()
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
CREATE PROCEDURE `SP_TOTAL_DOCUMENTOS_ACEPTADOS_AREA`(IN `IDAREA` INT)
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
CREATE PROCEDURE `SP_TOTAL_DOCUMENTOS_FINALIZADO`()
SELECT count(documento_id)as totaldocpen FROM documento where doc_estatus="FINALIZADO" ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TOTAL_DOCUMENTOS_PENDIENTES2` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_TOTAL_DOCUMENTOS_PENDIENTES2`(IN `IDUSUARIO` INT)
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
CREATE PROCEDURE `SP_TRAER_DATOS`()
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
CREATE PROCEDURE `SP_TRAER_DATOS2`(IN `ID` INT)
SELECT id_horario,hora_inicio,hora_fin
FROM horario
WHERE id_horario=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS_EXPEDIENTE` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE`(IN `ID` CHAR(12))
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
	documento.documento_id=ID ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SP_TRAER_DATOS_EXPEDIENTE_ADMIN` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SP_TRAER_DATOS_EXPEDIENTE_ADMIN`()
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
CREATE PROCEDURE `SP_TRAER_WIDGET`(IN `IDAREA` INT)
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
CREATE PROCEDURE `SP_VERIFICAR_USUARIO`(IN `USU` VARCHAR(255))
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
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


SET FOREIGN_KEY_CHECKS = 1;
