# Acta de entrega y conformidad

**SISTRAMITEDOC** · Versión 2.0

Plantilla. Se completa y firma al terminar la implementación en cada institución. Los datos entre
corchetes `[COMPLETAR]` se llenan con los de cada cliente.

---

## 1. Datos

| Dato | Valor |
| --- | --- |
| Institución | `[COMPLETAR]` |
| RUC | `[COMPLETAR]` |
| Contrato u orden de servicio | `[COMPLETAR]` |
| Proveedor | `[COMPLETAR]` |
| Lugar y fecha | `[COMPLETAR]` |
| Dirección del sistema | `[COMPLETAR: https://…]` |
| Versión entregada | 2.0 |

## 2. Entregables

| N° | Entregable | Entregado |
| --- | --- | :---: |
| 1 | Sistema instalado en el servidor de la institución | ☐ |
| 2 | Código fuente | ☐ |
| 3 | Instalador de base de datos (`database/instalacion/`) y migraciones | ☐ |
| 4 | Ficha del producto | ☐ |
| 5 | Especificación funcional | ☐ |
| 6 | Documento técnico | ☐ |
| 7 | Manual de instalación y despliegue | ☐ |
| 8 | Guía de configuración | ☐ |
| 9 | Manual de usuario | ☐ |
| 10 | Manual del administrador | ☐ |
| 11 | Documento de seguridad y datos personales | ☐ |
| 12 | Capacitación realizada, con registro de asistencia | ☐ |

## 3. Verificación de la instalación

| N° | Verificación | Conforme |
| --- | --- | :---: |
| 1 | El sistema abre por **HTTPS** con certificado válido | ☐ |
| 2 | Se cambió la contraseña del usuario `admin` | ☐ |
| 3 | La base de datos usa un usuario propio, no `root` | ☐ |
| 4 | `storage/`, `config/database.php`, `database/` y los PDF de `controller/tramite/documentos/` responden «acceso denegado» (403) | ☐ |
| 5 | Datos de la institución, logo y horario configurados | ☐ |
| 6 | Áreas con siglas, empleados y usuarios cargados | ☐ |
| 7 | Feriados del año en curso y del siguiente cargados | ☐ |
| 8 | Correo de prueba enviado y recibido | ☐ / No aplica |
| 9 | Consulta de DNI probada | ☐ / No aplica |
| 10 | Asistente de IA probado, con la evaluación del área legal | ☐ / No aplica |
| 11 | Aviso de privacidad revisado por el área legal y guardado en el panel | ☐ |
| 12 | Respaldo automático programado y **restauración probada** | ☐ |

## 4. Pruebas funcionales

Realizadas en presencia de la institución:

| N° | Prueba | Resultado esperado | Conforme |
| --- | --- | --- | :---: |
| 1 | Registrar un trámite desde el portal ciudadano | N° de expediente, cargo en PDF y correo de confirmación | ☐ |
| 2 | Consultar ese trámite con N° de expediente y DNI | Muestra estado y recorrido | ☐ |
| 3 | Aceptar el trámite en Mesa de Partes y derivarlo | Llega al área de destino, con aviso | ☐ |
| 4 | Derivar con copia y con pedido de atención | Cada área lo recibe según su tipo | ☐ |
| 5 | Observar un trámite externo y subsanarlo desde el portal | Vuelve a su estado con el documento nuevo | ☐ |
| 6 | Registrar un trámite interno con número automático | Número con la sigla del área | ☐ |
| 7 | Firmar un documento con certificado | PDF con sello y QR | ☐ |
| 8 | Validar esa firma en la página pública | Muestra firmante y documento íntegro | ☐ |
| 9 | Finalizar un trámite | Estado Finalizado | ☐ |
| 10 | Rastrear el trámite | Diagrama desde el origen real | ☐ |
| 11 | Generar y exportar un reporte | PDF, Excel y CSV | ☐ |
| 12 | Revisar la bitácora | Aparecen las acciones anteriores | ☐ |
| 13 | Un usuario sin permiso de firma no ve la opción de firmar | Opción ausente | ☐ |

## 5. Observaciones

| N° | Observación | Responsable | Plazo |
| --- | --- | --- | --- |
| | | | |
| | | | |

## 6. Credenciales

Las credenciales de administración, base de datos y servicios externos se entregan **en sobre
cerrado o por un medio privado**, nunca en esta acta.

## 7. Garantía y soporte

| Dato | Valor |
| --- | --- |
| Periodo de garantía | `[COMPLETAR]` |
| Qué cubre | `[COMPLETAR: corrección de fallas del sistema entregado]` |
| Qué no cubre | `[COMPLETAR: cambios de alcance, fallas del servidor o de servicios externos, uso indebido]` |
| Canal de soporte | `[COMPLETAR]` |
| Tiempo de respuesta | `[COMPLETAR]` |

## 8. Conformidad

La institución declara haber recibido los entregables y verificado el funcionamiento descrito, con
las observaciones de la sección 5.

| Por la institución | Por el proveedor |
| --- | --- |
| Nombre: | Nombre: |
| Cargo: | Cargo: |
| DNI: | DNI: |
| Firma: | Firma: |
