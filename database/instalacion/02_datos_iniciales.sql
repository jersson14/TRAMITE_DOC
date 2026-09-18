-- ============================================================================
-- SISTRAMITEDOC - Datos iniciales (instalación nueva)
--
-- Lo mínimo para que el sistema arranque, después de 01_esquema.sql:
--   - la institución, con datos de ejemplo que se cambian en Configuración;
--   - el área MESA DE PARTES, que DEBE ser la número 1 (ahí entra lo del portal);
--   - un usuario administrador;
--   - el catálogo de tipos de documento;
--   - los feriados nacionales del Perú de 2026 y 2027;
--   - los ajustes de configuración, vacíos.
--
-- No contiene datos personales ni claves de servicios externos.
--
-- IMPORTANTE: el administrador se crea con una contraseña conocida.
--   Usuario:    admin
--   Contraseña: Admin.Cambiar2026
-- Cámbiela en cuanto entre por primera vez (Usuarios > Cambiar contraseña).
-- Mientras no la cambie, cualquiera que lea este archivo puede entrar.
--
-- Aplicar con el juego de caracteres correcto, o las tildes se graban dañadas:
--   mysql --default-character-set=utf8mb4 -u USUARIO -p sistema_tramite < 02_datos_iniciales.sql
-- ============================================================================

SET NAMES utf8mb4;

-- Institución: se reemplaza desde Configuración > Datos de la institución
INSERT INTO empresa (empresa_id, emp_razon, emp_sigla, emp_email, emp_cod, emp_telefono, emp_direccion,
                     emp_logo, emp_color, emp_hora_inicio, emp_hora_fin)
VALUES (1, 'NOMBRE DE LA INSTITUCIÓN', 'SIGLA', 'mesadepartes@institucion.gob.pe', '000000',
        '000000000', 'Dirección de la institución', '', '#1F2358', '08:00:00', '16:30:00');

-- Mesa de Partes DEBE tener area_cod = 1: el portal ciudadano registra ahí
INSERT INTO area (area_cod, area_nombre, area_estado, area_sigla)
VALUES (1, 'MESA DE PARTES', 'ACTIVO', 'MP');

-- Administrador inicial (ver la advertencia de la cabecera)
INSERT INTO empleado (empleado_id, emple_nombre, emple_apepat, emple_apemat, emple_feccreacion,
                      emple_nrodocumento, emple_movil, emple_email, emple_estatus, emple_direccion)
VALUES (1, 'ADMINISTRADOR', 'DEL', 'SISTEMA', CURDATE(), '00000000', '000000000', '', 'ACTIVO', '');

INSERT INTO usuario (usu_id, usu_usuario, usu_contra, usu_feccreacion, empleado_id, usu_estatus,
                     area_id, usu_rol, empresa_id)
VALUES (1, 'admin', '$2y$12$nuvkNe7IIPXw37lEOy6cyuyLCCOtQTwr4mQg3SPt1xU.nFh.EkBdG', CURDATE(), 1, 'ACTIVO',
        1, 'Administrador', 1);

-- Tipos de documento de uso común en la administración pública peruana.
-- Se agregan o desactivan en Tipos de documento.
INSERT INTO tipo_documento (tipodo_descripcion, tipodo_estado) VALUES
  ('CARTA', 'ACTIVO'),
  ('OFICIO', 'ACTIVO'),
  ('SOLICITUD', 'ACTIVO'),
  ('MEMORÁNDUM', 'ACTIVO'),
  ('RECIBO', 'ACTIVO'),
  ('CARTA CIRCULAR', 'ACTIVO'),
  ('CARTA DE PRESENTACIÓN', 'ACTIVO'),
  ('CARTA DE REQUERIMIENTO', 'ACTIVO'),
  ('CARTA MÚLTIPLE', 'ACTIVO'),
  ('CARTA NOTARIAL', 'ACTIVO'),
  ('CÉDULA DE NOTIFICACIÓN', 'ACTIVO'),
  ('CONTRATO', 'ACTIVO'),
  ('CONVENIO', 'ACTIVO'),
  ('DOCUMENTO CONFIDENCIAL', 'ACTIVO'),
  ('DOCUMENTO VÍA CORREO ELECTRÓNICO', 'ACTIVO'),
  ('EXHORTO', 'ACTIVO'),
  ('INFORME', 'ACTIVO'),
  ('INFORME CONFIDENCIAL', 'ACTIVO'),
  ('INFORME TÉCNICO', 'ACTIVO'),
  ('DOCUMENTO DE SUPERVISIÓN', 'ACTIVO'),
  ('MEMORÁNDUM MÚLTIPLE', 'ACTIVO'),
  ('MEMORIAL', 'ACTIVO'),
  ('NOTIFICACIÓN JUDICIAL', 'ACTIVO'),
  ('OFICIO CIRCULAR', 'ACTIVO'),
  ('OFICIO MÚLTIPLE', 'ACTIVO'),
  ('PETITORIO', 'ACTIVO'),
  ('PROVEÍDO', 'ACTIVO'),
  ('RESOLUCIÓN', 'ACTIVO'),
  ('ADENDA', 'ACTIVO'),
  ('OFERTA', 'ACTIVO'),
  ('OTROS', 'ACTIVO');

