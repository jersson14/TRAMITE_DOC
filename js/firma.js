/**
 * Firma digital de los PDF del trámite (panel "Archivos del trámite").
 *
 * El botón "Firmar" de cada PDF despliega el formulario debajo del archivo, dentro
 * del mismo panel: el panel ya está en un modal y abrir otro modal encima da
 * problemas de foco y de fondo en Bootstrap 4.
 *
 * El certificado y la contraseña viajan solo en la petición de firma; el
 * servidor no los guarda.
 */
var FIRMA_PERU_CONFIGURADO = false;

/*
 * `archivos` = [{origen, nombre}] cuando se firman varios de una vez desde el
 * botón de la cabecera. Para "Cofirmar" viene uno solo y sin casillas.
 */
function Formulario_Firma(origen, nombre, firmas, archivos) {
  var idForm = "firma_" + origen;
  var varios = !!(archivos && archivos.length);

  var aviso = firmas > 0
    ? '<div class="firma-nota"><i class="fas fa-info-circle"></i> Este archivo ya tiene ' + firmas +
      (firmas === 1 ? " firma" : " firmas") + ". Su firma se agregará al mismo archivo sin invalidar las anteriores.</div>"
    : '<div class="firma-nota"><i class="fas fa-info-circle"></i> De cada archivo se crea una copia firmada como anexo nuevo. ' +
      "Los originales no se modifican y quedan como evidencia de lo que se registró.</div>";

  // Un solo certificado para todos los archivos elegidos
  var lista = "";
  if (varios) {
    for (var i = 0; i < archivos.length; i++) {
      lista +=
        '<label class="firma-archivo">' +
          '<input type="checkbox" name="archivo" value="' + escaparTexto(archivos[i].origen) + '" checked> ' +
          '<i class="far fa-file-pdf"></i> <span>' + escaparTexto(archivos[i].nombre) + "</span>" +
        "</label>";
    }
    lista =
      '<div class="form-group">' +
        '<label>Archivos a firmar</label>' +
        '<div class="firma-archivos">' + lista + "</div>" +
      "</div>";
  }

  var firmaPeru = FIRMA_PERU_CONFIGURADO
    ? '<div class="firma-aviso firma-aviso-info"><i class="fas fa-id-card"></i><div><b>Firma Perú configurado.</b> ' +
      "La firma con DNI electrónico o token requiere el Firmador de Firma Perú instalado en su computadora. " +
      "Mientras se completa la conexión con el Firmador, use su certificado en archivo.</div></div>"
    : '<div class="firma-aviso"><i class="fas fa-id-card"></i><div><b>Firma con DNI electrónico o token (Firma Perú): aún no disponible.</b> ' +
      "La entidad debe solicitar sus credenciales de Firma Perú a la PCM (Secretaría de Gobierno y Transformación Digital) " +
      "y el administrador registrarlas en el sistema. Mientras tanto, firme con su certificado digital en archivo (.pfx o .p12).</div></div>";

  return (
    '<form class="firma-form" data-origen="' + escaparTexto(origen) + '" novalidate>' +
      '<div class="firma-titulo"><i class="fas fa-file-signature"></i> ' +
        (varios ? "Firmar documentos del trámite" : "Firmar «" + escaparTexto(nombre) + "»") + "</div>" +
      '<div class="firma-metodos" role="radiogroup" aria-label="Método de firma">' +
        '<label class="firma-metodo activo"><input type="radio" name="metodo" value="PFX" checked> ' +
          '<i class="fas fa-file-contract"></i> Certificado en archivo (.pfx / .p12)</label>' +
        '<label class="firma-metodo"><input type="radio" name="metodo" value="FIRMA_PERU"> ' +
          '<i class="fas fa-id-card"></i> DNIe o token (Firma Perú)</label>' +
      "</div>" +
      '<div class="firma-panel-pfx">' +
        lista +
        '<div class="form-row">' +
          '<div class="form-group col-md-6">' +
            '<label for="' + idForm + '_cert">Certificado digital</label>' +
            '<input type="file" class="form-control" id="' + idForm + '_cert" name="certificado" accept=".pfx,.p12,application/x-pkcs12">' +
          "</div>" +
          '<div class="form-group col-md-6">' +
            '<label for="' + idForm + '_clave">Contraseña del certificado</label>' +
            '<input type="password" class="form-control" id="' + idForm + '_clave" name="clave" autocomplete="off">' +
          "</div>" +
        "</div>" +
        '<div class="form-group">' +
          '<label for="' + idForm + '_motivo">Motivo <small class="text-muted">(opcional)</small></label>' +
          '<input type="text" class="form-control" id="' + idForm + '_motivo" name="motivo" maxlength="150" ' +
            'placeholder="Ej.: Conformidad, Visto bueno, Aprobación">' +
        "</div>" +
        aviso +
        '<div class="firma-botones">' +
          '<button type="button" class="btn btn-secondary btn-sm firma-cancelar">Cancelar</button>' +
          '<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-file-signature"></i> Firmar documento</button>' +
        "</div>" +
      "</div>" +
      '<div class="firma-panel-peru" hidden>' + firmaPeru + "</div>" +
      '<div class="firma-seguridad"><i class="fas fa-lock"></i> Su certificado y su contraseña no se guardan en el sistema.</div>' +
    "</form>"
  );
}

