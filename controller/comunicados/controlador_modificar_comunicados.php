<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_comunicados.php';
    require_once __DIR__ . '/_datos_comunicado.php';

    header('Content-Type: application/json; charset=utf-8');

    $MC = new Modelo_Comunicados();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        Seguridad::responderError(422, 'Comunicado no válido.');
    }
    $estado = strtoupper(trim((string) ($_POST['estado'] ?? 'NUEVO')));
    if (!in_array($estado, ['NUEVO', 'PASADO'], true)) {
        Seguridad::responderError(422, 'Estado no válido.');
    }

    $datos = datosComunicado();
    $anterior = $MC->Imagen_De($id);
    $imagen = imagenComunicado();
    $quitar = !empty($_POST['quitar_imagen']);

    // Sin imagen nueva se conserva la que tenía, salvo que pidan quitarla
    $rutaImagen = $imagen ?: ($quitar ? null : $anterior);

    $MC->Modificar_Comunicado($id, $datos['titulo'], $datos['descripcion'], $datos['enlace'],
        $datos['destino'], $datos['desde'], $datos['hasta'], $datos['areas'], $estado, $rutaImagen);

    // La imagen reemplazada o quitada se borra del servidor
    if ($anterior && $anterior !== $rutaImagen) {
        Seguridad::borrarArchivoEn(carpetaImagenes(), $anterior);
    }

    Bitacora::registrar(Bitacora::MODIFICO, 'comunicado', (string) $id,
        mb_substr($datos['titulo'], 0, 100) . ' · para ' . $datos['destino'] . ' · ' . $estado);

    echo json_encode(['status' => 'ok']);
