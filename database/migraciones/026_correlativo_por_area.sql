-- ============================================================================
-- 026 - Numeración automática de documentos internos por área
--
-- El expediente ya tenía su número (EXP-2026-000048), pero el número del
-- documento interno -- "OFICIO N° 001-2026-DIRESA-APURÍMAC/OGA" -- lo escribía el
-- usuario a mano, llevando la cuenta en un cuaderno o en su memoria. Eso produce
-- saltos y números repetidos.
--
-- Ahora cada área numera sola, con una secuencia propia por TIPO de documento y
-- por AÑO: los oficios de Contabilidad tienen su correlativo, los memorándums de
-- Contabilidad el suyo, y los oficios de Patrimonio otro distinto.
--
-- Se reutiliza la tabla correlativo y SP_SIGUIENTE_CORRELATIVO, que ya numeraban
-- el expediente de forma atómica (sin choques entre dos registros simultáneos).
-- La clave es "A<área>-T<tipo>" con el año aparte, que cabe en corr_tipo(10).
--
-- El número es automático pero editable: si el documento ya venía numerado en
-- papel, el usuario lo escribe y el sistema no consume un número de la secuencia.
--
-- Solo aplica a documentos INTERNOS. Uno externo ya trae su propio número, el
-- que le puso quien lo envía.
-- ============================================================================

SET NAMES utf8mb4;

-- Un número completo como "001-2026-DIRESA-APURÍMAC/RRHH" no cabía en 15 caracteres.
-- Solo se amplía la columna: los procedimientos de registro siguen recibiendo 15,
-- y el número completo se guarda desde PHP justo después de insertar.
ALTER TABLE documento
  MODIFY COLUMN doc_nrodocumento VARCHAR(80) NOT NULL;

-- Sigla del área para el número (OGA, RRHH, MP...). Si está vacía, el sistema la
-- deduce del nombre; el administrador la corrige en el mantenimiento de Áreas.
SET @existe := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'area' AND COLUMN_NAME = 'area_sigla'
);
SET @sql := IF(@existe = 0,
  "ALTER TABLE area ADD COLUMN area_sigla VARCHAR(20) NULL COMMENT 'Sigla para numerar documentos: OGA, RRHH, MP...'",
  "DO 0");
PREPARE p FROM @sql; EXECUTE p; DEALLOCATE PREPARE p;
