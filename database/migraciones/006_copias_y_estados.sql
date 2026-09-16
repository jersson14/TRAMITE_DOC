-- ============================================================================
-- 006 - Copias visibles para las áreas, anexos por envío y estados corregidos
--
-- 1. ACEPTAR cambiaba varios trámites a la vez. El procedimiento buscaba por
--    doc_nrodocumento (el número que trae el papel del ciudadano), que se repite
--    entre trámites: al aceptar el Nº 334 se aceptaban dos trámites distintos.
--    Ahora busca por el código del trámite.
--
-- 2. RECHAZAR rechazaba TODOS los trámites pendientes del sistema. Recibía el
--    código como INT; 'D0000041' convertido a número vale 0 y la comparación
--    documento_id = 0 era verdadera para todos. Probado en transacción: rechazar
--    un solo trámite rechazaba los 17 pendientes y los movía a Mesa de Partes.
--
-- 3. Las copias no llegaban a las áreas: la bandeja de recibidos solo mostraba
--    los trámites cuyo destino principal era el área. Ahora incluye los que le
--    llegaron en copia, marcados con es_copia = 1 (para conocimiento: esa área
--    no acepta ni rechaza el trámite de otra).
--
-- 4. Las copias se registraban sin archivo (mov_archivo NULL), así que el botón
--    de la copia no abría nada. Se completan con el archivo del envío original.
--
-- 5. Los anexos pasan a vincularse con cada envío (movimiento), para mostrarse
--    junto al documento en el historial de movimientos.
--
-- Reversión parcial: ver 006_copias_y_estados_revertir.sql
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 4. Copias sin archivo: toman el del movimiento original del mismo envío
--    (mismo trámite, registrado en el mismo instante, que no es copia).
-- ----------------------------------------------------------------------------
UPDATE movimiento AS copia
INNER JOIN movimiento AS original
        ON original.documento_id = copia.documento_id
       AND original.mov_descripcion NOT LIKE 'COPIA - %'
       AND original.mov_archivo IS NOT NULL
       AND original.mov_archivo <> ''
       AND ABS(TIMESTAMPDIFF(SECOND, original.mov_fecharegistro, copia.mov_fecharegistro)) <= 5
SET copia.mov_archivo = original.mov_archivo
WHERE copia.mov_descripcion LIKE 'COPIA - %'
  AND (copia.mov_archivo IS NULL OR copia.mov_archivo = '');

-- ----------------------------------------------------------------------------
-- 5. Anexos por envío
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS movimiento_anexo (
  movimiento_id INT    NOT NULL,
  anexo_id      BIGINT NOT NULL,
  PRIMARY KEY (movimiento_id, anexo_id),
  KEY idx_movanexo_anexo (anexo_id),
  CONSTRAINT fk_movanexo_movimiento
    FOREIGN KEY (movimiento_id) REFERENCES movimiento (movimiento_id) ON DELETE CASCADE,
  CONSTRAINT fk_movanexo_anexo
    FOREIGN KEY (anexo_id) REFERENCES documento_anexo (anexo_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anexos ya subidos: se vinculan con los movimientos creados en el mismo envío.
INSERT IGNORE INTO movimiento_anexo (movimiento_id, anexo_id)
SELECT m.movimiento_id, a.anexo_id
FROM documento_anexo a
INNER JOIN movimiento m
        ON m.documento_id = a.documento_id
       AND ABS(TIMESTAMPDIFF(SECOND, m.mov_fecharegistro, a.anexo_fecha)) <= 5;

DELIMITER $$

-- ----------------------------------------------------------------------------
-- 1. Aceptar: por código de trámite
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_MODIFICAR_TRAMITE_ESTATUS$$
CREATE PROCEDURE SP_MODIFICAR_TRAMITE_ESTATUS(IN ID CHAR(12), IN ESTATUS VARCHAR(50))
BEGIN
  UPDATE documento
     SET doc_estatus = ESTATUS,
         dias_pasados = 0
   WHERE documento_id = ID;
END$$

-- ----------------------------------------------------------------------------
-- 2. Rechazar: por código de trámite, sin tocar las copias
--    (su descripción "COPIA - ..." es la que las identifica como copia)
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_RECHAZAR_TRAMITE$$
CREATE PROCEDURE SP_RECHAZAR_TRAMITE(IN ID CHAR(12), IN DESCRIPCION VARCHAR(255), IN LOCAL1 INT)
BEGIN
  UPDATE movimiento
     SET mov_estatus = 'RECHAZADO',
         mov_descripcion = DESCRIPCION
   WHERE documento_id = ID
     AND mov_estatus = 'PENDIENTE'
     AND mov_descripcion NOT LIKE 'COPIA - %';

  UPDATE documento
     SET doc_estatus = 'RECHAZADO',
         dias_pasados = 0,
         area_destino = LOCAL1
   WHERE documento_id = ID
     AND doc_estatus = 'PENDIENTE';
END$$

-- ----------------------------------------------------------------------------
-- 3. Bandeja de recibidos del área: destino principal + copias
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_LISTAR_TRAMITE_AREA$$
CREATE PROCEDURE SP_LISTAR_TRAMITE_AREA(IN IDUSUARIO INT)
BEGIN
  DECLARE v_area INT;
  SELECT area_id INTO v_area FROM usuario WHERE usu_id = IDUSUARIO;

  SELECT
    documento.documento_id,
    documento.doc_expediente,
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
    CASE WHEN documento.area_destino = v_area THEN 0 ELSE 1 END AS es_copia
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

-- ----------------------------------------------------------------------------
-- 5. Historial de movimientos con los anexos de cada envío.
--    Los anexos viajan en una sola columna: ruta y nombre separados por un
--    tabulador, y cada anexo en su propia línea. Los nombres se limpian al
--    subir para que nunca contengan esos caracteres.
-- ----------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS SP_LISTAR_TRAMITE_SEGUIMIENTO$$
CREATE PROCEDURE SP_LISTAR_TRAMITE_SEGUIMIENTO(IN ID CHAR(12))
BEGIN
  SET SESSION group_concat_max_len = 65535;

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

DELIMITER ;
