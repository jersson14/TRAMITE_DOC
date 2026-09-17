-- ============================================================================
-- 015 - Horario de recepción de documentos
--
-- La Mesa de Partes Virtual recibe documentos a cualquier hora, pero los que
-- llegan fuera del horario de atención (de noche, fines de semana o feriados)
-- se consideran presentados el siguiente día hábil, a la hora de apertura. Los
-- plazos se cuentan desde esa fecha.
--
-- empresa.emp_hora_inicio / emp_hora_fin: horario de atención (lunes a viernes,
--   sin feriados de la tabla feriado). Lo edita el administrador.
-- documento.doc_fecharecepcion: fecha en que el documento se considera
--   presentado. NULL en los trámites anteriores (se usa doc_fecharegistro).
--
-- Columnas al final de cada tabla: ningún procedimiento usa SELECT * ni lee
-- estas tablas por posición.
-- ============================================================================

ALTER TABLE empresa
  ADD COLUMN IF NOT EXISTS emp_hora_inicio TIME NOT NULL DEFAULT '08:00:00',
  ADD COLUMN IF NOT EXISTS emp_hora_fin    TIME NOT NULL DEFAULT '16:30:00';

ALTER TABLE documento
  ADD COLUMN IF NOT EXISTS doc_fecharecepcion DATETIME NULL
    COMMENT 'Fecha en que se considera presentado (horario de atención)';
