<?php
/**
 * =====================================================
 * CLASE DE NOTIFICACIONES DE CORREO
 * =====================================================
 */
require_once __DIR__ . '/../vendor/autoload.php';
// La configuración del correo sale del panel (migración 027). Si todavía no se
// cargó ahí, Configuracion::smtp() usa config/config_email.php. Antes este archivo
// se exigía siempre: una instalación nueva sin él tiraba error al registrar.
require_once __DIR__ . '/../lib/Configuracion.php';
require_once __DIR__ . '/../model/model_conexion.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notificacion extends conexionBD {

    /**
     * Obtiene los empleados activos de un área (email + nombre).
     * Relación: usuario.area_id → empleado.emple_email
     */
    public function obtenerCorreosPorArea($id_area) {
        $c = conexionBD::conexionPDO();
        $sql = "SELECT e.emple_email AS email,
                       CONCAT_WS(' ', e.emple_nombre, e.emple_apepat, e.emple_apemat) AS nombre
                FROM usuario u
                INNER JOIN empleado e ON u.empleado_id = e.empleado_id
                WHERE u.area_id = :id_area
                  AND u.usu_estatus = 'ACTIVO'
                  AND e.emple_email IS NOT NULL
                  AND e.emple_email <> ''";
        $query = $c->prepare($sql);
        $query->bindParam(':id_area', $id_area, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el nombre de un área por su ID.
     */
    private function obtenerNombreArea($id_area) {
        if (empty($id_area)) return 'Sin especificar';
        $c = conexionBD::conexionPDO();
        $sql = "SELECT area_nombre FROM area WHERE area_cod = :id";
        $query = $c->prepare($sql);
        $query->bindParam(':id', $id_area, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['area_nombre'] : 'Área #' . $id_area;
    }

    /**
     * Obtiene el nombre completo de un empleado a partir de su usuario_id.
     */
    private function obtenerNombreUsuario($id_usuario) {
        if (empty($id_usuario)) return 'Sistema';
        $c = conexionBD::conexionPDO();
        $sql = "SELECT CONCAT_WS(' ', e.emple_nombre, e.emple_apepat, e.emple_apemat) AS nombre
                FROM usuario u
                INNER JOIN empleado e ON u.empleado_id = e.empleado_id
                WHERE u.usu_id = :id";
        $query = $c->prepare($sql);
        $query->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['nombre'] : 'Usuario #' . $id_usuario;
    }

    /**
     * Convierte el ID numérico del tipo de documento a su descripción textual.
     * Ej: 3 → 'OFICIO', 5 → 'MEMORÁNDUM'
     */
    private function obtenerDescripcionTipoDoc($tipodocumento_id) {
        if (empty($tipodocumento_id)) return 'Sin especificar';
        // Si ya viene como texto (no numérico), lo devolvemos directo
        if (!is_numeric($tipodocumento_id)) return strtoupper($tipodocumento_id);
        $c = conexionBD::conexionPDO();
        $sql = "SELECT tipodo_descripcion FROM tipo_documento WHERE tipodocumento_id = :id";
        $query = $c->prepare($sql);
        $query->bindParam(':id', $tipodocumento_id, PDO::PARAM_INT);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row ? strtoupper($row['tipodo_descripcion']) : 'Tipo #' . $tipodocumento_id;
    }

    /**
     * Obtiene tipo de documento y asunto a partir del documento_id.
     * Útil en derivaciones donde solo se tiene el ID del documento.
     *
     * @return array ['tipo_doc' => '...', 'asunto' => '...']
     */
    private function obtenerDatosDocumento($documento_id) {
        if (empty($documento_id)) return ['tipo_doc' => 'Sin especificar', 'asunto' => ''];
        $c = conexionBD::conexionPDO();
        $sql = "SELECT td.tipodo_descripcion AS tipo_doc, d.doc_asunto AS asunto
                FROM documento d
                INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
                WHERE d.documento_id = :id";
        $query = $c->prepare($sql);
        $query->bindParam(':id', $documento_id);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row
            ? ['tipo_doc' => strtoupper($row['tipo_doc']), 'asunto' => strtoupper($row['asunto'])]
            : ['tipo_doc' => 'Sin especificar', 'asunto' => ''];
    }

    /**
     * Crea y configura PHPMailer con los datos SMTP.
     */
    private function crearMailer() {
        return self::mailerDesde(Configuracion::smtp());
    }

    /**
     * PHPMailer armado con una configuración dada. Es público para que el panel
     * pueda probar una configuración ANTES de guardarla.
     */
    public static function mailerDesde(array $smtp) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp['host'];
        $mail->SMTPAuth   = $smtp['usuario'] !== '';
        $mail->Username   = $smtp['usuario'];
        $mail->Password   = $smtp['clave'];
        // 'ssl' = puerto 465 (SMTPS), 'tls' = puerto 587 (STARTTLS), '' = sin cifrar
        $mail->SMTPSecure = $smtp['seguridad'];
        $mail->SMTPAutoTLS = $smtp['seguridad'] !== '';
        $mail->Port       = (int) $smtp['puerto'];
        $mail->Timeout    = 15;   // un servidor que no responde no debe colgar el registro
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom($smtp['correo'] !== '' ? $smtp['correo'] : $smtp['usuario'], $smtp['nombre']);
        return $mail;
    }

    /**
     * Carga una plantilla HTML y reemplaza {{CLAVE}} con datos reales.
     */
    private function cargarPlantilla($archivo, $datos) {
        $ruta = __DIR__ . '/plantillas/' . $archivo;
        if (!file_exists($ruta)) return '<p>Error: Plantilla no encontrada.</p>';
        $html = file_get_contents($ruta);
        foreach ($datos as $clave => $valor) {
            $html = str_replace('{{' . $clave . '}}', htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8'), $html);
        }
        return $html;
    }

    /**
     * Envía confirmación al CIUDADANO cuando su trámite externo es registrado.
     * Incluye código de seguimiento, datos del documento y link de seguimiento.
     *
     * @param string $email_ciudadano  Correo del ciudadano
     * @param string $nombre_ciudadano Nombre completo del ciudadano
     * @param string $documento_id     Código generado (ej: D0000037)
     * @param int    $tipodocumento_id ID del tipo de documento
     * @param string $nro_documento    Número de registro del documento
     * @param string $asunto           Asunto del documento
     * @param int    $id_area_destino  ID del área destino (para obtener su nombre)
     * @param string $url_base         URL base del sistema (ej: http://midominio.com/SISTRAMITEDOC)
     * @param array  $extra            expediente, recibido (fecha y hora) y presentado (si llegó fuera del horario)
     */
    public function notificarCiudadano($email_ciudadano, $nombre_ciudadano, $documento_id, $tipodocumento_id, $nro_documento, $asunto, $id_area_destino, $url_base = '', array $extra = []) {
        if (!Configuracion::correoActivo()) return false;
        if (empty($email_ciudadano)) return false;

        $tipo_doc_texto      = $this->obtenerDescripcionTipoDoc($tipodocumento_id);
        $nombre_area_destino = $this->obtenerNombreArea($id_area_destino);
        $expediente          = !empty($extra['expediente']) ? $extra['expediente'] : $documento_id;
        $link_seguimiento    = rtrim($url_base, '/') . '/seguimiento.php?codigo=' . rawurlencode($expediente);
        $aviso_horario       = !empty($extra['presentado'])
            ? 'Su documento llegó fuera del horario de atención. Se considera presentado el ' . $extra['presentado'] . ' y los plazos se cuentan desde esa fecha.'
            : 'Su documento fue presentado dentro del horario de atención.';

        $datos = [
            'NUMERO'        => $documento_id,
            'EXPEDIENTE'    => $expediente,
            'AVISO_HORARIO' => $aviso_horario,
            'REMITENTE'     => strtoupper($nombre_ciudadano),
            'TIPO_DOC'      => $tipo_doc_texto,
            'NRO_DOCUMENTO' => $nro_documento,
            'ASUNTO'        => strtoupper($asunto),
            'AREA_DESTINO'  => $nombre_area_destino,
            'FECHA'         => $extra['recibido'] ?? date('d/m/Y H:i'),
            'LINK_SEGUIMIENTO' => $link_seguimiento,
        ];
        $html = $this->cargarPlantilla('notificacion_ciudadano.html', $datos);

        try {
            $mail = $this->crearMailer();
            $mail->isHTML(true);
            $mail->Subject = '✅ Trámite recibido: expediente ' . $expediente;
            $mail->Body    = $html;
            $mail->AltBody = "Su trámite fue registrado.\nExpediente: $expediente\nCódigo de seguimiento: $documento_id\n$aviso_horario\nAsunto: $asunto\nÁrea receptora: $nombre_area_destino\nSeguimiento: $link_seguimiento";
            $mail->addAddress($email_ciudadano, $nombre_ciudadano);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[NOTIFICACION_CIUDADANO] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Avisa al ciudadano que su trámite fue observado: qué falta y hasta cuándo
     * puede subsanarlo desde el portal (migración 025).
     */
    public function notificarObservacion($email, $nombre, $expediente, $motivo, $limite, $url_base = '') {
        if (!Configuracion::correoActivo() || empty($email)) return false;
        $link = rtrim($url_base, '/') . '/seguimiento.php?codigo=' . rawurlencode($expediente);
        $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

        $html = '<div style="font-family:Arial,sans-serif;max-width:560px;color:#1f2937;">'
            . '<h2 style="color:#B45309;margin-bottom:.5rem;">Su trámite fue observado</h2>'
            . '<p>Estimado(a) <b>' . $e(strtoupper($nombre)) . '</b>:</p>'
            . '<p>Revisamos su expediente <b>' . $e($expediente) . '</b> y necesitamos que corrija o complete lo siguiente:</p>'
            . '<div style="background:#FFFBEB;border-left:4px solid #B45309;padding:.75rem 1rem;margin:1rem 0;white-space:pre-wrap;">'
            . $e($motivo) . '</div>'
            . '<p>Tiene plazo <b>hasta el ' . $e($limite) . '</b>. Puede subsanarlo en línea, sin acudir a la entidad:</p>'
            . '<p><a href="' . $e($link) . '" style="background:#1E3A5F;color:#fff;padding:.6rem 1rem;border-radius:.3rem;text-decoration:none;">'
            . 'Subsanar mi trámite</a></p>'
            . '<p style="font-size:.85rem;color:#6b7280;">Ingrese con su N° de expediente y su DNI. '
            . 'Si no subsana dentro del plazo, el trámite podrá ser rechazado.</p></div>';

        try {
            $mail = $this->crearMailer();
            $mail->isHTML(true);
            $mail->Subject = 'Trámite observado: expediente ' . $expediente;
            $mail->Body    = $html;
            $mail->AltBody = "Su trámite $expediente fue observado.\n\nQué debe corregir:\n$motivo\n\n"
                           . "Plazo: hasta el $limite\nSubsane en línea: $link";
            $mail->addAddress($email, $nombre);
            $mail->send();
            return true;
        } catch (Exception $ex) {
            error_log('[NOTIFICACION_OBSERVACION] ' . $ex->getMessage());
            return false;
        }
    }

    /** Avisa al área que el ciudadano subsanó y puede continuar con el trámite. */
    public function notificarSubsanacion($id_area, $expediente, $texto) {
        if (!Configuracion::correoActivo()) return false;
        $correos = $this->obtenerCorreosPorArea($id_area);
        if (empty($correos)) return false;
        $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

        $html = '<div style="font-family:Arial,sans-serif;max-width:560px;color:#1f2937;">'
            . '<h2 style="color:#15803D;margin-bottom:.5rem;">Trámite subsanado</h2>'
            . '<p>El ciudadano subsanó la observación del expediente <b>' . $e($expediente) . '</b>. '
            . 'El trámite volvió a su bandeja para que lo continúe.</p>'
            . ($texto !== '' ? '<div style="background:#F0FDF4;border-left:4px solid #15803D;padding:.75rem 1rem;white-space:pre-wrap;">'
                               . $e($texto) . '</div>' : '')
            . '</div>';

        try {
            $mail = $this->crearMailer();
            $mail->isHTML(true);
            $mail->Subject = 'Trámite subsanado: expediente ' . $expediente;
            $mail->Body    = $html;
            $mail->AltBody = "El ciudadano subsanó el expediente $expediente.\n$texto";
            foreach ($correos as $c) {
                $mail->addAddress(is_array($c) ? ($c['email'] ?? reset($c)) : $c);
            }
            $mail->send();
            return true;
        } catch (Exception $ex) {
            error_log('[NOTIFICACION_SUBSANACION] ' . $ex->getMessage());
            return false;
        }
    }

    /**
     * Envía notificación cuando se registra un nuevo documento.
     *
     * @param int    $id_area_destino   ID numérico del área que recibe
     * @param int    $id_area_origen    ID numérico del área que origina/registra (0 si es externo)
     * @param string $numero            Número de expediente
     * @param string $tipo_doc          Tipo de documento
     * @param string $asunto            Asunto
     * @param string $remitente         Nombre del ciudadano/remitente
     */
    public function notificarRegistro($id_area_destino, $id_area_origen, $numero, $tipo_doc, $asunto, $remitente) {
        if (!Configuracion::correoActivo()) return false;
        $correos = $this->obtenerCorreosPorArea($id_area_destino);
        if (empty($correos)) return false;

        // Convertir ID numérico del tipo de documento a su descripción real
        $tipo_doc_texto = $this->obtenerDescripcionTipoDoc($tipo_doc);

        // Nombres de áreas obtenidos automáticamente desde la BD
        $nombre_area_destino = $this->obtenerNombreArea($id_area_destino);
        $nombre_area_origen  = ($id_area_origen > 0)
                               ? $this->obtenerNombreArea($id_area_origen)
                               : 'CIUDADANO / EXTERNO';

        // Lista de quiénes reciben el correo
        $recibido_por = implode(', ', array_column($correos, 'nombre'));

        $datos = [
            'NUMERO'       => $numero,
            'TIPO_DOC'     => $tipo_doc_texto,
            'ASUNTO'       => $asunto,
            'REMITENTE'    => $remitente,
            'AREA_ORIGEN'  => $nombre_area_origen,
            'AREA_DESTINO' => $nombre_area_destino,
            'RECIBIDO_POR' => $recibido_por ?: 'Personal del área',
            'FECHA'        => date('d/m/Y H:i'),
        ];
        $html = $this->cargarPlantilla('notificacion_registro.html', $datos);

        try {
            $mail = $this->crearMailer();
            $mail->isHTML(true);
            $mail->Subject = '📄 Nuevo Documento: ' . $asunto;
            $mail->Body    = $html;
            $mail->AltBody = "Nuevo documento registrado.\nExpediente: $numero\nAsunto: $asunto\nRemitente: $remitente\nOrigen: $nombre_area_origen → Destino: $nombre_area_destino";
            foreach ($correos as $dest) {
                $mail->addAddress($dest['email'], $dest['nombre']);
            }
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[NOTIFICACION_REGISTRO] Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía notificación cuando se deriva un documento.
     *
     * @param int    $id_area_destino   ID numérico del área que recibe
     * @param int    $id_area_origen    ID numérico del área que deriva
     * @param int    $id_usuario        ID del usuario que hace la derivación
     * @param string $numero            Número de expediente
     * @param string $tipo_doc          Tipo de documento
     * @param string $asunto            Asunto
     * @param string $tipo_accion       DERIVAR, CONTESTAR, etc.
     */
    public function notificarDerivacion($id_area_destino, $id_area_origen, $id_usuario, $numero, $tipo_doc, $asunto, $tipo_accion = 'DERIVAR') {
        if (!Configuracion::correoActivo()) return false;
        $correos = $this->obtenerCorreosPorArea($id_area_destino);
        if (empty($correos)) return false;

        // Auto-obtener tipo_doc y asunto desde la BD usando el documento_id ($numero)
        $datos_doc = $this->obtenerDatosDocumento($numero);
        $tipo_doc_texto = !empty($tipo_doc) ? $this->obtenerDescripcionTipoDoc($tipo_doc) : $datos_doc['tipo_doc'];
        $asunto_texto   = !empty($asunto)   ? $asunto : $datos_doc['asunto'];

        // Nombres automáticos desde la BD
        $nombre_area_destino = $this->obtenerNombreArea($id_area_destino);
        $nombre_area_origen  = $this->obtenerNombreArea($id_area_origen);
        $derivado_por        = $this->obtenerNombreUsuario($id_usuario);
        $recibido_por        = implode(', ', array_column($correos, 'nombre'));

        $datos = [
            'NUMERO'       => $numero,
            'TIPO_DOC'     => $tipo_doc_texto,
            'ASUNTO'       => $asunto_texto,
            'AREA_ORIGEN'  => $nombre_area_origen,
            'DERIVADO_POR' => $derivado_por,
            'AREA_DESTINO' => $nombre_area_destino,
            'RECIBIDO_POR' => $recibido_por ?: 'Personal del área',
            'TIPO_ACCION'  => $tipo_accion,
            'FECHA'        => date('d/m/Y H:i'),
        ];
        $html = $this->cargarPlantilla('notificacion_derivacion.html', $datos);

        try {
            $mail = $this->crearMailer();
            $mail->isHTML(true);
            $mail->Subject = '🔁 Documento Derivado: ' . $asunto;
            $mail->Body    = $html;
            $mail->AltBody = "Documento derivado.\nExpediente: $numero\nAsunto: $asunto\nOrigen: $nombre_area_origen ({$derivado_por}) → Destino: $nombre_area_destino\nRecibido por: $recibido_por";
            foreach ($correos as $dest) {
                $mail->addAddress($dest['email'], $dest['nombre']);
            }
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[NOTIFICACION_DERIVACION] Error: ' . $e->getMessage());
            return false;
        }
    }
}
