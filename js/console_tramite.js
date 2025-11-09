var tbl_tramite;
function listar_tramite(){
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
          "url":"../controller/tramite/controlador_listar_tramite.php",
          type:'POST'
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
                }else if(data=='SIN RESPUESTA'){
                  return '<span class="badge bg-danger">DOCUMENTO SIN RESPUESTA</span>';
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
               if(row["dias_pasados"] >= data){
                return "<span class='badge badge-danger' style='size:10px'>"+data+" días</span>";              
              }else {
                return "<span class='badge badge-success' style='size:10px'>"+data+" días</span>";              
              }
          
            }
          }, 
        {"defaultContent":"<button class='delete btn btn-danger btn-sm' title='Eliminar tramite'><i class='fa fa-trash'></i><b> Eliminar</b></button>&nbsp;<button style='font-size:13px;' type='button' title='Imprimir ticket' id='imprimir' class='imprimir btn btn-primary btn-sm'><i class='fa fa-print'></i><b> Imprimir Ticket</b></button>&nbsp;<button style='font-size:13px;' type='button' title='Imprimir hoja de seguimiento' id='imprimir' class='imprimir2 btn btn-success btn-sm'><i class='fa fa-file'></i><b> Imprimir Seguimiento</b></button>&nbsp;<button style='font-size:13px;' type='button' title='Imprimir hoja de ruta' id='imprimir' class='imprimir3 btn btn-warning btn-sm'><i class='fa fa-file'></i><b> Imprimir Hoja de Ruta</b></button>"},
     
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
$('#tabla_tramite').on('click','.imprimir',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();
  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
  window.open("MPDF/REPORTE/ticket_tramite.php?codigo="+data.documento_id
  +"#zoom=100%","Ticket","scrollbars=NO");
  
})
$('#tabla_tramite').on('click','.imprimir2',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();
  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
  window.open("MPDF/REPORTE/ficha_seguimiento_automatico.php?codigo="+data.documento_id
  +"#zoom=100%","Ticket","scrollbars=NO");
  
})
$('#tabla_tramite').on('click','.imprimir3',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();
  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
  window.open("MPDF/REPORTE/ficha_seguimiento.php?codigo="+data.documento_id
  +"#zoom=100%","Ticket","scrollbars=NO");
  
})
function listar_tramite_buscar(){
  let esta = document.getElementById('select_buscar_estado').value;
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
          "url":"../controller/tramite/controlador_listar_tramite_estado.php",
          type:'POST',
          data:{
            estados:esta
          }
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
          {"defaultContent":"<button class='delete btn btn-danger btn-sm' title='Eliminar tramite'><i class='fa fa-trash'></i><b> Eliminar</b></button>&nbsp;<button style='font-size:13px;' type='button' title='Imprimir ticket' id='imprimir' class='imprimir btn btn-primary btn-sm'><i class='fa fa-print'></i><b> Imprimir Ticket</b></button>&nbsp;<button style='font-size:13px;' type='button' title='Imprimir hoja de seguimiento' id='imprimir' class='imprimir2 btn btn-success btn-sm'><i class='fa fa-file'></i><b> Imprimir Seguimiento</b></button>&nbsp;<button style='font-size:13px;' type='button' title='Imprimir hoja de ruta' id='imprimir' class='imprimir3 btn btn-warning btn-sm'><i class='fa fa-file'></i><b> Imprimir Hoja de Ruta</b></button>"},
     
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
$('#tabla_tramite').on('click','.delete',function(){
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if(tbl_tramite.row(this).child.isShown()){
      var data = tbl_tramite.row(this).data();
  }
  Swal.fire({
    title: 'Desea eliminar el tramite con código '+data.documento_id+'?',
    text: "Una vez aceptado el tramite sera eliminado!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Eliminar'
  }).then((result) => {
    if (result.isConfirmed) {
      Eliminar_Tramite(data.documento_id);
    }
  })
})
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

$('#tabla_tramite').on('click', '.mas', function () {
  var data = tbl_tramite.row($(this).parents('tr')).data();

  if (tbl_tramite.row(this).child.isShown()) {
      data = tbl_tramite.row(this).data();
  }

  $("#modal_mas").modal('show');
  document.getElementById('txt_ndocumento').value = data.doc_nrodocumento;
  document.getElementById('txt_folio').value = data.doc_folio;
  document.getElementById('txt_asunto').value = data.doc_asunto;
  document.getElementById('txt_observacion').value = data.doc_observaciones;
  document.getElementById('txt_tiempo_respuesta').value = data.dias_respuesta;
  document.getElementById('txt_acciones').value = data.acciones;

  document.getElementById('lb_titulo_datos').innerHTML = "DATOS DEL EXPEDIENTE Nº: " + data.doc_nrodocumento;
  $("#select_area_p").select2().val(data.area_origen).trigger('change.select2');
  $("#select_area_d").select2().val(data.area_destino).trigger('change.select2');
  $("#select_tipo").select2().val(data.tipodocumento_id).trigger('change.select2');

  document.getElementById('txt_dni').value = data.doc_dniremitente;
  document.getElementById('txt_nom').value = data.doc_nombreremitente;
  document.getElementById('txt_apepat').value = data.doc_apepatremitente;
  document.getElementById('txt_apemat').value = data.doc_apematremitente;
  document.getElementById('txt_celular').value = data.doc_celularremitente;
  document.getElementById('txt_email').value = data.doc_emailremitente;
  document.getElementById('txt_dire').value = data.doc_direccionremitente;
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
    "url":"../controller/area/controlador_registro_area.php",
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
    "url":"../controller/area/controlador_modificar_area.php",
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
  function Cargar_Select_Tipo(){
    $.ajax({
      "url":"../controller/tramite/controlador_cargar_select_tipo.php",
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
      "url":"../controller/tramite/controlador_traerrequisito.php",
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
  function Cargar_Select_DNI(){
    $.ajax({
      "url":"../controller/tramite/controlador_cargar_DNI.php",
          type:'POST'
    }).done(function(resp){
        var data = JSON.parse(resp);
        var cadena="";
        if(data.length>0){
            for(var i=0; i < data.length; i++){
                cadena+="<option value='"+data[i][1]+"'> DNI: "+data[i][1]+" - Remitente: "+data[i][5]+" - Área: "+data[i][10]+"</option>";
            }
            $('#txt_dni').html(cadena);
            var id =$("#txt_dni").val();
  
        }
        else{
            cadena+="<option value=''>No se encontraron regitros</option>";
            $('#txt_dni').html(cadena);
        }
    })
  }
  function TraerrequisitoDNI(idrequisito){
      $.ajax({
              
        "url":"../controller/tramite/controlador_traerrequisitoDNI.php",
        type:'POST',
            data:{
              id:idrequisito
            }
          }).done(function(resp){
          var data = JSON.parse(resp);
          var cadena="";
          if(data.length>0){
            $("#txt_nom").val(data[0][2]);
            $("#txt_apepat").val(data[0][3]);
            $("#txt_apemat").val(data[0][4]);
            $("#txt_celular").val(data[0][6]);
            $("#txt_email").val(data[0][7]);
            $("#txt_dire").val(data[0][8]);
  
  
          }
          else{
              return Swal.fire("Mensaje de Error","No se pudo traer el requisito","error");
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
    let arp = document.getElementById('select_area_p').value;
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
    if(dni.length==0 || nom.length==0 ||apt.length==0 ||  apm.length==0 || cel.length==0 ||
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
      url:"../controller/tramite/controlador_registro_tramite.php",
      type:'POST',
      data:formData,
      contentType:false,
      processData:false,
      success:function(resp){
        if(resp.length>0){
          Swal.fire("Mensaje de Confirmación","Nueva Tramite Registrado código: "+resp,"success").then((value)=>{
            window.open("MPDF/REPORTE/ficha_seguimiento.php?codigo="+resp+"#zomm=100");
            $("#contenido_principal").load("tramite/view_tramite.php");
            document.getElementById('txt_dni').value="";
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
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
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
function Total_documentos_pendientes(){
  $.ajax({
      "url":"../controller/tramite/controlador_total_dopendientes.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if(data.length>0){
        $("#totaldocpendientes").html(data[0][0]);
      }
      else{
          return Swal.fire("Mensaje de Error","No se pudo traer los empleados","error");
      }
  })
}
function Total_documentos_aceptados(){
  $.ajax({
      "url":"../controller/tramite/controlador_total_doaceptados.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if(data.length>0){
        $("#totaldocpaceptados").html(data[0][0]);
      }
      else{
          return Swal.fire("Mensaje de Error","No se pudo traer los empleados","error");
      }
  })
}
function Total_documentos_finalizado(){
  $.ajax({
      "url":"../controller/tramite/controlador_total_dofinalizado.php",
      type:'POST'
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if(data.length>0){
        $("#totaldocfinalizado").html(data[0][0]);
      }
      else{
          return Swal.fire("Mensaje de Error","No se pudo traer los empleados","error");
      }
  })
}

////ELIMINAR TRAMITE ADMINISTRADOR
function Eliminar_Tramite(id){
  $.ajax({
    "url":"../controller/tramite/controlador_eliminar_tramite.php",
    type:'POST',
    data:{
      id:id
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se elimino el tramite con exito","success").then((value)=>{
          tbl_tramite.ajax.reload();
        });
    }else{
      return Swal.fire("Mensaje de Error","No se pudo completar el proceso","error");

    }
  })
}
