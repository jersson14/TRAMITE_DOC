<script src="../js/console_tramite_area_buscar_fecha_tipodoc.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css">

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          <h1 class="m-0"><b>REPORTES POR FECHA Y TIPO DE DOCUMENTO</b></h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
              <li class="breadcrumb-item active">TRÁMITE</li>
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
          <!-- /.col-md-6 -->
          <div class="col-lg-12">
            <div class="card card-modern">
              <div class="card-header">
              <h3 class="card-title"><i class="fas fa-file-signature"></i>&nbsp;&nbsp;<b>Listado de Trámites</b></h3>
              </div>
                <div class="table-responsive" style="text-align:left">
                <div class="card-body">
                <div class="row">
                <div class="col-12 col-md-3" role="document">
                    <div class="form-group">
                    <label for="txtfechainicio">Fecha Desde:</label>
                        <div class="input-group mb-2">
                         <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <input type="date" class="form-control" id="txtfechainicio" name="txtfechainicio" required>
                        <div class="valid-input invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-md-3" role="document">
                    <div class="form-group">
                    <label for="txtfechafin">Fecha Hasta:</label>
                        <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                        <input type="date" class="form-control" id="txtfechafin" name="txtfechafin" required>
                        <div class="valid-input invalid-feedback"></div>
                    </div>
                    </div>
                </div>
                <div class="col-12 col-md-3" role="document">
                    <div class="form-group">
                    <label for="select_tipo">Tipo Documento:</label>
                        <div class="input-group mb-2">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="fas fa-th"></i>
                            </div>
                        </div>
                        <select type="text" class="form-control js-example-basic-single" id="select_tipo" name="select_tipo" style="width:80%"></select>
                        <div class="valid-input invalid-feedback"></div>
                    </div>
                    </div>
                </div>
                <div class="col-12 col-md-3" role="document">
                    <label for="">&nbsp;</label><br>
                    <button onclick="listar_fechas_busqueda()" class="btn btn-gradient-primary btn-modern mr-2" style="width:100%" onclick><i class="fas fa-search mr-1"></i>Buscar Documentos</button>
                </div>
                </div>
                
                <div class="table-responsive" style="text-align:center">
                  <div class="card-body">
                    <table id="tabla_tramite" class="table table-striped table-bordered table-modern" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align:center">Nro.</th>
                                <th style="text-align:center">N° Expediente</th>
                                <th style="text-align:center">Tipo Documento</th>
                                <th style="text-align:center">DNI Remit.</th>
                                <th style="text-align:center">Remitente</th>
                                <th style="text-align:center">Fecha</th>
                                <th style="text-align:center">Asunto</th>
                                <th style="text-align:center">Área Origen</th>
                                <th style="text-align:center">Localización</th>
                                <th style="text-align:center">Estado Documento</th>
                          </tr>
                        </thead>
                    </table>
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

    <!-- /.content -->
<div class="modal fade" id="modal_seguimiento" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lb_titulo">Seguimiento del Trámite</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12" style="text-align:center"> 
          <div class="table-responsive" style="text-align:center">
            <div class="card-body">   
            <table id="tabla_seguimiento" class="display compact table-modern" style="width:100%" style="text-align:center">
                <thead>
                  <tr style="text-align:center">
                      <th style="text-align:center">PROCEDENCIA</th>
                      <th style="text-align:center">FECHA</th>
                      <th style="text-align:center">DESCRIPCION</th>
                      <th style="text-align:center">ESTADO</th>
                      <th style="text-align:center">ARCHIVO ANEXADO</th>
                   </tr>
                  </thead>
                </table>     
          </div>
        </div>
        </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-arrow-right-from-bracket"></i>Cerrar</button>
      </div>
    </div>
  </div>
</div>
    <!-- /MODAL MAS DATOS -->

