/**
 * Comunicados como alerta dentro del sistema.
 *
 * Al entrar, se muestran uno por uno los comunicados vigentes dirigidos a esa
 * persona que todavía no confirmó leer. Antes los comunicados solo vivían en una
 * tabla del tablero y en un menú que casi siempre estaba vacío, así que nadie
 * los veía. «Entendido» deja constancia de la lectura y no vuelve a aparecer;
 * cerrando la ventana, el comunicado se vuelve a mostrar en el próximo ingreso.
 */
var COMUNICADOS_PENDIENTES = [];
var COMUNICADO_ACTUAL = 0;

function textoComunicado(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

var DESTINOS_COMUNICADO = {
  TODOS: "Todo el personal",
  ADMINISTRADORES: "Administradores",
  SECRETARIAS: "Personal de áreas",
  AREAS: "Áreas seleccionadas",
};

function Mostrar_Comunicado() {
  if (COMUNICADO_ACTUAL >= COMUNICADOS_PENDIENTES.length) {
    $("#modal_comunicado_alerta").modal("hide");
    if (typeof Cargar_Notificaciones === "function") Cargar_Notificaciones();
    return;
  }
  var c = COMUNICADOS_PENDIENTES[COMUNICADO_ACTUAL];
  var total = COMUNICADOS_PENDIENTES.length;

  document.getElementById("comunicado_contador").textContent =
    total > 1 ? "Comunicado " + (COMUNICADO_ACTUAL + 1) + " de " + total : "Comunicado";
  document.getElementById("comunicado_titulo").textContent = c.titulo;
  // El salto de línea del texto escrito por el administrador se conserva
  document.getElementById("comunicado_texto").innerHTML =
    textoComunicado(c.descripcion).replace(/\n/g, "<br>");
  document.getElementById("comunicado_fecha").textContent = c.fecha;
  document.getElementById("comunicado_destino").textContent = DESTINOS_COMUNICADO[c.com_destino] || c.com_destino;
  document.getElementById("comunicado_vigencia").textContent = c.hasta ? "Vigente hasta el " + c.hasta : "";

  // La imagen, si la tiene, se muestra sobre el texto y se puede abrir en grande
  var caja = document.getElementById("comunicado_imagen_enlace");
  if (c.com_imagen) {
    caja.href = "../" + c.com_imagen;
    document.getElementById("comunicado_imagen").src = "../" + c.com_imagen;
    caja.hidden = false;
  } else {
    caja.hidden = true;
    document.getElementById("comunicado_imagen").removeAttribute("src");
  }

  var enlace = document.getElementById("comunicado_enlace");
  if (c.enlace) {
    enlace.href = c.enlace;
    enlace.hidden = false;
  } else {
    enlace.hidden = true;
  }

  document.getElementById("comunicado_boton").innerHTML = COMUNICADO_ACTUAL + 1 < total
    ? '<i class="fas fa-check"></i> Entendido, siguiente'
    : '<i class="fas fa-check"></i> Entendido';

  $("#modal_comunicado_alerta").modal({ backdrop: "static", keyboard: true, show: true });
}

function Confirmar_Comunicado() {
  var c = COMUNICADOS_PENDIENTES[COMUNICADO_ACTUAL];
  if (!c) return;
  var boton = $("#comunicado_boton");
  boton.prop("disabled", true);
  $.ajax({
    url: "../controller/comunicados/controlador_marcar_leido.php",
    type: "POST",
    dataType: "json",
    data: { id: c.id_comunicado },
  }).always(function () {
    boton.prop("disabled", false);
    COMUNICADO_ACTUAL++;
    Mostrar_Comunicado();
  });
}

function Cargar_Comunicados_Pendientes() {
  if (!document.getElementById("modal_comunicado_alerta")) return;
  $.ajax({ url: "../controller/comunicados/controlador_pendientes.php", type: "POST", dataType: "json" })
    .done(function (r) {
      COMUNICADOS_PENDIENTES = r.items || [];
      COMUNICADO_ACTUAL = 0;
      if (COMUNICADOS_PENDIENTES.length) Mostrar_Comunicado();
    });
}

document.addEventListener("DOMContentLoaded", function () {
  // Un momento después del ingreso, para no tapar la carga del tablero
  setTimeout(Cargar_Comunicados_Pendientes, 1200);
});
