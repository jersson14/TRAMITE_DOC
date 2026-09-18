# Manual del administrador

**SISTRAMITEDOC** · Versión 2.0 · Septiembre de 2026

Para quien administra el sistema en la institución, con el rol **Administrador**. La configuración
inicial (institución, correo, DNI, asistente, firma) está en la
[Guía de configuración](05-guia-de-configuracion.md). Este manual cubre el trabajo de todos los días.

---

## 1. Qué ve el administrador

El administrador ve **toda la institución**, no solo un área. Su menú:

| Menú | Para qué |
| --- | --- |
| Trámite | Todos los trámites. Registrar, ver, imprimir hoja de ruta, ticket y hoja de seguimiento, eliminar |
| Ver Movimientos | Todas las derivaciones de todas las áreas |
| Empleado | Fichas del personal |
| Área | Áreas de la institución y sus siglas |
| Tipo Documento | Catálogo de tipos de documento |
| Feriados | Días no hábiles para el cálculo de plazos |
| Bitácora | Registro de todo lo que se hizo en el sistema |
| Rastrear Trámite | Recorrido de cualquier trámite |
| Comunicados | Avisos al personal |
| Reporte de Trámites | Los mismos reportes que las áreas, para toda la institución |
| Usuario | Cuentas de acceso y roles |
| Institución y asistente | Datos de la institución, servicios externos y aviso de privacidad del portal |

## 2. Usuarios y roles

### 2.1 Dar de alta a una persona

1. **Menú → Empleado → Nuevo.** Registre nombres, **DNI real**, correo y celular.
   - El DNI se usa para validar su certificado digital al firmar.
   - El correo recibe los avisos de trámites de su área.
2. **Menú → Usuario → Nuevo.** Elija el empleado, escriba el nombre de usuario y una contraseña
   inicial, y asigne **área** y **rol**.
3. Entregue la contraseña a la persona por un medio privado.

> Los usuarios **no pueden cambiar su propia contraseña**: la cambia el administrador con
> **Cambiar contraseña de usuario**. Use contraseñas de al menos 10 caracteres, con letras y números;
> el sistema no impone una longitud mínima.

### 2.2 Roles

| Rol | Para quién | Puede |
| --- | --- | --- |
| **Administrador** | TI o la persona a cargo del sistema | Todo, en todas las áreas |
| **Jefe de Área** | Responsable del área | Registrar, derivar, finalizar, rechazar, observar y firmar |
| **Secretario(a)** | Apoyo administrativo del área | Registrar, derivar, finalizar, rechazar y observar. No firma |
| **Mesa de Partes** | Personal de recepción | Registrar, derivar y observar. No finaliza ni firma |
| **Especialista** | Profesional que atiende y emite informes | Registrar y firmar. No deriva ni finaliza |

Los permisos los controla el servidor en cada operación: aunque alguien manipule la pantalla, una
acción que su rol no permite es rechazada.

**Buenas prácticas:**

- Dé a cada persona el rol mínimo que necesita.
- Tenga **pocos administradores**: ven y pueden eliminar todo.
- Una cuenta por persona. No comparta usuarios: la bitácora registra a nombre de quién se hizo cada cosa.

### 2.3 Cuando alguien se va o cambia de área

| Situación | Qué hacer |
| --- | --- |
| Deja la institución | **Desactivar usuario** el mismo día. No lo elimine: su historial debe conservarse |
| Cambia de área | **Editar datos de usuario** y cambie el área. Verá los trámites de la nueva área |
| Cambia de función | Cambie el rol |
| Olvidó su contraseña | **Cambiar contraseña de usuario** y entréguele la nueva |
| Quedó bloqueado por intentos fallidos | Espere 15 minutos o cambie la contraseña |

## 3. Catálogos

### 3.1 Áreas

