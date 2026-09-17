<?php
require_once __DIR__ . '/../../lib/Seguridad.php';
Seguridad::requiereVista();
$esAdmin = Seguridad::esAdmin();
?>
<script src="../js/console_reporte_plazos.js?rev=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../../plantilla/dist/css/modern-admin-theme.css'); ?>">

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><b>PLAZOS Y PRODUCTIVIDAD POR ÁREA</b></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
          <li class="breadcrumb-item active">REPORTE DE PLAZOS</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-12">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-stopwatch"></i>&nbsp;&nbsp;<b>Cumplimiento de plazos y carga de trabajo</b></h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3 form-group">
                <label for="txt_desde" style="font-size:small;">Desde:</label>
                <input type="date" class="form-control" id="txt_desde">
              </div>
              <div class="col-md-3 form-group">
                <label for="txt_hasta" style="font-size:small;">Hasta:</label>
                <input type="date" class="form-control" id="txt_hasta">
              </div>
              <?php if ($esAdmin) { ?>
                <div class="col-md-3 form-group">
                  <label for="cbo_area_plazos" style="font-size:small;">Área:</label>
                  <select class="form-control" id="cbo_area_plazos"><option value="0">Todas las áreas</option></select>
                </div>
              <?php } ?>
              <div class="col-md-3 form-group" style="display:flex; align-items:flex-end;">
                <button class="btn btn-gradient-primary btn-block" onclick="Listar_Plazos()">
                  <i class="fas fa-search"></i> Generar reporte
                </button>
              </div>
              <div class="col-12">
                <small class="text-muted">
                  <i class="fas fa-info-circle"></i>
                  Se cuentan los envíos principales (no copias ni atenciones) en días hábiles, sin sábados, domingos ni feriados.
                  <b>Recibidos</b> y <b>despachados</b> corresponden al período elegido; <b>en curso</b> y <b>vencidos</b> son la situación de hoy.
                </small>
              </div>
            </div>

            <div id="resumen_plazos"></div>

            <div class="exportaciones" id="exportar_plazos"></div>

            <h6 class="indicadores-titulo">Resumen por área</h6>
            <div class="table-responsive" style="text-align:center">
              <table id="tabla_plazos" class="table table-striped table-bordered table-modern" style="width:100%">
                <thead>
                  <tr>
                    <th style="text-align:center">Área</th>
                    <th style="text-align:center">Recibidos</th>
                    <th style="text-align:center">Despachados</th>
                    <th style="text-align:center">Días promedio</th>
                    <th style="text-align:center">Días máximo</th>
                    <th style="text-align:center">Dentro del plazo</th>
                    <th style="text-align:center">En curso hoy</th>
                    <th style="text-align:center">Vencidos hoy</th>
                  </tr>
                </thead>
              </table>
            </div>

            <h6 class="indicadores-titulo">Trámites con plazo vencido</h6>
            <div class="table-responsive" style="text-align:center">
              <table id="tabla_vencidos" class="table table-striped table-bordered table-modern" style="width:100%">
                <thead>
                  <tr>
                    <th style="text-align:center">N° Expediente</th>
                    <th style="text-align:center">Asunto</th>
                    <th style="text-align:center">Remitente</th>
                    <th style="text-align:center">Área</th>
                    <th style="text-align:center">Estado</th>
                    <th style="text-align:center">Venció el</th>
                    <th style="text-align:center">Atraso</th>
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

<script>
  $(document).ready(function () {
    Iniciar_Reporte_Plazos(<?php echo $esAdmin ? 'true' : 'false'; ?>);
  });
</script>
