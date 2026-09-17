/**
 * Panel de filtros de las bandejas (Recibidos, Enviados, Trámites, Movimientos).
 *
 * Las bandejas ya cargan todos sus trámites; el panel los filtra al instante en
 * el navegador, combinando texto, estado, plazo, tipo de documento, fechas y (en
 * Recibidos) si llegó como principal, copia o atención. Antes había que elegir
 * un único estado y pulsar "Buscar", lo que volvía a pedir los datos al servidor.
 *
 * Uso en la vista: <div id="filtros_bandeja" data-tipos="1"></div> y, después de
 * crear la tabla, Filtros_Bandeja.montar("#filtros_bandeja", "#tabla_tramite").
 */
var Filtros_Bandeja = (function () {
  var panel = null;
  var idTabla = null;
  var esperaTexto = null;

  var PLAZOS = [
    ["", "Todos"],
    ["ROJO", "Vencido"],
    ["AMBAR", "Por vencer"],
    ["VERDE", "En plazo"],
    ["SIN_PLAZO", "Sin plazo"],
    ["CERRADO", "Cerrado"],
  ];
  var ESTADOS = ["PENDIENTE", "ACEPTADO", "RECHAZADO", "FINALIZADO"];

  function normalizar(valor) {
    return String(valor === null || valor === undefined ? "" : valor)
      .normalize("NFD")
      .replace(/[̀-ͯ]/g, "")
      .toLowerCase();
  }

  function opciones(lista) {
    return lista.map(function (o) {
      return '<option value="' + escaparTexto(o[0]) + '">' + escaparTexto(o[1]) + "</option>";
    }).join("");
  }

  function html(conTipos) {
    return (
      '<div class="filtros-bandeja" role="search" aria-label="Filtrar trámites">' +
        '<div class="filtros-fila">' +
          '<div class="filtro filtro-texto">' +
            '<label for="fb_texto">Buscar</label>' +
            '<div class="filtro-caja-texto"><i class="fas fa-search" aria-hidden="true"></i>' +
              '<input type="search" id="fb_texto" class="form-control" autocomplete="off" ' +
                'placeholder="Expediente, N° documento, remitente, DNI o asunto"></div>' +
          "</div>" +
          '<div class="filtro">' +
            '<label for="fb_estado">Estado</label>' +
            '<select id="fb_estado" class="form-control">' +
              opciones([["", "Todos"]].concat(ESTADOS.map(function (e) { return [e, e.charAt(0) + e.slice(1).toLowerCase()]; }))) +
            "</select>" +
          "</div>" +
          '<div class="filtro">' +
            '<label for="fb_plazo">Plazo</label>' +
            '<select id="fb_plazo" class="form-control">' + opciones(PLAZOS) + "</select>" +
          "</div>" +
        "</div>" +
        '<div class="filtros-fila">' +
          '<div class="filtro">' +
            '<label for="fb_tipo_doc">Tipo de documento</label>' +
            '<select id="fb_tipo_doc" class="form-control"><option value="">Todos</option></select>' +
          "</div>" +
          (conTipos
            ? '<div class="filtro">' +
                '<label for="fb_llegada">Llegó como</label>' +
                '<select id="fb_llegada" class="form-control">' +
                  opciones([["", "Todos"], ["PRINCIPAL", "Responsable"], ["COPIA", "Copia"], ["ATENCION", "Atención pedida"]]) +
                "</select>" +
              "</div>"
            : "") +
          '<div class="filtro filtro-fecha">' +
            '<label for="fb_desde">Registrado desde</label>' +
            '<input type="date" id="fb_desde" class="form-control">' +
          "</div>" +
          '<div class="filtro filtro-fecha">' +
            '<label for="fb_hasta">Hasta</label>' +
            '<input type="date" id="fb_hasta" class="form-control">' +
          "</div>" +
          '<div class="filtro filtro-acciones">' +
            '<button type="button" class="btn btn-archivo fb-limpiar" disabled><i class="fas fa-times"></i> Limpiar filtros</button>' +
          "</div>" +
        "</div>" +
        '<div class="filtros-resumen" aria-live="polite"></div>' +
      "</div>"
    );
  }

  function valor(id) {
    var el = document.getElementById(id);
    return el ? el.value : "";
  }

  function hayFiltros() {
    return ["fb_texto", "fb_estado", "fb_plazo", "fb_tipo_doc", "fb_llegada", "fb_desde", "fb_hasta"]
      .some(function (id) { return valor(id) !== ""; });
  }

  function cumple(fila) {
    var texto = normalizar(valor("fb_texto")).trim();
    if (texto) {
      var campos = normalizar([
        fila.doc_expediente, fila.documento_id, fila.doc_nrodocumento, fila.REMITENTE,
        fila.doc_dniremitente, fila.doc_asunto, fila.tipodo_descripcion,
      ].join(" "));
      // Cada palabra debe aparecer en algún campo: "perez 2026" encuentra a Pérez en 2026
      var palabras = texto.split(/\s+/);
      for (var i = 0; i < palabras.length; i++) {
        if (campos.indexOf(palabras[i]) === -1) return false;
      }
    }

    var estado = valor("fb_estado");
    if (estado && fila.doc_estatus !== estado) return false;

    var plazo = valor("fb_plazo");
    if (plazo && fila.plazo_semaforo !== plazo) return false;

    var tipoDoc = valor("fb_tipo_doc");
    if (tipoDoc && fila.tipodo_descripcion !== tipoDoc) return false;

    var llegada = valor("fb_llegada");
    if (llegada) {
      var como = fila.es_atencion == 1 ? "ATENCION" : fila.es_copia == 1 ? "COPIA" : "PRINCIPAL";
      if (como !== llegada) return false;
    }

    var fecha = String(fila.doc_fecharegistro || "").slice(0, 10);
    var desde = valor("fb_desde");
    var hasta = valor("fb_hasta");
    if (desde && (!fecha || fecha < desde)) return false;
    if (hasta && (!fecha || fecha > hasta)) return false;

    return true;
  }

  // Un único filtro global de DataTables; solo actúa sobre la tabla con panel montado.
  // Se registra al montar porque view/index.php carga DataTables después de este archivo.
  var registrado = false;
  function registrarFiltro() {
    if (registrado) return;
    registrado = true;
    $.fn.dataTable.ext.search.push(function (settings, datos, indice, fila) {
      if (!panel || !document.body.contains(panel) || settings.nTable.id !== idTabla) return true;
      return cumple(fila || {});
    });
  }

  function tabla() {
    return $.fn.dataTable.isDataTable("#" + idTabla) ? $("#" + idTabla).DataTable() : null;
  }

  function aplicar() {
    var t = tabla();
    if (t) t.draw();
    actualizarResumen();
  }

  function actualizarResumen() {
    var t = tabla();
    var resumen = panel && panel.querySelector(".filtros-resumen");
    var limpiar = panel && panel.querySelector(".fb-limpiar");
    if (!t || !resumen) return;
    var total = t.rows().count();
    var visibles = t.rows({ search: "applied" }).count();
    var activos = hayFiltros();
    limpiar.disabled = !activos;
    resumen.innerHTML = !activos
      ? total + (total === 1 ? " trámite" : " trámites")
      : visibles === 0
        ? '<span class="filtros-sin-resultados"><i class="fas fa-info-circle"></i> Ningún trámite coincide con los filtros.</span>'
        : "Mostrando <b>" + visibles + "</b> de " + total + " trámites";
  }

  /** Llena "Tipo de documento" con los tipos presentes en la bandeja. */
  function cargarTiposDocumento() {
    var t = tabla();
    var select = document.getElementById("fb_tipo_doc");
    if (!t || !select) return;
    var elegido = select.value;
    var tipos = {};
    t.rows().data().each(function (fila) {
      if (fila.tipodo_descripcion) tipos[fila.tipodo_descripcion] = true;
    });
    var lista = Object.keys(tipos).sort(function (a, b) { return a.localeCompare(b, "es"); });
    select.innerHTML = '<option value="">Todos</option>' + opciones(lista.map(function (x) { return [x, x]; }));
    select.value = tipos[elegido] ? elegido : "";
  }

  function montar(selectorPanel, selectorTabla, inicial) {
    var contenedor = document.querySelector(selectorPanel);
    if (!contenedor) return;
    registrarFiltro();
    idTabla = String(selectorTabla || "#tabla_tramite").replace(/^#/, "");
    contenedor.innerHTML = html(contenedor.getAttribute("data-tipos") === "1");
    panel = contenedor.querySelector(".filtros-bandeja");

    // El buscador propio de DataTables queda de más: el panel lo reemplaza
    $(contenedor).closest(".card").addClass("bandeja-filtrada");

    if (inicial && inicial.estado) document.getElementById("fb_estado").value = inicial.estado;

    $(panel).on("input", "#fb_texto", function () {
      clearTimeout(esperaTexto);
      esperaTexto = setTimeout(aplicar, 200);
    });
    $(panel).on("change", "select, input[type=date]", aplicar);
    $(panel).on("keydown", "#fb_texto", function (e) {
      if (e.key === "Escape" && this.value) { this.value = ""; aplicar(); }
    });
    $(panel).on("click", ".fb-limpiar", function () {
      $(panel).find("input").val("");
      $(panel).find("select").val("");
      aplicar();
      document.getElementById("fb_texto").focus();
    });

    // La tabla se vuelve a crear al refrescar la bandeja: se escucha en el documento
    $(document).off("xhr.dt.filtros").on("xhr.dt.filtros", function (e) {
      if (e.target.id !== idTabla) return;
      setTimeout(function () { cargarTiposDocumento(); aplicar(); }, 0);
    });
    $(document).off("draw.dt.filtros").on("draw.dt.filtros", function (e) {
      if (e.target.id === idTabla) actualizarResumen();
    });

    cargarTiposDocumento();
    aplicar();
  }

  return { montar: montar };
})();