-- Feriados nacionales del Perú. Los plazos se cuentan en días hábiles sin ellos.
-- Jueves y Viernes Santo cambian cada año. Revise la lista cada año y agregue los
-- feriados regionales o días no laborables que decrete el Gobierno.
INSERT INTO feriado (fecha, descripcion, tipo) VALUES
  ('2026-01-01', 'Año Nuevo', 'NACIONAL'),
  ('2026-04-02', 'Jueves Santo', 'NACIONAL'),
  ('2026-04-03', 'Viernes Santo', 'NACIONAL'),
  ('2026-05-01', 'Día del Trabajo', 'NACIONAL'),
  ('2026-06-07', 'Batalla de Arica y Día de la Bandera', 'NACIONAL'),
  ('2026-06-29', 'San Pedro y San Pablo', 'NACIONAL'),
  ('2026-07-23', 'Día de la Fuerza Aérea del Perú', 'NACIONAL'),
  ('2026-07-28', 'Fiestas Patrias', 'NACIONAL'),
  ('2026-07-29', 'Fiestas Patrias', 'NACIONAL'),
  ('2026-08-06', 'Batalla de Junín', 'NACIONAL'),
  ('2026-08-30', 'Santa Rosa de Lima', 'NACIONAL'),
  ('2026-10-08', 'Combate de Angamos', 'NACIONAL'),
  ('2026-11-01', 'Día de Todos los Santos', 'NACIONAL'),
  ('2026-12-08', 'Inmaculada Concepción', 'NACIONAL'),
  ('2026-12-09', 'Batalla de Ayacucho', 'NACIONAL'),
  ('2026-12-25', 'Navidad', 'NACIONAL'),
  ('2027-01-01', 'Año Nuevo', 'NACIONAL'),
  ('2027-03-25', 'Jueves Santo', 'NACIONAL'),
  ('2027-03-26', 'Viernes Santo', 'NACIONAL'),
  ('2027-05-01', 'Día del Trabajo', 'NACIONAL'),
  ('2027-06-07', 'Batalla de Arica y Día de la Bandera', 'NACIONAL'),
  ('2027-06-29', 'San Pedro y San Pablo', 'NACIONAL'),
  ('2027-07-23', 'Día de la Fuerza Aérea del Perú', 'NACIONAL'),
  ('2027-07-28', 'Fiestas Patrias', 'NACIONAL'),
  ('2027-07-29', 'Fiestas Patrias', 'NACIONAL'),
  ('2027-08-06', 'Batalla de Junín', 'NACIONAL'),
  ('2027-08-30', 'Santa Rosa de Lima', 'NACIONAL'),
  ('2027-10-08', 'Combate de Angamos', 'NACIONAL'),
  ('2027-11-01', 'Día de Todos los Santos', 'NACIONAL'),
  ('2027-12-08', 'Inmaculada Concepción', 'NACIONAL'),
  ('2027-12-09', 'Batalla de Ayacucho', 'NACIONAL'),
  ('2027-12-25', 'Navidad', 'NACIONAL');

-- Ajustes del panel de Configuración, vacíos: se cargan desde la pantalla.
-- Ninguna clave de servicio externo va en este archivo.
INSERT INTO configuracion (conf_clave, conf_valor) VALUES
  ('ia_activo', '1'), ('ia_proveedor', 'openai'), ('ia_modelo', 'gpt-4.1-mini'), ('ia_clave', ''),
  ('ia_limite_minuto', '10'), ('ia_limite_dia', '100'),
  ('smtp_activo', ''), ('smtp_host', ''), ('smtp_puerto', ''), ('smtp_seguridad', ''),
  ('smtp_usuario', ''), ('smtp_clave', ''), ('smtp_remitente_nombre', ''), ('smtp_remitente_correo', ''),
  ('dni_activo', '1'), ('dni_token', '');
