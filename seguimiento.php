<?php
require_once __DIR__ . '/lib/Seguridad.php';
require_once __DIR__ . '/lib/Institucion.php';
$inst = Institucion::datos();
$e = [Seguridad::class, 'e'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de Trámite | <?= $e($inst['sigla']) ?></title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="plantilla/dist/css/adminlte.min.css">
    <link rel="icon" href="<?= $e($inst['logo']) ?>">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #1E3A5F;
            min-height: 100vh;
        }
        
        .header-banner {
            background: #1E3A5F;
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .logo-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            display: inline-block;
            max-width: 360px;
        }
        
        .logo-container img {
            width: 100%;
            height: auto;
        }
        
        .navigation-bar {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            padding: 0.8rem 0;
        }
        
        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #2d3748 !important;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .navbar-brand i {
            color: #15803D;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: #4a5568 !important;
            font-weight: 500;
            padding: 0.75rem 1.5rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin: 0 0.25rem;
        }
        
        .nav-link:hover {
            background: #f7fafc;
            color: #1E3A5F !important;
            transform: translateY(-2px);
        }
        
        .nav-link i {
            margin-right: 0.5rem;
        }
        
        .content-wrapper {
            background: transparent;
            padding: 2rem 0;
            /* Página pública sin menú lateral: AdminLTE reserva 250 px a la izquierda */
            margin-left: 0 !important;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-title {
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }
        
        .page-title h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .page-title p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .info-text {
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.8;
            text-align: justify;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 12px;
            border-left: 4px solid #1E3A5F;
        }
        
        .search-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 2rem;
        }
        
        .card-header-search {
            background: #1E3A5F;
            color: white;
            padding: 1.8rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .card-header-search i {
            font-size: 1.8rem;
        }
        
        .card-header-search h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .card-body-search {
            padding: 2.5rem;
        }
        
        .search-form {
            display: grid;
            gap: 2rem;
        }
        
        .form-group-custom {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .form-group-custom label {
            color: #2d3748;
            font-weight: 600;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group-custom label i {
            color: #1E3A5F;
        }
        
        .required {
            color: #e53e3e;
            font-size: 1.2rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1rem;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f7fafc;
        }
        
        .form-control-custom:focus {
            outline: none;
            border-color: #1E3A5F;
            background: white;
            box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.1);
        }
        
        .btn-search {
            background: #15803D;
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(21, 128, 61, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 128, 61, 0.4);
        }
        
        .btn-search:active {
            transform: translateY(0);
        }
        
        .btn-search i {
            font-size: 1.3rem;
        }
        
        /* Resumen de la consulta pública */
        .resumen-estado { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; border: 2px solid; border-radius: 14px; padding: 1.1rem 1.25rem; margin-bottom: 1.25rem; }
        .resumen-estado > i { font-size: 2rem; }
        .resumen-principal { flex: 1 1 220px; }
        .resumen-rotulo { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #718096; font-weight: 600; }
        .resumen-estado strong { display: block; font-size: 1.3rem; }
        .resumen-area { display: block; margin-top: 0.25rem; color: #4a5568; }
        .resumen-plazo { padding: 0.35rem 0.8rem; border-radius: 999px; font-weight: 600; font-size: 0.85rem; border: 1px solid; }
        .plazo-verde { color: #15803D; background: #F0FDF4; border-color: #15803D; }
        .plazo-ambar { color: #B45309; background: #FFFBEB; border-color: #B45309; }
        .plazo-rojo { color: #B91C1C; background: #FEF2F2; border-color: #B91C1C; }
        .plazo-gris { color: #4A5568; background: #F7FAFC; border-color: #CBD5E0; }
        .resumen-datos { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.75rem 1.25rem; margin: 0 0 1.75rem; }
        .resumen-datos .ancho { grid-column: 1 / -1; }
        .resumen-datos dt { font-size: 0.75rem; color: #718096; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
        .resumen-datos dd { margin: 0.1rem 0 0; color: #1a202c; font-weight: 500; overflow-wrap: anywhere; }
        .recorrido-titulo { font-size: 1.05rem; font-weight: 700; color: #1E3A5F; margin: 0 0 1rem; }
        .movimiento-areas { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-bottom: 0.5rem; }
        .movimiento-acuse { color: #15803D; font-weight: 600; margin: 0 0 0.5rem; }
        .movimiento-sin-acuse { color: #718096; margin: 0 0 0.5rem; }
        .movimiento-nota { margin: 0; padding: 0.75rem 1rem; background: #fff; border-radius: 8px; color: #4a5568; overflow-wrap: anywhere; }
        a.btn-action { text-decoration: none; }

        .results-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 2rem;
            display: none;
        }
        
        .results-card.show {
            display: block;
            animation: slideIn 0.5s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-header-results {
            background: #1E3A5F;
            color: white;
            padding: 1.8rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .card-header-results i {
            font-size: 1.8rem;
        }
        
        .card-header-results h2 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .card-body-results {
            padding: 2.5rem;
        }
        
        .timeline {
            position: relative;
            padding: 2rem 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 2rem;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #1E3A5F;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 5rem;
            margin-bottom: 2rem;
        }
        
        .timeline-icon {
            position: absolute;
            left: 0.75rem;
            width: 2.5rem;
            height: 2.5rem;
            background: #1E3A5F;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(30, 58, 95, 0.3);
        }
        
        .timeline-content {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #1E3A5F;
        }
        
        .timeline-date {
            color: #1E3A5F;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .timeline-title {
            color: #2d3748;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .timeline-description {
            color: #4a5568;
            line-height: 1.6;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            flex: 1;
            min-width: 200px;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .btn-action.primary {
            background: #1E3A5F;
            color: white;
            box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3);
        }
        
        .btn-action.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 58, 95, 0.4);
        }
        
        .btn-action.success {
            background: #15803D;
            color: white;
            box-shadow: 0 4px 15px rgba(21, 128, 61, 0.3);
        }
        
        .btn-action.success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(21, 128, 61, 0.4);
        }
        
        .footer {
            background: white;
            padding: 2rem 0;
            box-shadow: 0 -2px 15px rgba(0,0,0,0.08);
            margin-top: 3rem;
        }
        
        .footer-content {
            text-align: center;
            color: #4a5568;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 3rem;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #e2e8f0;
            border-top-color: #1E3A5F;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        
        /* Tablets (768px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .main-container {
                max-width: 95%;
            }
            
            .card-body-search,
            .card-body-results {
                padding: 2rem;
            }
        }
        
        /* Móviles (max-width: 768px) */
        @media (max-width: 768px) {
            .header-banner {
                padding: 1.5rem 0;
            }
            
            .logo-container {
                max-width: 150px;
                padding: 1rem;
            }
            
            .navigation-bar {
                padding: 0.5rem 0;
            }
            
            .navbar-brand {
                font-size: 1rem;
            }
            
            .nav-link {
                padding: 0.6rem 1rem !important;
                font-size: 0.9rem;
                margin: 0.25rem;
            }
            
            .content-wrapper {
                padding: 1.5rem 0;
            }
            
            .page-title {
                margin-bottom: 1.5rem;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .page-title p {
                font-size: 1rem;
            }
            
            .info-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .info-text {
                font-size: 0.95rem;
                padding: 0.875rem;
            }
            
            .card-body-search,
            .card-body-results {
                padding: 1.5rem;
            }
            
            .card-header-search,
            .card-header-results {
                padding: 1.25rem 1.5rem;
            }
            
            .card-header-search h2,
            .card-header-results h2 {
                font-size: 1.2rem;
            }
            
            .card-header-search i,
            .card-header-results i {
                font-size: 1.4rem;
            }
            
            /* Inputs touch-friendly */
            .form-control-custom {
                padding: 1rem 1rem 1rem 3rem;
                font-size: 1rem;
                min-height: 48px;
            }
            
            .btn-search {
                padding: 1rem 1.5rem;
                font-size: 1rem;
                min-height: 48px;
            }
            
            /* Timeline más compacto */
            .timeline {
                padding: 1.5rem 0;
            }
            
            .timeline::before {
                left: 1rem;
            }
            
            .timeline-item {
                padding-left: 3.5rem;
                margin-bottom: 1.5rem;
            }
            
            .timeline-icon {
                left: 0.25rem;
                width: 2rem;
                height: 2rem;
                font-size: 1rem;
            }
            
            .timeline-content {
                padding: 1.25rem;
            }
            
            .timeline-title {
                font-size: 1rem;
            }
            
            .timeline-description {
                font-size: 0.9rem;
            }
            
            /* Grid de origen/destino responsive */
            .timeline-description div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
                gap: 0.75rem !important;
            }
            
            /* Botones de acción */
            .action-buttons {
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .btn-action {
                width: 100%;
                min-width: 100%;
                padding: 0.875rem 1.25rem;
                font-size: 0.95rem;
                min-height: 48px;
            }
        }
        
        /* Móviles pequeños (max-width: 480px) */
        @media (max-width: 480px) {
            .header-banner {
                padding: 1rem 0;
            }
            
            .logo-container {
                max-width: 120px;
                padding: 0.75rem;
            }
            
            .page-title h1 {
                font-size: 1.5rem;
            }
            
            .page-title p {
                font-size: 0.9rem;
            }
            
            .info-card {
                padding: 1.25rem;
            }
            
            .info-text {
                font-size: 0.85rem;
            }
            
            .card-body-search,
            .card-body-results {
                padding: 1.25rem;
            }
            
            .card-header-search,
            .card-header-results {
                padding: 1rem 1.25rem;
            }
            
            .card-header-search h2,
            .card-header-results h2 {
                font-size: 1.1rem;
            }
            
            .timeline-content {
                padding: 1rem;
            }
            
            .timeline-title {
                font-size: 0.95rem;
            }
            
            .timeline-description {
                font-size: 0.85rem;
            }
            
            .nav-link {
                font-size: 0.85rem;
                padding: 0.5rem 0.75rem !important;
            }
            
            .btn-action {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
            }
            
            .form-group-custom label {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <!-- Inputs ocultos -->
    <input type="text" class="form-control" id="txt_id" hidden>
    <input type="text" class="form-control" id="txt_fecha_inicio" hidden>
    <input type="text" class="form-control" id="txt_fecha_fin" hidden>

    <!-- Header Banner -->
    <div class="header-banner">
        <div class="container text-center">
            <div class="logo-container">
                <img src="<?= $e($inst['logo']) ?>" alt="<?= $e($inst['razon']) ?>">
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navigation-bar">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <a href="index.php" class="navbar-brand">
                    <i class="fas fa-search"></i>
                    <span>TRÁMITE VIRTUAL</span>
                </a>
                <div class="d-flex flex-wrap">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-user"></i> Iniciar Sesión
                    </a>
                    <a href="registrar.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i> Nuevo Trámite
                    </a>
                    <a href="" target="_blank" class="nav-link">
                        <i class="fas fa-book"></i> Manual de Usuario
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="main-container">
            <div class="container">
                <!-- Page Title -->
                <div class="page-title">
                    <h1>
                        <i class="fas fa-map-marked-alt"></i> Seguimiento de Trámite
                    </h1>
                    <p>Rastrea el estado de tu documento en tiempo real</p>
                </div>

                <!-- Info Card -->
                <div class="info-card">
                    <div class="info-text">
                        <i class="fas fa-info-circle" style="color: #1E3A5F; margin-right: 0.5rem;"></i>
                        <strong><?= $e($inst['sigla']) ?></strong> pone a su disposición la consulta del estado de sus trámites.
                        Ingrese el <strong>N° de expediente</strong> (por ejemplo EXP-2026-000123) o el <strong>código de seguimiento</strong>
                        que figura en su cargo de recepción, y el <strong>DNI del remitente</strong>.
                    </div>
                </div>

                <!-- Search Card -->
                <div class="search-card">
                    <div class="card-header-search">
                        <i class="fas fa-search"></i>
                        <h2>Buscador de Trámite</h2>
                    </div>
                    <div class="card-body-search">
                        <form class="search-form" onsubmit="event.preventDefault(); Traer_Datos_Seguimiento();">
                            <div class="form-group-custom">
                                <label>
                                    <i class="fas fa-barcode"></i>
                                    N° de expediente o código de seguimiento
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <i class="input-icon fas fa-hashtag"></i>
                                    <input type="text" 
                                           class="form-control-custom" 
                                           id="txt_numero" 
                                           placeholder="Ej.: EXP-2026-000123 o D0000123" autocomplete="off"
                                           required>
                                </div>
                            </div>

                            <div class="form-group-custom">
                                <label>
                                    <i class="fas fa-id-card"></i>
                                    Número de DNI
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <i class="input-icon fas fa-user"></i>
                                    <input type="text" 
                                           class="form-control-custom" 
                                           id="txt_dni" 
                                           placeholder="Ingrese 8 dígitos"
                                           maxlength="8"
                                           onkeypress="return soloNumeros(event)"
                                           required>
                                </div>
                            </div>

                            <button type="submit" class="btn-search">
                                <i class="fas fa-search"></i>
                                <span>Buscar Documento</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Loading -->
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p style="color: white; font-weight: 600; font-size: 1.1rem;">Buscando documento...</p>
                </div>

                <!-- Results Card -->
                <div class="results-card" id="div_buscador">
                    <div class="card-header-results">
                        <i class="fas fa-route"></i>
                        <h2 id="lbl_titulo">Seguimiento del Trámite</h2>
                    </div>
                    <div class="card-body-results">
                        <!-- Resumen del trámite -->
                        <div id="div_resumen"></div>

                        <h3 class="recorrido-titulo"><i class="fas fa-route"></i> Recorrido del trámite</h3>
                        <div id="div_seguimiento"></div>

                        <!-- Documentos para descargar -->
                        <div class="action-buttons">
                            <a class="btn-action primary" id="btn_cargo" href="#" target="_blank" rel="noopener">
                                <i class="fas fa-file-download"></i>
                                <span>Cargo de recepción</span>
                            </a>
                            <a class="btn-action success" id="btn_hoja" href="#" target="_blank" rel="noopener">
                                <i class="fas fa-print"></i>
                                <span>Hoja de seguimiento</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <p style="margin: 0;">
                    <strong>© <?= date('Y') ?> <?= $e($inst['razon']) ?></strong>
                </p>
                <p style="margin: 0.5rem 0 0 0; color: #718096;">
                    <em>Mesa de Partes Virtual · SISTRAMITE</em>
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="plantilla/plugins/jquery/jquery.min.js"></script>
    <script src="plantilla/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plantilla/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/console_usuario.js"></script>

    <script>
        // Limitar DNI a 8 dígitos
        var inputDni = document.getElementById('txt_dni');
        inputDni.addEventListener('input', function() {
            if (this.value.length > 8) 
                this.value = this.value.slice(0, 8); 
        });

        // Solo letras
        function sololetras(e) {
            key = e.keyCode || e.which;
            teclado = String.fromCharCode(key).toLowerCase();
            letras = "qwertyuiopasdfghjklñzxcvbnmáéíóú ";
            especiales = "8-37-38-46-164";
            teclado_especial = false;
            
            for(var i in especiales) {
                if(key == especiales[i]) {
                    teclado_especial = true;
                    break;
                }
            }
            
            if(letras.indexOf(teclado) == -1 && !teclado_especial) {
                return false;
            }
        }

        // Solo números
        function soloNumeros(e) {
            tecla = (document.all) ? e.keyCode : e.which;
            if (tecla == 8) {
                return true;
            }
            patron = /[0-9]/;
            tecla_final = String.fromCharCode(tecla);
            return patron.test(tecla_final);
        }

        function textoSeguro(valor) {
            return String(valor === null || valor === undefined ? '' : valor)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        var ESTILOS_ESTADO = {
            PENDIENTE:  { color: '#B45309', icono: 'fas fa-clock', texto: 'Pendiente de atención' },
            ACEPTADO:   { color: '#15803D', icono: 'fas fa-check', texto: 'Aceptado por el área' },
            DERIVADO:   { color: '#1E3A5F', icono: 'fas fa-share', texto: 'Derivado a otra área' },
            FINALIZADO: { color: '#2C5282', icono: 'fas fa-flag-checkered', texto: 'Finalizado' },
            RECHAZADO:  { color: '#B91C1C', icono: 'fas fa-times-circle', texto: 'Observado / rechazado' }
        };

        function textoPlazo(t) {
            if (t.plazo_semaforo === 'CERRADO') return { clase: 'gris', texto: 'Trámite cerrado' };
            if (t.plazo_semaforo === 'SIN_PLAZO' || !t.plazo_limite) return { clase: 'gris', texto: 'Sin plazo definido' };
            if (t.plazo_semaforo === 'ROJO') return { clase: 'rojo', texto: 'Plazo vencido el ' + t.plazo_limite };
            if (t.plazo_restante === 0) return { clase: 'ambar', texto: 'Vence hoy (' + t.plazo_limite + ')' };
            return { clase: t.plazo_semaforo === 'AMBAR' ? 'ambar' : 'verde', texto: 'Plazo hasta el ' + t.plazo_limite };
        }

        function Pintar_Consulta(r) {
            var t = r.tramite;
            var estilo = ESTILOS_ESTADO[t.estado] || ESTILOS_ESTADO.PENDIENTE;
            var plazo = textoPlazo(t);

            document.getElementById('lbl_titulo').textContent = 'Expediente ' + (t.expediente || t.codigo);

            document.getElementById('div_resumen').innerHTML =
                '<div class="resumen-estado" style="border-color:' + estilo.color + ';">' +
                    '<i class="' + estilo.icono + '" style="color:' + estilo.color + ';"></i>' +
                    '<div class="resumen-principal"><span class="resumen-rotulo">Estado actual</span>' +
                    '<strong style="color:' + estilo.color + ';">' + textoSeguro(t.estado_texto) + '</strong>' +
                    (t.area_actual && t.estado !== 'FINALIZADO' ? '<span class="resumen-area"><i class="fas fa-map-marker-alt"></i> Se encuentra en: <b>' + textoSeguro(t.area_actual) + '</b></span>' : '') +
                    '</div>' +
                    '<span class="resumen-plazo plazo-' + plazo.clase + '">' + textoSeguro(plazo.texto) + '</span>' +
                '</div>' +
                '<dl class="resumen-datos">' +
                    '<div><dt>N° de expediente</dt><dd>' + textoSeguro(t.expediente || '—') + '</dd></div>' +
                    '<div><dt>Código de seguimiento</dt><dd>' + textoSeguro(t.codigo) + '</dd></div>' +
                    '<div><dt>Registrado el</dt><dd>' + textoSeguro(t.fecha_registro) + '</dd></div>' +
                    (t.fecha_presentado
                        ? '<div><dt>Se considera presentado el</dt><dd>' + textoSeguro(t.fecha_presentado) + ' <small style="color:#B45309;">(llegó fuera del horario de atención)</small></dd></div>'
                        : '') +
                    '<div><dt>Documento</dt><dd>' + textoSeguro(t.tipo) + (t.numero ? ' N° ' + textoSeguro(t.numero) : '') + '</dd></div>' +
                    '<div class="ancho"><dt>Asunto</dt><dd>' + textoSeguro(t.asunto) + '</dd></div>' +
                    '<div><dt>Remitente</dt><dd>' + textoSeguro(t.remitente) + '</dd></div>' +
                    '<div><dt>Archivos</dt><dd>' + (1 + t.anexos) + ' (' + (t.anexos === 1 ? '1 anexo' : t.anexos + ' anexos') + ')</dd></div>' +
                '</dl>';

            var cadena = '<div class="timeline">' +
                '<div class="timeline-item"><div class="timeline-icon" style="background:#2C5282;"><i class="fas fa-inbox"></i></div>' +
                '<div class="timeline-content" style="border-left-color:#2C5282;">' +
                '<div class="timeline-date"><i class="fas fa-calendar"></i> ' + textoSeguro(t.fecha_registro) + '</div>' +
                '<div class="timeline-title" style="color:#2C5282;">Trámite registrado</div>' +
                '<div class="timeline-description">Se recibió el documento y se generó el expediente.</div></div></div>';

            r.movimientos.forEach(function (m) {
                var e = ESTILOS_ESTADO[m.estado] || ESTILOS_ESTADO.PENDIENTE;
                cadena +=
                    '<div class="timeline-item"><div class="timeline-icon" style="background:' + e.color + ';"><i class="' + e.icono + '"></i></div>' +
                    '<div class="timeline-content" style="border-left-color:' + e.color + ';">' +
                    '<div class="timeline-date"><i class="fas fa-clock"></i> ' + textoSeguro(m.fecha) + '</div>' +
                    '<div class="timeline-title" style="color:' + e.color + ';">' + textoSeguro(e.texto) + '</div>' +
                    '<div class="timeline-description">' +
                        (m.origen === m.destino
                            ? '<div class="movimiento-areas"><span><i class="fas fa-map-marker-alt"></i> En: <b>' + textoSeguro(m.destino) + '</b></span></div>'
                            : '<div class="movimiento-areas"><span><i class="fas fa-map-marker-alt"></i> De: <b>' + textoSeguro(m.origen) + '</b></span>' +
                              '<span><i class="fas fa-flag-checkered"></i> A: <b>' + textoSeguro(m.destino || '—') + '</b></span></div>') +
                        (m.recibido
                            ? '<p class="movimiento-acuse"><i class="fas fa-check-double"></i> Recibido por el área el ' + textoSeguro(m.recibido) + '</p>'
                            : (m.estado === 'PENDIENTE' ? '<p class="movimiento-sin-acuse"><i class="far fa-clock"></i> El área aún no confirma la recepción</p>' : '')) +
                        (m.descripcion ? '<p class="movimiento-nota">' + textoSeguro(m.descripcion) + '</p>' : '') +
                    '</div></div></div>';
            });
            cadena += '</div>';
            document.getElementById('div_seguimiento').innerHTML = cadena;

            document.getElementById('btn_cargo').href = r.cargo;
            document.getElementById('btn_hoja').href = r.hoja;

            var resultados = document.getElementById('div_buscador');
            resultados.style.display = 'block';
            resultados.classList.add('show');
            resultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function Traer_Datos_Seguimiento() {
            var numero = document.getElementById('txt_numero').value.trim();
            var dni = document.getElementById('txt_dni').value.trim();
            if (!numero || !dni) {
                return Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Ingrese el N° de expediente (o código) y el DNI del remitente.', confirmButtonColor: '#1E3A5F' });
            }
            if (!/^\d{8}$/.test(dni)) {
                return Swal.fire({ icon: 'warning', title: 'DNI no válido', text: 'El DNI debe tener 8 dígitos.', confirmButtonColor: '#1E3A5F' });
            }

            $('#loading').addClass('show');
            $('#div_buscador').removeClass('show').hide();

            $.ajax({
                url: 'controller/tramite/controlador_consulta_publica.php',
                type: 'POST',
                dataType: 'json',
                data: { numero: numero, dni: dni }
            }).done(function (r) {
                $('#loading').removeClass('show');
                if (!r.encontrado) {
                    return Swal.fire({ icon: 'info', title: 'No encontrado', text: r.mensaje, confirmButtonColor: '#1E3A5F' });
                }
                Pintar_Consulta(r);
            }).fail(function (xhr) {
                $('#loading').removeClass('show');
                var mensaje = (xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo realizar la búsqueda. Intente nuevamente.';
                Swal.fire({ icon: xhr.status === 422 || xhr.status === 429 ? 'warning' : 'error', title: 'No se pudo consultar', text: mensaje, confirmButtonColor: '#1E3A5F' });
            });
        }

        // Si se llega desde el QR o el cargo (?codigo=...), el número ya viene escrito
        // y el cursor pasa al DNI, que es lo único que falta.
        document.addEventListener('DOMContentLoaded', function() {
            var codigo = (new URLSearchParams(window.location.search).get('codigo') || '').toUpperCase().trim();
            if (/^[A-Z0-9-]{1,20}$/.test(codigo)) {
                document.getElementById('txt_numero').value = codigo;
                document.getElementById('txt_dni').focus();
            } else {
                document.getElementById('txt_numero').focus();
            }
        });
    </script>
</body>
</html>
