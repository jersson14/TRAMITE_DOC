-- ============================================================================
-- 022 - La procedencia se deduce del REMITENTE, no de la pantalla
--
-- La migración 021 dejó los 32 trámites existentes como INTERNO. Eso estaba mal:
-- no siempre inicia un externo, pero tampoco siempre un interno. Al revisar los
-- datos, 23 de los 32 tienen como remitente a alguien con cuenta de usuario
-- (personal de la entidad) y el resto a personas o entidades de fuera.
--
-- Regla: el remitente separa los dos casos.
--   - Remitente CON cuenta de usuario Y a nombre propio -> INTERNO. El documento
--     lo produce la entidad, así que su responsable lo firma en el sistema.
--   - Remitente que es persona jurídica (RUC o razón social) -> EXTERNO siempre:
--     viene de otra organización, por más que quien lo presente sea empleado.
--   - Remitente SIN cuenta -> EXTERNO. Lo trae un ciudadano u otra entidad: no se
--     firma aquí, solo se verifica la firma que ya traiga.
--
-- Es el mismo criterio que usan las pantallas de registro, cuyo selector de
-- "remitente interno" se llena con usuario JOIN empleado (SP_CARGAR_DNI_UL).
--
-- En el código la deducción vive en Modelo_Firma::Procedencia_De_Registro(), que
-- además deja que la casilla "Es trámite externo" fuerce EXTERNO (un empleado
-- puede presentar un documento a título personal), pero nunca al revés.
-- ============================================================================

UPDATE documento d
SET d.doc_procedencia = CASE
    -- Una persona jurídica es otra organización (empresa u otra entidad): el
    -- documento no lo redactó esta entidad, aunque quien lo presenta comparta DNI
    -- con un empleado. Manda sobre la regla del remitente.
    WHEN TRIM(COALESCE(d.doc_ruc, '')) <> ''
      OR TRIM(COALESCE(d.doc_empresa, '')) <> ''
      OR UPPER(COALESCE(d.doc_representacion, '')) LIKE '%JUR%' THEN 'EXTERNO'
    WHEN EXISTS (
        SELECT 1 FROM usuario u
        JOIN empleado e ON e.empleado_id = u.empleado_id
        WHERE e.emple_nrodocumento = d.doc_dniremitente
    ) THEN 'INTERNO'
    ELSE 'EXTERNO'
END;

-- Nota: no se intenta reconocer los registros del portal ciudadano por la forma
-- del primer movimiento (area_origen = area_destino = 1). Esa forma la comparten
-- SP_REGISTRAR_TRAMITE_EXTERNO y el registro interno de mesa de partes, así que
-- marcarlos por ahí clasificaría mal trámites internos. De los 32 existentes, los
-- 6 que encajan en ese patrón ya quedan EXTERNO por la regla del remitente.
-- De aquí en adelante el portal graba EXTERNO directamente (migración 021).
