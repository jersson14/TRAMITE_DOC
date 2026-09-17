/**
 * Comunicados (administrador).
 *
 * Cada comunicado se dirige a todo el personal, a los administradores, al
 * personal de áreas o a áreas concretas, con vigencia opcional. Los vigentes se
 * muestran como aviso al entrar al sistema (js/console_comunicados_alerta.js) y
 * queda constancia de quién confirmó haberlos leído.
 */
var tbl_comunicados;

var DESTINOS = {
  TODOS: ["badge-primary", "Todo el personal"],
  ADMINISTRADORES: ["badge-info", "Administradores"],
  SECRETARIAS: ["badge-info", "Personal de áreas"],
  AREAS: ["badge-warning", "Áreas específicas"],
};

function escaparComunicado(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

function listar_comunicado() {
  tbl_comunicados = $("#tabla_comunicados").DataTable({
    ordering: false,
    bLengthChange: true,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
    pageLength: 10,
    pagingType: "full_numbers",
    responsive: true,
    destroy: true,
    processing: true,
    ajax: { url: "../controller/comunicados/controlador_listar_comunicados.php", type: "POST" },
    columns: [
      { defaultContent: "" },
      { data: "titulo" },
      {
        data: "descripcion",
        render: function (data, type) {
          if (type !== "display") return data || "";
          var texto = String(data || "");
          return '<span title="' + escaparComunicado(texto) + '">' +
            escaparComunicado(texto.length > 90 ? texto.slice(0, 90) + "…" : texto) + "</span>";
        },
      },
      {
        data: "com_destino",
        render: function (data, type, row) {
          var d = DESTINOS[data] || ["badge-light", data];
          var etiqueta = '<span class="badge ' + d[0] + '">' + d[1] + "</span>";
          if (data === "AREAS" && row.areas) {
            etiqueta += '<br><small class="text-muted">' + escaparComunicado(row.areas) + "</small>";
          }
          return etiqueta;
        },
      },
      { data: "fecha_formateada" },
      {
        data: "desde_texto",
        render: function (data, type, row) {
          if (!data && !row.hasta_texto) return '<span class="text-muted">Sin límite</span>';
          return (data ? "Desde " + data : "Sin inicio") + "<br>" + (row.hasta_texto ? "Hasta " + row.hasta_texto : "sin fin");
        },
      },
      {
        data: "estado",
        render: function (data) {
          return data === "NUEVO"
            ? '<span class="badge badge-success">Vigente</span>'
            : '<span class="badge badge-secondary">Archivado</span>';
        },
      },
      {
        data: "leidos",
        render: function (data, type, row) {
          var total = parseInt(data, 10) || 0;
          return "<button class='lecturas btn btn-sm " + (total ? "btn-primary" : "btn-secondary") + "' " +
            "title='Ver quiénes confirmaron la lectura'><i class='fas fa-check-double'></i> " + total + "</button>";
        },
      },
      {
        defaultContent:
          "<button class='editar btn btn-primary btn-sm' title='Editar comunicado'><i class='fa fa-edit'></i></button>",
      },
    ],
    language: idioma_espanol,
  });

  tbl_comunicados.on("draw.td", function () {
    var info = $("#tabla_comunicados").DataTable().page.info();
    tbl_comunicados.column(0, { page: "current" }).nodes().each(function (celda, i) {
      celda.innerHTML = i + 1 + info.start;
    });
  });
}

/** Áreas para el selector de destinatarios. */
function Cargar_Areas_Comunicado() {
  $.ajax({ url: "../controller/usuario/controlador_cargar_select_area.php", type: "POST" }).done(function (resp) {
    var data = typeof resp === "string" ? JSON.parse(resp) : resp;
    var cadena = "";
    (data || []).forEach(function (a) {
      cadena += '<option value="' + a[0] + '">' + escaparComunicado(a[1]) + "</option>";
    });
    document.getElementById("select_areas_comunicado").innerHTML = cadena;
    $("#select_areas_comunicado").select2({
      width: "100%",
      placeholder: "Elija una o varias áreas",
      dropdownParent: $("#modal_comunicado"),
    });
  });
}

/** El selector de áreas solo aparece cuando el comunicado va a áreas concretas. */
function Cambio_Destino_Comunicado() {
  var esAreas = document.getElementById("cbo_destino").value === "AREAS";
  document.getElementById("bloque_areas_comunicado").hidden = !esAreas;
}

function Abrir_Comunicado(fila) {
  document.getElementById("lb_titulo_comunicado").innerHTML = fila
    ? '<i class="fas fa-edit mr-2"></i>EDITAR COMUNICADO'
    : '<i class="fas fa-bullhorn mr-2"></i>NUEVO COMUNICADO';
  document.getElementById("txt_id_comun").value = fila ? fila.id_comunicado : "";
  document.getElementById("txt_titulo").value = fila ? fila.titulo : "";
  document.getElementById("txt_descripcion").value = fila ? fila.descripcion : "";
  document.getElementById("txt_enlace").value = fila && fila.enlace ? fila.enlace : "";
  document.getElementById("cbo_destino").value = fila ? fila.com_destino : "TODOS";
  document.getElementById("cbo_estado_comunicado").value = fila ? fila.estado : "NUEVO";
  document.getElementById("txt_desde_comunicado").value = fila && fila.com_desde ? fila.com_desde : "";
  document.getElementById("txt_hasta_comunicado").value = fila && fila.com_hasta ? fila.com_hasta : "";
  $("#select_areas_comunicado").val(fila && fila.areas_id ? String(fila.areas_id).split(",") : []).trigger("change");
  Cambio_Destino_Comunicado();
  $("#modal_comunicado").modal({ backdrop: "static", keyboard: true, show: true });
}

function Guardar_Comunicado() {
  var id = document.getElementById("txt_id_comun").value;
  var destino = document.getElementById("cbo_destino").value;
  var areas = $("#select_areas_comunicado").val() || [];
  var datos = {
    titulo: document.getElementById("txt_titulo").value.trim(),
    descri: document.getElementById("txt_descripcion").value.trim(),
    enlace: document.getElementById("txt_enlace").value.trim(),
    destino: destino,
    estado: document.getElementById("cbo_estado_comunicado").value,
    desde: document.getElementById("txt_desde_comunicado").value,
    hasta: document.getElementById("txt_hasta_comunicado").value,
    "areas[]": areas,
  };

  if (datos.titulo.length < 4 || datos.descri.length < 4) {
    return Swal.fire("Mensaje de Advertencia", "Escriba el título y el contenido del comunicado", "warning");
  }
  if (destino === "AREAS" && areas.length === 0) {
    return Swal.fire("Mensaje de Advertencia", "Elija al menos un área destinataria", "warning");
  }

  var nuevo = !id;
  if (!nuevo) datos.id = id;

  $.ajax({
    url: nuevo
      ? "../controller/comunicados/controlador_registro_comunicados.php"
      : "../controller/comunicados/controlador_modificar_comunicados.php",
    type: "POST",
    dataType: "json",
    data: datos,
  }).done(function () {
    $("#modal_comunicado").modal("hide");
    Swal.fire("Mensaje de Confirmación", nuevo ? "Comunicado publicado" : "Comunicado actualizado", "success");
    tbl_comunicados.ajax.reload();
    if (typeof Cargar_Notificaciones === "function") Cargar_Notificaciones();
  });
}

$("#tabla_comunicados").on("click", ".editar", function () {
  Abrir_Comunicado(tbl_comunicados.row($(this).parents("tr")).data());
});

$("#tabla_comunicados").on("click", ".lecturas", function () {
  var fila = tbl_comunicados.row($(this).parents("tr")).data();
  document.getElementById("lb_comunicado_lecturas").textContent = fila.titulo;
  document.getElementById("lista_lecturas").innerHTML = '<div class="archivos-aviso">Cargando...</div>';
  $("#modal_lecturas").modal("show");
  $.ajax({
    url: "../controller/comunicados/controlador_lecturas.php",
    type: "POST",
    dataType: "json",
    data: { id: fila.id_comunicado },
  }).done(function (r) {
    var filas = (r.data || []).map(function (l) {
      return "<tr><td>" + escaparComunicado(l.persona) + "</td><td>" + escaparComunicado(l.area || "—") +
        "</td><td>" + escaparComunicado(l.fecha) + "</td></tr>";
    }).join("");
    document.getElementById("lista_lecturas").innerHTML = filas
      ? '<table class="table table-sm table-modern"><thead><tr><th>Persona</th><th>Área</th><th>Confirmó el</th></tr></thead><tbody>' +
        filas + "</tbody></table>"
      : '<div class="archivos-aviso">Todavía nadie confirmó la lectura.</div>';
  });
});

/* ----- Tabla de comunicados del tablero (se mantiene) ----- */
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
                    if(data=='PENDIENTE'){
                    return '<span class="badge bg-warning">POR LEER</span>';
                    }else{
                    return '<span class="badge bg-success">LEÍDO</span>';
                    }
            }   
        },      
    ],

    "language":idioma_espanol,
    select: false
});

}
