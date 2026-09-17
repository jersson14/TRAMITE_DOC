<?php
/**
 * Ejecuta consultas SELECT escritas por la IA, sin confiar en ellas.
 *
 * El asistente del chat traduce la pregunta del usuario a SQL. Ese SQL llega
 * aquí como texto sospechoso y se acepta solo si cumple todo:
 *   - una sola sentencia SELECT, sin punto y coma ni comentarios;
 *   - ninguna palabra que escriba, borre o lea fuera de la lista blanca;
 *   - todas las tablas de FROM/JOIN están en EsquemaConsulta;
 *   - ninguna columna vetada (la contraseña);
 *   - un LIMIT propio o el que se le impone.
 *
 * Además se ejecuta en una transacción de solo lectura con tiempo máximo, así
 * una consulta mal escrita no bloquea ni cuelga el sistema.
 *
 * Para el personal de área, documento y movimiento se envuelven para que solo
 * vea los trámites por los que pasó su área: esa restricción se aplica aquí,
 * no en el texto que escribió la IA.
 */
require_once __DIR__ . '/../model/model_conexion.php';
require_once __DIR__ . '/EsquemaConsulta.php';

class ConsultaSegura
{
    const LIMITE_FILAS = 200;
    const SEGUNDOS_MAXIMO = 8;

    /** Palabras que no pueden aparecer en la consulta. */
    const PROHIBIDAS = [
        'insert', 'update', 'delete', 'drop', 'alter', 'create', 'truncate', 'rename',
        'replace', 'grant', 'revoke', 'call', 'do', 'set', 'load', 'load_file', 'handler',
        'lock', 'unlock', 'commit', 'rollback', 'start', 'begin', 'prepare', 'execute',
        'deallocate', 'into', 'outfile', 'dumpfile', 'infile', 'information_schema',
        'performance_schema', 'mysql', 'sys', 'sleep', 'benchmark', 'user', 'version',
        'database', 'schema', 'session_user', 'system_user', 'current_user', 'analyze',
        'optimize', 'flush', 'kill', 'show', 'explain', 'use', 'with',
    ];
    // DESC no está en la lista a propósito: es parte de ORDER BY ... DESC

    /**
     * Valida y ejecuta. Devuelve ['columnas', 'filas', 'sql', 'recortada'].
     * Lanza RuntimeException con un motivo entendible si la rechaza.
     */
    public static function ejecutar(string $sql, bool $esAdmin, ?int $areaId): array
    {
        $consulta = self::normalizar($sql);
        self::revisarForma($consulta);
        $tablas = EsquemaConsulta::tablasPermitidas($esAdmin);
        $consulta = self::revisarTablas($consulta, $tablas, $esAdmin);
        $consulta = self::imponerLimite($consulta);

        $parametros = [];
        if (!$esAdmin) {
            // El área se pasa como parámetro: nunca se interpola en el texto
            $consulta = self::acotarAlArea($consulta, $parametros, (int) $areaId);
        }

        $pdo = (new conexionBD())->conexionPDO();
        try {
            $pdo->exec('SET SESSION max_statement_time=' . self::SEGUNDOS_MAXIMO);
        } catch (Throwable $e) {
            error_log('[CONSULTA] sin max_statement_time: ' . $e->getMessage());
        }

        $pdo->exec('START TRANSACTION READ ONLY');
        try {
            $query = $pdo->prepare($consulta);
            $query->execute($parametros);
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $pdo->exec('ROLLBACK');
            error_log('[CONSULTA] ' . $e->getMessage() . ' | SQL: ' . $consulta);
            throw new RuntimeException('La consulta no se pudo ejecutar: ' . $e->getMessage());
        }
        $pdo->exec('ROLLBACK');

        return [
            'columnas'  => $filas ? array_keys($filas[0]) : [],
            'filas'     => $filas,
            'sql'       => $consulta,
            'recortada' => count($filas) >= self::LIMITE_FILAS,
        ];
    }

    /** Quita adornos del modelo (bloques de código, punto y coma final, espacios). */
    private static function normalizar(string $sql): string
    {
        $sql = trim($sql);
        // La IA suele devolver el SQL dentro de ```sql ... ```
        if (preg_match('/```(?:sql)?\s*(.+?)```/is', $sql, $m)) {
            $sql = trim($m[1]);
        }
        $sql = trim($sql);
        $sql = rtrim($sql, "; \t\n\r");
        return preg_replace('/\s+/', ' ', $sql);
    }

