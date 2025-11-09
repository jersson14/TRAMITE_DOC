<script src="../js/console_tramite_area_buscar_fecha_tipodoc.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">

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
            <div class="card">
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
                    <button onclick="listar_fechas_busqueda()" class="btn btn-danger mr-2" style="width:100%" onclick><i class="fas fa-search mr-1"></i>Buscar Documentos</button>
                </div>
                </div>
                
                <div class="table-responsive" style="text-align:center">
                  <div class="card-body">
                    <table id="tabla_tramite" class="table table-striped table-bordered" style="width:100%">
                        <thead style="background-color:#0A5D86;color:#FFFFFF; ">
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
            <table id="tabla_seguimiento" class="display compact" style="width:100%" style="text-align:center">
                <thead style="background-color:#0A5D86;color:#FFFFFF; ">
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

<div class="modal fade" id="modal_mas" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <h5 class="modal-title" id="lb_titulo_datos">Datos del Trámite</h5>
      </div>
      <div class="modal-body">
        <div class="row">
        <div class="col-12">
          <div class="card card-primary card-tabs">
            <div class="card-header p-0 pt-1">
              <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill" href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home" aria-selected="true">Información</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill" href="#custom-tabs-one-profile" role="tab" aria-controls="custom-tabs-one-profile" aria-selected="false">Datos del Remitente</a>
                </li>
              </ul>
              </div>
            <div class="card-body">
              <div class="tab-content" id="custom-tabs-one-tabContent">
                <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel" aria-labelledby="custom-tabs-one-home-tab">
                  <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Procedencia del Documento</label>
                                <select class="js-example-basic-single" id="select_area_p" style="width:100%; background-color:white" disabled></select>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Área de Destino</label>
                                <select class="js-example-basic-single" id="select_area_d" style="width:100%; background-color:white" disabled></select>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Tipo Documento</label>
                                <select class="js-example-basic-single" id="select_tipo" style="width:100%; background-color:white" disabled></select>
                            </div>
                            <div class="col-8 form-group">
                                <label for="" style="font-size:small;">N° Expediente</label>
                                <input type="text" class="form-control" id="txt_ndocumento" readonly>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">N° Folios</label>
                                <input type="text" class="form-control" id="txt_folio" readonly>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Asunto</label>
                                <textarea class="form-control" id="txt_asunto" rows="3" style="resize:none" readonly></textarea>
                            </div>
                            
                        </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel" aria-labelledby="custom-tabs-one-profile-tab">
                <div class="row">
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">N° DNI</label>
                                <input type="text" class="form-control" id="txt_dni" readonly style="background-color:white;">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Nombre</label>
                                <input type="text" class="form-control" id="txt_nom" readonly style="background-color:white;">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Apellido Paterno</label>
                                <input type="text" class="form-control" id="txt_apepat" readonly style="background-color:white;">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Apellidos Materno</label>
                                <input type="text" class="form-control" id="txt_apemat" readonly style="background-color:white;">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Celular</label>
                                <input type="text" class="form-control" id="txt_celular" readonly style="background-color:white;">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Email</label>
                                <input type="text" class="form-control" id="txt_email" readonly style="background-color:white;">
                            </div>
                            <div class="col-12">
                                <label for="" style="font-size:small;">Dirección</label>
                                <input type="text" class="form-control" id="txt_dire" readonly style="background-color:white;">
                            </div>
                            <div class="col-12"><br>
                                <label for="" style="font-size:small;">En Representación</label>
                            </div>
                            <div class="col-12 row">
                                <!--radio-->
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" checked value="A Nombre Propio" id="rad_presentacion1" name="r1" readoly style="background-color:white;" disabled>
                                        <label for="rad_presentacion1" style="font-weight:normal; font-size:small" disabled>
                                            <b>A Nombre Propio</b>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="rad_presentacion2" name="r1" value="A Otra Persona Natural" readoly style="background-color:white;" disabled>
                                        <label for="rad_presentacion2" style="font-weight:normal; font-size:small" disabled>
                                            <b>A Otra Persona Natural</b>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="rad_presentacion3" name="r1" value="Persona Jurídica" readoly style="background-color:white;" disabled>
                                        <label for="rad_presentacion3" style="font-weight:normal; font-size:small" disabled>
                                            <b>Persona Jurídica</b>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 row" id="div_juridico" style="display:none">
                                <div class="row">
                                <div class="col-4 form-group" >
                                    <label for="" style="font-size:small;">RUC</label>
                                    <input type="text" class="form-control" id="txt_ruc">
                                </div>
                                <div class="col-8 form-group" >
                                    <label for="" style="font-size:small;">Razón Social</label>
                                    <input type="text" class="form-control" id="txt_razon">
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
        <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-arrow-right-from-bracket"></i>Cerrar</button>
      </div>
    </div>
  </div>
</div>

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