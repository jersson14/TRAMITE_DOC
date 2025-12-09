<script src="../js/console_tramite_area.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css">

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><b>MANTENIMIENTO DE TRÁMITE</b></h1>
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
                <button class="btn btn-gradient-success float-right" onclick="cargar_contenido('contenido_principal','tramite_area/view_tramite_registro.php')"><i class="fas fa-plus"></i> Nuevo Registro</button>
                
              </div><br>
              <div class="row">
                <div class="col-lg-12 form-group" style="text-align:right">
                <label for="" style="color:#000000">ALERTAS POR DÍAS TRANSCURRIDOS: </label> 
                <label for="" style="color:#000000"> 1 día Negro</label>
                <label for="" style="color:#000000"> | </label> 
                <label for="" style="color:#008000"> 2 días Verde</label>  
                <label for="" style="color:#000000"> | </label> 
                <label for="" style="color:#FFC300"> 3 - 4 días Amarillo</label>
                <label for="" style="color:#000000"> | </label> 
                <label for="" style="color:#FF0000"> 5 días a más Rojo</label><br>
                <label for="" style="color:#000000">ALERTAS POR DÍAS DE RESPUESTA: </label> 
                <label for="" style="color:#008000"> Verde = Dentro de tiempo</label>
                <label for="" style="color:#000000"> | </label> 
                <label for="" style="color:#FF0000"> Rojo = Plazo a vencer o vencido</label>             
                </div>
                
                </div>
              
                <div class="table-responsive" style="text-align:center">
                  <div class="card-body">
                    <table id="tabla_tramite" class="display compact table-modern" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align:center">Nro.</th>
                                <th style="text-align:center">N° Expediente</th>
                                <th style="text-align:center">Tipo Documento</th>
                                <th style="text-align:center">DNI Remit.</th>
                                <th style="text-align:center">Remitente</th>
                                <th style="text-align:center">Mas Datos</th>
                                <th style="text-align:center">Seguimiento</th>
                                <th style="text-align:center">Área Origen</th>
                                <th style="text-align:center">Localización</th>
                                <th style="text-align:center">Estado Documento</th>
                                <th style="text-align:center">Alerta Días Transc.</th>
                                <th style="text-align:center">Alerta Días de Respu.</th>
                                <th style="text-align:center">Acci&oacute;n</th>

                            </tr>
                        </thead>
                    </table>
                  </div>
                </div>           
           </div>
          </div>
          <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->

    <!-- /.content -->
