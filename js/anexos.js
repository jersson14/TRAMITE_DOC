/**
 * Archivos del trámite: documento principal y anexos.
 *
 * Se muestran juntos en el modal "Datos del Expediente". Si la vista no tiene el
 * contenedor #lista_anexos, la función no hace nada, así puede llamarse desde
 * cualquier pantalla sin romper las que no lo muestran.
 */
function escaparTexto(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

/**
 * Dirección para abrir un archivo del trámite. Las carpetas de documentos están
 * cerradas al acceso directo: el archivo lo entrega un controlador que exige
 * sesión y comprueba que el área pueda ver ese trámite.
 */
function urlArchivo(ruta) {
  return "../controller/tramite/controlador_ver_archivo.php?ruta=" + encodeURIComponent(ruta || "");
}

function tamanoLegible(bytes) {
  var n = parseInt(bytes, 10) || 0;
  if (n <= 0) return "";
  if (n < 1024) return n + " B";
  if (n < 1048576) return Math.round(n / 1024) + " KB";
  return (n / 1048576).toFixed(1).replace(".", ",") + " MB";
}

/**
 * Una fila de archivo. `datos` = { titulo, etiqueta, principal, ruta, descarga, meta, existe,
 * origen (qué se firma o verifica: "principal" o el anexo_id; vacío si no es PDF),
 * externo (el documento llega de fuera: se verifica, no se firma), firmas, firmantes }
 */
function filaArchivo(datos) {
  var clases = "archivo-item" + (datos.principal ? " es-principal" : "") + (datos.existe ? "" : " no-disponible");
  var url = escaparTexto(urlArchivo(datos.ruta));
  var firmas = datos.firmas || 0;

  /*
   * Qué se ofrece por archivo:
   *   - Trámite externo: el PDF lo redactó un ciudadano u otra entidad. Firmarlo
   *     sería atribuirse autoría ajena, así que solo se comprueba lo que trae.
   *   - Archivo ya firmado: "Cofirmar", para sumar la firma de otro responsable
   *     al mismo documento (visto bueno).
   *   - Archivo con copia firmada aparte: nada. Se conserva como evidencia de lo
   *     registrado, pero firmarlo otra vez solo generaría más duplicados.
   *   - El resto se firma desde el botón único de arriba, que pide el certificado
   *     una sola vez para todos los archivos elegidos.
   */
  var botonFirmar = "";
  if (datos.origen && datos.externo) {
    botonFirmar =
      '<button type="button" class="btn btn-archivo btn-verificar-firma" data-origen="' + escaparTexto(datos.origen) + '" ' +
        'data-nombre="' + escaparTexto(datos.titulo) + '" ' +
        'title="Comprobar la firma digital que trae este documento">' +
        '<i class="fas fa-user-shield"></i> Verificar firma</button>';
  } else if (datos.origen && firmas) {
    botonFirmar =
      '<button type="button" class="btn btn-archivo btn-firmar" data-origen="' + escaparTexto(datos.origen) + '" ' +
        'data-nombre="' + escaparTexto(datos.titulo) + '" data-firmas="' + firmas + '" ' +
        'title="Agregar mi firma a este archivo">' +
        '<i class="fas fa-file-signature"></i> Cofirmar</button>';
  }

  var reemplazado = datos.reemplazado
    ? '<span class="archivo-etiqueta etiqueta-reemplazado" title="Se firmó y la versión firmada quedó como archivo aparte. Este se conserva como evidencia de lo que se registró.">' +
      '<i class="fas fa-file-signature"></i> Tiene versión firmada</span>'
    : "";

  var sello = firmas
    ? '<span class="archivo-etiqueta etiqueta-firmado" title="' + escaparTexto("Firmado por: " + (datos.firmantes || []).join(", ")) + '">' +
      '<i class="fas fa-check"></i> Firmado' + (firmas > 1 ? " · " + firmas : "") + "</span>"
    : "";

  var acciones = datos.existe
    ? '<div class="archivo-acciones">' + botonFirmar +
        '<a class="btn btn-archivo" href="' + url + '" target="_blank" rel="noopener" title="Abrir en otra pestaña">' +
          '<i class="fas fa-eye"></i> Ver</a>' +
        '<a class="btn btn-archivo" href="' + url + '" download="' + escaparTexto(datos.descarga) + '" ' +
          'title="Descargar" aria-label="Descargar ' + escaparTexto(datos.titulo) + '">' +
          '<i class="fas fa-download"></i></a>' +
      "</div>"
    : "";

  var meta = datos.existe
    ? escaparTexto(datos.meta)
    : '<span class="archivo-perdido"><i class="fas fa-exclamation-triangle"></i> ' +
      "El archivo no se encuentra en el servidor</span>";

  return (
    '<div class="' + clases + '">' +
      '<div class="archivo-icono"><i class="fas ' + (datos.principal ? "fa-file-alt" : "fa-paperclip") + '"></i></div>' +
      '<div class="archivo-cuerpo">' +
        '<div class="archivo-nombre" title="' + escaparTexto(datos.titulo) + '">' +
          escaparTexto(datos.titulo) +
          '<span class="archivo-etiqueta">' + escaparTexto(datos.etiqueta) + "</span>" + sello + reemplazado +
        "</div>" +
        '<div class="archivo-meta">' + meta + "</div>" +
      "</div>" +
      acciones +
    "</div>"
  );
}

/*
 * `firmables` = archivos que todavía se pueden firmar, como [{origen, nombre}].
 * Cuando hay alguno se ofrece un solo botón para todos: el certificado y la
 * contraseña se piden una vez, no una por archivo.
 */
function marcoArchivos(contador, cuerpo, documentoId, firmables) {
  var boton = (firmables && firmables.length)
    ? '<button type="button" class="btn btn-archivo btn-firmar-varios" ' +
        "data-archivos='" + escaparTexto(JSON.stringify(firmables)) + "' " +
        'title="Firmar con su certificado digital">' +
        '<i class="fas fa-file-signature"></i> Firmar documentos</button>'
    : "";

  return (
    '<div class="archivos-tramite" data-documento="' + escaparTexto(documentoId || "") + '">' +
      '<div class="archivos-cabecera">' +
        '<h6 class="archivos-titulo"><i class="fas fa-folder-open"></i> Archivos del trámite</h6>' +
        '<div class="archivos-cabecera-acciones">' +
          (contador ? '<span class="archivos-contador">' + contador + "</span>" : "") +
          boton +
        "</div>" +
      "</div>" +
      cuerpo +
    "</div>"
  );
}

function Cargar_Anexos(documentoId) {
  if (typeof Cargar_Atenciones === "function") Cargar_Atenciones(documentoId);
  var caja = document.getElementById("lista_anexos");
  if (!caja) return;

  if (!documentoId) {
    caja.innerHTML = "";
    return;
  }

  caja.innerHTML = marcoArchivos("", '<div class="archivos-aviso">Cargando archivos...</div>');

  $.ajax({
    url: "../controller/tramite/controlador_listar_anexos.php",
    type: "POST",
    data: { id: documentoId },
    dataType: "json",
    // El aviso general de view/index.php ya muestra el mensaje de un 403;
    // aquí solo se refleja dentro del recuadro.
  })
    .done(function (respuesta) {
      var principal = respuesta && respuesta.principal;
      var anexos = (respuesta && respuesta.data) || [];
      var filas = "";
      if (typeof FIRMA_PERU_CONFIGURADO !== "undefined") FIRMA_PERU_CONFIGURADO = !!(respuesta && respuesta.firma_peru);
      // Además de que la pantalla sepa firmar, el rol tiene que poder (migración 024)
      var puedeFirmar = typeof Formulario_Firma === "function"
        && (typeof puede !== "function" || puede("firmar"));
      var esExterno = !!(respuesta && respuesta.procedencia === "EXTERNO");
      // Archivos que aún se pueden firmar: PDF de un trámite interno, sin firmas
      // propias todavía y sin una copia firmada ya creada.
      var firmables = [];

      if (principal) {
        var metaPrincipal = ["Registrado el " + principal.fecha];
        if (tamanoLegible(principal.bytes)) metaPrincipal.push(tamanoLegible(principal.bytes));
        filas += filaArchivo({
          titulo: "Documento principal",
          etiqueta: "Principal",
          principal: true,
          ruta: principal.ruta,
          descarga: (principal.expediente || documentoId) + ".pdf",
          meta: metaPrincipal.join(" · "),
          existe: principal.existe,
          origen: puedeFirmar && /\.pdf$/i.test(principal.ruta) ? "principal" : "",
          externo: esExterno,
          reemplazado: principal.reemplazado,
        });
        if (puedeFirmar && !esExterno && !principal.reemplazado && principal.existe && /\.pdf$/i.test(principal.ruta)) {
          firmables.push({ origen: "principal", nombre: "Documento principal" });
        }
      }

      for (var i = 0; i < anexos.length; i++) {
        var a = anexos[i];
        var metaAnexo = [];
        if (tamanoLegible(a.anexo_bytes)) metaAnexo.push(tamanoLegible(a.anexo_bytes));
        var f = formatoFechaHora(a.anexo_fecha);
        if (f) metaAnexo.push(f.fecha + " " + f.hora);
        if (a.usu_usuario) metaAnexo.push("subido por " + a.usu_usuario);
        filas += filaArchivo({
          titulo: a.anexo_nombre,
          etiqueta: "Anexo",
          principal: false,
          ruta: a.anexo_ruta,
          descarga: a.anexo_nombre,
          meta: metaAnexo.join(" · "),
          existe: a.existe,
          origen: puedeFirmar && a.es_pdf ? String(a.anexo_id) : "",
          externo: esExterno,
          reemplazado: a.reemplazado,
          firmas: a.firmas,
          firmantes: a.firmantes,
        });
        if (puedeFirmar && !esExterno && !a.reemplazado && !a.firmas && a.existe && a.es_pdf) {
          firmables.push({ origen: String(a.anexo_id), nombre: a.anexo_nombre });
        }
      }

      var total = (principal ? 1 : 0) + anexos.length;
      if (total === 0) {
        caja.innerHTML = marcoArchivos("", '<div class="archivos-aviso">Este trámite no tiene archivos adjuntos.</div>', documentoId, []);
        return;
      }
      if (principal && anexos.length === 0) {
        filas += '<div class="archivos-aviso archivos-aviso-fila">Sin anexos adicionales.</div>';
      }

      if (esExterno) {
        filas =
          '<div class="archivos-aviso archivos-aviso-fila"><i class="fas fa-inbox"></i> ' +
          "Trámite externo: el documento llega firmado desde fuera. Aquí no se firma, se verifica." +
          "</div>" + filas;
      }

      caja.innerHTML = marcoArchivos(total + (total === 1 ? " archivo" : " archivos"), filas, documentoId, firmables);
    })
    .fail(function (xhr) {
      var mensaje = xhr && xhr.status === 403
        ? "No tiene acceso a los archivos de este trámite."
        : "No se pudieron cargar los archivos.";
      caja.innerHTML = marcoArchivos("", '<div class="archivos-aviso archivo-perdido">' + mensaje + "</div>");
    });
}

/**
 * Columna ARCHIVO del historial de movimientos.
 *
 * Muestra el documento de ese envío y, al costado, un botón por cada anexo que
 * viajó con él (el nombre aparece al pasar el mouse). Antes esta pintura estaba
 * copiada en 11 archivos y armaba un enlace a "../null" cuando la copia no tenía
 * archivo; ahora vive aquí.
 */
function Render_Archivos_Movimiento(data, type, row) {
  if (type !== "display") return data || "";

  var html = '<div class="archivos-movimiento">';

  if (data) {
    html +=
      '<a class="btn btn-sm btn-primary" href="' + escaparTexto(urlArchivo(data)) + '" target="_blank" rel="noopener" ' +
      'title="Ver documento"><i class="fas fa-file-download"></i></a>';
  } else {
    html +=
      '<button class="btn btn-sm btn-secondary" disabled title="Sin documento">' +
      '<i class="fa fa-file-pdf"></i></button>';
  }

  var lineas = row && row.anexos ? String(row.anexos).split("\n") : [];
  var anexos = [];
  for (var i = 0; i < lineas.length; i++) {
    var partes = lineas[i].split("\t");
    if (partes[0]) anexos.push({ ruta: partes[0], nombre: partes[1] || "Anexo" });
  }

  for (var j = 0; j < anexos.length; j++) {
    html +=
      '<a class="btn btn-sm btn-anexo" href="' + escaparTexto(urlArchivo(anexos[j].ruta)) + '" target="_blank" rel="noopener" ' +
      'title="Anexo: ' + escaparTexto(anexos[j].nombre) + '" aria-label="Anexo: ' + escaparTexto(anexos[j].nombre) + '">' +
      '<i class="fas fa-paperclip"></i>' + (anexos.length > 1 ? '<span class="anexo-numero">' + (j + 1) + "</span>" : "") +
      "</a>";
  }

  return html + "</div>";
}

/**
 * Columna ESTADO del historial de movimientos, con el acuse de recepción.
 * Reproduce las dos variantes que había copiadas en 10 archivos: la compacta
 * (reportes) y la de íconos (conIconos = true).
 */
function Render_Estado_Movimiento(data, type, row, conIconos) {
  if (type !== "display") return data || "";
  var estilos = {
    PENDIENTE: ["bg-warning", "fas fa-clock"],
    RECHAZADO: ["bg-danger", "fas fa-times-circle"],
    ACEPTADO: ["bg-success", "fas fa-check-circle"],
    FINALIZADO: ["bg-primary", "fas fa-flag"],
    DERIVADO: ["bg-dark", "fas fa-share"],
    ATENDIDO: ["bg-info", "fas fa-reply"],
  };
  var e = estilos[data];
  var html = !e
    ? escaparTexto(data)
    : conIconos
      ? '<span class="badge ' + e[0] + '" style="font-size: 12px; padding: 6px 10px;"><i class="' + e[1] + '"></i> ' + data + "</span>"
      : '<span class="badge ' + e[0] + '">' + data + "</span>";

  if (row && row.recibido_fecha) {
    html += '<small class="d-block text-muted mt-1" style="white-space:nowrap;"><i class="fas fa-inbox"></i> Recibido ' +
      escaparTexto(row.recibido_fecha) + "</small>";
    if (row.recibido_por) html += '<small class="d-block text-muted">' + escaparTexto(row.recibido_por) + "</small>";
  } else if (row && data === "PENDIENTE" && Object.prototype.hasOwnProperty.call(row, "recibido_fecha")) {
    html += '<small class="d-block text-muted mt-1">Sin acuse de recepción</small>';
  }
  return html;
}

/**
 * Panel "Atención solicitada a otras áreas" del modal "Datos del Expediente".
 * Solo aparece si al trámite se le pidió atención a alguna área.
 */
function Cargar_Atenciones(documentoId) {
  var caja = document.getElementById("lista_atenciones");
  if (!caja) return;
  caja.innerHTML = "";
  if (!documentoId) return;

  $.ajax({
    url: "../controller/tramite_area/controlador_listar_atenciones.php",
    type: "POST",
    data: { id: documentoId },
    dataType: "json",
  }).done(function (r) {
    var filas = (r && r.data) || [];
    if (filas.length === 0) return;

    var respondidas = filas.filter(function (a) { return a.respuesta_fecha; }).length;
    var clases = { VERDE: "semaforo-verde", AMBAR: "semaforo-ambar", ROJO: "semaforo-rojo" };
    var html = "";

    for (var i = 0; i < filas.length; i++) {
      var a = filas[i];
      var estado;
      if (a.respuesta_fecha) {
        estado = '<span class="semaforo semaforo-verde">Respondió ' + escaparTexto(a.respuesta_fecha) + "</span>";
      } else if (a.semaforo === "ROJO") {
        estado = '<span class="semaforo semaforo-rojo">Vencido hace ' + Math.abs(a.plazo_restante) + " día(s)</span>";
      } else if (clases[a.semaforo]) {
        estado = '<span class="semaforo ' + clases[a.semaforo] + '">' +
          (a.plazo_restante === 0 ? "Vence hoy" : "Quedan " + a.plazo_restante + " día(s)") + "</span>";
      } else {
        estado = '<span class="semaforo semaforo-gris">Sin plazo</span>';
      }

      var meta = [];
      if (a.plazo_limite) meta.push("límite " + escaparTexto(a.plazo_limite));
      if (a.recibido_fecha) meta.push("recibido " + escaparTexto(a.recibido_fecha));
      else if (!a.respuesta_fecha) meta.push("aún sin acuse de recepción");
      if (a.respuesta_por) meta.push("respondió " + escaparTexto(a.respuesta_por));

      var respuesta = a.respuesta
        ? '<div class="atencion-respuesta">' + escaparTexto(a.respuesta) +
          (a.respuesta_archivo
            ? '<div class="mt-2"><a class="btn btn-archivo" href="' + escaparTexto(urlArchivo(a.respuesta_archivo)) + '" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> Ver archivo de la respuesta</a></div>'
            : "") + "</div>"
        : "";

      html += '<div class="archivo-item" style="align-items:flex-start;">' +
        '<div class="archivo-icono"><i class="fas fa-tasks"></i></div>' +
        '<div class="archivo-cuerpo">' +
          '<div class="archivo-nombre">' + escaparTexto(a.area) + "</div>" +
          '<div class="archivo-meta">' + meta.join(" · ") + "</div>" + respuesta +
        "</div>" +
        '<div class="archivo-acciones">' + estado + "</div>" +
      "</div>";
    }

    caja.innerHTML = '<div class="archivos-tramite">' +
      '<div class="archivos-cabecera"><h6 class="archivos-titulo"><i class="fas fa-tasks"></i> Atención solicitada a otras áreas</h6>' +
      '<span class="archivos-contador">' + respondidas + " de " + filas.length + " respondieron</span></div>" +
      html + "</div>";
  });
}
