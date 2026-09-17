/**
 * Pantalla de configuración (solo administrador).
 *
 * Reúne en un lugar los datos de la institución —que antes se editaban en dos
 * ventanas del tablero y no incluían la sigla ni el color— y los ajustes del
 * asistente del chat, que antes vivían en un archivo del servidor.
 */
var CONFIG_PROVEEDORES = {};

function escaparConfig(valor) {
  return String(valor === null || valor === undefined ? "" : valor)
    .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
}

function Cargar_Configuracion() {
  $.ajax({
    url: "../controller/configuracion/controlador_leer.php",
    type: "POST",
    dataType: "json",
  }).done(function (r) {
    Pintar_Institucion(r.institucion);
    Pintar_Asistente(r.asistente);
  }).fail(function () {
    Swal.fire("Mensaje de Error", "No se pudo leer la configuración", "error");
  });
}

function Pintar_Institucion(i) {
  document.getElementById("cfg_id_empresa").value = i.id;
  document.getElementById("cfg_razon").value = i.razon;
  document.getElementById("cfg_sigla").value = i.sigla;
  document.getElementById("cfg_email").value = i.email;
  document.getElementById("cfg_codigo").value = i.codigo;
  document.getElementById("cfg_telefono").value = i.telefono;
  document.getElementById("cfg_direccion").value = i.direccion;
  document.getElementById("cfg_hora_inicio").value = i.hora_inicio;
  document.getElementById("cfg_hora_fin").value = i.hora_fin;
  document.getElementById("cfg_color").value = i.color;
  document.getElementById("cfg_color_texto").value = i.color;
  document.getElementById("cfg_logo_actual").value = i.logo;
  // Se muestra el logo que el sistema está usando de verdad
  document.getElementById("cfg_logo_vista").src = "../" + i.logo_usado;
  document.getElementById("cfg_logo_aviso").hidden = i.logo_existe;
}

function Pintar_Asistente(a) {
  CONFIG_PROVEEDORES = a.proveedores || {};
  var opciones = "";
  Object.keys(CONFIG_PROVEEDORES).forEach(function (clave) {
    opciones += '<option value="' + escaparConfig(clave) + '">' +
      escaparConfig(CONFIG_PROVEEDORES[clave][0]) + "</option>";
  });
  document.getElementById("cfg_ia_proveedor").innerHTML = opciones;
  document.getElementById("cfg_ia_proveedor").value = a.proveedor;
  document.getElementById("cfg_ia_modelo").value = a.modelo;
  document.getElementById("cfg_ia_activo").checked = !!a.activo;
  document.getElementById("cfg_ia_minuto").value = a.limite_minuto;
  document.getElementById("cfg_ia_dia").value = a.limite_dia;
  document.getElementById("cfg_ia_clave").value = "";
  document.getElementById("cfg_bloque_quitar_clave").hidden = !a.tiene_clave;
  document.getElementById("cfg_quitar_clave").checked = false;
  document.getElementById("cfg_ia_clave_estado").innerHTML = a.tiene_clave
    ? "Hay una clave guardada (" + escaparConfig(a.clave) + "). Déjelo vacío para conservarla."
    : "No hay ninguna clave guardada: el asistente responde solo lo básico.";
  Ayuda_Modelo();
}

/** Al cambiar de proveedor se sugiere su modelo habitual. */
function Cambio_Proveedor_IA() {
  var proveedor = document.getElementById("cfg_ia_proveedor").value;
  var sugerido = (CONFIG_PROVEEDORES[proveedor] || [])[1];
  if (sugerido) document.getElementById("cfg_ia_modelo").value = sugerido;
  Ayuda_Modelo();
}

function Ayuda_Modelo() {
  var proveedor = document.getElementById("cfg_ia_proveedor").value;
  document.getElementById("cfg_ia_ayuda_modelo").textContent = proveedor === "gemini"
    ? "Por ejemplo gemini-2.5-flash. La clave se obtiene en Google AI Studio."
    : "Por ejemplo gpt-4o. La clave se obtiene en platform.openai.com (empieza con sk-).";
}

/* ---- Institución ---- */

