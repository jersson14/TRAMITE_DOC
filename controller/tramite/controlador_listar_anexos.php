<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite.php';
    require '../../model/model_firma.php';
    require_once __DIR__ . '/../../lib/FirmaDigital.php';

    header('Content-Type: application/json; charset=utf-8');

    $MTR = new Modelo_Tramite();
    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));

    // El código de seguimiento tiene un formato fijo; cualquier otra cosa no se consulta.
    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        echo json_encode(['principal' => null, 'data' => []]);
        exit;
    }

    // Sin esta comprobación, cualquier usuario con sesión podía listar los archivos
    // de un trámite ajeno con solo cambiar el código en la petición.
    if (!Seguridad::esAdmin() && !$MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'No tiene acceso a los archivos de este trámite.');
    }

    $raiz = realpath(__DIR__ . '/../..');

    /** Comprueba en disco que el archivo siga existiendo y devuelve su tamaño. */
    function estadoArchivo(string $raiz, $ruta): array
    {
        $ruta = (string) $ruta;
        if ($ruta === '') {
            return ['existe' => false, 'bytes' => 0];
        }
        $completa = realpath($raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta));
        $valida = $completa && strpos($completa, $raiz . DIRECTORY_SEPARATOR) === 0 && is_file($completa);
        return ['existe' => (bool) $valida, 'bytes' => $valida ? filesize($completa) : 0];
    }

    $MFI = new Modelo_Firma();

    // Los trámites externos traen el documento de fuera: no se firman aquí, solo se
    // verifica la firma que ya tengan (migración 021).
    $procedencia = $MFI->Procedencia($id) ?: 'INTERNO';

    // Archivos que ya tienen su copia "(firmado)": no se vuelven a ofrecer para
    // firmar, o se generarían duplicados del mismo contenido sin fin.
    $yaFirmados = $MFI->Origenes_Ya_Firmados($id);

    $principal = null;
    $documento = $MTR->Traer_Archivo_Principal($id);
    if ($documento && $documento['doc_archivo'] !== '') {
        $estado = estadoArchivo($raiz, $documento['doc_archivo']);
        $principal = [
            'ruta'       => $documento['doc_archivo'],
            'expediente' => $documento['doc_expediente'],
            'fecha'      => $documento['fecha_texto'],
            'existe'     => $estado['existe'],
            'bytes'      => $estado['bytes'],
            'reemplazado' => in_array($documento['doc_archivo'], $yaFirmados, true),
        ];
    }

    $anexos = $MTR->Listar_Anexos($id);
    $filas = $anexos['data'] ?? [];
    $firmas = $MFI->Firmas_Por_Anexo($id);
    foreach ($filas as &$fila) {
        $estado = estadoArchivo($raiz, $fila['anexo_ruta']);
        $fila['existe'] = $estado['existe'];
        $firma = $firmas[(int) $fila['anexo_id']] ?? null;
        $fila['firmas'] = $firma ? (int) $firma['total'] : 0;
        $fila['firmantes'] = $firma ? explode("\n", $firma['firmantes']) : [];
        $fila['es_pdf'] = (bool) preg_match('/\.pdf$/i', $fila['anexo_ruta']);
        $fila['reemplazado'] = in_array($fila['anexo_ruta'], $yaFirmados, true);
    }
    unset($fila);

    // Firma Perú (DNIe/token) se ofrece solo cuando la entidad configuró sus credenciales
    echo json_encode([
        'principal'   => $principal,
        'data'        => $filas,
        'procedencia' => $procedencia,
        'firma_peru'  => FirmaDigital::firmaPeruConfigurado(),
    ]);
