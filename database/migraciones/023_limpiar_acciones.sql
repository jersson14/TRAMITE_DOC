-- ============================================================================
-- 023 - Quitar la basura "ON"/"ONON" de las acciones del trámite
--
-- Causa: las pantallas de registro armaban el campo de acciones recorriendo
-- document.querySelectorAll('input[type=checkbox]'), es decir TODAS las casillas
-- de la página. Entraban también la de términos (checkboxSuccess1), la de
-- "Tiene el documento firmado digitalmente" (checkboxSuccess2) y la de trámite
-- externo (chk_externo). Ninguna tiene atributo value, así que el navegador les
-- da "on", el controlador lo pasa por strtoupper y se guardaba, por ejemplo:
--
--     "-1. ACCIÓN--2. TRAMITAR-ONON"
--
-- y en los trámites donde no se marcó ninguna acción real, simplemente "ONON",
-- que es lo que la pantalla mostraba como "Acciones a realizar: ONON".
--
-- Ya corregido en las 4 vistas acotando el selector a .checkbox-card-container.
-- Esta migración limpia lo ya guardado.
--
-- Es seguro recortar un "ON" final porque toda acción real termina en "-"
-- ("-1. ACCIÓN-", "4. -V° B°-", "--REVISAR--"): ninguna acabaría en ON.
-- Un trámite que queda con el campo vacío es la verdad: no se marcó ninguna.
--
-- Respaldo previo de las dos columnas en storage/backups/acciones_antes_023.csv
-- ============================================================================

UPDATE documento
   SET acciones = REGEXP_REPLACE(acciones, '(ON)+$', '')
 WHERE acciones REGEXP '(ON)+$';

UPDATE movimiento
   SET mov_acciones = REGEXP_REPLACE(mov_acciones, '(ON)+$', '')
 WHERE mov_acciones REGEXP '(ON)+$';