<div class="modal fade modal-modern" id="modal_mas" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lb_titulo_datos"><i class="fas fa-file-alt mr-2"></i>Datos del Expediente</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-12">
            <!-- Modern Tabs -->
            <ul class="nav nav-pills mb-4" id="expediente-tabs" role="tablist">
              <li class="nav-item flex-fill">
                <a class="nav-link active text-center" id="info-tab" data-toggle="pill" href="#info-content" role="tab" style="border-radius: 12px; font-weight: 600; transition: all 0.3s;">
                  <i class="fas fa-info-circle mr-2"></i>Información del Documento
                </a>
              </li>
              <li class="nav-item flex-fill ml-2">
                <a class="nav-link text-center" id="remitente-tab" data-toggle="pill" href="#remitente-content" role="tab" style="border-radius: 12px; font-weight: 600; transition: all 0.3s;">
                  <i class="fas fa-user mr-2"></i>Datos del Remitente
                </a>
              </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="expediente-tabContent">
              <!-- Información del Documento -->
              <div class="tab-pane fade show active" id="info-content" role="tabpanel">
                <div class="card shadow-modern-sm" style="border-radius: 15px; border: none;">
                  <div class="card-body p-4">
                    <div class="row">
                      <!-- Procedencia -->
                      <div class="col-12 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-building text-primary mr-2"></i>Procedencia del Documento
                        </label>
                        <select class="js-example-basic-single" id="select_area_p" style="width:100%;" disabled></select>
                      </div>

                      <!-- Área de Destino -->
                      <div class="col-12 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-map-marker-alt text-success mr-2"></i>Área de Destino
                        </label>
                        <select class="js-example-basic-single" id="select_area_d" style="width:100%;" disabled></select>
                      </div>

                      <!-- Tipo Documento -->
                      <div class="col-12 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-file-invoice text-info mr-2"></i>Tipo de Documento
                        </label>
                        <select class="js-example-basic-single" id="select_tipo" style="width:100%;" disabled></select>
                      </div>

                      <!-- N° Expediente y Folios -->
                      <div class="col-md-8 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-hashtag text-warning mr-2"></i>N° Expediente
                        </label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                              <i class="fas fa-folder-open"></i>
                            </span>
                          </div>
                          <input type="text" class="form-control font-weight-bold" id="txt_ndocumento" readonly style="background-color: #f8f9fa; border-left: none;">
                        </div>
                      </div>

                      <div class="col-md-4 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-copy text-secondary mr-2"></i>N° Folios
                        </label>
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <span class="input-group-text" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none;">
                              <i class="fas fa-file"></i>
                            </span>
                          </div>
                          <input type="text" class="form-control font-weight-bold text-center" id="txt_folio" readonly style="background-color: #f8f9fa; border-left: none;">
                        </div>
                      </div>

                      <!-- Asunto -->
                      <div class="col-12 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-align-left text-danger mr-2"></i>Asunto
                        </label>
                        <textarea class="form-control" id="txt_asunto" rows="4" style="resize:none; background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;" readonly></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Datos del Remitente -->
              <div class="tab-pane fade" id="remitente-content" role="tabpanel">
                <div class="card shadow-modern-sm" style="border-radius: 15px; border: none;">
                  <div class="card-body p-4">
                    <!-- Datos Personales -->
                    <h6 class="font-weight-bold mb-3" style="color: #667eea;">
                      <i class="fas fa-id-card mr-2"></i>Datos Personales
                    </h6>
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-id-card-alt text-primary mr-2"></i>N° DNI
                        </label>
                        <input type="text" class="form-control" id="txt_dni" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                      <div class="col-md-6 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-user text-success mr-2"></i>Nombre
                        </label>
                        <input type="text" class="form-control" id="txt_nom" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                      <div class="col-md-6 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-user-tag text-info mr-2"></i>Apellido Paterno
                        </label>
                        <input type="text" class="form-control" id="txt_apepat" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                      <div class="col-md-6 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-user-tag text-warning mr-2"></i>Apellido Materno
                        </label>
                        <input type="text" class="form-control" id="txt_apemat" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                    </div>

                    <!-- Contacto -->
                    <h6 class="font-weight-bold mb-3 mt-3" style="color: #667eea;">
                      <i class="fas fa-address-book mr-2"></i>Información de Contacto
                    </h6>
                    <div class="row">
                      <div class="col-md-6 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-mobile-alt text-success mr-2"></i>Celular
                        </label>
                        <input type="text" class="form-control" id="txt_celular" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                      <div class="col-md-6 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-envelope text-danger mr-2"></i>Email
                        </label>
                        <input type="text" class="form-control" id="txt_email" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                      <div class="col-12 form-group">
                        <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                          <i class="fas fa-map-marked-alt text-primary mr-2"></i>Dirección
                        </label>
                        <input type="text" class="form-control" id="txt_dire" readonly style="background-color: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 12px;">
                      </div>
                    </div>

                    <!-- Representación -->
                    <h6 class="font-weight-bold mb-3 mt-3" style="color: #667eea;">
                      <i class="fas fa-user-shield mr-2"></i>En Representación
                    </h6>
                    <div class="radio-card-container mb-3">
                      <div class="radio-card">
                        <input type="radio" checked value="A Nombre Propio" id="rad_presentacion1" name="r1" disabled>
                        <label for="rad_presentacion1">
                          <i class="fas fa-user-circle mr-2"></i>A Nombre Propio
                        </label>
                      </div>
                      <div class="radio-card">
                        <input type="radio" id="rad_presentacion2" name="r1" value="A Otra Persona Natural" disabled>
                        <label for="rad_presentacion2">
                          <i class="fas fa-user-friends mr-2"></i>Otra Persona Natural
                        </label>
                      </div>
                      <div class="radio-card">
                        <input type="radio" id="rad_presentacion3" name="r1" value="Persona Jurídica" disabled>
                        <label for="rad_presentacion3">
                          <i class="fas fa-building mr-2"></i>Persona Jurídica
                        </label>
                      </div>
                    </div>

                    <!-- Datos Jurídicos -->
                    <div id="div_juridico" style="display:none">
                      <div class="card mt-3" style="background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border: 2px solid #f093fb50; border-radius: 12px;">
                        <div class="card-body">
                          <h6 class="font-weight-bold mb-3" style="color: #f5576c;">
                            <i class="fas fa-briefcase mr-2"></i>Datos de la Empresa
                          </h6>
                          <div class="row">
                            <div class="col-md-4 form-group">
                              <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                                <i class="fas fa-hashtag text-danger mr-2"></i>RUC
                              </label>
                              <input type="text" class="form-control" id="txt_ruc" readonly style="background-color: white; border: 2px solid #e2e8f0; border-radius: 12px;">
                            </div>
                            <div class="col-md-8 form-group">
                              <label class="font-weight-bold text-muted mb-2" style="font-size: 0.85rem;">
                                <i class="fas fa-building text-primary mr-2"></i>Razón Social
                              </label>
                              <input type="text" class="form-control" id="txt_razon" readonly style="background-color: white; border: 2px solid #e2e8f0; border-radius: 12px;">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gradient-danger btn-modern" data-dismiss="modal">
          <i class="fas fa-times-circle mr-2"></i>Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Modern Tab Styling */
#expediente-tabs .nav-link {
  background: white;
  color: #718096;
  border: 2px solid #e2e8f0;
}

#expediente-tabs .nav-link:hover {
  background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
  border-color: #667eea;
  color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

#expediente-tabs .nav-link.active {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-color: #667eea;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

/* Disabled Select2 Styling */
.select2-container--disabled .select2-selection--single {
  background-color: #f8f9fa !important;
  border: 2px solid #e2e8f0 !important;
  border-radius: 12px !important;
}
</style>

    <script>
    $(document).ready(function () {
      listar_tramite();
      $('.js-example-basic-single').select2();
      Cargar_Select_Area();   
      Cargar_Select_Tipo();
    });
    var n = new Date();
    var y= n.getFullYear();
    var m= n.getMonth()+1;
    var d= n.getDate();
    if(d<10){
        d='0' + d;
    }
    if(m<10){
        m='0' + m;

    }
    document.getElementById('txtfechainicio').value = y + "-" + m + "-" + d;
    document.getElementById('txtfechafin').value = y + "-" + m + "-" + d;
    </script>