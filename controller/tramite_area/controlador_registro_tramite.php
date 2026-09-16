<?php
    require_once __DIR__ . '/../_guard.php';
    // Faltaba: sin esto la llamada a Bitacora tumbaba la derivación con un error 500
    // después de guardarla, y no llegaban los avisos a las áreas copiadas.
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_tramite_area.php';
    require '../../model/model_tramite.php';
    require '../../utilitario/class_notificacion.php';
    $MTRA = new Modelo_TramiteArea();
    $MTR  = new Modelo_Tramite(); // para los anexos del expediente
    $NTF  = new Notificacion();

    //DATOS DE DERIVACIÓN//
    $iddo = strtoupper(htmlspecialchars($_POST['iddo'],ENT_QUOTES,'UTF-8'));
    $orig = strtoupper(htmlspecialchars($_POST['orig'],ENT_QUOTES,'UTF-8'));
    $dest = strtoupper(htmlspecialchars($_POST['dest'],ENT_QUOTES,'UTF-8'));
    $desc = strtoupper(htmlspecialchars($_POST['desc'],ENT_QUOTES,'UTF-8'));
    $idusu = Seguridad::usuarioId();
    $tipo = strtoupper(htmlspecialchars($_POST['tipo'],ENT_QUOTES,'UTF-8'));
    $acc = strtoupper(htmlspecialchars($_POST['acc'],ENT_QUOTES,'UTF-8'));

    // Datos extra para el correo (opcionales, se envían si el JS los incluye)
    $asunto_doc   = isset($_POST['asunto'])   ? htmlspecialchars($_POST['asunto'],ENT_QUOTES,'UTF-8')   : '';
    $tipo_doc_txt = isset($_POST['tipo_doc']) ? htmlspecialchars($_POST['tipo_doc'],ENT_QUOTES,'UTF-8') : '';
    $area_orig_txt = isset($_POST['area_orig_txt']) ? htmlspecialchars($_POST['area_orig_txt'],ENT_QUOTES,'UTF-8') : '';
    $area_dest_txt = isset($_POST['area_dest_txt']) ? htmlspecialchars($_POST['area_dest_txt'],ENT_QUOTES,'UTF-8') : '';

    if (!preg_match('/^[A-Z0-9\-]{1,15}$/', $iddo)) {
        Seguridad::responderError(422, 'Trámite no válido.');
    }
    if (!in_array($tipo, ['DERIVAR', 'FINALIZAR'], true)) {
        Seguridad::responderError(422, 'Operación no válida.');
    }
    if ($tipo === 'DERIVAR' && (int) $dest <= 0) {
        Seguridad::responderError(422, 'Seleccione el área de destino.');
    }
    // Solo el área que tiene el trámite (o el administrador) puede derivarlo o
    // finalizarlo. Antes no se comprobaba: cualquier sesión podía mover un trámite ajeno.
    if (!Seguridad::esAdmin() && !$MTR->Es_Area_Destino($iddo, Seguridad::areaId())) {
        Seguridad::responderError(403, 'Solo el área que tiene el trámite puede derivarlo o finalizarlo.');
    }

    // Recibir copias
    $copias = isset($_POST['copias']) ? json_decode($_POST['copias'], true) : [];
    if(!is_array($copias)){
        $copias = [];
    }
    $copias = array_values(array_unique(array_filter(array_map('intval', $copias), fn($a) => $a > 0)));

    // Áreas para atención: deben responder, cada una con su plazo en días hábiles.
    // Solo al derivar; no pueden ser el área destino ni la que deriva.
    $atenciones = [];
    if ($tipo === 'DERIVAR') {
        $pedidas = isset($_POST['atenciones']) ? json_decode($_POST['atenciones'], true) : [];
        foreach (is_array($pedidas) ? $pedidas : [] as $p) {
            $area = (int) ($p['area'] ?? 0);
            $plazo = max(0, min(365, (int) ($p['plazo'] ?? 0)));
            if ($area > 0 && $area !== (int) $dest && $area !== (int) $orig && !isset($atenciones[$area])) {
                $atenciones[$area] = ['area' => $area, 'plazo' => $plazo];
            }
        }
        $atenciones = array_values($atenciones);
        // Un área a la que se pide atención no necesita además una copia
        $areasAtencion = array_column($atenciones, 'area');
        $copias = array_values(array_diff($copias, $areasAtencion));
    }

    // El nombre y el tipo del archivo los determina el servidor, no el navegador
    try {
        $nombrearchivo = Seguridad::guardarArchivo('achivoobj', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ARCH');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }
    $ruta = $nombrearchivo ? 'controller/tramite_area/documentos/'.$nombrearchivo : '';

    // Anexos de la derivación: se validan antes de mover el expediente.
    try {
        $anexos = Seguridad::guardarArchivos('anexos', __DIR__ . '/documentos', Seguridad::MIME_PDF, 20 * 1048576, 'ANX');
    } catch (RuntimeException $e) {
        Seguridad::borrarArchivoEn(__DIR__ . '/documentos', (string) $nombrearchivo);
        Seguridad::responderError(422, $e->getMessage());
    }

    // Los movimientos posteriores a este son los que crea esta derivación
    // (la principal y sus copias); a ellos se vinculan los anexos.
    $ultimoMovimiento = $MTR->Ultimo_Movimiento($iddo);

    // Registrar la derivación principal
    $consulta = $MTRA->Registrar_Deri($iddo,$orig,$dest,$desc,$idusu,$ruta,$tipo,$acc);

    if($consulta==1){
        $idsAnexos = $MTR->Registrar_Anexos($iddo, $anexos, 'controller/tramite_area/documentos', $idusu);
        // Registrar copias si existen
        foreach($copias as $area_copia){
            $MTRA->Registrar_Copia($iddo, $orig, $area_copia, $desc, $idusu, $ruta, $acc);
        }
        $MTR->Registrar_Atenciones($iddo, $orig, $atenciones, $desc, $idusu, $ruta, $acc);
        $MTR->Vincular_Anexos($iddo, $idsAnexos, $ultimoMovimiento);

        // ✉️ NOTIFICACIÓN: la clase obtiene automáticamente nombres de área y de usuario
        $NTF->notificarDerivacion($dest, $orig, $idusu, $iddo, '', $desc, $tipo);
        Bitacora::registrar(Bitacora::DERIVO_TRAMITE, 'documento', $iddo,
            $tipo === 'FINALIZAR'
                ? 'finalizado en el área ' . $orig
                : 'del área ' . $orig . ' al área ' . $dest . (count($copias) ? ' (con ' . count($copias) . ' copia(s))' : ''));

        // ✉️ NOTIFICACIÓN a áreas que reciben copias
        foreach($copias as $area_copia){
            $NTF->notificarDerivacion($area_copia, $orig, $idusu, $iddo, '', 'COPIA - ' . $desc, $tipo);
        }

        // ✉️ Áreas a las que se pidió atención
        if ($atenciones) {
            foreach ($atenciones as $a) {
                $NTF->notificarDerivacion($a['area'], $orig, $idusu, $iddo, '', 'ATENCIÓN - ' . $desc, $tipo);
            }
            Bitacora::registrar(Bitacora::ATENCION_SOLICITADA, 'documento', $iddo,
                'a las áreas ' . implode(', ', array_map(fn($a) => $a['area'] . ($a['plazo'] ? ' (' . $a['plazo'] . ' días)' : ''), $atenciones)));
        }

        echo $consulta;
    }
?>