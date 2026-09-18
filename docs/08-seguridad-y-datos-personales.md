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
| **OpenAI** o **Google** (asistente) | La pregunta y hasta 40 filas del resultado, **sin identificadores, datos de contacto ni nombres de personas** (ver abajo) | En cada pregunta al asistente | Desactivar el asistente |

**Qué protege el asistente de IA.** OpenAI y Google procesan los datos en servidores fuera del Perú.
Para que los datos personales no salgan del servidor:

- **Nunca se leen** el DNI, el celular, el correo ni la dirección de los remitentes; el DNI, el correo
  y el celular del personal; el DNI de los firmantes; las direcciones IP ni las contraseñas. Una
  consulta que los nombre se rechaza, y si llegaran por un `SELECT *` se quitan del resultado antes
  de usarlo.
- **Los nombres de personas se reemplazan** por `[persona 1]`, `[persona 2]`… en lo que se envía a
  la IA para redactar. Quien preguntó sí ve los nombres en la tabla del chat, que se arma en el
  servidor de la institución.
- Lo que el propio usuario **escriba en su pregunta** sí se envía. Si pregunta por el nombre de una
  persona, ese nombre llega al proveedor.

Aun así, el área legal debe evaluar el uso del asistente antes de activarlo. Si no se autoriza, se
deja desactivado: el chat sigue respondiendo lo básico sin enviar nada afuera.

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
| Aviso de privacidad | Enlazado en el registro y el seguimiento. Sin aceptarlo no se puede registrar un trámite |

### 3.3 Archivos

| Medida | Detalle |
| --- | --- |
| Validación de tipo | Por el contenido real del archivo, no por su extensión |
| Nombre del archivo | Lo genera el servidor; no se usa el nombre que envía el usuario |
| Carpetas de documentos | Cerradas al acceso web: no se pueden abrir, listar ni ejecutar |
| Acceso a los archivos | Cada archivo lo entrega el sistema tras comprobar la sesión y que el área del usuario haya intervenido en el trámite (el administrador ve todos). Conocer la dirección de un PDF no basta para descargarlo |

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
| **Informar a los titulares** | El portal muestra un **aviso de privacidad** que el ciudadano debe aceptar para registrar (el servidor lo exige). Trae un texto base armado con los datos de la institución; **el área legal debe revisarlo** y guardar su versión en Institución y asistente → Aviso de privacidad |
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
- **Documentos sin cifrar en disco.** Los PDF se guardan tal como se subieron; su protección depende
  del acceso al servidor y de los respaldos.
- **El aviso de privacidad trae un texto base**, no uno revisado por un abogado (ver sección 5).
- **Lo que se escribe en la pregunta al asistente llega al proveedor de IA** (ver sección 2).
- **Sin pruebas de penetración independientes.** Se recomienda una antes de exponer el sistema a
  internet.

## 7. Qué hacer ante un incidente

1. **Contener:** desactivar las cuentas comprometidas; si hay sospecha sobre una clave de servicio,
   regenerarla en el proveedor y guardarla de nuevo.
2. **Revisar la bitácora:** qué cuentas, desde qué direcciones y qué hicieron.
3. **Preservar evidencia:** copia de la base de datos y de los registros del servidor.
4. **Evaluar** con el área legal si corresponde comunicarlo a los titulares o a la autoridad.
5. **Restaurar** desde el último respaldo si hubo pérdida o alteración de datos.
