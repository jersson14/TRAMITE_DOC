<?php
    require_once 'model_conexion.php';

    class Modelo_Dashboard extends conexionBD{

        const ESTADOS_EN_CURSO = "'PENDIENTE','ACEPTADO'";
        const DIAS_DETENIDO = 15;

        /** Documentos con días desde su último movimiento (dias_respuesta = 0 significa sin plazo). */
        private function consultaBandeja(): string {
            return "SELECT d.documento_id, d.doc_estatus, d.doc_asunto, d.doc_nrodocumento, td.tipodo_descripcion AS tipo,
                           CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS remitente,
                           o.area_nombre AS origen, d.area_destino, d.dias_respuesta,
                           COALESCE(m.ultimo, d.doc_fecharegistro) AS ultimo_movimiento,
                           DATEDIFF(NOW(), COALESCE(m.ultimo, d.doc_fecharegistro)) AS dias
                    FROM documento d
                    INNER JOIN tipo_documento td ON td.tipodocumento_id = d.tipodocumento_id
                    LEFT JOIN area o ON o.area_cod = d.area_origen
                    LEFT JOIN (SELECT documento_id, MAX(mov_fecharegistro) AS ultimo FROM movimiento GROUP BY documento_id) m
                           ON m.documento_id = d.documento_id
                    WHERE d.doc_estatus IN (" . self::ESTADOS_EN_CURSO . ")";
        }

        /** Clasifica el plazo de un documento: vencido, por_vencer, en_plazo o sin_plazo. */
        public static function clasificarPlazo(int $dias, int $plazo): array {
            if ($plazo <= 0) {
                return ['estado' => 'sin_plazo', 'restante' => null];
            }
            $restante = $plazo - $dias;
            if ($restante < 0) {
                return ['estado' => 'vencido', 'restante' => $restante];
            }
            return ['estado' => $restante <= 1 ? 'por_vencer' : 'en_plazo', 'restante' => $restante];
        }

        private function formatearDocumento(array $d): array {
            $plazo = self::clasificarPlazo((int) $d['dias'], (int) $d['dias_respuesta']);
            return [
                'codigo'        => $d['documento_id'],
                'estatus'       => $d['doc_estatus'],
                'tipo'          => $d['tipo'],
                'numero'        => $d['doc_nrodocumento'],
                'asunto'        => $d['doc_asunto'],
                'remitente'     => trim($d['remitente']),
                'origen'        => $d['origen'],
                'dias'          => (int) $d['dias'],
                'plazo'         => (int) $d['dias_respuesta'],
                'restante'      => $plazo['restante'],
                'estado_plazo'  => $plazo['estado'],
                'detenido'      => (int) $d['dias'] >= self::DIAS_DETENIDO,
                'ultimo_movimiento' => $d['ultimo_movimiento'],
            ];
        }

        /** Lo más urgente primero: vencidos, por vencer y luego los más antiguos. */
        private function ordenarPorUrgencia(array $docs): array {
            $rango = ['vencido' => 0, 'por_vencer' => 1, 'en_plazo' => 2, 'sin_plazo' => 2];
            usort($docs, function ($a, $b) use ($rango) {
                return [$rango[$a['estado_plazo']], -$a['dias']] <=> [$rango[$b['estado_plazo']], -$b['dias']];
            });
            return $docs;
        }

        public function Resumen_Area(int $area): array {
            $c = conexionBD::conexionPDO();
            $query = $c->prepare($this->consultaBandeja() . " AND d.area_destino = ?");
            $query->execute([$area]);
            $docs = array_map([$this, 'formatearDocumento'], $query->fetchAll(PDO::FETCH_ASSOC));

            $finalizados = $c->prepare("SELECT COUNT(DISTINCT documento_id) FROM movimiento
                                        WHERE areadestino_id = ? AND mov_estatus = 'FINALIZADO'
                                        AND mov_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')");
            $finalizados->execute([$area]);

            $contar = function (callable $filtro) use ($docs) {
                return count(array_filter($docs, $filtro));
            };

            return [
                'contadores' => [
                    'por_atender'     => $contar(fn($d) => $d['estatus'] === 'PENDIENTE'),
                    'en_atencion'     => $contar(fn($d) => $d['estatus'] === 'ACEPTADO'),
                    'vencidos'        => $contar(fn($d) => $d['estado_plazo'] === 'vencido'),
                    'por_vencer'      => $contar(fn($d) => $d['estado_plazo'] === 'por_vencer'),
                    'finalizados_mes' => (int) $finalizados->fetchColumn(),
                ],
                'lista' => array_slice($this->ordenarPorUrgencia($docs), 0, 8),
                'total_en_curso' => count($docs),
            ];
        }

        public function Resumen_Global(): array {
            $c = conexionBD::conexionPDO();

            $estados = ['PENDIENTE' => 0, 'ACEPTADO' => 0, 'FINALIZADO' => 0, 'RECHAZADO' => 0];
            foreach ($c->query("SELECT doc_estatus, COUNT(*) AS n FROM documento GROUP BY doc_estatus") as $fila) {
                $estados[$fila['doc_estatus']] = (int) $fila['n'];
            }

            $registros = $c->query("SELECT SUM(DATE(doc_fecharegistro) = CURDATE()) AS hoy,
                                           SUM(doc_fecharegistro >= DATE_FORMAT(NOW(), '%Y-%m-01')) AS mes
                                    FROM documento")->fetch(PDO::FETCH_ASSOC);

            $personal = $c->query("SELECT (SELECT COUNT(*) FROM usuario WHERE usu_estatus = 'ACTIVO') AS usuarios,
                                          (SELECT COUNT(*) FROM empleado WHERE emple_estatus = 'ACTIVO') AS empleados")->fetch(PDO::FETCH_ASSOC);

            // Red de áreas: cada área activa con sus expedientes en curso y vencidos
            $areas = [];
            foreach ($c->query("SELECT area_cod, area_nombre FROM area WHERE area_estado = 'ACTIVO' ORDER BY area_cod") as $a) {
                $areas[(int) $a['area_cod']] = ['id' => (int) $a['area_cod'], 'nombre' => $a['area_nombre'],
                                                'en_curso' => 0, 'vencidos' => 0, 'mas_antiguo' => 0];
            }
            $vencidosTotal = 0;
            foreach ($c->query($this->consultaBandeja()) as $fila) {
                $doc = $this->formatearDocumento($fila);
                $id = (int) $fila['area_destino'];
                if (!isset($areas[$id])) {
                    continue;
                }
                $areas[$id]['en_curso']++;
                $areas[$id]['mas_antiguo'] = max($areas[$id]['mas_antiguo'], $doc['dias']);
                if ($doc['estado_plazo'] === 'vencido') {
                    $areas[$id]['vencidos']++;
                    $vencidosTotal++;
                }
            }

            $recientes = $c->query("SELECT m.documento_id AS codigo, m.mov_fecharegistro AS fecha, m.mov_estatus AS estatus,
                                           COALESCE(ao.area_nombre, 'EXTERNO') AS origen, ad.area_nombre AS destino
                                    FROM movimiento m
                                    LEFT JOIN area ao ON ao.area_cod = m.area_origen_id
                                    LEFT JOIN area ad ON ad.area_cod = m.areadestino_id
                                    ORDER BY m.mov_fecharegistro DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);

            return [
                'estados'    => $estados,
                'vencidos'   => $vencidosTotal,
                'registrados_hoy' => (int) $registros['hoy'],
                'registrados_mes' => (int) $registros['mes'],
                'usuarios'   => (int) $personal['usuarios'],
                'empleados'  => (int) $personal['empleados'],
                'areas'      => array_values($areas),
                'recientes'  => $recientes,
            ];
        }
    }
?>
