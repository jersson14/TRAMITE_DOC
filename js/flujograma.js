/**
 * Diagrama de flujo del recorrido de un trámite.
 *
 * El historial en tarjetas apiladas obliga a leerlo entero para entender por
 * dónde pasó un expediente. Aquí el recorrido se dibuja: una columna con los
 * envíos que trasladaron la responsabilidad (los PRINCIPAL) y, a la derecha de
 * cada uno, las copias y las atenciones que salieron en ese mismo paso.
 *
 * Los datos vienen de controller/tramite/controlador_flujo.php por nombre de
 * campo, y los plazos ya calculados en días hábiles por lib/Plazos.php.
 */
var FLUJO_ESTADOS = {
  PENDIENTE:  ["flujo-pendiente", "fas fa-clock", "Pendiente"],
  ACEPTADO:   ["flujo-aceptado", "fas fa-check", "Aceptado"],
  DERIVADO:   ["flujo-aceptado", "fas fa-arrow-right", "Derivado"],
  FINALIZADO: ["flujo-finalizado", "fas fa-flag-checkered", "Finalizado"],
  ATENDIDO:   ["flujo-finalizado", "fas fa-check-double", "Atendido"],
  RECHAZADO:  ["flujo-rechazado", "fas fa-times-circle", "Rechazado"],
};

