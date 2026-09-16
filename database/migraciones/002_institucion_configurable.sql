-- =====================================================================
-- 002 - Institución configurable (marca por entidad)
-- Agrega la sigla y el color de marca de la institución. El nombre, el
-- logo y los datos de contacto ya existían en la tabla empresa.
-- =====================================================================
ALTER TABLE empresa
  ADD COLUMN IF NOT EXISTS emp_sigla VARCHAR(60) NULL AFTER emp_razon,
  ADD COLUMN IF NOT EXISTS emp_color CHAR(7) NOT NULL DEFAULT '#1F2358' AFTER emp_logo;

UPDATE empresa SET emp_sigla = 'DIRESA Apurímac'
WHERE (emp_sigla IS NULL OR emp_sigla = '') AND emp_razon LIKE '%DIRESA%';