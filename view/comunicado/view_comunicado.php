<?php require_once __DIR__ . '/../../lib/Seguridad.php'; Seguridad::requiereVista([Seguridad::ROL_ADMIN]); ?>
<script src="../js/console_comunicados.js?rev=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../../plantilla/dist/css/modern-admin-theme.css'); ?>">

<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><b>COMUNICADOS</b></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
          <li class="breadcrumb-item active">COMUNICADOS</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<!-- /.content-header -->

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title"><i class="nav-icon fas fa-bullhorn"></i>&nbsp;&nbsp;<b>Avisos para el personal</b></h3>
            <button class="btn btn-gradient-success float-right" onclick="Abrir_Comunicado()"><i class="fas fa-plus"></i> Nuevo comunicado</button>
          </div>
          <div class="card-body">
            <small class="text-muted">
              <i class="fas fa-info-circle"></i>
              Los comunicados vigentes se muestran como aviso al entrar al sistema, solo a quien van dirigidos, y
              quedan registrados cuando la persona confirma haberlos leído. Los archivados no se muestran.
            </small>
            <div class="table-responsive mt-3" style="text-align:center">
              <table id="tabla_comunicados" class="table table-striped table-bordered table-modern" style="width:100%">
                <thead>
                  <tr>
                    <th style="text-align:center">Nro.</th>
                    <th style="text-align:center">Título</th>
                    <th style="text-align:center">Contenido</th>
                    <th style="text-align:center">Dirigido a</th>
                    <th style="text-align:center">Publicado</th>
                    <th style="text-align:center">Vigencia</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Leído por</th>
                    <th style="text-align:center">Acción</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /.content -->

<!-- MODAL NUEVO / EDITAR COMUNICADO -->
<div class="modal fade" id="modal_comunicado" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: #1E3A5F; color: white;">
        <h5 class="modal-title" id="lb_titulo_comunicado"><i class="fas fa-bullhorn mr-2"></i>NUEVO COMUNICADO</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12 form-group" style="color:red">
            <h6><b>Campos obligatorios (*)</b></h6>
            <input type="text" id="txt_id_comun" hidden>
          </div>
          <div class="col-12 form-group">
            <label for="txt_titulo">Título(*):</label>
            <input type="text" class="form-control" id="txt_titulo" maxlength="300">
          </div>
          <div class="col-12 form-group">
            <label for="txt_descripcion">Contenido(*):</label>
            <textarea class="form-control" id="txt_descripcion" rows="5" style="resize:vertical"></textarea>
          </div>
          <div class="col-12 form-group">
            <label for="txt_enlace">Enlace <small class="text-muted">(opcional; debe empezar con http:// o https://)</small>:</label>
            <input type="url" class="form-control" id="txt_enlace" placeholder="https://...">
          </div>
          <div class="col-md-6 form-group">
            <label for="cbo_destino">Dirigido a(*):</label>
            <select class="form-control" id="cbo_destino" onchange="Cambio_Destino_Comunicado()">
              <option value="TODOS">Todo el personal</option>
              <option value="ADMINISTRADORES">Solo administradores</option>
              <option value="SECRETARIAS">Solo personal de áreas</option>
              <option value="AREAS">Áreas específicas</option>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label for="cbo_estado_comunicado">Estado:</label>
            <select class="form-control" id="cbo_estado_comunicado">
              <option value="NUEVO">Vigente (se muestra como aviso)</option>
              <option value="PASADO">Archivado (no se muestra)</option>
            </select>
          </div>
          <div class="col-12 form-group" id="bloque_areas_comunicado" hidden>
            <label for="select_areas_comunicado">Áreas destinatarias(*):</label>
            <select class="form-control" id="select_areas_comunicado" multiple style="width:100%"></select>
          </div>
          <div class="col-md-6 form-group">
            <label for="txt_desde_comunicado">Vigente desde <small class="text-muted">(opcional)</small>:</label>
            <input type="date" class="form-control" id="txt_desde_comunicado">
          </div>
          <div class="col-md-6 form-group">
            <label for="txt_hasta_comunicado">Vigente hasta <small class="text-muted">(opcional)</small>:</label>
            <input type="date" class="form-control" id="txt_hasta_comunicado">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gradient-danger" data-dismiss="modal"><i class="fas fa-times ml-1"></i> Cerrar</button>
        <button type="button" class="btn btn-gradient-success" onclick="Guardar_Comunicado()"><i class="fas fa-save"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL QUIÉNES LO LEYERON -->
<div class="modal fade" id="modal_lecturas" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: #1E3A5F; color: white;">
        <h5 class="modal-title"><i class="fas fa-check-double mr-2"></i>CONFIRMARON LA LECTURA</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p class="text-muted" id="lb_comunicado_lecturas"></p>
        <div id="lista_lecturas"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function () {
    listar_comunicado();
    Cargar_Areas_Comunicado();
  });
  $('#modal_comunicado').on('shown.bs.modal', function () {
    $('#txt_titulo').trigger('focus');
  });
</script>
