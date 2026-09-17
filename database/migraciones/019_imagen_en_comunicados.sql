-- ============================================================================
-- 019 - Imagen en los comunicados
--
-- Un comunicado puede llevar una imagen (afiche, cronograma, fotografía) que se
-- muestra en el aviso que aparece al entrar al sistema y en la lista del
-- administrador. Se guarda la ruta relativa del archivo, igual que con los
-- documentos del trámite; el archivo vive en controller/comunicados/imagenes/.
--
-- Columna al final de la tabla: ningún procedimiento usa SELECT *.
-- ============================================================================

ALTER TABLE comunicados
  ADD COLUMN IF NOT EXISTS com_imagen VARCHAR(255) NULL
    COMMENT 'Ruta relativa de la imagen del comunicado';
