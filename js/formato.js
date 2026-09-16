/**
 * Formatos compartidos por las pantallas del sistema.
 * Se carga una sola vez desde view/index.php, antes que los demás guiones.
 */

/**
 * Convierte "2026-06-16 12:00:49" en { fecha: "16/06/2026", hora: "12:00" }.
 * Se lee el texto tal cual llega de la base en vez de usar Date, que aplicaría
 * la zona horaria del navegador y podría correr la fecha un día.
 */
function formatoFechaHora(valor) {
  if (!valor) return null;
  var partes = String(valor).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
  if (!partes) return null;
  return {
    fecha: partes[3] + "/" + partes[2] + "/" + partes[1],
    hora: partes[4] + ":" + partes[5],
  };
}

/**
 * Columna "N° Expediente": el código no debe partirse en dos líneas
 * ("EXP-2026-" arriba y "000006" abajo se lee como dos datos distintos).
 */
function Render_Expediente(data, type) {
  if (type !== "display") return data || "";
  if (!data) return '<span class="text-muted">—</span>';
  return '<span style="white-space:nowrap;">' + escaparTextoFormato(data) + "</span>";
}

function escaparTextoFormato(valor) {
  return String(valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;");
}

/**
 * Columna "Fecha de Registro" de las tablas de trámites (DataTables).
 * Fecha y hora van en dos líneas para no ensanchar una tabla que ya es ancha;
 * la búsqueda funciona escribiendo la fecha como se ve: 16/06/2026.
 */
function Render_Fecha_Registro(data, type) {
  var f = formatoFechaHora(data);
  if (!f) {
    return type === "display" ? '<span class="text-muted">—</span>' : "";
  }
  if (type === "display") {
    return (
      '<span style="white-space:nowrap;">' + f.fecha + "</span>" +
      '<small class="d-block text-muted">' + f.hora + "</small>"
    );
  }
  if (type === "filter") {
    return f.fecha + " " + f.hora;
  }
  return data;
}
