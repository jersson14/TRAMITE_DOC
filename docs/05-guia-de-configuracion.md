# Guía de configuración

**SISTRAMITEDOC** · Versión 2.0 · Septiembre de 2026

Para el administrador del sistema. Todo lo que se explica aquí se hace desde el panel, entrando con el
rol **Administrador**, salvo la firma con DNI electrónico (sección 7).

**Orden recomendado para una instalación nueva:**

1. [Datos de la institución](#1-datos-de-la-institución)
2. [Áreas y sus siglas](#2-áreas-y-sus-siglas)
3. [Empleados y usuarios](#3-empleados-y-usuarios)
4. [Feriados](#4-feriados)
5. [Correo saliente](#5-correo-saliente-smtp)
6. [Consulta de DNI](#6-consulta-de-dni-reniec)
7. [Firma digital](#7-firma-digital)
8. [Asistente con inteligencia artificial](#8-asistente-con-inteligencia-artificial)

Ninguno de los servicios externos (5, 6 y 8) es obligatorio. Sin ellos el sistema funciona igual:
no se envían correos, el nombre del remitente se escribe a mano y el chat responde lo básico.

---

## 1. Datos de la institución

Ruta: **Menú → Institución y asistente → Datos de la institución**

| Campo | Para qué se usa |
| --- | --- |
| Nombre de la institución | Encabezados, reportes, cargos de recepción y correos |
| Sigla o nombre corto | Barra del sistema y numeración de documentos: `001-2026-`**`DIRESA-APURÍMAC`**`/CONT` |
| Color institucional | Color principal de la interfaz |
| Correo, código, teléfono, dirección | Datos de contacto en el portal y los documentos |
| Recepción desde / hasta | Horario de atención de lunes a viernes |
| Logo | JPG, PNG o WEBP, hasta 5 MB |

**El horario de atención afecta los plazos.** Un documento presentado por el portal fuera de ese
horario —de noche, en fin de semana o en feriado— se considera presentado el siguiente día hábil a la
hora de apertura, y los plazos corren desde ahí.

## 2. Áreas y sus siglas

Ruta: **Menú → Área**

Registre cada área de la institución. La **sigla** es la que va al final del número de sus
documentos:

```text
OFICIO N° 001-2026-DIRESA-APURÍMAC/OGA
                                   └── sigla del área
```

Si deja la sigla vacía, el sistema la deduce del nombre: con varias palabras usa sus iniciales
(«RECURSOS HUMANOS» → «RH») y con una sola, sus primeras cuatro letras («CONTABILIDAD» → «CONT»). La
tabla de áreas muestra en gris las siglas deducidas.

> **Revise las siglas deducidas.** Dos áreas pueden deducir la misma: «PLANIFICACIÓN» y
> «PLANEAMIENTO» darían ambas «PLAN». Cada área igual tiene su propia secuencia de números, pero los
> documentos impresos se verían iguales. Asigne siglas distintas.

> **No borre ni renombre el área número 1.** Es MESA DE PARTES, y ahí entra todo lo que se presenta
> por el portal ciudadano.

## 3. Empleados y usuarios

Ruta: **Menú → Empleado** y **Menú → Usuario**

Primero se registra a la persona como empleado y después se le crea su usuario. Dos datos del empleado
importan especialmente:

| Dato | Por qué importa |
| --- | --- |
| **DNI** | Es el que se compara con el certificado digital al firmar. Debe tener 8 dígitos y ser el real: con un DNI incorrecto, esa persona no podrá firmar. |
| **Correo** | A ese correo llegan los avisos de los trámites que recibe su área. Sin correo, no recibe avisos. |

Cada usuario tiene un **rol** y un **área**. Lo que puede hacer cada rol está en el
[Manual del administrador](07-manual-del-administrador.md#2-usuarios-y-roles).

## 4. Feriados

Ruta: **Menú → Feriados**

Los plazos se cuentan en días hábiles, sin sábados, domingos ni los feriados de esta lista. El sistema
trae los feriados nacionales; **agregue cada año**:

- Jueves y Viernes Santo del año siguiente, que cambian de fecha.
- Los feriados regionales de su jurisdicción.
- Los días no laborables que decrete el Gobierno.

Un feriado que falte hace que el sistema cuente ese día como hábil y los plazos venzan antes.

## 5. Correo saliente (SMTP)

Ruta: **Menú → Institución y asistente → Correo saliente (SMTP)**

Con el correo configurado el sistema avisa:

| Cuándo | A quién |
| --- | --- |
| Un ciudadano registra un trámite | Al ciudadano, con su N° de expediente y el enlace de seguimiento |
| Un área observa un trámite | Al ciudadano, con lo que debe corregir y hasta cuándo |
| El ciudadano subsana | Al área que observó |
| Un trámite llega a un área | Al personal activo de esa área |

### 5.1 Datos de los proveedores comunes

| Proveedor | Servidor SMTP | Puerto | Cifrado |
| --- | --- | --- | --- |
| Gmail / Google Workspace | `smtp.gmail.com` | 587 | TLS |
| Microsoft 365 / Outlook | `smtp.office365.com` | 587 | TLS |
| Hostinger | `smtp.hostinger.com` | 465 | SSL |
| Correo propio de la entidad | Consúltelo con TI o con quien administra el dominio | | |

> **Gmail no acepta la contraseña normal de la cuenta.** Active la verificación en dos pasos y cree
> una **contraseña de aplicación** en la seguridad de la cuenta de Google; esa es la que va en el
> panel. En Microsoft 365, el administrador de la cuenta puede tener que habilitar el envío por SMTP
> para ese buzón.

### 5.2 Campos

| Campo | Qué poner | Ejemplo |
| --- | --- | --- |
| Enviar correos | Marcado para que salgan los avisos | Marcado |
| Servidor SMTP | El de su proveedor | `smtp.gmail.com` |
| Cifrado | SSL o TLS; al elegirlo se propone el puerto | TLS |
| Puerto | 465 para SSL, 587 para TLS | `587` |
| Usuario | Casi siempre, el correo completo | `tramite@institucion.gob.pe` |
| Contraseña | La del correo o la de aplicación. Vacío = conserva la guardada | |
| Nombre del remitente | Lo que verá quien reciba el aviso | `Mesa de Partes - DIRESA` |
| Correo del remitente | Normalmente el mismo del usuario | `tramite@institucion.gob.pe` |

### 5.3 Pasos

1. Complete los campos.
2. Escriba su propio correo en **Enviar correo de prueba a** y pulse **Enviar prueba**. La prueba usa
   lo escrito en el formulario, aunque todavía no esté guardado.
3. Revise su bandeja y la carpeta de spam.
4. Si llegó, pulse **Guardar correo**.

Si la pantalla indica que los datos vienen de `config/config_email.php`, el correo se configuró en el
servidor antes de que existiera esta sección. Funciona igual. Al guardar desde el panel, el panel
pasa a mandar y el archivo deja de usarse.

## 6. Consulta de DNI (RENIEC)

Ruta: **Menú → Institución y asistente → Consulta de DNI (RENIEC)**

Al registrar un trámite basta con escribir el DNI del remitente y pulsar la búsqueda: el sistema
completa nombres y apellidos. Funciona en el registro de mesa de partes y en el portal ciudadano.

El sistema **no se conecta a RENIEC directamente**: usa **apis.net.pe**, un servicio que consulta el
padrón y cobra por consulta según el plan contratado.

1. Cree una cuenta en [apis.net.pe](https://apis.net.pe) con los datos de la entidad y elija un plan.
2. Copie el token de su panel: tiene la forma `apis-token-1234.AbCdEf…`
3. Péguelo en **Token de apis.net.pe** y pulse **Probar consulta**. Con **DNI para la prueba** en blanco, la prueba
   usa el de su propia ficha, para no consultar a un tercero sin motivo.
4. Pulse **Guardar**.

Como el portal es público, cada dirección de internet puede hacer como máximo **20 consultas cada
10 minutos**, para que nadie use el sistema para gastar el saldo de la entidad.

## 7. Firma digital

### 7.1 Certificados de los firmantes

Cada persona que firma necesita **su propio certificado digital** en archivo (.pfx o .p12), emitido por
una entidad de certificación acreditada ante INDECOPI (RENIEC o un proveedor privado acreditado).
El sistema no emite ni guarda certificados: cada firmante lo carga al firmar, junto con su
contraseña, y ambos se descartan al terminar.

Para que una persona pueda firmar:

- [ ] Su rol lo permite: **Administrador**, **Jefe de Área** o **Especialista**.
- [ ] El **DNI de su ficha de empleado** es el mismo que figura en su certificado.
- [ ] Su certificado está **vigente**.

> **Si al firmar aparece «la contraseña es incorrecta» y la contraseña es la correcta**, el certificado
> probablemente está en un formato de cifrado antiguo (RC2) que el sistema no puede leer. Pida a la
> certificadora o a TI que lo reexporte con cifrado AES.

**Certificados de prueba.** Para capacitación se pueden usar certificados autofirmados: el sistema
firma con ellos, pero marca la firma con el aviso «certificado autofirmado», porque **no tienen
validez legal**. No los use en documentos reales.

### 7.2 Firma con DNI electrónico o token (Firma Perú)

**En esta versión todavía no firma.** La opción aparece en pantalla con un aviso. La configuración
está preparada para cuando la integración esté terminada:

1. La entidad solicita sus credenciales de Firma Perú a la Secretaría de Gobierno y Transformación
   Digital de la PCM.
2. En el servidor, se copia `config/firmaperu.example.php` como `config/firmaperu.php` y se
   completan `client_id`, `client_secret` y `habilitado => true`.
3. Cada firmante instala el Firmador de Firma Perú en su computadora, con su lector de DNIe o su token.

## 8. Asistente con inteligencia artificial

Ruta: **Menú → Institución y asistente → Asistente del chat**

El asistente responde preguntas del personal sobre los trámites («¿cuántos expedientes tengo
pendientes?», «¿dónde está el EXP-2026-000048?») con los datos del sistema. Solo lee: no puede
modificar nada, y cada usuario solo obtiene respuestas sobre lo que su área puede ver.

### 8.1 Obtener la clave

**ChatGPT (OpenAI)**, el proveedor predeterminado:

1. Entre a [platform.openai.com](https://platform.openai.com) con la cuenta de la entidad.
2. En **Billing**, cargue saldo: la API se paga por uso.
3. En **API keys**, pulse **Create new secret key** y póngale un nombre, por ejemplo
   `sistramite-produccion`.
4. Copie la clave en ese momento: empieza con `sk-` y no se vuelve a mostrar.

**Gemini (Google)**, como alternativa: genere la clave en
[aistudio.google.com](https://aistudio.google.com), opción **Get API key**.

### 8.2 Campos

| Campo | Qué poner | Valor sugerido |
| --- | --- | --- |
| Asistente inteligente activo | Desmarcado, el chat responde solo lo básico | Marcado |
| Proveedor | ChatGPT (OpenAI) o Gemini (Google) | ChatGPT |
| Modelo | El botón ↻ consulta qué modelos admite su clave | `gpt-4.1-mini` o `gemini-2.5-flash` |
| Clave de la API | Vacío = conserva la guardada | |
| Consultas por minuto | Tope por persona | 10 |
| Por día | Tope diario por persona | 100 |

Pulse **Probar conexión** y luego **Guardar asistente**.

## 9. Cómo se protegen las claves

La clave del asistente, la contraseña del correo y el token del DNI:

- **Nunca vuelven a mostrarse** una vez guardadas; la pantalla solo indica que existen, y en las
  claves de API sus últimos cuatro caracteres (`••••••••a3Kq`).
- **Se conservan** si deja el campo vacío al guardar.
- **Se quitan** marcando «Quitar la clave guardada» (o la contraseña, o el token) y guardando.
- **Cada cambio queda en la bitácora**, sin el valor de la clave.
- Solo el **Administrador** puede verlas y cambiarlas.

Si una clave pudo filtrarse, regenérela en el proveedor, pegue la nueva y guarde.

## 10. Mensajes de error frecuentes

| Mensaje | Qué hacer |
| --- | --- |
| `SMTP Error: Could not authenticate` | Usuario o contraseña incorrectos. En Gmail, use una contraseña de aplicación |
| `SMTP Error: Could not connect to SMTP host` | Revise servidor y puerto: 465 con SSL, 587 con TLS. El servidor puede estar bloqueando la salida por ese puerto |
| El correo de prueba se envió pero no llega | Revise spam. Si el remitente no coincide con el usuario, muchos proveedores descartan el mensaje |
| El servicio rechazó el token | El token de apis.net.pe no es válido, venció o se regeneró |
| Se alcanzó el límite de consultas del servicio | Se agotó el plan de apis.net.pe |
| Demasiadas consultas de DNI seguidas | Límite del sistema: 20 cada 10 minutos. Espere unos minutos |
| El certificado pertenece a otra persona | El DNI del certificado no es el de la ficha del empleado. Corrija la ficha o use el certificado propio |
| El certificado venció | Renueve el certificado con la certificadora |
| El asistente responde solo lo básico | Revise que esté activo, que tenga clave y que la cuenta del proveedor tenga saldo |
