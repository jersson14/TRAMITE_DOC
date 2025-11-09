var tbl_comunicados;
function listar_comunicado(){
  tbl_comunicados = $("#tabla_comunicados").DataTable({
      "ordering":false,   
      "bLengthChange":true,
      "searching": { "regex": false },
      "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
      "pageLength": 10,
      pagingType: 'full_numbers',
      scrollCollapse: true,
      responsive: true,
      "destroy":true,
      "async": false ,
      "processing": true,
      "ajax":{
          "url":"../controller/comunicados/controlador_listar_comunicados.php",
          type:'POST'
      },
      dom: 'Bfrtip', 
     
      buttons:[ 
        
    {
      extend:    'excelHtml5',
      text:      '<i class="fas fa-file-excel"></i> ',
      titleAttr: 'Exportar a Excel',
      
      filename: function() {
        return  "LISTA DE COMUNICADOS"
      },
        title: function() {
          return  "LISTA DE COMUNICADOS" }
  
    },
    {
      extend:    'pdfHtml5',
      text:      '<i class="fas fa-file-pdf"></i> ',
      orientation: 'landscape',
      pageSize: 'LEGAL',
      titleAttr: 'Exportar a PDF',
      filename: function() {
        return  "LISTA DE COMUNICADOS"
      },
    title: function() {
      return  "LISTA DE COMUNICADOS"
    }
  },
    {
      extend:    'print',
      text:      '<i class="fa fa-print"></i> ',
      titleAttr: 'Imprimir',
      
    title: function() {
      return  "LISTA DE COMUNICADOS"
  
    }
    }],
      "columns":[
        {"defaultContent":""},
        {"data":"titulo"},
        {"data":"descripcion"},
        {"data":"fecha_formateada"},
        {"data":"enlace",
          render: function (datae, type, row ) {
            if(datae==''){
              return "<a href="+datae+" target='_blank'><button disabled style='font-size:13px;' type='button' class='control btn btn-warning btn-sm' title='Ver Enlace'><i class='fas fa-eye'></i> Ver</button></a>  ";                 
            }
            {
              return "<a href="+datae+" target='_blank'><button style='font-size:13px;' type='button' class='control btn btn-warning btn-sm' title='Ver Enlace'><i class='fas fa-eye'></i> Ver</button></a>  ";                 
            }
          }
        },
        {"data":"estado",
            render: function(data,type,row){
                    if(data=='NUEVO'){
                    return '<span class="badge bg-success">ULTIMO COMUNICADO</span>';
                    }else{
                    return '<span class="badge bg-danger">PASADO</span>';
                    }
            }   
        },
        {"defaultContent":"<button class='editar btn btn-primary  btn-sm' title='Editar datos de comunicado'><i class='fa fa-edit'>Editar</i></button>"},
        
    ],

    "language":idioma_espanol,
    select: true
});
tbl_comunicados.on('draw.td',function(){
  var PageInfo = $("#tabla_comunicados").DataTable().page.info();
  tbl_comunicados.column(0, {page: 'current'}).nodes().each(function(cell, i){
    cell.innerHTML = i + 1 + PageInfo.start;
  });
});
}
$('#tabla_comunicados').on('click','.editar',function(){
  var data = tbl_comunicados.row($(this).parents('tr')).data();

  if(tbl_comunicados.row(this).child.isShown()){
      var data = tbl_comunicados.row(this).data();
  }
  $("#modal_editar").modal('show');
  document.getElementById('txt_titulo_editar').value=data.titulo;
  document.getElementById('txt_descripcion_editar').value=data.descripcion;
  document.getElementById('txt_id_comun').value=data.id_comunicado;
  document.getElementById('txt_enlace_editar').value=data.enlace;
})

function AbrirRegistro(){
  $("#modal_registro").modal({backdrop:'static',keyboard:false})
  $("#modal_registro").modal('show');
}

function Registrar_Comunicado(){
  let titulo = document.getElementById('txt_titulo').value;
  let descri = document.getElementById('txt_descripcion').value;
  let idusu = document.getElementById('txtprincipalid').value;
  let enlace = document.getElementById('txt_enlace').value;

  if(titulo.length==0 || descri.length==0){
      return Swal.fire("Mensaje de Advertencia","Llene los campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/comunicados/controlador_registro_comunicados.php",
    type:'POST',
    data:{
      titulo:titulo,
      descri:descri,
      idusu:idusu,
      enlace:enlace
    }
  }).done(function(resp){
      if(resp>0){
        Swal.fire("Mensaje de Confirmación","Nuevo Comunicado registrado","success").then((value)=>{
          tbl_comunicados.ajax.reload();
          document.getElementById('txt_titulo').value="";
          document.getElementById('txt_descripcion').value="";
          document.getElementById('txt_enlace').value="";

          $("#modal_registro").modal('hide');
        });
      
    }else{
      return Swal.fire("Mensaje de Error","No se completo el registro","error");
    }
  })
}
function Modificar_Comunicado(){
  let id = document.getElementById('txt_id_comun').value;
  let titulo = document.getElementById('txt_titulo_editar').value;
  let descri = document.getElementById('txt_descripcion_editar').value;
  let enlace = document.getElementById('txt_enlace_editar').value;

  if(titulo.length==0 || descri.length==0 || id.length==0){
      return Swal.fire("Mensaje de Advertencia","Tiene campos vacios","warning");
  }
  $.ajax({
    "url":"../controller/comunicados/controlador_modificar_comunicados.php",
    type:'POST',
    data:{
      id:id,
      titulo:titulo,
      descri:descri,
      enlace:enlace

    }
  }).done(function(resp){
      if(resp>0){
        Swal.fire("Mensaje de Confirmación","Datos actualizados correctamente","success").then((value)=>{
          tbl_comunicados.ajax.reload();
        $("#modal_editar").modal('hide');
        });
    }else{
      return Swal.fire("Mensaje de Error","No se completo la actualización","error");

    }
  })
}

/////

var tbl_comunicados_dash;
function listar_comunicado_dash(){
  tbl_comunicados_dash = $("#tabla_comunicados_listar").DataTable({
      "ordering":false,   
      "processing": true,
      responsive: true,
      "searching": false ,
      "bPaginate": false,
      "ajax":{
          "url":"../controller/comunicados/controlador_listar_comunicados2.php",
          type:'POST'
      },
     
      "columns":[
        {"data":"titulo"},
        {"data":"descripcion"},
        {"data":"fecha_formateada"},
        {"data":"enlace",
        render: function (datae, type, row ) {
          if(datae==''){
            return "<a href="+datae+"  target='_blank'><button disabled style='font-size:13px;' type='button' class='control btn btn-warning btn-sm' title='Ver Enlace'><i class='fas fa-eye'></i> Ver</button></a>  ";                 
          }
          {
            return "<a href="+datae+" target='_blank'><button style='font-size:13px;' type='button' class='control btn btn-warning btn-sm' title='Ver Enlace'><i class='fas fa-eye'></i> Ver</button></a>  ";                 
          }
        }
      },
        {"data":"estado",
            render: function(data,type,row){
                    if(data=='NUEVO'){
                    return '<span class="badge bg-success">ULTIMO COMUNICADO</span>';
                    }else{
                    return '<span class="badge bg-danger">PASADO</span>';
                    }
            }   
        },      
    ],

    "language":idioma_espanol,
    select: false
});

}
