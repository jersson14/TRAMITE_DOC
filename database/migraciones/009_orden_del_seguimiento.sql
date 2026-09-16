-- ============================================================================
-- 009 - Orden estable en la línea de tiempo del rastreo
--
-- El envío principal y sus copias se registran en el mismo segundo, y el detalle
-- del seguimiento solo ordenaba por fecha: con el empate, las copias podían salir
-- antes que el envío principal. Se desempata por movimiento_id, que respeta el
-- orden real en que se registraron.
--
-- Primero se crea con un nombre temporal para validar: si esa creación fallara,
-- el cliente se detiene antes de borrar el procedimiento real.
-- ============================================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS SP_VALIDAR_009$$
CREATE PROCEDURE SP_VALIDAR_009(IN NUMERO VARCHAR(12))
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
    ORDER BY movimiento.mov_fecharegistro ASC, movimiento.movimiento_id ASC;
END$$
DROP PROCEDURE IF EXISTS SP_VALIDAR_009$$

DROP PROCEDURE IF EXISTS SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE$$
CREATE PROCEDURE SP_CARGAR_SEGUIMIENTO_TRAMITE_DETALLE(IN NUMERO VARCHAR(12))
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
    ORDER BY movimiento.mov_fecharegistro ASC, movimiento.movimiento_id ASC;
END$$

DELIMITER ;
