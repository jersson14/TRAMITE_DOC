<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require_once __DIR__ . '/../../lib/Bitacora.php';
    Seguridad::iniciarSesion();
    // Se anota antes de cerrar: después ya no se sabe quién era.
    if (Seguridad::autenticado()) {
        Bitacora::registrar(Bitacora::SALIDA, 'usuario', (string) Seguridad::usuarioId());
    }
    Seguridad::cerrarSesion();
    header('Location: ../../index.php');
    exit;
