<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';

    header('Content-Type: application/json; charset=utf-8');

    /*
     * Firma un PDF del trámite con el certificado digital del usuario (.pfx/.p12).
     *
     * - El certificado y su contraseña se leen en memoria y se descartan: no se
     *   guardan en disco ni en la base de datos, ni se escriben en la bitácora.
     * - Firmar el documento principal o un anexo sin firma crea un anexo nuevo
     *   "(firmado)"; el archivo original queda tal cual.
     * - Firmar un anexo que ya tiene firmas agrega la firma a ese mismo archivo
     *   (varios firmantes), sin invalidar las anteriores.
     */

    $MTR = new Modelo_Tramite();
    $MFI = new Modelo_Firma();

    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    $origen = trim((string) ($_POST['origen'] ?? ''));
    $motivo = trim((string) ($_POST['motivo'] ?? ''));
    $clave = (string) ($_POST['clave'] ?? '');
    unset($_POST['clave']);

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if ($origen !== 'principal' && !ctype_digit($origen)) {
        Seguridad::responderError(422, 'Seleccione el archivo que va a firmar.');
    }
    if (mb_strlen($motivo) > 150) {
        Seguridad::responderError(422, 'El motivo no debe superar los 150 caracteres.');
    }
    if (!Seguridad::esAdmin() && !$MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'No tiene acceso a los archivos de este trámite.');
    }

    // --- Archivo a firmar ---
    $raiz = realpath(__DIR__ . '/../..');
    $anexo = null;
    if ($origen === 'principal') {
        $documento = $MTR->Traer_Archivo_Principal($id);
        if (!$documento || $documento['doc_archivo'] === '') {
            Seguridad::responderError(422, 'El trámite no tiene documento principal.');
        }
        $rutaOrigen = $documento['doc_archivo'];
        $nombreBase = ($documento['doc_expediente'] ?: $id);
    } else {
        $anexo = $MFI->Traer_Anexo($id, (int) $origen);
        if (!$anexo) {
            Seguridad::responderError(422, 'El archivo no pertenece a este trámite.');
        }
        $rutaOrigen = $anexo['anexo_ruta'];
        $nombreBase = preg_replace('/\.pdf$/i', '', $anexo['anexo_nombre']);
    }

    $completa = realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaOrigen));
    if (!$completa || strpos($completa, $raiz . DIRECTORY_SEPARATOR) !== 0 || !is_file($completa)) {
        Seguridad::responderError(422, 'El archivo no se encuentra en el servidor.');
    }
    $pdf = file_get_contents($completa);
    if (strncmp($pdf, '%PDF', 4) !== 0) {
        Seguridad::responderError(422, 'Solo se pueden firmar documentos PDF.');
    }

    // Un anexo con firmas registradas recibe la nueva firma en el mismo archivo
    $firmasPrevias = $anexo ? ($MFI->Firmas_Por_Anexo($id)[(int) $anexo['anexo_id']] ?? null) : null;
    $anexoDestino = $firmasPrevias ? (int) $anexo['anexo_id'] : null;

    $verificacionPrevia = FirmaDigital::verificar($pdf);
    foreach ($verificacionPrevia as $v) {
        if (!$v['valida']) {
            Seguridad::responderError(422, 'El archivo tiene una firma que ya no es válida (fue modificado después de firmarse). No se puede firmar encima.');
        }
    }

    // --- Certificado (solo en memoria) ---
    $subida = $_FILES['certificado'] ?? null;
    if (!$subida || ($subida['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($subida['tmp_name'])) {
        Seguridad::responderError(422, 'Adjunte su certificado digital (.pfx o .p12).');
    }
    if ($subida['size'] > 1048576) {
        @unlink($subida['tmp_name']);
        Seguridad::responderError(422, 'El certificado no debe pesar más de 1 MB.');
    }
    $contenidoPfx = file_get_contents($subida['tmp_name']);
    @unlink($subida['tmp_name']);

    try {
        $certs = FirmaDigital::leerPfx($contenidoPfx, $clave);
        $contenidoPfx = null;
        $clave = null;
        $cert = FirmaDigital::datosCertificado($certs['cert']);
        FirmaDigital::validarCertificado($cert);
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }

    // El certificado debe ser del propio usuario: se compara el DNI con el de su ficha de empleado
    $usuario = $MFI->Datos_Usuario(Seguridad::usuarioId());
    $dniUsuario = $usuario ? preg_replace('/\D/', '', (string) $usuario['emple_nrodocumento']) : '';
    if ($cert['dni'] && $dniUsuario !== '' && $cert['dni'] !== $dniUsuario) {
        Seguridad::responderError(422, 'El certificado pertenece a otra persona (DNI ' . $cert['dni'] . '). Solo puede firmar con su propio certificado.');
    }

    // --- Firma ---
    $codigo = $MFI->Codigo_Libre();
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $raizWeb = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)), '/');
    $urlValidar = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $raizWeb . '/validar_firma.php?codigo=' . $codigo;

    try {
        $sello = FirmaDigital::sello($cert['nombre'], $cert['dni'], $motivo, date('d/m/Y H:i'), $codigo, $urlValidar);
        $firmado = FirmaDigital::firmar($pdf, $certs, [
            'nombre' => $cert['nombre'],
            'motivo' => $motivo,
            'lugar'  => 'Abancay, Apurímac',
            'sello'  => $sello,
        ]);
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    } catch (Throwable $e) {
        error_log('[FIRMA] ' . $e->getMessage());
        Seguridad::responderError(422, 'No se pudo firmar este PDF. Puede estar protegido con contraseña o tener un formato no compatible.');
    }
    $certs = null;

    $nombreArchivo = 'FIRMA' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.pdf';
    $rutaFirmado = 'controller/tramite/documentos/' . $nombreArchivo;
    if (file_put_contents(__DIR__ . '/documentos/' . $nombreArchivo, $firmado['pdf']) === false) {
        Seguridad::responderError(500, 'No se pudo guardar el documento firmado.');
    }

    try {
        $anexoId = $MFI->Registrar_Firma([
            'codigo'           => $codigo,
            'documento_id'     => $id,
            'orden'            => $firmado['orden'],
            'archivo_origen'   => $rutaOrigen,
            'archivo_firmado'  => $rutaFirmado,
            'hash_origen'      => hash('sha256', $pdf),
            'hash_firmado'     => hash('sha256', $firmado['pdf']),
            'bytes'            => strlen($firmado['pdf']),
            'motivo'           => $motivo !== '' ? $motivo : null,
            'firmante_nombre'  => mb_substr($cert['nombre'], 0, 200),
            'firmante_dni'     => $cert['dni'],
            'cert_emisor'      => mb_substr((string) $cert['emisor'], 0, 255),
            'cert_serie'       => mb_substr((string) $cert['serie'], 0, 80),
            'cert_desde'       => $cert['desde'],
            'cert_hasta'       => $cert['hasta'],
            'cert_huella'      => $cert['huella'],
            'cert_autofirmado' => $cert['autofirmado'],
            'usuario_id'       => Seguridad::usuarioId() ?: null,
            'area_id'          => Seguridad::areaId() ?: null,
        ], $anexoDestino, mb_substr($nombreBase, 0, 185) . ' (firmado).pdf');
    } catch (Throwable $e) {
        @unlink(__DIR__ . '/documentos/' . $nombreArchivo);
        error_log('[FIRMA] ' . $e->getMessage());
        Seguridad::responderError(500, 'No se pudo registrar la firma.');
    }

    Bitacora::registrar(Bitacora::FIRMA_DIGITAL, 'documento', $id,
        'Firmó ' . ($origen === 'principal' ? 'el documento principal' : '"' . $anexo['anexo_nombre'] . '"') .
        ' · código ' . $codigo . ($firmado['orden'] > 1 ? ' · firma n.° ' . $firmado['orden'] : '') .
        ($cert['autofirmado'] ? ' · certificado autofirmado' : '') .
        ($firmado['normalizado'] ? ' · PDF reconstruido para poder firmarlo' : ''));

    echo json_encode([
        'status'      => 'ok',
        'codigo'      => $codigo,
        'orden'       => $firmado['orden'],
        'anexo_id'    => $anexoId,
        'ruta'        => $rutaFirmado,
        'firmante'    => $cert['nombre'],
        'autofirmado' => $cert['autofirmado'],
        'normalizado' => $firmado['normalizado'],
    ]);
