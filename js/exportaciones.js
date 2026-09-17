/**
 * Botones de exportación de los reportes (PDF, Excel y CSV).
 *
 * El archivo se arma en el servidor (lib/Exportador.php) con los filtros
 * aplicados y todos los resultados. Los botones de DataTables exportaban solo
 * lo que estaba en pantalla, sin el nombre de la institución ni los filtros, y
 * arrastraban la columna de botones «Ver».
 *
 * Uso en una vista:
 *   Exportaciones_Montar('#exportar_plazos', 'plazos', function () {
 *     return { desde: ..., hasta: ..., area: ... };   // null si falta algo
 *   });
 */
function Exportaciones_Montar(selector, reporte, obtenerFiltros) {
  var caja = document.querySelector(selector);
  if (!caja) return;

  caja.innerHTML =
    '<span class="exportaciones-rotulo"><i class="fas fa-download"></i> Exportar:</span>' +
    '<button type="button" class="btn btn-exportar pdf" data-formato="pdf"><i class="fas fa-file-pdf"></i> PDF</button>' +
    '<button type="button" class="btn btn-exportar excel" data-formato="excel"><i class="fas fa-file-excel"></i> Excel</button>' +
    '<button type="button" class="btn btn-exportar csv" data-formato="csv"><i class="fas fa-file-csv"></i> CSV</button>';

  $(caja).off("click", ".btn-exportar").on("click", ".btn-exportar", function () {
    var filtros = obtenerFiltros();
    if (!filtros) return;

    // Una descarga no se puede pedir por AJAX. Se manda un formulario a un marco
    // oculto: con target="_blank" quedaba una pestaña en blanco y, al quitar el
    // formulario enseguida, el navegador cancelaba el envío y no descargaba nada.
    var marco = document.getElementById("marco_exportacion");
    if (!marco) {
      marco = document.createElement("iframe");
      marco.id = "marco_exportacion";
      marco.name = "marco_exportacion";
      marco.style.display = "none";
      document.body.appendChild(marco);
    }

    var form = document.createElement("form");
    form.method = "POST";
    form.action = "../controller/reporte/controlador_exportar.php";
    form.target = "marco_exportacion";
    filtros.reporte = reporte;
    filtros.formato = $(this).data("formato");
    filtros._csrf = document.querySelector('meta[name="csrf-token"]').content;
    Object.keys(filtros).forEach(function (clave) {
      var campo = document.createElement("input");
      campo.type = "hidden";
      campo.name = clave;
      campo.value = filtros[clave];
      form.appendChild(campo);
    });
    document.body.appendChild(form);
    form.submit();

    // El formulario se retira después: quitarlo de inmediato cancela el envío
    var boton = $(this);
    var etiqueta = boton.html();
    boton.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');
    setTimeout(function () {
      form.remove();
      boton.prop("disabled", false).html(etiqueta);
      // Si el servidor rechazó la exportación, la respuesta queda dentro del marco
      // y el usuario no vería nada: se lee y se avisa.
      try {
        var texto = (marco.contentDocument && marco.contentDocument.body && marco.contentDocument.body.innerText) || "";
        if (texto.indexOf('"status":"error"') !== -1) {
          var datos = JSON.parse(texto);
          marco.contentDocument.body.innerHTML = "";
          Swal.fire("No se pudo exportar", datos.mensaje || "Intente nuevamente.", "warning");
        }
      } catch (e) {
        // Con la descarga hecha, el marco queda vacío o sin acceso: no hay error que mostrar
      }
    }, 2500);
  });
}
