<?php
    require_once __DIR__ . '/../_guard.php';
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

    // Recibir copias
    $copias = isset($_POST['copias']) ? json_decode($_POST['copias'], true) : [];
    if(!is_array($copias)){
        $copias = [];
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

    // Registrar la derivación principal
    $consulta = $MTRA->Registrar_Deri($iddo,$orig,$dest,$desc,$idusu,$ruta,$tipo,$acc);

    if($consulta==1){
        $MTR->Registrar_Anexos($iddo, $anexos, 'controller/tramite_area/documentos', $idusu);
        // Registrar copias si existen
        foreach($copias as $area_copia){
            $MTRA->Registrar_Copia($iddo, $orig, $area_copia, $desc, $idusu, $ruta, $acc);
        }

        // ✉️ NOTIFICACIÓN: la clase obtiene automáticamente nombres de área y de usuario
        $NTF->notificarDerivacion($dest, $orig, $idusu, $iddo, '', $desc, $tipo);
        Bitacora::registrar(Bitacora::DERIVO_TRAMITE, 'documento', $iddo,
            'del área ' . $orig . ' al área ' . $dest . (count($copias) ? ' (con ' . count($copias) . ' copia(s))' : ''));

        // ✉️ NOTIFICACIÓN a áreas que reciben copias
        foreach($copias as $area_copia){
            $NTF->notificarDerivacion($area_copia, $orig, $idusu, $iddo, '', 'COPIA - ' . $desc, $tipo);
        }

        echo $consulta;
    }
?>