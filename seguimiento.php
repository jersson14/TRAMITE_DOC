<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seguimiento de Trámite | EPS EMUSAP ABANCAY</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="plantilla/dist/css/adminlte.min.css">
    <link rel="icon" href="img/emusap.jpg" type="image/jpg">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .header-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 2rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .logo-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            display: inline-block;
            max-width: 200px;
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
            color: #10b981;
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
            color: #667eea !important;
            transform: translateY(-2px);
        }
        
        .nav-link i {
            margin-right: 0.5rem;
        }
        
        .content-wrapper {
            background: transparent;
            padding: 2rem 0;
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
            border-left: 4px solid #667eea;
        }
        
        .search-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 2rem;
        }
        
        .card-header-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .btn-search {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-search:active {
            transform: translateY(0);
        }
        
        .btn-search i {
            font-size: 1.3rem;
        }
        
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 10px rgba(102, 126, 234, 0.3);
        }
        
        .timeline-content {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid #667eea;
        }
        
        .timeline-date {
            color: #667eea;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-action.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-action.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-action.success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
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
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .card-body-search,
            .card-body-results {
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn-action {
                width: 100%;
            }
            
            .timeline::before {
                left: 1rem;
            }
            
            .timeline-item {
                padding-left: 4rem;
            }
            
            .timeline-icon {
                left: 0.25rem;
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
                <img src="img/emusap.jpg" alt="EPS EMUSAP Logo">
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
                        <i class="fas fa-info-circle" style="color: #667eea; margin-right: 0.5rem;"></i>
                        La <strong>EPS EMUSAP ABANCAY SA</strong> pone a su disposición el <strong>Sistema de Seguimiento de Documentos</strong>, 
                        para realizar la búsqueda y rastreo de los diferentes documentos registrados en la plataforma. 
                        Debe ingresar correctamente el <strong>CÓDIGO DE SEGUIMIENTO</strong> y el <strong>DNI del remitente</strong> 
                        sin espacios en blanco para realizar la búsqueda correcta de su documento.
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
                                    Código de Seguimiento
                                    <span class="required">*</span>
                                </label>
                                <div class="input-wrapper">
                                    <i class="input-icon fas fa-hashtag"></i>
                                    <input type="text" 
                                           class="form-control-custom" 
                                           id="txt_numero" 
                                           placeholder="Ej: D0000001"
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
                        <!-- Timeline will be inserted here -->
                        <div id="div_seguimiento"></div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button class="btn-action primary" id="ticket">
                                <i class="fas fa-ticket-alt"></i>
                                <span>Imprimir Ticket de Atención</span>
                            </button>
                            <button class="btn-action success" id="seguimiento">
                                <i class="fas fa-print"></i>
                                <span>Imprimir Seguimiento</span>
                            </button>
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
                    <strong>Copyright © 2025
                        <a href="" target="_blank" 
                           style="color: #667eea; text-decoration: none;">
                            EPS EMUSAP ABANCAY SA

                        </a>
                    </strong>
                </p>
                <p style="margin: 0.5rem 0 0 0; color: #718096;">
                    <em>Versión 1.0.0</em>
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
    <script src="js/console_horario.js"></script>

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

        // Función de búsqueda con tu lógica original
        function Traer_Datos_Seguimiento() {
            let numero = document.getElementById('txt_numero').value;
            let dni = document.getElementById('txt_dni').value;
            
            if(numero.length == 0 || dni.length == 0) {
                return Swal.fire("Mensaje de Advertencia", "Llene el N° de Documento y DNI para buscar el documento", "warning");
            }

            // Mostrar loading
            $("#loading").addClass('show');
            $("#div_buscador").removeClass('show');
            
            $.ajax({
                "url": "controller/usuario/controlador_traer_seguimiento.php",
                type: 'POST',
                data: {
                    numero: numero,
                    dni: dni
                }
            }).done(function(resp) {
                $("#loading").removeClass('show');
                let data = JSON.parse(resp);
                var cadena = "";
                
                if(data.length > 0) {
                    document.getElementById("div_buscador").style.display = "block";
                    $("#div_buscador").addClass('show');
                    document.getElementById('lbl_titulo').innerHTML = "<b>Seguimiento del Trámite: " + data[0][0] + " - " + data[0][2] + "</b>";
                    
                    cadena += '<div class="timeline">';
                    cadena += '<div class="timeline-item">' +
                              '<div class="timeline-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">' +
                              '<i class="fas fa-calendar-alt"></i>' +
                              '</div>' +
                              '<div class="timeline-content">' +
                              '<div class="timeline-date"><i class="fas fa-calendar"></i> Fecha de Registro: ' + data[0][4] + '</div>' +
                              '<div class="timeline-title">Documento Registrado</div>' +
                              '<div class="timeline-description">El trámite ha sido registrado exitosamente en el sistema</div>' +
                              '</div>' +
                              '</div>';
                    
                    // AJAX PARA EL DETALLE DEL SEGUIMIENTO
                    $.ajax({
                        "url": "controller/usuario/controlador_traer_seguimiento_detalle.php",
                        type: 'POST',
                        data: {
                            codigo: data[0][0]
                        }
                    }).done(function(resp) {
                        let datadetalle = JSON.parse(resp);
                        
                        if(datadetalle.length > 0) {
                            for (let i = 0; i < datadetalle.length; i++) {
                                let iconClass = "fas fa-envelope";
                                let gradientColor = "";
                                let statusColor = "";
                                let statusText = "";
                                let actionText = "";
                                
                                if(datadetalle[i][7] == "DERIVADO") {
                                    iconClass = "fas fa-share";
                                    gradientColor = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);";
                                    statusColor = "#667eea";
                                    actionText = "DERIVADO";
                                    statusText = "El documento fue derivado al área de: <strong>" + datadetalle[i][3] + "</strong>";
                                } else if(datadetalle[i][7] == "RECHAZADO") {
                                    iconClass = "fas fa-times-circle";
                                    gradientColor = "background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);";
                                    statusColor = "#f5576c";
                                    actionText = "RECHAZADO";
                                    statusText = "El documento fue rechazado en el área de: <strong>" + datadetalle[i][3] + "</strong>";
                                } else if(datadetalle[i][7] == "FINALIZADO") {
                                    iconClass = "fas fa-check-circle";
                                    gradientColor = "background: linear-gradient(135deg, #10b981 0%, #059669 100%);";
                                    statusColor = "#10b981";
                                    actionText = "FINALIZADO";
                                    statusText = "El documento fue finalizado en el área de: <strong>" + datadetalle[i][3] + "</strong>";
                                } else {
                                    iconClass = "fas fa-clock";
                                    gradientColor = "background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);";
                                    statusColor = "#f59e0b";
                                    actionText = datadetalle[i][7];
                                    statusText = "El documento se encuentra en el área de: <strong>" + datadetalle[i][3] + "</strong>";
                                }
                                
                                cadena += '<div class="timeline-item">' +
                                          '<div class="timeline-icon" style="' + gradientColor + '">' +
                                          '<i class="' + iconClass + '"></i>' +
                                          '</div>' +
                                          '<div class="timeline-content" style="border-left-color: ' + statusColor + ';">' +
                                          '<div class="timeline-date"><i class="fas fa-clock"></i> ' + datadetalle[i][4] + '</div>' +
                                          '<div class="timeline-title" style="color: ' + statusColor + ';">' +
                                          '<i class="fas fa-info-circle"></i> Estado: ' + actionText +
                                          '</div>' +
                                          '<div class="timeline-description">' +
                                          '<p style="margin-bottom: 0.5rem;">' + statusText + '</p>' +
                                          '<p style="margin: 0; padding: 1rem; background: white; border-radius: 8px; border-left: 3px solid ' + statusColor + ';">' +
                                          '<i class="fas fa-comment-alt"></i> <strong>Observación:</strong><br>' +
                                          datadetalle[i][6] +
                                          '</p>' +
                                          '</div>' +
                                          '</div>' +
                                          '</div>';
                            }
                            
                            cadena += '</div>';
                            document.getElementById("div_seguimiento").innerHTML = cadena;
                        }
                    });
                } else {
                    document.getElementById("div_buscador").style.display = "none";
                    return Swal.fire("Mensaje de Advertencia", "No se encontraron datos del Documento Buscado", "warning");
                }
            }).fail(function() {
                $("#loading").removeClass('show');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo realizar la búsqueda. Intente nuevamente.',
                    confirmButtonColor: '#667eea'
                });
            });
        }

        // Imprimir ticket
        $('#ticket').click(function() {
            var codigo = $("#txt_numero").val();
            if (!codigo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin Código',
                    text: 'Primero debe realizar una búsqueda',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            window.open("view/MPDF/REPORTE/ticket_tramite.php?codigo=" + codigo + "#zoom=100%", 
                       "Ticket", "scrollbars=NO,width=800,height=600");
        });

        // Imprimir seguimiento
        $('#seguimiento').click(function() {
            var codigo = $("#txt_numero").val();
            if (!codigo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin Código',
                    text: 'Primero debe realizar una búsqueda',
                    confirmButtonColor: '#667eea'
                });
                return;
            }
            window.open("view/MPDF/REPORTE/ficha_seguimiento_automatico.php?codigo=" + codigo + "#zoom=100%", 
                       "Seguimiento", "scrollbars=NO,width=800,height=600");
        });

        // Focus en primer campo
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('txt_numero').focus();
        });
    </script>
</body>
</html>