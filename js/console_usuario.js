function Iniciar_Sesion(){
    recuerdame();
    let usu = document.getElementById("txt_usuario").value.trim();
    let con = document.getElementById("txt_contra").value;
    if(usu.length==0 || con.length==0){
       return Swal.fire({icon:'warning', title:'Campos incompletos', text:'Ingrese su usuario y contraseña', heightAuto:false});
    }
    let boton = document.getElementById("entrar");
    boton.classList.add("loading");
    $.ajax({
        url:'controller/usuario/controlador_iniciar_sesion.php',
        type:'POST',
        dataType:'json',
        data:{ u:usu, c:con }
    }).done(function(resp){
        if(resp.status==="ok"){
            location.reload();
            return;
        }
        boton.classList.remove("loading");
        let mensajes = {
            vacio: ['warning','Campos incompletos','Ingrese su usuario y contraseña'],
            inactivo: ['warning','Usuario inactivo','El usuario '+usu+' se encuentra inactivo. Comuníquese con el administrador.'],
            bloqueado: ['error','Acceso bloqueado temporalmente','Demasiados intentos fallidos. Intente nuevamente en '+resp.minutos+' minuto(s).'],
            error: ['error','Credenciales incorrectas','Usuario o contraseña incorrectos']
        };
        let m = mensajes[resp.status] || mensajes.error;
        Swal.fire({icon:m[0], title:m[1], text:m[2], heightAuto:false});
    }).fail(function(){
        boton.classList.remove("loading");
        Swal.fire({icon:'error', title:'Sin conexión', text:'No se pudo contactar con el servidor. Intente nuevamente.', heightAuto:false});
    });
}

function recuerdame(){
    // Solo se recuerda el nombre de usuario, nunca la contraseña
    try {
        if(rmcheck.checked && usuarioInput.value !=""){
            localStorage.usuario  = usuarioInput.value;
            localStorage.checkbox = rmcheck.value;
        }else{
            localStorage.removeItem('usuario');
            localStorage.removeItem('checkbox');
        }
        localStorage.removeItem('pass');
    } catch(e) {}
}

