# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Mesa de Partes:** recibe y registra documentos de ciudadanos y empresas (presencial o desde el portal), valida identidad (DNI/RUC), deriva a las áreas y entrega el cargo de recepción. Trabajo intensivo y repetitivo durante toda la jornada, normalmente en PC de oficina.
- **Secretario(a) de área:** recibe los documentos que llegan a su área, los registra, deriva, responde y hace seguimiento de lo enviado.
- **Jefe de Área:** revisa, aprueba, firma digitalmente y decide a quién se deriva o asigna un expediente. Revisa y firma tanto en PC como desde el celular fuera de la oficina.
- **Especialista / Técnico:** atiende los expedientes que su jefe le asigna y prepara informes o respuestas.
- **Administrador:** configura la institución, áreas, tipos de documento, empleados y usuarios, y supervisa la operación con reportes.
- **Ciudadano / administrado (externo):** presenta trámites desde el portal público, consulta el estado con código + DNI y descarga su constancia.

## Product Purpose

Sistema web de trámite documentario (gestión documental interna y externa) para entidades públicas. Reemplaza el expediente en papel: registro, derivación entre áreas con copias, atención, respuesta, firma digital y archivo, con trazabilidad completa de cada movimiento. Éxito: menos tiempo de atención, menos papel y ciudadanos que pueden seguir su trámite sin acudir a la entidad. Criterio de aceptación: un usuario inicia sesión, entiende qué debe atender y abre un trámite nuevo o revisa un expediente en dos clics.

## Positioning

Producto de JCM Digital & AI Consulting pensado para la realidad de las entidades públicas peruanas: firma digital con certificados .pfx y DNIe (vía Firma Perú de la PCM), validación de identidad con RENIEC/SUNAT, portal ciudadano de seguimiento y un asistente con IA que responde consultas sobre los expedientes a partir de los datos de la propia institución.

## Operating Context

- Uso mixto: PC de oficina durante la jornada y celular para revisar, aprobar y firmar fuera de la oficina.
- Vocabulario del dominio: expediente, trámite, documento, folios, asunto, remitente, área de origen/destino, derivar, copia, aceptar, observar, rechazar, finalizar, archivar, cargo de recepción, plazo de respuesta, Mesa de Partes.
- Tipos documentales habituales: oficio, memorándum, informe, carta, solicitud, resolución.
- Documentos adjuntos en PDF; constancias y fichas de seguimiento generadas en PDF (mPDF).
- Notificaciones por correo (SMTP) a áreas y ciudadanos.

## Capabilities and Constraints

- Stack: PHP 8 (MVC propio, procedimientos almacenados), MySQL/MariaDB, jQuery, Bootstrap 4, AdminLTE 3, DataTables, Select2, SweetAlert2, mPDF, PHPMailer. Despliegue en hosting compartido (Hostinger) y XAMPP local.
- Las vistas del panel se cargan como fragmentos HTML dentro de `view/index.php`; hay que conservar las rutas y controladores existentes.
- **Multi-institución:** nombre, logo y color principal de la entidad son configurables; por defecto se usa DIRESA Apurímac.
- Firma digital: .pfx firmado en servidor (sin almacenar el certificado) y Firma Perú para DNIe/token; si la entidad no tiene credenciales de Firma Perú, la opción se muestra con un aviso.
- Roles: Administrador, Mesa de Partes, Jefe de Área, Especialista/Técnico, Secretario(a) de área (hoy solo existen Administrador y Secretario(a)).
- Pendiente de decidir: numeración oficial del expediente por entidad y plazos legales por tipo de trámite.

## Brand Commitments

- La marca del producto es de JCM Digital & AI Consulting; la identidad visible para el usuario es la de la institución configurada.
- Institución por defecto: Dirección Regional de Salud de Apurímac (DIRESA Apurímac), logo oficial en `img/diresa.png` (azul marino con sello del Gobierno Regional). Otros recursos: `view/gore.PNG`, `img/minsa.png`. Logo de JCM: `img/empre.jpg` (solo como crédito del proveedor).
- Todo el texto de la interfaz en español, tono institucional, claro y directo.
- **Decisión visual vinculante (2026-09-15, final):** se conserva **la interfaz original del sistema** (AdminLTE 3 + Bootstrap 4, vistas cargadas con `$.load()` en `#contenido_principal`). El usuario evaluó y rechazó tres rediseños (editorial, ERP tipo SAP Fiori y panel moderno estilo JACMEZOR) y pidió volver al aspecto inicial. No se proponen ni aplican rediseños salvo que el usuario lo pida de forma explícita: las funcionalidades nuevas se montan sobre el markup y las clases ya existentes.
- **Paleta vigente (2026-09-15):** azul institucional en **color plano, sin degradados**. Lo único que se cambió del aspecto original fueron los colores; estructura, botones y espaciados quedaron intactos.
  - `#1E3A5F` azul principal (cabeceras de tarjeta, menú activo, botones primarios) · `#16304E` su estado *hover* · `#16283F` fondo del menú lateral
  - `#2C5282` azul medio (información y cabeceras decorativas secundarias)
  - `#15803D` verde (aceptado, finalizado, éxito) · `#B45309` ámbar (**solo** pendiente / por vencer) · `#B91C1C` rojo (rechazado, error, peligro)
  - Regla: el ámbar significa "pendiente" y nada más; un adorno nunca va en ámbar ni en rojo. Los colores con transparencia conservan su alfa (`#B4530915` es un fondo tenue, no un sólido).

## Evidence on Hand

- Datos reales de la institución en la base local (áreas, tipos de documento, expedientes de prueba).
- Resultados declarados en el README de la investigación académica (90 % menos papel, 60 % menos tiempo, satisfacción 4.8/5): provienen de una tesis y no deben presentarse como métricas del producto sin confirmación.
- No hay testimonios, clientes adicionales ni precios; no se deben inventar.

## Product Principles

1. **Lo pendiente primero:** cada usuario debe ver de inmediato qué le toca atender y qué está por vencer.
2. **Trazabilidad sin excepciones:** todo movimiento, firma y descarga queda registrado con responsable y fecha.
3. **Dos clics a la acción:** registrar, derivar, responder o firmar nunca debe requerir buscar entre menús.
4. **Validez jurídica:** la firma y los documentos generados deben poder verificarse fuera del sistema.
5. **Configurable, no a medida:** lo que cambia entre entidades (marca, áreas, tipos, plazos) es configuración, no código.

## Accessibility & Inclusion

Personal con distintos niveles de familiaridad digital y ciudadanos de toda la región: textos claros en español, contraste suficiente, objetivos táctiles cómodos en celular y funcionamiento en conexiones lentas.
