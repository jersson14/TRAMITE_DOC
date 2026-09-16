/**
 * Anexos del trámite.
 *
 * Los formularios de datos del expediente muestran el documento principal; esta
 * función agrega debajo la lista de archivos adicionales. Si la vista no tiene
 * el contenedor #lista_anexos, no hace nada, así puede llamarse desde cualquier
 * pantalla sin romper las que todavía no lo muestran.
 */
function escaparTexto(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function tamanoLegible(bytes) {
  var n = parseInt(bytes, 10) || 0;
  if (n < 1024) return n + " B";
  if (n < 1048576) return (n / 1024).toFixed(0) + " KB";
  return (n / 1048576).toFixed(1) + " MB";
}

function Cargar_Anexos(documentoId) {
  var caja = document.getElementById("lista_anexos");
  if (!caja) return;

  if (!documentoId) {
    caja.innerHTML = "";
    return;
  }

  caja.innerHTML = '<small class="text-muted">Cargando anexos...</small>';

  $.ajax({
    url: "../controller/tramite/controlador_listar_anexos.php",
    type: "POST",
    data: { id: documentoId },
    dataType: "json",
  })
    .done(function (respuesta) {
      var filas = (respuesta && respuesta.data) || [];
      if (filas.length === 0) {
        caja.innerHTML =
          '<small class="text-muted"><i class="fas fa-paperclip"></i> Este trámite no tiene anexos.</small>';
        return;
      }

      var html =
        '<div class="list-group">';
      for (var i = 0; i < filas.length; i++) {
        var a = filas[i];
        html +=
          '<a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" ' +
          'href="../' + escaparTexto(a.anexo_ruta) + '" target="_blank" rel="noopener">' +
          '<span><i class="fas fa-file-pdf text-danger mr-2"></i>' +
          escaparTexto(a.anexo_nombre) +
          '</span>' +
          '<small class="text-muted">' +
          tamanoLegible(a.anexo_bytes) +
          (a.anexo_fecha_texto ? " &middot; " + escaparTexto(a.anexo_fecha_texto) : "") +
          "</small></a>";
      }
      html += "</div>";
      caja.innerHTML = html;
    })
    .fail(function () {
      caja.innerHTML =
        '<small class="text-danger">No se pudieron cargar los anexos.</small>';
    });
}
