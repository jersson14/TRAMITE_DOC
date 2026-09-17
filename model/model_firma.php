<?php
    require_once 'model_conexion.php';

    /**
     * Firmas digitales de los archivos del trámite (tabla firma, migración 014).
     */
    class Modelo_Firma extends conexionBD{

        /** Nombre y DNI del empleado dueño de la cuenta. */
        public function Datos_Usuario($usuario_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT u.usu_usuario, e.emple_nrodocumento,
                                         CONCAT_WS(' ', e.emple_nombre, e.emple_apepat, e.emple_apemat) AS nombre
                                  FROM usuario u LEFT JOIN empleado e ON e.empleado_id = u.empleado_id
                                  WHERE u.usu_id = ?");
            $query->execute([$usuario_id]);
            return $query->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        /** Un anexo del trámite, solo si pertenece a ese trámite. */
        public function Traer_Anexo($documento_id, $anexo_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT anexo_id, anexo_nombre, anexo_ruta FROM documento_anexo
                                  WHERE documento_id = ? AND anexo_id = ?");
            $query->execute([$documento_id, $anexo_id]);
            return $query->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        /** Cantidad de firmas registradas por anexo firmado del trámite: [anexo_id => n]. */
        public function Firmas_Por_Anexo($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT anexo_id, COUNT(*) AS total,
                                         GROUP_CONCAT(firmante_nombre ORDER BY firma_orden SEPARATOR '\n') AS firmantes
                                  FROM firma WHERE documento_id = ? AND anexo_id IS NOT NULL
                                  GROUP BY anexo_id");
            $query->execute([$documento_id]);
            $mapa = array();
            foreach($query->fetchAll(PDO::FETCH_ASSOC) as $fila){
                $mapa[(int) $fila['anexo_id']] = $fila;
            }
            return $mapa;
        }

        /**
         * Procedencia del trámite: 'INTERNO' (lo produce la entidad, se firma) o
         * 'EXTERNO' (llega de afuera, solo se verifica). Migración 021.
         */
        public function Procedencia($documento_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT doc_procedencia FROM documento WHERE documento_id = ?");
            $query->execute([$documento_id]);
            $valor = $query->fetchColumn();
            return $valor === false ? null : (string) $valor;
        }

        /**
         * ¿El remitente es un usuario del sistema?
         *
         * Es lo que separa un trámite interno de uno externo: el selector de
         * "remitente interno" de las pantallas de registro se llena con
         * usuario JOIN empleado (SP_CARGAR_DNI_UL), así que un remitente con
         * cuenta es personal de la entidad y el documento lo produce ella.
         * Cualquier otro remitente (ciudadano, otra entidad) es externo.
         */
        public function Remitente_Es_Usuario($dni){
            $dni = preg_replace('/\D/', '', (string) $dni);
            if ($dni === '') {
                return false;
            }
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT 1 FROM usuario u
                                  JOIN empleado e ON e.empleado_id = u.empleado_id
                                  WHERE e.emple_nrodocumento = ? LIMIT 1");
            $query->execute([$dni]);
            return (bool) $query->fetchColumn();
        }

        /**
         * ¿El remitente es una persona jurídica? Entonces el documento viene de otra
         * organización (empresa u otra entidad), no de la nuestra, aunque quien lo
         * presente comparta DNI con un empleado.
         */
        public function Remitente_Es_Juridica($ruc, $razonSocial, $representacion){
            return trim((string) $ruc) !== ''
                || trim((string) $razonSocial) !== ''
                || stripos((string) $representacion, 'JUR') !== false;
        }

        /**
         * Procedencia que corresponde a un registro.
         *
         * Interno solo si el remitente es un usuario del sistema Y el documento no
         * viene de otra organización. Todo lo demás es externo.
         *
         * Los dos primeros parámetros solo pueden FORZAR externo:
         *   - $marcadoExterno: la casilla "Es trámite externo" de la pantalla; un
         *     empleado puede presentar un documento a título personal.
         *   - $esJuridica: el documento lo manda una empresa u otra entidad.
         * Ninguno puede declarar interno a un remitente sin cuenta: eso habilitaría
         * a firmar en el sistema un documento que la entidad no redactó.
         */
        public function Procedencia_De_Registro($dni, $marcadoExterno = false, $esJuridica = false){
            if ($marcadoExterno || $esJuridica) {
                return 'EXTERNO';
            }
            return $this->Remitente_Es_Usuario($dni) ? 'INTERNO' : 'EXTERNO';
        }

        /** Marca el trámite recién registrado como interno o externo. */
        public function Marcar_Procedencia($documento_id, $procedencia){
            $procedencia = $procedencia === 'EXTERNO' ? 'EXTERNO' : 'INTERNO';
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("UPDATE documento SET doc_procedencia = ? WHERE documento_id = ?");
            $query->execute([$procedencia, $documento_id]);
            return $query->rowCount();
        }

        /**
         * Rutas del trámite que ya fueron firmadas y tienen su copia "(firmado)".
         *
         * Firmar el documento principal (o un anexo sin firma) no toca el archivo
         * original: crea una copia firmada como anexo nuevo. Sin esto el original
         * seguía ofreciendo "Firmar" y se podían generar copias sin fin.
         *
         * En la cofirma no aplica: ahí el anexo se actualiza a la ruta nueva, así
         * que su ruta actual ya no coincide con ningún archivo_origen.
         */
        public function Origenes_Ya_Firmados($documento_id){
            $c = conexionBD::conexionPDO();
            // archivo_origen = archivo_firmado significa que se firmó EN SITIO (al
            // registrar el trámite): no hay copia aparte, el archivo ya es el firmado.
            $query = $c->prepare("SELECT DISTINCT archivo_origen FROM firma
                                  WHERE documento_id = ? AND archivo_origen <> archivo_firmado");
            $query->execute([$documento_id]);
            return $query->fetchAll(PDO::FETCH_COLUMN) ?: [];
        }

        /** Un código libre para el sello. */
        public function Codigo_Libre(){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT 1 FROM firma WHERE firma_codigo = ?");
            do {
                $codigo = FirmaDigital::codigo();
                $query->execute([$codigo]);
            } while ($query->fetchColumn());
            return $codigo;
        }

        /**
         * Guarda la firma. Si $anexo_id es null crea el anexo con el archivo firmado;
         * si no, actualiza ese anexo (firma adicional sobre un archivo ya firmado).
         * Todo en una transacción: o queda la firma con su archivo, o nada.
         */
        public function Registrar_Firma(array $f, $anexo_id, $anexo_nombre){
            $c = conexionBD::conexionPDO();
            $c->beginTransaction();
            try {
                if($anexo_id === null){
                    $query = $c->prepare("INSERT INTO documento_anexo (documento_id, anexo_nombre, anexo_ruta, anexo_bytes, usuario_id)
                                          VALUES (?,?,?,?,?)");
                    $query->execute([$f['documento_id'], $anexo_nombre, $f['archivo_firmado'], $f['bytes'], $f['usuario_id']]);
                    $anexo_id = (int) $c->lastInsertId();
                } else {
                    $query = $c->prepare("UPDATE documento_anexo SET anexo_ruta = ?, anexo_bytes = ?
                                          WHERE anexo_id = ? AND documento_id = ?");
                    $query->execute([$f['archivo_firmado'], $f['bytes'], $anexo_id, $f['documento_id']]);
                }

                $query = $c->prepare("INSERT INTO firma (firma_codigo, documento_id, anexo_id, firma_orden, archivo_origen, archivo_firmado,
                                        hash_origen, hash_firmado, metodo, motivo, firmante_nombre, firmante_dni, cert_emisor, cert_serie,
                                        cert_desde, cert_hasta, cert_huella, cert_autofirmado, usuario_id, area_id)
                                      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $query->execute([
                    $f['codigo'], $f['documento_id'], $anexo_id, $f['orden'], $f['archivo_origen'], $f['archivo_firmado'],
                    $f['hash_origen'], $f['hash_firmado'], 'PFX', $f['motivo'], $f['firmante_nombre'], $f['firmante_dni'],
                    $f['cert_emisor'], $f['cert_serie'], $f['cert_desde'], $f['cert_hasta'], $f['cert_huella'],
                    $f['cert_autofirmado'] ? 1 : 0, $f['usuario_id'], $f['area_id'],
                ]);
                $c->commit();
                return $anexo_id;
            } catch (Throwable $e) {
                $c->rollBack();
                throw $e;
            }
        }

        /**
         * Guarda una firma sobre un archivo que no es un anexo del trámite: hoy, la
         * respuesta de atención de un área, que vive en movimiento.mov_respuesta_archivo.
         * anexo_id queda en NULL y validar_firma.php cae a archivo_firmado para
         * localizar el PDF.
         */
        public function Registrar_Firma_Archivo(array $f, $anexo_id = null){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("INSERT INTO firma (firma_codigo, documento_id, anexo_id, firma_orden, archivo_origen, archivo_firmado,
                                    hash_origen, hash_firmado, metodo, motivo, firmante_nombre, firmante_dni, cert_emisor, cert_serie,
                                    cert_desde, cert_hasta, cert_huella, cert_autofirmado, usuario_id, area_id)
                                  VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $query->execute([
                $f['codigo'], $f['documento_id'], $anexo_id, $f['orden'], $f['archivo_origen'], $f['archivo_firmado'],
                $f['hash_origen'], $f['hash_firmado'], 'PFX', $f['motivo'], $f['firmante_nombre'], $f['firmante_dni'],
                $f['cert_emisor'], $f['cert_serie'], $f['cert_desde'], $f['cert_hasta'], $f['cert_huella'],
                $f['cert_autofirmado'] ? 1 : 0, $f['usuario_id'], $f['area_id'],
            ]);
            return true;
        }

        /**
         * El tamaño del anexo cambia al firmarlo en sitio (la firma se agrega al
         * PDF). Sin esto el panel seguiría mostrando el peso del archivo sin firma.
         */
        public function Actualizar_Bytes_Anexo($anexo_id, $bytes){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("UPDATE documento_anexo SET anexo_bytes = ? WHERE anexo_id = ?");
            $query->execute([(int) $bytes, (int) $anexo_id]);
            return $query->rowCount();
        }

        /** Datos de una firma para la página pública de validación. */
        public function Buscar_Por_Codigo($codigo){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT f.*, a.anexo_nombre, a.anexo_ruta, d.doc_expediente, d.doc_asunto,
                                         DATE_FORMAT(f.firma_fecha, '%d/%m/%Y %H:%i') AS fecha_texto,
                                         DATE_FORMAT(f.cert_hasta, '%d/%m/%Y') AS cert_hasta_texto,
                                         ar.area_nombre
                                  FROM firma f
                                  JOIN documento d ON d.documento_id = f.documento_id
                                  LEFT JOIN documento_anexo a ON a.anexo_id = f.anexo_id
                                  LEFT JOIN area ar ON ar.area_cod = f.area_id
                                  WHERE f.firma_codigo = ?");
            $query->execute([$codigo]);
            return $query->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        /** Todas las firmas del mismo archivo, en orden. */
        public function Firmas_Del_Anexo($anexo_id){
            $c = conexionBD::conexionPDO();
            $query = $c->prepare("SELECT firma_codigo, firma_orden, firmante_nombre, firmante_dni, motivo, cert_autofirmado,
                                         DATE_FORMAT(firma_fecha, '%d/%m/%Y %H:%i') AS fecha_texto
                                  FROM firma WHERE anexo_id = ? ORDER BY firma_orden");
            $query->execute([$anexo_id]);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        }
    }