$(document).on("click", ".btn-firmar", function () {
  var fila = $(this).closest(".archivo-item");
  var siguiente = fila.next(".firma-form");
  $(".firma-form").remove();
  if (siguiente.length) return; // segundo clic: cierra el formulario

  var origen = String($(this).data("origen"));
  var form = $(Formulario_Firma(origen, $(this).data("nombre"), parseInt($(this).data("firmas"), 10) || 0));
  fila.after(form);
  form.find("input[type=file]").trigger("focus");
});

/* Botón único de la cabecera: un certificado para todos los archivos elegidos. */
$(document).on("click", ".btn-firmar-varios", function () {
  var marco = $(this).closest(".archivos-tramite");
  if (marco.find(".firma-form").length) {
    marco.find(".firma-form").remove();
    return; // segundo clic: cierra
  }
  $(".firma-form").remove();

  var archivos = [];
  try {
    archivos = JSON.parse(String($(this).attr("data-archivos") || "[]"));
  } catch (e) {
    archivos = [];
  }
  if (!archivos.length) {
    Swal.fire("Atención", "No hay archivos pendientes de firma en este trámite.", "info");
    return;
  }

  var form = $(Formulario_Firma("varios", "", 0, archivos));
  $(this).closest(".archivos-cabecera").after(form);
  form.find("input[type=file]").trigger("focus");
});

$(document).on("click", ".firma-cancelar", function () {
  $(this).closest(".firma-form").remove();
});

$(document).on("change", ".firma-form input[name=metodo]", function () {
  var form = $(this).closest(".firma-form");
  var peru = this.value === "FIRMA_PERU";
  form.find(".firma-metodo").removeClass("activo");
  $(this).closest(".firma-metodo").addClass("activo");
  form.find(".firma-panel-pfx").prop("hidden", peru);
  form.find(".firma-panel-peru").prop("hidden", !peru);
});

