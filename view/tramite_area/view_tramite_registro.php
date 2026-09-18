<?php require_once __DIR__ . '/../../lib/Seguridad.php'; Seguridad::requiereVista(); ?>
<script src="../js/console_tramite_area.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../../plantilla/dist/css/modern-admin-theme.css'); ?>">


<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><b>REGISTRO DE TRÁMITE</b></h1>
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
    <div class="col-12">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-modern card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-signature"></i> <b>DATOS DEL TRÁMITE</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                        </button>
                    </div>

                </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 form-group" style="color:#2d3748; margin-bottom: 1rem;">
                                <h6><i class="fas fa-clipboard-list"></i> <b>Acciones del trámite:</b></h6>
                                <small class="text-muted">Seleccione las acciones que se deben realizar con este trámite</small>
                            </div>
                            <div class="col-12">
                                <div class="checkbox-card-container">
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="accion" name="accion" value="-1. ACCIÓN-">
                                        <i class="fas fa-bolt"></i>
                                        <label for="accion">Atender</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="tramitar" name="tramitar" value="-2. TRAMITAR-">
                                        <i class="fas fa-file-alt"></i>
                                        <label for="tramitar">Tramitar</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="revisar" name="revisar" value="-3. REVISAR-">
                                        <i class="fas fa-search"></i>
                                        <label for="revisar">Revisar</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="vb" name="vb" value="4. -V° B°-">
                                        <i class="fas fa-check-circle"></i>
                                        <label for="vb">Dar visto bueno</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="coordinar" name="coordinar" value="-5. COORDINAR-">
                                        <i class="fas fa-handshake"></i>
                                        <label for="coordinar">Coordinar</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="conocimiento" name="conocimiento" value="-6. CONOCIMIENTO-">
                                        <i class="fas fa-lightbulb"></i>
                                        <label for="conocimiento">Tomar conocimiento</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="evaluar" name="evaluar" value="-13. EVALUAR-">
                                        <i class="fas fa-chart-line"></i>
                                        <label for="evaluar">Evaluar</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="opinion" name="opinion" value="-15. OPINIÓN-">
                                        <i class="fas fa-comment"></i>
                                        <label for="opinion">Emitir opinión</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="informe" name="informe" value="-17. INFORME-">
                                        <i class="fas fa-file-invoice"></i>
                                        <label for="informe">Emitir informe</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="dar_respuesta" name="dar_respuesta" value="-10. DAR RESPUESTA-">
                                        <i class="fas fa-reply"></i>
                                        <label for="dar_respuesta">Responder</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="seguimiento" name="seguimiento" value="-9. SEGUIMIENTO-">
                                        <i class="fas fa-route"></i>
                                        <label for="seguimiento">Hacer seguimiento</label>
                                    </div>
                                    <div class="checkbox-card">
                                        <input type="checkbox" id="archivo" name="archivo" value="-12. ARCHIVO-">
                                        <i class="fas fa-archive"></i>
                                        <label for="archivo">Archivar</label>
                                    </div>
                                </div>
                            </div>
                            <textarea class="form-control" id="txt_acciones" rows="3" style="resize:none" hidden></textarea>

                        </div>
                    </div>
                </div>
                
            </div>
            <div class="col-md-12">
                <div class="card card-modern card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> <b>DATOS DEL REMITENTE</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                        </button>
                    </div>

                </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 form-group" style="color:red">
                                <h7><b>Campos Obligatorios (*)</b></h7>
                            </div>
                            <div class="col-12 form-group" hidden>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="chk_externo">
                                <label class="form-check-label" for="chk_externo" style="font-size:small;">
                                    Es trámite externo
                                </label>
                            </div>
                        </div>

                        <!-- Select interno -->
                        <div class="col-6 form-group" id="div_dni_select">
                            <label for="" style="font-size:small;">N° DNI(*):</label>
                            <select type="text" class="form-control js-example-basic-single" id="txt_dni" style="width:100%">
                            </select>
                        </div>

                        <!-- Input externo -->
                        <div class="col-6 form-group" id="div_dni_input" style="display:none;" hidden>
                            <label for="" style="font-size:small;">N° DNI<b style="color:red">(*)</b>:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="txt_dni2" maxlength="8" onkeypress="return soloNumeros(event)">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="prueba"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Nombre(*):</label>
                                <input type="text" class="form-control" id="txt_nom" onkeypress="return sololetras(event)">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Apellido Paterno(*):</label>
                                <input type="text" class="form-control" id="txt_apepat" onkeypress="return sololetras(event)">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Apellidos Materno(*):</label>
                                <input type="text" class="form-control" id="txt_apemat" onkeypress="return sololetras(event)">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Celular(*):</label>
                                <input type="text" class="form-control" id="txt_celular" onkeypress="return soloNumeros(event)">
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Email(Opcional)::</label>
                                <input type="text" class="form-control" id="txt_email">
                            </div>
                            <div class="col-12">
                                <label for="" style="font-size:small;">Dirección(*):</label>
                                <input type="text" class="form-control" id="txt_dire">
                            </div>
                            <div class="col-12"><br>
                                <label for="" style="font-size:small;">En Representación</label>
                            </div>
                            <div class="col-12 row">
                                <!--radio-->
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" checked value="A Nombre Propio" id="rad_presentacion1" name="r1" >
                                        <label for="rad_presentacion1" style="font-weight:normal; font-size:small">
                                            <b>A Nombre Propio</b>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="rad_presentacion2" name="r1" value="A Otra Persona Natural">
                                        <label for="rad_presentacion2" style="font-weight:normal; font-size:small">
                                            <b>A Otra Persona Natural</b>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-4 form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" id="rad_presentacion3" name="r1" value="Persona Jurídica">
                                        <label for="rad_presentacion3" style="font-weight:normal; font-size:small">
                                            <b>Persona Jurídica</b>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 row" id="div_juridico" style="display:none">
                                <div class="row">
                                <div class="col-4 form-group" >
                                    <label for="" style="font-size:small;">RUC(*):</label>
                                    <input type="text" class="form-control" id="txt_ruc" onkeypress="return soloNumeros(event)">
                                </div>
                                <div class="col-8 form-group" >
                                    <label for="" style="font-size:small;">Razón Social(*):</label>
                                    <input type="text" class="form-control" id="txt_razon">
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="text-align:justify">
                    <p>*NOTA: Enviar los documentos en un solo archivo en formato pdf, deberá optimizar los documentos antes de enviarlos. El tamaño máximo de los archivos no debe superar los 30MB.</p>
               </div>
            </div>
            <div class="col-md-12">
                <div class="card card-modern card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-file-alt"></i> <b>DATOS DEL DOCUMENTO</b></h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                        </button>
                    </div>

                </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 form-group" style="color:red">
                                <h7><b>Campos Obligatorios (*)</b></h7>
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Área de Destino(*):</label>
                                <select class="js-example-basic-single" id="select_area_d" style="width:100%"></select>
                            </div>
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">Tipo Documento(*):</label>
                                <select class="js-example-basic-single" id="select_tipo" style="width:100%"></select>
                            </div>
                            <div class="col-12 form-group" style="color:red">
                            <label for="">Requisitos: "OJO los documentos como requisitos deben estar en un solo archivo junto al documento principal a presentar"</label>
                                <textarea style="color:red" class="form-control" id="txt_requisitos" readonly rows="2" style="resize:none"></textarea>
                            </div>
                            <div class="col-4 form-group">
                            <label for="" style="font-size:small;">N° Documento(*):</label>
                                <input type="text" class="form-control" id="txt_ndocumento" onkeypress="return soloNumeros(event)">
                            </div>
                             <div class="col-4 form-group">
                                <label for="" style="font-size:small;">N° Folios(*):</label>
                                <input type="text" class="form-control" id="txt_folio" onkeypress="return soloNumeros(event)">
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">Tiempo de respuesta en días(opcional):</label>
                                <input type="number" class="form-control" id="txt_tiempo_respuesta" onkeypress="return soloNumeros(event)">
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Copias a (Opcional):</label>
                                <select class="js-example-basic-multiple form-control" id="select_area_copias" name="states[]" multiple="multiple" style="width:100%">
                                </select>
                                <small class="text-muted">Seleccione las áreas que recibirán copia de este documento</small>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Asunto(*):</label>
                                <textarea class="form-control" id="txt_asunto" rows="3" style="resize:none"></textarea>
                            </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Observaciónes / Motivo de Archivo:</label>
                                <textarea class="form-control" id="txt_observacion" rows="3" style="resize:none"></textarea>
                            </div>
                         
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Adjuntar Documento(*):</label>
                                <input class="form-control" type="file" id="txt_archivo" accept=".pdf">
                                <!-- Al elegir el PDF el servidor comprueba qué firmas trae. -->
                                <div id="firma_del_archivo" aria-live="polite"></div>
                                <br>
                                <div class="col-12 form-group">
                                    <label for="" style="font-size:small;">Anexos (opcional):</label>
                                    <input class="form-control" type="file" id="txt_anexos" accept=".pdf" multiple>
                                    <small class="text-muted">Puede seleccionar varios PDF a la vez: hasta 10 archivos de 20 MB cada uno.
                                    Use este campo para los recaudos (DNI, partida, planos); el documento principal va arriba.</small>
                                </div>

                                <!-- Firma al registrar: el documento que redacta la entidad debe salir
                                     firmado, en vez de enviarse y firmarse después desde el expediente.
                                     Es opcional; sin certificado el trámite se registra igual. -->
                                <fieldset id="firma_registro" class="mt-3" style="border:1px solid #dee2e6;border-radius:.4rem;padding:.75rem 1rem;">
                                    <legend style="font-size:.9rem;font-weight:700;width:auto;padding:0 .4rem;margin:0;color:#1E3A5F;">
                                        <i class="fas fa-file-signature"></i> Firmar el documento al registrarlo
                                    </legend>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="txt_cert_registro" style="font-size:small;">Certificado digital (.pfx / .p12)</label>
                                            <input type="file" class="form-control" id="txt_cert_registro" accept=".pfx,.p12">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="txt_clave_registro" style="font-size:small;">Contraseña del certificado</label>
                                            <input type="password" class="form-control" id="txt_clave_registro" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="txt_motivo_registro" style="font-size:small;">Motivo <small class="text-muted">(opcional)</small></label>
                                        <input type="text" class="form-control" id="txt_motivo_registro" maxlength="150"
                                               placeholder="Ej.: Conformidad, Visto bueno, Aprobación">
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="chk_firmar_principal" checked>
                                        <label class="form-check-label" for="chk_firmar_principal" style="font-size:small;">Firmar el documento principal</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="chk_firmar_anexos">
                                        <label class="form-check-label" for="chk_firmar_anexos" style="font-size:small;">Firmar también los anexos</label>
                                    </div>
                                    <small class="text-muted d-block mt-2">Si deja el certificado en blanco, el trámite se registra sin firma
                                    digital y así queda en la bitácora. Su certificado y su contraseña no se guardan.
                                    En un trámite externo no se firma: el documento llega firmado de fuera.</small>
                                </fieldset>
                                <label for="" style="font-size:16px;color:red">El documento debe estar en formato PDF y con un tamaño máximo de 30 MB.</label>

                            </div>
                           
                            <div class="col-12">
                                <div class="form-group clearfix">
                                    <div class="icheck-success d-inline">
                                        <input type="checkbox"  id="checkboxSuccess1" onclick="Validar_Informacion()">
                                        <label for="checkboxSuccess1" style="align:justify">
                                            Declaro bajo penalidad de pejurio, que toda información proporcionada es correscta y veridica.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12" style="text-align:center">
                                <button class="btn btn-gradient-success btn-modern btn-lg" onclick="Registrar_Tramite()" id="btn_registro">
                                    <i class="fas fa-save"></i> REGISTRAR TRÁMITE
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <i class="fa-sharp fa-solid fa-floppy-disk-circle-arrow-right"></i>
<script>
    $(document).ready(function () {
        Cargar_Select_DNI_UL();

        $('.js-example-basic-single').select2();
        $('.js-example-basic-multiple').select2({
            placeholder: 'Seleccione las áreas para copias',
            allowClear: true
        });
        $("#rad_presentacion1").on('click', function(){
            document.getElementById('div_juridico').style.display="none";
        });
        $("#rad_presentacion2").on('click', function(){
            document.getElementById('div_juridico').style.display="none";
        });
        $("#rad_presentacion3").on('click', function(){
            document.getElementById('div_juridico').style.display="block";
        });
        Cargar_Select_Tipo();
        Cargar_Select_Area();
        Cargar_Select_Area_Copias();
        TraerNotificacionDocumentos();
        
    });
    $("#txt_dni").change(function(){
        var id=$("#txt_dni").val();
        TraerrequisitoDNI(id);
        });
        $("#select_tipo").change(function(){
        var id=$("#select_tipo").val();
        Traerrequisitotipodoc(id);
        });

       
    Validar_Informacion();
    function Validar_Informacion(){

        if(document.getElementById('checkboxSuccess1').checked==false){
            $("#btn_registro").addClass("disabled");
        }else{
            $("#btn_registro").removeClass("disabled");
        }
    }

    // Al elegir el PDF el servidor comprueba las firmas que trae y las muestra
    // en #firma_del_archivo. Ya no hay casilla que declare la firma sin verificarla.
    // Número automático del documento interno (migración 026): lo emite el área
    // del usuario. Un trámite externo trae el número que le puso quien lo envía.
    if (typeof Correlativo_Enganchar === "function") {
      var sugerirNumero = Correlativo_Enganchar(
        function () { return document.getElementById("txtidprincipalarea").value; },
        function () { var c = document.getElementById("chk_externo"); return !!(c && c.checked); }
      );
      $("#chk_externo").on("change", function () { if (sugerirNumero) sugerirNumero(); });
    }

    // Si firma.js no cargó, el registro debe seguir funcionando: sin esta guarda
    // un error aquí cortaría el resto del guion de la vista.
    if (typeof Verificar_PDF_Al_Elegir === "function") {
      Verificar_PDF_Al_Elegir(
        document.getElementById("txt_archivo"),
        document.getElementById("firma_del_archivo")
      );
    }

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
                $("#txt_archivo").val("");
                return;
                //$("#btn_subir").prop("disabled",true);
            }else{
                //$("#btn_subir").attr("disabled",false);
            }
            $("#txtformato").val(ext);
        }
        else{
            $("#txt_archivo").val("");
            Swal.fire("Mensaje de Error","Extensión no permitida: " + ext,
            "error");
        }
        }
    });
var input=  document.getElementById('txt_dni');
input.addEventListener('input',function(){
  if (this.value.length > 8) 
     this.value = this.value.slice(0,8); 
})
var input=  document.getElementById('txt_celular');
input.addEventListener('input',function(){
  if (this.value.length > 9) 
     this.value = this.value.slice(0,9); 
})
var input=  document.getElementById('txt_folio');
input.addEventListener('input',function(){
  if (this.value.length > 3) 
     this.value = this.value.slice(0,3); 
})
// Solo las casillas de acciones. Sin acotar el selector entraban también la
// de términos y la de trámite externo, que no tienen value: aportaban "on"
// cada una y se guardaba "...ONON" en documento.acciones.
var checkboxes = document.querySelectorAll('.checkbox-card-container input[type=checkbox]');
var text = document.getElementById('txt_acciones');

function checkboxClick(event) {
  var valor = '';
  for (var i = 0; i < checkboxes.length; i++) {
    if (checkboxes[i].checked) {
      valor += checkboxes[i].value;
    }
  }
  txt_acciones.value = valor;
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
</script>
