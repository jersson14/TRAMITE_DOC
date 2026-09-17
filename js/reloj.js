/**
 * Reloj de la barra superior, en hora de Perú.
 *
 * La hora inicial la escribe el servidor en el atributo data-ahora (zona
 * América/Lima); aquí solo se guarda la diferencia con el reloj de esta
 * computadora y se avanza segundo a segundo. Así la hora que se ve es la del
 * sistema —la que decide si un documento entró dentro del horario de
 * recepción— y no la que tenga configurada cada máquina.
 */
var RELOJ_DIAS = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];
var RELOJ_MESES = ["enero", "febrero", "marzo", "abril", "mayo", "junio",
  "julio", "agosto", "setiembre", "octubre", "noviembre", "diciembre"];

/** Solo el día de la semana va con mayúscula inicial, no cada palabra. */
function conMayuscula(texto) {
  return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function Iniciar_Reloj() {
  var caja = document.getElementById("reloj_peru");
  if (!caja) return;

  var partes = String(caja.getAttribute("data-ahora") || "")
    .match(/(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2}):(\d{2})/);
  var desfase = 0;
  if (partes) {
    var servidor = new Date(+partes[1], +partes[2] - 1, +partes[3], +partes[4], +partes[5], +partes[6]);
    desfase = servidor.getTime() - Date.now();
  }

  var dos = function (n) { return ("0" + n).slice(-2); };
  var latir = function () {
    var f = new Date(Date.now() + desfase);
    var horas = f.getHours();
    caja.innerHTML =
      '<i class="far fa-clock"></i>' +
      '<span class="reloj-textos">' +
        '<span class="reloj-hora">' + dos(horas % 12 === 0 ? 12 : horas % 12) + ":" + dos(f.getMinutes()) +
          ":" + dos(f.getSeconds()) + " " + (horas < 12 ? "a.m." : "p.m.") + "</span>" +
        '<span class="reloj-fecha">' + conMayuscula(RELOJ_DIAS[f.getDay()]) + " " + f.getDate() + " de " +
          RELOJ_MESES[f.getMonth()] + " de " + f.getFullYear() + "</span>" +
      "</span>";
  };

  latir();
  setInterval(latir, 1000);
}

document.addEventListener("DOMContentLoaded", Iniciar_Reloj);
