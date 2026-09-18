var tbl_tramite;
/*
 * Botón "Observar" (migración 025). Solo para trámites EXTERNOS, que son los que el
 * ciudadano subsana desde el portal, y solo si el rol puede observar.
 */
function Boton_Observar(row){
  if (row.procedencia !== "EXTERNO") return "";
  if (typeof puede === "function" && !puede("observar")) return "";
  return "<button class='observar btn btn-warning btn-sm' title='Pedir al ciudadano que corrija o complete'>" +
         "<i class='fas fa-exclamation-circle'></i> Observar</button>&nbsp;";
}

$(document).on('click', '#tabla_tramite .observar', function(){
  var tr = $(this).closest('tr');
  if(tr.hasClass('child')){ tr = tr.prev(); }
  var data = tbl_tramite.row(tr).data();
  if(!data){ return; }

  Swal.fire({
    title: "Observar trámite",
    html: "<div style='text-align:left'>" +
      "<p class='mb-2'>Expediente <b>"+escaparTexto(data.doc_expediente || data.documento_id)+"</b><br>" +
      "<small class='text-muted'>El ciudadano verá la observación en el portal y podrá subsanar desde ahí, " +
      "sin acudir a la entidad. Si dejó correo, se le avisa.</small></p>" +
      "<label style='font-size:small'>¿Qué debe corregir o completar? (*)</label>" +
      "<textarea id='txt_obs_motivo' class='form-control' rows='5' maxlength='2000' " +
        "placeholder='Ej.: Falta adjuntar la copia de DNI del representante y la vigencia de poder.'></textarea>" +
      "<label class='mt-3' style='font-size:small'>Plazo para subsanar (días hábiles)</label>" +
      "<input type='number' id='txt_obs_plazo' class='form-control' min='1' max='30' value='2'>" +
      "<small class='text-muted'>Se cuentan solo días hábiles: sin fines de semana ni feriados.</small>" +
      "</div>",
    showCancelButton: true,
    confirmButtonText: "Observar",
    confirmButtonColor: "#B45309",
    cancelButtonText: "Cancelar",
    focusConfirm: false,
    preConfirm: function(){
      var motivo = document.getElementById('txt_obs_motivo').value.trim();
      var plazo = parseInt(document.getElementById('txt_obs_plazo').value, 10);
      if(motivo.length < 10){ Swal.showValidationMessage("Explique qué debe corregir (mínimo 10 caracteres)"); return false; }
      if(!(plazo >= 1 && plazo <= 30)){ Swal.showValidationMessage("El plazo debe estar entre 1 y 30 días hábiles"); return false; }
      return $.ajax({
        url:"../controller/tramite/controlador_observar_tramite.php", type:"POST", dataType:"json",
        data:{ id: data.documento_id, motivo: motivo, plazo: plazo }
      }).then(function(r){ return r; }, function(xhr){
        Swal.showValidationMessage((xhr.responseJSON && xhr.responseJSON.mensaje) || "No se pudo observar el trámite");
        return false;
      });
    }
  }).then(function(res){
    if(res.isConfirmed && res.value){
      var r = res.value;
      Swal.fire({
        icon: "success",
        title: "Trámite observado",
        html: "El ciudadano tiene plazo hasta el <b>"+escaparTexto(r.limite)+"</b> para subsanar." +
              (r.avisado
                ? "<br><small class='text-muted'>Se le avisó por correo.</small>"
                : "<br><small style='color:#B45309;'>" + (r.tiene_correo
                    ? "No se pudo enviar el correo: avísele por otro medio."
                    : "El ciudadano no dejó correo: avísele por teléfono o al atenderlo.") + "</small>")
      });
      tbl_tramite.ajax.reload(null,false);
    }
  });
});

