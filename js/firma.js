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

function Formulario_Firma(origen, nombre, firmas) {
  var idForm = "firma_" + origen;
  var aviso = firmas > 0
    ? '<div class="firma-nota"><i class="fas fa-info-circle"></i> Este archivo ya tiene ' + firmas +
      (firmas === 1 ? " firma" : " firmas") + ". Su firma se agregará al mismo archivo sin invalidar las anteriores.</div>"
    : '<div class="firma-nota"><i class="fas fa-info-circle"></i> Se creará una copia firmada como anexo nuevo. El archivo original no se modifica.</div>';

  var firmaPeru = FIRMA_PERU_CONFIGURADO
    ? '<div class="firma-aviso firma-aviso-info"><i class="fas fa-id-card"></i><div><b>Firma Perú configurado.</b> ' +
      "La firma con DNI electrónico o token requiere el Firmador de Firma Perú instalado en su computadora. " +
      "Mientras se completa la conexión con el Firmador, use su certificado en archivo.</div></div>"
    : '<div class="firma-aviso"><i class="fas fa-id-card"></i><div><b>Firma con DNI electrónico o token (Firma Perú): aún no disponible.</b> ' +
      "La entidad debe solicitar sus credenciales de Firma Perú a la PCM (Secretaría de Gobierno y Transformación Digital) " +
      "y el administrador registrarlas en el sistema. Mientras tanto, firme con su certificado digital en archivo (.pfx o .p12).</div></div>";

  return (
    '<form class="firma-form" data-origen="' + escaparTexto(origen) + '" novalidate>' +
      '<div class="firma-titulo"><i class="fas fa-file-signature"></i> Firmar «' + escaparTexto(nombre) + "»</div>" +
      '<div class="firma-metodos" role="radiogroup" aria-label="Método de firma">' +
        '<label class="firma-metodo activo"><input type="radio" name="metodo" value="PFX" checked> ' +
          '<i class="fas fa-file-contract"></i> Certificado en archivo (.pfx / .p12)</label>' +
        '<label class="firma-metodo"><input type="radio" name="metodo" value="FIRMA_PERU"> ' +
          '<i class="fas fa-id-card"></i> DNIe o token (Firma Perú)</label>' +
      "</div>" +
      '<div class="firma-panel-pfx">' +
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

  var datos = new FormData();
  datos.append("id", documentoId);
  datos.append("origen", form.data("origen"));
  datos.append("motivo", form.find("input[name=motivo]").val());
  datos.append("clave", clave);
  datos.append("certificado", archivo);

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
      Swal.fire({
        icon: "success",
        title: "Documento firmado",
        html:
          "Firmado por <b>" + escaparTexto(r.firmante) + "</b>" +
          (r.orden > 1 ? " (firma n.° " + r.orden + ")" : "") +
          '.<br>Código de verificación: <b style="letter-spacing:.05em;">' + escaparTexto(r.codigo) + "</b>" +
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
