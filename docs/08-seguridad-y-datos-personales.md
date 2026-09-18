# Seguridad y datos personales

**SISTRAMITEDOC** · Versión 2.0 · Septiembre de 2026

Para el área legal, el oficial de datos personales y TI de la institución. Describe qué datos trata
el sistema, cómo los protege y qué le corresponde hacer a la institución.

> **Este documento no es una opinión legal ni una certificación de cumplimiento.** Describe cómo
> funciona el sistema para que el área legal de la institución evalúe el cumplimiento de la
> **Ley N° 29733** (Protección de Datos Personales) y su reglamento, la **Ley N° 27269** (Firmas y
> Certificados Digitales) y demás normas aplicables. El cumplimiento depende también de cómo la
> institución instale, configure y use el sistema.

---

## 1. Datos personales que trata el sistema

| Titular | Datos | Para qué |
| --- | --- | --- |
| Ciudadano o representante | DNI, nombres, apellidos, celular, correo, dirección | Identificar al remitente, notificarle y permitirle consultar su trámite |
| Persona jurídica | RUC, razón social | Identificar a la entidad remitente |
| Personal de la institución | DNI, nombres, apellidos, celular, correo, dirección, foto, usuario, área y rol | Dar acceso, atribuir cada acción y validar su firma digital |
| Todos | Dirección IP y fecha y hora de cada acción | Bitácora de auditoría y límites contra abuso |
| Contenido | Los documentos PDF presentados y generados | El propio trámite. Pueden contener datos sensibles según el asunto |

**No se almacenan:** las contraseñas en texto legible (solo su huella con bcrypt), los certificados
digitales ni sus contraseñas (se usan para firmar y se descartan).

## 2. Servicios de terceros

Los datos salen del servidor de la institución **solo** si se activa el servicio correspondiente:

| Servicio | Qué se envía | Cuándo | Cómo evitarlo |
| --- | --- | --- | --- |
| **apis.net.pe** (consulta de DNI) | El número de DNI consultado | Al buscar un DNI al registrar | Desactivar la consulta automática |
| **Servidor de correo** configurado | Correo del destinatario, N° de expediente, asunto, texto de la observación | En cada aviso | Desactivar **Enviar correos** |
| **OpenAI** o **Google** (asistente) | La pregunta y **hasta 40 filas** del resultado de la consulta | En cada pregunta al asistente | Desactivar el asistente |

> **Atención con el asistente de IA.** Las filas enviadas al proveedor pueden incluir datos de los
> remitentes: nombres, DNI, correo, celular y dirección. OpenAI y Google procesan esos datos en
> servidores fuera del Perú, lo que constituye un **flujo transfronterizo** de datos personales.
> Antes de activar el asistente, el área legal debe evaluarlo y, si corresponde, informarlo a los
> titulares y a la Autoridad Nacional de Protección de Datos Personales. Si no se autoriza, el
> asistente se deja desactivado: el chat sigue respondiendo lo básico sin enviar nada afuera.

## 3. Medidas de seguridad implementadas

### 3.1 Acceso

| Medida | Detalle |
| --- | --- |
| Autenticación | Usuario y contraseña, guardada con bcrypt (costo 12) |
| Bloqueo por intentos | 5 intentos fallidos → 15 minutos de bloqueo por dirección IP |
| Duración de la sesión | 2 horas |
| Cookie de sesión | `HttpOnly`, `SameSite=Lax`, `Secure` bajo HTTPS |
| Permisos por rol | Cinco roles. El servidor valida el permiso en cada operación, no solo la pantalla |
| Acceso por área | Cada persona solo ve los trámites que pasaron por su área. El administrador ve todos |
| Protección CSRF | Token en todas las operaciones internas |

### 3.2 Portal público

| Medida | Detalle |
| --- | --- |
| Consulta del trámite | Exige N° de expediente **y** DNI del remitente. No muestra nombres de funcionarios ni documentos internos |
| Límites por dirección IP | Consulta de DNI: 20 cada 10 min · Consulta de trámite: 30 cada 10 min · Registro: 10 por hora · Subsanación: 10 cada 10 min · Validación de firma: 40 cada 10 min |
| Validación de firmas | Muestra quién firmó y cuándo, pero **no** el documento |

### 3.3 Archivos

| Medida | Detalle |
| --- | --- |
| Validación de tipo | Por el contenido real del archivo, no por su extensión |
| Nombre del archivo | Lo genera el servidor; no se usa el nombre que envía el usuario |
| Carpetas de documentos | No permiten ejecutar código ni listar su contenido |
| Listado de archivos | En el sistema, los archivos de un trámite solo se muestran al administrador y a las áreas por las que pasó |
| Descarga directa | **Limitación:** quien conozca la dirección exacta de un PDF puede descargarlo sin iniciar sesión (ver sección 6) |

