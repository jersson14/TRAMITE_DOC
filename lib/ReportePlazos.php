<?php
/**
 * Reporte de plazos y productividad por área.
 *
 * Responde lo que no decían los reportes por fecha, estado o tipo de documento:
 * cuánto recibió y despachó cada área en un período, cuánto demoró en días
 * hábiles, cuántos trámites atendió dentro del plazo y qué tiene vencido hoy.
 *
 * Cómo se calcula (solo envíos principales, no copias ni atenciones):
 *  - recibidos: envíos que llegaron al área en el período.
 *  - despachados: envíos que el área derivó, finalizó o rechazó en el período.
 *  - días de atención: días hábiles entre la llegada al área y su salida.
 *  - dentro del plazo: salidas cuyos días hábiles no superaron el plazo asignado.
 *  - en curso y vencidos: situación de hoy, con el mismo semáforo de las bandejas.
 */
require_once __DIR__ . '/Plazos.php';
require_once __DIR__ . '/../model/model_conexion.php';

class ReportePlazos
{
    /**
     * @param string   $desde fecha inicial (Y-m-d)
     * @param string   $hasta fecha final (Y-m-d)
     * @param int|null $area  solo esa área, o null para todas
     */
    public static function calcular(string $desde, string $hasta, ?int $area = null): array
    {
        $pdo = (new conexionBD())->conexionPDO();
        $hastaFin = $hasta . ' 23:59:59';

        $areas = [];
        foreach ($pdo->query("SELECT area_cod, area_nombre FROM area ORDER BY area_nombre") as $a) {
            $areas[(int) $a['area_cod']] = [
                'area_id'      => (int) $a['area_cod'],
                'area'         => $a['area_nombre'],
                'recibidos'    => 0,
                'despachados'  => 0,
                'en_plazo'     => 0,
                'fuera_plazo'  => 0,
                'dias'         => [],
                'en_curso'     => 0,
                'vencidos'     => 0,
            ];
        }

        // Recorrido de cada trámite por los envíos principales
        $consulta = $pdo->prepare(
            "SELECT m.documento_id, m.movimiento_id, m.mov_fecharegistro, m.area_origen_id, m.areadestino_id,
                    m.mov_plazo_dias, d.dias_respuesta
               FROM movimiento m
               INNER JOIN documento d ON d.documento_id = m.documento_id
              WHERE m.mov_tipo = 'PRINCIPAL'
              ORDER BY m.documento_id, m.movimiento_id"
        );
        $consulta->execute();

        $porTramite = [];
        foreach ($consulta->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $porTramite[$fila['documento_id']][] = $fila;
        }

        foreach ($porTramite as $movimientos) {
            foreach ($movimientos as $n => $mov) {
                $destino = (int) $mov['areadestino_id'];
                if (!isset($areas[$destino]) || ($area !== null && $destino !== $area)) {
                    continue;
                }
                $llegada = $mov['mov_fecharegistro'];
                if ($llegada >= $desde && $llegada <= $hastaFin) {
                    $areas[$destino]['recibidos']++;
                }

                // Salida: el siguiente envío principal que sale de esa área
                $salida = null;
                for ($j = $n + 1; $j < count($movimientos); $j++) {
                    if ((int) $movimientos[$j]['area_origen_id'] === $destino) {
                        $salida = $movimientos[$j];
                        break;
                    }
                }
                if (!$salida || $salida['mov_fecharegistro'] < $desde || $salida['mov_fecharegistro'] > $hastaFin) {
                    continue;
                }

                $areas[$destino]['despachados']++;
                $dias = Plazos::habilesEntre(
                    (new DateTimeImmutable($llegada))->setTime(0, 0),
                    (new DateTimeImmutable($salida['mov_fecharegistro']))->setTime(0, 0)
                );
                $areas[$destino]['dias'][] = $dias;
                $plazo = (int) ($mov['mov_plazo_dias'] ?: $mov['dias_respuesta']);
                if ($plazo > 0) {
                    if ($dias <= $plazo) {
                        $areas[$destino]['en_plazo']++;
                    } else {
                        $areas[$destino]['fuera_plazo']++;
                    }
                }
            }
        }

        // Situación de hoy: en curso y vencidos, con el semáforo en días hábiles
        $enCurso = $pdo->query(
            "SELECT d.documento_id, d.doc_expediente, d.doc_asunto, d.doc_estatus, d.doc_fecharegistro,
                    d.doc_fecharecepcion, d.dias_respuesta, d.area_destino, a.area_nombre AS area,
                    CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS remitente
               FROM documento d
               LEFT JOIN area a ON a.area_cod = d.area_destino
              WHERE d.doc_estatus IN ('PENDIENTE', 'ACEPTADO')"
        )->fetchAll(PDO::FETCH_ASSOC);
        Plazos::agregar($enCurso);

        $vencidos = [];
        foreach ($enCurso as $fila) {
            $id = (int) $fila['area_destino'];
            if (!isset($areas[$id]) || ($area !== null && $id !== $area)) {
                continue;
            }
            $areas[$id]['en_curso']++;
            if ($fila['plazo_semaforo'] === Plazos::ROJO) {
                $areas[$id]['vencidos']++;
                $vencidos[] = [
                    'expediente' => $fila['doc_expediente'] ?: $fila['documento_id'],
                    'asunto'     => $fila['doc_asunto'],
                    'remitente'  => trim($fila['remitente']),
                    'area'       => $fila['area'] ?: 'Sin área',
                    'estado'     => $fila['doc_estatus'],
                    'limite'     => $fila['plazo_limite'],
                    'atraso'     => abs((int) $fila['plazo_restante']),
                ];
            }
        }
        usort($vencidos, fn($x, $y) => $y['atraso'] <=> $x['atraso']);

        // Solo las áreas con movimiento en el período o con trámites en curso
        $filas = [];
        foreach ($areas as $fila) {
            if ($fila['recibidos'] === 0 && $fila['despachados'] === 0 && $fila['en_curso'] === 0) {
                continue;
            }
            $conPlazo = $fila['en_plazo'] + $fila['fuera_plazo'];
            $fila['promedio'] = $fila['dias'] ? round(array_sum($fila['dias']) / count($fila['dias']), 1) : null;
            $fila['maximo'] = $fila['dias'] ? max($fila['dias']) : null;
            $fila['cumplimiento'] = $conPlazo > 0 ? round(($fila['en_plazo'] / $conPlazo) * 100) : null;
            unset($fila['dias']);
            $filas[] = $fila;
        }
        usort($filas, fn($x, $y) => [$y['vencidos'], $y['recibidos']] <=> [$x['vencidos'], $x['recibidos']]);

        $totales = [
            'recibidos'   => array_sum(array_column($filas, 'recibidos')),
            'despachados' => array_sum(array_column($filas, 'despachados')),
            'en_curso'    => array_sum(array_column($filas, 'en_curso')),
            'vencidos'    => array_sum(array_column($filas, 'vencidos')),
            'en_plazo'    => array_sum(array_column($filas, 'en_plazo')),
            'fuera_plazo' => array_sum(array_column($filas, 'fuera_plazo')),
        ];
        $conPlazo = $totales['en_plazo'] + $totales['fuera_plazo'];
        $totales['cumplimiento'] = $conPlazo > 0 ? round(($totales['en_plazo'] / $conPlazo) * 100) : null;

        return ['areas' => $filas, 'vencidos' => $vencidos, 'totales' => $totales];
    }
}