var tbl_usuario;
function listar_usuario(){
  tbl_usuario = $("#tabla_usuario").DataTable({
    pagingType: 'full_numbers',
    scrollCollapse: true,
    responsive: true,
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
          "url":"../controller/usuario/controlador_listar_usuario.php",
          type:'POST'
      },
      dom: 'Bfrtip',       
    buttons:[ 
  {
    extend:    'excelHtml5',
    text:      '<i class="fas fa-file-excel"></i> ',
    titleAttr: 'Exportar a Excel',
    
    filename: function() {
      return  "LISTA DE USUARIOS"
    },
      title: function() {
        return  "LISTA DE USUARIOS" }

  },
  {
    extend:    'pdfHtml5',
    text:      '<i class="fas fa-file-pdf"></i> ',
    titleAttr: 'Exportar a PDF',
    filename: function() {
      return  "LISTA DE USUARIOS"
    },
  title: function() {
    return  "LISTA DE USUARIOS"
  }
},
  {
    extend:    'print',
    text:      '<i class="fa fa-print"></i> ',
    titleAttr: 'Imprimir',
    
  title: function() {
    return  "LISTA DE USUARIOS"

  }
  }],
      "columns":[
        {"defaultContent":""},
        {"data":"usu_usuario"},
        {"data":"area_nombre"},
        {"data":"usu_rol"},
        {"data":"nempleado"},
        {"data":"usu_estatus",
            render: function(data,type,row){
                    if(data=='ACTIVO'){
                    return '<span class="badge bg-success">ACTIVO</span>';
                    }else{
                    return '<span class="badge bg-danger">INACTIVO</span>';
                    }
            }   
        },
        {"data":"usu_estatus",
            render: function(data,type,row){
                    if(data=='ACTIVO'){
                    return "<button class='editar btn btn-primary btn-sm' title='Editar datos de usuario'><i class='fa fa-edit'></i></button>&nbsp;<button class='contra btn btn-warning btn-sm' title='Cambiar contraseña de usuario'><i class='fas fa-key'></i></button>&nbsp;<button class='btn btn-success btn-sm' disabled title='Activar usuario'><i class='fa fa-check-circle'></i></button>&nbsp;<button class='desactivar btn btn-danger btn-sm' title='Desactivar usuario'><i class='fa fa-times-circle'></i></button>";
                    }else{
                    return "<button class='editar btn btn-primary btn-sm' title='Editar datos de usuario'><i class='fa fa-edit'></i></button>&nbsp;<button class='contra btn btn-warning btn-sm' title='Cambiar contraseña de usuario'><i class='fas fa-key'></i></button>&nbsp;<button class='activar btn btn-success btn-sm' title='Activar usuario'><i class='fa fa-check-circle'></i></button>&nbsp;<button class='btn btn-danger btn-sm' disabled title='Desactivar usuario'><i class='fa fa-times-circle'></i></button>";
                    }
            }   
        }
    ],

    "language":idioma_espanol,
    select: true
});
tbl_usuario.on('draw.td',function(){
  var PageInfo = $("#tabla_usuario").DataTable().page.info();
  tbl_usuario.column(0, {page: 'current'}).nodes().each(function(cell, i){
    cell.innerHTML = i + 1 + PageInfo.start;
  });
});
}
function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}
$('#tabla_usuario').on('click','.editar',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
  $("#modal_editar").modal('show');
  document.getElementById('txt_idusuario').value=data.usu_id;
  document.getElementById('txt_usu_editar').value=data.usu_usuario;
  $("#select_empleado_editar").select2().val(data.empleado_id).trigger('change.select2');
  $("#select_area_editar").select2().val(data.area_id).trigger('change.select2');
  $("#select_rol_editar").select2().val(data.usu_rol).trigger('change.select2');

})


$('#tabla_usuario').on('click','.contra',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
  $("#modal_contra").modal('show');
  document.getElementById('txt_idusuario_contra').value=data.usu_id;

})
$('#tabla_usuario').on('click','.desactivar',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
    Swal.fire({
      title: 'Desea desactivar al usuario '+data.usu_usuario+'?',
      text: "Una vez desactivado el usuario no tendra acceso al sistema",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#1E3A5F',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Desactivar'
    }).then((result) => {
      if (result.isConfirmed) {
        Modificar_Estatus_Usuario(parseInt(data.usu_id),'INACTIVO',data.usu_usuario);
      }
    })

})


$('#tabla_usuario').on('click','.activar',function(){
  var data = tbl_usuario.row($(this).parents('tr')).data();

  if(tbl_usuario.row(this).child.isShown()){
      var data = tbl_usuario.row(this).data();
  }
    Swal.fire({
      title: 'Desea activar al usuario '+data.usu_usuario+'?',
      text: "Una vez activado el usuario tendra acceso al sistema",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#1E3A5F',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Desactivar'
    }).then((result) => {
      if (result.isConfirmed) {
        Modificar_Estatus_Usuario(parseInt(data.usu_id),'ACTIVO',data.usu_usuario);
      }
    })

})


