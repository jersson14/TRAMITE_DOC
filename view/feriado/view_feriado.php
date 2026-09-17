<?php require_once __DIR__ . '/../../lib/Seguridad.php'; Seguridad::requiereVista([Seguridad::ROL_ADMIN]); ?>
<script src="../js/console_feriado.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../../plantilla/dist/css/modern-admin-theme.css'); ?>">

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><b>FERIADOS Y DÍAS NO LABORABLES</b></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
              <li class="breadcrumb-item active">FERIADOS</li>
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
                <h3 class="card-title"><i class="nav-icon far fa-calendar-times"></i>&nbsp;&nbsp;<b>Días que no cuentan para los plazos</b></h3>
                <button class="btn btn-gradient-success float-right" onclick="Abrir_Feriado()"><i class="fas fa-plus"></i> Nuevo feriado</button>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 form-group">
                    <label for="cbo_anio" style="font-size:small;">Año:</label>
                    <select class="form-control" id="cbo_anio" onchange="Listar_Feriados()"></select>
                  </div>
                  <div class="col-md-5 form-group" style="display:flex; align-items:flex-end;">
                    <button class="btn btn-gradient-primary btn-block" onclick="Cargar_Nacionales()">
                      <i class="fas fa-download"></i> Cargar feriados nacionales del año
                    </button>
                  </div>
                  <div class="col-12">
                    <small class="text-muted">
                      <i class="fas fa-info-circle"></i>
                      Los plazos de atención se cuentan en días hábiles: no se cuentan sábados, domingos ni los días de esta lista.
                      También determinan cuándo se considera presentado un documento que llega fuera del horario de atención.
                      «Cargar feriados nacionales» agrega los del año elegido (incluidos Jueves y Viernes Santo, que cambian cada año)
                      sin tocar los que ya estén registrados. Los feriados regionales y los días no laborables que decrete el Gobierno
                      se agregan a mano.
                    </small>
                  </div>
                </div>

                <div id="aviso_anio"></div>

                <div class="table-responsive" style="text-align:center">
                  <table id="tabla_feriado" class="table table-striped table-bordered table-modern" style="width:100%">
                      <thead>
                          <tr>
                              <th style="text-align:center">Fecha</th>
                              <th style="text-align:center">Día</th>
                              <th style="text-align:center">Motivo</th>
                              <th style="text-align:center">Tipo</th>
                              <th style="text-align:center">Acción</th>
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

<!-- MODAL NUEVO / EDITAR FERIADO -->
<div class="modal fade" id="modal_feriado" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#1E3A5F; color:#fff;">
        <h5 class="modal-title" id="lb_titulo_feriado"><i class="far fa-calendar-plus mr-2"></i>NUEVO FERIADO</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 form-group" style="color:red">
            <h6><b>Campos obligatorios (*)</b></h6>
          </div>
          <div class="col-12 form-group">
            <label for="txt_fecha_feriado">Fecha(*):</label>
            <input type="date" class="form-control" id="txt_fecha_feriado">
            <small class="text-muted" id="aviso_fecha_feriado"></small>
          </div>
          <div class="col-12 form-group">
            <label for="txt_motivo_feriado">Motivo(*):</label>
            <input type="text" class="form-control" id="txt_motivo_feriado" maxlength="120" placeholder="Ej.: Aniversario de Abancay">
          </div>
          <div class="col-12 form-group">
            <label for="cbo_tipo_feriado">Tipo(*):</label>
            <select class="form-control" id="cbo_tipo_feriado">
              <option value="NACIONAL">Feriado nacional</option>
              <option value="REGIONAL">Feriado regional</option>
              <option value="NO_LABORABLE">Día no laborable</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
        <button type="button" class="btn btn-success" onclick="Guardar_Feriado()"><i class="fas fa-check"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    Listar_Feriados();
  });
</script>