function listar_tramite(){
    let idusuario = document.getElementById('txtprincipalid').value;
  tbl_tramite = $("#tabla_tramite").DataTable({
      "ordering":false,   
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 10,
      "destroy":true,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "async": false ,
      "processing": true,
      // Migración 024: no ofrecer lo que el servidor va a rechazar por el rol
      "drawCallback": function(){
        if (typeof puede === "function" && !puede("derivar")) {
          $("#tabla_tramite .derivar").remove();
        }
        // Rechazar cierra el expediente: exige el mismo permiso que finalizar
        if (typeof puede === "function" && !puede("finalizar")) {
          $("#tabla_tramite .rechazar").remove();
        }
      },
      "ajax":{
          "url":"../controller/tramite_area/controlador_listar_tramite.php",
          type:'POST',
          data:{
            idusuario:idusuario
          }
      },
      
      "columns":[
        {"data":"documento_id"},
        {"data":"doc_expediente", render: Render_Expediente, responsivePriority: 1},
        {"data":"doc_nrodocumento"},
        {"data":"doc_fecharegistro", render: Render_Fecha_Registro},
        {"data":"tipodo_descripcion"},
        {"data":"doc_dniremitente"},
        {"data":"REMITENTE"},
        {"defaultContent":"<button class='mas btn btn-danger  btn-sm' title='Ver más datos'><i class='fa fa-search'></i><b> Ver</b></button>"},
        {"defaultContent":"<button class='seguimiento btn btn-success  btn-sm' title='Ver documentos'><i class='fa fa-search'></i><b> Ver</b></button>"},
        {"data":"origen"},
        {"data":"destino"},
        {"data":"doc_estatus",
        render: function(data,type,row){
                if(data=='PENDIENTE'){
                    return '<span class="badge bg-warning">PENDIENTE</span>';
                }else if(data=='RECHAZADO'){
                    return '<span class="badge bg-danger">RECHAZADO</span>';
                }else if(data=='ACEPTADO'){
                    return '<span class="badge bg-success">ACEPTADO</span>';
                }else if(data=='FINALIZADO'){
                  return '<span class="badge bg-primary">FINALIZADO</span>';
              }else if(data=='OBSERVADO'){
                  // Migración 025: espera que el ciudadano subsane desde el portal
                  return '<span class="badge bg-warning" title="Espera la subsanación del ciudadano"><i class="fas fa-exclamation-circle"></i> OBSERVADO</span>';
              }
            }
             
        },
        {"data":"dias_pasados", render: Render_Dias_Area},
        {"data":"dias_respuesta", render: Render_Plazo, responsivePriority: 2},
        {"data":"doc_estatus",
        render: function(data,type,row){
                // Llegó en copia: es para conocimiento, esta área no decide sobre él.
                // Se le pidió atención: confirma recepción y responde; no decide sobre el trámite
                if(row.es_atencion == 1){
                  var botones = "";
                  if(!row.acuse_fecha){
                    botones += "<button class='acuse-copia btn btn-sm btn-archivo' title='Confirmar que su área recibió el pedido'><i class='fas fa-inbox'></i> Confirmar recepción</button> ";
                  }
                  if(row.atencion_respondida){
                    return botones + "<span class='badge badge-atencion' title='Respuesta registrada'><i class='fas fa-check'></i> Atendido · "+row.atencion_respondida+"</span>";
                  }
                  return botones + "<button class='responder-atencion btn btn-sm btn-gradient-primary' title='Registrar la respuesta de su área'><i class='fas fa-reply'></i> Responder</button>";
                }
                if(row.es_copia == 1){
                  if(row.acuse_fecha){
                    return "<span class='badge badge-copia' title='Recepción confirmada'><i class='fas fa-check'></i> Copia recibida · "+row.acuse_fecha+"</span>";
                  }
                  return "<button class='acuse-copia btn btn-sm btn-archivo' title='Confirmar que su área recibió esta copia'><i class='fas fa-inbox'></i> Confirmar recepción</button>";
                }
                // Migración 025: mientras el ciudadano no subsane, el trámite queda en espera
                if(data=='OBSERVADO'){
                  return "<span class='badge badge-copia' title='El ciudadano debe subsanar desde el portal'><i class='fas fa-hourglass-half'></i> Esperando subsanación</span>";
                }
                if(data=='PENDIENTE'){
                    return Boton_Observar(row) + "</button>&nbsp;<button  title='Aceptar Documento' class='aceptar btn btn-success  btn-sm'><i class='fa fa-check'></i> Aceptar</button>&nbsp;<button  title='Rechazar Documento' class='rechazar btn btn-danger  btn-sm'><i class='fa fa-search'></i> Rechazar</button>&nbsp;<button hidden class='derivar btn btn-primary  btn-sm' title='Derivar Documento'><i class='fa fa-share-square'></i> Derivar</button>";
                }else if (data=='ACEPTADO'){
                  return Boton_Observar(row) + "</button>&nbsp;<button hidden title='Aceptar Documento' class='aceptar btn btn-success  btn-sm'><i class='fa fa-check'></i> Aceptar</button>&nbsp;<button hidden title='Rechazar Documento' class='rechazar btn btn-danger  btn-sm'><i class='fa fa-search'></i> Rechazar</button>&nbsp;<button class='derivar btn btn-primary  btn-sm' title='Derivar Documento'><i class='fa fa-share-square'></i> Derivar</button>";
                }else if (data=='RECHAZADO'){
                  return "</button>&nbsp;<button hidden title='Aceptar Documento' class='aceptar btn btn-success  btn-sm'><i class='fa fa-check'></i> Aceptar</button>&nbsp;<button hidden title='Rechazar Documento' class='rechazar btn btn-danger  btn-sm'><i class='fa fa-search'></i> Rechazar</button>&nbsp;<button hidden class='derivar btn btn-primary  btn-sm' title='Derivar Documento'><i class='fa fa-share-square'></i> Derivar</button>";
                }else if (data=='FINALIZADO'){
                  return "</button>&nbsp;<button hidden title='Aceptar Documento' class='aceptar btn btn-success  btn-sm'><i class='fa fa-check'></i> Aceptar</button>&nbsp;<button hidden title='Rechazar Documento' class='rechazar btn btn-danger  btn-sm'><i class='fa fa-search'></i> Rechazar</button>&nbsp;<button hidden class='derivar btn btn-primary  btn-sm' title='Derivar Documento'><i class='fa fa-share-square'></i> Derivar</button>";
                }
            }
             
        },
       
               
    ],

    "language":idioma_espanol,
    select: true
});
tbl_tramite.on('draw.td',function(){
  var PageInfo = $("#tabla_tramite").DataTable().page.info();
  tbl_tramite.column(0, {page: 'current'}).nodes().each(function(cell, i){
    cell.innerHTML = i + 1 + PageInfo.start;
  });
});
}

$('#tabla_tramite').on('click','.derivar',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
  // Migración 024: mesa de partes deriva pero no finaliza
  if (typeof puede === "function") {
    $("#select_derivar_de option[value='FINALIZAR']").prop("disabled", !puede("finalizar")).prop("hidden", !puede("finalizar"));
    if (!puede("finalizar")) { $("#select_derivar_de").val("DERIVAR").trigger("change"); }
  }
  $("#modal_derivar").modal('show');
  document.getElementById('lb_titulo_derivar').innerHTML="<b>DERIVAR O FINALIZAR TRAMITE: </b>"+data.documento_id;
  document.getElementById('txt_fecha_de').value=data.doc_fecharegistro;
  document.getElementById('txt_origen_de').value=data.destino;
  Cargar_Select_Area_Destino(data.area_destino);
  document.getElementById('txt_iddocumento_de').value=data.documento_id;
  document.getElementById('txt_idareaorigen').value=data.area_destino;

  
})
$('#tabla_tramite').on('click','.rechazar',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
}
$("#modal_rechazar").modal('show');
document.getElementById('lb_titulo_derivar2').innerHTML="<b>RECHAZAR TRAMITE Nº:</b> <b style='color:red'>"+data.documento_id+"</b>";
document.getElementById('txt_fecha_de2').value=data.doc_fecharegistro;
document.getElementById('area_destino2').value=data.destino;
document.getElementById('txt_iddocumento_de2').value=data.documento_id;
document.getElementById('txt_idareaorigen2').value=data.area_destino;

})