<div class="modal fade modal-modern" id="modal_seguimiento" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="modal-title" id="lb_titulo"><i class="fas fa-route mr-2"></i>SEGUIMIENTO DE TRAMITE N°: <span id="nro_expediente_seguimiento"></span></h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Legend Section -->
        <div class="card mb-3" style="background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%); border: none; border-radius: 12px;">
          <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-center">
              <div class="mr-4 mb-2">
                <i class="fas fa-info-circle text-primary mr-2"></i>
                <strong class="text-primary">Leyenda:</strong>
              </div>
              <div class="d-flex flex-wrap gap-3">
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); font-size: 0.85rem;">
                  <i class="fas fa-map-marker-alt mr-1"></i> Origen
                </span>
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size: 0.85rem;">
                  <i class="fas fa-map-pin mr-1"></i> Destino
                </span>
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); font-size: 0.85rem;">
                  <i class="fas fa-clock mr-1"></i> PENDIENTE
                </span>
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); font-size: 0.85rem;">
                  <i class="fas fa-check-circle mr-1"></i> ACEPTADO
                </span>
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); font-size: 0.85rem;">
                  <i class="fas fa-times-circle mr-1"></i> RECHAZADO
                </span>
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); font-size: 0.85rem;">
                  <i class="fas fa-share mr-1"></i> DERIVADO
                </span>
                <span class="badge badge-pill px-3 py-2" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); font-size: 0.85rem;">
                  <i class="fas fa-flag-checkered mr-1"></i> FINALIZADO
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Historial de Movimientos Section -->
        <div class="card" style="border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
          <div class="card-header" style="background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); color: white; border-radius: 12px 12px 0 0;">
            <h6 class="mb-0"><i class="fas fa-list-ul mr-2"></i> Historial de Movimientos</h6>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table id="tabla_seguimiento" class="display compact table-modern" style="width:100%">
                <thead>
                  <tr style="text-align:center">
                    <th style="text-align:center"><i class="fas fa-map-marker-alt mr-1"></i> ORIGEN</th>
                    <th style="text-align:center"><i class="fas fa-map-pin mr-1"></i> DESTINO</th>
                    <th style="text-align:center"><i class="fas fa-calendar-alt mr-1"></i> FECHA</th>
                    <th style="text-align:center"><i class="fas fa-comment-dots mr-1"></i> DESCRIPCIÓN</th>
                    <th style="text-align:center"><i class="fas fa-info-circle mr-1"></i> ESTADO</th>
                    <th style="text-align:center"><i class="fas fa-tasks mr-1"></i> ACCIONES</th>
                    <th style="text-align:center"><i class="fas fa-paperclip mr-1"></i> ARCHIVO ANEXADO</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer" style="background-color: #f8f9fa; border-radius: 0 0 12px 12px;">
        <button type="button" class="btn btn-gradient-danger btn-modern" data-dismiss="modal">
          <i class="fas fa-times-circle mr-2"></i>Cerrar
        </button>
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
                                <select class="js-example-basic-single" id="select_area_p" style="width:100%; backgroun-color:white" disabled></select>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Área de Destino</label>
                                <select class="js-example-basic-single" id="select_area_d" style="width:100%; backgroun-color:white" disabled></select>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Tipo Documento</label>
                                <select class="js-example-basic-single" id="select_tipo" style="width:100%; backgroun-color:white" disabled></select>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">N° Expediente</label>
                                <input type="text" class="form-control" id="txt_ndocumento" readonly>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">N° Folios</label>
                                <input type="text" class="form-control" id="txt_folio" readonly>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">Tiempo de respuesta en días:</label>
                                <input type="text" class="form-control" id="txt_tiempo_respuesta" readonly>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Asunto</label>
                                <textarea class="form-control" id="txt_asunto" rows="3" style="resize:none" readonly></textarea>
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Acciones a realizar:</label>
                                <textarea class="form-control" id="txt_acciones" rows="3" style="resize:none" readonly></textarea>
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Observaciónes / Motivo de Archivo:</label>
                                <textarea class="form-control" id="txt_observacion" rows="3" style="resize:none" readonly></textarea>
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
                                        <input type="radio" checked value="A Nombre Propio" id="rad_presentacion1" name="r1" readonly style="background-color:white;" disabled>
                                        <label for="rad_presentacion1" style="font-weight:normal; font-size:small" disabled>
                                            <b>A Nombre Propio</b>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="rad_presentacion2" name="r1" value="A Otra Persona Natural" readonly style="background-color:white;" disabled>
                                        <label for="rad_presentacion2" style="font-weight:normal; font-size:small" disabled>
                                            <b>A Otra Persona Natural</b>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="rad_presentacion3" name="r1" value="Persona Jurídica" readonly style="background-color:white;" disabled>
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
                                    <input type="text" class="form-control" id="txt_ruc" readonly>
                                </div>
                                <div class="col-8 form-group" >
                                    <label for="" style="font-size:small;">Razón Social</label>
                                    <input type="text" class="form-control" id="txt_razon" readonly>
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
 <!-- /MODAL DERIVAR-->
 <div class="modal fade modal-modern" id="modal_derivar" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="modal-title" id="lb_titulo_derivar"><i class="fas fa-share-square"></i> Derivar Trámite</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-6 form-group">   
              <label for="">Fecha de Registro</label>
              <input type="text" id="txt_fecha_de" class="form-control" readonly style="background-color:white">
          </div>
          <div class="col-6 form-group">
            <label for="">Acción</label>
            <select class="form-control" id="select_derivar_de">
              <option value="DERIVAR">DERIVAR</option>
              <option value="FINALIZAR">FINALIZAR</option>
            </select>
          </div>
          <div class="col-6 form-group div_derivacion">   
              <label for="">Área Origen:</label>
              <input type="text" id="txt_origen_de" class="form-control" readonly style="background-color:white">
          </div>
          <div class="col-6 form-group div_derivacion">   
            <label for=""><i class="fas fa-building"></i> Área destino(*):</label>
            <select class="js-example-basic-single" style="width:100%;" id="select_destino_de">
                
            </select>
          </div>
          <div class="col-12 form-group">
            <label for=""><i class="fas fa-copy"></i> Copias a (Opcional):</label>
            <select class="js-example-basic-multiple form-control" id="select_area_copias_derivar" name="states[]" multiple="multiple" style="width:100%">
            </select>
            <small class="text-muted">Seleccione las áreas que recibirán copia de este documento</small>
          </div>
          <div class="col-12 form-group">
            <label for=""><i class="fas fa-paperclip"></i> Anexar documento:</label>
            <input type="file" id="txt_documento_de" class="form-control">
            <label for="" style="font-size:13px;color:red">El documento debe estar en formato PDF y con un tamaño máximo de 30 MB.</label>

          </div>
          
          <div class="col-12 form-group" style="color:#2d3748; margin-bottom: 1rem;">
              <h6><i class="fas fa-clipboard-list"></i> <b>Acciones del trámite:</b></h6>
              <small class="text-muted">Seleccione las acciones que se deben realizar con este trámite</small>
          </div>
          <div class="col-12" style="border: 2px solid #e2e8f0; border-radius: 8px; padding: 1rem; background-color: #f8fafc;">
            <div class="checkbox-card-container">
              <div class="checkbox-card">
                <input type="checkbox" id="accion" name="accion" value="-1. ACCIÓN-">
                <i class="fas fa-bolt"></i>
                <label for="accion">1. ACCIÓN</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="tramitar" name="tramitar" value="-2. TRAMITAR-">
                <i class="fas fa-file-alt"></i>
                <label for="tramitar">2. TRAMITAR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="revisar" name="revisar" value="-3. REVISAR-">
                <i class="fas fa-search"></i>
                <label for="revisar">3. REVISAR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="vb" name="vb" value="4. -V° B°-">
                <i class="fas fa-check-circle"></i>
                <label for="vb">4. V° B°</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="coordinar" name="coordinar" value="-5. COORDINAR-">
                <i class="fas fa-handshake"></i>
                <label for="coordinar">5. COORDINAR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="conocimiento" name="conocimiento" value="-6. CONOCIMIENTO-">
                <i class="fas fa-lightbulb"></i>
                <label for="conocimiento">6. CONOCIMIENTO</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="proyectar" name="proyectar" value="-7. PROYECTAR DISPOSITIVOS-">
                <i class="fas fa-project-diagram"></i>
                <label for="proyectar">7. PROYECTAR DISPOSITIVOS</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="consolidar" name="consolidar" value="-8. CONSOLIDAD-">
                <i class="fas fa-layer-group"></i>
                <label for="consolidar">8. CONSOLIDAR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="seguimiento" name="seguimiento" value="-9. SEGUIMIENTO-">
                <i class="fas fa-route"></i>
                <label for="seguimiento">9. SEGUIMIENTO</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="dar_respuesta" name="dar_respuesta" value="-10. DAR RESPUESTA-">
                <i class="fas fa-reply"></i>
                <label for="dar_respuesta">10. DAR RESPUESTA</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="difundir" name="difundir" value="-11. DIFUNDIR-">
                <i class="fas fa-bullhorn"></i>
                <label for="difundir">11. DIFUNDIR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="archivo" name="archivo" value="-12. ARCHIVO-">
                <i class="fas fa-archive"></i>
                <label for="archivo">12. ARCHIVO</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="evaluar" name="evaluar" value="-13. EVALUAR-">
                <i class="fas fa-chart-line"></i>
                <label for="evaluar">13. EVALUAR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="preparar" name="preparar" value="-14. PREPARAR RESPUESTA-">
                <i class="fas fa-pen"></i>
                <label for="preparar">14. PREPARAR RESPUESTA</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="opinion" name="opinion" value="-15. OPINIÓN-">
                <i class="fas fa-comment"></i>
                <label for="opinion">15. OPINIÓN</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="corregir" name="corregir" value="-16. CORREGIR-">
                <i class="fas fa-edit"></i>
                <label for="corregir">16. CORREGIR</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="informe" name="informe" value="-17. INFORME-">
                <i class="fas fa-file-invoice"></i>
                <label for="informe">17. INFORME</label>
              </div>
              <div class="checkbox-card">
                <input type="checkbox" id="asistir" name="asistir" value="-18. ASISTIR-">
                <i class="fas fa-hands-helping"></i>
                <label for="asistir">18. ASISTIR</label>
              </div>
            </div>
          </div>
          
          <textarea class="form-control" id="txt_acciones2" rows="3" style="resize:none" hidden></textarea>       
          <div class="col-12 form-group">
            <label for=""><i class="fas fa-comment-dots"></i> Descripción:</label>
            <textarea name="" id="txt_descripcion_De" rows="3" class="form-control" style="resize:none;"></textarea>
          </div>
          <input type="text" id="txt_iddocumento_de" hidden>
          <input type="text" id="txt_idareaorigen" hidden>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gradient-danger btn-modern" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
        <button type="button" class="btn btn-gradient-success btn-modern" onclick="Registrar_Derivacion();"><i class="fas fa-paper-plane"></i> Derivar</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modal_rechazar" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lb_titulo_derivar2"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
        <div class="col-6 form-group">   
              <label for="">Fecha de Registro</label>
              <input type="text" id="txt_fecha_de2" class="form-control" readonly style="background-color:white" disabled>
          </div>
  
          <div class="col-6 form-group div_derivacion">   
            <label for="">Área Localizada</label>
            <input type="text" id="area_destino2" class="form-control" readonly style="background-color:white" disabled>                
            </select>
          </div>
          <div class="col-12">
            <label for="">Motivo de Rechazo</label>
            <textarea name="" id="txt_descripcion_De2" rows="3" class="form-control" style="resize:none;"></textarea>
          </div>
          <input type="text" id="txt_iddocumento_de2" hidden>
          <input type="text" id="txt_idareaorigen2" hidden>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-gradient-danger" data-dismiss="modal"><i class="fa fa-arrow-right-from-bracket"></i>Cerrar</button>
        <button type="button" class="btn btn-gradient-success" onclick="Rechazar_Tramite();">Rechazar</button>
      </div>
    </div>
  </div>
