<?php require_once __DIR__ . '/../../lib/Seguridad.php'; Seguridad::requiereVista([Seguridad::ROL_ADMIN]); ?>
<script src="../js/console_configuracion.js?rev=<?php echo time(); ?>"></script>
<link rel="stylesheet" href="../plantilla/dist/css/modern-admin-theme.css?v=<?php echo @filemtime(__DIR__ . '/../../plantilla/dist/css/modern-admin-theme.css'); ?>">

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><b>CONFIGURACIÓN</b></h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="../index.php">MENU</a></li>
          <li class="breadcrumb-item active">CONFIGURACIÓN</li>
        </ol>
      </div>
    </div>
  </div>
</div>

<div class="content">
  <div class="container-fluid">
    <div class="row">

      <!-- DATOS DE LA INSTITUCIÓN -->
      <div class="col-lg-7">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title"><i class="nav-icon fas fa-building"></i>&nbsp;&nbsp;<b>Datos de la institución</b></h3>
          </div>
          <div class="card-body">
            <small class="text-muted">
              <i class="fas fa-info-circle"></i>
              El nombre, la sigla, el logo y el color se usan en el menú, en el portal del ciudadano,
              en los tickets y en los reportes PDF.
            </small>

            <div class="row mt-3">
              <div class="col-12 form-group">
                <label for="cfg_razon">Nombre de la institución(*):</label>
                <input type="text" class="form-control" id="cfg_razon" maxlength="200">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_sigla">Sigla / nombre corto(*):</label>
                <input type="text" class="form-control" id="cfg_sigla" maxlength="60" placeholder="DIRESA Apurímac">
                <small class="text-muted">Es lo que aparece en el menú y en las páginas públicas.</small>
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_color">Color institucional(*):</label>
                <div class="d-flex align-items-center">
                  <input type="color" class="form-control cfg-color" id="cfg_color" value="#1F2358">
                  <input type="text" class="form-control ml-2" id="cfg_color_texto" maxlength="7" placeholder="#1F2358">
                </div>
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_email">Correo(*):</label>
                <input type="email" class="form-control" id="cfg_email" maxlength="200">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_codigo">Código(*):</label>
                <input type="text" class="form-control" id="cfg_codigo" maxlength="30">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_telefono">Teléfono / celular(*):</label>
                <input type="text" class="form-control" id="cfg_telefono" maxlength="20" onkeypress="return soloNumeros(event)">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_direccion">Dirección(*):</label>
                <input type="text" class="form-control" id="cfg_direccion" maxlength="250">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_hora_inicio">Recepción desde(*):</label>
                <input type="time" class="form-control" id="cfg_hora_inicio">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_hora_fin">Hasta(*):</label>
                <input type="time" class="form-control" id="cfg_hora_fin">
              </div>
              <div class="col-12">
                <small class="text-muted">
                  Lunes a viernes, sin contar feriados. Lo que llegue por la Mesa de Partes Virtual fuera de
                  ese horario se considera presentado el siguiente día hábil.
                </small>
              </div>
            </div>

            <hr>
            <div class="row align-items-center">
              <div class="col-md-4 text-center">
                <img id="cfg_logo_vista" class="cfg-logo" alt="Logo de la institución">
                <div id="cfg_logo_aviso" class="text-danger mt-2" style="font-size:.8rem" hidden>
                  <i class="fas fa-exclamation-triangle"></i> El logo guardado ya no existe en el servidor;
                  se está usando el de reserva. Suba uno nuevo.
                </div>
              </div>
              <div class="col-md-8 form-group mb-0">
                <label for="cfg_logo">Cambiar el logo <small class="text-muted">(JPG, PNG o WEBP, hasta 5 MB)</small>:</label>
                <input type="file" class="form-control-file" id="cfg_logo" accept="image/jpeg,image/png,image/webp">
                <small class="text-muted">
                  Al cambiarlo se actualiza también en los tickets, hojas de envío y reportes.
                </small>
                <input type="text" id="cfg_id_empresa" hidden>
                <input type="text" id="cfg_logo_actual" hidden>
              </div>
            </div>
          </div>
          <div class="card-footer text-right">
            <button type="button" class="btn btn-gradient-success" onclick="Guardar_Institucion()">
              <i class="fas fa-save"></i> Guardar datos de la institución
            </button>
          </div>
        </div>
      </div>

      <!-- ASISTENTE (CHATBOT) -->
      <div class="col-lg-5">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title"><i class="nav-icon fas fa-robot"></i>&nbsp;&nbsp;<b>Asistente del chat</b></h3>
          </div>
          <div class="card-body">
            <small class="text-muted">
              <i class="fas fa-info-circle"></i>
              El asistente traduce las preguntas del personal a consultas sobre los trámites y responde
              con los datos del sistema. Solo lee: nunca modifica nada, y cada consulta queda en la bitácora.
            </small>

            <div class="custom-control custom-switch mt-3">
              <input type="checkbox" class="custom-control-input" id="cfg_ia_activo">
              <label class="custom-control-label" for="cfg_ia_activo">Asistente inteligente activo</label>
            </div>
            <small class="text-muted d-block mb-3">
              Apagado, el chat sigue respondiendo lo básico (pendientes, resumen del área y búsqueda de un expediente).
            </small>

            <div class="form-group">
              <label for="cfg_ia_proveedor">Proveedor(*):</label>
              <select class="form-control" id="cfg_ia_proveedor" onchange="Cambio_Proveedor_IA()"></select>
            </div>
            <div class="form-group">
              <label for="cfg_ia_modelo">Modelo(*):</label>
              <div class="input-group">
                <input type="text" class="form-control" id="cfg_ia_modelo" maxlength="60"
                       list="cfg_lista_modelos" placeholder="gpt-4.1-mini">
                <datalist id="cfg_lista_modelos"></datalist>
                <div class="input-group-append">
                  <button class="btn btn-outline-secondary" type="button" onclick="Cargar_Modelos()"
                          title="Consultar al proveedor qué modelos admite la clave">
                    <i class="fas fa-sync-alt"></i>
                  </button>
                </div>
              </div>
              <small class="text-muted" id="cfg_ia_ayuda_modelo"></small>
            </div>
            <div class="form-group">
              <label for="cfg_ia_clave">Clave de la API:</label>
              <input type="password" class="form-control" id="cfg_ia_clave" autocomplete="new-password"
                     placeholder="Pegue aquí la clave">
              <small class="text-muted" id="cfg_ia_clave_estado"></small>
              <div class="custom-control custom-checkbox mt-2" id="cfg_bloque_quitar_clave" hidden>
                <input type="checkbox" class="custom-control-input" id="cfg_quitar_clave">
                <label class="custom-control-label" for="cfg_quitar_clave">Quitar la clave guardada</label>
              </div>
            </div>
            <div class="row">
              <div class="col-6 form-group">
                <label for="cfg_ia_minuto">Consultas por minuto:</label>
                <input type="number" class="form-control" id="cfg_ia_minuto" min="1" max="120">
              </div>
              <div class="col-6 form-group">
                <label for="cfg_ia_dia">Por día:</label>
                <input type="number" class="form-control" id="cfg_ia_dia" min="1" max="5000">
              </div>
            </div>
            <div class="col-12 px-0">
              <small class="text-muted">Por persona, para que un uso intensivo no agote la cuota del proveedor.</small>
            </div>

            <div id="cfg_ia_resultado" class="mt-3"></div>
          </div>
          <div class="card-footer text-right">
            <button type="button" class="btn btn-secondary" onclick="Probar_IA()">
              <i class="fas fa-plug"></i> Probar conexión
            </button>
            <button type="button" class="btn btn-gradient-success" onclick="Guardar_IA()">
              <i class="fas fa-save"></i> Guardar asistente
            </button>
          </div>
        </div>
      </div>

    </div>

    <!-- Migración 027: correo saliente y consulta de DNI, que antes solo se
         cambiaban editando archivos en el servidor (y el token del DNI estaba
         escrito en el código fuente). -->
    <div class="row">

      <!-- CORREO SALIENTE (SMTP) -->
      <div class="col-lg-7">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title"><i class="nav-icon fas fa-envelope"></i>&nbsp;&nbsp;<b>Correo saliente (SMTP)</b></h3>
          </div>
          <div class="card-body">
            <small class="text-muted">
              <i class="fas fa-info-circle"></i>
              Con esto el sistema envía los avisos: al ciudadano cuando registra o le observan un trámite,
              y a las áreas cuando reciben uno. Los datos los da su proveedor de correo.
            </small>
            <div id="cfg_smtp_origen" class="mt-2"></div>

            <div class="custom-control custom-switch mt-3">
              <input type="checkbox" class="custom-control-input" id="cfg_smtp_activo">
              <label class="custom-control-label" for="cfg_smtp_activo">Enviar correos</label>
            </div>
            <small class="text-muted d-block mb-3">Apagado, el sistema funciona igual pero no avisa a nadie por correo.</small>

            <div class="row">
              <div class="col-md-6 form-group">
                <label for="cfg_smtp_host">Servidor SMTP(*):</label>
                <input type="text" class="form-control" id="cfg_smtp_host" maxlength="120" placeholder="smtp.gmail.com">
              </div>
              <div class="col-md-3 form-group">
                <label for="cfg_smtp_puerto">Puerto(*):</label>
                <input type="number" class="form-control" id="cfg_smtp_puerto" min="1" max="65535" placeholder="465">
              </div>
              <div class="col-md-3 form-group">
                <label for="cfg_smtp_seguridad">Cifrado(*):</label>
                <select class="form-control" id="cfg_smtp_seguridad" onchange="Cambio_Cifrado_SMTP()">
                  <option value="ssl">SSL (465)</option>
                  <option value="tls">TLS (587)</option>
                  <option value="">Ninguno</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label for="cfg_smtp_usuario">Usuario:</label>
                <input type="text" class="form-control" id="cfg_smtp_usuario" maxlength="150" autocomplete="off"
                       placeholder="casi siempre, el mismo correo">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_smtp_clave">Contraseña:</label>
                <input type="password" class="form-control" id="cfg_smtp_clave" autocomplete="new-password"
                       placeholder="Escriba solo para cambiarla">
                <small class="text-muted" id="cfg_smtp_clave_estado"></small>
                <div class="custom-control custom-checkbox mt-1" id="cfg_bloque_quitar_smtp" hidden>
                  <input type="checkbox" class="custom-control-input" id="cfg_quitar_smtp">
                  <label class="custom-control-label" for="cfg_quitar_smtp">Quitar la contraseña guardada</label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label for="cfg_smtp_nombre">Nombre del remitente(*):</label>
                <input type="text" class="form-control" id="cfg_smtp_nombre" maxlength="120"
                       placeholder="Mesa de Partes - DIRESA">
              </div>
              <div class="col-md-6 form-group">
                <label for="cfg_smtp_correo">Correo del remitente(*):</label>
                <input type="email" class="form-control" id="cfg_smtp_correo" maxlength="150"
                       placeholder="tramite@institucion.gob.pe">
              </div>
            </div>
            <div class="form-group mb-0">
              <label for="cfg_smtp_destino">Enviar correo de prueba a:</label>
              <input type="email" class="form-control" id="cfg_smtp_destino" maxlength="150" placeholder="su correo">
              <small class="text-muted">La prueba usa lo que está escrito en el formulario, aunque no lo haya guardado.</small>
            </div>

            <div id="cfg_smtp_resultado" class="mt-3"></div>
          </div>
          <div class="card-footer text-right">
            <button type="button" class="btn btn-secondary" onclick="Probar_Correo()">
              <i class="fas fa-paper-plane"></i> Enviar prueba
            </button>
            <button type="button" class="btn btn-gradient-success" onclick="Guardar_Correo()">
              <i class="fas fa-save"></i> Guardar correo
            </button>
          </div>
        </div>
      </div>

      <!-- CONSULTA DE DNI (RENIEC vía apis.net.pe) -->
      <div class="col-lg-5">
        <div class="card card-modern">
          <div class="card-header">
            <h3 class="card-title"><i class="nav-icon fas fa-id-card"></i>&nbsp;&nbsp;<b>Consulta de DNI (RENIEC)</b></h3>
          </div>
          <div class="card-body">
            <small class="text-muted">
              <i class="fas fa-info-circle"></i>
              Al registrar un trámite, completa el nombre del remitente a partir de su DNI. El sistema no se
              conecta a RENIEC directamente: usa el servicio <b>apis.net.pe</b>, que cobra por consulta con un token.
            </small>

            <div class="custom-control custom-switch mt-3">
              <input type="checkbox" class="custom-control-input" id="cfg_dni_activo">
              <label class="custom-control-label" for="cfg_dni_activo">Consulta automática activa</label>
            </div>
            <small class="text-muted d-block mb-3">Apagada, el nombre del remitente se escribe a mano.</small>

            <div class="form-group">
              <label for="cfg_dni_token">Token de apis.net.pe:</label>
              <input type="password" class="form-control" id="cfg_dni_token" autocomplete="new-password"
                     placeholder="Pegue aquí el token">
              <small class="text-muted" id="cfg_dni_token_estado"></small>
              <div class="custom-control custom-checkbox mt-1" id="cfg_bloque_quitar_dni" hidden>
                <input type="checkbox" class="custom-control-input" id="cfg_quitar_dni">
                <label class="custom-control-label" for="cfg_quitar_dni">Quitar el token guardado</label>
              </div>
            </div>
            <div class="form-group mb-0">
              <label for="cfg_dni_prueba">DNI para la prueba:</label>
              <input type="text" class="form-control" id="cfg_dni_prueba" maxlength="8" placeholder="en blanco: el suyo">
              <small class="text-muted">Si lo deja en blanco se consulta el DNI de su propia ficha de empleado.</small>
            </div>

            <div id="cfg_dni_resultado" class="mt-3"></div>
          </div>
          <div class="card-footer text-right">
            <button type="button" class="btn btn-secondary" onclick="Probar_Dni()">
              <i class="fas fa-plug"></i> Probar consulta
            </button>
            <button type="button" class="btn btn-gradient-success" onclick="Guardar_Dni()">
              <i class="fas fa-save"></i> Guardar
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
  $(function () {
    Cargar_Configuracion();
  });
</script>
