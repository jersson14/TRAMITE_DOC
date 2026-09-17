<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require_once __DIR__ . '/../../lib/Feriados.php';
    require '../../model/model_feriado.php';

    header('Content-Type: application/json; charset=utf-8');

    // Carga los feriados nacionales del año pedido. Los que ya existen no se tocan,
    // para no perder descripciones o tipos editados a mano.
    $MF = new Modelo_Feriado();
    $anio = (int) ($_POST['anio'] ?? 0);
    if ($anio < 2000 || $anio > 2100) {
        Seguridad::responderError(422, 'Indique un año entre 2000 y 2100.');
    }

    $nacionales = Feriados::nacionales($anio);
    $agregados = $MF->Cargar_Nacionales($anio, $nacionales);

    if ($agregados > 0) {
        Bitacora::registrar(Bitacora::REGISTRO, 'feriado', (string) $anio,
            'cargó ' . $agregados . ' feriados nacionales de ' . $anio);
    }

    echo json_encode([
        'status'    => 'ok',
        'agregados' => $agregados,
        'total'     => count($nacionales),
        'anio'      => $anio,
    ]);