function Modificar_Estatus_Usuario(id,estatus,user){
  let esta=estatus;
  if(esta==="INACTIVO"){
    esta="Desactivo";
  }
  $.ajax({
    "url":"../controller/usuario/controlador_modificar_usuario_estatus.php",
    type:'POST',
    data:{
      id:id,
      estatus:estatus
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Se "+esta+" con exito El Usuario "+user,"success").then((value)=>{
          tbl_usuario.ajax.reload();
        });
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}
function Registrar_Usuario(){
  let usu = document.getElementById('txt_usu').value;
  let con = document.getElementById('txt_con').value;
  let ide = document.getElementById('select_empleado').value;
  let ida = document.getElementById('select_area').value;
  let rol = document.getElementById('select_rol').value;

  if(usu.length==0 || con.length==0 || ide.length==0 || ida.length==0 || rol.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/usuario/controlador_registro_usuario.php",
    type:'POST',
    data:{
      usu:usu,
      con:con,
      ide:ide,
      ida:ida,
      rol:rol
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Nueva Usuario Registrado","success").then((value)=>{
          tbl_usuario.ajax.reload();
          document.getElementById('txt_usu').value="";
          document.getElementById('txt_con').value="";
          document.getElementById('select_empleado').value="";
          document.getElementById('select_area').value="";
          document.getElementById('select_rol').value="";
        $("#modal_registro").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El Usuario ingresado ya se encuentra en la base de datos","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo el registro","error");

    }
  })
}
function Modificar_Usuario(){
  let id = document.getElementById('txt_idusuario').value;
  let ide = document.getElementById('select_empleado_editar').value;
  let ida = document.getElementById('select_area_editar').value;
  let rol = document.getElementById('select_rol_editar').value;

  if(id.length==0 || ide.length==0 || ida.length==0 || rol.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/usuario/controlador_modificar_usuario.php",
    type:'POST',
    data:{
      id:id,
      ide:ide,
      ida:ida,
      rol:rol
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Datos del Usuario Actualizado","success").then((value)=>{
          tbl_usuario.ajax.reload();
        $("#modal_editar").modal('hide');
        });
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}


function Modificar_Contra(){
  let id = document.getElementById('txt_idusuario_contra').value;
  let con = document.getElementById('txt_contra_nueva').value;

  if(id.length==0 || con.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/usuario/controlador_modificar_usuario_contra.php",
    type:'POST',
    data:{
      id:id,
      con:con
    }
  }).done(function(resp){
    if(resp>0){
        Swal.fire("Mensaje de Confirmación","Contraseña del Usuario Actualizada","success").then((value)=>{
          tbl_usuario.ajax.reload();
        $("#modal_contra").modal('hide');
        });
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

function Cargar_Select_Empleado(){
  $.ajax({
    "url":"../controller/usuario/controlador_cargar_select_empleado.php",
    type:'POST',
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i][0]+"'>"+data[i][4]+"</option>";    
      }
        document.getElementById('select_empleado').innerHTML=cadena;
        document.getElementById('select_empleado_editar').innerHTML=cadena;

    }else{
      cadena+="<option value=''>No hay empleado en la base de datos</option>";
      document.getElementById('select_empleado').innerHTML=cadena;
      document.getElementById('select_empleado_editar').innerHTML=cadena;

    }
  })
}

function Cargar_Select_Area_Solo(){
  $.ajax({
    "url":"../controller/usuario/controlador_cargar_select_area_solo.php",
    type:'POST',
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i][0]+"'>"+data[i][1]+"</option>";    
      }
        document.getElementById('select_area').innerHTML=cadena;
        document.getElementById('select_area_editar').innerHTML=cadena;
    }else{
      cadena+="<option value=''>No hay empleado en la base de datos</option>";
      document.getElementById('select_area').innerHTML=cadena;
      document.getElementById('select_area_editar').innerHTML=cadena;
    }
  })
}