$('#tabla_tramite').on('click','.seguimiento',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
$("#modal_seguimiento").modal('show');
  document.getElementById('nro_expediente_seguimiento').innerHTML=data.documento_id;
  listar_seguimiento_tramite(data.documento_id);
})

$('#tabla_tramite').on('click','.mas',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }


$("#modal_mas").modal('show');
document.getElementById('txt_ndocumento').value=data.doc_nrodocumento;
document.getElementById('txt_folio').value=data.doc_folio;
document.getElementById('txt_asunto').value=data.doc_asunto;
document.getElementById('txt_observacion').value=data.doc_observaciones;
document.getElementById('txt_tiempo_respuesta').value=data.dias_respuesta;
document.getElementById('txt_acciones').value=data.acciones;
// El título ahora muestra el expediente oficial; si el listado aún no lo
// trae, se cae al número de documento de siempre.
document.getElementById('lb_titulo_datos').innerHTML = "DATOS DEL EXPEDIENTE Nº: " + (data.doc_expediente || data.doc_nrodocumento);
if (typeof Cargar_Anexos === "function") { Cargar_Anexos(data.documento_id); }
$("#select_area_p").select2().val(data.area_origen).trigger('change.select2');
$("#select_area_d").select2().val(data.area_destino).trigger('change.select2');
$("#select_tipo").select2().val(data.tipodocumento_id).trigger('change.select2');


document.getElementById('txt_dni').value=data.doc_dniremitente;
document.getElementById('txt_nom').value=data.doc_nombreremitente;
document.getElementById('txt_apepat').value=data.doc_apepatremitente;
document.getElementById('txt_apemat').value=data.doc_apematremitente;
document.getElementById('txt_celular').value=data.doc_celularremitente;
document.getElementById('txt_email').value=data.doc_emailremitente;
document.getElementById('txt_dire').value=data.doc_direccionremitente;
document.getElementById('txt_dire').value=data.doc_direccionremitente;
document.getElementById('txt_ruc').value = data.doc_ruc;
document.getElementById('txt_razon').value = data.doc_empresa;

  // Lógica mejorada para la selección del tipo de representación
  if (data.doc_representacion == "A NOMBRE PROPIO") {
      $("#rad_presentacion1").prop('checked', true);
      document.getElementById('div_juridico').style.display = "none"; // Oculta el div si no es persona jurídica
  } else if (data.doc_representacion == "A OTRA PERSONA NATURAL") {
      $("#rad_presentacion2").prop('checked', true);
      document.getElementById('div_juridico').style.display = "none";
  } else if (data.doc_representacion == "PERSONA JURíDICA") {
      $("#rad_presentacion3").prop('checked', true);
      document.getElementById('div_juridico').style.display = "block"; // Muestra el div si es persona jurídica
  } else {
      document.getElementById('div_juridico').style.display = "none"; // Oculta el div en otros casos
  }

  // Agregar eventos para los radio buttons para manejar la visibilidad del div_juridico
  $("#rad_presentacion3").on('click', function () {
      document.getElementById('div_juridico').style.display = "block";
  });

  $("#rad_presentacion1, #rad_presentacion2").on('click', function () {
      document.getElementById('div_juridico').style.display = "none";
  });
});


function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}


