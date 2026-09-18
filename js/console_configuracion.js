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
    if (r.correo) Pintar_Correo(r.correo);
    if (r.dni) Pintar_Dni(r.dni);
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

function Ayuda_Modelo(extra) {
  var proveedor = document.getElementById("cfg_ia_proveedor").value;
  var base = proveedor === "gemini"
    ? "La clave se obtiene en Google AI Studio."
    : "La clave se obtiene en platform.openai.com (empieza con sk-).";
  document.getElementById("cfg_ia_ayuda_modelo").textContent =
    (extra ? extra + " " : "Use el botón de la derecha para ver los modelos que admite la clave. ") + base;
}

/**
 * Pregunta al proveedor qué modelos admite la clave y los ofrece en la lista.
 * Los proveedores retiran modelos (a gemini-1.5-flash le pasó) y cada cuenta
 * tiene acceso a unos distintos, así que conviene verlos y no escribirlos.
 */
function Cargar_Modelos() {
  var caja = document.getElementById("cfg_ia_resultado");
  caja.innerHTML = '<div class="alert alert-secondary mb-0"><i class="fas fa-spinner fa-spin"></i> Consultando los modelos disponibles...</div>';
  $.ajax({
    url: "../controller/configuracion/controlador_modelos.php",
    type: "POST",
    dataType: "json",
    data: {
      proveedor: document.getElementById("cfg_ia_proveedor").value,
      clave: document.getElementById("cfg_ia_clave").value.trim(),
    },
  }).done(function (r) {
    if (r.status !== "ok") {
      caja.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle"></i> ' +
        escaparConfig(r.mensaje) + "</div>";
      return;
    }
    var lista = "";
    (r.modelos || []).forEach(function (m) {
      lista += '<option value="' + escaparConfig(m) + '"></option>';
    });
    document.getElementById("cfg_lista_modelos").innerHTML = lista;
    Ayuda_Modelo((r.modelos || []).length + " modelos disponibles en la lista.");
    caja.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-check-circle"></i> ' +
      "Modelos disponibles: " + escaparConfig((r.modelos || []).slice(0, 8).join(", ")) +
      ((r.modelos || []).length > 8 ? " y otros." : "") + "</div>";
  }).fail(function (x) {
    caja.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-times-circle"></i> ' +
      escaparConfig((x.responseJSON && x.responseJSON.mensaje) || "No se pudieron consultar los modelos") + "</div>";
  });
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

/* =====================================================================
 * Correo saliente y consulta de DNI (migración 027)
 *
 * Antes solo se cambiaban editando archivos en el servidor, y el token del
 * DNI estaba escrito en el código fuente. Los secretos (contraseña SMTP y
 * token) nunca llegan al navegador: se muestran enmascarados y solo se
 * reemplazan si se escribe uno nuevo.
 * ===================================================================== */

function Caja_Resultado(id, clase, icono, html) {
  document.getElementById(id).innerHTML =
    '<div class="alert alert-' + clase + ' mb-0"><i class="fas ' + icono + '"></i> ' + html + "</div>";
}

function Pintar_Correo(c) {
  document.getElementById("cfg_smtp_activo").checked = !!c.activo;
  document.getElementById("cfg_smtp_host").value = c.host || "";
  document.getElementById("cfg_smtp_puerto").value = c.puerto || "";
  document.getElementById("cfg_smtp_seguridad").value = c.seguridad || "";
  document.getElementById("cfg_smtp_usuario").value = c.usuario || "";
  document.getElementById("cfg_smtp_nombre").value = c.nombre || "";
  document.getElementById("cfg_smtp_correo").value = c.correo || "";
  document.getElementById("cfg_smtp_clave").value = "";
  document.getElementById("cfg_quitar_smtp").checked = false;
  document.getElementById("cfg_bloque_quitar_smtp").hidden = !c.tiene_clave;
  document.getElementById("cfg_smtp_clave_estado").innerHTML = c.tiene_clave
    ? "Hay una contraseña guardada. Déjelo vacío para conservarla."
    : "No hay contraseña guardada.";

  // De dónde sale lo que se ve: si viene del archivo, todavía no se guardó en el panel
  var origen = document.getElementById("cfg_smtp_origen");
  if (c.origen === "archivo") {
    origen.innerHTML = '<div class="alert alert-info py-2 mb-0"><small><i class="fas fa-file-code"></i> ' +
      "Estos datos vienen del archivo <code>config/config_email.php</code> del servidor. " +
      "Al guardar aquí pasan al panel y el archivo deja de usarse.</small></div>";
  } else if (c.origen === "ninguno") {
    origen.innerHTML = '<div class="alert alert-warning py-2 mb-0"><small><i class="fas fa-exclamation-triangle"></i> ' +
      "El correo no está configurado: el sistema no está avisando a nadie.</small></div>";
  } else {
    origen.innerHTML = "";
  }
}

/** Al cambiar el cifrado se propone el puerto habitual, si no se escribió otro. */
function Cambio_Cifrado_SMTP() {
  var puerto = document.getElementById("cfg_smtp_puerto");
  var cifrado = document.getElementById("cfg_smtp_seguridad").value;
  var habituales = { ssl: "465", tls: "587", "": "25" };
  if (!puerto.value || ["465", "587", "25"].indexOf(puerto.value) !== -1) {
    puerto.value = habituales[cifrado];
  }
}

function DatosCorreo() {
  return {
    activo: document.getElementById("cfg_smtp_activo").checked ? 1 : "",
    host: document.getElementById("cfg_smtp_host").value.trim(),
    puerto: document.getElementById("cfg_smtp_puerto").value,
    seguridad: document.getElementById("cfg_smtp_seguridad").value,
    usuario: document.getElementById("cfg_smtp_usuario").value.trim(),
    clave: document.getElementById("cfg_smtp_clave").value,
    nombre: document.getElementById("cfg_smtp_nombre").value.trim(),
    correo: document.getElementById("cfg_smtp_correo").value.trim(),
    quitar_clave: document.getElementById("cfg_quitar_smtp").checked ? 1 : "",
  };
}

function Guardar_Correo() {
  var datos = DatosCorreo();
  if (!datos.host || !datos.puerto) {
    return Swal.fire("Mensaje de Advertencia", "Indique el servidor y el puerto", "warning");
  }
  $.ajax({
    url: "../controller/configuracion/controlador_guardar_correo.php",
    type: "POST", dataType: "json", data: datos,
  }).done(function () {
    Swal.fire("Mensaje de Confirmación", "Configuración del correo guardada", "success");
    document.getElementById("cfg_smtp_resultado").innerHTML = "";
    Cargar_Configuracion();
  }).fail(function (x) {
    Swal.fire("Mensaje de Advertencia", (x.responseJSON && x.responseJSON.mensaje) || "No se pudo guardar", "warning");
  });
}

function Probar_Correo() {
  var datos = DatosCorreo();
  datos.destino = document.getElementById("cfg_smtp_destino").value.trim();
  if (!datos.destino) {
    return Swal.fire("Mensaje de Advertencia", "Escriba a qué correo enviar la prueba", "warning");
  }
  Caja_Resultado("cfg_smtp_resultado", "secondary", "fa-spinner fa-spin", "Enviando...");
  $.ajax({
    url: "../controller/configuracion/controlador_probar_correo.php",
    type: "POST", dataType: "json", data: datos,
  }).done(function (r) {
    Caja_Resultado("cfg_smtp_resultado", "success", "fa-check-circle",
      "<b>Correo enviado</b> a " + escaparConfig(r.destino) + ". Revise la bandeja (y la de spam).");
  }).fail(function (x) {
    Caja_Resultado("cfg_smtp_resultado", "danger", "fa-times-circle",
      "<b>No se pudo enviar</b><br>" + escaparConfig((x.responseJSON && x.responseJSON.mensaje) || "La prueba falló"));
  });
}

function Pintar_Dni(d) {
  document.getElementById("cfg_dni_activo").checked = !!d.activo;
  document.getElementById("cfg_dni_token").value = "";
  document.getElementById("cfg_quitar_dni").checked = false;
  document.getElementById("cfg_bloque_quitar_dni").hidden = !d.tiene_token;
  document.getElementById("cfg_dni_token_estado").innerHTML = d.tiene_token
    ? "Hay un token guardado (" + escaparConfig(d.token) + "). Déjelo vacío para conservarlo."
    : "No hay token guardado: el nombre del remitente se escribe a mano.";
}

function Guardar_Dni() {
  $.ajax({
    url: "../controller/configuracion/controlador_guardar_dni.php",
    type: "POST", dataType: "json",
    data: {
      activo: document.getElementById("cfg_dni_activo").checked ? 1 : "",
      token: document.getElementById("cfg_dni_token").value.trim(),
      quitar_token: document.getElementById("cfg_quitar_dni").checked ? 1 : "",
    },
  }).done(function () {
    Swal.fire("Mensaje de Confirmación", "Configuración de la consulta de DNI guardada", "success");
    document.getElementById("cfg_dni_resultado").innerHTML = "";
    Cargar_Configuracion();
  }).fail(function (x) {
    Swal.fire("Mensaje de Advertencia", (x.responseJSON && x.responseJSON.mensaje) || "No se pudo guardar", "warning");
  });
}

function Probar_Dni() {
  Caja_Resultado("cfg_dni_resultado", "secondary", "fa-spinner fa-spin", "Consultando...");
  $.ajax({
    url: "../controller/configuracion/controlador_probar_dni.php",
    type: "POST", dataType: "json",
    data: {
      token: document.getElementById("cfg_dni_token").value.trim(),
      dni: document.getElementById("cfg_dni_prueba").value.trim(),
    },
  }).done(function (r) {
    if (r.encontrado) {
      Caja_Resultado("cfg_dni_resultado", "success", "fa-check-circle",
        "<b>Consulta correcta</b><br>" + escaparConfig(r.nombre));
    } else {
      Caja_Resultado("cfg_dni_resultado", "success", "fa-check-circle",
        "<b>Conexión correcta</b><br>" + escaparConfig(r.mensaje));
    }
  }).fail(function (x) {
    Caja_Resultado("cfg_dni_resultado", "danger", "fa-times-circle",
      "<b>No se pudo consultar</b><br>" + escaparConfig((x.responseJSON && x.responseJSON.mensaje) || "La prueba falló"));
  });
}
