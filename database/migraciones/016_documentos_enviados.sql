-- ============================================================================
-- 016 - "Documentos Enviados" muestra lo que el área envió
--
-- SP_LISTAR_TRAMITE_AREA1 filtraba movimiento por area_origen / area_destino,
-- columnas que no existen en movimiento (se llaman area_origen_id y
-- areadestino_id). MariaDB las resolvía contra la tabla documento de la consulta
-- externa, así que esa parte no hacía nada: la bandeja mostraba los trámites
-- registrados por el área o que estaban en ella en ese momento (es decir,
-- también recibidos) y omitía los que el área derivó a otra.
--
-- Ahora: trámites registrados por el área, o con algún envío (principal, copia
-- o atención) cuyo origen es el área y cuyo destino es otra área.
-- Mismas columnas y en el mismo orden que antes (doc_expediente al final).
-- ============================================================================

DROP PROCEDURE IF EXISTS SP_LISTAR_TRAMITE_AREA1;

DELIMITER $$
CREATE PROCEDURE SP_LISTAR_TRAMITE_AREA1(IN IDUSUARIO INT)
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
END$$
DELIMITER ;
