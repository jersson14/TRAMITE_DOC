/**
 * Tablero del sistema, para el administrador y para el personal de área.
 *
 * Lo primero que se ve es lo que hay que hacer —vencidos, sin acuse, atenciones
 * sin responder, trámites detenidos— y después el volumen y los tiempos. Los
 * plazos vienen calculados en días hábiles por lib/Plazos.php, el mismo cálculo
 * del semáforo de las bandejas, así que las cifras coinciden.
 */
function textoIndicador(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

function tarjetaIndicador(rotulo, valor, detalle, color, icono, ruta) {
  var contenido =
    '<div class="indicador" style="border-left-color:' + color + ';">' +
      '<div class="indicador-icono" style="background:' + color + ';"><i class="' + icono + '"></i></div>' +
      '<div class="indicador-cuerpo">' +
        '<span class="indicador-rotulo">' + textoIndicador(rotulo) + "</span>" +
        '<strong class="indicador-valor">' + textoIndicador(valor) + "</strong>" +
        (detalle ? '<span class="indicador-detalle">' + textoIndicador(detalle) + "</span>" : "") +
      "</div>" +
    "</div>";
  return ruta
    ? '<a class="indicador-enlace" href="#/' + ruta + '" title="Ver ' + textoIndicador(rotulo.toLowerCase()) + '">' + contenido + "</a>"
    : contenido;
}

/** "hace 5 minutos", "ayer 09:14": más útil que una fecha suelta. */
function cuandoIndicador(fecha) {
  if (!fecha) return "—";
  var f = new Date(String(fecha).replace(" ", "T"));
  if (isNaN(f)) return textoIndicador(fecha);
  var minutos = Math.round((Date.now() - f.getTime()) / 60000);
  if (minutos < 1) return "hace un momento";
  if (minutos < 60) return "hace " + minutos + " min";
  var horas = Math.round(minutos / 60);
  if (horas < 24) return "hace " + horas + " h";
  var dias = Math.round(horas / 24);
  if (dias === 1) return "ayer";
  if (dias < 30) return "hace " + dias + " días";
  // Más atrás en el tiempo, la fecha con el formato del resto del sistema
  var dd = ("0" + f.getDate()).slice(-2);
  var mm = ("0" + (f.getMonth() + 1)).slice(-2);
  return dd + "/" + mm + "/" + f.getFullYear();
}

var TIPOS_MOVIMIENTO = {
  PRINCIPAL: ["badge-primary", "Derivado"],
  COPIA: ["badge-info", "Copia"],
  ATENCION: ["badge-warning", "Atención"],
};

/** Botones a lo que más se usa, según el rol. */
function accesosIndicadores(rol) {
  var accesos = rol === "admin"
    ? [
        ["tramite-nuevo", "fas fa-plus", "Registrar trámite"],
        ["movimientos", "fas fa-exchange-alt", "Movimientos"],
        ["rastreo", "fas fa-search", "Rastrear"],
        ["reportes/plazos", "fas fa-chart-bar", "Plazos y productividad"],
        ["empleados", "fas fa-users", "Personal"],
        ["configuracion", "fas fa-sliders-h", "Configuración"],
      ]
    : [
        ["tramite-nuevo", "fas fa-plus", "Registrar trámite"],
        ["recibidos", "fas fa-inbox", "Recibidos"],
        ["enviados", "fas fa-paper-plane", "Enviados"],
        ["rastreo", "fas fa-search", "Rastrear"],
        ["reportes/plazos", "fas fa-chart-bar", "Plazos"],
      ];
  return accesos.map(function (a) {
    return '<a class="acceso-rapido" href="#/' + a[0] + '"><i class="' + a[1] + '"></i> ' + a[2] + "</a>";
  }).join("");
}

function Pintar_Indicadores(r) {
  var caja = document.getElementById("panel_indicadores");
  if (!caja) return;
  var c = r.contadores;
  var a = r.atencion;
  var esAdmin = r.rol === "admin";

  var avisos = (r.avisos || []).map(function (av) {
    var clase = av.tipo === "peligro" ? "alert-danger" : "alert-warning";
    return '<div class="alert ' + clase + '"><i class="fas fa-exclamation-triangle"></i> ' + textoIndicador(av.texto) +
      ' <a href="#/feriados" class="alert-link">Ir a Feriados</a></div>';
  }).join("");

  var variacion = c.mes_anterior > 0
    ? Math.round(((c.registrados_mes - c.mes_anterior) / c.mes_anterior) * 100)
    : null;
  var detalleMes = variacion === null
    ? "sin datos del mes anterior"
    : (variacion >= 0 ? "+" : "") + variacion + "% respecto al mes anterior";

  var tarjetas =
    tarjetaIndicador("Vencidos", c.vencidos, "de " + c.en_curso + " en curso", "#B91C1C", "fas fa-exclamation-circle",
      esAdmin ? "movimientos" : "recibidos") +
    tarjetaIndicador("Por vencer", c.por_vencer, "vencen hoy o el próximo día hábil", "#B45309", "fas fa-hourglass-half",
      esAdmin ? "movimientos" : "recibidos") +
    tarjetaIndicador("En curso", c.en_curso, c.sin_plazo + " sin plazo definido", "#1E3A5F", "fas fa-folder-open",
      esAdmin ? "movimientos" : "recibidos") +
    tarjetaIndicador("Detenidos", c.detenidos, "sin movimiento en 10 días hábiles", "#7C2D12", "fas fa-pause-circle",
      esAdmin ? "movimientos" : "recibidos") +
    tarjetaIndicador(esAdmin ? "Recibidos este mes" : "Llegaron este mes", c.registrados_mes, detalleMes,
      "#2C5282", "fas fa-inbox", esAdmin ? "tramites" : "recibidos") +
    tarjetaIndicador(esAdmin ? "Recibidos hoy" : "Llegaron hoy", c.registrados_hoy, "", "#2C5282", "fas fa-calendar-day",
      esAdmin ? "tramites" : "recibidos") +
    (esAdmin
      ? tarjetaIndicador("Finalizados este mes", c.finalizados_mes, c.finalizados + " finalizados en total",
          "#15803D", "fas fa-flag-checkered", "movimientos")
      : tarjetaIndicador("Despachados este mes", c.despachados_mes === null ? "—" : c.despachados_mes,
          c.finalizados_mes + " finalizados este mes", "#15803D", "fas fa-paper-plane", "enviados")) +
    tarjetaIndicador("Atención (días hábiles)", a.promedio === null ? "—" : a.promedio,
      a.promedio === null ? "sin trámites cerrados" : "mediana " + a.mediana + " · máximo " + a.maximo + " · " + a.documentos + " trámites",
      "#1E3A5F", "fas fa-stopwatch", "");

  // Lo que requiere acción: lo que no tiene nada pendiente se muestra en gris
  var pendientes = (r.pendientes || []).map(function (p) {
    var clase = p.total === 0 ? "pendiente-cero" : (p.urgente ? "pendiente-urgente" : "pendiente-aviso");
    return '<a class="pendiente ' + clase + '" href="#/' + p.ruta + '">' +
      '<span class="pendiente-total">' + p.total + "</span>" +
      '<span class="pendiente-texto">' + textoIndicador(p.texto) + "</span>" +
      (p.total ? '<i class="fas fa-chevron-right"></i>' : "") +
    "</a>";
  }).join("");

  var filasAreas = (r.areas || []).map(function (x) {
    return "<tr>" +
      "<td>" + textoIndicador(x.area) + "</td>" +
      '<td class="centro">' + x.en_curso + "</td>" +
      '<td class="centro">' + (x.por_vencer ? '<span class="semaforo semaforo-ambar">' + x.por_vencer + "</span>" : "—") + "</td>" +
      '<td class="centro">' + (x.vencidos ? '<span class="semaforo semaforo-rojo">' + x.vencidos + "</span>" : "—") + "</td>" +
      '<td class="centro">' + (x.mas_atrasado ? x.mas_atrasado + " día(s)" : "—") + "</td>" +
    "</tr>";
  }).join("");

  var filasAtrasados = (r.mas_atrasados || []).map(function (x) {
    return "<tr>" +
      "<td><b>" + textoIndicador(x.expediente) + "</b><br><small>" + textoIndicador(x.asunto) + "</small></td>" +
      "<td>" + textoIndicador(x.area) + "</td>" +
      '<td class="centro"><span class="semaforo semaforo-rojo">' + x.dias + " día(s)</span><br><small>límite " + textoIndicador(x.limite) + "</small></td>" +
    "</tr>";
  }).join("");

  var filasDetenidos = (r.detenidos || []).map(function (x) {
    return "<tr>" +
      "<td><b>" + textoIndicador(x.expediente) + "</b><br><small>" + textoIndicador(x.asunto) + "</small></td>" +
      "<td>" + textoIndicador(x.area) + "</td>" +
      '<td class="centro">' + x.dias + " día(s)</td>" +
    "</tr>";
  }).join("");

  var filasActividad = (r.actividad || []).map(function (x) {
    var t = TIPOS_MOVIMIENTO[x.tipo] || ["badge-secondary", x.tipo];
    // Un envío de un área a sí misma es el ingreso del documento: mostrarlo como
    // "MESA DE PARTES → MESA DE PARTES" no decía nada. De quién viene lo dice la
    // procedencia: no siempre es un ciudadano, puede remitirlo un área.
    var mismaArea = x.origen && x.origen === x.destino;
    var origen = x.origen || "—";
    if (mismaArea) {
      origen = x.procedencia === "INTERNO"
        ? "Interno" + (x.area_procedencia ? " · " + x.area_procedencia : "")
        : "Externo · ciudadano";
      t = ["badge-secondary", "Recepción"];
    }
    return "<tr>" +
      "<td><b>" + textoIndicador(x.expediente || x.documento_id) + "</b><br>" +
        '<span class="badge ' + t[0] + '">' + t[1] + "</span></td>" +
      "<td>" + textoIndicador(origen) + ' <i class="fas fa-long-arrow-alt-right"></i> ' +
        textoIndicador(x.destino || "—") +
        (x.persona ? '<br><small class="text-muted">' + textoIndicador(x.persona) + "</small>" : "") + "</td>" +
      '<td class="centro"><small>' + cuandoIndicador(x.fecha) + "</small></td>" +
    "</tr>";
  }).join("");

  var maxMes = Math.max.apply(null, (r.por_mes || []).map(function (m) { return +m.total; }).concat([1]));
  var barras = (r.por_mes || []).map(function (m) {
    return '<div class="barra-mes" title="' + textoIndicador(m.mes + ": " + m.total + " trámites") + '">' +
      '<div class="barra" style="height:' + Math.max(6, Math.round((m.total / maxMes) * 100)) + '%;"><span>' + m.total + "</span></div>" +
      '<small>' + textoIndicador(m.mes) + "</small></div>";
  }).join("");

  var tipos = (r.por_tipo || []).map(function (t) {
    return "<li>" + textoIndicador(t.tipo) + " <b>" + t.total + "</b></li>";
  }).join("");

  // El administrador ve la carga de todas las áreas; un área, sus detenidos
  var bloqueIzquierdo = esAdmin
    ? '<h6 class="indicadores-titulo">Carga por área</h6>' +
      '<div class="table-responsive"><table class="table table-sm table-modern tabla-indicadores">' +
        "<thead><tr><th>Área</th><th>En curso</th><th>Por vencer</th><th>Vencidos</th><th>Atraso mayor</th></tr></thead>" +
        "<tbody>" + (filasAreas || '<tr><td colspan="5" class="centro">Sin trámites en curso.</td></tr>') + "</tbody>" +
      "</table></div>"
    : '<h6 class="indicadores-titulo">Trámites detenidos <small class="text-muted">(sin movimiento en 10 días hábiles)</small></h6>' +
      '<div class="table-responsive"><table class="table table-sm table-modern tabla-indicadores">' +
        "<thead><tr><th>Expediente</th><th>Área</th><th>Detenido</th></tr></thead>" +
        "<tbody>" + (filasDetenidos || '<tr><td colspan="3" class="centro">Ningún trámite detenido.</td></tr>') + "</tbody>" +
      "</table></div>";

  caja.innerHTML =
    '<div class="card card-modern">' +
      '<div class="card-header"><h5 class="m-0" style="text-align:center;"><i class="fas fa-chart-line"></i> <b>' +
        (esAdmin ? "TABLERO DE GESTIÓN" : "TABLERO DE " + textoIndicador(r.area || "MI ÁREA")) + "</b></h5></div>" +
      '<div class="card-body">' +
        avisos +
        '<div class="accesos-rapidos">' + accesosIndicadores(r.rol) + "</div>" +
        '<div class="row">' +
          '<div class="col-lg-8"><div class="indicadores">' + tarjetas + "</div></div>" +
          '<div class="col-lg-4">' +
            '<h6 class="indicadores-titulo">Requiere atención</h6>' +
            '<div class="pendientes">' + (pendientes || '<small class="text-muted">Nada pendiente.</small>') + "</div>" +
          "</div>" +
        "</div>" +
        '<div class="row mt-3">' +
          '<div class="col-lg-7">' + bloqueIzquierdo + "</div>" +
          '<div class="col-lg-5">' +
            '<h6 class="indicadores-titulo">Trámites más atrasados</h6>' +
            '<div class="table-responsive"><table class="table table-sm table-modern tabla-indicadores">' +
              "<thead><tr><th>Expediente</th><th>Área</th><th>Atraso</th></tr></thead>" +
              "<tbody>" + (filasAtrasados || '<tr><td colspan="3" class="centro">Ningún trámite vencido.</td></tr>') + "</tbody>" +
            "</table></div>" +
          "</div>" +
        "</div>" +
        '<div class="row">' +
          '<div class="col-lg-7">' +
            '<h6 class="indicadores-titulo">Movimientos recientes</h6>' +
            '<div class="table-responsive"><table class="table table-sm table-modern tabla-indicadores">' +
              "<thead><tr><th>Expediente</th><th>Recorrido</th><th>Cuándo</th></tr></thead>" +
              "<tbody>" + (filasActividad || '<tr><td colspan="3" class="centro">Sin movimientos registrados.</td></tr>') + "</tbody>" +
            "</table></div>" +
          "</div>" +
          '<div class="col-lg-5">' +
            '<h6 class="indicadores-titulo">' + (esAdmin ? "Trámites recibidos por mes" : "Llegadas a su área por mes") + "</h6>" +
            '<div class="barras">' + (barras || '<small class="text-muted">Sin registros en los últimos meses.</small>') + "</div>" +
            '<h6 class="indicadores-titulo mt-3">Tipos de documento más frecuentes (' + new Date().getFullYear() + ")</h6>" +
            '<ul class="lista-tipos">' + (tipos || "<li>Sin registros este año</li>") + "</ul>" +
          "</div>" +
        "</div>" +
      "</div>" +
    "</div>";
}

function Cargar_Indicadores() {
  if (!document.getElementById("panel_indicadores")) return;
  $.ajax({ url: "../controller/dashboard/controlador_indicadores.php", type: "POST", dataType: "json" })
    .done(Pintar_Indicadores)
    .fail(function () {
      document.getElementById("panel_indicadores").innerHTML =
        '<div class="alert alert-warning">No se pudieron cargar los indicadores del tablero.</div>';
    });
}

document.addEventListener("DOMContentLoaded", Cargar_Indicadores);
