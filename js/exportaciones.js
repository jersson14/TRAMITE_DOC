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

    // Una descarga no se puede pedir por AJAX: se manda un formulario con el token
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "../controller/reporte/controlador_exportar.php";
    form.target = "_blank";
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
    document.body.removeChild(form);
  });
}
