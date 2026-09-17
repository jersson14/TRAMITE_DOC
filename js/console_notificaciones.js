/**
 * Notificaciones de la barra superior: comunicados y pendientes por atender.
 *
 * Se refrescan cada minuto y después de cada acción sobre un trámite (aceptar,
 * derivar, acuse, responder una atención), así el contador no queda viejo.
 * Los textos se escapan: el título de un comunicado o el asunto de un trámite
 * los escribe una persona y antes se insertaban tal cual en el HTML.
 */
function textoNotificacion(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

/** Pone el número en la insignia y la oculta cuando no hay nada. */
function insigniaNotificacion(id, total) {
  var el = document.getElementById(id);
  if (!el) return;
  el.textContent = total > 99 ? "99+" : total;
  el.style.display = total > 0 ? "" : "none";
}

function itemNotificacion(item, color, ruta) {
  return '<a class="notificacion" href="' + (ruta ? "#/" + ruta : "#") + '">' +
      '<span class="notificacion-marca" style="background:' + color + ';"></span>' +
      '<span class="notificacion-cuerpo">' +
        '<b>' + textoNotificacion(item.titulo) + "</b>" +
        (item.detalle ? '<span class="notificacion-detalle">' + textoNotificacion(item.detalle) + "</span>" : "") +
        (item.pie ? '<span class="notificacion-pie">' + textoNotificacion(item.pie) + "</span>" : "") +
      "</span>" +
    "</a>";
}

function Pintar_Notificaciones(r) {
  // --- Comunicados ---
  insigniaNotificacion("lbl_contador", r.comunicados.total);
  var caja = document.getElementById("div_cuerpo");
  if (caja) {
    caja.innerHTML = r.comunicados.items.length
      ? r.comunicados.items.map(function (c) {
          var enlace = c.enlace ? '<a href="' + textoNotificacion(c.enlace) + '" target="_blank" rel="noopener">Ver la noticia completa</a>' : "";
          return '<div class="notificacion">' +
              '<span class="notificacion-marca" style="background:#2C5282;"></span>' +
              '<span class="notificacion-cuerpo">' +
                "<b>" + textoNotificacion(c.titulo) + "</b>" +
                '<span class="notificacion-detalle">' + textoNotificacion(c.descripcion) + "</span>" +
                '<span class="notificacion-pie">' + textoNotificacion(c.fecha) + (enlace ? " · " + enlace : "") + "</span>" +
              "</span>" +
            "</div>";
        }).join("")
      : '<div class="notificacion-vacio"><i class="far fa-check-circle"></i> No hay comunicados nuevos.</div>';
  }

  // --- Pendientes por atender ---
  insigniaNotificacion("lbl_contador_pendientes", r.por_atender);
  var cajaPendientes = document.getElementById("div_cuerpo_tramite");
  if (!cajaPendientes) return;

  var conDatos = (r.grupos || []).filter(function (g) { return g.total > 0; });
  if (!conDatos.length) {
    cajaPendientes.innerHTML = '<div class="notificacion-vacio"><i class="far fa-check-circle"></i> Todo al día: no hay nada pendiente.</div>';
    return;
  }

  cajaPendientes.innerHTML = conDatos.map(function (g) {
    var resto = g.total - g.items.length;
    return '<div class="notificacion-grupo">' +
        '<div class="notificacion-titulo" style="color:' + g.color + ';">' +
          '<i class="' + g.icono + '"></i> ' + textoNotificacion(g.titulo) +
          '<span class="notificacion-cuenta" style="background:' + g.color + ';">' + g.total + "</span>" +
        "</div>" +
        g.items.map(function (i) { return itemNotificacion(i, g.color, g.ruta); }).join("") +
        (resto > 0 ? '<a class="notificacion-mas" href="#/' + g.ruta + '">y ' + resto + " más…</a>" : "") +
      "</div>";
  }).join("");
}

function Cargar_Notificaciones() {
  if (!document.getElementById("lbl_contador") && !document.getElementById("lbl_contador_pendientes")) return;
  $.ajax({ url: "../controller/usuario/controlador_notificaciones.php", type: "POST", dataType: "json" })
    .done(Pintar_Notificaciones);
}

document.addEventListener("DOMContentLoaded", function () {
  Cargar_Notificaciones();
  // Un minuto es suficiente para que el contador se sienta vivo sin cargar el servidor
  setInterval(Cargar_Notificaciones, 60000);
});

// Cualquier acción sobre un trámite cambia lo pendiente: se refresca al terminar
$(document).ajaxSuccess(function (evento, xhr, opciones) {
  var url = (opciones && opciones.url) || "";
  if (/controlador_(modificar_tramite_estatus|registro_tramite|acuse_copia|responder_atencion|rechazar_tramite|registro_comunicados|modificar_comunicados|eliminar_comunicados)/.test(url)) {
    setTimeout(Cargar_Notificaciones, 500);
  }
});
