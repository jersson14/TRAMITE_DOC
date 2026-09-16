<?php
    require_once __DIR__ . '/../_guard.php';
    require_once __DIR__ . '/../../lib/Plazos.php';
    require '../../model/model_tramite.php';
    $MTR = new Modelo_Tramite();

    header('Content-Type: application/json; charset=utf-8');

    $id = strtoupper(trim((string) ($_POST['id'] ?? '')));
    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        echo json_encode(['data' => []]);
        exit;
    }
    if (!Seguridad::esAdmin() && !$MTR->Area_Puede_Ver($id, Seguridad::areaId())) {
        Seguridad::responderError(403, 'No tiene acceso a este trámite.');
    }

    $filas = $MTR->Listar_Atenciones($id);

    // Semáforo de cada atención: su propio plazo, desde que se pidió
    $hoy = new DateTimeImmutable('today');
    foreach ($filas as &$f) {
        $f['plazo_limite'] = null;
        $f['plazo_restante'] = null;
        if ($f['respuesta_fecha']) {
            $f['semaforo'] = 'ATENDIDO';
            continue;
        }
        $plazo = (int) $f['plazo'];
        if ($plazo <= 0) {
            $f['semaforo'] = Plazos::SIN_PLAZO;
            continue;
        }
        $inicio = (new DateTimeImmutable($f['fecha']))->setTime(0, 0);
        $limite = Plazos::sumarHabiles($inicio, $plazo);
        $f['plazo_limite'] = $limite->format('d/m/Y');
        if ($hoy > $limite) {
            $f['plazo_restante'] = -Plazos::habilesEntre($limite, $hoy);
            $f['semaforo'] = Plazos::ROJO;
        } else {
            $f['plazo_restante'] = Plazos::habilesEntre($hoy, $limite);
            $f['semaforo'] = $f['plazo_restante'] <= 1 ? Plazos::AMBAR : Plazos::VERDE;
        }
    }
    unset($f);

    echo json_encode(['data' => $filas]);
