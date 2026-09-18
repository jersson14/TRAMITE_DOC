-- ============================================================================
-- 027 - Correo saliente y consulta de DNI configurables desde el panel
--
-- Hasta ahora:
--   - El correo (SMTP) se configuraba solo editando config/config_email.php en
--     el servidor, y si el archivo faltaba el sistema tiraba error al registrar.
--   - El token de apis.net.pe para consultar DNI estaba ESCRITO EN EL CÓDIGO
--     FUENTE (consulta-dni-ajax.php y dos copias) y se subió al repositorio.
--
-- Ahora ambos se guardan en la tabla configuracion (migración 020) y los cambia
-- el administrador en Configuración, con un botón para probarlos antes de
-- guardar. Los secretos (contraseña SMTP y token) nunca se devuelven al
-- navegador: se muestran enmascarados.
--
-- Esta migración solo crea las claves VACÍAS. Ningún secreto va en este archivo:
-- se cargan desde el panel. Mientras el correo del panel esté vacío, el sistema
-- sigue usando config/config_email.php si existe, así que actualizar no corta
-- los correos de una instalación que ya funcionaba.
--
-- IMPORTANTE: el token que estaba en el código quedó expuesto en el historial
-- del repositorio. Hay que regenerarlo en apis.net.pe y cargar el nuevo aquí.
-- ============================================================================

SET NAMES utf8mb4;

INSERT INTO configuracion (conf_clave, conf_valor) VALUES
  ('smtp_activo',           ''),
  ('smtp_host',             ''),
  ('smtp_puerto',           ''),
  ('smtp_seguridad',        ''),
  ('smtp_usuario',          ''),
  ('smtp_clave',            ''),
  ('smtp_remitente_nombre', ''),
  ('smtp_remitente_correo', ''),
  ('dni_activo',            '1'),
  ('dni_token',             '')
ON DUPLICATE KEY UPDATE conf_clave = conf_clave;