</div>
    <script>
    $(document).ready(function () {
      listar_tramite();
      $('.js-example-basic-single').select2();
      Cargar_Select_Area_REMI();
      Cargar_Select_Area();   
      Cargar_Select_Tipo();
    });

    $("#select_derivar_de").change(function(){
      let de = document.getElementById('select_derivar_de').value;
      if(de=="DERIVAR"){
        let x = document.getElementsByClassName('div_derivacion');
        var i;
        for (i = 0; i < x.length; i++) {
          x[i].style.display='block';
        }
      }else{
        let x = document.getElementsByClassName('div_derivacion');
        var i;
        for (i = 0; i < x.length; i++) {
          x[i].style.display='none';
        }
      }
    });
    $('input[type="file"]').on('change', function(){
        var ext = $( this ).val().split('.').pop();
        console.log($( this ).val());
        if($(this).val() !=''){
        if(ext == "PDF" || ext =="pdf"){
            if($(this)[0].files[0].size > 31457280){//----- 30 MB
            //if($(this)[0].files[0].size> 1048576){ ------- 1 MB
            //if($(this)[0].files[0].size> 10485760){ ------- 10 MB
                Swal.fire("El archivo seleccionado es demasiado pesado",
                "<label style='color:#9B0000;'>Seleccionar un archivo mas liviano</label>","waning");
                $("#txt_documento_de").val("");
                return;
                //$("#btn_subir").prop("disabled",true);
            }else{
                //$("#btn_subir").attr("disabled",false);
            }
            $("#txtformato").val(ext);
        }
        else{
            $("#txt_documento_de").val("");
            Swal.fire("Mensaje de Error","Extensión no permitida: " + ext,
            "error");
        }
        }
    });
