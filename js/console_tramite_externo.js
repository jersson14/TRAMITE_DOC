var tbl_tramite;
function listar_tramite(){
  tbl_tramite = $("#tabla_tramite").DataTable({
      "ordering":false,   
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 10,
      "destroy":true,
      "async": false ,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "processing": true,
      "ajax":{
          "url":"controller/tramite/controlador_listar_tramite.php",
          type:'POST'
      },
      dom: 'Bfrtip',       
      buttons:[ 
    {
      extend:    'excelHtml5',
      text:      '<i class="fas fa-file-excel"></i> ',
      titleAttr: 'Exportar a Excel',
      
      filename: function() {
        return  "LISTA DE DOCUMENTOS"
      },
        title: function() {
          return  "LISTA DE DOCUMENTOS" }
  
    },
    {
      extend:    'pdfHtml5',
      text:      '<i class="fas fa-file-pdf"></i> ',
      titleAttr: 'Exportar a PDF',
      filename: function() {
        return  "LISTA DE DOCUMENTOS"
      },
    title: function() {
      return  "LISTA DE DOCUMENTOS"
    }
  },
    {
      extend:    'print',
      text:      '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      
    title: function() {
      return  "LISTA DE DOCUMENTOS"
  
    }
    }],
      "columns":[
        {"data":"documento_id"},
        {"data":"doc_nrodocumento"},
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
$('#tabla_tramite').on('click','.editar',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
  $("#modal_editar").modal('show');
  document.getElementById('txt_area_editar').value=data.area_nombre;
  document.getElementById('txt_idarea').value=data.area_cod;
  document.getElementById('txt_estatus').value=data.area_estado;
})
$('#tabla_tramite').on('click','.seguimiento',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
$("#modal_seguimiento").modal('show');
  document.getElementById('lb_titulo').innerHTML="SEGUIMIENTO DE TRAMITE Nº: "+data.documento_id;
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

function Registrar_Area(){
  let area = document.getElementById('txt_area').value;
  if(area.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"controller/area/controlador_registro_area.php",
    type:'POST',
    data:{
      a:area
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Nueva Área registrada","success").then((value)=>{
          tbl_tramite.ajax.reload();
          document.getElementById('txt_area').value="";
        $("#modal_registro").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El área ingresada ya se encuentra en la base de datos","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo el registro","error");

    }
  })
}
function Modificar_Area(){
  let id = document.getElementById('txt_idarea').value;
  let area = document.getElementById('txt_area_editar').value;
  let esta = document.getElementById('txt_estatus').value;

  if(area.length==0 || id.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"controller/area/controlador_modificar_area.php",
    type:'POST',
    data:{
      id:id,
      are:area,
      esta:esta
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Datos actualizados","success").then((value)=>{
          tbl_tramite.ajax.reload();
        $("#modal_editar").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El área ingresada ya se encuentra en la base de datos","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

  function Cargar_Select_Tipo(){
    $.ajax({
      "url":"controller/tramite/controlador_cargar_select_tipo.php",
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

function Registrar_Tramite(){
  //DATOS DEL REMITENTE
    let dni = document.getElementById('txt_dni').value;
    let nom = document.getElementById('txt_nom').value;
    let apt = document.getElementById('txt_apepat').value;
    let apm = document.getElementById('txt_apemat').value;
    let cel = document.getElementById('txt_celular').value;
    let ema = document.getElementById('txt_email').value;
    let dir = document.getElementById('txt_dire').value;

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

    let tip = document.getElementById('select_tipo').value;
    let ndo = document.getElementById('txt_ndocumento').value;
    let asu = document.getElementById('txt_asunto').value;
    let arc = document.getElementById('txt_archivo').value;
    let fol = document.getElementById('txt_folio').value;
    if(arc.length==0){
      return Swal.fire("Mensaje de Advertencia","Seleccione algún tipo de documento","warning")
    }

    let extension = arc.split('.').pop();//DOCUMENTO.PPT
    let nombrearchivo="";
    let f = new Date();
    
    if(arc.length>0){
      nombrearchivo="ARCH"+f.getDate()+"-"+(f.getMonth()+1)+"-"+f.getFullYear()+"-"+f.getHours()+"-"+f.getMilliseconds()+"."+extension;
    }
    if(dni.length==0 || nom.length==0 ||apt.length==0 ||  apm.length==0 || cel.length==0 ||
      dir.length==0 ){
        return Swal.fire("Mensaje de Advertencia","Llene todo los campos del remitente","warning")
      }
    if(tip.length==0 ||  ndo.length==0 || asu.length==0 || fol.length==0){
        return Swal.fire("Mensaje de Advertencia","Llene todo los campos del documento","warning")
    }
    // El correo es obligatorio: ahí se envían el código y las respuestas
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ema)){
        return Swal.fire("Mensaje de Advertencia","Ingrese un correo electrónico válido: ahí recibirá su código y la respuesta.","warning")
    }
    // PHP descarta un envío de más de 40 MB; se avisa antes de subir
    var pesoTotal = $("#txt_archivo")[0].files[0] ? $("#txt_archivo")[0].files[0].size : 0;
    var listaPeso = $("#txt_anexos").length ? $("#txt_anexos")[0].files : [];
    for(var iPeso=0; iPeso<listaPeso.length; iPeso++){ pesoTotal += listaPeso[iPeso].size; }
    if(pesoTotal > 35 * 1048576){
        return Swal.fire("Mensaje de Advertencia","El documento y los anexos no deben superar los 35 MB en total.","warning")
    }

    // Declaración de veracidad y aviso de privacidad (Ley N° 29733): el servidor
    // también rechaza el registro si no se aceptó el aviso
    if(!$("#checkboxSuccess1").is(":checked")){
        return Swal.fire("Mensaje de Advertencia","Marque la declaración de que la información es correcta y verídica.","warning")
    }
    if(!$("#chk_privacidad").is(":checked")){
        return Swal.fire("Mensaje de Advertencia","Lea y acepte el aviso de privacidad para registrar su trámite.","warning")
    }

    let formData = new FormData();
    let achivoobj = $("#txt_archivo")[0].files[0];//El objeto del archivo adjuntado

    //////DATOS DEL REMITENTE/////
    formData.append("dni",dni);
    formData.append("nom",nom);
    formData.append("apt",apt);
    formData.append("apm",apm);
    formData.append("cel",cel);
    formData.append("ema",ema);
    formData.append("dir",dir);
    formData.append("vpresentacion",vpresentacion);
    formData.append("ruc",ruc);
    formData.append("raz",raz);
    formData.append("privacidad", "1");
    ///////DATOS DEL DOCUMENTO//////

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

    $.ajax({
      url:"controller/tramite/controlador_registro_tramite_externo.php",
      error:function(xhr){
        $("#btn_registro").prop("disabled", !($("#checkboxSuccess1").is(":checked") && $("#chk_privacidad").is(":checked")));
        let mensaje = (xhr.responseJSON && xhr.responseJSON.mensaje) || "No se pudo registrar el trámite. Intente nuevamente.";
        Swal.fire("No se pudo registrar", mensaje, "error");
      },
      type:'POST',
      data:formData,
      contentType:false,
      processData:false,
      dataType:'json',
      beforeSend:function(){
        $("#btn_registro").prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');
      },
      complete:function(){
        $("#btn_registro").html('<i class="fas fa-paper-plane"></i> REGISTRAR TRÁMITE');
      },
      success:function(r){
        if(!r || r.status !== "ok"){
          $("#btn_registro").prop("disabled", false);
          return Swal.fire("No se pudo registrar","No se pudo registrar el trámite. Intente nuevamente.","error");
        }
        Mostrar_Cargo_Recepcion(r);
      }
    });
    return false;
}

/**
 * Tras registrar: reemplaza el formulario por la confirmación con el cargo de
 * recepción. Antes se abría el ticket en una ventana emergente, que el navegador
 * suele bloquear, y el ciudadano se quedaba solo con un aviso.
 */
function Mostrar_Cargo_Recepcion(r){
  var expediente = r.expediente || r.codigo;
  $("#cr_expediente").text(expediente);
  $("#cr_codigo").text(r.codigo);
  $("#cr_fecha").text(r.fecha);
  // Fuera del horario de atención: se avisa desde cuándo cuenta como presentado
  var horario = document.getElementById("cr_horario");
  if (r.presentado) {
    horario.innerHTML = '<i class="fas fa-clock"></i> Su documento llegó fuera del horario de atención (' + $("<i>").text(r.horario).html() +
      '). Se considera presentado el <strong>' + $("<i>").text(r.presentado).html() + '</strong> y los plazos se cuentan desde esa fecha.';
    horario.hidden = false;
  } else {
    horario.hidden = true;
  }
  $("#cr_detalle").text(
    "Recibimos " + r.archivos + (r.archivos === 1 ? " archivo" : " archivos") + ". " +
    (r.correo ? "Enviaremos las notificaciones a " + r.correo + "." : "")
  );
  $("#cr_cargo").attr({ href: r.cargo + "&descargar=1", download: "Cargo_" + expediente + ".pdf" });
  $("#cr_ver").attr("href", r.cargo);
  $("#cr_seguimiento").attr("href", r.seguimiento);

  $("#formulario_registro, #bienvenida_registro").attr("hidden", true).hide();
  var panel = document.getElementById("confirmacion_registro");
  panel.hidden = false;
  window.scrollTo({ top: 0, behavior: "smooth" });
  panel.focus({ preventScroll: true });
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
          "url":"controller/tramite/controlador_listar_tabla_seguimiento.php",
          type:'POST',
          data:{
            id:id
          }
      },
      "columns":[
        {"data":"area_nombre"},
        {"data":"fecha_formateada"},
        {"data":"mov_descripcion"},
        {"data":"mov_archivo", render: Render_Archivos_Movimiento},       
    ],

    "language":idioma_espanol,
    select: true
});
}

function Cargar_Select_Tipo(){
  $.ajax({
    "url":"controller/tramite/controlador_cargar_select_tipo.php",
        type:'POST'
  }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if(data.length>0){
          for(var i=0; i < data.length; i++){
              cadena+="<option value='"+data[i][0]+"'>"+data[i][1]+"</option>";
          }
          $('#select_tipo').html(cadena);
          var id =$("#select_tipo").val();

          Traerrequisitotipodoc(id);
          
      }
      else{
          cadena+="<option value=''>No se encontraron regitros</option>";
          $('#select_tipo').html(cadena);
      }
  })
}
function Traerrequisitotipodoc(idrequisito){
  $.ajax({
    "url":"controller/tramite/controlador_traerrequisito.php",
    type:'POST',
        data:{
          id:idrequisito
        }
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if(data.length>0){
        $("#txt_requisitos").val(data[0][1]);
      }
      else{
          return Swal.fire("Mensaje de Error","No se pudo traer el requisito","error");
      }
  })
}


