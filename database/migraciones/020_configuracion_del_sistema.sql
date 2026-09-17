-- ============================================================================
-- 020 - Configuración del sistema (asistente de IA)
--
-- Hasta ahora el proveedor de IA, su modelo y su clave vivían en
-- config/gemini_config.php: cambiarlos obligaba a editar un archivo en el
-- servidor. El administrador debe poder configurarlo desde el panel, así que
-- pasan a la base, en una tabla de pares clave/valor que sirve también para
-- futuros ajustes del sistema.
--
-- La clave de la API queda en texto en la base: solo la leen el administrador
-- (nunca se devuelve al navegador, se muestra enmascarada) y el servidor al
-- llamar al proveedor. El acceso a la base ya está restringido.
--
-- Los datos de la institución siguen en la tabla empresa; no se duplican aquí.
-- ============================================================================

CREATE TABLE IF NOT EXISTS configuracion (
  conf_clave       VARCHAR(60) NOT NULL,
  conf_valor       TEXT NULL,
  conf_actualizado DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (conf_clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Valores iniciales: ChatGPT-4o como proveedor, sin clave (la pone el
-- administrador desde el panel) y con los topes que ya usaba el chat.
INSERT INTO configuracion (conf_clave, conf_valor) VALUES
  ('ia_activo',        '1'),
  ('ia_proveedor',     'openai'),
  ('ia_modelo',        'gpt-4o'),
  ('ia_clave',         ''),
  ('ia_limite_minuto', '10'),
  ('ia_limite_dia',    '100')
ON DUPLICATE KEY UPDATE conf_clave = conf_clave;
