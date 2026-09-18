# Ficha del producto

**SISTRAMITEDOC — Sistema de Trámite Documentario** · Versión 2.0
Producto de JCM Digital & AI Consulting

---

## Qué es

Un sistema web que reemplaza el expediente en papel de una entidad pública. Registra cada documento
que entra o se produce, lo hace circular entre las áreas, controla los plazos, permite firmar
digitalmente y deja constancia de cada paso. El ciudadano presenta sus documentos y sigue su trámite
por internet, sin acudir a la entidad.

Está pensado para la realidad de las entidades públicas peruanas: numeración de expedientes por año,
plazos en días hábiles con los feriados nacionales, observación y subsanación de documentos, firma
digital con certificados y consulta de DNI.

## Qué problemas resuelve

| Hoy, con papel | Con SISTRAMITEDOC |
| --- | --- |
| Nadie sabe en qué área está un expediente | El recorrido completo se ve como un diagrama, paso a paso |
| Los plazos se vencen sin que nadie lo note | Semáforo en días hábiles en cada bandeja y en el tablero |
| El ciudadano tiene que ir a preguntar | Consulta su trámite por internet con su N° de expediente y DNI |
| Un documento incompleto se rechaza y se pierde | Se observa, el ciudadano lo corrige en línea y el trámite sigue |
| No queda registro de quién hizo qué | Bitácora de cada acción, con usuario, fecha y hora |
| Numerar oficios a mano produce saltos y repetidos | Cada área numera sola, por tipo de documento y año |
| Las firmas se hacen en papel y se escanean | Firma digital sobre el PDF, verificable por cualquiera con un código QR |

## Módulos

### Para el ciudadano

- **Mesa de partes virtual**: presenta su documento y anexos en PDF a cualquier hora, y recibe su cargo de recepción.
- **Seguimiento en línea**: consulta el estado y el recorrido de su trámite.
- **Subsanación en línea**: si su trámite es observado, sube lo que falta desde la misma página.
- **Validación de firmas**: comprueba con el código del sello que un documento firmado es auténtico.

### Para el personal de la entidad

- **Registro de trámites**: internos y externos, con consulta de DNI y número automático del documento.
- **Bandeja de recibidos**: aceptar, rechazar, observar, derivar y finalizar.
- **Derivación a varias áreas**: un área responsable, copias para conocimiento y pedidos de atención con plazo.
- **Documentos enviados**: todo lo que el área derivó.
- **Rastreo**: el recorrido de cada expediente como diagrama de flujo y como detalle.
- **Firma digital**: al registrar, al derivar y al responder, con certificado digital.
- **Tablero**: indicadores y trámites que requieren atención, para cada área.
- **Asistente con inteligencia artificial**: responde preguntas sobre los trámites en lenguaje natural.
- **Reportes**: por fechas y área, por estado, por tipo de documento, y de plazos y productividad; exportables a PDF, Excel y CSV.

### Para el administrador

- Usuarios con cinco roles, empleados, áreas, tipos de documento, feriados.
- Comunicados internos dirigidos, con acuse de lectura.
- Bitácora de auditoría.
- Configuración de la institución, el correo, el asistente y la consulta de DNI, desde el panel.

## Lo que lo distingue

- **Firma digital verificable**: el PDF firmado lleva un sello con código y QR; cualquiera puede validarlo en línea sin entrar al sistema. Adobe Reader reconoce la firma.
- **Plazos reales**: se cuentan en días hábiles, sin fines de semana ni feriados, y los documentos que llegan fuera del horario de atención se consideran presentados el siguiente día hábil.
- **Asistente con IA que solo lee**: contesta preguntas como «¿cuántos trámites de Contabilidad se vencieron en agosto?», sin poder modificar nada y respetando lo que cada área puede ver.
- **Configurable por institución**: nombre, sigla, logo, color, horario de atención y siglas de cada área, sin tocar código.
- **Todo queda registrado**: cada registro, derivación, firma, observación y cambio de configuración.

## Requisitos para instalarlo

| Recurso | Mínimo |
| --- | --- |
| Servidor | Apache 2.4 (probado en Windows con XAMPP; en Linux se verifica al instalar) |
| Lenguaje | PHP 8.2 con las extensiones openssl, curl, gd, mbstring, pdo_mysql, fileinfo y zip |
| Base de datos | MariaDB 10.4 o superior (las migraciones usan sintaxis de MariaDB; MySQL no es compatible) |
| Seguridad | Certificado HTTPS (obligatorio en producción) |
| Usuarios | Navegador actualizado; funciona también en el celular |

El detalle está en [Instalación y despliegue](04-instalacion-y-despliegue.md).

## Servicios externos (opcionales)

El sistema funciona sin ellos, con estas funciones desactivadas:

| Servicio | Para qué | Costo |
| --- | --- | --- |
| OpenAI (ChatGPT) o Google (Gemini) | Asistente con IA | Pago por uso, a cargo de la entidad |
| Correo SMTP | Avisos por correo a ciudadanos y áreas | Según el proveedor de correo de la entidad |
| apis.net.pe | Consulta de DNI (RENIEC) | Por plan, a cargo de la entidad |
| Certificados digitales | Firma digital con plena validez legal | Uno por firmante, emitido por una entidad acreditada ante INDECOPI |

## Qué incluye la entrega

- El sistema instalado y configurado con los datos de la entidad. `[COMPLETAR: si la instalación la hace el proveedor o la entidad]`
- Esta documentación completa.
- Capacitación según el [Plan de capacitación](09-plan-de-capacitacion.md).
- `[COMPLETAR: período de garantía]`
- `[COMPLETAR: soporte técnico: canales, horario y tiempos de respuesta]`
- `[COMPLETAR: si incluye migración de expedientes existentes]`

## Limitaciones conocidas de esta versión

Para que la entidad decida con información completa:

- **Firma con DNI electrónico o token (Firma Perú)**: la opción aparece en pantalla pero todavía no firma. Hoy se firma con certificado en archivo (.pfx o .p12).
- **Consulta de RUC (SUNAT)**: no está disponible; el RUC y la razón social se escriben a mano.
- **Documentos generados en el sistema**: el sistema no redacta oficios a partir de plantillas; se sube el PDF ya redactado.
- **Aplicación móvil**: no hay aplicación nativa; el sistema web se adapta al celular.
- **Seguridad y datos personales**: los PDF pueden descargarse por su dirección directa sin iniciar sesión, el portal no muestra todavía un aviso de privacidad y no hay segundo factor de autenticación. El detalle está en [Seguridad y datos personales](08-seguridad-y-datos-personales.md#6-limitaciones-conocidas).

## Condiciones comerciales

| Concepto | Detalle |
| --- | --- |
| Modalidad | `[COMPLETAR: licencia, suscripción o desarrollo a medida]` |
| Precio | `[COMPLETAR]` |
| Forma de pago | `[COMPLETAR]` |
| Plazo de implementación | `[COMPLETAR]` |
| Garantía | `[COMPLETAR]` |
| Soporte y mantenimiento | `[COMPLETAR]` |

## Contacto

`[COMPLETAR: nombre, correo, teléfono y sitio web]`
