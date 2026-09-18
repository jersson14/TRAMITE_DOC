# Notas de versión

**SISTRAMITEDOC**

---

## Versión 2.0 · Septiembre de 2026

Renovación integral del sistema: seguridad, circulación de documentos, firma digital, portal
ciudadano, configuración desde el panel y documentación completa.

### Para actualizar desde la versión 1

Respalde la base y los documentos, y aplique las migraciones **001 a 029** de
`database/migraciones/` en orden, con `--default-character-set=utf8mb4`. Detalle en
[Instalación y despliegue](04-instalacion-y-despliegue.md#3-actualizar-una-instalación-existente).
Una instalación nueva no necesita migraciones: use `database/instalacion/`.

### Nuevo

**Circulación de documentos**

- Derivación a un área responsable, con **copias** para conocimiento y pedidos de **atención** a
  otras áreas, cada uno con su plazo.
- **Acuse de recepción**: queda quién recibió cada envío y cuándo.
- **Observar y subsanar**: el área observa un trámite externo con un plazo y el ciudadano sube la
  corrección desde el portal, sin ir a la institución.
- **Semáforo de plazos en días hábiles**, descontando fines de semana y feriados.
- **Número de documento automático por área**, tipo y año (`001-2026-DIRESA-APURÍMAC/CONT`),
  editable, sin números repetidos aunque dos personas registren a la vez.
- **Procedencia interna o externa** determinada por el remitente.
- Varios **anexos** por trámite.
- Filtros de búsqueda en las bandejas.

**Firma digital**

- Firma **PAdES** con certificado .pfx/.p12, con sello visible y código QR.
- Se firma donde se crea el documento: al registrar, al derivar y al responder una atención.
- Un solo botón para firmar varios archivos a la vez, y cofirma.
- **Verificación** de las firmas que traen los documentos externos.
- Página pública para **validar una firma** con el código o el QR del sello.

**Portal ciudadano**

- **Cargo de recepción** en PDF con QR.
- Consulta por N° de expediente y DNI.
- Correo de confirmación con el N° de expediente y el enlace de seguimiento.
- Lo presentado fuera del horario de atención cuenta desde el siguiente día hábil.

**Rastreo**

- El recorrido se dibuja como **diagrama de flujo**, empezando por el origen real: ciudadano,
  entidad externa o área que lo remitió.

**Tablero, reportes y avisos**

- Tablero de indicadores para el administrador y para cada área.
- Reporte de **plazos y productividad por área**.
- Exportación de reportes a PDF, Excel y CSV.
- Notificaciones de pendientes con enlace, sin recargar la página.
- **Comunicados** internos dirigidos, con imagen y acuse de lectura.

**Asistente con IA**

- Responde en lenguaje natural con los datos del sistema, solo lectura y dentro de lo que el usuario
  puede ver. Compatible con ChatGPT (OpenAI) y Gemini (Google).

**Administración**

- **Cinco roles**: Administrador, Jefe de Área, Secretario(a), Mesa de Partes y Especialista.
- Configuración desde el panel: datos de la institución, horario, logo y color, correo saliente,
  consulta de DNI y asistente de IA, con prueba antes de guardar.
- Pantalla de **feriados**.
- **Bitácora** consultable de todas las acciones.
- Sigla por área para la numeración de documentos.
- Cada módulo con su propia dirección: F5 y Atrás/Adelante vuelven al mismo lugar.
- Instalador para una base nueva (`database/instalacion/`).

### Corregido

- Rastreo, orden del seguimiento y fechas en español.
- Ticket y hojas de envío PDF, con expediente y QR.
- Documentos Enviados, y acceso al historial limitado por área.
- Copias visibles para las áreas y estados de los trámites.
- Exportación de reportes, que descargaba archivos vacíos.
- El portal registraba los trámites a nombre de un usuario fijo; ahora quedan sin usuario, como
  corresponde a lo presentado por un ciudadano.
- Nombres de feriados con tildes dañadas.
- Menú vacío para los roles nuevos.
- Nombres de las acciones del trámite unificados (de 18 a 12).

### Seguridad

- Sesiones endurecidas, protección CSRF y permisos validados en el servidor.
- Límites de intentos en el ingreso y en todos los servicios públicos.
- Validación de archivos por su contenido real y nombres generados por el servidor.
- Carpetas de documentos sin ejecución de código.
- Las claves de servicios externos no salen del servidor.
- Retirados los scripts de consulta de DNI/RUC y de prueba de correo que quedaban expuestos.
- Los PDF de los trámites ya no se descargan por su dirección directa: el sistema los entrega solo
  con sesión y a las áreas que intervinieron.
- El asistente de IA no lee DNI, correos, celulares ni direcciones, y los nombres de personas se
  ocultan antes de enviar datos al proveedor. Tampoco puede leer las contraseñas con un `SELECT *`.
- **Aviso de privacidad** en el portal, obligatorio para registrar y editable desde el panel.

### Limitaciones conocidas

Detalladas en [Seguridad y datos personales](08-seguridad-y-datos-personales.md#6-limitaciones-conocidas):

- El aviso de privacidad trae un texto base que el área legal debe revisar.
- Sin segundo factor de autenticación ni política de contraseñas.
- La firma con DNI electrónico (Firma Perú) está preparada pero no habilitada.
- Solo compatible con MariaDB.