    /** Forma general: una sola sentencia SELECT y ninguna palabra prohibida. */
    private static function revisarForma(string $sql): void
    {
        if ($sql === '') {
            throw new RuntimeException('No se generó ninguna consulta.');
        }
        if (strpos($sql, ';') !== false) {
            throw new RuntimeException('Solo se admite una consulta a la vez.');
        }
        if (preg_match('/--|#|\/\*|\*\//', $sql)) {
            throw new RuntimeException('La consulta no puede llevar comentarios.');
        }
        if (strpos($sql, '?') !== false) {
            throw new RuntimeException('La consulta no puede llevar marcadores.');
        }
        if (!preg_match('/^select\s/i', $sql)) {
            throw new RuntimeException('Solo se admiten consultas de lectura (SELECT).');
        }

        // Las palabras se buscan fuera de los textos entre comillas: un asunto
        // puede decir "solicito la creación de..." sin que eso sea una orden SQL
        $sinTextos = preg_replace("/'(?:[^'\\\\]|\\\\.)*'|\"(?:[^\"\\\\]|\\\\.)*\"/", "''", $sql);
        foreach (self::PROHIBIDAS as $palabra) {
            if (preg_match('/\b' . preg_quote($palabra, '/') . '\b/i', $sinTextos)) {
                throw new RuntimeException('La consulta usa una instrucción no permitida (' . strtoupper($palabra) . ').');
            }
        }
        if (strpos($sinTextos, '@@') !== false) {
            throw new RuntimeException('La consulta no puede leer variables del servidor.');
        }
        foreach (EsquemaConsulta::COLUMNAS_VETADAS as $columna) {
            if (stripos($sinTextos, $columna) !== false) {
                throw new RuntimeException('Esa columna no se puede consultar.');
            }
        }
    }

    /**
     * Todas las tablas de FROM/JOIN deben estar permitidas. Para el personal de
     * área, documento y movimiento se renombran a las versiones acotadas.
     */
    private static function revisarTablas(string $sql, array $permitidas, bool $esAdmin): string
    {
        $encontradas = [];
        preg_match_all('/\b(?:from|join)\s+`?([A-Za-z0-9_$.]+)`?/i', $sql, $coincidencias);
        foreach ($coincidencias[1] as $tabla) {
            $encontradas[strtolower($tabla)] = true;
        }
        if (!$encontradas) {
            throw new RuntimeException('La consulta no indica de qué tabla leer.');
        }
        foreach (array_keys($encontradas) as $tabla) {
            if (!in_array($tabla, $permitidas, true)) {
                throw new RuntimeException('No se puede consultar la tabla "' . $tabla . '".');
            }
        }

        if ($esAdmin) {
            return $sql;
        }
        $acotadas = [
            'documento'       => '_doc_area',
            'movimiento'      => '_mov_area',
            'documento_anexo' => '_anx_area',
        ];
        return preg_replace_callback(
            '/\b(from|join)\s+`?(documento_anexo|documento|movimiento)`?\b/i',
            function (array $m) use ($acotadas): string {
                return $m[1] . ' ' . $acotadas[strtolower($m[2])];
            },
            $sql
        );
    }

    /** Impone el tope de filas, respetando un LIMIT menor si la IA lo puso. */
    private static function imponerLimite(string $sql): string
    {
        if (!preg_match('/\blimit\s+(\d+)\s*(?:,\s*(\d+))?\s*$/i', $sql, $m)) {
            return $sql . ' LIMIT ' . self::LIMITE_FILAS;
        }
        // Con "LIMIT desplazamiento, cantidad" el tope es el segundo número
        $cantidad = (int) (isset($m[2]) && $m[2] !== '' ? $m[2] : $m[1]);
        if ($cantidad > 0 && $cantidad <= self::LIMITE_FILAS) {
            return $sql;
        }
        return preg_replace('/\blimit\s+\d+\s*(?:,\s*\d+)?\s*$/i', 'LIMIT ' . self::LIMITE_FILAS, $sql);
    }

    /**
     * Envuelve documento y movimiento para el personal de área: solo los
     * trámites en los que su área intervino, igual que en las bandejas.
     */
    private static function acotarAlArea(string $sql, array &$parametros, int $areaId): string
    {
        $parametros = [
            'area_uno'    => $areaId,
            'area_dos'    => $areaId,
            'area_tres'   => $areaId,
            'area_cuatro' => $areaId,
            'area_cinco'  => $areaId,
        ];

        return 'WITH _doc_area AS ('
            . ' SELECT d.* FROM documento d'
            . ' WHERE d.area_destino = :area_uno OR d.area_origen = :area_dos OR d.area_id = :area_tres'
            . '   OR EXISTS (SELECT 1 FROM movimiento m WHERE m.documento_id = d.documento_id'
            . '               AND (m.areadestino_id = :area_cuatro OR m.area_origen_id = :area_cinco))'
            . '), _mov_area AS ('
            . ' SELECT m.* FROM movimiento m'
            . ' WHERE m.documento_id IN (SELECT documento_id FROM _doc_area)'
            . '), _anx_area AS ('
            . ' SELECT a.* FROM documento_anexo a'
            . ' WHERE a.documento_id IN (SELECT documento_id FROM _doc_area)'
            . ') ' . $sql;
    }
}
