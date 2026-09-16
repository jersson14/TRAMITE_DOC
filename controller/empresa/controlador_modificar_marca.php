<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_conexion.php';

    $id = (int) ($_POST['id'] ?? 0);
    $sigla = trim((string) ($_POST['sigla'] ?? ''));
    $color = strtoupper(trim((string) ($_POST['color'] ?? '')));

    if ($id <= 0 || mb_strlen($sigla) > 60 || !preg_match('/^#[0-9A-F]{6}$/', $color)) {
        Seguridad::responderError(422, 'Revise la sigla (máximo 60 caracteres) y el color (formato #RRGGBB).');
    }

    $pdo = (new conexionBD())->conexionPDO();
    $consulta = $pdo->prepare('UPDATE empresa SET emp_sigla = ?, emp_color = ? WHERE empresa_id = ?');
    echo $consulta->execute([$sigla, $color, $id]) ? 1 : 0;
?>