function Registrar_Derivacion(){

  let iddo = document.getElementById('txt_iddocumento_de').value;
  let orig = document.getElementById('txt_idareaorigen').value;
  let dest = document.getElementById('select_destino_de').value;
  let desc = document.getElementById('txt_descripcion_De').value;
  let arc = document.getElementById('txt_documento_de').value;
  let idusu = document.getElementById('txtprincipalid').value;
  let tipo = document.getElementById('select_derivar_de').value;
  let acc = document.getElementById('txt_acciones2').value;
  
  // Capturar las copias seleccionadas
  let copias = $('#select_area_copias_derivar').val(); // Array de IDs de áreas
  if(!copias) copias = []; // Si no hay selección, array vacío

  let nombrearchivo="";

  // Antes mostraban el aviso pero seguían y enviaban la derivación igual
  if(tipo=="DERIVAR" && dest.length==0){
    return Swal.fire("Mensaje de Advertencia","Seleccionar el área destino","warning");
  }
  if(acc.length==0){
    return Swal.fire("Mensaje de Advertencia","Seleccionar al menos una acción a realizar","warning");
  }

  // Finalizar con atenciones sin responder: se avisa antes
  if(tipo=="FINALIZAR" && !window._finalizarConfirmado){
    $.post("../controller/tramite_area/controlador_listar_atenciones.php",{id:iddo},null,"json").done(function(r){
      var pendientes = (r.data || []).filter(function(a){ return !a.respuesta_fecha; });
      if(pendientes.length === 0){
        window._finalizarConfirmado = true;
        Registrar_Derivacion();
        return;
      }
      var lista = pendientes.map(function(a){ return "<li>"+escaparTexto(a.area)+"</li>"; }).join("");
      Swal.fire({
        title: "Hay atenciones sin responder",
        html: "Estas áreas todavía no respondieron:<ul style='text-align:left'>"+lista+"</ul>¿Desea finalizar el trámite de todos modos?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Finalizar de todos modos",
        confirmButtonColor: "#B91C1C",
        cancelButtonText: "Esperar respuestas"
      }).then(function(res){
        if(res.isConfirmed){ window._finalizarConfirmado = true; Registrar_Derivacion(); }
      });
    });
    return false;
  }
  window._finalizarConfirmado = false;
  if(arc==""){

  }else{
    let f = new Date();
    let extension = arc.split('.').pop();//DOCUMENTO.PPT
    nombrearchivo="ARCH"+f.getDate()+"-"+(f.getMonth()+1)+"-"+f.getFullYear()+"-"+f.getHours()+"-"+f.getMilliseconds()+"."+extension;
  }
  let formData = new FormData();
  let achivoobj = $("#txt_documento_de")[0].files[0];//El objeto del archivo adjuntado

  //////DATOS DERIVACION/////
  formData.append("iddo",iddo);
  formData.append("orig",orig);
  formData.append("dest",dest);
  formData.append("desc",desc);
  formData.append("idusu",idusu);
  formData.append("nombrearchivo",nombrearchivo);
  formData.append("achivoobj",achivoobj);
  // Anexos opcionales: archivos adicionales al documento principal
  if($("#txt_anexos").length && $("#txt_anexos")[0].files.length){
    var listaAnexos = $("#txt_anexos")[0].files;
    for(var iAnexo=0; iAnexo<listaAnexos.length; iAnexo++){
      formData.append("anexos[]", listaAnexos[iAnexo]);
    }
  }
  formData.append("tipo",tipo);
  formData.append("acc",acc);
  formData.append("copias",JSON.stringify(copias)); // Enviar copias como JSON
  formData.append("atenciones",JSON.stringify(Leer_Atenciones_Derivar()));

  /*
   * Firma del documento que se adjunta al derivar: lo produce esta área, así que
   * sale firmado. Derivar en sí no se firma (es enrutamiento, ya queda registrado
   * con usuario, fecha y acuse). Es opcional.
   */
  var certDer = document.getElementById("txt_cert_derivar");
  var certArchivoDer = certDer && certDer.files[0];
  if (certArchivoDer) {
    if (!achivoobj) {
      return Swal.fire("Mensaje de Advertencia", "Adjunte el documento que va a firmar, o quite el certificado", "warning");
    }
    var claveDer = document.getElementById("txt_clave_derivar").value;
    if (!/\.(pfx|p12)$/i.test(certArchivoDer.name)) {
      return Swal.fire("Mensaje de Advertencia", "El certificado debe ser un archivo .pfx o .p12", "warning");
    }
    if (!claveDer) {
      return Swal.fire("Mensaje de Advertencia", "Escriba la contraseña de su certificado", "warning");
    }
    formData.append("certificado", certArchivoDer);
    formData.append("clave", claveDer);
    formData.append("motivo", document.getElementById("txt_motivo_derivar").value);
    formData.append("firmar_anexos", document.getElementById("chk_firmar_anexos_derivar").checked ? "1" : "0");
  }

  $.ajax({
    url:"../controller/tramite_area/controlador_registro_tramite.php",
    type:'POST',
    data:formData,
    contentType:false,
    processData:false,
    success:function(resp){
      if(resp.length>0){
        Swal.fire("Mensaje de Confirmación","Tramite Derivado o Finalizado","success").then((value)=>{
          $("#modal_derivar").modal('hide');
          // La contraseña no debe quedar escrita tras derivar
          if(document.getElementById('txt_cert_derivar')){
            document.getElementById('txt_cert_derivar').value="";
            document.getElementById('txt_clave_derivar').value="";
            document.getElementById('txt_motivo_derivar').value="";
          }
                      tbl_tramite.ajax.reload();

          
      });
      }else{
        Swal.fire("Mensaje de Advertencia","No se pudo completar el proceso","warning");
      }
    }
  });
  return false;
}
///RECHAZAR
function Rechazar_Tramite(){
  let id2 = document.getElementById('txt_iddocumento_de2').value;
  let desc2 = document.getElementById('txt_descripcion_De2').value;
  let loc = document.getElementById('txt_idareaorigen2').value;

  if(id2.length==0 ||desc2.length==0){
      return Swal.fire("Mensaje de Advertencia","Llene el motivo de rechazo","warning");
  }
  $.ajax({
    "url":"../controller/tramite/controlador_rechazar_tramite.php",
    type:'POST',
    data:{
      id2:id2,
      desc2:desc2,
      loc:loc
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se rechazo el documento","success").then((value)=>{
          tbl_tramite.ajax.reload();
        $("#modal_rechazar").modal('hide');

        });
 
    }else{
      return Swal.fire("Mensaje de Advertencia","No se pudo rechazar el documento","warning");

    }
  })
}
/// ACEPTAR
$('#tabla_tramite').on('click','.aceptar',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
    Swal.fire({
      title: 'Desea aceptar el documento Nº '+data.documento_id+'?',
      text: "Una vez aceptado el documento usted podra finalizar o derivar el documento",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#1E3A5F',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Aceptar'
    }).then((result) => {
      if (result.isConfirmed) {
        Modificar_Estatus_Documento(data.documento_id,'ACEPTADO', data.doc_expediente);
      }
    })

})

