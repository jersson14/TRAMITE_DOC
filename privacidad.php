<?php
/**
 * Aviso de privacidad del portal ciudadano, en una página pública propia para
 * poder enlazarlo desde el registro, el seguimiento y los correos.
 * El texto se administra en Configuración → Aviso de privacidad.
 */
require_once __DIR__ . '/lib/Seguridad.php';
require_once __DIR__ . '/lib/Institucion.php';
require_once __DIR__ . '/lib/AvisoPrivacidad.php';

header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

$e = [Seguridad::class, 'e'];
$institucion = Institucion::datos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aviso de privacidad | <?= $e($institucion['sigla']) ?></title>
    <link rel="icon" href="<?= $e($institucion['logo']) ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <style>
        :root { --azul: #1E3A5F; --texto: #2D3748; --suave: #718096; --borde: #E2E8F0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: #F4F6F9; color: var(--texto); }
        header { background: var(--azul); color: #fff; }
        .barra { max-width: 820px; margin: 0 auto; padding: 1rem 16px; display: flex; align-items: center; gap: .85rem; }
        .barra img { width: 46px; height: 46px; object-fit: contain; background: #fff; border-radius: 8px; padding: 4px; }
        .barra b { display: block; font-size: 1rem; }
        .barra small { opacity: .85; }
        main { max-width: 820px; margin: 0 auto; padding: 1.5rem 16px 3rem; }
        h1 { font-size: 1.35rem; margin: 0 0 1rem; color: var(--azul); }
        .tarjeta { background: #fff; border: 1px solid var(--borde); border-radius: 10px; padding: 1.25rem 1.5rem; line-height: 1.6; }
        .tarjeta p { margin: 0 0 .9rem; overflow-wrap: anywhere; }
        .volver { display: inline-block; margin-top: 1rem; color: var(--azul); font-weight: 600; }
    </style>
</head>
<body>
<header>
    <div class="barra">
        <img src="<?= $e($institucion['logo']) ?>" alt="">
        <div><b><?= $e($institucion['razon']) ?></b><small>Mesa de partes virtual</small></div>
    </div>
</header>
<main>
    <h1>Aviso de privacidad</h1>
    <div class="tarjeta"><?= AvisoPrivacidad::html() ?></div>
    <a class="volver" href="registrar.php">&larr; Volver a la mesa de partes virtual</a>
</main>
</body>
</html>