$(document).on("submit", ".firma-form", function (e) {
  e.preventDefault();
  var form = $(this);
  var documentoId = form.closest(".archivos-tramite").data("documento");
  var archivo = form.find("input[name=certificado]")[0].files[0];
  var clave = form.find("input[name=clave]").val();

  if (!archivo) {
    Swal.fire("Atención", "Seleccione su certificado digital (.pfx o .p12).", "warning");
    return;
  }
  if (!/\.(pfx|p12)$/i.test(archivo.name)) {
    Swal.fire("Atención", "El certificado debe ser un archivo .pfx o .p12.", "warning");
    return;
  }
  if (!clave) {
    Swal.fire("Atención", "Escriba la contraseña de su certificado.", "warning");
    form.find("input[name=clave]").trigger("focus");
    return;
  }

  var marcados = form.find("input[name=archivo]:checked");
  var esVarios = form.find("input[name=archivo]").length > 0;
  if (esVarios && !marcados.length) {
    Swal.fire("Atención", "Marque al menos un archivo para firmar.", "warning");
    return;
  }

  var datos = new FormData();
  datos.append("id", documentoId);
  datos.append("motivo", form.find("input[name=motivo]").val());
  datos.append("clave", clave);
  datos.append("certificado", archivo);
  if (esVarios) {
    marcados.each(function () {
      datos.append("origenes[]", this.value);
    });
  } else {
    datos.append("origen", form.data("origen"));
  }

  var boton = form.find("button[type=submit]");
  boton.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Firmando...');

  $.ajax({
    url: "../controller/tramite/controlador_firmar_documento.php",
    type: "POST",
    data: datos,
    processData: false,
    contentType: false,
    dataType: "json",
  })
    .done(function (r) {
      form.find("input[name=clave]").val("");

      var hechos = r.firmados || [];
      var fallos = r.errores || [];
      var detalle = "";
      for (var i = 0; i < hechos.length; i++) {
        detalle +=
          '<li style="margin-bottom:.3rem;"><i class="fas fa-check-circle"></i> ' +
          "<b>" + escaparTexto(hechos[i].archivo) + "</b>" +
          (hechos[i].orden > 1 ? " (firma n.° " + hechos[i].orden + ")" : "") +
          '<br><small>Código: <b style="letter-spacing:.05em;">' + escaparTexto(hechos[i].codigo) + "</b></small></li>";
      }
      for (var j = 0; j < fallos.length; j++) {
        detalle +=
          '<li style="margin-bottom:.3rem;color:#9B0000;"><i class="fas fa-times-circle"></i> ' +
          "<b>" + escaparTexto(fallos[j].archivo) + "</b><br><small>" + escaparTexto(fallos[j].mensaje) + "</small></li>";
      }

      Swal.fire({
        icon: fallos.length ? "warning" : "success",
        title: hechos.length === 1 ? "Documento firmado"
             : hechos.length + " documentos firmados",
        html:
          "Firmado por <b>" + escaparTexto(r.firmante) + "</b>." +
          '<ul style="text-align:left;padding-left:1.2rem;margin-top:.75rem;">' + detalle + "</ul>" +
          (fallos.length
            ? '<div class="firma-nota text-left"><i class="fas fa-info-circle"></i> Los demás se firmaron igual: ' +
              "puede corregir lo señalado y volver a intentar solo con esos.</div>"
            : "") +
          (r.autofirmado
            ? '<div class="firma-aviso mt-3 text-left"><i class="fas fa-exclamation-triangle"></i><div>Su certificado es <b>autofirmado</b>: ' +
              "la firma es íntegra, pero no fue emitida por una entidad de certificación acreditada ante INDECOPI.</div></div>"
            : ""),
      });
      Cargar_Anexos(documentoId);
    })
    .fail(function (xhr) {
      // 403 y 422 ya los muestra el aviso general de view/index.php
      if ([403, 419, 422, 429].indexOf(xhr.status) === -1) {
        Swal.fire("Error", (xhr.responseJSON && xhr.responseJSON.mensaje) || "No se pudo firmar el documento.", "error");
      }
      form.find("input[name=clave]").val("");
      boton.prop("disabled", false).html('<i class="fas fa-file-signature"></i> Firmar documento');
    });
});

/**
 * Presentación del resultado de una verificación, compartida por el botón
 * "Verificar firma" del panel y por la comprobación al registrar un trámite.
 * `r` = { total, validas, todas_validas, firmantes: [{nombre, dni, emisor, valida}] }
 */
