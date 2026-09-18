<?php
    require_once 'model_conexion.php';

    class Modelo_Tramite extends conexionBD{

        /**
         * Guarda los archivos adicionales de un trámite.
         * $anexos viene de Seguridad::guardarArchivos() y $rutaBase es la carpeta
         * relativa donde quedaron, por ejemplo controller/tramite/documentos/.
         */
        /** Devuelve los anexo_id creados, para vincularlos después con cada envío. */
        public function Registrar_Anexos($documento_id, array $anexos, $rutaBase, $idusu){
            if(empty($documento_id) || empty($anexos)){
                return array();
            }
            $c = conexionBD::conexionPDO();
            $sql = "INSERT INTO documento_anexo (documento_id, anexo_nombre, anexo_ruta, anexo_bytes, usuario_id)
                    VALUES (?,?,?,?,?)";
            $query = $c->prepare($sql);
            $ids = array();
            foreach($anexos as $anexo){
                $query->execute([
                    $documento_id,
                    $anexo['original'],
                    rtrim($rutaBase,'/').'/'.$anexo['nombre'],
                    $anexo['bytes'],
                    $idusu > 0 ? $idusu : null
                ]);
                $ids[] = (int) $c->lastInsertId();
            }
            return $ids;
        }

        /** Último movimiento del trámite; sirve para saber cuáles crea un envío nuevo. */
        public function Ultimo_Movimiento($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT IFNULL(MAX(movimiento_id), 0) FROM movimiento WHERE documento_id = ?");
            $query->execute([$documento_id]);
            return (int) $query->fetchColumn();
        }

        /**
         * Vincula los anexos con los movimientos que creó un envío: el principal y
         * sus copias, que son los posteriores a $desdeMovimiento. Así el historial
         * muestra cada anexo junto al documento con el que viajó.
         */
        public function Vincular_Anexos($documento_id, array $anexoIds, $desdeMovimiento){
            if(empty($anexoIds)){
                return 0;
            }
            $c = conexionBD::conexionPDO();
            $marcas = implode(',', array_fill(0, count($anexoIds), '?'));
            $sql = "INSERT IGNORE INTO movimiento_anexo (movimiento_id, anexo_id)
                    SELECT m.movimiento_id, a.anexo_id
                      FROM movimiento m
                      INNER JOIN documento_anexo a ON a.documento_id = m.documento_id
                     WHERE m.documento_id = ?
                       AND m.movimiento_id > ?
                       AND a.anexo_id IN ($marcas)";
            $query = $c->prepare($sql);
            $query->execute(array_merge([$documento_id, (int) $desdeMovimiento], array_map('intval', $anexoIds)));
            return $query->rowCount();
        }

        /**
         * Acuse de recepción del área de destino: marca el envío principal que le
         * llegó (no las copias) con la fecha y quién lo recibió. Solo la primera
         * vez: volver a aceptar no cambia la fecha original.
         */
        public function Registrar_Acuse_Destino($documento_id, $usuario_id){
            $c = conexionBD::conexionPDO();
            // El envío pasa a ACEPTADO: así el historial no muestra "PENDIENTE" junto
            // a "Recibido". La derivación (migración 011) cierra envíos PENDIENTE o ACEPTADO.
            $query = $c->prepare("UPDATE movimiento m
                                  INNER JOIN documento d ON d.documento_id = m.documento_id
                                     SET m.mov_recibido_fecha = NOW(), m.mov_recibido_usuario = ?,
                                         m.mov_estatus = IF(m.mov_estatus = 'PENDIENTE', 'ACEPTADO', m.mov_estatus)
                                   WHERE m.documento_id = ?
                                     AND m.areadestino_id = d.area_destino
                                     AND m.mov_tipo = 'PRINCIPAL'
                                     AND m.mov_recibido_fecha IS NULL");
            $query->execute([$usuario_id, $documento_id]);
            return $query->rowCount();
        }

        /**
         * Acuse de un área que recibió el trámite en copia. No toca el estado del
         * trámite: solo deja constancia de que esa área lo vio.
         * Devuelve -1 si el área no recibió copia de este trámite.
         */
        public function Registrar_Acuse_Copia($documento_id, $area_id, $usuario_id){
            $c = conexionBD::conexionPDO();
            $existe = $c->prepare("SELECT COUNT(*) FROM movimiento
                                    WHERE documento_id = ? AND areadestino_id = ? AND mov_tipo IN ('COPIA', 'ATENCION')");
            $existe->execute([$documento_id, $area_id]);
            if((int) $existe->fetchColumn() === 0){
                return -1;
            }
            $query = $c->prepare("UPDATE movimiento
                                     SET mov_recibido_fecha = NOW(), mov_recibido_usuario = ?
                                   WHERE documento_id = ? AND areadestino_id = ?
                                     AND mov_tipo IN ('COPIA', 'ATENCION')
                                     AND mov_recibido_fecha IS NULL");
            $query->execute([$usuario_id, $documento_id, $area_id]);
            return $query->rowCount();
        }

        /**
         * Pide atención a otras áreas al derivar. Cada una recibe su propio envío
         * (mov_tipo ATENCION) con su plazo en días hábiles y debe responder.
         * $atenciones = [['area' => int, 'plazo' => int], ...]
         */
        public function Registrar_Atenciones($iddo, $orig, array $atenciones, $desc, $idusu, $ruta, $acc){
            if(empty($atenciones)){
                return 0;
            }
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_fecharegistro,
                                                          mov_descripcion, mov_estatus, usuario_id, mov_archivo, mov_acciones,
                                                          mov_tipo, mov_plazo_dias)
                                  VALUES (?, ?, ?, NOW(), ?, 'PENDIENTE', ?, ?, ?, 'ATENCION', ?)");
            $total = 0;
            foreach($atenciones as $a){
                $query->execute([$iddo, $orig, $a['area'], 'ATENCIÓN - ' . $desc, $idusu, $ruta, $acc,
                                 $a['plazo'] > 0 ? $a['plazo'] : null]);
                $total++;
            }
            return $total;
        }

        /** Atenciones pedidas en un trámite, con su respuesta si ya la hay. */
        public function Listar_Atenciones($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT m.movimiento_id, m.areadestino_id AS area_id, a.area_nombre AS area,
                                         o.area_nombre AS solicitada_por, m.mov_fecharegistro AS fecha,
                                         m.mov_plazo_dias AS plazo, m.mov_estatus AS estado,
                                         DATE_FORMAT(m.mov_recibido_fecha, '%d/%m/%Y %H:%i') AS recibido_fecha,
                                         m.mov_respuesta AS respuesta,
                                         DATE_FORMAT(m.mov_respuesta_fecha, '%d/%m/%Y %H:%i') AS respuesta_fecha,
                                         m.mov_respuesta_archivo AS respuesta_archivo,
                                         (SELECT COALESCE(NULLIF(TRIM(CONCAT_WS(' ', e.emple_nombre, e.emple_apepat)), ''), u.usu_usuario)
                                            FROM usuario u LEFT JOIN empleado e ON e.empleado_id = u.empleado_id
                                           WHERE u.usu_id = m.mov_respuesta_usuario) AS respuesta_por
                                    FROM movimiento m
                                    LEFT JOIN area a ON a.area_cod = m.areadestino_id
                                    LEFT JOIN area o ON o.area_cod = m.area_origen_id
                                   WHERE m.documento_id = ? AND m.mov_tipo = 'ATENCION'
                                   ORDER BY m.movimiento_id");
            $query->execute([$documento_id]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }

        /**
         * Respuesta de un área a la atención que se le pidió (la última sin responder).
         * Devuelve -1 si al área no se le pidió atención en este trámite,
         * 0 si ya había respondido, 1 si quedó registrada.
         */
        public function Responder_Atencion($documento_id, $area_id, $usuario_id, $texto, $archivo){
            $c = conexionBD::conexionPDO();
            $existe = $c->prepare("SELECT COUNT(*) FROM movimiento WHERE documento_id = ? AND areadestino_id = ? AND mov_tipo = 'ATENCION'");
            $existe->execute([$documento_id, $area_id]);
            if((int) $existe->fetchColumn() === 0){
                return -1;
            }
            $query = $c->prepare("UPDATE movimiento
                                     SET mov_respuesta = ?, mov_respuesta_fecha = NOW(), mov_respuesta_usuario = ?,
                                         mov_respuesta_archivo = ?, mov_estatus = 'ATENDIDO',
                                         mov_recibido_fecha = COALESCE(mov_recibido_fecha, NOW()),
                                         mov_recibido_usuario = COALESCE(mov_recibido_usuario, ?)
                                   WHERE documento_id = ? AND areadestino_id = ? AND mov_tipo = 'ATENCION'
                                     AND mov_respuesta_fecha IS NULL
                                   ORDER BY movimiento_id DESC
                                   LIMIT 1");
            $query->execute([$texto, $usuario_id, $archivo, $usuario_id, $documento_id, $area_id]);
            return $query->rowCount();
        }

        /** Solo el área a la que va dirigido el trámite puede aceptarlo o rechazarlo. */
        public function Es_Area_Destino($documento_id, $area_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT 1 FROM documento WHERE documento_id = ? AND area_destino = ? LIMIT 1");
            $query->execute([$documento_id, $area_id]);
            return (bool) $query->fetchColumn();
        }

        /**
         * Guarda el número completo del documento (migración 026). Los procedimientos
         * de registro lo reciben en 15 caracteres y "001-2026-DIRESA-APURÍMAC/CONT" no
         * cabe, así que se reescribe aquí justo después de insertar.
         */
        public function Actualizar_Nro_Documento($documento_id, $numero){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("UPDATE documento SET doc_nrodocumento = ? WHERE documento_id = ?");
            $query->execute([$numero, $documento_id]);
            return $query->rowCount();
        }

        /** Nombre, correo y expediente del remitente, para avisarle por correo. */
        public function Traer_Datos_Ciudadano($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT doc_emailremitente AS email, doc_expediente AS expediente,
                                         CONCAT_WS(' ', doc_nombreremitente, doc_apepatremitente, doc_apematremitente) AS nombre
                                  FROM documento WHERE documento_id = ?");
            $query->execute([$documento_id]);
            return $query->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        /** Documento principal del trámite (el archivo con que se registró). */
        public function Traer_Archivo_Principal($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT doc_archivo, doc_expediente, doc_fecharegistro,
                                         DATE_FORMAT(doc_fecharegistro, '%d/%m/%Y %H:%i') AS fecha_texto
                                  FROM documento WHERE documento_id = ?");
            $query->execute([$documento_id]);
            $fila = $query->fetch(PDO::FETCH_ASSOC);
            return $fila ? $fila : null;
        }

        /**
         * Un usuario de área solo puede ver los archivos de trámites que pasaron
         * por su área: los que originó, los que recibió o los que le derivaron.
         */
        public function Area_Puede_Ver($documento_id, $area_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT 1 FROM documento d
                                  WHERE d.documento_id = ?
                                    AND (d.area_origen = ? OR d.area_destino = ?
                                         OR EXISTS (SELECT 1 FROM movimiento m
                                                    WHERE m.documento_id = d.documento_id
                                                      AND (m.area_origen_id = ? OR m.areadestino_id = ?)))
                                  LIMIT 1");
            $query->execute([$documento_id, $area_id, $area_id, $area_id, $area_id]);
            return (bool) $query->fetchColumn();
        }

        /**
         * Trámites a los que pertenece un archivo, buscándolo en todos los lugares
         * donde el sistema guarda rutas: documento principal, anexos, documento de
         * cada envío, respuesta de una atención, copias firmadas y subsanaciones.
         */
        public function Documentos_De_Archivo($ruta){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT documento_id FROM documento WHERE doc_archivo = ?
                                  UNION SELECT documento_id FROM documento_anexo WHERE anexo_ruta = ?
                                  UNION SELECT documento_id FROM movimiento WHERE mov_archivo = ? OR mov_respuesta_archivo = ?
                                  UNION SELECT documento_id FROM firma WHERE archivo_firmado = ? OR archivo_origen = ?
                                  UNION SELECT documento_id FROM observacion WHERE sub_archivo = ?");
            $query->execute([$ruta, $ruta, $ruta, $ruta, $ruta, $ruta, $ruta]);
            return array_column($query->fetchAll(PDO::FETCH_ASSOC), 'documento_id');
        }

        public function Listar_Anexos($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("CALL SP_LISTAR_ANEXOS(?)");
            $query->execute([$documento_id]);
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            $query->closeCursor();
            $arreglo = array();
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
        }

        public function Listar_Tramite(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TRAMITE()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }

        public function Cargar_Select_Tipo(){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_CARGAR_SELECT_TIPO()";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_Tramite($dni,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$arp,$ard,$tip
        ,$ndo,$asu,$ruta,$fol,$idusu,$acc,$obs,$tre,$copias=array()){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_TRAMITE(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$dni);
            $query ->bindParam(2,$nom);
            $query ->bindParam(3,$apt);
            $query ->bindParam(4,$apm);
            $query ->bindParam(5,$cel);
            $query ->bindParam(6,$ema);
            $query ->bindParam(7,$dir);
            $query ->bindParam(8,$vpresentacion);
            $query ->bindParam(9,$ruc);
            $query ->bindParam(10,$raz);
            $query ->bindParam(11,$arp);
            $query ->bindParam(12,$ard);
            $query ->bindParam(13,$tip);
            $query ->bindParam(14,$ndo);
            $query ->bindParam(15,$asu);
            $query ->bindParam(16,$ruta);
            $query ->bindParam(17,$fol);
            $query ->bindParam(18,$idusu);
            $query ->bindParam(19,$acc);
            $query ->bindParam(20,$obs);
            $query ->bindParam(21,$tre);

            $query ->execute();
            $documento_id = null;
            if($row=$query->fetchColumn()){
                $documento_id = $row;
            }
            
            // Cerrar el cursor del stored procedure antes de ejecutar nuevas consultas
            $query->closeCursor();
            
            // Insertar copias si existen
            if(!empty($copias) && is_array($copias)){
                foreach($copias as $area_copia_id){
                    if(!empty($area_copia_id)){
                        $sql_copia = "INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_descripcion, mov_estatus, usuario_id, mov_acciones, mov_archivo, mov_tipo)
                                     VALUES (?, ?, ?, ?, 'PENDIENTE', ?, ?, ?, 'COPIA')";
                        $query_copia = $c->prepare($sql_copia);
                        $descripcion_copia = "COPIA - " . $asu;
                        // La copia lleva el mismo archivo: sin él, el área copiada no podía abrir el documento.
                        $query_copia->execute([$documento_id, $arp, $area_copia_id, $descripcion_copia, $idusu, $acc, $ruta]);
                    }
                }
            }
            
            return $documento_id;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_Tramite_ul($documentoFinal,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$arp,$ard,$tip
        ,$ndo,$asu,$ruta,$fol,$idusu,$acc,$obs,$tre,$copias=array()){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_TRAMITE_UL(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$documentoFinal);
            $query ->bindParam(2,$nom);
            $query ->bindParam(3,$apt);
            $query ->bindParam(4,$apm);
            $query ->bindParam(5,$cel);
            $query ->bindParam(6,$ema);
            $query ->bindParam(7,$dir);
            $query ->bindParam(8,$vpresentacion);
            $query ->bindParam(9,$ruc);
            $query ->bindParam(10,$raz);
            $query ->bindParam(11,$arp);
            $query ->bindParam(12,$ard);
            $query ->bindParam(13,$tip);
            $query ->bindParam(14,$ndo);
            $query ->bindParam(15,$asu);
            $query ->bindParam(16,$ruta);
            $query ->bindParam(17,$fol);
            $query ->bindParam(18,$idusu);
            $query ->bindParam(19,$acc);
            $query ->bindParam(20,$obs);
            $query ->bindParam(21,$tre);

            $query ->execute();
            $documento_id = null;
            if($row=$query->fetchColumn()){
                $documento_id = $row;
            }
            
            // Cerrar el cursor del stored procedure antes de ejecutar nuevas consultas
            $query->closeCursor();
            
            // Insertar copias si existen
            if(!empty($copias) && is_array($copias)){
                foreach($copias as $area_copia_id){
                    if(!empty($area_copia_id)){
                        $sql_copia = "INSERT INTO movimiento (documento_id, area_origen_id, areadestino_id, mov_descripcion, mov_estatus, usuario_id, mov_acciones, mov_archivo, mov_tipo)
                                     VALUES (?, ?, ?, ?, 'PENDIENTE', ?, ?, ?, 'COPIA')";
                        $query_copia = $c->prepare($sql_copia);
                        $descripcion_copia = "COPIA - " . $asu;
                        // La copia lleva el mismo archivo: sin él, el área copiada no podía abrir el documento.
                        $query_copia->execute([$documento_id, $arp, $area_copia_id, $descripcion_copia, $idusu, $acc, $ruta]);
                    }
                }
            }
            
            return $documento_id;
            conexionBD::cerrar_conexion();
        }
        public function Registrar_Tramite_Externo($dni,$nom,$apt,$apm,$cel,$ema,$dir,$vpresentacion,$ruc,$raz,$tip,$ndo,$asu,$ruta,$fol){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_REGISTRAR_TRAMITE_EXTERNO(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query ->bindParam(1,$dni);
            $query ->bindParam(2,$nom);
            $query ->bindParam(3,$apt);
            $query ->bindParam(4,$apm);
            $query ->bindParam(5,$cel);
            $query ->bindParam(6,$ema);
            $query ->bindParam(7,$dir);
            $query ->bindParam(8,$vpresentacion);
            $query ->bindParam(9,$ruc);
            $query ->bindParam(10,$raz);
            $query ->bindParam(11,$tip);
            $query ->bindParam(12,$ndo);
            $query ->bindParam(13,$asu);
            $query ->bindParam(14,$ruta);
            $query ->bindParam(15,$fol);


            $query ->execute();
            if($row=$query->fetchColumn()){
                return $row;
            }
            conexionBD::cerrar_conexion();
        }
        public function Listar_Tramite_Seguimiento($id){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TRAMITE_SEGUIMIENTO(?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->bindParam(1,$id);
            $query->execute();
            $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
            foreach($resultado as $resp){
                $arreglo["data"][]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
        }
        public function TraerRequisitos($id){
            $c = conexionBD::conexionPDO();
            $sql = "CALL SP_LISTAR_TRAE_REQUISITO(?)";
            $arreglo = array();
            $query  = $c->prepare($sql);
            $query->bindParam(1,$id);
            $query->execute();
            $resultado = $query->fetchAll();
            foreach($resultado as $resp){
                $arreglo[]=$resp;
            }
            return $arreglo;
            conexionBD::cerrar_conexion();
    }
    public function Cargar_Select_DNI(){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_CARGAR_SELECT_DNI()";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll();
        foreach($resultado as $resp){
            $arreglo[]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    public function TraerRequisitosDNI($id){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_LISTAR_TRAE_REQUISITO_DNI(?)";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->bindParam(1,$id);
        $query->execute();
        $resultado = $query->fetchAll();
        foreach($resultado as $resp){
            $arreglo[]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
}
    public function listar_total_docpendientes(){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_LISTAR_TOTAL_DOC_PENDIENTE()";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll();
        foreach($resultado as $resp){
            $arreglo[]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    public function listar_total_docaceptado(){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_TOTAL_DOCUMENTOS_ACEPTADOS()";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll();
        foreach($resultado as $resp){
            $arreglo[]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    public function listar_total_docfinalizado(){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_TOTAL_DOCUMENTOS_FINALIZADO()";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->execute();
        $resultado = $query->fetchAll();
        foreach($resultado as $resp){
            $arreglo[]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    
    public function Rechazar_Tramite($id2,$desc2,$loc){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_RECHAZAR_TRAMITE(?,?,?)";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query ->bindParam(1,$id2);
        $query ->bindParam(2,$desc2);
        $query ->bindParam(3,$loc);

        $resul = $query->execute();
        if($resul){
            return 1;
        }else{
            return 0;
        }
        conexionBD::cerrar_conexion();
    }
    ///Eliminar tramite
    public function Eliminar_Tramite($id){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_ELIMINAR_TRAMITE(?)";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query ->bindParam(1,$id);

        $resul = $query->execute();
        if($resul){
            return 1;
        }else{
            return 0;
        }
        conexionBD::cerrar_conexion();
    }
    public function Listar_Tramite_Fecha_Area($fechainicio,$fechafin,$area){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_LISTAR_TRAMITE_AREA_FECHAS(?,?,?)";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->bindParam(1,$fechainicio);
        $query->bindParam(2,$fechafin);
        $query->bindParam(3,$area);

        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach($resultado as $resp){
            $arreglo["data"][]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    public function Listar_Tramite_Fecha_Estado($fechainicio,$fechafin,$estado){
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_LISTAR_TRAMITE_AREA_ESTADO(?,?,?)";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->bindParam(1,$fechainicio);
        $query->bindParam(2,$fechafin);
        $query->bindParam(3,$estado);

        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach($resultado as $resp){
            $arreglo["data"][]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    public function Listar_Tramite_Fecha_Tipodoc($fechainicio,$fechafin,$tipodoc)
    {
        $c = conexionBD::conexionPDO();
        $sql = "CALL SP_LISTAR_TRAMITE_AREA_TIPO_DOC(?,?,?)";
        $arreglo = array();
        $query  = $c->prepare($sql);
        $query->bindParam(1,$fechainicio);
        $query->bindParam(2,$fechafin);
        $query->bindParam(3,$tipodoc);

        $query->execute();
        $resultado = $query->fetchAll(PDO::FETCH_ASSOC);
        foreach($resultado as $resp){
            $arreglo["data"][]=$resp;
        }
        return $arreglo;
        conexionBD::cerrar_conexion();
    }
    }

?>