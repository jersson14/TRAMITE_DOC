<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();

    header('Content-Type: application/json; charset=utf-8');

    // Un área a la que se le pidió atención registra su respuesta.
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    $texto = trim((string) ($_POST['respuesta'] ?? ''));

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if (mb_strlen($texto) < 3) {
        Seguridad::responderError(422, 'Escriba la respuesta de su área.');
    }
    if (mb_strlen($texto) > 4000) {
        Seguridad::responderError(422, 'La respuesta no debe superar los 4000 caracteres.');
    }

    // Archivo opcional (informe, opinión técnica)
    try {
        $archivo = Seguridad::guardarArchivo('archivo', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'RESP');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }
    $ruta = $archivo ? 'controller/tramite_area/documentos/' . $archivo : null;

    $resultado = $MTR->Responder_Atencion($id, Seguridad::areaId(), Seguridad::usuarioId(), $texto, $ruta);

    if ($resultado !== 1 && $archivo) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $archivo);
    }
    if ($resultado === -1) {
        Seguridad::responderError(403, 'A su área no se le pidió atención en este trámite.');
    }
    if ($resultado === 0) {
        Seguridad::responderError(422, 'Su área ya respondió esta atención.');
    }

    Bitacora::registrar(Bitacora::ATENCION_RESPONDIDA, 'documento', $id, mb_substr($texto, 0, 120) . ($ruta ? ' · con archivo' : ''));
    echo json_encode(['status' => 'ok']);
