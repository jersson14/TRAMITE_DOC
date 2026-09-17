<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';
    require_once __DIR__ . '/../../lib/FirmaAlRegistrar.php';
    $MTR = new Modelo_Tramite();//Instaciamos
    //DATOS DE REMITENTE//
    $documentoFinal = strtoupper(htmlspecialchars($_POST['documentoFinal'],ENT_QUOTES,'UTF-8'));
    $nom = strtoupper(htmlspecialchars($_POST['nom'],ENT_QUOTES,'UTF-8'));
    $apt = strtoupper(htmlspecialchars($_POST['apt'],ENT_QUOTES,'UTF-8'));
    $apm = strtoupper(htmlspecialchars($_POST['apm'],ENT_QUOTES,'UTF-8'));
    $cel = strtoupper(htmlspecialchars($_POST['cel'],ENT_QUOTES,'UTF-8'));
    $ema = htmlspecialchars($_POST['ema'],ENT_QUOTES,'UTF-8');
    $dir = strtoupper(htmlspecialchars($_POST['dir'],ENT_QUOTES,'UTF-8'));
    $vpresentacion = strtoupper(htmlspecialchars($_POST['vpresentacion'],ENT_QUOTES,'UTF-8'));
    $ruc = strtoupper(htmlspecialchars($_POST['ruc'],ENT_QUOTES,'UTF-8'));
    $raz = strtoupper(htmlspecialchars($_POST['raz'],ENT_QUOTES,'UTF-8'));


    //DATOS DEL DOCUMENTO //
    $arp = strtoupper(htmlspecialchars($_POST['arp'],ENT_QUOTES,'UTF-8'));
    $ard = strtoupper(htmlspecialchars($_POST['ard'],ENT_QUOTES,'UTF-8'));
    $tip = strtoupper(htmlspecialchars($_POST['tip'],ENT_QUOTES,'UTF-8'));
    $ndo = strtoupper(htmlspecialchars($_POST['ndo'],ENT_QUOTES,'UTF-8'));
    $asu = strtoupper(htmlspecialchars($_POST['asu'],ENT_QUOTES,'UTF-8'));
    $fol = strtoupper(htmlspecialchars($_POST['fol'],ENT_QUOTES,'UTF-8'));
    $idusu = Seguridad::usuarioId();
    $acc = strtoupper(htmlspecialchars($_POST['acc'],ENT_QUOTES,'UTF-8'));
    $obs = strtoupper(htmlspecialchars($_POST['obs'],ENT_QUOTES,'UTF-8'));
    $tre = strtoupper(htmlspecialchars($_POST['tre'],ENT_QUOTES,'UTF-8'));

    // Recibir y decodificar las copias
    $copias = array();
    if(isset($_POST['copias']) && !empty($_POST['copias'])){
        $copias = json_decode($_POST['copias'], true);
        if(!is_array($copias)){
            $copias = array();
        }
    }

    // El nombre y el tipo del archivo los determina el servidor, no el navegador
    try {
        $nombrearchivo = Seguridad::guardarArchivo('achivoobj', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ARCH');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }

    // Anexos: se validan antes de registrar para no dejar el trámite a medias.
    try {
        $anexos = Seguridad::guardarArchivos('anexos', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ANX');
    } catch (RuntimeException $e) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
        Seguridad::responderError(422, $e->getMessage());
    }

    /*
     * Firma al registrar: el documento que redacta la entidad debe salir firmado,
     * en vez de enviarse y firmarse después desde el panel de archivos.
     *
     * El certificado se comprueba ANTES de crear el trámite: si está mal, no se
     * registra nada y el usuario corrige. Es opcional: sin certificado el trámite
     * se registra igual y queda constancia en la bitácora.
     */
    $MFI = new Modelo_Firma();
    $quiereFirmar = FirmaAlRegistrar::solicitada();
    $preparado = null;
    if ($quiereFirmar) {
        $usuarioFicha = $MFI->Datos_Usuario(Seguridad::usuarioId());
        try {
            $preparado = FirmaAlRegistrar::prepararCertificado(
                (string) ($_POST['clave'] ?? ''),
                $usuarioFicha ? (string) $usuarioFicha['emple_nrodocumento'] : null
            );
        } catch (RuntimeException $e) {
            Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
            foreach ($anexos as $a) {
                Seguridad::borrarArchivoEn(__DIR__ . '/documentos', $a['nombre']);
            }
            Seguridad::responderError(422, $e->getMessage());
        }
    }
    unset($_POST['clave']);

    $ruta='controller/tramite/documentos/'.$nombrearchivo;
    $consulta = $MTR->Registrar_Tramite_ul($documentoFinal,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$arp,
    $ard,$tip,$ndo,$asu,$ruta,$fol,$idusu,$acc,$obs,$tre,$copias);
    if ($consulta) {
        // Trámite nuevo: todos sus movimientos (principal y copias) son de este envío.
        $idsAnexos = $MTR->Registrar_Anexos($consulta, $anexos, 'controller/tramite/documentos', $idusu);
        $MTR->Vincular_Anexos($consulta, $idsAnexos, 0);
        // Procedencia: decide si este documento se firma en el sistema (INTERNO) o
        // solo se verifica la firma que ya trae de fuera (EXTERNO). Migración 021.
        //
        // No se toma de la pantalla sin más: la decide el REMITENTE. Si tiene cuenta
        // de usuario es personal de la entidad y el documento es interno; si no, es
        // externo. La casilla "Es trámite externo" solo puede forzar externo.
        $procedencia = $MFI->Procedencia_De_Registro(
            $documentoFinal,
            ($_POST['procedencia'] ?? '') === 'EXTERNO',
            $MFI->Remitente_Es_Juridica($ruc, $raz, $vpresentacion)
        );
        $MFI->Marcar_Procedencia($consulta, $procedencia);

        /*
         * Se firma recién ahora, con el trámite ya creado: la tabla firma necesita
         * su documento_id. El certificado ya se validó arriba, así que aquí solo
         * puede fallar un PDF puntual, y eso no bota a los demás.
         *
         * Un trámite externo no se firma: el documento es de un ciudadano u otra
         * entidad, y firmarlo sería atribuirse autoría ajena.
         */
        $resultadoFirma = ['firmados' => [], 'errores' => []];
        if ($preparado && $procedencia === 'INTERNO') {
            $porFirmar = [];
            if (($_POST['firmar_principal'] ?? '1') !== '0') {
                $porFirmar[] = [
                    'ruta'     => $ruta,
                    'absoluta' => __DIR__ . '/documentos/' . $nombrearchivo,
                    'anexo_id' => null,
                    'etiqueta' => 'Documento principal',
                ];
            }
            if (($_POST['firmar_anexos'] ?? '0') === '1') {
                foreach ($anexos as $i => $a) {
                    $porFirmar[] = [
                        'ruta'     => 'controller/tramite/documentos/' . $a['nombre'],
                        'absoluta' => __DIR__ . '/documentos/' . $a['nombre'],
                        'anexo_id' => $idsAnexos[$i] ?? null,
                        'etiqueta' => $a['original'],
                    ];
                }
            }
            $resultadoFirma = FirmaAlRegistrar::firmarArchivos(
                $porFirmar, $preparado, $MFI, $consulta,
                trim((string) ($_POST['motivo'] ?? '')),
                Seguridad::usuarioId() ?: null, Seguridad::areaId() ?: null
            );
        }
        $preparado = null;

        // Se deja constancia de con qué firma llegó el documento. No bloquea el
        // registro: muchos documentos llegan en papel escaneado, sin firma digital.
        $detalleFirma = '';
        $completa = __DIR__ . '/documentos/' . $nombrearchivo;
        if (is_file($completa)) {
            try {
                $detalleFirma = ' · ' . FirmaDigital::resumenTexto(
                    FirmaDigital::resumenFirmas((string) file_get_contents($completa)));
            } catch (Throwable $e) {
                error_log('[FIRMA] resumen al registrar: ' . $e->getMessage());
            }
        }

        Bitacora::registrar(Bitacora::REGISTRO_TRAMITE, 'documento', $consulta,
            'asunto: ' . $asu . (count($anexos) ? ' · ' . count($anexos) . ' anexo(s)' : '') .
            ' · ' . strtolower($procedencia) . $detalleFirma .
            FirmaAlRegistrar::resumenBitacora($resultadoFirma) .
            ($quiereFirmar || $procedencia === 'EXTERNO' ? '' : ' · SIN firma digital'));
        echo $consulta;
    }
?>