-- ============================================================================
-- 012 - Feriados, para contar plazos en días hábiles
--
-- Los plazos administrativos se cuentan en días hábiles (Ley 27444): se excluyen
-- sábados, domingos y feriados. Esta tabla guarda los feriados; lib/Plazos.php
-- la usa para el semáforo de plazos.
--
-- Se cargan los feriados NACIONALES de 2025 y 2026. Los feriados regionales o
-- días no laborables que decrete el Gobierno deben agregarse aquí; conviene
-- revisar la lista cada año.
-- ============================================================================

CREATE TABLE IF NOT EXISTS feriado (
  fecha       DATE         NOT NULL,
  descripcion VARCHAR(120) NOT NULL,
  PRIMARY KEY (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO feriado (fecha, descripcion) VALUES
  ('2025-01-01', 'Año Nuevo'),
  ('2025-04-17', 'Jueves Santo'),
  ('2025-04-18', 'Viernes Santo'),
  ('2025-05-01', 'Día del Trabajo'),
  ('2025-06-07', 'Batalla de Arica y Día de la Bandera'),
  ('2025-06-29', 'San Pedro y San Pablo'),
  ('2025-07-23', 'Día de la Fuerza Aérea del Perú'),
  ('2025-07-28', 'Fiestas Patrias'),
  ('2025-07-29', 'Fiestas Patrias'),
  ('2025-08-06', 'Batalla de Junín'),
  ('2025-08-30', 'Santa Rosa de Lima'),
  ('2025-10-08', 'Combate de Angamos'),
  ('2025-11-01', 'Día de Todos los Santos'),
  ('2025-12-08', 'Inmaculada Concepción'),
  ('2025-12-09', 'Batalla de Ayacucho'),
  ('2025-12-25', 'Navidad'),
  ('2026-01-01', 'Año Nuevo'),
  ('2026-04-02', 'Jueves Santo'),
  ('2026-04-03', 'Viernes Santo'),
  ('2026-05-01', 'Día del Trabajo'),
  ('2026-06-07', 'Batalla de Arica y Día de la Bandera'),
  ('2026-06-29', 'San Pedro y San Pablo'),
  ('2026-07-23', 'Día de la Fuerza Aérea del Perú'),
  ('2026-07-28', 'Fiestas Patrias'),
  ('2026-07-29', 'Fiestas Patrias'),
  ('2026-08-06', 'Batalla de Junín'),
  ('2026-08-30', 'Santa Rosa de Lima'),
  ('2026-10-08', 'Combate de Angamos'),
  ('2026-11-01', 'Día de Todos los Santos'),
  ('2026-12-08', 'Inmaculada Concepción'),
  ('2026-12-09', 'Batalla de Ayacucho'),
  ('2026-12-25', 'Navidad');
