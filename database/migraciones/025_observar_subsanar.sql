-- ============================================================================
-- 025 - Observar un trámite y que el ciudadano lo subsane
--
-- Hasta ahora, si un ciudadano presentaba algo incompleto, mesa de partes solo
-- podía aceptarlo o rechazarlo. En la práctica se OBSERVA: se le indica qué
-- falta y se le da un plazo para subsanarlo, sin perder el expediente.
--
-- Flujo:
--   1. El área observa el trámite con un motivo y un plazo en días hábiles.
--      El documento pasa a OBSERVADO y se guarda su estado anterior.
--   2. El ciudadano entra a seguimiento.php con su N° de expediente y DNI
--      (el mismo par que ya usa para consultar), ve qué le observaron y sube
--      lo que falta.
--   3. Al subsanar, el trámite vuelve a su estado anterior y el área recibe
--      el aviso para continuar.
--
-- Solo se observan trámites EXTERNOS: la subsanación la hace el ciudadano desde
-- el portal público. Un documento interno se corrige derivándolo o respondiendo.
--
-- El vencimiento del plazo NO lo marca una tarea programada: se calcula al leer
-- (obs_fecha_limite < hoy y todavía pendiente). Así no hace falta un cron que
-- alguien tenga que acordarse de configurar en el servidor.
-- ============================================================================

SET NAMES utf8mb4;

-- El estado nuevo va al final del enum para no alterar el valor de los demás
ALTER TABLE documento
  MODIFY COLUMN doc_estatus ENUM(
    'PENDIENTE', 'RECHAZADO', 'ACEPTADO', 'FINALIZADO', 'SIN RESPUESTA', 'OBSERVADO'
  ) NOT NULL;

CREATE TABLE IF NOT EXISTS observacion (
  observacion_id        BIGINT        NOT NULL AUTO_INCREMENT,
  documento_id          CHAR(12)      NOT NULL,
  obs_motivo            TEXT          NOT NULL COMMENT 'Qué le falta o qué debe corregir el ciudadano',
  obs_plazo_dias        INT           NOT NULL COMMENT 'Plazo en días hábiles',
  obs_fecha             DATETIME      NOT NULL DEFAULT current_timestamp(),
  obs_fecha_limite      DATE          NOT NULL,
  obs_estado            ENUM('PENDIENTE', 'SUBSANADO') NOT NULL DEFAULT 'PENDIENTE',
  obs_estado_anterior   VARCHAR(20)   NOT NULL COMMENT 'Estado del trámite antes de observarlo; se restituye al subsanar',
  usuario_id            INT           NULL,
  area_id               INT           NULL,
  sub_fecha             DATETIME      NULL,
  sub_texto             TEXT          NULL COMMENT 'Comentario del ciudadano al subsanar',
  sub_archivo           VARCHAR(255)  NULL,
  sub_nombre_archivo    VARCHAR(200)  NULL,
  sub_ip                VARCHAR(45)   NULL,
  PRIMARY KEY (observacion_id),
  KEY idx_obs_documento (documento_id),
  KEY idx_obs_pendientes (obs_estado, obs_fecha_limite),
  CONSTRAINT fk_obs_documento FOREIGN KEY (documento_id) REFERENCES documento (documento_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