// id = código del trámite. Antes se mandaba el Nº de documento del ciudadano,
// que se repite entre trámites y hacía que aceptar uno aceptara varios.
function Modificar_Estatus_Documento(id,estatus,etiqueta){
  let esta=estatus;

  if(esta==="ACEPTADO"){
    esta="Acepto";
  }
  $.ajax({
    "url":"../controller/tramite_area/controlador_modificar_tramite_estatus.php",
    type:'POST',
    data:{
      id:id,
      estatus:estatus
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se "+esta+ " con éxito el expediente Nº "+(etiqueta || id),"success").then((value)=>{
          tbl_tramite.ajax.reload();
        });
    }else{
      return Swal.fire("Mensaje de Error","No se pudo ACEPTAR el documento","error");

    }
  })
}


function Cargar_Select_Area_REMI(){
    $.ajax({
      "url":"../controller/usuario/controlador_cargar_select_area.php",
      type:'POST',
    }).done(function(resp){
      let data=JSON.parse(resp);
      if(data.length>0){
        let cadena ="<option value=''>Seleccionar Área</option>";
        for (let i = 0; i < data.length; i++) {
          cadena+="<option value='"+data[i][0]+"'> Área: "+data[i][1]+" - Remitente: "+data[i][5]+"<option>";
    
        }
          document.getElementById('select_area_p').innerHTML=cadena;

      }else{
        cadena+="<option value=''>No hay áreas disponibles</option>";
        document.getElementById('select_area_p').innerHTML=cadena;

      }
    })
}

