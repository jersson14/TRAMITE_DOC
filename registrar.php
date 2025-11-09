<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mesa de Partes Virtual | EPS EMUSAP ABANCAY</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="plantilla/dist/css/adminlte.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        }
        
        .logo-container img {
            max-width: 180px;
            height: auto;
        }
        
        .navigation-bar {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            padding: 0.8rem 0;
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
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .welcome-title {
            color: #2d3748;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .welcome-subtitle {
            color: #4a5568;
            font-size: 1rem;
            line-height: 1.7;
            text-align: justify;
        }
        
        .alert-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }
        
        .alert-box i {
            color: #ffc107;
            margin-right: 0.5rem;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .card-header-custom.danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .card-body-custom {
            padding: 2rem;
        }
        
        .form-group label {
            color: #2d3748;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .required {
            color: #e53e3e;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .input-group-append .btn {
            border-radius: 0 10px 10px 0;
        }
        
        .btn-search {
            background: #667eea;
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-search:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .radio-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .radio-item {
            flex: 1;
            min-width: 150px;
        }
        
        .radio-item input[type="radio"] {
            margin-right: 0.5rem;
        }
        
        .radio-item label {
            cursor: pointer;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            display: block;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .radio-item input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .select2-container--default .select2-selection--single {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            height: 45px;
            padding: 0.5rem;
        }
        
        .info-box {
            background: #e6fffa;
            border-left: 4px solid #38b2ac;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .info-box i {
            color: #38b2ac;
            margin-right: 0.5rem;
        }
        
        .warning-box {
            background: #fff5f5;
            border-left: 4px solid #fc8181;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .warning-box i {
            color: #fc8181;
            margin-right: 0.5rem;
        }
        
/* REEMPLAZAR el CSS de .file-upload-wrapper en tu HTML con esto: */

.file-upload-wrapper {
    position: relative;
    border: 2px dashed #cbd5e0;
    border-radius: 10px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: #f7fafc;
}

.file-upload-wrapper:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 10;
}

.file-upload-content {
    pointer-events: none;
    position: relative;
    z-index: 1;
}

/* Mostrar nombre del archivo seleccionado */
.file-name-display {
    margin-top: 1rem;
    padding: 0.5rem;
    background: white;
    border-radius: 8px;
    font-size: 0.85rem;
    color: #667eea;
    font-weight: 500;
    display: none;
}

.file-name-display.show {
    display: block;
}
        
        .checkbox-custom {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 10px;
            margin: 1rem 0;
        }
        
        .checkbox-custom input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 0.75rem;
            cursor: pointer;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-submit.disabled {
            opacity: 0.5;
            cursor: not-allowed;
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
        
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 1.4rem;
            }
            
            .card-body-custom {
                padding: 1.5rem;
            }
            
            .radio-group {
                flex-direction: column;
            }
        }
        
    </style>
</head>

<body>
    <input type="text" id="dni" autocomplete="off" name="dni" hidden>

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
            <div class="d-flex justify-content-center flex-wrap">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Inicio
                </a>
                <a href="index.php" class="nav-link">
                    <i class="fas fa-user"></i> Iniciar Sesión
                </a>
                <a href="seguimiento.php" class="nav-link">
                    <i class="fas fa-search"></i> Seguimiento de Trámite
                </a>
                <a href="" target="_blank" class="nav-link">
                    <i class="fas fa-book"></i> Manual de Usuario
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-wrapper">
        <div class="main-container">
            <div class="container">
                <!-- Welcome Card -->
                <div class="welcome-card">
                    <h1 class="welcome-title">
                        <i class="fas fa-file-signature" style="color: #667eea;"></i>
                        Mesa de Partes Virtual
                    </h1>
                    <p class="welcome-subtitle">
                        La EPS EMUSAP ABANCAY pone a su disposición la <strong>Mesa de Partes Virtual</strong> 
                        para la recepción de documentos y solicitudes. Los documentos serán atendidos y recepcionados 
                        de manera eficiente. Es obligatorio registrar su correo electrónico para dar respuesta a su solicitud.
                    </p>
                    
                    <div class="alert-box">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Importante:</strong> Enviar los documentos en un solo archivo en formato PDF. 
                        El tamaño máximo no debe superar los 30MB.
                    </div>
                </div>

                <!-- Forms Row -->
                <div class="row">
                    <!-- Remitente Form -->
                    <div class="col-lg-6">
                        <div class="form-card">
                            <div class="card-header-custom">
                                <i class="fas fa-user"></i> Datos del Remitente
                            </div>
                            <div class="card-body-custom">
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    Los campos marcados con <span class="required">(*)</span> son obligatorios
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>N° DNI <span class="required">(*)</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="txt_dni" maxlength="8" 
                                                   placeholder="Ingrese su DNI" onkeypress="return soloNumeros(event)">
                                            <div class="input-group-append">
                                                <button class="btn btn-search" id="prueba">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Nombres <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_nom" 
                                               placeholder="Nombres" onkeypress="return sololetras(event)" disabled>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Apellido Paterno <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_apepat" 
                                               placeholder="Apellido Paterno" onkeypress="return sololetras(event)" disabled>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Apellido Materno <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_apemat" 
                                               placeholder="Apellido Materno" onkeypress="return sololetras(event)" disabled>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Celular <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_celular" maxlength="9"
                                               placeholder="999999999" onkeypress="return soloNumeros(event)">
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Email <span class="required">(*)</span></label>
                                        <input type="email" class="form-control" id="txt_email" 
                                               placeholder="correo@ejemplo.com">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label>Dirección <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_dire" 
                                               placeholder="Ingrese su dirección completa">
                                    </div>

                                    <div class="col-12">
                                        <label style="margin-bottom: 1rem;">En Representación</label>
                                        <div class="radio-group">
                                            <div class="radio-item">
                                                <input type="radio" checked value="A Nombre Propio" 
                                                       id="rad_presentacion1" name="r1" style="display:none">
                                                <label for="rad_presentacion1">A Nombre Propio</label>
                                            </div>
                                            <div class="radio-item">
                                                <input type="radio" id="rad_presentacion2" name="r1" 
                                                       value="A Otra Persona Natural" style="display:none">
                                                <label for="rad_presentacion2">Otra Persona Natural</label>
                                            </div>
                                            <div class="radio-item">
                                                <input type="radio" id="rad_presentacion3" name="r1" 
                                                       value="Persona Jurídica" style="display:none">
                                                <label for="rad_presentacion3">Persona Jurídica</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12" id="div_juridico" style="display:none">
                                        <div class="row mt-3">
                                            <div class="col-md-4 form-group">
                                                <label>RUC <span class="required">(*)</span></label>
                                                <input type="text" class="form-control" id="txt_ruc" maxlength="11"
                                                       placeholder="RUC" onkeypress="return soloNumeros(event)">
                                            </div>
                                            <div class="col-md-8 form-group">
                                                <label>Razón Social <span class="required">(*)</span></label>
                                                <input type="text" class="form-control" id="txt_razon" 
                                                       placeholder="Razón Social">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="warning-box mt-4">
                                    <i class="fas fa-shield-alt"></i>
                                    <strong>Declaración Jurada:</strong> El administrado garantiza la autenticidad e 
                                    integridad de los documentos presentados. El incumplimiento puede ser motivo de 
                                    denuncia penal por falsedad genérica e ideológica.
                                </div>

                                <div class="info-box">
                                    <i class="fas fa-phone"></i>
                                    <strong>Consultas:</strong> Para mayor información, comuníquese a nuestra 
                                    central telefónica al (083) 321117.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Form -->
                    <div class="col-lg-6">
                        <div class="form-card">
                            <div class="card-header-custom danger">
                                <i class="fas fa-file-alt"></i> Datos del Documento
                            </div>
                            <div class="card-body-custom">
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    Los campos marcados con <span class="required">(*)</span> son obligatorios
                                </div>

                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label>Tipo de Documento <span class="required">(*)</span></label>
                                        <select class="js-example-basic-single form-control" id="select_tipo" 
                                                style="width:100%">
                                            <option value="">Seleccione un tipo de documento</option>
                                        </select>
                                    </div>

                                    <div class="col-12 form-group">
                                        <label>Requisitos</label>
                                        <textarea class="form-control" id="txt_requisitos" readonly rows="3" 
                                                  style="resize:none; background: #fff3cd; border-color: #ffc107;">
                                        </textarea>
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation-circle"></i> 
                                            Los documentos como requisitos deben estar en un solo archivo junto al documento principal
                                        </small>
                                    </div>

                                    <div class="col-12 form-group">
                                        <label>N° de Registro / N° Documento <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_ndocumento" 
                                               placeholder="Número de documento" onkeypress="return soloNumeros(event)">
                                    </div>

                                    <div class="col-12 form-group">
                                        <label>Asunto <span class="required">(*)</span></label>
                                        <textarea class="form-control" id="txt_asunto" rows="4" 
                                                  style="resize:none" placeholder="Describa el asunto de su trámite...">
                                        </textarea>
                                    </div>

                                    <div class="col-12">
                                        <div class="info-box">
                                            <i class="fas fa-signature"></i>
                                            <strong>Firma Digital:</strong> 
                                            <a href="" target="_blank" style="color: #667eea; text-decoration: none;">
                                                Descargue el manual para realizar la firma digital con ReFirma PDF
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-md-8 form-group">
                                        <label>Adjuntar Documento <span class="required">(*)</span></label>
                                        <div class="file-upload-wrapper" onclick="document.getElementById('txt_archivo').click()">
                                            <div class="file-upload-content">
                                                <i class="fas fa-cloud-upload-alt fa-3x" style="color: #cbd5e0;"></i>
                                                <p style="margin: 1rem 0 0 0; color: #4a5568;">
                                                    <strong>Click para seleccionar archivo</strong>
                                                </p>
                                                <small style="color: #718096;">PDF | Máx. 30MB</small>
                                            </div>
                                            <input type="file" id="txt_archivo" accept=".pdf" style="display: none;">
                                            <div class="file-name-display" id="file-name-display"></div>
                                        </div>
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            El documento debe estar debidamente firmado, en formato PDF
                                        </small>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label>N° Folios <span class="required">(*)</span></label>
                                        <input type="text" class="form-control" id="txt_folio" maxlength="3"
                                               placeholder="000" onkeypress="return soloNumeros(event)">
                                    </div>

                                    <div class="col-12">
                                        <div class="checkbox-custom">
                                            <input type="checkbox" id="checkboxSuccess1" onclick="Validar_Informacion()">
                                            <label for="checkboxSuccess1" style="margin: 0; cursor: pointer;">
                                                Declaro bajo penalidad de perjurio, que toda la información proporcionada 
                                                es correcta y verídica.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 text-center mt-3">
                                        <button class="btn-submit" onclick="Registrar_Tramite()" id="btn_registro">
                                            <i class="fas fa-paper-plane"></i> REGISTRAR TRÁMITE
                                        </button>
                                    </div>
                                </div>
                            </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="plantilla/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plantilla/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/console_tramite_externo.js"></script>

    <script>
        // Focus en DNI al cargar
        txt_dni.focus();

        // Configuración inicial
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
                placeholder: 'Seleccione un tipo de documento',
                allowClear: true
            });

            $('#txt_dni').change(function() {
                valor = $(this).val();
                $('#dni').val(valor);
            });

            $("#select_tipo").change(function() {
                var id = $("#select_tipo").val();
                Traerrequisitotipodoc(id);
            });

            // Radio buttons
            $("#rad_presentacion1").on('click', function() {
                document.getElementById('div_juridico').style.display = "none";
            });
            
            $("#rad_presentacion2").on('click', function() {
                document.getElementById('div_juridico').style.display = "none";
            });
            
            $("#rad_presentacion3").on('click', function() {
                document.getElementById('div_juridico').style.display = "block";
            });

            Cargar_Select_Tipo();
        });

        // Búsqueda de DNI
        $("#prueba").click(function() {
            var dni = $("#dni").val();
            
            if(dni.length !== 8) {
                Swal.fire({
                    icon: 'warning',
                    title: 'DNI Inválido',
                    text: 'El DNI debe tener 8 dígitos',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            $.ajax({
                type: "POST",
                url: "consulta-dni-ajax.php",
                data: 'dni=' + dni,
                dataType: 'json',
                beforeSend: function() {
                    $("#prueba").html('<i class="fas fa-spinner fa-spin"></i>');
                },
                success: function(data) {
                    $("#prueba").html('<i class="fas fa-search"></i>');
                    
                    if(data == 1) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'El DNI tiene que tener 8 dígitos',
                            confirmButtonColor: '#667eea'
                        });
                    } else {
                        document.getElementById("txt_nom").value = data.nombres;
                        document.getElementById("txt_apepat").value = data.apellidoPaterno;
                        document.getElementById("txt_apemat").value = data.apellidoMaterno;
                        
                        // Habilitar campos
                        $("#txt_nom").prop('disabled', false);
                        $("#txt_apepat").prop('disabled', false);
                        $("#txt_apemat").prop('disabled', false);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Datos encontrados',
                            text: 'Se cargaron los datos correctamente',
                            confirmButtonColor: '#667eea',
                            timer: 2000
                        });
                    }
                },
                error: function() {
                    $("#prueba").html('<i class="fas fa-search"></i>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo consultar el DNI',
                        confirmButtonColor: '#667eea'
                    });
                }
            });
        });

        // Validación de checkbox
        Validar_Informacion();
        function Validar_Informacion() {
            if(document.getElementById('checkboxSuccess1').checked == false) {
                $("#btn_registro").addClass("disabled");
            } else {
                $("#btn_registro").removeClass("disabled");
            }
        }

        // Validación de archivo
        $('input[type="file"]').on('change', function() {
            var ext = $(this).val().split('.').pop();
            
            if($(this).val() != '') {
                if(ext == "PDF" || ext == "pdf") {
                    if($(this)[0].files[0].size > 31457280) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Archivo muy pesado',
                            text: 'El archivo no debe superar los 30 MB',
                            confirmButtonColor: '#667eea'
                        });
                        $("#txt_archivo").val("");
                        return;
                    }
                } else {
                    $("#txt_archivo").val("");
                    Swal.fire({
                        icon: 'error',
                        title: 'Formato no permitido',
                        text: 'Solo se permiten archivos PDF. Extensión detectada: ' + ext,
                        confirmButtonColor: '#667eea'
                    });
                }
            }
        });

        // Solo letras
        function sololetras(e) {
            key = e.keyCode || e.which;
            teclado = String.fromCharCode(key).toLowerCase();
            letras = "qwertyuiopasdfghjklñzxcvbnm ";
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

        // Limitar longitud de inputs
        var input = document.getElementById('txt_dni');
        input.addEventListener('input', function() {
            if (this.value.length > 8) 
                this.value = this.value.slice(0, 8); 
        });

        var input = document.getElementById('txt_celular');
        input.addEventListener('input', function() {
            if (this.value.length > 9) 
                this.value = this.value.slice(0, 9); 
        });

        var input = document.getElementById('txt_folio');
        input.addEventListener('input', function() {
            if (this.value.length > 3) 
                this.value = this.value.slice(0, 3); 
        });

        var input = document.getElementById('txt_ruc');
        input.addEventListener('input', function() {
            if (this.value.length > 11) 
                this.value = this.value.slice(0, 11); 
        });

        // Placeholder para Select2
        $('.js-example-basic-single').select2({
            theme: 'default',
            width: '100%',
            placeholder: 'Seleccione un tipo de documento',
            allowClear: true
        });
        // Mostrar nombre del archivo seleccionado
$('#txt_archivo').on('change', function() {
    var fileName = $(this).val().split('\\').pop();
    if(fileName) {
        $('#file-name-display').text('📄 ' + fileName).addClass('show');
    } else {
        $('#file-name-display').removeClass('show');
    }
});
    </script>
</body>
</html>