<script src="../js/console_tramite.js?rev=<?php echo time();?>"></script>
<link rel="stylesheet" href="../plantilla/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css">


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
                        <h3 class="card-title"><i class="fas fa-tasks"></i> <b>DATOS DEL TRÁMITE</b></h3>
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
                            <div class="col-6 form-group">
                                <label for="" style="font-size:small;">N° DNI(*):</label>
                                <select type="text" class="form-control js-example-basic-single" id="txt_dni" style="width:100%"></select>
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

                </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 form-group" style="color:red">
                                <h7><b>Campos Obligatorios (*)</b></h7>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">Procedencia del Documento(*):</label>
                                <select class="js-example-basic-single" id="select_area_p" style="width:100%"></select>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">Área de Destino(*):</label>
                                <select class="js-example-basic-single" id="select_area_d" style="width:100%"></select>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">Tipo Documento(*):</label>
                                <select class="js-example-basic-single" id="select_tipo" style="width:100%"></select>
                            </div>
                            <div class="col-12 form-group" style="color:red">
                            <label for="">Requisitos: "OJO los documentos como requisitos deben estar en un solo archivo junto al documento principal a presentar"</label>
                                <textarea style="color:red" class="form-control" id="txt_requisitos" readonly rows="2" style="resize:none"></textarea>
                            </div>
                            <div class="col-4 form-group">
                                <label for="" style="font-size:small;">N° Expediente(*):</label>
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
                                                   <div class="col-12">
                            <div class="form-group clearfix">
                                <div class="icheck-success d-inline">
                                    <input type="checkbox" id="checkboxSuccess2">
                                    <label for="checkboxSuccess2" style="align:justify;color:red">
                                        Tiene el documento firmado digitalmente, si es así dale click para habilitar la subida de documentos.
                                    </label>
                                </div>
                            </div>
                        </div>
                            <div class="col-12 form-group">
                                <label for="" style="font-size:small;">Adjuntar Documento(*):</label>
                                <input class="form-control" type="file" id="txt_archivo" disabled><br>
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
                            <div class="col-12" style="text-align:center; margin-top: 1.5rem;">
                                <button class="btn btn-gradient-success btn-lg" onclick="Registrar_Tramite()" id="btn_registro">
                                    <i class="fas fa-save"></i> <b>REGISTRAR TRÁMITE</b>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    $(document).ready(function () {
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
        Cargar_Select_Area_REMI();
        Cargar_Select_DNI()
        Cargar_Select_Tipo();
        Cargar_Select_Area();
        Cargar_Select_Area_Copias();
        
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

    let precio = document.getElementById("txt_archivo")
    let cajaChecada = document.getElementById("checkboxSuccess2")
    
    cajaChecada.addEventListener("click", () => {
      if(precio.disabled) {
        precio.disabled = false
      } else {
        precio.disabled = true
      }
    })
    
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
var checkboxes = document.querySelectorAll('input[type=checkbox]');
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
