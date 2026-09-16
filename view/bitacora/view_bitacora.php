<?php require_once __DIR__ . '/../../lib/Seguridad.php'; Seguridad::requiereVista([Seguridad::ROL_ADMIN]); ?>
<script src="../js/console_bitacora.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css">

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><b>BITÁCORA DEL SISTEMA</b></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
              <li class="breadcrumb-item active">BITÁCORA</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-12">
            <div class="card card-modern">
              <div class="card-header">
                <h3 class="card-title"><i class="nav-icon fas fa-clipboard-list"></i>&nbsp;&nbsp;<b>Registro de Actividad</b></h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 form-group">
                    <label for="" style="font-size:small;">Desde:</label>
                    <input type="date" class="form-control" id="txt_desde">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="" style="font-size:small;">Hasta:</label>
                    <input type="date" class="form-control" id="txt_hasta">
                  </div>
                  <div class="col-md-3 form-group">
                    <label for="" style="font-size:small;">Acción:</label>
                    <select class="form-control" id="cbo_accion">
                      <option value="">Todas</option>
                      <option value="INGRESO">Ingreso al sistema</option>
                      <option value="INGRESO_FALLIDO">Intento fallido de ingreso</option>
                      <option value="SALIDA">Cierre de sesión</option>
                      <option value="REGISTRO_TRAMITE">Registro de trámite</option>
                      <option value="DERIVO_TRAMITE">Derivación</option>
                      <option value="CAMBIO_ESTADO">Cambio de estado</option>
                      <option value="ELIMINO_TRAMITE">Eliminación de trámite</option>
                    </select>
                  </div>
                  <div class="col-md-3 form-group" style="display:flex; align-items:flex-end;">
                    <button class="btn btn-gradient-primary btn-block" onclick="Listar_Bitacora()">
                      <i class="fas fa-search"></i> Buscar
                    </button>
                  </div>
                  <div class="col-12">
                    <small class="text-muted">
                      <i class="fas fa-info-circle"></i>
                      Se muestran los 1000 movimientos más recientes del rango elegido.
                      La bitácora es solo de lectura: deja constancia de quién hizo qué y cuándo.
                    </small>
                  </div>
                </div>

                <div class="table-responsive" style="text-align:center">
                  <table id="tabla_bitacora" class="table table-striped table-bordered table-modern" style="width:100%">
                      <thead>
                          <tr>
                              <th style="text-align:center">Fecha y hora</th>
                              <th style="text-align:center">Usuario</th>
                              <th style="text-align:center">Rol</th>
                              <th style="text-align:center">Acción</th>
                              <th style="text-align:center">Referencia</th>
                              <th style="text-align:center">Detalle</th>
                              <th style="text-align:center">Origen (IP)</th>
                          </tr>
                      </thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
