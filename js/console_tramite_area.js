var tbl_tramite;
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
      "ajax":{
          "url":"../controller/tramite_area/controlador_listar_tramite.php",
          type:'POST',
          data:{
            idusuario:idusuario
          }
      },
      
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
        {"data":"dias_pasados",
        render: function(data,type,row){
                if(data=='1'){
                    return '<i class="fa fa-circle text-dark fa-0x"></i>';
                }else if(data=='2'){
                    return '<i class="fa fa-circle text-success fa-0x"></i>';
                }else if(data>='3' && data<='4'){
                    return '<i class="fa fa-circle text-warning fa-0x"></i>';
                }else if(data>='5'){
                  return '<i class="fa fa-circle text-danger fa-0x"></i>';
                }else if(data=='0'){
                  return '<i class="fa fa-circle text-danger fa-0x" hidden></i>';
                }
            }
             
        },
        {"data":"dias_respuesta",
        render: function (data, type, row ) {
           if(row["dias_pasados"] >= row["dias_respuesta"]){
            return "<span class='badge badge-danger' style='size:10px'>"+data+" días</span>";              
          }else {
            return "<span class='badge badge-success' style='size:10px'>"+data+" días</span>";              
          }
        }
      },
        {"data":"doc_estatus",
        render: function(data,type,row){
                if(data=='PENDIENTE'){
                    return "</button>&nbsp;<button  title='Aceptar Documento' class='aceptar btn btn-success  btn-sm'><i class='fa fa-check'></i> Aceptar</button>&nbsp;<button  title='Rechazar Documento' class='rechazar btn btn-danger  btn-sm'><i class='fa fa-search'></i> Rechazar</button>&nbsp;<button hidden class='derivar btn btn-primary  btn-sm' title='Derivar Documento'><i class='fa fa-share-square'></i> Derivar</button>";
                }else if (data=='ACEPTADO'){
                  return "</button>&nbsp;<button hidden title='Aceptar Documento' class='aceptar btn btn-success  btn-sm'><i class='fa fa-check'></i> Aceptar</button>&nbsp;<button hidden title='Rechazar Documento' class='rechazar btn btn-danger  btn-sm'><i class='fa fa-search'></i> Rechazar</button>&nbsp;<button class='derivar btn btn-primary  btn-sm' title='Derivar Documento'><i class='fa fa-share-square'></i> Derivar</button>";
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
document.getElementById('txt_observacion').value=data.doc_observaciones;
document.getElementById('txt_tiempo_respuesta').value=data.dias_respuesta;
document.getElementById('txt_acciones').value=data.acciones;
document.getElementById('lb_titulo_datos').innerHTML="DATOS DEL EXPEDIENTE Nº: "+data.doc_nrodocumento;
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

  let nombrearchivo="";

  if(dest.length==0){
    Swal.fire("Mensaje de Advertencia","Seleccionar el área destino","warning");
  }
  if(acc.length==0){
    Swal.fire("Mensaje de Advertencia","Seleccionar al menos una acción a realizar","warning");
  }
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
  formData.append("tipo",tipo);
  formData.append("acc",acc);

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
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Aceptar'
    }).then((result) => {
      if (result.isConfirmed) {
        Modificar_Estatus_Documento(parseInt(data.doc_nrodocumento),'ACEPTADO');
      }
    })

})

function Modificar_Estatus_Documento(id,estatus){
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
        Swal.fire("Mensaje de Confirmación","Se "+esta+ " con exito El Documento Nº "+id,"success").then((value)=>{
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
    formData.append("idusu",idusu);
    formData.append("acc",acc);
    formData.append("obs",obs);
    formData.append("tre",tre);


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
            $("#contenido_principal").load("tramite_area/view_tramite_enviados.php");
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
        {"data":"area_nombre"},
        {"data":"fecha_formateada"},
        {"data":"mov_descripcion"},
        {"data":"mov_estatus",
        render: function(data,type,row){
                if(data=='PENDIENTE'){
                    return '<span class="badge bg-warning">PENDIENTE</span>';
                }else if(data=='RECHAZADO'){
                    return '<span class="badge bg-danger">RECHAZADO</span>';
                }else if(data=='ACEPTADO'){
                    return '<span class="badge bg-success">ACEPTADO</span>';
                }else if(data=='FINALIZADO'){
                  return '<span class="badge bg-primary">FINALIZADO</span>';
                }else if(data=='DERIVADO'){
                  return '<span class="badge bg-dark">DERIVADO</span>';
                }
            
            }
             
        },
        {"data":"mov_acciones"},

        {"data":"mov_archivo",
        render: function(data,type,row){
          if(data==''){
            return "<button class='btn btn-danger btn-sm' disabled title='Ver archivo'><i class='fa fa-file-pdf'></i></button>";
          }else{
            return "<a class='btn btn-primary btn-sm' href='../"+data+"' target='_blank' title='Ver archivo'><i class='fas fa-file-download'></i></a>";
          }
              }   
        },     
    ],

    "language":idioma_espanol,
    select: true
});

}