Cada área necesita un nombre y, recomendable, una **sigla** única, que va al final del número de sus
documentos. Detalles en la [Guía de configuración](05-guia-de-configuracion.md#2-áreas-y-sus-siglas).

**El área número 1 es MESA DE PARTES** y recibe lo del portal ciudadano: no la elimine ni la
renombre. Un área que ya no existe se **desactiva**, no se elimina, para conservar su historial.

### 3.2 Tipos de documento

Se agregan los que use la institución y se desactivan los que no. Cada área tiene su propia
numeración por tipo de documento y por año.

### 3.3 Feriados

Agregue cada año los feriados móviles (Jueves y Viernes Santo), los regionales y los días no
laborables decretados. Un feriado que falte hace vencer los plazos antes de tiempo.

## 4. Trámites

### 4.1 Consultar

**Menú → Trámite** muestra todos los trámites de la institución. **Ver Movimientos** muestra cada
derivación. **Rastrear Trámite** muestra el recorrido de uno, como diagrama y como lista.

### 4.2 Imprimir

Desde la lista de trámites:

| Botón | Documento |
| --- | --- |
| Imprimir ticket | Constancia breve de recepción |
| Imprimir hoja de ruta | Datos del trámite y su recorrido, para el expediente físico |
| Imprimir hoja de seguimiento | Detalle de movimientos |

### 4.3 Eliminar

**Eliminar tramite** borra un trámite registrado por error. Úselo solo para eso: un trámite real,
aunque se haya rechazado, debe conservarse. La eliminación queda en la bitácora.

## 5. Comunicados

**Menú → Comunicados → Nuevo**

| Campo | Opciones |
| --- | --- |
| Dirigido a | Todo el personal · Solo administradores · Solo personal de áreas · Áreas específicas |
| Vigencia | Opcional: desde y hasta cuándo se muestra |
| Imagen | Opcional |
| Estado | Vigente (se muestra como aviso) · Archivado (no se muestra) |

Los comunicados vigentes aparecen al entrar al sistema. El personal confirma su lectura y usted ve
quién los leyó.

## 6. Bitácora

**Menú → Bitácora** registra, con fecha, hora, usuario y dirección de red:

| Grupo | Acciones |
| --- | --- |
| Acceso | Ingreso al sistema, intento fallido de ingreso, cierre de sesión |
| Trámites | Registro, derivación, cambio de estado, eliminación, acuse de recepción |
| Atenciones | Atención solicitada, atención respondida |
| Firma | Firma digital |
| Observaciones | Observación del área, subsanación del ciudadano |
| Administración | Registro, modificación y eliminación en los mantenimientos y la configuración |

Úsela para responder «¿quién hizo esto y cuándo?». Revise periódicamente los **intentos fallidos de
ingreso**: muchos seguidos sobre una misma cuenta pueden ser un intento de adivinar la contraseña.

La bitácora **no** guarda contraseñas ni claves de servicios.

## 7. Reportes

Los mismos de las áreas (ver [Manual de usuario](06-manual-de-usuario.md#8-reportes)), para toda la
institución. El de **plazos y productividad por área** sirve para ver qué áreas acumulan trámites
vencidos.

## 8. Tareas periódicas

| Frecuencia | Tarea |
| --- | --- |
| Diaria | Comprobar que el respaldo automático se hizo |
| Semanal | Revisar trámites vencidos en el reporte de plazos |
| Mensual | Revisar intentos fallidos en la bitácora. Desactivar usuarios de personal que se fue |
| Mensual | Revisar el saldo de los servicios de IA y de consulta de DNI |
| Anual | Cargar los feriados del año siguiente. Revisar la vigencia de los certificados de los firmantes |
| Anual | Probar restaurar un respaldo |

Los respaldos y la actualización del sistema están en
[Instalación y despliegue](04-instalacion-y-despliegue.md).

## 9. Qué hacer si…

| Problema | Qué revisar |
| --- | --- |
| Nadie recibe correos | Institución y asistente → Correo saliente → **Enviar prueba** |
| La búsqueda por DNI no completa nombres | Institución y asistente → Consulta de DNI → **Probar consulta**. Puede ser falta de saldo |
| El asistente responde solo lo básico | Institución y asistente → Asistente del chat → **Probar conexión** |
| Una persona no puede firmar | Su rol, el DNI de su ficha y la vigencia de su certificado |
| Un área no ve un trámite | Si el trámite pasó por esa área y si el usuario tiene el área correcta |
| Un plazo vence en un día raro | Si falta un feriado en la lista |
| Tildes o eñes se ven como símbolos raros | Un script SQL se aplicó sin `--default-character-set=utf8mb4`. Avise a TI |
| El sistema no abre | Que Apache y MariaDB estén encendidos. Avise a TI |