//SEGUIMIENTO TRAMITE //
function Traer_Datos_Seguimiento(){
  let numero= document.getElementById('txt_numero').value;
  let dni= document.getElementById('txt_dni').value;
  if(numero.length==0 || dni.length==0){
   return Swal.fire("Mensaje de Advertencia","Llene el N° de Documento y DNI para buscar el documento","warning");

  }
  $.ajax({
    "url":"../controller/usuario/controlador_traer_seguimiento.php",
    type:'POST',
    data:{
      numero:numero,
      dni:dni
    }
  }).done(function(resp){
    let data=JSON.parse(resp);
    var cadena="";
    if(data.length>0){
      document.getElementById("div_buscador").style.display = "block";
      document.getElementById('lbl_titulo').innerHTML="<b>Seguimiento del Tramite: "+data[0][0]+" - "+data[0][2]+"</b>";
      cadena +='<div class="timeline">'+
      '<div class="time-label">'+
        '<span class="bg-red">'+data[0][4]+'</span>'+
      '</div>';
      //AJAX PARA EL DETALLE DEL SEGUIMIENTO//
      $.ajax({
        "url":"../controller/usuario/controlador_traer_seguimiento_detalle.php",
        type:'POST',
        data:{
          codigo:data[0][0],
          dni:dni
        }
      }).done(function(resp){
        let datadetalle=JSON.parse(resp);
        if(datadetalle.length>0){
          for (let i = 0; i < datadetalle.length; i++) {
            if(datadetalle[i][7]=="DERIVADO")
            {
            cadena+='<div>'+
            '<i class="fas fa-envelope bg-blue"></i>'+
            '<div class="timeline-item">'+
              '<span class="time"><i class="fas fa-clock"></i>'+datadetalle[i][4]+
              '</span>'+
              '<h3 class="timeline-header" style="color:blue"><a href="#" style="color:BLUE">El documento fue DERIVADO al área de: '+datadetalle[i][3]+'</a> - <b>ESTADO: '+datadetalle[i][7]+'</b></h3>'+
              '<div class="timeline-body">'+
              datadetalle[i][6]+
              '</div>'+
            '</div>'+
          '</div>';
            }else if(datadetalle[i][7]=="RECHAZADO")
            {
            cadena+='<div>'+
            '<i class="fas fa-envelope bg-red"></i>'+
            '<div class="timeline-item">'+
              '<span class="time"><i class="fas fa-clock"></i>'+datadetalle[i][4]+
              '</span>'+
              '<h3 class="timeline-header" style="color:red"><a href="#" style="color:red">El documento fue RECHAZADO en el área de: '+datadetalle[i][3]+'</a> - <b>ESTADO: '+datadetalle[i][7]+'</b></h3>'+
              '<div class="timeline-body">'+
              datadetalle[i][6]+
              '</div>'+
            '</div>'+
          '</div>';
            }else if(datadetalle[i][7]=="FINALIZADO")
            {
            cadena+='<div>'+
            '<i class="fas fa-envelope bg-success"></i>'+
            '<div class="timeline-item">'+
              '<span class="time"><i class="fas fa-clock"></i>'+datadetalle[i][4]+
              '</span>'+
              '<h3 class="timeline-header" style="color:green"><a href="#" style="color:green">El documento fue FINALIZADO en el área de: '+datadetalle[i][3]+'</a> - <b>ESTADO: '+datadetalle[i][7]+'</b></h3>'+
              '<div class="timeline-body">'+
              datadetalle[i][6]+
              '</div>'+
            '</div>'+
          '</div>';
            }else{
              cadena+='<div>'+
            '<i class="fas fa-envelope bg-warning"></i>'+
            '<div class="timeline-item">'+
              '<span class="time"><i class="fas fa-clock"></i>'+datadetalle[i][4]+
              '</span>'+
              '<h3 class="timeline-header" style="color:orange"><a href="#" style="color:orange">El documento se ENCUENTRA en el área de: '+datadetalle[i][3]+'</a> - <b>ESTADO: '+datadetalle[i][7]+'</b></h3>'+
              '<div class="timeline-body">'+
              datadetalle[i][6]+
              '</div>'+
            '</div>'+
          '</div>';
            }   
          }
          cadena+='</div>';
          document.getElementById("div_seguimiento").innerHTML=cadena;
          // El recorrido también se dibuja como diagrama (js/flujograma.js)
          if (typeof Flujograma_Cargar === "function") { Flujograma_Cargar(data[0][0], "#div_flujo"); Flujo_Vista("diagrama"); }

        }
      })
      ////TERMINA EL AJAX//////////
    }else{
      document.getElementById("div_buscador").style.display = "none";
      return Swal.fire("Mensaje de Advertencia","No se encontraron datos del Documento Buscado","warning");

    }
  })
}