function Cargar_Select_Area(){
    $.ajax({
      "url":"../controller/usuario/controlador_cargar_select_area.php",
      type:'POST',
    }).done(function(resp){
      let data=JSON.parse(resp);
      if(data.length>0){
        let cadena ="<option value=''>Seleccionar Área</option>";
        for (let i = 0; i < data.length; i++) {
          cadena+="<option value='"+data[i][0]+"'> Área: "+data[i][1]+" - Destinatario: "+data[i][5]+"<option>";
    
        }
          document.getElementById('select_area_d').innerHTML=cadena;

      }else{
        cadena+="<option value=''>No hay áreas disponibles</option>";
        document.getElementById('select_area_d').innerHTML=cadena;

      }
    })
}
function Cargar_Select_Area_Copias(){
  $.ajax({
    "url":"../controller/usuario/controlador_cargar_select_area.php",
    type:'POST',
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i][0]+"'>"+data[i][1]+"</option>";    
      }
        $('#select_area_copias').html(cadena);

    }else{
      cadena+="<option value=''>No hay áreas disponibles</option>";
      $('#select_area_copias').html(cadena);

    }
  })
}
function Registrar_Tramite(){
  //DATOS DEL REMITENTE
    let dni = document.getElementById('txt_dni').value.trim();
    let dni2 = document.getElementById('txt_dni2').value.trim();
    let nom = document.getElementById('txt_nom').value;
    let apt = document.getElementById('txt_apepat').value;
    let apm = document.getElementById('txt_apemat').value;
    let cel = document.getElementById('txt_celular').value;
    let ema = document.getElementById('txt_email').value;
    let dir = document.getElementById('txt_dire').value;
    let idusu = document.getElementById('txtprincipalid').value;

    let presentacion = document.getElementsByName("r1");
    let vpresentacion ="";
    for (let i = 0; i < presentacion.length; i++) {
      if(presentacion[i].checked){
        vpresentacion = presentacion[i].value;
      }
      
    }
    let ruc = document.getElementById('txt_ruc').value;
    let raz = document.getElementById('txt_razon').value;

  //DATOS DEL DOCUMENTO
    let arp = document.getElementById('txtidprincipalarea').value;
    let ard = document.getElementById('select_area_d').value;
    let tip = document.getElementById('select_tipo').value;
    let ndo = document.getElementById('txt_ndocumento').value;
    let asu = document.getElementById('txt_asunto').value;
    let arc = document.getElementById('txt_archivo').value;
    let fol = document.getElementById('txt_folio').value;
    let acc = document.getElementById('txt_acciones').value;
    let obs = document.getElementById('txt_observacion').value;
    let tre = document.getElementById('txt_tiempo_respuesta').value;
    
    // Capturar las copias seleccionadas
    let copias = $('#select_area_copias').val(); // Array de IDs de áreas
    if(!copias) copias = []; // Si no hay selección, array vacío

    if(arc.length==0){
      return Swal.fire("Mensaje de Advertencia","Seleccione algún tipo de documento","warning")
    }

    let extension = arc.split('.').pop();//DOCUMENTO.PPT
    let nombrearchivo="";
    let f = new Date();
    
    if(arc.length>0){
      nombrearchivo="ARCH"+f.getDate()+"-"+(f.getMonth()+1)+"-"+f.getFullYear()+"-"+f.getHours()+"-"+f.getMilliseconds()+"."+extension;
    }
   let documentoFinal = '';
    let esExterno = document.getElementById("chk_externo").checked;

    if (esExterno) {
        // Validar input manual
        let dni2 = document.getElementById("txt_dni2").value.trim();
        if (!dni2) {
            return Swal.fire("Mensaje de Advertencia", "El campo DNI es obligatorio para trámite externo", "warning");
        }
        documentoFinal = dni2;
    } else {
        // Validar select
        let dni = document.getElementById("txt_dni").value.trim();
        if (!dni) {
            return Swal.fire("Mensaje de Advertencia", "Debe seleccionar un DNI", "warning");
        }
        documentoFinal = dni;
    }


    if(nom.length==0 ||apt.length==0 ||  apm.length==0 || cel.length==0 ||
      dir.length==0 ){
        return Swal.fire("Mensaje de Advertencia","Llene todo los campos del remitente","warning")
      }
    if(arp.length==0 || ard.length==0 ||tip.length==0 ||  ndo.length==0 || asu.length==0 || fol.length==0){
        return Swal.fire("Mensaje de Advertencia","Llene todo los campos del documento","warning")
    }
    if(acc.length==0 ){
      return Swal.fire("Mensaje de Advertencia","Seleccione al menos una acción a realizar","warning")
    }
    let formData = new FormData();
    let achivoobj = $("#txt_archivo")[0].files[0];//El objeto del archivo adjuntado

    //////DATOS DEL REMITENTE/////
    formData.append("documentoFinal",documentoFinal);
    formData.append("nom",nom);
    formData.append("apt",apt);
    formData.append("apm",apm);
    formData.append("cel",cel);
    formData.append("ema",ema);
    formData.append("dir",dir);
    formData.append("vpresentacion",vpresentacion);
    formData.append("ruc",ruc);
    formData.append("raz",raz);
    ///////DATOS DEL DOCUMENTO//////
    formData.append("arp",arp);
    formData.append("ard",ard);
    formData.append("tip",tip);
    formData.append("ndo",ndo);
    formData.append("asu",asu);
    formData.append("nombrearchivo",nombrearchivo);
    formData.append("fol",fol);
    formData.append("achivoobj",achivoobj);
    // Anexos opcionales: archivos adicionales al documento principal
    if($("#txt_anexos").length && $("#txt_anexos")[0].files.length){
      var listaAnexos = $("#txt_anexos")[0].files;
      for(var iAnexo=0; iAnexo<listaAnexos.length; iAnexo++){
        formData.append("anexos[]", listaAnexos[iAnexo]);
      }
    }
    formData.append("idusu",idusu);
    formData.append("acc",acc);
    formData.append("obs",obs);
    formData.append("tre",tre);
    // Procedencia: un trámite externo trae el documento ya firmado de fuera y no se
    // firma en el sistema; uno interno lo produce la entidad y sí se firma (migración 021).
    formData.append("procedencia", esExterno ? "EXTERNO" : "INTERNO");
    // Migración 026: si el número se dejó automático, el servidor gasta el
    // siguiente del área al guardar (puede no ser el que se vio, si hubo carrera)
    formData.append("correlativo_auto", (typeof Correlativo_EsAutomatico === "function" && Correlativo_EsAutomatico()) ? "1" : "0");

    /*
     * Firma al registrar: el documento propio sale firmado en vez de enviarse y
     * firmarse después desde el expediente. Es opcional; sin certificado el
     * trámite se registra igual y queda en la bitácora que salió sin firma.
     */
    var certReg = document.getElementById("txt_cert_registro");
    var certArchivo = certReg && certReg.files[0];
    if (certArchivo) {
      var claveReg = document.getElementById("txt_clave_registro").value;
      if (!/\.(pfx|p12)$/i.test(certArchivo.name)) {
        return Swal.fire("Mensaje de Advertencia", "El certificado debe ser un archivo .pfx o .p12", "warning");
      }
      if (!claveReg) {
        return Swal.fire("Mensaje de Advertencia", "Escriba la contraseña de su certificado", "warning");
      }
      var firmaPrincipal = document.getElementById("chk_firmar_principal").checked;
      var firmaAnexos = document.getElementById("chk_firmar_anexos").checked;
      if (!firmaPrincipal && !firmaAnexos) {
        return Swal.fire("Mensaje de Advertencia", "Marque qué documentos va a firmar, o quite el certificado", "warning");
      }
      formData.append("certificado", certArchivo);
      formData.append("clave", claveReg);
      formData.append("motivo", document.getElementById("txt_motivo_registro").value);
      formData.append("firmar_principal", firmaPrincipal ? "1" : "0");
      formData.append("firmar_anexos", firmaAnexos ? "1" : "0");
    }
    formData.append("copias",JSON.stringify(copias)); // Enviar copias como JSON


    $.ajax({
      url:"../controller/tramite/controlador_registro_tramite_ul.php",
      type:'POST',
      data:formData,
      contentType:false,
      processData:false,
      success:function(resp){
        if(resp.length>0){
          Swal.fire("Mensaje de Confirmación","Nueva Tramite Registrado código: "+resp,"success").then((value)=>{
            window.open("MPDF/REPORTE/ficha_seguimiento.php?codigo="+resp+"#zomm=100");
            cargar_contenido("contenido_principal", "tramite_area/view_tramite_enviados.php");
            document.getElementById('txt_dni2').value="";
            document.getElementById('txt_nom').value="";
            document.getElementById('txt_apepat').value="";
            document.getElementById('txt_apemat').value="";
            document.getElementById('txt_celular').value="";
            document.getElementById('txt_email').value="";
            document.getElementById('txt_dire').value="";
            document.getElementById('txt_ruc').value;
            document.getElementById('txt_razon').value="";
  //DATOS DEL REMITENTE
            document.getElementById('select_area_p').value="";
            document.getElementById('select_area_d').value="";
            document.getElementById('select_tipo').value="";
            document.getElementById('txt_ndocumento').value="";
            document.getElementById('txt_asunto').value="";
            document.getElementById('txt_folio').value="";
            document.getElementById('txt_acciones').value="";
            // La contraseña no debe quedar escrita en pantalla tras registrar
            if(document.getElementById('txt_cert_registro')){
              document.getElementById('txt_cert_registro').value="";
              document.getElementById('txt_clave_registro').value="";
              document.getElementById('txt_motivo_registro').value="";
            }
            document.getElementById('txt_observacion').value="";
            document.getElementById('txt_tiempo_respuesta').value="";

          });
        }else{
          Swal.fire("Mensaje de Advertencia","No se pudo realizar el","warning");
        }
      }
    });
    return false;
}

