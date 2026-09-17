-- ============================================================================
-- 017 - Tipo de feriado
--
-- La pantalla de feriados (view/feriado) distingue feriados nacionales,
-- regionales (Apurímac) y días no laborables decretados por el Gobierno. Todos
-- se descuentan igual al contar plazos; el tipo es para que el administrador
-- sepa de dónde sale cada fecha. Columna al final: lib/Plazos.php solo lee fecha.
-- ============================================================================

ALTER TABLE feriado
  ADD COLUMN IF NOT EXISTS tipo ENUM('NACIONAL', 'REGIONAL', 'NO_LABORABLE') NOT NULL DEFAULT 'NACIONAL';
