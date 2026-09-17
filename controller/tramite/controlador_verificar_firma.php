<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';

    header('Content-Type: application/json; charset=utf-8');

    /*
     * Comprueba qué firmas digitales trae un PDF. No firma nada ni modifica archivos.
     *
     * Dos usos:
     *   1. Al registrar un trámite: se envía el PDF que el operador acaba de elegir
     *      (campo "archivo"). Se lee en memoria, se verifica y se descarta: así el
     *      operador ve si el documento viene firmado ANTES de confirmar el registro.
     *      Esto reemplaza la casilla "Tiene el documento firmado digitalmente", que
     *      solo era una declaración sin comprobar nada.
     *   2. Sobre un archivo ya guardado del trámite (id + origen): es lo que ofrece
     *      el botón "Verificar firma" en los documentos externos, que no se firman.
     */

    $MTR = new Modelo_Tramite();
    $MFI = new Modelo_Firma();

    /** Lee el PDF a verificar, ya sea de la subida o del trámite. Nunca lo guarda. */
    function pdfAVerificar(Modelo_Tramite $MTR, Modelo_Firma $MFI): string
    {
        $subida = $_FILES['archivo'] ?? null;
        if ($subida && ($subida['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ($subida['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($subida['tmp_name'])) {
                Seguridad::responderError(422, 'No se pudo recibir el archivo.');
            }
            if ($subida['size'] > 20 * 1048576) {
                @unlink($subida['tmp_name']);
                Seguridad::responderError(422, 'El archivo supera los 20 MB.');
            }
            $pdf = (string) file_get_contents($subida['tmp_name']);
            @unlink($subida['tmp_name']);   // verificar no guarda nada
            return $pdf;
        }

        // Archivo ya registrado en el trámite
        $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
        $origen = trim((string) ($_POST['origen'] ?? ''));
        if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
            Seguridad::responderError(422, 'Trámite no válido.');
        }
        if ($origen !== 'principal' && !ctype_digit($origen)) {
            Seguridad::responderError(422, 'Seleccione el archivo que va a verificar.');
        }
        if (!Seguridad::esAdmin() && !$MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
            Seguridad::responderError(403, 'No tiene acceso a los archivos de este trámite.');
        }

        if ($origen === 'principal') {
            $documento = $MTR->Traer_Archivo_Principal($id);
            $ruta = $documento ? (string) $documento['doc_archivo'] : '';
        } else {
            $anexo = $MFI->Traer_Anexo($id, (int) $origen);
            if (!$anexo) {
                Seguridad::responderError(422, 'El archivo no pertenece a este trámite.');
            }
            $ruta = (string) $anexo['anexo_ruta'];
        }

        $raiz = realpath(__DIR__ . '/../..');
        $completa = $ruta === '' ? false : realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta));
        if (!$completa || strpos($completa, $raiz . DIRECTORY_SEPARATOR) !== 0 || !is_file($completa)) {
            Seguridad::responderError(422, 'El archivo no se encuentra en el servidor.');
        }
        return (string) file_get_contents($completa);
    }

    $pdf = pdfAVerificar($MTR, $MFI);
    if (strncmp($pdf, '%PDF', 4) !== 0) {
        Seguridad::responderError(422, 'El archivo no es un PDF.');
    }

    try {
        $resumen = FirmaDigital::resumenFirmas($pdf);
    } catch (Throwable $e) {
        error_log('[FIRMA] verificar: ' . $e->getMessage());
        Seguridad::responderError(422, 'No se pudo leer el PDF para comprobar sus firmas.');
    }

    echo json_encode(['status' => 'ok'] + $resumen);
