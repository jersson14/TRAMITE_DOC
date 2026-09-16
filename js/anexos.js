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

function tamanoLegible(bytes) {
  var n = parseInt(bytes, 10) || 0;
  if (n <= 0) return "";
  if (n < 1024) return n + " B";
  if (n < 1048576) return Math.round(n / 1024) + " KB";
  return (n / 1048576).toFixed(1).replace(".", ",") + " MB";
}

/** Una fila de archivo. `datos` = { titulo, etiqueta, principal, ruta, descarga, meta, existe } */
function filaArchivo(datos) {
  var clases = "archivo-item" + (datos.principal ? " es-principal" : "") + (datos.existe ? "" : " no-disponible");
  var url = "../" + escaparTexto(datos.ruta);

  var acciones = datos.existe
    ? '<div class="archivo-acciones">' +
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
          '<span class="archivo-etiqueta">' + escaparTexto(datos.etiqueta) + "</span>" +
        "</div>" +
        '<div class="archivo-meta">' + meta + "</div>" +
      "</div>" +
      acciones +
    "</div>"
  );
}

function marcoArchivos(contador, cuerpo) {
  return (
    '<div class="archivos-tramite">' +
      '<div class="archivos-cabecera">' +
        '<h6 class="archivos-titulo"><i class="fas fa-folder-open"></i> Archivos del trámite</h6>' +
        (contador ? '<span class="archivos-contador">' + contador + "</span>" : "") +
      "</div>" +
      cuerpo +
    "</div>"
  );
}

function Cargar_Anexos(documentoId) {
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
        });
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
        });
      }

      var total = (principal ? 1 : 0) + anexos.length;
      if (total === 0) {
        caja.innerHTML = marcoArchivos("", '<div class="archivos-aviso">Este trámite no tiene archivos adjuntos.</div>');
        return;
      }
      if (principal && anexos.length === 0) {
        filas += '<div class="archivos-aviso archivos-aviso-fila">Sin anexos adicionales.</div>';
      }

      caja.innerHTML = marcoArchivos(total + (total === 1 ? " archivo" : " archivos"), filas);
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
      '<a class="btn btn-sm btn-primary" href="../' + escaparTexto(data) + '" target="_blank" rel="noopener" ' +
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
      '<a class="btn btn-sm btn-anexo" href="../' + escaparTexto(anexos[j].ruta) + '" target="_blank" rel="noopener" ' +
      'title="Anexo: ' + escaparTexto(anexos[j].nombre) + '" aria-label="Anexo: ' + escaparTexto(anexos[j].nombre) + '">' +
      '<i class="fas fa-paperclip"></i>' + (anexos.length > 1 ? '<span class="anexo-numero">' + (j + 1) + "</span>" : "") +
      "</a>";
  }

  return html + "</div>";
}