function Cargar_Select_DNI_UL(){
  let id = document.getElementById('txtprincipalid').value;

  $.ajax({
    "url":"../controller/tramite_area/controlador_cargar_DNI_ul.php",
    type:'POST',
    data:{
      id:id
  }
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="<option value=''>Seleccionar Remitente</option>";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i][1]+"'> DNI: "+data[i][1]+" - Remitente: "+data[i][5]+" - Área: "+data[i][6]+"</option>";    
      }
        document.getElementById('txt_dni').innerHTML=cadena;
    }else{
      cadena+="<option value=''>No hay tipos disponibles</option>";
      document.getElementById('txt_dni').innerHTML=cadena;
    }
  })
}
function Cargar_Select_Tipo(){
    $.ajax({
      "url":"../controller/tramite/controlador_cargar_select_tipo.php",
      type:'POST',
    }).done(function(resp){
      let data=JSON.parse(resp);
      if(data.length>0){
        let cadena ="<option value=''>Seleccionar Tipo Documento</option>";
        for (let i = 0; i < data.length; i++) {
          cadena+="<option value='"+data[i][0]+"'>"+data[i][1]+"</option>";    
        }
          document.getElementById('select_tipo').innerHTML=cadena;
      }else{
        cadena+="<option value=''>No hay tipos disponibles</option>";
        document.getElementById('select_tipo').innerHTML=cadena;
      }
    })
}

function Cargar_Select_Area_Destino(id){
  
  $.ajax({
    "url":"../controller/usuario/controlador_cargar_select_area.php",
    type:'POST',
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="<option value=''>Seleccionar Área</option>";
      for (let i = 0; i < data.length; i++) {
        //1!=3
        if(data[i][0]!=id){
        cadena+="<option value='"+data[i][0]+"'>"+data[i][1]+"</option>";    
        }
      }
        document.getElementById('select_destino_de').innerHTML=cadena;
    }else{
      cadena+="<option value=''>No hay áreas disponibles</option>";
      document.getElementById('select_destino_de').innerHTML=cadena;
    }
  })
}
//SEGUIMIENTO TRAMITE
var tbl_seguimiento;
function listar_seguimiento_tramite(id){
  tbl_seguimiento = $("#tabla_seguimiento").DataTable({
      "ordering":false,
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 10,
      "destroy":true,
      "async": false ,
      "processing": true,
      "ajax":{
          "url":"../controller/tramite/controlador_listar_tabla_seguimiento.php",
          type:'POST',
          data:{
            id:id
          }
      },
      "columns":[
        {"data":"area_origen_nombre",
          render: function(data, type, row){
            if(data == 'EXTERNO' || data == null){
              return '<span class="badge badge-secondary" style="font-size: 13px; padding: 8px 12px;"><i class="fas fa-building"></i> EXTERNO</span>';
            } else {
              return '<span class="badge badge-primary" style="font-size: 13px; padding: 8px 12px;"><i class="fas fa-map-marker-alt"></i> ' + data + '</span>';
            }
          }
        },
        {"data":"area_destino_nombre",
          render: function(data, type, row){
            return '<span class="badge badge-success" style="font-size: 13px; padding: 8px 12px;"><i class="fas fa-flag-checkered"></i> ' + data + '</span>';
          }
        },
        {"data":"fecha_formateada",
          render: function(data, type, row){
            return '<small><i class="far fa-calendar-alt"></i> ' + data + '</small>';
          }
        },
        {"data":"mov_descripcion",
          render: function(data, type, row){
            // Resaltar si es una copia
            if(data && data.toUpperCase().includes('COPIA')){
              return '<span style="color: #B91C1C; font-weight: bold;"><i class="fas fa-copy"></i> ' + data + '</span>';
            }
            return '<span>' + data + '</span>';
          }
        },
        {"data":"mov_estatus", render: function(data,type,row){ return Render_Estado_Movimiento(data,type,row,true); }},
        {"data":"mov_acciones",
          render: function(data, type, row){
            if(data && data.trim() != ''){
              return '<small style="color: #2c3e50;">' + data + '</small>';
            }
            return '<small class="text-muted">Sin acciones</small>';
          }
        },

        {"data":"mov_archivo", render: Render_Archivos_Movimiento},     
    ],

    "language":idioma_espanol,
    select: true
});

}

/// ACUSE DE COPIA: el área que recibió el trámite en copia confirma que lo recibió
$('#tabla_tramite').on('click','.acuse-copia',function(){
  var tr = $(this).closest('tr');
  if(tr.hasClass('child')){ tr = tr.prev(); } // vista adaptable: el botón vive en la fila hija
  var data = tbl_tramite.row(tr).data();
  if(!data){ return; }
  $.ajax({
    "url":"../controller/tramite_area/controlador_acuse_copia.php",
    type:'POST',
    dataType:"json",
    data:{ id:data.documento_id }
  }).done(function(){
    Swal.fire("Recepción confirmada","Quedó registrado que su área recibió la copia del expediente "+(data.doc_expediente || data.documento_id)+".","success");
    tbl_tramite.ajax.reload(null,false);
  });
});

/// ÁREAS PARA ATENCIÓN
// Una fila de plazo por cada área seleccionada en el modal de derivar
function Pintar_Plazos_Atencion(){
  var caja = document.getElementById('plazos_atencion_derivar');
  if(!caja){ return; }
  var anteriores = {};
  $(caja).find('input[data-area]').each(function(){ anteriores[this.getAttribute('data-area')] = this.value; });
  var html = "";
  $('#select_area_atencion_derivar option:selected').each(function(){
    var id = this.value;
    var plazo = anteriores[id] !== undefined ? anteriores[id] : "3";
    html += "<div class='d-flex align-items-center mb-1' style='gap:.5rem;'>" +
      "<span style='min-width:45%;'><i class='fas fa-building text-muted'></i> "+escaparTexto(this.text)+"</span>" +
      "<input type='number' min='0' max='365' class='form-control form-control-sm' style='width:5.5rem;' data-area='"+escaparTexto(id)+"' value='"+escaparTexto(plazo)+"'>" +
      "<small class='text-muted'>días hábiles (0 = sin plazo)</small></div>";
  });
  caja.innerHTML = html;
}

