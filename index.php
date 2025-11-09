<?php
  session_start();
  if(isset($_SESSION['S_ID'])){
    header('Location: view/index.php');
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión | EPS EMUSAP ABANCAY</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="plantilla/dist/css/adminlte.min.css">
    <link rel="icon" href="img/emusap.jpg" type="image/jpg">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-50px, -50px); }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1100px;
            margin: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.8s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Left Side - Branding */
        .login-brand {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-brand::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            right: -150px;
        }
        
        .login-brand::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
        }
        
        .brand-logo {
            position: relative;
            z-index: 1;
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 2rem;
            width: 200px;
        }
        
        .brand-logo img {
            width: 100%;
            height: auto;
        }
        
        .brand-title {
            position: relative;
            z-index: 1;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            line-height: 1.3;
        }
        
        .brand-subtitle {
            position: relative;
            z-index: 1;
            font-size: 1.1rem;
            opacity: 0.95;
            line-height: 1.6;
        }
        
        .brand-features {
            position: relative;
            z-index: 1;
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            text-align: left;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            background: white;
            color: #667eea;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .feature-text {
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        /* Right Side - Login Form */
        .login-form-container {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 800;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .form-subtitle {
            color: #718096;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.1rem;
            transition: color 0.3s ease;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
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
        
        .form-control-custom:focus + .input-icon {
            color: #667eea;
        }
        
        .toggle-password {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .toggle-password:hover {
            color: #667eea;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .remember-me label {
            color: #4a5568;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .forgot-password:hover {
            color: #5568d3;
        }
        
        .btn-login {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login i {
            font-size: 1.2rem;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 2rem 0;
            color: #a0aec0;
            font-size: 0.9rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            padding: 0 1rem;
        }
        
        .quick-links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .quick-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #4a5568;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .quick-link:hover {
            border-color: #667eea;
            background: white;
            color: #667eea;
            transform: translateX(5px);
        }
        
        .quick-link i {
            font-size: 1.3rem;
        }
        
        @media (max-width: 768px) {
            .login-container {
                grid-template-columns: 1fr;
                margin: 1rem;
            }
            
            .login-brand {
                padding: 2rem;
            }
            
            .brand-title {
                font-size: 1.5rem;
            }
            
            .brand-features {
                display: none;
            }
            
            .login-form-container {
                padding: 2rem;
            }
            
            .form-title {
                font-size: 1.6rem;
            }
        }
        
        /* Loading Animation */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-login.loading i {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-brand">
            <div class="brand-logo">
                <img src="img/emusap.jpg" alt="EPS EMUSAP Logo">
            </div>
            <h1 class="brand-title">Sistema de Trámite Documentario Web</h1>
            <p class="brand-subtitle">Gestión eficiente y moderna de documentos administrativos</p>
            
            <div class="brand-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="feature-text">Acceso 24/7 a tus trámites</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-text">Sistema seguro y confiable</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div class="feature-text">Seguimiento en tiempo real</div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="login-form-container">
            <div class="form-header">
                <h2 class="form-title">¡Bienvenido!</h2>
                <p class="form-subtitle">Ingresa tus credenciales para acceder al sistema</p>
            </div>
            
            <div>
                <div class="form-group">
                    <label class="form-label">Usuario</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-user"></i>
                        <input type="text" 
                               class="form-control-custom" 
                               id="txt_usuario" 
                               placeholder="Ingrese su usuario"
                               autocomplete="username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contraseña</label>
                    <div class="input-wrapper">
                        <i class="input-icon fas fa-lock"></i>
                        <input type="password" 
                               class="form-control-custom" 
                               id="txt_contra" 
                               placeholder="Ingrese su contraseña"
                               autocomplete="current-password">
                        <i class="toggle-password fas fa-eye" id="togglePassword"></i>
                    </div>
                </div>
                
                <div class="form-options">
                    <div class="remember-me">
                        <input type="checkbox" id="remember">
                        <label for="remember">Recuérdame</label>
                    </div>
                </div>
                
                <button class="btn-login" id="entrar" onclick="Iniciar_Sesion()">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                </button>
            </div>
            
            <div class="divider">
                <span>O accede a</span>
            </div>
            
            <div class="quick-links">
                <a href="seguimiento.php" class="quick-link">
                    <i class="fas fa-search" style="color: #10b981;"></i>
                    <span>Rastrear Trámite</span>
                </a>
                <a href="registrar.php" class="quick-link">
                    <i class="fas fa-file-signature" style="color: #667eea;"></i>
                    <span>Registrar Nuevo Trámite</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="plantilla/plugins/jquery/jquery.min.js"></script>
    <script src="plantilla/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plantilla/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/console_usuario.js"></script>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('txt_contra');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Remember me functionality
        const rmcheck = document.getElementById('remember');
        const usuarioInput = document.getElementById('txt_usuario');
        const passInput = document.getElementById('txt_contra');
        
        if(localStorage.checkbox && localStorage.checkbox != "") {
            rmcheck.setAttribute("checked", "checked");
            usuarioInput.value = localStorage.usuario;
            passInput.value = localStorage.pass;
        } else {
            rmcheck.removeAttribute("checked");
            usuarioInput.value = "";
            passInput.value = "";
        }

        // Focus on username input
        txt_usuario.focus();

        // Enter key to submit
        passwordInput.addEventListener("keyup", function(event) {
            if (event.keyCode === 13) {
                event.preventDefault();
                document.getElementById("entrar").click();
            }
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
    </script>
</body>
</html>