function Traer_Datos_Seguimiento2(){
  let numero= document.getElementById('txt_numero').value;
  let dni= document.getElementById('txt_dni').value;
  if(numero.length==0 || dni.length==0){
   return Swal.fire("Mensaje de Advertencia","Llene el N° de Documento y DNI para buscar el documento","warning");

  }
  $.ajax({
    "url":"../controller/usuario/controlador_traer_seguimiento.php",
    type:'POST',
    data:{
      numero:numero,
      dni:dni
    }
  }).done(function(resp){
    let data=JSON.parse(resp);
    var cadena="";
    if(data.length>0){
      document.getElementById("div_buscador").style.display = "block";
      document.getElementById('lbl_titulo').innerHTML="<i class='fas fa-route'></i> Expediente "+(data[0].doc_expediente || data[0][0])+" · Código "+data[0][0]+" · Remitente: "+data[0][2];
      
      // Información inicial del documento
      cadena += '<div class="alert alert-info" style="border-left: 4px solid #17a2b8; margin-bottom: 2rem;">'+
                '<h5 style="margin-bottom: 1rem;"><i class="far fa-calendar-alt"></i> <strong>Fecha de Registro:</strong> '+data[0][4]+'</h5>'+
                '<p style="margin: 0;"><i class="fas fa-file-alt"></i> <strong>Documento:</strong> '+data[0][0]+' | <i class="fas fa-user"></i> <strong>Remitente:</strong> '+data[0][2]+'</p>'+
                '</div>';
      
      //AJAX PARA EL DETALLE DEL SEGUIMIENTO//
      $.ajax({
        "url":"../controller/usuario/controlador_traer_seguimiento_detalle.php",
        type:'POST',
        data:{
          codigo:data[0][0],
          dni:dni
        }
      }).done(function(resp){
        let datadetalle=JSON.parse(resp);
        if(datadetalle.length>0){
          /*
           * El recorrido abre por de dónde viene el trámite y recién después van las
           * derivaciones. Puede venir de un área de la entidad, de otra entidad o de
           * un ciudadano; el servidor ya lo resolvió (mismo criterio que el diagrama).
           */
          let org = datadetalle[0] || {};
          if(org.origen_rotulo){
            let icoOrg = org.origen_tipo === 'INTERNO' ? 'fas fa-building'
                       : (org.origen_tipo === 'ENTIDAD' ? 'fas fa-city' : 'fas fa-user-tie');
            let colorOrg = org.origen_tipo === 'INTERNO' ? '#1E3A5F' : '#6B7A90';
            cadena += '<div class="card mb-3" style="border:none;box-shadow:0 4px 15px rgba(0,0,0,0.08);border-radius:15px;overflow:hidden;">'+
                        '<div class="card-header" style="background:'+colorOrg+';color:white;padding:0.9rem 1.25rem;">'+
                          '<h6 class="mb-0" style="font-weight:700;letter-spacing:.03em;">'+
                            '<i class="'+icoOrg+'"></i> '+String(org.origen_rotulo).toUpperCase()+
                          '</h6>'+
                        '</div>'+
                        '<div class="card-body" style="padding:1.25rem 1.5rem;background:#f8f9fa;">'+
                          '<div style="font-weight:700;color:#1f2937;font-size:1.15rem;">'+org.origen_titulo+'</div>'+
                          (org.origen_persona
                            ? '<div style="font-size:0.9rem;color:#6b7280;margin-top:0.25rem;"><i class="far fa-user"></i> '+org.origen_persona+'</div>'
                            : '')+
                          (org.origen_ruc
                            ? '<div style="font-size:0.9rem;color:#6b7280;"><i class="far fa-building"></i> RUC '+org.origen_ruc+'</div>'
                            : '')+
                          (org.origen_enlace
                            ? '<div style="font-size:0.85rem;color:#9aa3ad;margin-top:0.5rem;"><i class="fas fa-long-arrow-alt-down"></i> '+org.origen_enlace+'</div>'
                            : '')+
                        '</div>'+
                      '</div>';
          }
          for (let i = 0; i < datadetalle.length; i++) {
            let iconClass = "fas fa-clock";
            let cardBg = "#B45309";
            let statusBadge = "badge-warning";
            let statusText = "PENDIENTE";
            let statusIcon = "fas fa-clock";
            
            // Detectar si es una copia
            let isCopy = datadetalle[i][7] && datadetalle[i][7].toUpperCase().includes('COPIA');
            
            // Obtener origen y destino - ÍNDICES CORREGIDOS
            let areaOrigen = datadetalle[i][3] || 'EXTERNO';  // area_origen_nombre
            let areaDestino = datadetalle[i][4] || 'N/A';     // area_destino_nombre

            // Un primer envío de un área a sí misma es el INGRESO del documento, no
            // una derivación: se señala con una etiqueta y no se toca su estado.
            let esRecepcion = !!datadetalle[i].es_recepcion;
            let rotuloOrigen = esRecepcion ? 'INGRESA POR' : 'ORIGEN';
            let iconoOrigen = esRecepcion ? 'fas fa-file-import' : 'fas fa-map-marker-alt';
            
            if(datadetalle[i][8]=="DERIVADO"){
              cardBg = "#1E3A5F";
              statusBadge = "badge-primary";
              statusText = "DERIVADO";
              statusIcon = "fas fa-arrow-right";
            } else if(datadetalle[i][8]=="RECHAZADO"){
              cardBg = "#B45309";
              statusBadge = "badge-danger";
              statusText = "RECHAZADO";
              statusIcon = "fas fa-times-circle";
            } else if(datadetalle[i][8]=="FINALIZADO"){
              cardBg = "#15803D";
              statusBadge = "badge-success";
              statusText = "FINALIZADO";
              statusIcon = "fas fa-check-circle";
            } else if(datadetalle[i][8]=="ACEPTADO"){
              cardBg = "#15803D";
              statusBadge = "badge-success";
              statusText = "ACEPTADO";
              statusIcon = "fas fa-check";
            }
            
            // Crear tarjeta de movimiento
            cadena += '<div class="card mb-3" style="border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 15px; overflow: hidden;">'+
                      '<div class="card-header" style="background: '+cardBg+'; color: white; padding: 1.25rem;">'+
                        '<div class="d-flex justify-content-between align-items-center flex-wrap">'+
                          '<h5 class="mb-0" style="font-weight: 700;">'+
                            '<i class="'+statusIcon+'"></i> '+statusText+
                            (isCopy ? ' <span class="badge badge-light text-danger ml-2"><i class="fas fa-copy"></i> COPIA</span>' : '')+
                            (esRecepcion ? ' <span class="badge badge-light ml-2" style="color:#2C5282;"><i class="fas fa-file-import"></i> RECEPCIÓN</span>' : '')+
                          '</h5>'+
                          '<span style="font-size: 0.9rem;"><i class="far fa-clock"></i> '+datadetalle[i][5]+'</span>'+
                        '</div>'+
                      '</div>'+
                      '<div class="card-body" style="padding: 1.5rem; background: #f8f9fa;">'+
                        '<div class="row mb-3">'+
                          '<div class="col-md-6 mb-2">'+
                            '<div style="background: white; padding: 1rem; border-radius: 10px; border-left: 4px solid #2C5282;">'+
                              '<div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 0.25rem;">'+
                                '<i class="'+iconoOrigen+'"></i> '+rotuloOrigen+
                              '</div>'+
                              '<div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">'+areaOrigen+'</div>'+

                            '</div>'+
                          '</div>'+
                          '<div class="col-md-6 mb-2">'+
                            '<div style="background: white; padding: 1rem; border-radius: 10px; border-left: 4px solid #15803D;">'+
                              '<div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 0.25rem;">'+
                                '<i class="fas fa-flag-checkered"></i> DESTINO'+
                              '</div>'+
                              '<div style="font-weight: 700; color: #1f2937; font-size: 1.1rem;">'+areaDestino+'</div>'+
                            '</div>'+
                          '</div>'+
                        '</div>'+
                        '<div style="background: white; padding: 1.25rem; border-radius: 10px; border-left: 4px solid #1E3A5F;">'+
                          '<div style="font-size: 0.85rem; color: #6b7280; margin-bottom: 0.5rem;">'+
                            '<i class="fas fa-comment-alt"></i> DESCRIPCIÓN'+
                          '</div>'+
                          '<div style="color: #2d3748; line-height: 1.6;">'+datadetalle[i][7]+'</div>'+
                        '</div>'+
                        // Acuse de recepción del área que recibió este envío
                        (datadetalle[i].recibido_fecha
                          ? '<div style="margin-top: 0.75rem; color: #15803D; font-weight: 600;"><i class="fas fa-inbox"></i> Recibido por el área el '+datadetalle[i].recibido_fecha+'</div>'
                          : (datadetalle[i][8]=="PENDIENTE" ? '<div style="margin-top: 0.75rem; color: #718096;"><i class="far fa-clock"></i> Aún sin acuse de recepción</div>' : ''))+
                      '</div>'+
                    '</div>';
          }
          document.getElementById("div_seguimiento").innerHTML=cadena;
          // El recorrido también se dibuja como diagrama (js/flujograma.js)
          if (typeof Flujograma_Cargar === "function") { Flujograma_Cargar(data[0][0], "#div_flujo"); Flujo_Vista("diagrama"); }

        }
      })
      ////TERMINA EL AJAX//////////
    }else{
      document.getElementById("div_buscador").style.display = "none";
      return Swal.fire("Mensaje de Advertencia","No se encontraron datos del Documento Buscado","warning");

    }
  })
}
function Cargar_Select_Expedientes(){
  let id = document.getElementById('txtprincipalid').value;

  $.ajax({
    "url":"../controller/tramite_area/controlador_expedientes.php",
    type:'POST',
    data:{
      id:id
  }
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="<option value=''>Seleccionar Expediente</option>";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i].documento_id+"'>"+(data[i].doc_expediente || data[i].documento_id)+" · Doc. N° "+data[i].doc_nrodocumento+" · DNI "+data[i].doc_dniremitente+" · "+data[i].REMITENTE+"</option>";    
      }
        document.getElementById('txt_expediente').innerHTML=cadena;
    }else{
      cadena+="<option value=''>No hay tipos disponibles</option>";
      document.getElementById('txt_expediente').innerHTML=cadena;
    }
  })
}
function Cargar_Select_Expedientes_Admin(){

  $.ajax({
    "url":"../controller/tramite_area/controlador_expedientes_admin.php",
    type:'POST',
   
  }).done(function(resp){
    let data=JSON.parse(resp);
    if(data.length>0){
      let cadena ="<option value=''>Seleccionar Expediente</option>";
      for (let i = 0; i < data.length; i++) {
        cadena+="<option value='"+data[i].documento_id+"'>"+(data[i].doc_expediente || data[i].documento_id)+" · Doc. N° "+data[i].doc_nrodocumento+" · DNI "+data[i].doc_dniremitente+" · "+data[i].REMITENTE+"</option>";    
      }
        document.getElementById('txt_expediente').innerHTML=cadena;
    }else{
      cadena+="<option value=''>No hay tipos disponibles</option>";
      document.getElementById('txt_expediente').innerHTML=cadena;
    }
  })
}
function Traerrdatosexpediente(idrequisito){
  $.ajax({
          
    "url":"../controller/tramite_area/controlador_traerdatos_expediente.php",
    type:'POST',
        data:{
          id:idrequisito
        }
      }).done(function(resp){
      var data = JSON.parse(resp);
      var cadena="";
      if(data.length>0){
        $("#txt_numero").val(data[0][0]);
        $("#txt_dni").val(data[0][1]);


      }
      else{
          return Swal.fire("Mensaje de Error","No se pudo traer el requisito","error");
      }
  })
}

