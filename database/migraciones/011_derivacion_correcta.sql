-- ============================================================================
-- 011 - La derivación cierra el envío correcto
--
-- DEFECTO 1: SP_REGISTRAR_TRAMITE_DERIVAR marcaba como DERIVADO "el último
-- movimiento PENDIENTE" del trámite, sin mirar a qué área iba. Las copias se
-- registran después del envío principal, así que al derivar se marcaba la COPIA
-- de otra área y el envío principal quedaba PENDIENTE para siempre.
-- Ahora cierra el último envío principal (no copia) dirigido al área donde está
-- el trámite, esté PENDIENTE o ACEPTADO (aceptar ya marca el envío como ACEPTADO).
--
-- DEFECTO 2 (corregido en model_tramite_area.php): al derivar con copias, cada
-- copia llamaba a este mismo procedimiento, que además cambiaba el destino del
-- trámite al área de la copia.
--
-- REPARACIÓN DE DATOS, solo donde hay evidencia:
--   A. Registros con copia (D0000028, D0000030, D0000031, D0000035, D0000040):
--      la copia quedó DERIVADO y el principal PENDIENTE, y existe un envío
--      posterior que sale del área del principal (prueba de que fue el principal
--      el que se derivó). Se intercambian los estados.
--   B. Derivaciones con copia (D0000019, D0000034): el trámite quedó en el área de
--      la copia. Se devuelve al área del envío principal y ese envío vuelve a
--      PENDIENTE, porque nadie lo atendió.
-- Respaldo previo en storage/backups.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- A. Copia marcada en lugar del principal
-- ----------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS reparar_a;
CREATE TEMPORARY TABLE reparar_a AS
SELECT p.movimiento_id AS principal, c.movimiento_id AS copia
  FROM movimiento c
  JOIN movimiento p
    ON p.documento_id = c.documento_id
   AND p.mov_descripcion NOT LIKE 'COPIA - %'
   AND p.mov_estatus = 'PENDIENTE'
   AND ABS(TIMESTAMPDIFF(SECOND, p.mov_fecharegistro, c.mov_fecharegistro)) <= 5
 WHERE c.mov_descripcion LIKE 'COPIA - %'
   AND c.mov_estatus = 'DERIVADO'
   AND EXISTS (SELECT 1 FROM movimiento d
                WHERE d.documento_id = p.documento_id
                  AND d.movimiento_id > c.movimiento_id
                  AND d.area_origen_id = p.areadestino_id
                  AND d.mov_descripcion NOT LIKE 'COPIA - %');

UPDATE movimiento m JOIN reparar_a r ON r.principal = m.movimiento_id SET m.mov_estatus = 'DERIVADO';
UPDATE movimiento m JOIN reparar_a r ON r.copia = m.movimiento_id SET m.mov_estatus = 'PENDIENTE';
DROP TEMPORARY TABLE reparar_a;

-- ----------------------------------------------------------------------------
-- B. Trámite movido al área de una copia durante una derivación
-- ----------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS reparar_b;
CREATE TEMPORARY TABLE reparar_b AS
SELECT d.documento_id, p.movimiento_id AS principal, p.areadestino_id AS area_correcta
  FROM documento d
  JOIN movimiento p ON p.movimiento_id = (
        SELECT m.movimiento_id FROM movimiento m
         WHERE m.documento_id = d.documento_id AND m.mov_descripcion NOT LIKE 'COPIA - %'
         ORDER BY m.movimiento_id DESC LIMIT 1)
  JOIN movimiento c
    ON c.documento_id = d.documento_id
   AND c.mov_descripcion LIKE 'COPIA - %'
   AND c.movimiento_id > p.movimiento_id
   AND c.areadestino_id = d.area_destino
   AND ABS(TIMESTAMPDIFF(SECOND, p.mov_fecharegistro, c.mov_fecharegistro)) <= 5
 WHERE d.area_destino <> p.areadestino_id
   AND p.mov_estatus = 'DERIVADO'
   AND d.doc_estatus = 'PENDIENTE';

