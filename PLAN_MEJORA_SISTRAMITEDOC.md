# Plan de mejora de SISTRAMITEDOC

## Objetivo
Convertir el sistema en una plataforma corporativa de trámite documentario para mesa de partes, áreas internas, responsables y administradores, con trazabilidad completa y una experiencia clara para el ciudadano y el personal.

## Fases

### 1. Centro de operaciones y lenguaje visual
- Dashboard distinto para Administrador y Secretario(a).
- Acciones rápidas: nuevo trámite, recibidos, enviados y rastreo.
- Indicadores útiles: pendientes, vencidos, en atención y finalizados.
- Navegación agrupada por trabajo, reportes y administración.
- Responsive, accesible, estados de carga y mensajes consistentes.

### 2. Flujo documentario completo
- Bandeja de entrada con prioridad, plazo, responsable y filtros combinables.
- Recepción, aceptar, observar, rechazar, derivar, devolver y finalizar.
- Derivación a múltiples áreas con copias, anexos y comentarios.
- Bandeja de enviados y expediente único con línea de tiempo.
- Historial inalterable de movimientos y responsables.

### 3. Mesa de partes externa e interna
- Registro externo desde la bandeja interna para el personal autorizado.
- Portal de consulta ciudadana con código, DNI y constancia descargable.
- Validación de DNI/RUC configurable, sin bloquear el registro si el servicio externo no está disponible.
- Notificaciones por correo y estados visibles para el ciudadano.

### 4. Documentos y firma digital
- Revisión de archivos, anexos, tamaño y tipo antes de registrar.
- Generación de constancias y documentos PDF con metadatos.
- Flujo de firma pendiente, firmada, observada y revocada.
- Integración con certificado digital y proveedor compatible con la normativa peruana.
- Validación de firma en servidor y sello de tiempo.

**Dependencia necesaria:** certificado digital vigente del firmante, proveedor o componente de firma (por ejemplo, cliente local/token o servicio de firma), política institucional y configuración HTTPS. La firma legal no puede implementarse solo con JavaScript o una imagen de rúbrica.

### 5. Seguridad, auditoría y operación
- Protección CSRF, control de permisos por acción, sesiones seguras y límites de carga.
- Validación de archivos por MIME/extensión, nombres aleatorios y almacenamiento fuera del acceso público.
- Auditoría de accesos, cambios y descargas.
- Copias de seguridad, logs y monitoreo de errores.
- Pruebas de flujo para registro, derivación, seguimiento y firma.

## Orden de ejecución
1. Tablero y navegación.
2. Bandeja de entrada y acciones del expediente.
3. Registro externo desde el área interna.
4. Expediente, anexos y notificaciones.
5. Firma digital con la dependencia técnica confirmada.
6. Seguridad, pruebas y puesta en producción.

## Criterio de aceptación inicial
Un usuario puede iniciar sesión, entender qué debe atender, abrir un trámite nuevo o revisar un expediente en dos clics, sin perder las rutas actuales del sistema.
