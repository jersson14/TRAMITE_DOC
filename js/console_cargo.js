var tbl_cargo;
function listar_area(){
  tbl_cargo = $("#tabla_cargo").DataTable({
      "ordering":true,   
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
          "url":"../controller/cargos/controlador_listar_cargos.php",
          type:'POST'
      },
      dom: 'Bfrtip', 
     
      buttons:[ 
        
    {
      extend:    'excelHtml5',
      text:      '<i class="fas fa-file-excel"></i> ',
      titleAttr: 'Exportar a Excel',
      
      filename: function() {
        return  "LISTA DE CARGOS"
      },
        title: function() {
          return  "LISTA DE CARGOS" }
  
    },
    {
      extend:    'pdfHtml5',
      text:      '<i class="fas fa-file-pdf"></i> ',
      titleAttr: 'Exportar a PDF',
      filename: function() {
        return  "LISTA DE CARGOS"
      },
    title: function() {
      return  "LISTA DE CARGOS"
    }
  },
    {
      extend:    'print',
      text:      '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      
    title: function() {
      return  "LISTA DE CARGOS"
  
    }
    }],
      "columns":[
        {"defaultContent":""},
        {"data":"nombre_cargo"},
        {"data":"descripcion"},
        {"data":"estado",
            render: function(data,type,row){
                    if(data=='ACTIVO'){
                    return '<span class="badge bg-success">ACTIVO</span>';
                    }else{
                    return '<span class="badge bg-danger">INACTIVO</span>';
                    }
            }   
        },
        {"defaultContent":"<button class='editar btn btn-primary  btn-sm' title='Editar datos de área'><i class='fa fa-edit'></i> Editar</button>"},
        
    ],

    "language":idioma_espanol,
    select: true
});
tbl_cargo.on('draw.td',function(){
  var PageInfo = $("#tabla_cargo").DataTable().page.info();
  tbl_cargo.column(0, {page: 'current'}).nodes().each(function(cell, i){
    cell.innerHTML = i + 1 + PageInfo.start;
  });
});
}
$('#tabla_cargo').on('click','.editar',function(){
  var data = tbl_cargo.row($(this).parents('tr')).data();

  if(tbl_cargo.row(this).child.isShown()){
      var data = tbl_cargo.row(this).data();
  }
  $("#modal_editar").modal('show');
  document.getElementById('txt_idcargo').value=data.id_cargo;
  document.getElementById('txt_cargo_editar').value=data.nombre_cargo;
  document.getElementById('txt_descripcion_editar').value=data.descripcion;
  document.getElementById('txt_estatus').value=data.estado;
})

function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}

function Registrar_Cargo(){
  let area = document.getElementById('txt_cargo').value;
  let cargo = document.getElementById('txt_descripcion').value;

  if(area.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/cargos/controlador_registro_cargo.php",
    type:'POST',
    data:{
        area:area,
        cargo:cargo
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Nuevo cargo registrado","success").then((value)=>{
          tbl_cargo.ajax.reload();
          document.getElementById('txt_cargo').value="";
          document.getElementById('txt_descripcion').value="";

        $("#modal_registro").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El cargo que intenta registrar ya se encuentra en la base de datos","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo el registro","error");

    }
  })
}
function Modificar_Area(){
  let id = document.getElementById('txt_idcargo').value;
  let cargo = document.getElementById('txt_cargo_editar').value;
  let descrip = document.getElementById('txt_descripcion_editar').value;
  let esta = document.getElementById('txt_estatus').value;

  if(cargo.length==0 || id.length==0||descrip.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/cargos/controlador_modificar_cargo.php",
    type:'POST',
    data:{
      id:id,
      cargo:cargo,
      descrip:descrip,
      esta:esta
    }
  }).done(function(resp){
    if(resp>0){
      if(resp==1){
        Swal.fire("Mensaje de Confirmación","Datos actualizados correctamente!!!","success").then((value)=>{
          tbl_cargo.ajax.reload();
        $("#modal_editar").modal('hide');
        });
      }else{
        Swal.fire("Mensaje de Advertencia","El nombre del cargo que intenta actualizar ya se encuentra en la base de datos","warning");
      }
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}