UPDATE documento d JOIN reparar_b r ON r.documento_id = d.documento_id SET d.area_destino = r.area_correcta;
UPDATE movimiento m JOIN reparar_b r ON r.principal = m.movimiento_id SET m.mov_estatus = 'PENDIENTE';
DROP TEMPORARY TABLE reparar_b;

DELIMITER $$

-- Validación: si esta versión no compila, el cliente se detiene aquí y el
-- procedimiento real no se toca.
DROP PROCEDURE IF EXISTS SP_VALIDAR_011$$
CREATE PROCEDURE SP_VALIDAR_011(IN ID CHAR(15))
BEGIN
  DECLARE v_mov INT;
  SELECT m.movimiento_id INTO v_mov FROM movimiento m
   INNER JOIN documento d ON d.documento_id = m.documento_id
   WHERE m.documento_id = ID AND m.mov_descripcion NOT LIKE 'COPIA - %'
     AND m.mov_estatus IN ('PENDIENTE', 'ACEPTADO') AND m.areadestino_id = d.area_destino
   ORDER BY m.movimiento_id DESC LIMIT 1;
END$$
DROP PROCEDURE IF EXISTS SP_VALIDAR_011$$

DROP PROCEDURE IF EXISTS SP_REGISTRAR_TRAMITE_DERIVAR$$
CREATE PROCEDURE SP_REGISTRAR_TRAMITE_DERIVAR(
  IN ID CHAR(15), IN ORIGEN INT(11), IN DESTINO INT(11), IN DESCRIPCION VARCHAR(255),
  IN IDUSUARIO INT(11), IN RUTA VARCHAR(255), IN TIPO VARCHAR(255), IN ACCION VARCHAR(255)
)
BEGIN
  DECLARE v_mov INT DEFAULT NULL;

  -- El envío que se cierra es el principal (no una copia) que llegó al área
  -- donde está el trámite ahora.
  SELECT m.movimiento_id INTO v_mov
    FROM movimiento m
    INNER JOIN documento d ON d.documento_id = m.documento_id
   WHERE m.documento_id = ID
     AND m.mov_descripcion NOT LIKE 'COPIA - %'
     AND m.mov_estatus IN ('PENDIENTE', 'ACEPTADO')
     AND m.areadestino_id = d.area_destino
   ORDER BY m.movimiento_id DESC
   LIMIT 1;

  -- Datos antiguos incoherentes: el último envío principal abierto, sea cual sea su área.
  IF v_mov IS NULL THEN
    SELECT movimiento_id INTO v_mov
      FROM movimiento
     WHERE documento_id = ID
       AND mov_descripcion NOT LIKE 'COPIA - %'
       AND mov_estatus IN ('PENDIENTE', 'ACEPTADO')
     ORDER BY movimiento_id DESC
     LIMIT 1;
  END IF;

  IF TIPO = 'FINALIZAR' THEN
    UPDATE movimiento SET mov_estatus = 'DERIVADO' WHERE movimiento_id = v_mov;

    UPDATE documento SET
      area_origen = ORIGEN,
      area_destino = ORIGEN,
      doc_estatus = 'FINALIZADO',
      dias_pasados = 0
    WHERE documento_id = ID;

    INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_fecharegistro,
                            mov_descripcion, mov_estatus, usuario_id, mov_archivo, mov_acciones)
    VALUES (ID, ORIGEN, ORIGEN, NOW(), DESCRIPCION, 'FINALIZADO', IDUSUARIO, RUTA, ACCION);
  ELSE
    UPDATE movimiento SET mov_estatus = 'DERIVADO' WHERE movimiento_id = v_mov;

    UPDATE documento SET
      area_origen = ORIGEN,
      area_destino = DESTINO,
      doc_estatus = 'PENDIENTE',
      dias_pasados = 0
    WHERE documento_id = ID;

    INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_fecharegistro,
                            mov_descripcion, mov_estatus, usuario_id, mov_archivo, mov_acciones)
    VALUES (ID, ORIGEN, DESTINO, NOW(), DESCRIPCION, 'PENDIENTE', IDUSUARIO, RUTA, ACCION);
  END IF;
END$$

DELIMITER ;
