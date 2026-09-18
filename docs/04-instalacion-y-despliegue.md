# Instalación y despliegue

**SISTRAMITEDOC** · Versión 2.0 · Septiembre de 2026

Para el área de TI. Cubre una instalación nueva, la actualización de una instalación existente, el
paso a producción y los respaldos.

---

## 1. Requisitos del servidor

| Recurso | Requisito |
| --- | --- |
| Servidor web | Apache 2.4 con `AllowOverride All` en la carpeta del sistema, y `mod_headers` recomendado |
| PHP | 8.2, con las extensiones `openssl`, `curl`, `gd`, `mbstring`, `pdo_mysql`, `fileinfo` y `zip` |
| Base de datos | MariaDB 10.4 o superior. **MySQL no es compatible**: las migraciones usan sintaxis de MariaDB |
| Composer | Para instalar las dependencias PHP |
| HTTPS | Certificado válido. **Obligatorio en producción** (ver 4.1) |
| Disco | `[COMPLETAR: según el volumen de expedientes]`. Cada trámite guarda sus PDF de hasta 20 MB |
| Sistema operativo | Probado en Windows con XAMPP. En Linux, verifique la instalación completa antes de pasar a producción |

> **`AllowOverride All` no es opcional.** Parte de la protección del sistema está en archivos
> `.htaccess`: bloquean el acceso a `storage/`, a los archivos de configuración, a los volcados `.sql` y
> a los scripts de prueba, e impiden ejecutar código en las carpetas de documentos. Si Apache los
> ignora, o si el sistema se sirve con otro servidor web (por ejemplo nginx), todo eso queda expuesto y
> hay que reproducir esas reglas en la configuración del servidor.

Ajustes de PHP (`php.ini`) necesarios para subir documentos:

```ini
upload_max_filesize = 20M
post_max_size = 40M
max_file_uploads = 20
memory_limit = 256M
```

Con `post_max_size` menor a 40 MB, un ciudadano que adjunte el documento y varios anexos recibe un
aviso de que los archivos superan el tamaño permitido.

## 2. Instalación nueva

### 2.1 Copiar el sistema

1. Copie la carpeta del sistema al directorio público del servidor (por ejemplo
   `htdocs/SISTRAMITEDOC` en XAMPP).
2. En esa carpeta, instale las dependencias:

   ```bash
   composer install --no-dev
   ```

3. Dé permiso de escritura al usuario del servidor web sobre:
   - `storage/`
   - `controller/tramite/documentos/`
   - `controller/tramite_area/documentos/`
   - `controller/comunicados/imagenes/`
   - la carpeta del logo de la institución

### 2.2 Crear la base de datos

1. Cree la base y un usuario **propio del sistema** (no use `root` en producción):

   ```sql
   CREATE DATABASE sistema_tramite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'sistramite'@'localhost' IDENTIFIED BY 'una-contraseña-segura';
   GRANT SELECT, INSERT, UPDATE, DELETE, EXECUTE ON sistema_tramite.* TO 'sistramite'@'localhost';
   ```

   Para cargar el esquema hace falta un usuario con permiso de crear tablas y procedimientos; use uno
   administrativo solo para ese paso.

2. Cargue el esquema y los datos iniciales, **en este orden y con el juego de caracteres correcto**:

   ```bash
   mysql --default-character-set=utf8mb4 -u root -p sistema_tramite < database/instalacion/01_esquema.sql
   mysql --default-character-set=utf8mb4 -u root -p sistema_tramite < database/instalacion/02_datos_iniciales.sql
   ```

   Sin `--default-character-set=utf8mb4`, en Windows las tildes y eñes se graban dañadas.

   En una instalación nueva **no** corra las migraciones de `database/migraciones/`: ya están
   incluidas en el esquema.

### 2.3 Conectar el sistema a la base

1. Copie `config/database.example.php` como `config/database.php`.
2. Complete los datos:

   ```php
   return [
       'host'    => '127.0.0.1',
       'puerto'  => 3306,
       'nombre'  => 'sistema_tramite',
       'usuario' => 'sistramite',
       'clave'   => 'una-contraseña-segura',
   ];
   ```

3. Copie también `model/model_conexion.example.php` como `model/model_conexion.php` si no existe.

Estos archivos tienen credenciales: están excluidos del control de versiones y no deben publicarse.

