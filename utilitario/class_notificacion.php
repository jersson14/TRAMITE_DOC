<?php
/**
 * =====================================================
 * CLASE DE NOTIFICACIONES DE CORREO
 * =====================================================
 */
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config_email.php';
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
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USERNAME;
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = EMAIL_SECURE;  // 'ssl' para Hostinger puerto 465
        $mail->Port       = EMAIL_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(EMAIL_FROM_EMAIL, EMAIL_FROM_NAME);
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
     */
    public function notificarCiudadano($email_ciudadano, $nombre_ciudadano, $documento_id, $tipodocumento_id, $nro_documento, $asunto, $id_area_destino, $url_base = '') {
        if (!EMAIL_ENABLED) return false;
        if (empty($email_ciudadano)) return false;

        $tipo_doc_texto      = $this->obtenerDescripcionTipoDoc($tipodocumento_id);
        $nombre_area_destino = $this->obtenerNombreArea($id_area_destino);
        $link_seguimiento    = rtrim($url_base, '/') . '/seguimiento.php';

        $datos = [
            'NUMERO'        => $documento_id,
            'REMITENTE'     => strtoupper($nombre_ciudadano),
            'TIPO_DOC'      => $tipo_doc_texto,
            'NRO_DOCUMENTO' => $nro_documento,
            'ASUNTO'        => strtoupper($asunto),
            'AREA_DESTINO'  => $nombre_area_destino,
            'FECHA'         => date('d/m/Y H:i'),
            'LINK_SEGUIMIENTO' => $link_seguimiento,
        ];
        $html = $this->cargarPlantilla('notificacion_ciudadano.html', $datos);

        try {
            $mail = $this->crearMailer();
            $mail->isHTML(true);
            $mail->Subject = '✅ Su trámite fue registrado: ' . $documento_id;
            $mail->Body    = $html;
            $mail->AltBody = "Su trámite fue registrado.\nCódigo: $documento_id\nAsunto: $asunto\nÁrea receptora: $nombre_area_destino\nSeguimiento: $link_seguimiento";
            $mail->addAddress($email_ciudadano, $nombre_ciudadano);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[NOTIFICACION_CIUDADANO] Error: ' . $e->getMessage());
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
        if (!EMAIL_ENABLED) return false;
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
        if (!EMAIL_ENABLED) return false;
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
