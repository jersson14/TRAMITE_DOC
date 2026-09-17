/**
 * Feriados y días no laborables (view/feriado/view_feriado.php).
 * Son los días que lib/Plazos.php descuenta al contar plazos en días hábiles.
 */
var tbl_feriado;
var FERIADO_ANIOS_LISTOS = false;

var TIPOS_FERIADO = {
  NACIONAL: ["badge-primary", "Nacional"],
  REGIONAL: ["badge-info", "Regional"],
  NO_LABORABLE: ["badge-warning", "No laborable"],
};

function anioElegido() {
  var select = document.getElementById("cbo_anio");
  return select && select.value ? select.value : new Date().getFullYear();
}

/** Llena el selector con los años que tienen feriados, más el actual y el siguiente. */
function Pintar_Anios(anios, anioActual) {
  var select = document.getElementById("cbo_anio");
  if (!select) return;
  var hoy = new Date().getFullYear();
  var lista = (anios || []).slice();
  [hoy, hoy + 1].forEach(function (a) {
    if (lista.indexOf(a) === -1) lista.push(a);
  });
  lista.sort(function (a, b) { return b - a; });
  select.innerHTML = lista.map(function (a) {
    return '<option value="' + a + '"' + (a == anioActual ? " selected" : "") + ">" + a + "</option>";
  }).join("");
}

function Avisar_Anio(filas, anio) {
  var caja = document.getElementById("aviso_anio");
  if (!caja) return;
  if (filas.length === 0) {
    caja.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ' +
      "El año " + anio + " no tiene feriados registrados: los plazos se contarían solo sin sábados y domingos. " +
      'Use el botón <b>"Cargar feriados nacionales del año"</b>.</div>';
  } else {
    caja.innerHTML = "";
  }
}

function Listar_Feriados() {
  var anio = anioElegido();
  tbl_feriado = $("#tabla_feriado").DataTable({
    ordering: false,
    bLengthChange: false,
    pageLength: 25,
    destroy: true,
    pagingType: "simple_numbers",
    responsive: true,
    processing: true,
    ajax: {
      url: "../controller/feriado/controlador_listar_feriado.php",
      type: "POST",
      data: { anio: anio },
      dataSrc: function (r) {
        if (!FERIADO_ANIOS_LISTOS) {
          Pintar_Anios(r.anios, r.anio);
          FERIADO_ANIOS_LISTOS = true;
        }
        Avisar_Anio(r.data || [], r.anio);
        return r.data || [];
      },
    },
    columns: [
      { data: "fecha_texto" },
      {
        data: "dia",
        render: function (data, type, row) {
          var dias = { Monday: "Lunes", Tuesday: "Martes", Wednesday: "Miércoles", Thursday: "Jueves",
                       Friday: "Viernes", Saturday: "Sábado", Sunday: "Domingo" };
          var nombre = dias[data] || data;
          // Un feriado en fin de semana no cambia nada: esos días ya no son hábiles
          return row.fin_de_semana
            ? nombre + '<br><small class="text-muted">no afecta los plazos</small>'
            : nombre;
        },
      },
      { data: "descripcion" },
      {
        data: "tipo",
        render: function (data) {
          var t = TIPOS_FERIADO[data] || ["badge-light", data];
          return '<span class="badge ' + t[0] + '">' + t[1] + "</span>";
        },
      },
      {
        defaultContent:
          "<button class='editar btn btn-primary btn-sm' title='Editar'><i class='fa fa-edit'></i></button>&nbsp;" +
          "<button class='borrar btn btn-danger btn-sm' title='Eliminar'><i class='fa fa-trash'></i></button>",
      },
    ],
    language: idioma_espanol,
  });
}

