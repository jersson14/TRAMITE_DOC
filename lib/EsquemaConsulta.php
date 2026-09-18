<?php
/**
 * Qué parte de la base de datos puede consultar el asistente.
 *
 * Aquí se decide dos cosas a la vez: qué tablas se le describen a la IA para
 * que sepa escribir la consulta, y qué tablas acepta después el validador
 * (ConsultaSegura). Si una tabla no está en esta lista, no se puede consultar
 * por el chat, aunque la IA la invente.
 *
 * El personal de área solo ve los trámites por los que pasó su área; esa
 * restricción no depende de la consulta que escriba la IA, la aplica
 * ConsultaSegura envolviendo documento y movimiento.
 */
class EsquemaConsulta
{
    /** Columnas que nunca se consultan, ni siquiera el administrador. */
    const COLUMNAS_VETADAS = ['usu_contra'];

    /**
     * Tablas disponibles con su descripción y columnas.
     * 'areas' => false marca las que solo ve el administrador (datos de toda la
     * institución: personal, usuarios, bitácora, firmas).
     */
    const TABLAS = [
        'documento' => [
            'que' => 'Un trámite. Cada fila es un expediente registrado (por mesa de partes, por un área o por el portal del ciudadano).',
            'areas' => true,
            'columnas' => [
                'documento_id' => 'clave interna',
                'doc_expediente' => 'número oficial, formato EXP-AAAA-NNNNNN',
                'doc_ncorrelativo' => 'código de seguimiento que se entrega al ciudadano, formato D0000041',
                'doc_dniremitente' => 'DNI del remitente',
                'doc_nombreremitente' => 'nombre del remitente',
                'doc_apepatremitente' => 'apellido paterno del remitente',
                'doc_apematremitente' => 'apellido materno del remitente',
                'doc_celularremitente' => 'celular del remitente',
                'doc_emailremitente' => 'correo del remitente',
                'doc_direccionremitente' => 'dirección del remitente',
                'doc_representacion' => 'a quién representa (NATURAL o JURIDICA)',
                'doc_ruc' => 'RUC si representa a una empresa',
                'doc_empresa' => 'razón social si representa a una empresa',
                'tipodocumento_id' => 'tipo de documento, se une con tipo_documento',
                'doc_nrodocumento' => 'número que trae el documento físico (no es el expediente)',
                'doc_folio' => 'cantidad de folios',
                'doc_asunto' => 'asunto del trámite',
                'doc_fecharegistro' => 'fecha y hora en que se registró',
                'doc_fecharecepcion' => 'fecha y hora de recepción efectiva en horario de atención',
                'doc_estatus' => 'estado actual: PENDIENTE, ACEPTADO, FINALIZADO, RECHAZADO',
                'area_origen' => 'área que lo envió (area.area_cod)',
                'area_destino' => 'área donde está ahora (area.area_cod)',
                'area_id' => 'área que lo registró (area.area_cod)',
                'dias_respuesta' => 'plazo en días hábiles acordado para responder; NULL = sin plazo',
                'dias_pasados' => 'contador antiguo en días corridos; NO usarlo para plazos',
                'doc_observaciones' => 'observaciones',
                'acciones' => 'acción pedida al área',
                'doc_archivo' => 'ruta del PDF principal',
                'id_empleado' => 'empleado que registró (empleado.empleado_id)',
            ],
        ],
        'movimiento' => [
            'que' => 'El recorrido del trámite: cada envío o derivación de un área a otra, con su acuse de recepción.',
            'areas' => true,
            'columnas' => [
                'movimiento_id' => 'clave interna',
                'documento_id' => 'trámite al que pertenece',
                'area_origen_id' => 'área que envía (area.area_cod)',
                'areadestino_id' => 'área que recibe (area.area_cod)',
                'mov_fecharegistro' => 'fecha y hora del envío',
                'mov_descripcion' => 'indicación escrita al derivar',
                'mov_estatus' => 'estado del envío: PENDIENTE, ACEPTADO, FINALIZADO, RECHAZADO, ATENDIDO',
                'mov_tipo' => 'PRINCIPAL (responsable del trámite), COPIA (solo conocimiento) o ATENCION (debe atender algo)',
                'mov_recibido_fecha' => 'fecha del acuse de recepción; NULL = todavía no lo recibieron',
                'mov_plazo_dias' => 'plazo en días hábiles pedido para la atención',
                'mov_respuesta' => 'respuesta del área a una atención',
                'mov_respuesta_fecha' => 'fecha de esa respuesta',
                'mov_archivo' => 'documento enviado en ese movimiento',
                'usuario_id' => 'usuario que hizo el envío',
                'mov_acciones' => 'acción solicitada',
            ],
        ],
        'area' => [
            'que' => 'Las áreas u oficinas de la institución.',
            'areas' => true,
            'columnas' => [
                'area_cod' => 'clave del área',
                'area_nombre' => 'nombre del área',
                'area_estado' => 'ACTIVO o INACTIVO',
                'area_fecha_registro' => 'fecha de creación',
            ],
        ],
        'tipo_documento' => [
            'que' => 'Tipos de documento admitidos (solicitud, oficio, informe, etc.).',
            'areas' => true,
            'columnas' => [
                'tipodocumento_id' => 'clave',
                'tipodo_descripcion' => 'nombre del tipo',
                'tipodo_estado' => 'ACTIVO o INACTIVO',
                'requisitos' => 'requisitos del tipo',
            ],
        ],
        'documento_anexo' => [
            'que' => 'Archivos anexos de cada trámite.',
            'areas' => true,
            'columnas' => [
                'anexo_id' => 'clave',
                'documento_id' => 'trámite',
                'anexo_nombre' => 'nombre original del archivo',
                'anexo_bytes' => 'tamaño en bytes',
                'anexo_fecha' => 'fecha en que se subió',
            ],
        ],
        'feriado' => [
            'que' => 'Feriados y días no laborables que no cuentan para los plazos.',
            'areas' => true,
            'columnas' => [
                'fecha' => 'fecha del feriado',
                'descripcion' => 'motivo',
                'tipo' => 'NACIONAL, REGIONAL o NO_LABORABLE',
            ],
        ],
        'empleado' => [
            'que' => 'Personal de la institución.',
            'areas' => false,
            'columnas' => [
                'empleado_id' => 'clave',
                'emple_nombre' => 'nombres',
                'emple_apepat' => 'apellido paterno',
                'emple_apemat' => 'apellido materno',
                'emple_nrodocumento' => 'DNI',
                'emple_email' => 'correo',
                'emple_movil' => 'celular',
                'emple_estatus' => 'ACTIVO o INACTIVO',
                'empl_modalidad' => 'modalidad de contrato',
                'emple_feccreacion' => 'fecha de registro',
            ],
        ],
        'usuario' => [
            'que' => 'Cuentas del sistema. La contraseña no se puede consultar.',
            'areas' => false,
            'columnas' => [
                'usu_id' => 'clave',
                'usu_usuario' => 'nombre de la cuenta',
                'usu_rol' => 'Administrador, Jefe de Área, Secretario (a), Mesa de Partes o Especialista',
                'usu_estatus' => 'ACTIVO o INACTIVO',
                'area_id' => 'área de la cuenta (area.area_cod)',
                'empleado_id' => 'empleado dueño de la cuenta',
                'usu_feccreacion' => 'fecha de creación',
            ],
        ],
        'bitacora' => [
            'que' => 'Registro de auditoría: quién hizo qué y cuándo.',
            'areas' => false,
            'columnas' => [
                'bit_id' => 'clave',
                'bit_fecha' => 'fecha y hora',
                'bit_usuario' => 'cuenta que actuó',
                'bit_rol' => 'rol de esa cuenta',
                'bit_accion' => 'acción: INGRESO, REGISTRO_TRAMITE, DERIVO_TRAMITE, CAMBIO_ESTADO, FIRMA_DIGITAL, etc.',
                'bit_entidad' => 'sobre qué actuó',
                'bit_entidad_id' => 'identificador de eso',
                'bit_detalle' => 'detalle en texto',
                'bit_ip' => 'dirección IP',
            ],
        ],
        'firma' => [
            'que' => 'Firmas digitales aplicadas a los documentos.',
            'areas' => false,
            'columnas' => [
                'firma_id' => 'clave',
                'firma_codigo' => 'código de verificación público',
                'documento_id' => 'trámite firmado',
                'firmante_nombre' => 'quién firmó',
                'firmante_dni' => 'DNI del firmante',
                'firma_fecha' => 'fecha y hora de la firma',
                'metodo' => 'método usado (PFX, etc.)',
                'motivo' => 'motivo de la firma',
                'cert_autofirmado' => '1 si el certificado es autofirmado',
                'area_id' => 'área del firmante',
            ],
        ],
        'comunicados' => [
            'que' => 'Avisos internos publicados al personal.',
            'areas' => false,
            'columnas' => [
                'id_comunicado' => 'clave',
                'titulo' => 'título',
                'descripcion' => 'contenido',
                'fecha_registro' => 'fecha de publicación',
                'estado' => 'NUEVO (vigente) o PASADO (archivado)',
                'com_destino' => 'TODOS, ADMINISTRADORES, SECRETARIAS o AREAS',
                'com_desde' => 'inicio de vigencia',
                'com_hasta' => 'fin de vigencia',
            ],
        ],
        'comunicado_leido' => [
            'que' => 'Acuses de lectura de los comunicados.',
            'areas' => false,
            'columnas' => [
                'id_comunicado' => 'comunicado',
                'usuario_id' => 'usuario que confirmó',
                'fecha' => 'fecha del acuse',
            ],
        ],
    ];

    /** Nombres de tabla que puede usar este rol. */
    public static function tablasPermitidas(bool $esAdmin): array
    {
        $tablas = [];
        foreach (self::TABLAS as $nombre => $datos) {
            if ($esAdmin || $datos['areas']) {
                $tablas[] = $nombre;
            }
        }
        return $tablas;
    }

    /** Descripción del esquema para el prompt, en el mismo orden de la lista. */
    public static function paraPrompt(bool $esAdmin): string
    {
        $texto = '';
        foreach (self::TABLAS as $nombre => $datos) {
            if (!$esAdmin && !$datos['areas']) {
                continue;
            }
            $texto .= "TABLA $nombre — {$datos['que']}\n";
            foreach ($datos['columnas'] as $columna => $detalle) {
                $texto .= "  - $columna: $detalle\n";
            }
            $texto .= "\n";
        }
        return $texto;
    }
}
