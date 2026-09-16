-- ============================================================================
-- Revertir 005 - Quita los anexos.
-- Ojo: borra el registro de los archivos adicionales. Los archivos en disco
-- (controller/tramite/documentos y controller/tramite_area/documentos) no se
-- tocan, para poder recuperarlos si hiciera falta.
-- ============================================================================

DROP PROCEDURE IF EXISTS SP_LISTAR_ANEXOS;
DROP TABLE IF EXISTS documento_anexo;
