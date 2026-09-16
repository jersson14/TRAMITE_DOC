<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    require '../../model/model_usuario.php';
    Seguridad::iniciarSesion();
    header('Content-Type: application/json; charset=utf-8');

    $usu = htmlspecialchars(trim((string) ($_POST['u'] ?? '')), ENT_QUOTES, 'UTF-8');
    $con = htmlspecialchars((string) ($_POST['c'] ?? ''), ENT_QUOTES, 'UTF-8');
    if ($usu === '' || $con === '') {
        echo json_encode(['status' => 'vacio']);
        exit;
    }

    // Máximo 5 intentos fallidos cada 15 minutos por IP y usuario
    $clave = 'login|' . Seguridad::ipCliente() . '|' . mb_strtolower($usu);
    $espera = Seguridad::esperaIntentos($clave, 5, 900);
    if ($espera > 0) {
        echo json_encode(['status' => 'bloqueado', 'minutos' => (int) ceil($espera / 60)]);
        exit;
    }

    $MU = new Modelo_Usuario();
    $consulta = $MU->Verificar_Usuario($usu, $con);
    if (count($consulta) === 0) {
        Seguridad::registrarIntento($clave, 900);
        Bitacora::registrar(Bitacora::INGRESO_FALLIDO, 'usuario', null, 'credenciales incorrectas', $usu);
        echo json_encode(['status' => 'error']);
        exit;
    }
    if ($consulta[0]['usu_estatus'] === 'INACTIVO') {
        Bitacora::registrar(Bitacora::INGRESO_FALLIDO, 'usuario', null, 'usuario inactivo', $usu);
        echo json_encode(['status' => 'inactivo']);
        exit;
    }

    Seguridad::limpiarIntentos($clave);
    Seguridad::establecerUsuario($consulta[0]);
    Bitacora::registrar(Bitacora::INGRESO, 'usuario', (string) Seguridad::usuarioId());
    echo json_encode(['status' => 'ok']);
