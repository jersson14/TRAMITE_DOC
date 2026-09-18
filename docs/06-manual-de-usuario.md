# Manual de usuario

**SISTRAMITEDOC** · Versión 2.0 · Septiembre de 2026

Para el personal de la institución que usa el sistema cada día y para quien orienta a los ciudadanos.
La administración del sistema está en el [Manual del administrador](07-manual-del-administrador.md).

---

## Contenido

1. [Conceptos básicos](#1-conceptos-básicos)
2. [Ingresar al sistema](#2-ingresar-al-sistema)
3. [La pantalla principal](#3-la-pantalla-principal)
4. [Registrar un trámite](#4-registrar-un-trámite)
5. [Trámites recibidos](#5-trámites-recibidos)
6. [Firmar documentos](#6-firmar-documentos)
7. [Documentos enviados y rastreo](#7-documentos-enviados-y-rastreo)
8. [Reportes](#8-reportes)
9. [El asistente del chat](#9-el-asistente-del-chat)
10. [El portal del ciudadano](#10-el-portal-del-ciudadano)
11. [Preguntas frecuentes](#11-preguntas-frecuentes)

---

## 1. Conceptos básicos

| Término | Qué es |
| --- | --- |
| **Trámite** | Un documento principal, sus anexos y todo su recorrido por las áreas |
| **N° de expediente** | El número oficial del trámite: `EXP-2026-000048`. Es el que se le da al ciudadano |
| **Código de seguimiento** | El identificador interno: `D0000083`. También sirve para buscarlo |
| **Interno** | Documento producido por la institución: un oficio, un informe, un memorándum |
| **Externo** | Documento que llega de afuera: de un ciudadano, una empresa u otra entidad |
| **Derivar** | Enviar el trámite a otra área para que continúe |
| **Copia** | Envío para que otra área tome conocimiento. No le pide nada |
| **Atención** | Envío a otra área pidiéndole una respuesta dentro de un plazo |
| **Observar** | Pedir al ciudadano que corrija o complete su documento |
| **Subsanar** | Lo que hace el ciudadano cuando corrige lo observado |

### Estados de un trámite

| Estado | Qué significa | Qué puede hacer su área |
| --- | --- | --- |
| **Pendiente** | Llegó a su área y nadie lo ha recibido | Aceptar, rechazar u observar |
| **Aceptado** | Su área lo recibió y lo está atendiendo | Derivar, finalizar u observar |
| **Observado** | Espera que el ciudadano corrija | Esperar. Vuelve solo cuando el ciudadano subsana |
| **Finalizado** | Atendido y cerrado | Consultar |
| **Rechazado** | No se admitió | Consultar |

### Qué puede hacer cada rol

| Acción | Jefe de Área | Secretario(a) | Mesa de Partes | Especialista |
| --- | :---: | :---: | :---: | :---: |
| Registrar trámites | ✓ | ✓ | ✓ | ✓ |
| Derivar | ✓ | ✓ | ✓ | — |
| Finalizar y rechazar | ✓ | ✓ | — | — |
| Observar para subsanación | ✓ | ✓ | ✓ | — |
| Firmar digitalmente | ✓ | — | — | ✓ |

Si no ve un botón que esperaba, es porque su rol no lo permite. Consulte con el administrador.

## 2. Ingresar al sistema

1. Abra la dirección del sistema que le dio la institución.
2. Escriba su usuario y contraseña, y pulse **Iniciar Sesión**.

- Después de **5 intentos fallidos** el ingreso se bloquea 15 minutos.
- La sesión se cierra sola tras **2 horas**. Lo que no haya guardado se pierde.
- Para salir, use el menú de su nombre, arriba a la derecha → **Cerrar Sesión**. No deje la sesión
  abierta en una computadora compartida.

## 3. La pantalla principal

| Parte | Qué muestra |
| --- | --- |
| **Menú lateral** | Trámite Nuevo, Trámites Recibidos, Documentos Enviados, Rastrear Trámites, Reportes y Manual de Usuario |
| **Barra superior** | La hora del sistema, los comunicados, los trámites pendientes de su área y su usuario |
| **Tablero** | Cuántos trámites tiene su área en cada estado y cuáles necesitan atención |
| **Chat** | El asistente, en la esquina inferior derecha |

Al entrar pueden aparecer **comunicados** de la institución. Léalos y confirme su lectura.

### El semáforo de plazos

| Color | Significado |
| --- | --- |
| Verde | Dentro del plazo |
| Amarillo | Vence pronto |
| Rojo | Vencido |

Los plazos se cuentan en **días hábiles**: sin sábados, domingos ni feriados.

## 4. Registrar un trámite

Ruta: **Menú → Trámite Nuevo**

### 4.1 Datos del remitente

- **Persona natural:** escriba el DNI y pulse la lupa. Si la consulta de DNI está activa, se completan
  nombres y apellidos. Si no, escríbalos.
- **Persona jurídica** (empresa u otra entidad): además, RUC y razón social. Un trámite de persona
  jurídica siempre es **externo**.

### 4.2 ¿Interno o externo?

- **En las áreas**, el remitente se elige de la lista del personal y el trámite es **interno**.
- **En Mesa de Partes**, la pantalla trae la casilla **Es trámite externo**. Márquela para lo que
  llega de fuera y escriba los datos del remitente. Sin marcarla, el sistema decide: si el remitente
  es **personal de la institución** con usuario en el sistema, es interno; si no, externo.

Un trabajador que presenta una solicitud personal se registra en Mesa de Partes como externo.

### 4.3 Datos del documento

| Campo | Indicaciones |
| --- | --- |
| Área de procedencia y de destino | De dónde viene y a qué área va |
| Tipo de documento | Oficio, informe, solicitud… |
| N° de documento | En un trámite **interno**, al elegir el tipo se propone el siguiente número de su área, por ejemplo `001-2026-DIRESA-APURÍMAC/CONT`. Si el documento ya venía numerado, cámbielo |
| Folios | Número de hojas |
| Asunto | Breve y claro: es lo que se lee en todas las bandejas |
| Acciones | Lo que se espera del área: Atender, Revisar, Emitir informe… |
| Plazo de respuesta | En días hábiles |
| Copias | Otras áreas que deben tomar conocimiento |

### 4.4 Archivos

- **Documento principal:** PDF de hasta 20 MB.
- **Anexos:** hasta 10 PDF.

Al elegir un PDF que **ya viene firmado**, el sistema muestra quién lo firmó y si la firma es válida.

### 4.5 Firmar antes de guardar

En un trámite **interno**, y si su rol puede firmar, aparece **Firmar el documento**. Márquelo para
firmar el principal y los anexos que elija al guardar. Vea la [sección 6](#6-firmar-documentos).

Pulse **Registrar**. El sistema muestra el N° de expediente asignado.

## 5. Trámites recibidos

Ruta: **Menú → Trámites Recibidos**

Aquí llega todo lo dirigido a su área. Use el buscador y los filtros de tipo y fechas para encontrar
un trámite. El botón **Ver** abre el expediente: datos, archivos y recorrido.

### 5.1 Aceptar

Pulse **Aceptar** cuando su área recibe el trámite. Queda constancia de quién lo recibió y cuándo, y
el trámite pasa a **Aceptado**.

### 5.2 Rechazar

Pulse **Rechazar** si el trámite no corresponde o no puede admitirse. Escriba el motivo: queda
registrado en el recorrido.

### 5.3 Observar

Solo en trámites **externos**. Úselo cuando al ciudadano le falta algo que puede corregir: una firma,
un anexo, un dato.

1. Pulse **Observar**.
2. Escriba con claridad **qué debe corregir**. El ciudadano leerá exactamente ese texto.
3. Elija el plazo: de 1 a 30 días hábiles (2 por defecto).
4. Confirme.

El trámite queda **Esperando subsanación** y el ciudadano recibe un correo. Cuando suba la corrección
desde el portal, el trámite vuelve al estado que tenía, con el nuevo documento en sus anexos, y su
área recibe un aviso.

### 5.4 Derivar

Con el trámite **Aceptado**, pulse **Derivar**:

1. Elija el área de destino.
2. Describa lo que se hizo o lo que se pide.
3. Marque las acciones y el plazo.
4. Adjunte, si corresponde, el documento que genera su área (un informe, un proveído) y sus anexos.
   Puede **firmarlo antes de enviarlo**.
5. Opcional: agregue **copias** a otras áreas o pida **atención** a otras áreas, cada una con su plazo.

### 5.5 Finalizar

Cuando el trámite quedó atendido, en la misma ventana de derivación elija **Finalizar**. El trámite se
cierra.

### 5.6 Copias y atenciones recibidas

| Llega a su área como | Botones | Qué hacer |
| --- | --- | --- |
| **Copia** | Confirmar recepción | Confirme que la vio. No cambia el estado del trámite |
| **Atención** | Confirmar recepción · Responder | Registre la respuesta de su área, con un informe en PDF si hace falta. Puede firmarlo antes de enviarlo |

## 6. Firmar documentos

### 6.1 Qué necesita

- Un rol que pueda firmar: **Jefe de Área** o **Especialista**.
- **Su certificado digital** en archivo **.pfx** o **.p12** y su contraseña.
- Que el **DNI de su ficha de empleado** sea el de su certificado. Si no coinciden, el sistema no deja
  firmar.

El sistema **no guarda** su certificado ni su contraseña: se usan para firmar y se descartan.

### 6.2 Dónde se firma

| Momento | Dónde |
| --- | --- |
| Al crear el documento | **Trámite Nuevo** → casilla **Firmar el documento** |
| Al derivar | En la ventana de derivación, sobre el documento adjunto |
| Al responder una atención | En la ventana de respuesta |
| Después, o para una segunda firma | En el expediente → **Firmar documentos**, eligiendo uno o varios archivos |

Solo se firman documentos **internos**. Los externos llegan firmados por su autor: en ellos aparece
**Verificar firma**, que comprueba esas firmas.

### 6.3 Qué le pasa al PDF

La firma agrega un **sello visible** con su nombre, su DNI, la fecha, un código de verificación de 10
caracteres y un **código QR**. Cualquier persona puede escanear el QR para comprobar la firma.

Si otra persona también debe firmar (**cofirma**), la segunda firma se agrega sin invalidar la primera.

## 7. Documentos enviados y rastreo

**Menú → Documentos Enviados** muestra todo lo que su área derivó.

**Menú → Rastrear Trámites** muestra el recorrido de un trámite como **diagrama de flujo** y como
**lista de movimientos**. El recorrido empieza por el origen real:

- un **ciudadano**, si lo presentó una persona;
- una **entidad externa**, con su RUC, si lo envió una empresa u otra institución;
- **el área que lo remitió**, si es un documento interno.

Luego sigue cada derivación, con copias, atenciones, quién recibió y cuándo.

## 8. Reportes

Ruta: **Menú → Reportes**

| Reporte | Para qué |
| --- | --- |
| Por fechas y área | Qué trámites pasaron por un área en un periodo |
| Por fechas y estado | Cuántos están pendientes, aceptados, finalizados… |
| Por fechas y tipo de documento | Volumen por tipo de documento |
| Plazos y productividad por área | Cumplimiento de plazos y tiempos de atención |

Todos se exportan a **PDF**, **Excel** y **CSV**.

## 9. El asistente del chat

Escriba en lenguaje natural, por ejemplo:

- «¿Cuántos trámites tengo pendientes?»
- «¿Dónde está el EXP-2026-000048?»
- «¿Qué trámites vencen esta semana?»

El asistente solo **lee**: no acepta, deriva ni cambia nada, y solo le responde sobre lo que su área
puede ver. Tiene un límite de consultas por minuto y por día.

## 10. El portal del ciudadano

El ciudadano **no necesita cuenta**. Estas páginas son públicas:

| Página | Para qué |
| --- | --- |
| **Mesa de Partes Virtual** (`registrar.php`) | Presentar un trámite |
| **Seguimiento de Trámite** (`seguimiento.php`) | Consultar el estado y subsanar observaciones |
| **Validar firma digital** (`validar_firma.php`) | Comprobar la firma de un documento |

### 10.1 Presentar un trámite

1. Datos del remitente: DNI, nombres y apellidos, celular y correo. Si presenta por una empresa o
   entidad, marque **persona jurídica** y agregue RUC y razón social.
2. Datos del documento: tipo, número, folios y asunto.
3. Documento principal en PDF y hasta 5 anexos en PDF, con 35 MB en total.
4. Marque la declaración de veracidad y la aceptación del **aviso de privacidad** (se puede leer
   desde el enlace), y pulse **Registrar Trámite**.

El ciudadano recibe su **N° de expediente**, puede descargar su **cargo de recepción** en PDF con QR y,
si dejó correo, recibe la confirmación.

> Lo presentado **fuera del horario de atención** (de noche, fin de semana o feriado) se considera
> recibido el siguiente día hábil, y los plazos corren desde ahí.

### 10.2 Consultar el trámite

Con el **N° de expediente** (o el código de seguimiento) y el **DNI del remitente**, o escaneando el
**QR del ticket o del cargo**, que abre el trámite directamente sin pedir el DNI. Muestra el
estado, el área donde está, el plazo y el recorrido. No muestra nombres de funcionarios ni documentos
internos.

### 10.3 Subsanar una observación

Si el trámite está **Observado**, la consulta muestra qué debe corregir y hasta cuándo. El ciudadano
sube el documento en PDF, con un comentario opcional, y pulsa **Enviar subsanación**.

### 10.4 Validar una firma

Se escanea el **QR del sello** o se escribe el **código de 10 caracteres**. La página muestra quién
firmó, cuándo, y si el documento sigue íntegro. También se puede subir una copia del PDF para
comprobar que es idéntica a la firmada.

## 11. Preguntas frecuentes

**No veo el botón de firmar.**
Su rol no puede firmar, el trámite es externo (se verifica, no se firma) o el archivo no es PDF.

**Al firmar dice que el certificado pertenece a otra persona.**
El DNI de su certificado no es el de su ficha de empleado. Pida al administrador que lo corrija.

**Al firmar dice que la contraseña es incorrecta, pero es la correcta.**
Su certificado puede estar en un formato antiguo. Pida a TI que lo reexporte (ver la
[Guía de configuración](05-guia-de-configuracion.md#71-certificados-de-los-firmantes)).

**No puedo observar un trámite.**
Solo se observan trámites externos, que estén pendientes o aceptados en su área, y su rol debe
permitirlo.

**El ciudadano dice que no le llegó el correo.**
Que revise su carpeta de spam y que el correo que dejó esté bien escrito. Si a nadie le llegan
correos, avise al administrador.

**El número de documento propuesto no es el que corresponde.**
Cámbielo antes de registrar. El sistema usa el número que usted deja.

**Me sacó del sistema.**
La sesión dura 2 horas. Vuelva a ingresar.
