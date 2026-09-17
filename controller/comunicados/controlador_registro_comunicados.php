<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_comunicados.php';
    require_once __DIR__ . '/_datos_comunicado.php';

    header('Content-Type: application/json; charset=utf-8');

    $MC = new Modelo_Comunicados();
    $datos = datosComunicado();

    $imagen = imagenComunicado();

    try {
        $id = $MC->Registrar_Comunicado($datos['titulo'], $datos['descripcion'], Seguridad::usuarioId(), $datos['enlace'],
            $datos['destino'], $datos['desde'], $datos['hasta'], $datos['areas'], $imagen);
    } catch (Throwable $e) {
        // Si falla el registro, la imagen subida no debe quedar en el servidor
        if ($imagen) {
            Seguridad::borrarArchivoEn(carpetaImagenes(), $imagen);
        }
        error_log('[COMUNICADO] ' . $e->getMessage());
        Seguridad::responderError(500, 'No se pudo publicar el comunicado.');
    }

    Bitacora::registrar(Bitacora::REGISTRO, 'comunicado', (string) $id,
        mb_substr($datos['titulo'], 0, 100) . ' · para ' . $datos['destino']);

    echo json_encode(['status' => 'ok', 'id' => $id]);
