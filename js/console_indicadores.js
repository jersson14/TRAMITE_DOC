/**
 * Indicadores de gestión del tablero del administrador.
 *
 * Los plazos vienen calculados en días hábiles por lib/Plazos.php (el mismo
 * cálculo del semáforo de las bandejas), así que las cifras coinciden.
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

function Pintar_Indicadores(r) {
  var caja = document.getElementById("panel_indicadores");
  if (!caja) return;
  var c = r.contadores;
  var a = r.atencion;

  var avisos = (r.avisos || []).map(function (av) {
    var clase = av.tipo === "peligro" ? "alert-danger" : "alert-warning";
    return '<div class="alert ' + clase + '"><i class="fas fa-exclamation-triangle"></i> ' + textoIndicador(av.texto) +
      ' <a href="#/feriados" class="alert-link">Ir a Feriados</a></div>';
  }).join("");

  var variacion = c.mes_anterior > 0
    ? Math.round(((c.registrados_mes - c.mes_anterior) / c.mes_anterior) * 100)
    : null;

  var tarjetas =
    tarjetaIndicador("Vencidos", c.vencidos, "de " + c.en_curso + " en curso", "#B91C1C", "fas fa-exclamation-circle", "movimientos") +
    tarjetaIndicador("Por vencer", c.por_vencer, "vencen hoy o el próximo día hábil", "#B45309", "fas fa-hourglass-half", "movimientos") +
    tarjetaIndicador("En curso", c.en_curso, c.sin_plazo + " sin plazo definido", "#1E3A5F", "fas fa-folder-open", "movimientos") +
    tarjetaIndicador("Recibidos este mes", c.registrados_mes,
      (variacion === null ? "sin datos del mes anterior" : (variacion >= 0 ? "+" : "") + variacion + "% respecto al mes anterior"),
      "#2C5282", "fas fa-inbox", "tramites") +
    tarjetaIndicador("Recibidos hoy", c.registrados_hoy, "", "#2C5282", "fas fa-calendar-day", "tramites") +
    tarjetaIndicador("Finalizados este mes", c.finalizados_mes, c.finalizados + " finalizados en total", "#15803D", "fas fa-flag-checkered", "movimientos") +
    tarjetaIndicador("Atención (días hábiles)", a.promedio === null ? "—" : a.promedio,
      a.promedio === null ? "sin trámites cerrados" : "mediana " + a.mediana + " · máximo " + a.maximo + " · " + a.documentos + " trámites",
      "#1E3A5F", "fas fa-stopwatch", "");

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

  var maxMes = Math.max.apply(null, (r.por_mes || []).map(function (m) { return +m.total; }).concat([1]));
  var barras = (r.por_mes || []).map(function (m) {
    return '<div class="barra-mes" title="' + textoIndicador(m.mes + ": " + m.total + " trámites") + '">' +
      '<div class="barra" style="height:' + Math.max(6, Math.round((m.total / maxMes) * 100)) + '%;"><span>' + m.total + "</span></div>" +
      '<small>' + textoIndicador(m.mes) + "</small></div>";
  }).join("");

  var tipos = (r.por_tipo || []).map(function (t) {
    return "<li>" + textoIndicador(t.tipo) + " <b>" + t.total + "</b></li>";
  }).join("");

  caja.innerHTML =
    '<div class="card card-modern">' +
      '<div class="card-header"><h5 class="m-0" style="text-align:center;"><i class="fas fa-chart-line"></i> <b>INDICADORES DE GESTIÓN</b></h5></div>' +
      '<div class="card-body">' +
        avisos +
        '<div class="indicadores">' + tarjetas + "</div>" +
        '<div class="row mt-3">' +
          '<div class="col-lg-7">' +
            '<h6 class="indicadores-titulo">Carga por área</h6>' +
            '<div class="table-responsive"><table class="table table-sm table-modern tabla-indicadores">' +
              "<thead><tr><th>Área</th><th>En curso</th><th>Por vencer</th><th>Vencidos</th><th>Atraso mayor</th></tr></thead>" +
              "<tbody>" + (filasAreas || '<tr><td colspan="5" class="centro">Sin trámites en curso.</td></tr>') + "</tbody>" +
            "</table></div>" +
          "</div>" +
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
            '<h6 class="indicadores-titulo">Trámites recibidos por mes</h6>' +
            '<div class="barras">' + (barras || '<small class="text-muted">Sin registros en los últimos meses.</small>') + "</div>" +
          "</div>" +
          '<div class="col-lg-5">' +
            '<h6 class="indicadores-titulo">Tipos de documento más frecuentes (' + new Date().getFullYear() + ")</h6>" +
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
        '<div class="alert alert-warning">No se pudieron cargar los indicadores.</div>';
    });
}

document.addEventListener("DOMContentLoaded", Cargar_Indicadores);
