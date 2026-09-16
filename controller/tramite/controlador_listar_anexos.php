<?php
    require_once __DIR__ . '/../_guard.php';
    require '../../model/model_tramite.php';

    $MTR = new Modelo_Tramite();
    $id = strtoupper(htmlspecialchars(trim((string) ($_POST['id'] ?? '')), ENT_QUOTES, 'UTF-8'));

    header('Content-Type: application/json; charset=utf-8');

    // El código de seguimiento tiene un formato fijo; cualquier otra cosa no se consulta.
    if (!preg_match('/^[A-Z0-9\-]{1,12}$/', $id)) {
        echo json_encode(['data' => []]);
        exit;
    }

    $resultado = $MTR->Listar_Anexos($id);
    echo json_encode(empty($resultado) ? ['data' => []] : $resultado);