### 2.4 Primer ingreso

1. Abra el sistema en el navegador e ingrese con:

   | Usuario | Contraseña |
   |---|---|
   | `admin` | `Admin.Cambiar2026` |

2. **Cambie la contraseña de inmediato** en Usuarios. Mientras no la cambie, cualquiera que conozca
   el instalador puede entrar.
3. Siga la [Guía de configuración](05-guia-de-configuracion.md): datos de la institución, áreas,
   usuarios y servicios externos.

### 2.5 Qué trae la instalación nueva

| Dato | Contenido |
| --- | --- |
| Institución | Datos de ejemplo, para reemplazar |
| Áreas | Solo MESA DE PARTES, con el número 1. **No la borre ni cambie su número**: ahí entra lo del portal |
| Usuarios | Solo el administrador |
| Tipos de documento | 31 de uso común |
| Feriados | Los nacionales de 2026 y 2027 |
| Trámites | Ninguno |

## 3. Actualizar una instalación existente

1. **Respalde** la base y las carpetas de documentos (ver sección 5).
2. Reemplace los archivos del sistema, conservando `config/database.php`, `model/model_conexion.php`,
   `config/config_email.php` si existe, y las carpetas de documentos.
3. Ejecute `composer install --no-dev`.
4. Aplique, **en orden**, las migraciones de `database/migraciones/` que aún no tenga, siempre con
   `--default-character-set=utf8mb4`:

   ```bash
   mysql --default-character-set=utf8mb4 -u root -p sistema_tramite < database/migraciones/024_roles.sql
   ```

   Los archivos que terminan en `_revertir.sql` deshacen su migración; no los ejecute al actualizar.
5. Entre como administrador y revise que cada módulo abra.

## 4. Paso a producción

### 4.1 HTTPS

Es obligatorio. Las contraseñas de los usuarios y de los certificados digitales, y los propios
archivos .pfx, viajan en las solicitudes. Sin HTTPS, cualquiera en la red puede leerlos. El sistema
detecta HTTPS solo y marca la cookie de sesión como segura.

### 4.2 Lista de verificación

- [ ] HTTPS activo y redirección de HTTP a HTTPS.
- [ ] La base usa un usuario propio con permisos mínimos, no `root`.
- [ ] Se cambió la contraseña del usuario `admin`.
- [ ] Se retiraron los archivos de prueba de la raíz (ver [Documento técnico](03-documento-tecnico.md#10-archivos-que-no-deben-ir-a-producción)).
- [ ] No hay volcados `.sql` con datos en carpetas públicas.
- [ ] `storage/`, `config/database.php`, `database/` y un PDF de `controller/tramite/documentos/` responden «acceso denegado» (403) desde el navegador: prueba de que Apache respeta los `.htaccess`.
- [ ] Las carpetas de documentos no permiten listar su contenido.
- [ ] `display_errors = Off` en `php.ini`; los errores van al registro del servidor.
- [ ] Respaldos automáticos programados y probados (sección 5).
- [ ] Datos de la institución, correo y feriados configurados.

## 5. Respaldos

El sistema pierde su valor probatorio si se pierden los PDF firmados. Respalde **las dos cosas**:

| Qué | Dónde | Frecuencia sugerida |
| --- | --- | --- |
| Base de datos | `sistema_tramite` | Diaria |
| Documentos | `controller/tramite/documentos/`, `controller/tramite_area/documentos/`, `controller/comunicados/imagenes/` | Diaria |
| Configuración | `config/`, `model/model_conexion.php` | Tras cada cambio |

Respaldo de la base, con procedimientos y sin bloquear el uso:

```bash
mysqldump --default-character-set=utf8mb4 --single-transaction --routines --triggers \
  -u root -p sistema_tramite > respaldo_$(date +%Y%m%d).sql
```

Guarde los respaldos fuera del servidor y **pruebe restaurarlos** al menos una vez: un respaldo que
nunca se restauró no está comprobado.

## 6. Mantenimiento anual

- Cargue en **Feriados** los del año siguiente: Jueves y Viernes Santo cambian cada año, y el Gobierno
  puede declarar feriados o días no laborables nuevos.
- Revise la vigencia de los certificados digitales de quienes firman.
- Revise el saldo de los servicios externos (IA y consulta de DNI).