function Abrir_Feriado(fila) {
  document.getElementById("lb_titulo_feriado").innerHTML = fila
    ? '<i class="far fa-calendar-check mr-2"></i>EDITAR FERIADO'
    : '<i class="far fa-calendar-plus mr-2"></i>NUEVO FERIADO';
  document.getElementById("txt_fecha_feriado").value = fila ? fila.fecha : "";
  document.getElementById("txt_motivo_feriado").value = fila ? fila.descripcion : "";
  document.getElementById("cbo_tipo_feriado").value = fila ? fila.tipo : "NACIONAL";
  document.getElementById("aviso_fecha_feriado").textContent = "";
  $("#modal_feriado").modal("show");
}

// Avisa si la fecha elegida cae sábado o domingo
$(document).on("change", "#txt_fecha_feriado", function () {
  var aviso = document.getElementById("aviso_fecha_feriado");
  if (!this.value) { aviso.textContent = ""; return; }
  var d = new Date(this.value + "T12:00:00").getDay();
  aviso.textContent = d === 0 || d === 6
    ? "Ese día es sábado o domingo: ya no cuenta para los plazos."
    : "";
});

function Guardar_Feriado() {
  var fecha = document.getElementById("txt_fecha_feriado").value;
  var descripcion = document.getElementById("txt_motivo_feriado").value.trim();
  var tipo = document.getElementById("cbo_tipo_feriado").value;

  if (!fecha) return Swal.fire("Mensaje de Advertencia", "Elija la fecha del feriado", "warning");
  if (descripcion.length < 3) return Swal.fire("Mensaje de Advertencia", "Escriba el motivo del feriado", "warning");

  $.ajax({
    url: "../controller/feriado/controlador_guardar_feriado.php",
    type: "POST",
    dataType: "json",
    data: { fecha: fecha, descripcion: descripcion, tipo: tipo },
  }).done(function () {
    $("#modal_feriado").modal("hide");
    Swal.fire("Mensaje de Confirmación", "Feriado guardado", "success");
    FERIADO_ANIOS_LISTOS = false;
    document.getElementById("cbo_anio").value = fecha.slice(0, 4);
    Listar_Feriados();
  });
}

$("#tabla_feriado").on("click", ".editar", function () {
  Abrir_Feriado(tbl_feriado.row($(this).parents("tr")).data());
});

$("#tabla_feriado").on("click", ".borrar", function () {
  var fila = tbl_feriado.row($(this).parents("tr")).data();
  Swal.fire({
    title: "¿Quitar este feriado?",
    html: "<b>" + fila.fecha_texto + "</b><br>" + $("<i>").text(fila.descripcion).html() +
      "<br><small>Ese día volverá a contar como hábil para los plazos.</small>",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, quitar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#B91C1C",
  }).then(function (r) {
    if (!r.isConfirmed) return;
    $.ajax({
      url: "../controller/feriado/controlador_eliminar_feriado.php",
      type: "POST",
      dataType: "json",
      data: { fecha: fila.fecha },
    }).done(function () {
      Swal.fire("Mensaje de Confirmación", "Feriado eliminado", "success");
      Listar_Feriados();
    });
  });
});

function Cargar_Nacionales() {
  var anio = anioElegido();
  Swal.fire({
    title: "¿Cargar los feriados nacionales de " + anio + "?",
    text: "Se agregarán los feriados nacionales que falten. Los que ya están registrados no se modifican.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cargar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#1E3A5F",
  }).then(function (r) {
    if (!r.isConfirmed) return;
    $.ajax({
      url: "../controller/feriado/controlador_cargar_nacionales.php",
      type: "POST",
      dataType: "json",
      data: { anio: anio },
    }).done(function (resp) {
      Swal.fire(
        resp.agregados > 0 ? "Feriados cargados" : "Sin cambios",
        resp.agregados > 0
          ? "Se agregaron " + resp.agregados + " feriados de " + resp.anio + "."
          : "Los " + resp.total + " feriados nacionales de " + resp.anio + " ya estaban registrados.",
        resp.agregados > 0 ? "success" : "info"
      );
      FERIADO_ANIOS_LISTOS = false;
      Listar_Feriados();
    });
  });
}