function Guardar_Institucion() {
  var id = document.getElementById("cfg_id_empresa").value;
  var razon = document.getElementById("cfg_razon").value.trim();
  var sigla = document.getElementById("cfg_sigla").value.trim();
  var color = document.getElementById("cfg_color_texto").value.trim().toUpperCase();
  var email = document.getElementById("cfg_email").value.trim();
  var codigo = document.getElementById("cfg_codigo").value.trim();
  var telefono = document.getElementById("cfg_telefono").value.trim();
  var direccion = document.getElementById("cfg_direccion").value.trim();
  var hini = document.getElementById("cfg_hora_inicio").value;
  var hfin = document.getElementById("cfg_hora_fin").value;

  if (!razon || !sigla || !email || !codigo || !telefono || !direccion) {
    return Swal.fire("Mensaje de Advertencia", "Complete todos los campos obligatorios", "warning");
  }
  if (!/^#[0-9A-F]{6}$/.test(color)) {
    return Swal.fire("Mensaje de Advertencia", "El color debe tener el formato #RRGGBB", "warning");
  }
  if (!hini || !hfin || hini >= hfin) {
    return Swal.fire("Mensaje de Advertencia", "La hora de inicio debe ser anterior a la de cierre", "warning");
  }

  // Los datos y la marca van a endpoints distintos; el logo, solo si eligieron uno
  $.ajax({
    url: "../controller/empresa/controlador_modificar_empresa.php",
    type: "POST",
    data: { id: id, nom: razon, email: email, cod: codigo, tel: telefono, dir: direccion, hini: hini, hfin: hfin },
  }).done(function () {
    $.ajax({
      url: "../controller/empresa/controlador_modificar_marca.php",
      type: "POST",
      data: { id: id, sigla: sigla, color: color },
    }).done(function () {
      Subir_Logo(id, function () {
        Swal.fire({
          title: "Mensaje de Confirmación",
          text: "Datos de la institución actualizados. La página se recargará para aplicar el logo y el nombre.",
          icon: "success",
        }).then(function () {
          location.reload();
        });
      });
    });
  });
}

/** Sube el logo elegido, si hay uno, y luego continúa. */
function Subir_Logo(id, alTerminar) {
  var archivo = document.getElementById("cfg_logo").files[0];
  if (!archivo) return alTerminar();
  if (archivo.size > 5 * 1048576) {
    return Swal.fire("Mensaje de Advertencia", "El logo no debe pesar más de 5 MB", "warning");
  }
  var datos = new FormData();
  datos.append("id", id);
  datos.append("fotoactual", document.getElementById("cfg_logo_actual").value);
  datos.append("foto", archivo);
  $.ajax({
    url: "../controller/empresa/controlador_empresa_modificar_foto.php",
    type: "POST",
    data: datos,
    contentType: false,
    processData: false,
  }).done(alTerminar).fail(function (x) {
    Swal.fire("Mensaje de Error", (x.responseJSON && x.responseJSON.mensaje) || "No se pudo subir el logo", "error");
  });
}

/* ---- Asistente ---- */

function DatosIA() {
  return {
    activo: document.getElementById("cfg_ia_activo").checked ? 1 : "",
    proveedor: document.getElementById("cfg_ia_proveedor").value,
    modelo: document.getElementById("cfg_ia_modelo").value.trim(),
    clave: document.getElementById("cfg_ia_clave").value.trim(),
    limite_minuto: document.getElementById("cfg_ia_minuto").value,
    limite_dia: document.getElementById("cfg_ia_dia").value,
    quitar_clave: document.getElementById("cfg_quitar_clave").checked ? 1 : "",
  };
}

function Guardar_IA() {
  var datos = DatosIA();
  if (!datos.modelo) {
    return Swal.fire("Mensaje de Advertencia", "Indique el modelo a usar", "warning");
  }
  $.ajax({
    url: "../controller/configuracion/controlador_guardar_ia.php",
    type: "POST",
    dataType: "json",
    data: datos,
  }).done(function () {
    Swal.fire("Mensaje de Confirmación", "Configuración del asistente guardada", "success");
    Cargar_Configuracion();
  }).fail(function (x) {
    Swal.fire("Mensaje de Advertencia", (x.responseJSON && x.responseJSON.mensaje) || "No se pudo guardar", "warning");
  });
}

function Probar_IA() {
  var datos = DatosIA();
  var caja = document.getElementById("cfg_ia_resultado");
  caja.innerHTML = '<div class="alert alert-secondary mb-0"><i class="fas fa-spinner fa-spin"></i> Consultando al proveedor...</div>';
  $.ajax({
    url: "../controller/configuracion/controlador_probar_ia.php",
    type: "POST",
    dataType: "json",
    data: { proveedor: datos.proveedor, modelo: datos.modelo, clave: datos.clave },
  }).done(function (r) {
    if (r.status === "ok") {
      caja.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-check-circle"></i> <b>Conexión correcta</b><br>' +
        escaparConfig(r.proveedor) + " respondió en " + r.ms + " ms.</div>";
    } else {
      caja.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle"></i> <b>No se pudo conectar</b><br>' +
        escaparConfig(r.mensaje) + "</div>";
    }
  }).fail(function (x) {
    caja.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle"></i> ' +
      escaparConfig((x.responseJSON && x.responseJSON.mensaje) || "La prueba falló") + "</div>";
  });
}

/* El selector de color y su texto se mantienen iguales */
$(document).on("input", "#cfg_color", function () {
  document.getElementById("cfg_color_texto").value = this.value.toUpperCase();
});
$(document).on("change", "#cfg_color_texto", function () {
  var valor = this.value.trim().toUpperCase();
  if (/^#[0-9A-F]{6}$/.test(valor)) document.getElementById("cfg_color").value = valor;
});

/* Al elegir un logo se ve antes de guardarlo */
$(document).on("change", "#cfg_logo", function () {
  var archivo = this.files && this.files[0];
  if (archivo) document.getElementById("cfg_logo_vista").src = URL.createObjectURL(archivo);
});
