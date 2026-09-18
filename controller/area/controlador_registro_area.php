<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_area.php';
    $MA = new Modelo_Area();
    $area = strtoupper(htmlspecialchars($_POST['a'],ENT_QUOTES,'UTF-8'));
    $consulta = $MA->Registrar_Area($area);
    // 1 = registrada (el procedimiento devuelve 2 si el nombre ya existía)
    if ((string) $consulta === '1' && isset($_POST['sigla'])) {
        $MA->Guardar_Sigla(null, $area, (string) $_POST['sigla']);
    }
    echo $consulta;



?>