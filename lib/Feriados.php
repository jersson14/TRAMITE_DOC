<?php
/**
 * Feriados nacionales del Perú para un año.
 *
 * Son los que la entidad cargaba a mano (migración 012). Jueves y Viernes Santo
 * cambian cada año y se calculan desde la fecha de Pascua. Si el Gobierno crea un
 * feriado nuevo o declara días no laborables, se agregan en la pantalla de feriados.
 */
class Feriados
{
    const FIJOS = [
        '01-01' => 'Año Nuevo',
        '05-01' => 'Día del Trabajo',
        '06-07' => 'Batalla de Arica y Día de la Bandera',
        '06-29' => 'San Pedro y San Pablo',
        '07-23' => 'Día de la Fuerza Aérea del Perú',
        '07-28' => 'Fiestas Patrias',
        '07-29' => 'Fiestas Patrias',
        '08-06' => 'Batalla de Junín',
        '08-30' => 'Santa Rosa de Lima',
        '10-08' => 'Combate de Angamos',
        '11-01' => 'Día de Todos los Santos',
        '12-08' => 'Inmaculada Concepción',
        '12-09' => 'Batalla de Ayacucho',
        '12-25' => 'Navidad',
    ];

    /** [fecha Y-m-d => descripción], ordenado por fecha. */
    public static function nacionales(int $anio): array
    {
        $lista = [];
        foreach (self::FIJOS as $mesDia => $nombre) {
            $lista["$anio-$mesDia"] = $nombre;
        }
        $pascua = self::pascua($anio);
        $lista[$pascua->modify('-3 days')->format('Y-m-d')] = 'Jueves Santo';
        $lista[$pascua->modify('-2 days')->format('Y-m-d')] = 'Viernes Santo';
        ksort($lista);
        return $lista;
    }

    /** Domingo de Pascua (algoritmo gregoriano de Meeus/Jones/Butcher). */
    public static function pascua(int $anio): DateTimeImmutable
    {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;
        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $anio, $mes, $dia));
    }
}
