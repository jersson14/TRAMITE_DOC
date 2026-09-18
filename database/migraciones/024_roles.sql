-- ============================================================================
-- 024 - Roles por función, no solo "Administrador" y "Secretario (a)"
--
-- Hasta ahora todo el que no era administrador era "Secretario (a)" y podía
-- hacer TODO dentro de su área: registrar, derivar, finalizar y firmar. No había
-- forma de que un jefe aprobara lo que su técnico preparó.
--
-- Se agregan tres valores al enum. Los usuarios existentes NO cambian: siguen
-- siendo Administrador o Secretario (a), y el administrador los reasigna desde
-- la pantalla de Usuarios cuando quiera.
--
-- Quién puede qué (se aplica en lib/Seguridad.php, no en la base):
--
--   Rol              Registra  Deriva  Finaliza  Firma  Mantenimientos
--   Administrador       si       si       si      si         si
--   Mesa de Partes      si       si       --      --         --
--   Jefe de Área        si       si       si      si         --
--   Especialista        si       --       --      si         --
--   Secretario (a)      si       si       si      --         --
--
-- El Especialista atiende lo que se le asigna y prepara respuestas, pero no saca
-- el expediente del área: eso lo hace el jefe o la secretaria.
--
-- El orden del enum importa poco, pero se agregan al final para no alterar el
-- valor numérico de los dos que ya existían.
-- ============================================================================

-- El cliente mysql de Windows lee el archivo como cp850 si no se le dice otra
-- cosa, y 'Jefe de Área' se grabaría con los bytes corruptos. Con esto el
-- servidor interpreta como UTF-8 lo que llega, sin importar cómo se invoque.
SET NAMES utf8mb4;

ALTER TABLE usuario
  MODIFY COLUMN usu_rol ENUM(
    'Secretario (a)',
    'Administrador',
    'Mesa de Partes',
    'Jefe de Área',
    'Especialista'
  ) NOT NULL;