### 3.4 Claves de servicios

La clave del asistente, la contraseña del correo y el token de DNI se guardan en la base de datos,
**nunca se envían al navegador**, solo el administrador puede cambiarlas y cada cambio queda en la
bitácora sin el valor.

### 3.5 Trazabilidad

La bitácora registra ingresos, intentos fallidos, salidas, registros, derivaciones, cambios de estado,
eliminaciones, acuses, atenciones, firmas, observaciones, subsanaciones, cambios en los
mantenimientos y cada consulta al asistente, con usuario, fecha, hora y dirección IP.

## 4. Firma digital

- Las firmas son **PAdES** (firma dentro del PDF), verificables en Adobe Acrobat y en el validador
  del propio sistema.
- Se firma con el certificado **.pfx/.p12** del propio firmante; el sistema comprueba que el DNI del
  certificado sea el de su ficha de empleado y que el certificado esté vigente.
- **La validez legal la da el certificado, no el sistema.** Para que una firma tenga los efectos de la
  Ley N° 27269, el certificado debe ser emitido por una entidad de certificación acreditada ante la
  Infraestructura Oficial de Firma Electrónica (IOFE / INDECOPI). Con certificados autofirmados o de
  prueba, el sistema firma pero la firma **no tiene validez legal**, y así lo indica.
- La firma con DNI electrónico mediante **Firma Perú** está preparada pero **no habilitada** en esta
  versión.

## 5. Responsabilidades de la institución

El sistema aporta medidas técnicas. Estas le corresponden a la institución:

| Obligación | Qué hacer |
| --- | --- |
| **Informar a los titulares** | El portal actualmente pide una declaración de veracidad, pero **no muestra un aviso de privacidad**. La institución debe redactar el aviso (finalidad, destinatarios, transferencias, plazo de conservación, cómo ejercer los derechos ARCO) para incluirlo en el portal. Incorporarlo es un cambio menor |
| Banco de datos | Evaluar la inscripción del banco de datos de trámites ante la Autoridad Nacional de Protección de Datos Personales |
| Derechos ARCO | Definir quién atiende las solicitudes de acceso, rectificación, cancelación y oposición |
| Plazo de conservación | Definir cuánto tiempo se conservan los expedientes según las normas de archivo aplicables. El sistema no borra nada automáticamente |
| HTTPS | Obligatorio en producción. Sin HTTPS, contraseñas y certificados viajan legibles |
| Respaldos | Diarios, fuera del servidor y probados (ver [Instalación y despliegue](04-instalacion-y-despliegue.md#5-respaldos)) |
| Cuentas | Una por persona, rol mínimo necesario, desactivar el mismo día que alguien se va |
| Contraseña inicial | Cambiar la del usuario `admin` en el primer ingreso |
| Servicios externos | Contratar con cuentas de la institución y evaluar sus condiciones de tratamiento de datos |
| Personal | Capacitar sobre confidencialidad y uso de las cuentas |

## 6. Limitaciones conocidas

Para una evaluación honesta, estas son las limitaciones actuales:

- **Sin segundo factor de autenticación.** El ingreso es solo con usuario y contraseña.
- **Sin política de contraseñas.** El sistema no exige longitud ni complejidad mínimas; el
  administrador debe asignar contraseñas robustas. Los usuarios no pueden cambiar su propia
  contraseña.
- **Los PDF se descargan por su dirección directa, sin sesión.** El control por área se aplica al
  mostrar los archivos, pero la carpeta de documentos es pública. Los archivos nuevos llevan un
  sufijo aleatorio difícil de adivinar; los **anteriores a esta versión** tienen nombres predecibles
  (fecha y hora). Corregirlo requiere servir los archivos a través de un controlador que verifique
  la sesión y el área. **Se recomienda hacerlo antes de exponer el sistema a internet.**
- **Documentos sin cifrar en disco.** Los PDF se guardan tal como se subieron; su protección depende
  del acceso al servidor y de los respaldos.
- **Sin aviso de privacidad en el portal** (ver sección 5).
- **El asistente de IA puede enviar datos de remitentes a terceros** (ver sección 2).
- **Sin pruebas de penetración independientes.** Se recomienda una antes de exponer el sistema a
  internet.

## 7. Qué hacer ante un incidente

1. **Contener:** desactivar las cuentas comprometidas; si hay sospecha sobre una clave de servicio,
   regenerarla en el proveedor y guardarla de nuevo.
2. **Revisar la bitácora:** qué cuentas, desde qué direcciones y qué hicieron.
3. **Preservar evidencia:** copia de la base de datos y de los registros del servidor.
4. **Evaluar** con el área legal si corresponde comunicarlo a los titulares o a la autoridad.
5. **Restaurar** desde el último respaldo si hubo pérdida o alteración de datos.