function textoFlujo(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

function estiloFlujo(estado) {
  return FLUJO_ESTADOS[String(estado || "").toUpperCase()] || ["flujo-pendiente", "fas fa-circle", estado || "—"];
}

/** Caja de un envío principal: el área que se hizo cargo del trámite. */
function cajaFlujo(m, numero) {
  var e = estiloFlujo(m.estado);
  var acuse = m.recibido
    ? '<span class="flujo-acuse flujo-acuse-si"><i class="fas fa-inbox"></i> Acuse ' + textoFlujo(m.recibido) + "</span>"
    : '<span class="flujo-acuse flujo-acuse-no"><i class="far fa-clock"></i> Sin acuse de recepción</span>';

  return '<div class="flujo-caja ' + e[0] + '">' +
      '<div class="flujo-caja-alto">' +
        '<span class="flujo-paso">Paso ' + numero + "</span>" +
        '<span class="flujo-estado"><i class="' + e[1] + '"></i> ' + textoFlujo(e[2]) + "</span>" +
      "</div>" +
      '<strong class="flujo-area">' + textoFlujo(m.destino || "—") + "</strong>" +
      '<div class="flujo-datos">' +
        '<span><i class="fas fa-sign-out-alt"></i> De ' + textoFlujo(m.origen) + "</span>" +
        '<span><i class="far fa-calendar-alt"></i> ' + textoFlujo(m.fecha) + "</span>" +
        (m.persona ? '<span><i class="far fa-user"></i> ' + textoFlujo(m.persona) + "</span>" : "") +
      "</div>" +
      acuse +
      (m.descripcion ? '<div class="flujo-indicacion">' + textoFlujo(m.descripcion) + "</div>" : "") +
    "</div>";
}

/** Caja lateral: copia (solo conocimiento) o atención (debe responder). */
function ramaFlujo(m) {
  var esAtencion = String(m.tipo).toUpperCase() === "ATENCION";
  var clase = esAtencion ? "flujo-rama-atencion" : "flujo-rama-copia";
  var titulo = esAtencion ? "Para atención" : "Copia";
  var cierre = esAtencion
    ? (m.respondido
        ? '<span class="flujo-acuse flujo-acuse-si"><i class="fas fa-reply"></i> Respondió ' + textoFlujo(m.respondido) + "</span>"
        : '<span class="flujo-acuse flujo-acuse-no"><i class="far fa-clock"></i> Sin responder</span>')
    : (m.recibido
        ? '<span class="flujo-acuse flujo-acuse-si"><i class="fas fa-eye"></i> Vista ' + textoFlujo(m.recibido) + "</span>"
        : '<span class="flujo-acuse flujo-acuse-no"><i class="far fa-clock"></i> Sin confirmar</span>');

  return '<div class="flujo-rama ' + clase + '">' +
      '<span class="flujo-rama-titulo">' + titulo +
        (esAtencion && m.plazo ? " · " + m.plazo + " día(s)" : "") + "</span>" +
      '<strong>' + textoFlujo(m.destino || "—") + "</strong>" +
      '<div class="flujo-datos"><span><i class="far fa-calendar-alt"></i> ' + textoFlujo(m.fecha) + "</span></div>" +
      cierre +
      (esAtencion && m.respuesta ? '<div class="flujo-indicacion">' + textoFlujo(m.respuesta) + "</div>" : "") +
    "</div>";
}

/** Une los movimientos en pasos: cada PRINCIPAL con sus copias y atenciones. */
function pasosFlujo(movimientos) {
  var pasos = [];
  var sueltas = [];
  (movimientos || []).forEach(function (m) {
    if (String(m.tipo).toUpperCase() === "PRINCIPAL") {
      pasos.push({ principal: m, ramas: [] });
    } else if (pasos.length) {
      pasos[pasos.length - 1].ramas.push(m);
    } else {
      sueltas.push(m);
    }
  });
  if (sueltas.length) {
    pasos.unshift({ principal: null, ramas: sueltas });
  }
  return pasos;
}

function Flujograma_Pintar(r, selector) {
  var caja = document.querySelector(selector);
  if (!caja) return;
  if (!r || !r.encontrado) {
    caja.innerHTML = '<div class="alert alert-warning">No se encontró el recorrido de ese trámite.</div>';
    return;
  }

  var t = r.tramite;
  var pasos = pasosFlujo(r.movimientos);
  var cerrado = ["FINALIZADO", "RECHAZADO"].indexOf(String(t.estado).toUpperCase()) >= 0;

  /*
   * El recorrido empieza fuera de la institución: el documento lo presenta una
   * persona, por el portal o en el mostrador, y recién ahí un área lo recibe.
   * Cuando el primer envío va de un área a sí misma, ese movimiento ES la
   * recepción —no una derivación—, así que se dibuja como tal y no aparece
   * repetido ("MESA DE PARTES → MESA DE PARTES").
   */
  var recepcion = null;
  if (pasos.length && pasos[0].principal && Number(pasos[0].principal.es_recepcion) === 1) {
    recepcion = pasos.shift();
  }
  var areaRecepcion = (recepcion && recepcion.principal.destino) || t.area_recepcion;
  var esPortal = t.procedencia === "PORTAL";

  // 1. Quién presentó el documento
  var html = '<div class="flujograma">' +
    '<div class="flujo-hito flujo-externo">' +
      '<span class="flujo-hito-rotulo"><i class="fas fa-user-tie"></i> Externo · ciudadano</span>' +
      "<strong>" + textoFlujo(t.remitente || "Remitente no registrado") + "</strong>" +
      '<div class="flujo-datos">' +
        (t.dni ? '<span><i class="far fa-id-card"></i> DNI ' + textoFlujo(t.dni) + "</span>" : "") +
        (t.tipo ? '<span><i class="far fa-file-alt"></i> ' + textoFlujo(t.tipo) + "</span>" : "") +
        (t.folios ? '<span><i class="fas fa-layer-group"></i> ' + textoFlujo(t.folios) + " folio(s)</span>" : "") +
      "</div>" +
    "</div>";

  // 2. El área que lo recibió y lo ingresó al sistema
  html += '<div class="flujo-flecha"><span>' +
      (esPortal ? "presenta por el portal" : "presenta en mesa de partes") + "</span></div>" +
    '<div class="flujo-fila">' +
      '<div class="flujo-hito flujo-inicio">' +
        '<span class="flujo-hito-rotulo"><i class="fas fa-file-import"></i> Recepción' +
          (esPortal ? " · Mesa de Partes Virtual" : "") + "</span>" +
        "<strong>" + textoFlujo(areaRecepcion) + "</strong>" +
        '<div class="flujo-datos">' +
          '<span><i class="far fa-calendar-alt"></i> ' +
            textoFlujo((recepcion && recepcion.principal.fecha) || t.fecha_presentado || t.fecha_registro) + "</span>" +
          '<span><i class="fas fa-hashtag"></i> ' + textoFlujo(t.expediente) + "</span>" +
        "</div>" +
        // Aunque el área lo haya registrado, el sistema pide que lo acepte en su bandeja
        (recepcion
          ? (recepcion.principal.recibido
              ? '<span class="flujo-acuse flujo-acuse-si"><i class="fas fa-inbox"></i> Recibido ' +
                  textoFlujo(recepcion.principal.recibido) + "</span>"
              : (String(recepcion.principal.estado).toUpperCase() === "PENDIENTE"
                  ? '<span class="flujo-acuse flujo-acuse-no"><i class="far fa-clock"></i> Pendiente de aceptar en la bandeja</span>'
                  : ""))
          : "") +
      "</div>";
  // Las copias y atenciones que salieron con la recepción van a su costado
  if (recepcion && recepcion.ramas.length) {
    html += '<div class="flujo-conector"></div><div class="flujo-ramas">' +
      recepcion.ramas.map(ramaFlujo).join("") + "</div>";
  }
  html += "</div>";

  // 3. Las derivaciones entre áreas
  pasos.forEach(function (paso, i) {
    html += '<div class="flujo-flecha"><span>' +
      (paso.principal ? "derivado a" : "en paralelo") + "</span></div>";
    html += '<div class="flujo-fila">';
    html += paso.principal
      ? cajaFlujo(paso.principal, i + 1)
      : '<div class="flujo-caja flujo-pendiente"><strong class="flujo-area">Sin envío principal</strong></div>';
    if (paso.ramas.length) {
      html += '<div class="flujo-conector"></div><div class="flujo-ramas">' +
        paso.ramas.map(ramaFlujo).join("") + "</div>";
    }
    html += "</div>";
  });

  // Cierre: estado en que quedó el trámite
  var eFinal = estiloFlujo(t.estado);
  var plazo = "";
  if (!cerrado && t.plazo_limite) {
    var restante = parseInt(t.plazo_restante, 10);
    plazo = isNaN(restante)
      ? ""
      : (restante < 0
          ? '<span class="flujo-vencido">Vencido hace ' + Math.abs(restante) + " día(s) hábiles</span>"
          : '<span class="flujo-a-tiempo">Quedan ' + restante + " día(s) hábiles</span>") +
        '<span><i class="far fa-calendar-check"></i> Límite ' + textoFlujo(t.plazo_limite) + "</span>";
  }

  html += '<div class="flujo-flecha"><span>estado</span></div>' +
    '<div class="flujo-hito flujo-fin ' + eFinal[0] + '">' +
      '<span class="flujo-hito-rotulo"><i class="' + eFinal[1] + '"></i> ' +
        (cerrado ? textoFlujo(eFinal[2]) : "En trámite") + "</span>" +
      "<strong>" + textoFlujo(cerrado ? "Expediente cerrado" : (t.area_actual || "—")) + "</strong>" +
      (plazo ? '<div class="flujo-datos">' + plazo + "</div>"
             : (cerrado ? "" : '<div class="flujo-datos"><span>Sin plazo de respuesta definido</span></div>')) +
    "</div>" +
  "</div>";

  caja.innerHTML = html;
}

/** Pide el recorrido y lo dibuja. */
function Flujograma_Cargar(documento, selector) {
  var caja = document.querySelector(selector || "#div_flujo");
  if (!caja) return;
  caja.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Armando el diagrama...</div>';
  $.ajax({
    url: "../controller/tramite/controlador_flujo.php",
    type: "POST",
    dataType: "json",
    data: { documento: documento },
  }).done(function (r) {
    Flujograma_Pintar(r, selector || "#div_flujo");
  }).fail(function (x) {
    caja.innerHTML = '<div class="alert alert-warning">' +
      textoFlujo((x.responseJSON && x.responseJSON.mensaje) || "No se pudo armar el diagrama.") + "</div>";
  });
}

/** Alterna entre el diagrama y el detalle de movimientos. */
function Flujo_Vista(cual) {
  var diagrama = document.getElementById("div_flujo");
  var detalle = document.getElementById("div_seguimiento");
  if (!diagrama || !detalle) return;
  var esDiagrama = cual !== "detalle";
  diagrama.hidden = !esDiagrama;
  detalle.hidden = esDiagrama;
  var botones = document.querySelectorAll("#flujo_vistas .btn");
  for (var i = 0; i < botones.length; i++) {
    botones[i].classList.toggle("activo", botones[i].dataset.vista === (esDiagrama ? "diagrama" : "detalle"));
  }
}
