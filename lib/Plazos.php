<?php
require_once __DIR__ . '/../model/model_conexion.php';

/**
 * Semáforo de plazos en días hábiles.
 *
 * Reemplaza a documento.dias_pasados, que tenía tres problemas: solo se
 * recalculaba cuando alguien registraba un movimiento (se congelaba), contaba
 * días calendario desde el registro y no desde que el trámite llegó al área, y
 * volvía a 0 al aceptar. Aquí se calcula en cada consulta.
 *
 * Reglas:
 *  - El plazo corre desde que el trámite llegó al área que lo tiene: el último
 *    envío principal (no copia). Se empieza a contar el día hábil siguiente.
 *  - Días hábiles: lunes a viernes que no sean feriado (tabla feriado).
 *  - dias_respuesta es el plazo en días hábiles; 0 o vacío = sin plazo.
 */
class Plazos
{
    const VERDE = 'VERDE';
    const AMBAR = 'AMBAR';        // vence hoy o el próximo día hábil
    const ROJO = 'ROJO';          // vencido
    const SIN_PLAZO = 'SIN_PLAZO';
    const CERRADO = 'CERRADO';    // finalizado o rechazado: ya no corre plazo

    private static $feriados;

    /**
     * Agrega a cada fila de un listado de trámites:
     *   plazo_dias_area  días hábiles que lleva en el área actual
     *   plazo_limite     fecha límite (dd/mm/aaaa) o null
     *   plazo_restante   días hábiles que faltan (negativo si venció) o null
     *   plazo_semaforo   VERDE | AMBAR | ROJO | SIN_PLAZO | CERRADO
     * Las filas deben traer documento_id, doc_estatus, doc_fecharegistro y dias_respuesta.
     */
    public static function agregar(array &$filas): void
    {
        if (!$filas) {
            return;
        }
        $llegadas = self::llegadasAlArea(array_column($filas, 'documento_id'));
        $hoy = new DateTimeImmutable('today');

        foreach ($filas as &$fila) {
            $estado = strtoupper((string) ($fila['doc_estatus'] ?? ''));
            $fila['plazo_dias_area'] = null;
            $fila['plazo_limite'] = null;
            $fila['plazo_restante'] = null;

            if (in_array($estado, ['FINALIZADO', 'RECHAZADO'], true)) {
                $fila['plazo_semaforo'] = self::CERRADO;
                continue;
            }

            $id = $fila['documento_id'] ?? '';
            $llegada = self::fecha($llegadas[$id] ?? ($fila['doc_fecharegistro'] ?? null));
            if (!$llegada) {
                $fila['plazo_semaforo'] = self::SIN_PLAZO;
                continue;
            }

            $fila['plazo_dias_area'] = self::habilesEntre($llegada, $hoy);

            $plazo = (int) ($fila['dias_respuesta'] ?? 0);
            if ($plazo <= 0) {
                $fila['plazo_semaforo'] = self::SIN_PLAZO;
                continue;
            }

            $limite = self::sumarHabiles($llegada, $plazo);
            $fila['plazo_limite'] = $limite->format('d/m/Y');

            if ($hoy > $limite) {
                $restante = -self::habilesEntre($limite, $hoy);
                $fila['plazo_semaforo'] = self::ROJO;
            } else {
                $restante = self::habilesEntre($hoy, $limite);
                $fila['plazo_semaforo'] = $restante <= 1 ? self::AMBAR : self::VERDE;
            }
            $fila['plazo_restante'] = $restante;
        }
        unset($fila);
    }

    /** Días hábiles en el intervalo (desde, hasta]: no cuenta el día de inicio. */
    public static function habilesEntre(DateTimeImmutable $desde, DateTimeImmutable $hasta): int
    {
        $total = 0;
        for ($d = $desde->modify('+1 day'); $d <= $hasta; $d = $d->modify('+1 day')) {
            if (self::esHabil($d)) {
                $total++;
            }
        }
        return $total;
    }

    /** Fecha que resulta de avanzar N días hábiles desde un día (sin contarlo). */
    public static function sumarHabiles(DateTimeImmutable $desde, int $dias): DateTimeImmutable
    {
        $d = $desde;
        while ($dias > 0) {
            $d = $d->modify('+1 day');
            if (self::esHabil($d)) {
                $dias--;
            }
        }
        return $d;
    }

    public static function esHabil(DateTimeImmutable $d): bool
    {
        if ((int) $d->format('N') >= 6) {
            return false;
        }
        return !isset(self::feriados()[$d->format('Y-m-d')]);
    }

    private static function feriados(): array
    {
        if (self::$feriados === null) {
            self::$feriados = [];
            try {
                $pdo = (new conexionBD())->conexionPDO();
                foreach ($pdo->query('SELECT fecha FROM feriado')->fetchAll(PDO::FETCH_COLUMN) as $f) {
                    self::$feriados[$f] = true;
                }
            } catch (Throwable $e) {
                // Sin tabla de feriados se cuentan solo los fines de semana.
                error_log('[PLAZOS] ' . $e->getMessage());
            }
        }
        return self::$feriados;
    }

    /** Fecha del último envío principal (no copia) de cada trámite. */
    private static function llegadasAlArea(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));
        if (!$ids) {
            return [];
        }
        $resultado = [];
        try {
            $pdo = (new conexionBD())->conexionPDO();
            foreach (array_chunk($ids, 500) as $bloque) {
                $marcas = implode(',', array_fill(0, count($bloque), '?'));
                $consulta = $pdo->prepare(
                    "SELECT documento_id, MAX(mov_fecharegistro)
                       FROM movimiento
                      WHERE documento_id IN ($marcas) AND mov_descripcion NOT LIKE 'COPIA - %'
                      GROUP BY documento_id"
                );
                $consulta->execute($bloque);
                foreach ($consulta->fetchAll(PDO::FETCH_NUM) as [$id, $fecha]) {
                    $resultado[$id] = $fecha;
                }
            }
        } catch (Throwable $e) {
            error_log('[PLAZOS] ' . $e->getMessage());
        }
        return $resultado;
    }

    private static function fecha($valor): ?DateTimeImmutable
    {
        if (!$valor) {
            return null;
        }
        try {
            return (new DateTimeImmutable((string) $valor))->setTime(0, 0);
        } catch (Throwable $e) {
            return null;
        }
    }
}