function Resumen_Firmas_Html(r) {
  if (!r || !r.total) {
    return (
      '<div class="firma-aviso text-left"><i class="fas fa-exclamation-triangle"></i><div>' +
      "<b>Este PDF no trae firma digital.</b> Puede ser un documento en papel escaneado. " +
      "Se puede registrar igual, pero no hay firma que comprobar.</div></div>"
    );
  }

  var lista = "";
  for (var i = 0; i < r.firmantes.length; i++) {
    var f = r.firmantes[i];
    lista +=
      '<li style="margin-bottom:.35rem;">' +
      '<i class="fas ' + (f.valida ? "fa-check-circle" : "fa-times-circle") + '"></i> ' +
      "<b>" + escaparTexto(f.nombre) + "</b>" +
      (f.dni ? " · DNI " + escaparTexto(f.dni) : "") +
      (f.emisor ? '<br><small class="text-muted">Emitido por ' + escaparTexto(f.emisor) + "</small>" : "") +
      (f.valida ? "" : '<br><small style="color:#9B0000;">Esta firma no verifica: el archivo cambió después de firmarse.</small>') +
      "</li>";
  }

  var encabezado = r.todas_validas
    ? '<div class="firma-nota"><i class="fas fa-shield-alt"></i> Trae <b>' + r.total +
      (r.total === 1 ? " firma válida" : " firmas válidas") + "</b>.</div>"
    : '<div class="firma-aviso text-left"><i class="fas fa-exclamation-triangle"></i><div>' +
      "<b>Atención:</b> " + r.validas + " de " + r.total + " firmas verifican. " +
      "El documento pudo haber sido modificado después de firmarse.</div></div>";

  return encabezado + '<ul style="text-align:left;padding-left:1.2rem;margin-top:.75rem;">' + lista + "</ul>";
}

/** Botón "Verificar firma": documentos externos, que no se firman aquí. */
$(document).on("click", ".btn-verificar-firma", function () {
  var boton = $(this);
  var documentoId = boton.closest(".archivos-tramite").data("documento");
  var nombre = String(boton.data("nombre") || "el documento");
  var original = boton.html();

  boton.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Verificando...');

  var datos = new FormData();
  datos.append("id", documentoId);
  datos.append("origen", boton.data("origen"));

  $.ajax({
    url: "../controller/tramite/controlador_verificar_firma.php",
    type: "POST",
    data: datos,
    processData: false,
    contentType: false,
    dataType: "json",
  })
    .done(function (r) {
      Swal.fire({
        icon: r.total && r.todas_validas ? "success" : "info",
        title: "Firma de «" + nombre + "»",
        html: Resumen_Firmas_Html(r),
      });
    })
    .fail(function (xhr) {
      if ([403, 419, 422, 429].indexOf(xhr.status) === -1) {
        Swal.fire("Error", (xhr.responseJSON && xhr.responseJSON.mensaje) || "No se pudo verificar la firma.", "error");
      }
    })
    .always(function () {
      boton.prop("disabled", false).html(original);
    });
});

/**
 * Comprobación real de la firma al elegir el PDF en el registro de un trámite.
 *
 * Reemplaza la casilla "Tiene el documento firmado digitalmente", que solo era una
 * declaración del operador: habilitaba el campo de archivo y nadie comprobaba nada.
 * Ahora el servidor lee el PDF, verifica criptográficamente las firmas que trae y
 * las muestra. El archivo no se guarda: se descarta apenas termina la comprobación.
 *
 * `input` = elemento <input type="file">, `caja` = contenedor donde pintar el aviso.
 */
function Verificar_PDF_Al_Elegir(input, caja) {
  if (!input || !caja) return;

  input.addEventListener("change", function () {
    var archivo = input.files && input.files[0];
    caja.innerHTML = "";
    if (!archivo || !/\.pdf$/i.test(archivo.name)) return;

    caja.innerHTML = '<div class="firma-nota"><i class="fas fa-spinner fa-spin"></i> Comprobando la firma del documento...</div>';

    var datos = new FormData();
    datos.append("archivo", archivo);

    $.ajax({
      url: "../controller/tramite/controlador_verificar_firma.php",
      type: "POST",
      data: datos,
      processData: false,
      contentType: false,
      dataType: "json",
    })
      .done(function (r) {
        caja.innerHTML = Resumen_Firmas_Html(r);
      })
      .fail(function () {
        // No bloquea el registro: es informativo. Un PDF ilegible ya lo rechaza el registro.
        caja.innerHTML =
          '<div class="firma-nota"><i class="fas fa-info-circle"></i> ' +
          "No se pudo comprobar la firma de este archivo.</div>";
      });
  });
}
