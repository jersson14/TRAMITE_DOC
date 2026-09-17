<?php
/**
 * De dónde arranca el recorrido de un trámite.
 *
 * NO todo trámite empieza afuera. Desde la migración 021/022, doc_procedencia
 * distingue lo que produce la entidad de lo que le llega, y hay tres casos que
 * las pantallas deben nombrar distinto:
 *
 *   INTERNO   -> lo remite un área de la entidad. Se nombra ESA OFICINA
 *                (la del primer movimiento) y la persona que lo firma.
 *   ENTIDAD   -> viene de fuera, de una persona jurídica: otra institución o
 *                empresa. Se nombra la razón social y quién la representa.
 *   CIUDADANO -> viene de fuera, a nombre propio.
 *
 * Antes cada pantalla rotulaba "EXTERNO · CIUDADANO" a todos, que era falso para
 * los internos y para los que manda otra entidad. La regla vive aquí para que el
 * diagrama de flujo, el detalle de movimientos y el tablero digan lo mismo.
 */
class OrigenTramite
{
    /**
     * $documento necesita: doc_procedencia, area_procedencia, remitente,
     * doc_empresa, doc_ruc, doc_representacion y doc_fecharecepcion.
     * Devuelve ['tipo', 'rotulo', 'titulo', 'persona', 'area', 'ruc', 'enlace'].
     */
    public static function describir(array $documento): array
    {
        $procedencia = (string) ($documento['doc_procedencia'] ?? '');
        $empresa = trim((string) ($documento['doc_empresa'] ?? ''));
        $ruc = trim((string) ($documento['doc_ruc'] ?? ''));
        $representacion = trim((string) ($documento['doc_representacion'] ?? ''));
        $remitente = trim((string) ($documento['remitente'] ?? ''));
        $area = trim((string) ($documento['area_procedencia'] ?? ''));
        $esPortal = !empty($documento['doc_fecharecepcion']);

        // "A NOMBRE PROPIO" es un ciudadano; "PERSONA JURÍDICA" (con o sin tilde),
        // una entidad. Coincide con Modelo_Firma::Remitente_Es_Juridica().
        $esJuridica = $empresa !== '' || $ruc !== '' || stripos($representacion, 'JUR') !== false;

        if ($procedencia === 'INTERNO') {
            // La oficina va en el rótulo y la persona como título: si se pusiera el
            // área como título, el nodo repetiría el área de recepción de abajo.
            return [
                'tipo'    => 'INTERNO',
                'rotulo'  => 'Interno' . ($area !== '' ? ' · ' . $area : ''),
                'titulo'  => $remitente !== '' ? $remitente : ($area ?: 'Remitente no registrado'),
                'persona' => '',
                'area'    => $area,
                'ruc'     => '',
                'enlace'  => 'registra en',
            ];
        }

        if ($esJuridica) {
            return [
                'tipo'    => 'ENTIDAD',
                'rotulo'  => 'Externo · entidad',
                'titulo'  => $empresa !== '' ? $empresa : ($remitente ?: 'Entidad no registrada'),
                'persona' => $remitente,
                'area'    => '',
                'ruc'     => $ruc,
                'enlace'  => $esPortal ? 'presenta por el portal' : 'presenta en mesa de partes',
            ];
        }

        return [
            'tipo'    => 'CIUDADANO',
            'rotulo'  => 'Externo · ciudadano',
            'titulo'  => $remitente ?: 'Remitente no registrado',
            'persona' => '',
            'area'    => '',
            'ruc'     => '',
            'enlace'  => $esPortal ? 'presenta por el portal' : 'presenta en mesa de partes',
        ];
    }

    /**
     * Los campos del documento que describir() necesita, listos para un SELECT.
     * Se pide doc_procedencia solo si la columna existe, para no romper una
     * instalación que todavía no aplicó la migración 021.
     */
    public static function consultar(PDO $pdo, string $documentoId): array
    {
        $hayProcedencia = (bool) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documento' AND COLUMN_NAME = 'doc_procedencia'"
        )->fetchColumn();

        /*
         * El área de donde viene sale del PRIMER movimiento, no de
         * documento.area_origen: SP_REGISTRAR_TRAMITE_DERIVAR sobrescribe esa
         * columna en cada derivación, así que guarda de dónde viene el ÚLTIMO
         * envío, no de dónde nació el trámite. Usarla mostraba, por ejemplo,
         * "Interno · MESA DE PARTES" en un documento que había remitido
         * CONTABILIDAD. documento.area_origen queda solo como respaldo para
         * trámites sin movimientos.
         */
        $consulta = $pdo->prepare(
            "SELECT d.doc_fecharecepcion, d.doc_empresa, d.doc_ruc, d.doc_representacion,
                    " . ($hayProcedencia ? 'd.doc_procedencia,' : "'' AS doc_procedencia,") . "
                    CONCAT_WS(' ', d.doc_nombreremitente, d.doc_apepatremitente, d.doc_apematremitente) AS remitente,
                    COALESCE(
                        (SELECT ao.area_nombre
                           FROM movimiento m
                           LEFT JOIN area ao ON ao.area_cod = m.area_origen_id
                          WHERE m.documento_id = d.documento_id
                          ORDER BY m.movimiento_id
                          LIMIT 1),
                        procede.area_nombre
                    ) AS area_procedencia
               FROM documento d
               LEFT JOIN area procede ON procede.area_cod = d.area_origen
              WHERE d.documento_id = ?
              LIMIT 1"
        );
        $consulta->execute([$documentoId]);
        return $consulta->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