var checkboxes = document.querySelectorAll('input[type=checkbox]');
var text = document.getElementById('txt_acciones2');

function checkboxClick(event) {
  var valor = '';
  for (var i = 0; i < checkboxes.length; i++) {
    if (checkboxes[i].checked) {
      valor += checkboxes[i].value;
    }
  }
  txt_acciones2.value = valor;
}

for (var i = 0; i < checkboxes.length; i++) {
  checkboxes[i].addEventListener('click', checkboxClick);
  // Add active class toggle for checkbox cards
  checkboxes[i].addEventListener('change', function() {
    const card = this.closest('.checkbox-card');
    if (card) {
      if (this.checked) {
        card.classList.add('active');
      } else {
        card.classList.remove('active');
      }
    }
  });
}

// Initialize Select2 for copias in derivation modal
$('#modal_derivar').on('shown.bs.modal', function () {
  if (!$('#select_area_copias_derivar').hasClass('select2-hidden-accessible')) {
    $('#select_area_copias_derivar').select2({
      placeholder: 'Seleccione las áreas para copias',
      allowClear: true,
      dropdownParent: $('#modal_derivar')
    });
  }
  // Load areas for copias every time modal opens
  $.ajax({
    url: '../controller/usuario/controlador_cargar_select_area.php',
    type: 'POST'
  }).done(function(resp) {
    let data = JSON.parse(resp);
    if (data.length > 0) {
      let cadena = "";
      for (let i = 0; i < data.length; i++) {
        cadena += "<option value='" + data[i][0] + "'>" + data[i][1] + "</option>";
      }
      $('#select_area_copias_derivar').html(cadena);
    } else {
      cadena = "<option value=''>No hay áreas disponibles</option>";
      $('#select_area_copias_derivar').html(cadena);
    }
  });
});
    </script>