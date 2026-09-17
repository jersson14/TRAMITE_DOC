<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';

    header('Content-Type: application/json; charset=utf-8');

    /*
     * Firma uno o varios PDF del trámite con el certificado digital del usuario.
     *
     * El certificado se pide UNA sola vez y se aplica a todos los archivos que se
     * hayan elegido: antes había que repetir certificado y contraseña por cada uno.
     * Se reciben en "origenes" ("principal" y/o ids de anexo); "origen" en singular
     * se sigue aceptando, que es lo que manda el botón "Cofirmar" de cada archivo.
     *
     * - El certificado y su contraseña se leen en memoria y se descartan: no se
     *   guardan en disco ni en la base de datos, ni se escriben en la bitácora.
     * - Firmar el documento principal o un anexo sin firma crea un anexo nuevo
     *   "(firmado)"; el archivo original queda tal cual, pero deja de ofrecerse
     *   para firmar (ya tiene su copia firmada).
     * - Firmar un anexo que ya tiene firmas agrega la firma a ese mismo archivo
     *   (varios firmantes), sin invalidar las anteriores.
     * - Si un archivo falla, los demás se firman igual: la respuesta dice cuáles
     *   salieron y cuáles no, en vez de perder todo el trabajo por uno malo.
     */

    $MTR = new Modelo_Tramite();
    $MFI = new Modelo_Firma();

    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    $motivo = trim((string) ($_POST['motivo'] ?? ''));
    $clave = (string) ($_POST['clave'] ?? '');
    unset($_POST['clave']);

    $origenes = $_POST['origenes'] ?? null;
    if (!is_array($origenes)) {
        $origenes = isset($_POST['origen']) ? [(string) $_POST['origen']] : [];
    }
    $origenes = array_values(array_unique(array_map('trim', $origenes)));

    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if (!$origenes) {
        Seguridad::responderError(422, 'Seleccione al menos un archivo para firmar.');
    }
    foreach ($origenes as $o) {
        if ($o !== 'principal' && !ctype_digit($o)) {
            Seguridad::responderError(422, 'Seleccione el archivo que va a firmar.');
        }
    }
    if (count($origenes) > 20) {
        Seguridad::responderError(422, 'No se pueden firmar más de 20 archivos a la vez.');
    }
    if (mb_strlen($motivo) > 150) {
        Seguridad::responderError(422, 'El motivo no debe superar los 150 caracteres.');
    }
    if (!Seguridad::esAdmin() && !$MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'No tiene acceso a los archivos de este trámite.');
    }

    // Solo se firma lo que produce la entidad. Un trámite externo trae el documento
    // de un ciudadano u otra entidad: firmarlo sería atribuirse autoría ajena, y
    // para eso está la verificación de la firma que el documento ya trae.
    if ($MFI->Procedencia($id) === 'EXTERNO') {
        Seguridad::responderError(403, 'Este trámite es externo: su documento llega firmado desde fuera y no se firma aquí. Use "Verificar firma" para comprobar la firma que ya trae.');
    }

    // --- Certificado (una sola vez, en memoria) ---
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

    $raiz = realpath(__DIR__ . '/../..');
    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $raizWeb = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)), '/');
    $firmasPorAnexo = $MFI->Firmas_Por_Anexo($id);
    $yaFirmados = $MFI->Origenes_Ya_Firmados($id);

    $hechos = [];
    $errores = [];

    foreach ($origenes as $origen) {
        $anexo = null;
        // Se reinicia en cada vuelta: si no, un fallo temprano reportaría el
        // nombre del archivo de la iteración anterior.
        $etiqueta = $origen === 'principal' ? 'Documento principal' : 'Archivo ' . $origen;
        try {
            // --- Archivo a firmar ---
            if ($origen === 'principal') {
                $documento = $MTR->Traer_Archivo_Principal($id);
                if (!$documento || $documento['doc_archivo'] === '') {
                    throw new RuntimeException('El trámite no tiene documento principal.');
                }
                $rutaOrigen = $documento['doc_archivo'];
                $nombreBase = ($documento['doc_expediente'] ?: $id);
                $etiqueta = 'Documento principal';
            } else {
                $anexo = $MFI->Traer_Anexo($id, (int) $origen);
                if (!$anexo) {
                    throw new RuntimeException('El archivo no pertenece a este trámite.');
                }
                $rutaOrigen = $anexo['anexo_ruta'];
                $nombreBase = preg_replace('/\.pdf$/i', '', $anexo['anexo_nombre']);
                $etiqueta = $anexo['anexo_nombre'];
            }

            // Ya tiene su copia firmada: firmarlo otra vez solo generaría duplicados
            if (in_array($rutaOrigen, $yaFirmados, true)) {
                throw new RuntimeException('Ya existe una versión firmada de este archivo.');
            }

            $completa = realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rutaOrigen));
            if (!$completa || strpos($completa, $raiz . DIRECTORY_SEPARATOR) !== 0 || !is_file($completa)) {
                throw new RuntimeException('El archivo no se encuentra en el servidor.');
            }
            $pdf = file_get_contents($completa);
            if (strncmp($pdf, '%PDF', 4) !== 0) {
                throw new RuntimeException('Solo se pueden firmar documentos PDF.');
            }

            // Un anexo con firmas registradas recibe la nueva firma en el mismo archivo
            $firmasPrevias = $anexo ? ($firmasPorAnexo[(int) $anexo['anexo_id']] ?? null) : null;
            $anexoDestino = $firmasPrevias ? (int) $anexo['anexo_id'] : null;

            foreach (FirmaDigital::verificar($pdf) as $v) {
                if (!$v['valida']) {
                    throw new RuntimeException('Tiene una firma que ya no es válida (el archivo cambió después de firmarse).');
                }
            }

            // --- Firma ---
            $codigo = $MFI->Codigo_Libre();
            $urlValidar = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $raizWeb . '/validar_firma.php?codigo=' . $codigo;
            $sello = FirmaDigital::sello($cert['nombre'], $cert['dni'], $motivo, date('d/m/Y H:i'), $codigo, $urlValidar);
            $firmado = FirmaDigital::firmar($pdf, $certs, [
                'nombre' => $cert['nombre'],
                'motivo' => $motivo,
                'lugar'  => 'Abancay, Apurímac',
                'sello'  => $sello,
            ]);

            $nombreArchivo = 'FIRMA' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.pdf';
            $rutaFirmado = 'controller/tramite/documentos/' . $nombreArchivo;
            if (file_put_contents(__DIR__ . '/documentos/' . $nombreArchivo, $firmado['pdf']) === false) {
                throw new RuntimeException('No se pudo guardar el documento firmado.');
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
                throw new RuntimeException('No se pudo registrar la firma.');
            }

            // Para que otro archivo de esta misma tanda no lo vuelva a firmar
            $yaFirmados[] = $rutaOrigen;

            Bitacora::registrar(Bitacora::FIRMA_DIGITAL, 'documento', $id,
                'Firmó ' . ($origen === 'principal' ? 'el documento principal' : '"' . $etiqueta . '"') .
                ' · código ' . $codigo . ($firmado['orden'] > 1 ? ' · firma n.° ' . $firmado['orden'] : '') .
                ($cert['autofirmado'] ? ' · certificado autofirmado' : '') .
                ($firmado['normalizado'] ? ' · PDF reconstruido para poder firmarlo' : ''));

            $hechos[] = [
                'origen'      => $origen,
                'archivo'     => $etiqueta,
                'codigo'      => $codigo,
                'orden'       => $firmado['orden'],
                'anexo_id'    => $anexoId,
                'ruta'        => $rutaFirmado,
                'normalizado' => $firmado['normalizado'],
            ];
        } catch (RuntimeException $e) {
            $errores[] = ['origen' => $origen, 'archivo' => $etiqueta, 'mensaje' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('[FIRMA] ' . $e->getMessage());
            $errores[] = [
                'origen'  => $origen,
                'archivo' => $etiqueta,
                'mensaje' => 'No se pudo firmar este PDF. Puede estar protegido con contraseña o tener un formato no compatible.',
            ];
        }
    }
    $certs = null;

    // Ninguno salió: es un error de la operación, no un resultado parcial
    if (!$hechos) {
        Seguridad::responderError(422, $errores ? $errores[0]['mensaje'] : 'No se pudo firmar.');
    }

    echo json_encode([
        'status'      => 'ok',
        'firmante'    => $cert['nombre'],
        'autofirmado' => $cert['autofirmado'],
        'firmados'    => $hechos,
        'errores'     => $errores,
        // Compatibilidad con el aviso de un solo archivo (botón "Cofirmar")
        'codigo'      => $hechos[0]['codigo'],
        'orden'       => $hechos[0]['orden'],
        'anexo_id'    => $hechos[0]['anexo_id'],
        'ruta'        => $hechos[0]['ruta'],
        'normalizado' => $hechos[0]['normalizado'],
    ]);
