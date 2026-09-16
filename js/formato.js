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
function Render_Expediente(data, type, row) {
  if (type !== "display") return data || "";
  var html = data
    ? '<span style="white-space:nowrap;">' + escaparTextoFormato(data) + "</span>"
    : '<span class="text-muted">—</span>';
  // En "Recibidos", un trámite que llegó en copia se marca aquí, a la vista:
  // la columna de acciones se oculta en pantallas medianas.
  if (row && row.es_copia == 1) {
    html += row.acuse_fecha
      ? '<span class="badge badge-copia d-block mt-1" title="Copia recibida el ' + escaparTextoFormato(row.acuse_fecha) + '"><i class="fas fa-check"></i> Copia recibida</span>'
      : '<span class="badge badge-copia d-block mt-1" title="Recibido en copia: falta confirmar la recepción"><i class="fas fa-copy"></i> Copia · por confirmar</span>';
  }
  return html;
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

/**
 * Columna "Días en el área": días hábiles desde que el trámite llegó al área que
 * lo tiene (lo calcula lib/Plazos.php en el servidor).
 */
function Render_Dias_Area(data, type, row) {
  var dias = row ? row.plazo_dias_area : null;
  if (type !== "display") return dias === null || dias === undefined ? -1 : dias;
  if (dias === null || dias === undefined) return '<span class="text-muted">—</span>';
  return '<span style="white-space:nowrap;">' + dias + (dias === 1 ? " día" : " días") + "</span>" +
    '<small class="d-block text-muted">hábiles</small>';
}

/**
 * Columna "Plazo": semáforo según el plazo de respuesta de cada trámite.
 *   verde = en plazo · ámbar = vence hoy o el próximo día hábil · rojo = vencido
 */
function Render_Plazo(data, type, row) {
  var semaforo = row ? row.plazo_semaforo : null;
  if (type !== "display") return row && row.plazo_restante !== null && row.plazo_restante !== undefined ? row.plazo_restante : 9999;

  if (semaforo === "CERRADO") return '<span class="text-muted">—</span>';
  if (semaforo === "SIN_PLAZO" || !semaforo) {
    return '<span class="semaforo semaforo-gris" title="No se indicó plazo de respuesta">Sin plazo</span>';
  }

  var r = row.plazo_restante;
  var detalle;
  if (semaforo === "ROJO") {
    detalle = "Vencido hace " + Math.abs(r) + (Math.abs(r) === 1 ? " día" : " días");
  } else if (r === 0) {
    detalle = "Vence hoy";
  } else {
    detalle = "Quedan " + r + (r === 1 ? " día" : " días");
  }
  var clase = { VERDE: "semaforo-verde", AMBAR: "semaforo-ambar", ROJO: "semaforo-rojo" }[semaforo] || "semaforo-gris";
  return '<span class="semaforo ' + clase + '" title="Plazo: ' + escaparTextoFormato(String(data)) +
    ' días hábiles · límite ' + escaparTextoFormato(row.plazo_limite) + '">' + detalle + "</span>" +
    '<small class="d-block text-muted" style="white-space:nowrap;">límite ' + escaparTextoFormato(row.plazo_limite) + "</small>";
}
