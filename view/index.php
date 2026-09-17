<?php
require_once __DIR__ . '/../lib/Seguridad.php';
require_once __DIR__ . '/../lib/Institucion.php';
Seguridad::iniciarSesion();
if (!Seguridad::autenticado()) {
  header('Location: ../index.php');
  exit;
}

// Marca de la institución y datos de quien está en sesión (los usan la barra
// superior y la ficha del menú). La foto de perfil de muchos empleados apunta a
// un archivo que no existe, así que se comprueba antes y si no está se muestran
// las iniciales en vez de una imagen rota.
$institucion = Institucion::datos();
$nombreUsuario = trim((string) ($_SESSION['S_NOMBRE'] ?? $_SESSION['S_APELLIDOS'] ?? ''));
$partesNombre = preg_split('/\s+/', $nombreUsuario, -1, PREG_SPLIT_NO_EMPTY) ?: [];
$iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? 'U', 0, 1) . mb_substr($partesNombre[1] ?? '', 0, 1));
$fotoUsuario = (string) ($_SESSION['S_FOTO'] ?? '');
$hayFoto = $fotoUsuario !== '' && is_file(__DIR__ . '/../' . $fotoUsuario);

// Hora del sistema en zona de Perú: la barra superior la muestra y el navegador
// solo la adelanta desde este valor.
$horaPeru = (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SISTRAMITE DOC</title>
  <meta name="csrf-token" content="<?php echo Seguridad::tokenCsrf(); ?>">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="../plantilla/plugins//fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="icon" href="../img/empre.jpg" type="image/jpg">

  <link rel="stylesheet" href="../plantilla/dist//css/adminlte.min.css">
  <!-- Modern Admin Theme CSS -->
  <link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../plantilla/dist/css/modern-admin-theme.css'); ?>">
  <link href="../utilitario/DataTables/datatables.min.css" type="text/css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">
    <?php if ($_SESSION['S_ROL'] == "Administrador") { ?>
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <!-- Hora del sistema (js/reloj.js): la pone el servidor en zona de Perú -->
          <li class="nav-item">
            <span class="reloj-barra" id="reloj_peru" title="Hora de Perú, según el servidor del sistema"
                  data-ahora="<?php echo $horaPeru; ?>"></span>
          </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Comunicados -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Comunicados">
              <i class="far fa-comments"></i>
              <span class="badge badge-danger navbar-badge" id="lbl_contador" style="display:none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right caja-notificaciones">
              <span class="dropdown-item dropdown-header">Comunicados</span>
              <div class="dropdown-divider"></div>
              <div id="div_cuerpo"></div>
              <div class="dropdown-divider"></div>
              <a href="#/comunicados" class="dropdown-item dropdown-footer"><b>Ver todos los comunicados</b></a>
            </div>
          </li>

          <!-- Pendientes de la institución -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Pendientes de atención">
              <i class="far fa-bell"></i>
              <span class="badge badge-warning navbar-badge" id="lbl_contador_pendientes" style="display:none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right caja-notificaciones">
              <span class="dropdown-item dropdown-header">Pendientes de la institución</span>
              <div class="dropdown-divider"></div>
              <div id="div_cuerpo_tramite"></div>
              <div class="dropdown-divider"></div>
              <a href="#/movimientos" class="dropdown-item dropdown-footer"><b>Ver todos los trámites</b></a>
            </div>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <?php if ($hayFoto) { ?><img src="../<?php echo htmlspecialchars($fotoUsuario, ENT_QUOTES); ?>" class="chip-usuario-foto" alt=""><?php } else { ?><span class="chip-usuario-foto chip-usuario-iniciales"><?php echo htmlspecialchars($iniciales); ?></span><?php } ?>
              <b><?php echo htmlspecialchars($nombreUsuario); ?></b>
              <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div class="dropdown-divider"></div>
              <a href="../controller/usuario/controlador_cerrar_sesion.php" class="dropdown-item">
                <i class="fas fa-power-off mr-2"></i><u><b>Cerrar Sesión</b></u>
              </a>
              <div class="dropdown-divider"></div>
            </div>
          </li>
        </ul>

      </nav>
    <?php
    }
    ?>
    <?php if ($_SESSION['S_ROL'] == "Secretario (a)") { ?>
      <!-- Navbar -->
      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <!-- Hora del sistema (js/reloj.js): la pone el servidor en zona de Perú -->
          <li class="nav-item">
            <span class="reloj-barra" id="reloj_peru" title="Hora de Perú, según el servidor del sistema"
                  data-ahora="<?php echo $horaPeru; ?>"></span>
          </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Comunicados -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Comunicados">
              <i class="far fa-comments"></i>
              <span class="badge badge-danger navbar-badge" id="lbl_contador" style="display:none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right caja-notificaciones">
              <span class="dropdown-item dropdown-header">Comunicados</span>
              <div class="dropdown-divider"></div>
              <div id="div_cuerpo"></div>
            </div>
          </li>

          <!-- Pendientes del área -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" title="Pendientes de mi área">
              <i class="far fa-bell"></i>
              <span class="badge badge-warning navbar-badge" id="lbl_contador_pendientes" style="display:none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right caja-notificaciones">
              <span class="dropdown-item dropdown-header">Pendientes de mi área</span>
              <div class="dropdown-divider"></div>
              <div id="div_cuerpo_tramite"></div>
              <div class="dropdown-divider"></div>
              <a href="#/recibidos" class="dropdown-item dropdown-footer"><b>Ver trámites recibidos</b></a>
            </div>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <?php if ($hayFoto) { ?><img src="../<?php echo htmlspecialchars($fotoUsuario, ENT_QUOTES); ?>" class="chip-usuario-foto" alt=""><?php } else { ?><span class="chip-usuario-foto chip-usuario-iniciales"><?php echo htmlspecialchars($iniciales); ?></span><?php } ?>
              <b><?php echo htmlspecialchars($nombreUsuario); ?></b>
              <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div class="dropdown-divider"></div>
              <a href="../controller/usuario/controlador_cerrar_sesion.php" class="dropdown-item">
                <i class="fas fa-power-off mr-2"></i><u><b>Cerrar Sesión</b></u>
              </a>
              <div class="dropdown-divider"></div>
            </div>
          </li>
        </ul>

      </nav>
    <?php
    }
    ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Marca de la institución: logo y sigla, no el banner del proveedor -->
      <a href="index.php" class="brand-link marca-institucion" title="<?php echo htmlspecialchars($institucion['razon'], ENT_QUOTES); ?>">
        <img src="../<?php echo htmlspecialchars($institucion['logo'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($institucion['razon'], ENT_QUOTES); ?>" class="marca-logo">
        <span class="marca-texto">
          <b><?php echo htmlspecialchars($institucion['sigla']); ?></b>
          <small>Trámite documentario</small>
        </span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Ficha de quién está usando el sistema -->
        <div class="user-panel panel-usuario">
          <div class="panel-usuario-cabecera">
            <?php if ($hayFoto) { ?>
              <img src="../<?php echo htmlspecialchars($fotoUsuario, ENT_QUOTES); ?>" class="panel-usuario-foto" alt="Foto de perfil">
            <?php } else { ?>
              <span class="panel-usuario-foto panel-usuario-iniciales"><?php echo htmlspecialchars($iniciales); ?></span>
            <?php } ?>
            <div class="panel-usuario-identidad">
              <span class="panel-usuario-saludo"><i class="fas fa-circle"></i> En sesión</span>
              <strong class="panel-usuario-nombre" title="<?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES); ?>"><?php echo htmlspecialchars($nombreUsuario); ?></strong>
            </div>
          </div>
          <dl class="panel-usuario-datos">
            <dt><i class="fas fa-user-shield"></i> Rol</dt>
            <dd><?php echo htmlspecialchars($_SESSION['S_ROL']); ?></dd>
            <dt><i class="fas fa-sitemap"></i> Área</dt>
            <dd title="<?php echo htmlspecialchars($_SESSION['S_AREA'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($_SESSION['S_AREA']); ?></dd>
          </dl>
        </div>
        <!-- Sidebar Menu -->
        <nav class="mt-1">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <li class="header text-center" style="color:#FFFFFF;background-color:Gray;"><b>GESTIÓN TRÁMITES</b></li>

            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <?php if ($_SESSION['S_ROL'] == "Administrador") { ?>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','tramite/view_tramite.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>
                    Trámite
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','tramite/view_movimiento.php')" class="nav-link">
                  <i class="nav-icon fas fa-file"></i>
                  <p>
                    Ver Movimientos
                  </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','empleado/view_empleado.php')" class="nav-link">
                  <i class="nav-icon fas fa-users"></i>
                  <p>
                    Empleado
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','area/view_area.php')" class="nav-link">
                  <i class="nav-icon fas fa-th"></i>
                  <p>
                    Área
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','tipo_documento/view_tipodocumento.php')" class="nav-link">
                  <i class="nav-icon fas fa-file"></i>
                  <p>
                    Tipo Documento
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','feriado/view_feriado.php')" class="nav-link">
                  <i class="nav-icon far fa-calendar-times"></i>
                  <p>
                    Feriados
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','bitacora/view_bitacora.php')" class="nav-link">
                  <i class="nav-icon fas fa-clipboard-list"></i>
                  <p>
                    Bitácora
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','rastreo/view_rastreo_admin.php')" class="nav-link">
                  <i class="nav-icon fas fa-search"></i>
                  <p>
                    Rastrear Trámite
                  </p>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','comunicado/view_comunicado.php')" class="nav-link">
                  <i class="nav-icon fas fa-bullhorn"></i>
                  <p>
                    Comunicados
                  </p>
                </a>
              </li>
              <li class="header text-center" style="color:#FFFFFF;background-color:Gray;"><b>REPORTE DE TRÁMITES</b></li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>
                    Reporte de Trámites
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','tramite/view_reporte_fecha_area.php')" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>Reporte por Fechas y Área
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','tramite/view_reporte_fecha_estado.php')" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>
                        Reporte por Fechas y Estado
                      </p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','tramite/view_reporte_fecha_tipodoc.php')" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>
                        Reporte por Fechas y Tipo de Documento
                      </p>
                    </a>
                  </li>

                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','reporte/view_reporte_plazos.php')" class="nav-link">
                      <i class="nav-icon fas fa-stopwatch"></i>
                      <p>Plazos y productividad por área</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="header text-center" style="color:#FFFFFF;background-color:Gray;"><b>CONFIGURACIÓN Y MANUAL</b></li>
              <li class="nav-item">
                <a onclick="cargar_contenido('contenido_principal','usuario/view_usuario.php')" class="nav-link">
                  <i class="nav-icon fas fa-user"></i>
                  <p>
                    Usuario
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','configuracion/view_configuracion.php')" class="nav-link">
                  <i class="nav-icon fas fa-sliders-h"></i>
                  <p>
                    Institución y asistente
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="" target="blank" onclick="" class="nav-link">
                  <i class="nav-icon fas fa-file"></i>
                  <p>
                    Manual de Usuario
                  </p>
                </a>
              </li>
            <?php
            }
            ?>
            <?php if ($_SESSION['S_ROL'] == "Secretario (a)") { ?>
              <li class="nav-item">
                <a href="#"
                  onclick="cargar_contenido(
             'contenido_principal',
             '<?php echo ($_SESSION['S_AREA'] == 'MESA DE PARTES')
                ? 'tramite_area/view_tramite_registro_mespa.php'
                : 'tramite_area/view_tramite_registro.php'; ?>'
           )"
                  class="nav-link">
                  <i class="nav-icon fas fa-plus"></i>
                  <p>Trámite Nuevo</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','tramite_area/view_tramite.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>Trámites Recibidos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','tramite_area/view_tramite_enviados.php')" class="nav-link">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>Documentos Enviados</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="cargar_contenido('contenido_principal','rastreo/view_rastreo.php')" class="nav-link">
                  <i class="nav-icon fas fa-search"></i>
                  <p>Rastrear Trámites</p>
                </a>
              </li>
              <li class="header text-center" style="color:#FFFFFF;background-color:Gray;"><b>REPORTE DE TRÁMITES</b></li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="nav-icon fas fa-file-signature"></i>
                  <p>
                    Reporte de Trámites
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','tramite_area/view_reporte_fecha_area.php')" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>Reporte por Fechas y Área</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','tramite_area/view_reporte_fecha_estado.php')" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>Reporte por Fechas y Estado</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','tramite_area/view_reporte_fecha_tipodoc.php')" class="nav-link">
                      <i class="nav-icon fas fa-file"></i>
                      <p>Reporte por Fechas y Tipo de Documento</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a onclick="cargar_contenido('contenido_principal','reporte/view_reporte_plazos.php')" class="nav-link">
                      <i class="nav-icon fas fa-stopwatch"></i>
                      <p>Plazos y productividad por área</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="header text-center" style="color:#FFFFFF;background-color:Gray;"><b>MANUAL</b></li>
              <li class="nav-item">
                <a href="" target="_blank" class="nav-link">
                  <i class="nav-icon fas fa-file"></i>
                  <p>Manual de Usuario</p>
                </a>
              </li>
            <?php } ?>

          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>
    <input type="text" id="txtprincipalid" value="<?php echo $_SESSION['S_ID']; ?>" hidden>
    <input type="text" id="txtprincipalusu" value="<?php echo $_SESSION['S_USU']; ?>" hidden>
    <input type="text" id="txtidprincipalarea" value="<?php echo $_SESSION['S_IDAREA']; ?>" hidden>
    <input type="text" id="txtprincipalrol" value="<?php echo $_SESSION['S_ROL']; ?>" hidden>
    <input type="text" id="txtprincipalarea" value="<?php echo $_SESSION['S_AREA']; ?>" hidden>
    <input type="text" id="txtfotoempresa" value="<?php echo $_SESSION['S_FOTO_EMPRESA']; ?>" hidden>
    <input type="text" id="txtrazon" value="<?php echo $_SESSION['S_RAZON']; ?>" hidden>


    <div class="content-wrapper" id="contenido_principal">


      <!-- Content Wrapper. Contains page content -->

      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0"><i class="fas fa-home"></i>
                <b>BIENVENIDOS AL SISTEMA</b>
              </h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">MENÚ</a></li>
                <li class="breadcrumb-item active">MENÚ PRINCIPAL</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->
      <?php if ($_SESSION['S_ROL'] == "Administrador") { ?>

        <!-- Indicadores de gestión (js/console_indicadores.js) -->
        <div class="content">
          <div class="container-fluid">
            <div id="panel_indicadores"></div>
          </div>
        </div>


          <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
      <?php
      }
      ?>
      <?php if ($_SESSION['S_ROL'] == "Secretario (a)") { ?>

        <!-- Tablero del área (js/console_indicadores.js) -->
        <div class="content">
          <div class="container-fluid">
            <div id="panel_indicadores"></div>
          </div>
        </div>

        <!-- /.content -->

        <!-- /.content-wrapper -->
      <?php
      }
      ?>
    </div>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
      <div class="p-3">
        <h5>Title</h5>
        <p>Sidebar content</p>
      </div>
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <!-- El año sale de la fecha: estaba fijo en 2025. El espacio de la derecha
         deja libre el botón del asistente, que antes tapaba la versión. -->
    <footer class="main-footer pie-sistema">
      <span class="pie-institucion">
        <strong><?php echo htmlspecialchars($institucion['sigla']); ?></strong> · Trámite documentario
      </span>
      <span class="pie-credito">
        <?php echo Institucion::PRODUCTO; ?> 1.0.0 · &copy; <?php echo date('Y'); ?>
        <a href="https://web.facebook.com/jerzhitho.cm/" target="_blank" rel="noopener">JCM</a>
      </span>
    </footer>
  </div>
  <!-- ./wrapper -->
  <!-- MODAL EDITAR HORARIO -->

  <div class="modal fade" id="modal_editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#1FA0E0;">
          <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>EDITAR HORARIO DE ATENCIÓN</b></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12 form-group" style="color:red">
              <h6><b>Campos Obligatorios (*)</b></h6>
            </div>
            <div class="col-12 form-group">
              <label for="">Hora Inicio(*):</label>
              <input type="text" class="form-control" id="txt_hora_inicio">
              <input type="text" id="txt_idhora" hidden>
            </div>
            <div class="col-12 form-group">
              <label for="">Hora Fin(*):</label>
              <input type="text" class="form-control" id="txt_hora_fin">

            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
          <button type="button" class="btn btn-success" onclick="Modificar_Horario()"><i class="fas fa-check"></i> Modificar</button>
        </div>
      </div>
    </div>
  </div>


  <!-- REQUIRED SCRIPTS -->
  <script>
    /*
     * Rutas de los módulos: cada módulo tiene su dirección (index.php#/recibidos),
     * así F5 vuelve al mismo módulo y funcionan Atrás/Adelante del navegador.
     * Solo se aceptan las rutas del rol de la sesión; cada vista además valida
     * el acceso en el servidor.
     */
    <?php
    if ($_SESSION['S_ROL'] == 'Administrador') {
      $rutas = [
        'tramites'            => ['tramite/view_tramite.php', 'Trámites'],
        'tramite-nuevo'       => ['tramite/view_tramite_registro.php', 'Nuevo trámite'],
        'movimientos'         => ['tramite/view_movimiento.php', 'Movimientos'],
        'empleados'           => ['empleado/view_empleado.php', 'Empleados'],
        'areas'               => ['area/view_area.php', 'Áreas'],
        'tipos-documento'     => ['tipo_documento/view_tipodocumento.php', 'Tipos de documento'],
        'feriados'            => ['feriado/view_feriado.php', 'Feriados'],
        'bitacora'            => ['bitacora/view_bitacora.php', 'Bitácora'],
        'rastreo'             => ['rastreo/view_rastreo_admin.php', 'Rastrear trámite'],
        'comunicados'         => ['comunicado/view_comunicado.php', 'Comunicados'],
        'reportes/por-area'   => ['tramite/view_reporte_fecha_area.php', 'Reporte por área'],
        'reportes/por-estado' => ['tramite/view_reporte_fecha_estado.php', 'Reporte por estado'],
        'reportes/por-tipo'   => ['tramite/view_reporte_fecha_tipodoc.php', 'Reporte por tipo de documento'],
        'reportes/plazos'     => ['reporte/view_reporte_plazos.php', 'Plazos y productividad'],
        'usuarios'            => ['usuario/view_usuario.php', 'Usuarios'],
        'configuracion'       => ['configuracion/view_configuracion.php', 'Configuración'],
      ];
    } else {
      $rutas = [
        'tramite-nuevo'       => [$_SESSION['S_AREA'] == 'MESA DE PARTES' ? 'tramite_area/view_tramite_registro_mespa.php' : 'tramite_area/view_tramite_registro.php', 'Nuevo trámite'],
        'recibidos'           => ['tramite_area/view_tramite.php', 'Trámites recibidos'],
        'enviados'            => ['tramite_area/view_tramite_enviados.php', 'Documentos enviados'],
        'rastreo'             => ['rastreo/view_rastreo.php', 'Rastrear trámites'],
        'reportes/por-area'   => ['tramite_area/view_reporte_fecha_area.php', 'Reporte por área'],
        'reportes/por-estado' => ['tramite_area/view_reporte_fecha_estado.php', 'Reporte por estado'],
        'reportes/por-tipo'   => ['tramite_area/view_reporte_fecha_tipodoc.php', 'Reporte por tipo de documento'],
        'reportes/plazos'     => ['reporte/view_reporte_plazos.php', 'Plazos y productividad'],
      ];
    }
    ?>
    var RUTAS = <?php echo json_encode($rutas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    var TITULO_BASE = document.title;
    var rutaActual = "";
    var moduloCargado = false;

    function rutaDeVista(vista) {
      for (var ruta in RUTAS) {
        if (RUTAS[ruta][0] === vista) return ruta;
      }
      return null;
    }

    function mostrarModulo(vista, ruta) {
      moduloCargado = true;
      rutaActual = ruta || "";
      $("#contenido_principal").load(vista);
      document.title = ruta ? RUTAS[ruta][1] + " | " + TITULO_BASE : TITULO_BASE;
      window.scrollTo(0, 0);

      // Menú: resalta el módulo abierto y despliega su grupo (Reportes)
      $(".nav-sidebar .nav-link").removeClass("active");
      var enlace = $(".nav-sidebar a").filter(function () {
        return (this.getAttribute("onclick") || "").indexOf(vista) !== -1;
      }).first();
      enlace.addClass("active");
      enlace.parents(".nav-treeview").closest(".nav-item").addClass("menu-open").children(".nav-link").addClass("active");

      // En el celular, el menú lateral se cierra al elegir un módulo
      if (window.innerWidth < 992) $("body").removeClass("sidebar-open").addClass("sidebar-collapse");
    }

    function cargar_contenido(id, vista) {
      if (id !== "contenido_principal") {
        $("#" + id).load(vista);
        return;
      }
      var ruta = rutaDeVista(vista);
      if (!ruta) {
        // Pantalla sin ruta propia (por ejemplo, un formulario interno): se abre sin cambiar la dirección
        history.replaceState(null, "", location.pathname + location.search);
        mostrarModulo(vista, null);
        return;
      }
      if (location.hash === "#/" + ruta) {
        mostrarModulo(vista, ruta); // mismo módulo: se vuelve a cargar
      } else {
        location.hash = "#/" + ruta; // el cambio de dirección lo abre (enrutar)
      }
    }

    function enrutar() {
      var hash = location.hash;
      // Para una dirección que termina en "#", location.hash devuelve "" (igual que sin ruta)
      if (!hash && location.href.slice(-1) === "#") {
        // Un enlace href="#" no es una ruta: se conserva la dirección del módulo abierto
        history.replaceState(null, "", location.pathname + location.search + (rutaActual ? "#/" + rutaActual : ""));
        return;
      }
      if (!hash) {
        // Sin ruta = inicio. El tablero se arma al cargar la página, por eso se recarga.
        if (moduloCargado) location.reload();
        return;
      }
      var ruta = decodeURIComponent(hash.replace(/^#\/?/, ""));
      if (!RUTAS[ruta]) {
        history.replaceState(null, "", location.pathname + location.search);
        if (moduloCargado) location.reload();
        return;
      }
      if (ruta !== rutaActual || !moduloCargado) mostrarModulo(RUTAS[ruta][0], ruta);
    }

    // Los enlaces href="#" (menú, panel del usuario) no deben cambiar la dirección a "#".
    // Se evita solo la navegación: los clics de AdminLTE y Bootstrap siguen funcionando.
    document.addEventListener("click", function (e) {
      var enlace = e.target.closest && e.target.closest('a[href="#"]');
      if (enlace) e.preventDefault();
    }, true);
    window.addEventListener("hashchange", enrutar);
    // jQuery y las vistas se cargan al final de la página: se enruta cuando el documento está listo
    document.addEventListener("DOMContentLoaded", function () { if (location.hash) enrutar(); });
    var idioma_espanol = {
      select: {
        rows: "%d fila seleccionada"
      },
      "sProcessing": "Procesando...",
      "sLengthMenu": "Mostrar _MENU_ registros",
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ning&uacute;n dato disponible en esta tabla",
      "sInfo": "Registros del (_START_ al _END_) total de _TOTAL_ registros",
      "sInfoEmpty": "Registros del (0 al 0) total de 0 registros",
      "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
      "sInfoPostFix": "",
      "sSearch": "Buscar:",
      "sUrl": "",
      "sInfoThousands": ",",
      "sLoadingRecords": "<b>No se encontraron datos</b>",
      "oPaginate": {
        "sFirst": "Primero",
        "sLast": "Último",
        "sNext": "Siguiente",
        "sPrevious": "Atras"
      },
      "oAria": {
        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
      }
    }

    function sololetras(e) {
      key = e.keyCode || e.which;

      teclado = String.fromCharCode(key).toLowerCase();

      letras = "qwertyuiopasdfghjklñzxcvbnmáéíóú ";

      especiales = "8-37-38-46-164";

      teclado_especial = false;

      for (var i in especiales) {
        if (key == especiales[i]) {
          teclado_especial = true;
          break;
        }
      }

      if (letras.indexOf(teclado) == -1 && !teclado_especial) {
        return false;
      }
    }


    function soloNumeros(e) {
      tecla = (document.all) ? e.keyCode : e.which;
      if (tecla == 8) {
        return true;
      }
      // Patron de entrada, en este caso solo acepta numeros
      patron = /[0-9]/;
      tecla_final = String.fromCharCode(tecla);
      return patron.test(tecla_final);
    }



    ///////VALIDAR EMAIL
    function validar_email(email) {
      var regex = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return regex.test(email) ? true : false;
    }
  </script>
  <!-- jQuery -->
  <script src="../plantilla/plugins//jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="../plantilla/plugins//bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../plantilla/dist/js/adminlte.min.js"></script>
  <script>
    // Todas las peticiones AJAX llevan el token CSRF; los errores de acceso se tratan en un solo lugar
    $.ajaxSetup({ headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content } });
    $(document).ajaxError(function (evento, xhr) {
      if (xhr.status === 401) {
        window.location.href = '../index.php';
        return;
      }
      if ([403, 419, 422, 429].indexOf(xhr.status) !== -1) {
        let mensaje = (xhr.responseJSON && xhr.responseJSON.mensaje) || 'No se pudo completar la operación.';
        Swal.fire('Atención', mensaje, 'warning');
      }
    });
  </script>
  <script src="../js/formato.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/anexos.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/firma.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/filtros_bandeja.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/reloj.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_indicadores.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_notificaciones.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/exportaciones.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_comunicados_alerta.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_comunicados.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_empleado.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_tramite.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_usuario.js?rev=<?php echo time(); ?>"></script>

  <script src="../utilitario/DataTables/datatables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>

</html>
<script>
  <?php if ($_SESSION['S_ROL'] == "Secretario (a)") { ?>
  <?php
  }
  ?>
</script>

<!-- Alerta de comunicados (js/console_comunicados_alerta.js) -->
<div class="modal fade" id="modal_comunicado_alerta" tabindex="-1" role="dialog" aria-labelledby="comunicado_titulo" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#1E3A5F; color:#fff;">
        <h5 class="modal-title"><i class="fas fa-bullhorn mr-2"></i><span id="comunicado_contador">Comunicado</span></h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" title="Cerrar; volverá a mostrarse">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h4 id="comunicado_titulo" class="comunicado-titulo"></h4>
        <div class="comunicado-datos">
          <span><i class="far fa-calendar-alt"></i> <span id="comunicado_fecha"></span></span>
          <span><i class="fas fa-users"></i> <span id="comunicado_destino"></span></span>
          <span id="comunicado_vigencia"></span>
        </div>
        <a id="comunicado_imagen_enlace" class="comunicado-imagen" href="#" target="_blank" rel="noopener" hidden>
          <img id="comunicado_imagen" alt="Imagen del comunicado">
        </a>
        <div id="comunicado_texto" class="comunicado-texto"></div>
        <a id="comunicado_enlace" class="btn btn-archivo mt-3" href="#" target="_blank" rel="noopener" hidden>
          <i class="fas fa-external-link-alt"></i> Ver más información
        </a>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Ver después</button>
        <button type="button" class="btn btn-success" id="comunicado_boton" onclick="Confirmar_Comunicado()">
          <i class="fas fa-check"></i> Entendido
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Chat Widget CSS -->
<link rel="stylesheet" href="../plantilla/dist/css/chat_widget.css?v=<?php echo time(); ?>">

<!-- Chat Widget Component -->
<?php include 'components/chat_widget.php'; ?>

<!-- Chat Widget JavaScript -->
<script src="../js/chat_assistant.js?v=<?php echo time(); ?>"></script>

</body>
</html>
