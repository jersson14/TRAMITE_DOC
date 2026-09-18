-- ============================================================================
-- 029 - Reparar los nombres de los feriados
--
-- La migración 012 se aplicó con el cliente mysql de Windows sin indicarle que
-- el archivo estaba en UTF-8. El cliente lo leyó como cp850 y grabó los nombres
-- con tildes y eñes dañados. La pantalla de Feriados mostraba, por ejemplo:
--
--     "Año Nuevo"          como  "A" + dos caracteres de dibujo + "o Nuevo"
--     "Día del Trabajo"    como  "D" + dos caracteres de dibujo + "a del Trabajo"
--
-- Afectaba a 14 filas: las 7 de cada año (2025 y 2026) que llevan tilde o eñe.
--
-- La reparación deshace la doble codificación: vuelve a pasar el texto a cp850
-- (recupera los bytes UTF-8 originales) y lo lee otra vez como UTF-8.
--
-- Solo toca las filas dañadas: las que tienen el carácter U+251C, la huella de
-- una tilde o eñe mal leída. Se escribe con UNHEX para que este archivo no tenga
-- caracteres especiales y no pueda dañarse de la misma forma al aplicarlo.
-- Se puede correr más de una vez: si no hay filas dañadas, no hace nada.
--
-- Para evitar que vuelva a pasar, aplique siempre las migraciones con:
--     mysql --default-character-set=utf8mb4 ...
-- ============================================================================

SET NAMES utf8mb4;

UPDATE feriado
   SET descripcion = CONVERT(CAST(CONVERT(descripcion USING cp850) AS BINARY) USING utf8mb4)
 WHERE descripcion LIKE CONCAT('%', CONVERT(UNHEX('E2949C') USING utf8mb4), '%') COLLATE utf8mb4_unicode_ci;
