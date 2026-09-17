/**
 * Reporte de plazos y productividad por área.
 * Los días se cuentan en días hábiles (lib/Plazos.php), como el semáforo.
 */
var tbl_plazos, tbl_vencidos, PLAZOS_ES_ADMIN = false;

function Iniciar_Reporte_Plazos(esAdmin) {
  PLAZOS_ES_ADMIN = esAdmin;
  var hoy = new Date();
  var inicio = new Date(hoy.getFullYear(), 0, 1);
  document.getElementById("txt_desde").value = inicio.toISOString().slice(0, 10);
  document.getElementById("txt_hasta").value = hoy.toISOString().slice(0, 10);

  if (esAdmin) {
    $.ajax({ url: "../controller/usuario/controlador_cargar_select_area.php", type: "POST" }).done(function (resp) {
      var data = typeof resp === "string" ? JSON.parse(resp) : resp;
      var cadena = '<option value="0">Todas las áreas</option>';
      (data || []).forEach(function (a) {
        cadena += '<option value="' + a[0] + '">' + escaparPlazos(a[1]) + "</option>";
      });
      document.getElementById("cbo_area_plazos").innerHTML = cadena;
    });
  }

  Exportaciones_Montar("#exportar_plazos", "plazos", Filtros_Plazos);
  Listar_Plazos();
}

function escaparPlazos(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

function Filtros_Plazos() {
  var desde = document.getElementById("txt_desde").value;
  var hasta = document.getElementById("txt_hasta").value;
  if (!desde || !hasta) {
    Swal.fire("Mensaje de Advertencia", "Elija el rango de fechas", "warning");
    return null;
  }
  if (desde > hasta) {
    Swal.fire("Mensaje de Advertencia", "La fecha inicial no puede ser posterior a la final", "warning");
    return null;
  }
  var area = PLAZOS_ES_ADMIN ? document.getElementById("cbo_area_plazos").value : 0;
  return { desde: desde, hasta: hasta, area: area };
}

function tarjetaPlazo(rotulo, valor, detalle, color) {
  return '<div class="indicador" style="border-left-color:' + color + ';">' +
      '<div class="indicador-cuerpo">' +
        '<span class="indicador-rotulo">' + escaparPlazos(rotulo) + "</span>" +
        '<strong class="indicador-valor">' + escaparPlazos(valor) + "</strong>" +
        (detalle ? '<span class="indicador-detalle">' + escaparPlazos(detalle) + "</span>" : "") +
      "</div>" +
    "</div>";
}

function Listar_Plazos() {
  var filtros = Filtros_Plazos();
  if (!filtros) return;

  $.ajax({ url: "../controller/reporte/controlador_plazos.php", type: "POST", dataType: "json", data: filtros })
    .done(function (r) {
      var t = r.totales;
      document.getElementById("resumen_plazos").innerHTML =
        '<div class="indicadores mt-2">' +
          tarjetaPlazo("Recibidos en el período", t.recibidos, "", "#2C5282") +
          tarjetaPlazo("Despachados", t.despachados, "envíos resueltos o derivados", "#15803D") +
          tarjetaPlazo("Dentro del plazo", t.cumplimiento === null ? "—" : t.cumplimiento + "%",
            t.en_plazo + " a tiempo · " + t.fuera_plazo + " fuera de plazo", "#1E3A5F") +
          tarjetaPlazo("En curso hoy", t.en_curso, "", "#B45309") +
          tarjetaPlazo("Vencidos hoy", t.vencidos, "", "#B91C1C") +
        "</div>";

      tbl_plazos = $("#tabla_plazos").DataTable({
        data: r.areas, destroy: true, ordering: true, order: [[7, "desc"]], paging: false, searching: false,
        info: false, responsive: true, language: idioma_espanol,
        columns: [
          { data: "area" },
          { data: "recibidos" },
          { data: "despachados" },
          { data: "promedio", render: function (d) { return d === null ? "—" : d; } },
          { data: "maximo", render: function (d) { return d === null ? "—" : d; } },
          {
            data: "cumplimiento",
            render: function (d) {
              if (d === null) return '<span class="text-muted">sin plazo</span>';
              var clase = d >= 90 ? "semaforo-verde" : d >= 70 ? "semaforo-ambar" : "semaforo-rojo";
              return '<span class="semaforo ' + clase + '">' + d + "%</span>";
            },
          },
          { data: "en_curso" },
          {
            data: "vencidos",
            render: function (d) { return d > 0 ? '<span class="semaforo semaforo-rojo">' + d + "</span>" : "—"; },
          },
        ],
      });

      tbl_vencidos = $("#tabla_vencidos").DataTable({
        data: r.vencidos, destroy: true, ordering: true, order: [[6, "desc"]], pageLength: 10,
        responsive: true, language: idioma_espanol,
        columns: [
          { data: "expediente" },
          { data: "asunto" },
          { data: "remitente" },
          { data: "area" },
          { data: "estado" },
          { data: "limite" },
          { data: "atraso", render: function (d) { return '<span class="semaforo semaforo-rojo">' + d + " día(s)</span>"; } },
        ],
      });
    });
}
