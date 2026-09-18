<?php
require_once __DIR__ . '/Institucion.php';

/**
 * Numeración automática de documentos internos por área (migración 026).
 *
 * Cada área lleva una secuencia propia por tipo de documento y por año:
 *
 *     001-2026-DIRESA-APURÍMAC/CONT     (1er oficio de Contabilidad en 2026)
 *
 * Se reutiliza la tabla correlativo y SP_SIGUIENTE_CORRELATIVO, que ya numeraban
 * el expediente de forma atómica. La clave es "A<área>-T<tipo>", que cabe en
 * corr_tipo VARCHAR(10).
 *
 * Hay dos operaciones distintas a propósito:
 *   - siguiente(): MIRA cuál sería el próximo, sin gastarlo. Es lo que se muestra
 *     en el formulario mientras el usuario lo llena.
 *   - consumir():  lo GASTA, de forma atómica. Solo al guardar, y solo si el usuario
 *     dejó el número automático. Si dos personas registran a la vez, cada una
 *     recibe un número distinto aunque ambas hayan visto el mismo en pantalla.
 */
class Correlativo
{
    /** Clave de la secuencia en la tabla correlativo. */
    public static function clave(int $area, int $tipo): string
    {
        return 'A' . $area . '-T' . $tipo;
    }

    /**
     * Sigla del área. Si el administrador no cargó una, se deduce del nombre:
     * con varias palabras, sus iniciales ("RECURSOS HUMANOS" -> "RH"); con una
     * sola, sus primeras cuatro letras ("CONTABILIDAD" -> "CONT").
     */
    public static function siglaArea(string $nombre, ?string $guardada = null): string
    {
        $guardada = trim((string) $guardada);
        if ($guardada !== '') {
            return mb_strtoupper($guardada);
        }
        $vacias = ['DE', 'DEL', 'LA', 'LAS', 'LOS', 'EL', 'Y', 'E', 'EN', 'PARA', 'POR'];
        $palabras = array_values(array_filter(
            preg_split('/\s+/u', mb_strtoupper(trim($nombre))),
            function ($p) use ($vacias) { return $p !== '' && !in_array($p, $vacias, true); }
        ));
        if (!$palabras) {
            return 'AREA';
        }
        if (count($palabras) === 1) {
            return mb_substr($palabras[0], 0, 4);
        }
        $sigla = '';
        foreach ($palabras as $p) {
            $sigla .= mb_substr($p, 0, 1);
        }
        return $sigla;
    }

    /** Sigla de la institución, lista para el número: "DIRESA Apurímac" -> "DIRESA-APURÍMAC". */
    public static function siglaInstitucion(): string
    {
        $sigla = trim((string) (Institucion::datos()['sigla'] ?? ''));
        if ($sigla === '') {
            return '';
        }
        return preg_replace('/\s+/u', '-', mb_strtoupper($sigla));
    }

    /** "001-2026-DIRESA-APURÍMAC/CONT". Sin sigla de institución: "001-2026/CONT". */
    public static function formatear(int $numero, int $anio, string $siglaArea): string
    {
        $institucion = self::siglaInstitucion();
        return sprintf('%03d-%d', $numero, $anio)
            . ($institucion !== '' ? '-' . $institucion : '')
            . '/' . $siglaArea;
    }

    /** Cuál sería el próximo número, SIN gastarlo. */
    public static function siguiente(PDO $pdo, int $area, int $tipo, int $anio): int
    {
        $q = $pdo->prepare("SELECT corr_ultimo FROM correlativo WHERE corr_anio = ? AND corr_tipo = ?");
        $q->execute([$anio, self::clave($area, $tipo)]);
        return ((int) $q->fetchColumn()) + 1;
    }

    /** Gasta el próximo número, de forma atómica. */
    public static function consumir(PDO $pdo, int $area, int $tipo, int $anio): int
    {
        $q = $pdo->prepare("CALL SP_SIGUIENTE_CORRELATIVO(?, ?, @num)");
        $q->execute([$anio, self::clave($area, $tipo)]);
        $q->closeCursor();
        return (int) $pdo->query("SELECT @num")->fetchColumn();
    }

    /** Nombre y sigla (guardada o deducida) de un área. */
    public static function datosArea(PDO $pdo, int $area): ?array
    {
        $q = $pdo->prepare("SELECT area_nombre, area_sigla FROM area WHERE area_cod = ?");
        $q->execute([$area]);
        $fila = $q->fetch(PDO::FETCH_ASSOC);
        if (!$fila) {
            return null;
        }
        return [
            'nombre' => $fila['area_nombre'],
            'sigla'  => self::siglaArea((string) $fila['area_nombre'], $fila['area_sigla']),
        ];
    }
}
