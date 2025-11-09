<?php
session_start();
if (!isset($_SESSION['S_ID'])) {
  header('Location: ../index.php');
}
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
  <title>EPS EMUSAP ABANCAY SA</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="../plantilla/plugins//fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="icon" href="../img/emusap.jpg" type="image/jpg">

  <link rel="stylesheet" href="../plantilla/dist//css/adminlte.min.css">
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
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Notifications Dropdown Menu -->
          <li class="nav-item dropdown" style="text-align:justify;">
            <a class="nav-link" data-toggle="dropdown" href="#" style="text-align:justify;">
              <i class="far fa-comments" title="Comunicados"></i>
              <span class="badge badge-danger navbar-badge" id="lbl_contador" style="text-align:justify"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="text-align:justify">
              <div id="div_cuerpo" style="text-align:justify; border: 1px solid #333333;width: 100%;font-size: 100%;overflow-x: scroll;">
              </div>


              <div class="dropdown-divider"></div>
              <a href="" class="dropdown-item dropdown-footer" onclick="listar_comunicado_dash()"><b><u>Ver Comunicados</u></b></a>
            </div>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-1" width="15" height="18">
              <b>Usuario: <?php echo $_SESSION['S_NOMBRE'] ?></b>
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
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
          <!-- Notifications Dropdown Menu -->
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <i class="far fa-comments" title="Comunicados"></i>
              <span class="badge badge-danger navbar-badge" id="lbl_contador"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div id="div_cuerpo" style="max-height: 300px; overflow-y: auto; padding: 10px;">
              </div>

              <div class="dropdown-divider"></div>
              <a href="#" class="dropdown-item dropdown-footer" onclick="listar_comunicado_dash(); return false;"><b><u>Ver Comunicados</u></b></a>
            </div>
          </li>
          
          <li class="nav-item">
            <span class="nav-link" style="padding: 0 5px;">|</span>
          </li>
          
          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <i class="far fa-bell" title="Documentos pendientes"></i>
              <span class="badge badge-warning navbar-badge" id="lbl_contador_pendientes"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
              <div id="div_cuerpo_tramite" style="max-height: 300px; overflow-y: auto; padding: 10px;">
              </div>
            </div>
          </li>

          <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
              <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-1" width="15" height="18">
              <b>Usuario: <?php echo $_SESSION['S_NOMBRE'] ?></b>
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
      <!-- Brand Logo -->
      <a href="index.php" class="brand-link">
        <img src="../img/emusap.jpg" alt="<?php echo $_SESSION['S_RAZON']; ?>" width="100%" height="auto">
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-1 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="../<?php echo $_SESSION['S_FOTO']; ?>" class="img-circle elevation-2" style="max-width: 100%;height: auto;">

          </div>
          <div class="info">
            <a style="text-align:center;" href="#" class="d-block"><i class="fa fa-circle text-success fa-0x"></i> ¡Hola!<br> <b style="color:white"><?php echo $_SESSION['S_APELLIDOS']; ?></b></a>
            <a style="text-align:center;margin:5px;color:white;font-size:15px" href="#" class="d-block">&nbsp;&nbsp;<b style="text-align:center"><i class="fa fa-user text-success fa-0x"></i><em> ROL: <?php echo $_SESSION['S_ROL']; ?></em></b></a>
            <a style="text-align:center;margin:5px;color:white;font-size:13px" href="#" class="d-block">&nbsp;&nbsp;<b style="text-align:center"><i class="fa fa-home text-success fa-0x"></i><em> ÁREA: <?php echo $_SESSION['S_AREA']; ?></em></b></a>

          </div>
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

        <!-- Main content -->
        <div class="content">
          <div class="container-fluid">
            <div class="row">
              <!-- /.col-md-6 -->
              <div class="col-lg-12">
                <div class="card-primary">
                  <div class="card-header">
                    <h5 class="m-0" style="font-family:cooper;text-align:center"><i class="fas fa-list-ol"></i> DATOS IMPORTANTES</b></h5>
                  </div>
                  <div class="card-body" style="background-color:white">
                    <div class="row">
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-info">
                          <div class="inner">
                            <b>Total de empleados</b>
                            <h3 id="total_empleados"><sup style="font-size: 20px"></sup></h3>

                          </div>
                          <div class="icon">
                            <i class="fas fa-users"></i>
                          </div>
                          <a href="#" onclick="cargar_contenido('contenido_principal','empleado/view_empleado.php')" class="small-box-footer"><b>Ver Empleados</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-warning">
                          <div class="inner">
                            <b>Nº De Documentos</b>
                            <h3 id="totaldocpendientes"><sup style="font-size: 20px"></sup></h3>

                          </div>
                          <div class="icon">
                            <i class="fas fa-file"></i>
                          </div>
                          <a href="#" onclick="cargar_contenido('contenido_principal','tramite/view_movimiento.php')" class="small-box-footer"><b>Documentos Pendientes</b>&nbsp;<i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-success">
                          <div class="inner">

                            <b>Nº De Documentos</b>
                            <h3 id="totaldocpaceptados"><sup style="font-size: 20px"></sup></h3>

                          </div>
                          <div class="icon">
                            <i class="fas fa-file"></i>
                          </div>
                          <a href="#" onclick="cargar_contenido('contenido_principal','tramite/view_movimiento.php')" class="small-box-footer"><b>Documentos Aceptados</b>&nbsp; <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                      <!-- ./col -->
                      <div class="col-lg-3 col-6">
                        <!-- small box -->
                        <div class="small-box bg-dark">
                          <div class="inner">
                            <b>Nº De Documentos</b>
                            <h3 id="totaldocfinalizado"><sup style="font-size: 20px"></sup></h3>

                          </div>
                          <div class="icon">
                            <i class="fas fa-file"></i>
                          </div>
                          <a href="#" onclick="cargar_contenido('contenido_principal','tramite/view_movimiento.php')" class="small-box-footer"><b>Documentos Finalizados</b>&nbsp; <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
              <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
          </div><!-- /.container-fluid -->
        </div>
        <div class="content">
          <div class="container-fluid">
            <div class="row">
              <!-- /.col-md-6 -->
              <div class="col-lg-12">
                <div class="card-primary">
                  <div class="card-header">
                    <h5 class="m-0" style="font-family:cooper;text-align:center"><i class="fas fa-bullhorn"></i><b> COMUNICADOS</b></h5>
                  </div>
                  <div class="table-responsive" style="text-align:center">
                    <div class="card-body">
                      <table id="tabla_comunicados_listar" class="table table-striped table-bordered" style="width:100%">
                        <thead style="background-color:#023D77;color:white;">
                          <tr>
                            <th style="text-align:center">Título</th>
                            <th style="text-align:center">Descripción</th>
                            <th style="text-align:center">Fecha</th>
                            <th style="text-align:center">Enlace</th>
                            <th style="text-align:center">Estado</th>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- /.col-md-6 -->
              </div>
              <!-- /.row -->
            </div><!-- /.container-fluid -->
          </div>
          <div class="content">
            <div class="container-fluid">
              <div class="row">
                <!-- /.col-md-6 -->
                <div class="col-lg-12">
                  <div class="card-primary">
                    <div class="card-header">
                      <h5 class="m-0" style="font-family:cooper;text-align:center"><i class="fas fa-bullhorn"></i><b> DATOS INSTITUCIÓN</b></h5>
                    </div>
                    <div class="table-responsive" style="text-align:center">
                      <div class="card-body">
                        <table id="tabla_empresa" class="table table-striped table-bordered" style="width:100%">
                          <thead style="background-color:#023D77;color:white;">
                            <tr>
                              <th style="text-align:center">Nro.</th>
                              <th style="text-align:center">Logo</th>
                              <th style="text-align:center">Nombre</th>
                              <th style="text-align:center">Email</th>
                              <th style="text-align:center">Código</th>
                              <th style="text-align:center">Teléfono</th>
                              <th style="text-align:center">Dirección</th>
                              <th style="text-align:center">Acciones</th>

                            </tr>
                          </thead>
                        </table>
                      </div>
                    </div>
                  </div>
                  <!-- /.col-md-6 -->
                </div>
                <!-- /.row -->
              </div><!-- /.container-fluid -->
            </div>
          </div>

          <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <div class="modal fade" id="modal_editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header" style="background-color:#1FA0E0;">
                <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>EDITAR DATOS DE LA INSTITUCIÒN</b></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-12 form-group" style="color:red">
                    <h6><b>Campos Obligatorios (*)</b></h6>
                  </div><br>
                  <div class="col-6 form-group">
                    <input type="text" id="txt_id_empresa" hidden>
                    <label for="">Nombre(*):</label>
                    <input type="text" class="form-control" id="txt_nombre" maxlenght="8">
                  </div>
                  <div class="col-6 form-group">
                    <label for="">Email(*):</label>
                    <input type="text" class="form-control" id="txt_email">
                  </div>
                  <div class="col-6 form-group">
                    <label for="">Código(*):</label>
                    <input type="text" class="form-control" id="txt_codigo">
                  </div>
                  <div class="col-6 form-group">
                    <label for="">Teléfono / Celular(*):</label>
                    <input type="text" class="form-control" id="txt_telefono" maxlenght="9" onkeypress="return soloNumeros(event)">
                  </div>
                  <div class="col-12 form-group">
                    <label for="">Dirección(*):</label>
                    <input type="text" class="form-control" id="txt_direccion">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Modificar_Empleado()"><i class="fas fa-check"></i> Modificar</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modal_editar_foto" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header" style="background-color:#1FA0E0;">
                <h5 class="modal-title" id="exampleModalLabel" style="color:white; text-align:center"><b>EDITAR FOTO DE LA INSTITUCIÓN: </b><label for="" id="lb_empresa"></label></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-12">
                    <input type="text" id="fotoactual" hidden>
                    <input type="text" id="txt_idempresa_foto" hidden>
                    <label for="checkboxSuccess2" style="align:justify;color:red">
                      OJO: Una vez cambiado el logo, tambien se cambiara el logo en los reportes y ticket.
                    </label>
                    <label for="">Subir Foto:</label>
                    <input class="form-control" type="file" id="txt_foto">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onclick="Modificar_Foto_Empresa()"><i class="fas fa-check"></i> Modificar</button>
              </div>
            </div>
          </div>
        </div>
      <?php
      }
      ?>
      <?php if ($_SESSION['S_ROL'] == "Secretario (a)") { ?>

        <!-- Main content -->

        <div class="content">
          <div class="container-fluid">
            <div class="row">
              <!-- /.col-md-6 -->
              <div class="col-lg-12">
                <div class="card-primary"><br>
                  <div class="card-header">

                    <h5 class="m-0" style="font-family:cooper;text-align:center"><i class="fas fa-bullhorn"></i><b> COMUNICADOS</b></h5>
                  </div>
                  <div class="table-responsive" style="text-align:center">
                    <div class="card-body">
                      <table id="tabla_comunicados_listar" class="table table-striped table-bordered" style="width:100%">
                        <thead style="background-color:#023D77;color:white;">
                          <tr>
                            <th style="text-align:center">Título</th>
                            <th style="text-align:center">Descripción</th>
                            <th style="text-align:center">Fecha</th>
                            <th style="text-align:center">Enlace</th>

                            <th style="text-align:center">Estado</th>
                          </tr>
                        </thead>
                      </table>
                <img src="../img/emusap.jpg" width="100%"><br>

                    </div>
                  </div>

                </div>
                <!-- /.col-md-6 -->
              </div>
              <!-- /.row -->
            </div><!-- /.container-fluid -->
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
    <footer class="main-footer">
      <!-- To the right -->
      <div class="float-right d-none d-sm-inline">
        <em>Versión 1.0.0</em>
      </div>
      <!-- Default to the left -->
      <strong>Copyright &copy; 2025 <a href="https://web.facebook.com/jerzhitho.cm/" target="_blank"><em>DESARROLLADO POR JCM</em></a></strong>
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
    function cargar_contenido(id, vista) {
      $("#" + id).load(vista);
    }
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
  <script src="../js/console_comunicados.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_empleado.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_tramite.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_usuario.js?rev=<?php echo time(); ?>"></script>
  <script src="../js/console_empresa.js?rev=<?php echo time(); ?>"></script>

  <script src="../utilitario/DataTables/datatables.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="../js/console_usuario.js?rev=<?php echo time(); ?>"></script>

</body>

</html>
<script>
  $(document).ready(function() {
    listar_comunicado_dash();
    listar_empresa();
    Total_empleados();
    Total_documentos_pendientes();
    Total_documentos_aceptados();
    Total_documentos_finalizado();
  });

  <?php if ($_SESSION['S_ROL'] == "Administrador") { ?>
    TraerNotificacionComunicado();

    function TraerNotificacionComunicado() {
      $.ajax({
        "url": "../controller/usuario/controlador_traer_notificacion_comunicado.php",
        type: 'POST',
      }).done(function(resp) {

        let data = JSON.parse(resp);
        document.getElementById('lbl_contador').innerHTML = data.length;
        let llenardata = "";
        if (data.length > 0) {
          let cadena = "";
          for (let i = 0; i < data.length; i++) {
            llenardata += '<a href="#" class="dropdown-item">' +
              '<div class="media">' +
              '<div class="media-body">' +
              '<h3 class="dropdown-item-title" >' +
              '<b>Título: </b>' + data[i][1] + '' +
              '<span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>' +
              '</h3>' +
              '<p class="text-sm"><b>Descripción: </b>' + data[i][2] + '</p>' +
              '<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i><b>Fecha: </b>' + data[i][4] + '</p>' +
              '</div>' +
              '</div>' +
              '</a>';
          }
          document.getElementById('div_cuerpo').innerHTML = llenardata;

        } else {
          document.getElementById('div_cuerpo').innerHTML = llenardata;

        }
      })
    }
    document.getElementById("txt_foto").addEventListener("change", () => {
      var fileName = document.getElementById("txt_foto").value;
      var idxDot = fileName.lastIndexOf(".") + 1;
      var extFile = fileName.substr(idxDot, fileName.length).toLowerCase();
      if (extFile == "jpg" || extFile == "jpeg" || extFile == "png") {
        //TO DO
      } else {
        Swal.fire("Mensaje de Advertencia", "Solo se aceptan imagenes - usted subio un archivo con extensión ." + extFile, "warning");
        document.getElementById("txt_foto").value = "";

      }
    })
  <?php
  }
  ?>
  <?php if ($_SESSION['S_ROL'] == "Secretario (a)") { ?>
    TraerNotificacionComunicado();

    function TraerNotificacionComunicado() {
      $.ajax({
        "url": "../controller/usuario/controlador_traer_notificacion_comunicado.php",
        type: 'POST',
      }).done(function(resp) {

        let data = JSON.parse(resp);
        document.getElementById('lbl_contador').innerHTML = data.length;
        let llenardata = "";
        if (data.length > 0) {
          let cadena = "";
          for (let i = 0; i < data.length; i++) {
            llenardata += '<a href="#" class="dropdown-item">' +
              '<div class="media">' +
              '<div class="media-body">' +
              '<h3 class="dropdown-item-title" >' +
              '<b>Título: </b>' + data[i][1] + '' +
              '<span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>' +
              '</h3>' +
              '<p class="text-sm"><b>Descripción: </b>' + data[i][2] + '</p>' +
              '<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i><b>Fecha: </b>' + data[i][4] + '</p>' +
              '</div>' +
              '</div>' +
              '</a>';
          }
          document.getElementById('div_cuerpo').innerHTML = llenardata;

        } else {
          document.getElementById('div_cuerpo').innerHTML = llenardata;

        }
      })
    }



    TraerNotificacionDocumentos();

    function TraerNotificacionDocumentos() {
      let idarea = document.getElementById('txtidprincipalarea').value;
      $.ajax({
        "url": "../controller/usuario/controlador_traer_notificacion_tramite.php",
        type: 'POST',
        data: {
          idarea: idarea
        }
      }).done(function(resp) {
        let data = JSON.parse(resp);
        document.getElementById('lbl_contador_pendientes').innerHTML = data.length;
        let llenardata = "";
        if (data.length > 0) {
          let cadena = "";
          for (let i = 0; i < data.length; i++) {
            llenardata += '<a href="#" class="dropdown-item">' +
              '<div class="media">' +
              '<div class="media-body">' +
              '<h3 class="dropdown-item-title" >' +
              '<b>DNI: </b>' + data[i][1] + '' +
              '<span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>' +
              '</h3>' +
              '<p class="text-sm"><b>Remitente: </b>' + data[i][2] + '</p>' +
              '<p class="text-sm"><b>Area Origén: </b>' + data[i][24] + '</p>' +
              '<p class="text-sm"><b>Asunto: </b>' + data[i][18] + '</p>' +
              '<p class="text-sm"><b>Estado: </b>' + data[i][8] + '</p>' +

              '<p class="text-sm text-muted"><i class="far fa-clock mr-1"></i><b>Fecha: </b>' + data[i][20] + '</p>' +
              '</div>' +
              '</div>' +
              '</a>';
          }
          document.getElementById('div_cuerpo_tramite').innerHTML = llenardata;

        } else {
          document.getElementById('div_cuerpo_tramite').innerHTML = llenardata;

        }
      })
    }

  <?php
  }
  ?>
</script>