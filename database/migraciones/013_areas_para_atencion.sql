-- ============================================================================
-- 013 - Derivación a varias áreas: área responsable + áreas para atención
--
-- El trámite sigue teniendo UNA área responsable (acepta, deriva y finaliza).
-- Al derivar se puede pedir atención a otras áreas: cada una recibe su propio
-- envío con plazo, da acuse y registra su respuesta (texto y archivo opcional).
-- La responsable ve esas respuestas antes de finalizar.
--
-- Hasta ahora el tipo de envío se deducía del texto "COPIA - " de la
-- descripción. Con un tercer tipo eso ya no alcanza, así que movimiento gana
-- mov_tipo (PRINCIPAL, COPIA, ATENCION) y los procedimientos que buscan el envío
-- principal lo usan. Las columnas nuevas de los listados van AL FINAL (ver 007).
-- ============================================================================

ALTER TABLE movimiento
  ADD COLUMN IF NOT EXISTS mov_tipo ENUM('PRINCIPAL','COPIA','ATENCION') NOT NULL DEFAULT 'PRINCIPAL',
  ADD COLUMN IF NOT EXISTS mov_plazo_dias INT NULL,
  ADD COLUMN IF NOT EXISTS mov_respuesta TEXT NULL,
  ADD COLUMN IF NOT EXISTS mov_respuesta_fecha DATETIME NULL,
  ADD COLUMN IF NOT EXISTS mov_respuesta_usuario INT NULL,
  ADD COLUMN IF NOT EXISTS mov_respuesta_archivo VARCHAR(255) NULL,
  MODIFY COLUMN mov_estatus ENUM('PENDIENTE','CONFORME','INCOFORME','ACEPTADO','DERIVADO','FINALIZADO','RECHAZADO','ATENDIDO') DEFAULT NULL;

-- Las copias existentes se reconocían por su descripción
UPDATE movimiento SET mov_tipo = 'COPIA'
 WHERE mov_descripcion LIKE 'COPIA - %' AND mov_tipo = 'PRINCIPAL';

DELIMITER $$

-- ----------------------------------------------------------------------------
-- Derivar: cierra el envío PRINCIPAL al área donde está el trámite
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_REGISTRAR_TRAMITE_DERIVAR$$
CREATE PROCEDURE SP_REGISTRAR_TRAMITE_DERIVAR(
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
END$$

-- ----------------------------------------------------------------------------
-- Rechazar: solo el envío principal
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_RECHAZAR_TRAMITE$$
CREATE PROCEDURE SP_RECHAZAR_TRAMITE(IN ID CHAR(12), IN DESCRIPCION VARCHAR(255), IN LOCAL1 INT)
BEGIN
  UPDATE movimiento
     SET mov_estatus = 'RECHAZADO', mov_descripcion = DESCRIPCION
   WHERE documento_id = ID AND mov_estatus = 'PENDIENTE' AND mov_tipo = 'PRINCIPAL';

  UPDATE documento
     SET doc_estatus = 'RECHAZADO', dias_pasados = 0, area_destino = LOCAL1
   WHERE documento_id = ID AND doc_estatus = 'PENDIENTE';
END$$

-- ----------------------------------------------------------------------------
-- Recibidos: destino + copias + atenciones (mismo orden de columnas; nuevas al final)
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_LISTAR_TRAMITE_AREA$$
CREATE PROCEDURE SP_LISTAR_TRAMITE_AREA(IN IDUSUARIO INT)
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
END$$

-- ----------------------------------------------------------------------------
-- Historial con tipo de envío y respuestas de atención
-- ----------------------------------------------------------------------------
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
END$$

DELIMITER ;