function Leer_Atenciones_Derivar(){
  var lista = [];
  $('#plazos_atencion_derivar input[data-area]').each(function(){
    lista.push({ area: parseInt(this.getAttribute('data-area'), 10), plazo: parseInt(this.value || "0", 10) || 0 });
  });
  return lista;
}

$('#tabla_tramite').on('click','.responder-atencion',function(){
  var tr = $(this).closest('tr');
  if(tr.hasClass('child')){ tr = tr.prev(); }
  var data = tbl_tramite.row(tr).data();
  if(!data){ return; }
  Swal.fire({
    title: "Responder atención",
    html: "<div style='text-align:left'>" +
      "<p class='mb-2'>Expediente <b>"+escaparTexto(data.doc_expediente || data.documento_id)+"</b><br><small class='text-muted'>"+escaparTexto(data.doc_asunto)+"</small></p>" +
      "<label style='font-size:small'>Respuesta de su área (*)</label>" +
      "<textarea id='txt_respuesta_atencion' class='form-control' rows='5' maxlength='4000' placeholder='Informe, opinión técnica o conclusión'></textarea>" +
      "<label class='mt-3' style='font-size:small'>Informe adjunto (opcional, PDF)</label>" +
      "<input type='file' id='txt_archivo_atencion' accept='.pdf' class='form-control'>" +
      // El informe lo produce la entidad: debe salir firmado. La firma va aquí,
      // antes de enviar, y no después desde el panel de archivos del expediente.
      "<fieldset id='firma_atencion' class='mt-3' style='border:1px solid #dee2e6;border-radius:.4rem;padding:.65rem;'>" +
        "<legend style='font-size:small;font-weight:600;width:auto;padding:0 .4rem;margin:0;'>" +
          "<i class='fas fa-file-signature'></i> Firmar el informe antes de enviarlo</legend>" +
        "<label style='font-size:small'>Certificado digital (.pfx / .p12)</label>" +
        "<input type='file' id='txt_cert_atencion' accept='.pfx,.p12' class='form-control form-control-sm'>" +
        "<label class='mt-2' style='font-size:small'>Contraseña del certificado</label>" +
        "<input type='password' id='txt_clave_atencion' class='form-control form-control-sm' autocomplete='off'>" +
        "<small class='text-muted d-block mt-2'>Si lo deja en blanco, el informe se envía sin firma digital " +
        "y así queda registrado en la bitácora. Su certificado y su contraseña no se guardan.</small>" +
      "</fieldset></div>",
    showCancelButton: true,
    confirmButtonText: "Enviar respuesta",
    confirmButtonColor: "#1E3A5F",
    cancelButtonText: "Cancelar",
    focusConfirm: false,
    preConfirm: function(){
      var texto = document.getElementById('txt_respuesta_atencion').value.trim();
      if(texto.length < 3){ Swal.showValidationMessage("Escriba la respuesta de su área"); return false; }
      var fd = new FormData();
      fd.append("id", data.documento_id);
      fd.append("respuesta", texto);
      var archivo = document.getElementById('txt_archivo_atencion').files[0];
      if(archivo){ fd.append("archivo", archivo); }

      var cert = document.getElementById('txt_cert_atencion').files[0];
      var clave = document.getElementById('txt_clave_atencion').value;
      if(cert && !archivo){
        Swal.showValidationMessage("Adjunte el informe en PDF que va a firmar, o quite el certificado");
        return false;
      }
      if(cert && !clave){
        Swal.showValidationMessage("Escriba la contraseña de su certificado");
        return false;
      }
      if(cert){
        fd.append("certificado", cert);
        fd.append("clave", clave);
      }

      return $.ajax({ url:"../controller/tramite_area/controlador_responder_atencion.php", type:"POST", data:fd, contentType:false, processData:false, dataType:"json" })
        .then(function(r){ return r; }, function(xhr){
          Swal.showValidationMessage((xhr.responseJSON && xhr.responseJSON.mensaje) || "No se pudo registrar la respuesta");
          return false;
        });
    }
  }).then(function(res){
    if(res.isConfirmed && res.value){
      var r = res.value;
      if(r.firmado){
        Swal.fire({
          icon: "success",
          title: "Respuesta enviada y firmada",
          html: "El informe salió firmado por <b>"+escaparTexto(r.firmante)+"</b>.<br>" +
                "Código de verificación: <b style='letter-spacing:.05em;'>"+escaparTexto(r.codigo)+"</b>" +
                (r.autofirmado
                  ? "<div class='firma-aviso mt-3 text-left'><i class='fas fa-exclamation-triangle'></i><div>" +
                    "Su certificado es <b>autofirmado</b>: la firma es íntegra, pero no fue emitida por una " +
                    "entidad de certificación acreditada ante INDECOPI.</div></div>"
                  : "")
        });
      } else if(r.sin_firma){
        Swal.fire({
          icon: "warning",
          title: "Respuesta enviada sin firma",
          html: "El informe de su área salió <b>sin firma digital</b> y así quedó registrado en la bitácora.<br>" +
                "<small class='text-muted'>Puede firmarlo desde los archivos del expediente, pero lo correcto " +
                "es firmarlo antes de enviarlo.</small>"
        });
      } else {
        Swal.fire("Respuesta registrada","El área responsable ya puede ver la respuesta de su área.","success");
      }
      tbl_tramite.ajax.reload(null,false);
    }
  });
});
