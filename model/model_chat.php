<?php
/**
 * Modelo para consultas del Chat con IA
 * Maneja las consultas a la base de datos para el asistente
 */

require_once __DIR__ . '/../model/model_conexion.php';

class Modelo_Chat {
    
    /**
     * Buscar expediente por número (documento_id o doc_nrodocumento)
     */
    public function buscar_expediente($numero) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        
        // Primero intentar por documento_id
        $sql = "SELECT 
                    d.documento_id,
                    CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) as remitente,
                    d.doc_nrodocumento,
                    d.doc_asunto,
                    d.doc_estatus,
                    DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y %H:%i') as fecha_registro,
                    d.area_destino,
                    d.area_origen,
                    td.tipodo_descripcion as tipo_documento,
                    d.dias_pasados,
                    d.dias_respuesta,
                    d.doc_folio,
                    d.doc_dniremitente,
                    d.doc_celularremitente,
                    d.doc_emailremitente
                FROM documento d
                INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
                WHERE d.documento_id = ? OR d.doc_nrodocumento = ?
                ORDER BY d.doc_fecharegistro DESC
                LIMIT 1";
        
        $query = $c->prepare($sql);
        $query->bindParam(1, $numero);
        $query->bindParam(2, $numero);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        
        // Si encontró resultado, obtener nombres de áreas
        if ($resultado) {
            // Obtener área actual
            if ($resultado['area_destino']) {
                $sql_area = "SELECT area_nombre FROM area WHERE area_cod = ?";
                $query_area = $c->prepare($sql_area);
                $query_area->bindParam(1, $resultado['area_destino']);
                $query_area->execute();
                $area_actual = $query_area->fetch(PDO::FETCH_ASSOC);
                $resultado['area_actual'] = $area_actual ? $area_actual['area_nombre'] : 'N/A';
            }
            
            // Obtener área origen
            if ($resultado['area_origen']) {
                $sql_area = "SELECT area_nombre FROM area WHERE area_cod = ?";
                $query_area = $c->prepare($sql_area);
                $query_area->bindParam(1, $resultado['area_origen']);
                $query_area->execute();
                $area_origen = $query_area->fetch(PDO::FETCH_ASSOC);
                $resultado['area_origen'] = $area_origen ? $area_origen['area_nombre'] : 'N/A';
            }
        }
        
        $conexion->cerrar_conexion();
        return $resultado;
    }
    
    /**
     * Buscar documentos por nombre de remitente
     */
    public function buscar_por_remitente($nombre, $area_id = null) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        
        $sql = "SELECT 
                    d.documento_id,
                    CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) as remitente,
                    d.doc_nrodocumento,
                    d.doc_asunto,
                    d.doc_estatus,
                    DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y') as fecha_registro,
                    d.area_destino,
                    td.tipodo_descripcion as tipo_documento
                FROM documento d
                INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
                WHERE (d.doc_nombreremitente LIKE ? 
                    OR d.doc_apepatremitente LIKE ? 
                    OR d.doc_apematremitente LIKE ?)";
        
        if ($area_id) {
            $sql .= " AND (d.area_origen = ? OR d.area_destino = ?)";
        }
        
        $sql .= " ORDER BY d.doc_fecharegistro DESC LIMIT 10";
        
        $query = $c->prepare($sql);
        $search_term = "%$nombre%";
        $query->bindParam(1, $search_term);
        $query->bindParam(2, $search_term);
        $query->bindParam(3, $search_term);
        
        if ($area_id) {
            $query->bindParam(4, $area_id);
            $query->bindParam(5, $area_id);
        }
        
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener nombres de áreas para cada resultado
        foreach ($resultados as &$doc) {
            if ($doc['area_destino']) {
                $sql_area = "SELECT area_nombre FROM area WHERE area_cod = ?";
                $query_area = $c->prepare($sql_area);
                $query_area->bindParam(1, $doc['area_destino']);
                $query_area->execute();
                $area = $query_area->fetch(PDO::FETCH_ASSOC);
                $doc['area_actual'] = $area ? $area['area_nombre'] : 'N/A';
            }
        }
        
        $conexion->cerrar_conexion();
        return $resultados;
    }
    
    /**
     * Listar documentos pendientes de un área
     */
    public function listar_pendientes($area_id) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        $sql = "SELECT 
                    d.documento_id,
                    CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) as remitente,
                    d.doc_nrodocumento,
                    d.doc_asunto,
                    DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y') as fecha_registro,
                    d.dias_pasados,
                    td.tipodo_descripcion as tipo_documento,
                    d.doc_folio
                FROM documento d
                INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
                WHERE d.area_destino = ? 
                AND d.doc_estatus = 'PENDIENTE'
                ORDER BY d.doc_fecharegistro DESC
                LIMIT 20";
        
        $query = $c->prepare($sql);
        $query->bindParam(1, $area_id);
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $conexion->cerrar_conexion();
        return $resultado;
    }
    
    /**
     * Obtener seguimiento de un documento
     */
    public function obtener_seguimiento($doc_id) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        
        $sql = "SELECT 
                    m.movimiento_id,
                    m.documento_id,
                    DATE_FORMAT(m.mov_fecharegistro, '%d/%m/%Y %H:%i') as fecha_movimiento,
                    m.mov_descripcion,
                    m.mov_estatus,
                    m.area_origen_id,
                    m.areadestino_id,
                    m.usuario_id
                FROM movimiento m
                WHERE m.documento_id = ?
                ORDER BY m.mov_fecharegistro ASC";
        
        $query = $c->prepare($sql);
        $query->bindParam(1, $doc_id);
        $query->execute();
        $resultados = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener nombres de áreas y usuarios
        foreach ($resultados as &$mov) {
            // Área origen
            if ($mov['area_origen_id']) {
                $sql_area = "SELECT area_nombre FROM area WHERE area_cod = ?";
                $query_area = $c->prepare($sql_area);
                $query_area->bindParam(1, $mov['area_origen_id']);
                $query_area->execute();
                $area = $query_area->fetch(PDO::FETCH_ASSOC);
                $mov['area_origen'] = $area ? $area['area_nombre'] : 'N/A';
            }
            
            // Área destino
            if ($mov['areadestino_id']) {
                $sql_area = "SELECT area_nombre FROM area WHERE area_cod = ?";
                $query_area = $c->prepare($sql_area);
                $query_area->bindParam(1, $mov['areadestino_id']);
                $query_area->execute();
                $area = $query_area->fetch(PDO::FETCH_ASSOC);
                $mov['area_destino'] = $area ? $area['area_nombre'] : 'N/A';
            }
            
            // Usuario
            if ($mov['usuario_id']) {
                $sql_user = "SELECT CONCAT_WS(' ', usu_nombre, usu_apepat, usu_apemat) as usuario FROM usuario WHERE usuario_id = ?";
                $query_user = $c->prepare($sql_user);
                $query_user->bindParam(1, $mov['usuario_id']);
                $query_user->execute();
                $user = $query_user->fetch(PDO::FETCH_ASSOC);
                $mov['usuario'] = $user ? $user['usuario'] : 'N/A';
            }
        }
        
        $conexion->cerrar_conexion();
        return $resultados;
    }
    
    /**
     * Estadísticas del área
     */
    public function estadisticas_area($area_id) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN doc_estatus = 'PENDIENTE' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN doc_estatus = 'FINALIZADO' THEN 1 ELSE 0 END) as finalizados,
                    SUM(CASE WHEN doc_estatus = 'RECHAZADO' THEN 1 ELSE 0 END) as rechazados,
                    SUM(CASE WHEN doc_estatus = 'ACEPTADO' THEN 1 ELSE 0 END) as aceptados
                FROM documento
                WHERE area_destino = ?";
        
        $query = $c->prepare($sql);
        $query->bindParam(1, $area_id);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        
        $conexion->cerrar_conexion();
        return $resultado;
    }
    
    /**
     * Buscar documentos por rango de fechas
     */
    public function buscar_por_fecha($fecha_inicio, $fecha_fin, $area_id = null) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        
        $sql = "SELECT 
                    d.documento_id,
                    CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) as remitente,
                    d.doc_nrodocumento,
                    d.doc_asunto,
                    d.doc_estatus,
                    DATE_FORMAT(d.doc_fecharegistro, '%d/%m/%Y') as fecha_registro,
                    td.tipodo_descripcion as tipo_documento
                FROM documento d
                INNER JOIN tipo_documento td ON d.tipodocumento_id = td.tipodocumento_id
                WHERE DATE(d.doc_fecharegistro) BETWEEN ? AND ?";
        
        if ($area_id) {
            $sql .= " AND (d.area_origen = ? OR d.area_destino = ?)";
        }
        
        $sql .= " ORDER BY d.doc_fecharegistro DESC LIMIT 50";
        
        $query = $c->prepare($sql);
        $query->bindParam(1, $fecha_inicio);
        $query->bindParam(2, $fecha_fin);
        
        if ($area_id) {
            $query->bindParam(3, $area_id);
            $query->bindParam(4, $area_id);
        }
        
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $conexion->cerrar_conexion();
        return $resultado;
    }
    
    /**
     * Contar documentos por estado en un área
     */
    public function contar_por_estado($area_id) {
        $conexion = new conexionBD();
        $c = $conexion->conexionPDO();
        $sql = "SELECT 
                    doc_estatus as estado,
                    COUNT(*) as cantidad
                FROM documento
                WHERE area_destino = ?
                GROUP BY doc_estatus";
        
        $query = $c->prepare($sql);
        $query->bindParam(1, $area_id);
        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        
        $conexion->cerrar_conexion();
        return $resultado;
    }
}

?>
