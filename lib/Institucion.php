<?php
/**
 * Marca de la institución configurada (el sistema es multi-institución).
 * Nombre, sigla, logo y color salen de la tabla empresa; si faltan o no son
 * válidos se usan valores por defecto para que la interfaz nunca quede rota.
 */
require_once __DIR__ . '/../model/model_conexion.php';

class Institucion
{
    const PRODUCTO = 'SISTRAMITE';
    const PROVEEDOR = 'JCM Digital & AI Consulting';
    const COLOR_DEFECTO = '#1F2358';
    const LOGO_DEFECTO = 'img/diresa.png';

    private static $datos;

    public static function datos(): array
    {
        if (self::$datos !== null) {
            return self::$datos;
        }

        $fila = [];
        try {
            $pdo = (new conexionBD())->conexionPDO();
            $fila = $pdo->query('SELECT * FROM empresa ORDER BY empresa_id LIMIT 1')->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            error_log('[INSTITUCION] ' . $e->getMessage());
        }

        $logo = (string) ($fila['emp_logo'] ?? '');
        if ($logo === '' || !is_file(dirname(__DIR__) . '/' . $logo)) {
            $logo = self::LOGO_DEFECTO;
        }

        $color = strtoupper((string) ($fila['emp_color'] ?? ''));
        if (!preg_match('/^#[0-9A-F]{6}$/', $color)) {
            $color = self::COLOR_DEFECTO;
        }

        $razon = trim((string) ($fila['emp_razon'] ?? '')) ?: 'Institución';
        $sigla = trim((string) ($fila['emp_sigla'] ?? '')) ?: $razon;

        return self::$datos = [
            'id'        => (int) ($fila['empresa_id'] ?? 0),
            'razon'     => $razon,
            'sigla'     => $sigla,
            'logo'      => $logo,
            'color'     => $color,
            'sobre'     => self::textoSobre($color),
            'email'     => (string) ($fila['emp_email'] ?? ''),
            'telefono'  => (string) ($fila['emp_telefono'] ?? ''),
            'direccion' => (string) ($fila['emp_direccion'] ?? ''),
            'codigo'    => (string) ($fila['emp_cod'] ?? ''),
            // Horario de atención (migración 015); lunes a viernes sin feriados
            'hora_inicio' => substr((string) ($fila['emp_hora_inicio'] ?? '08:00:00'), 0, 5),
            'hora_fin'    => substr((string) ($fila['emp_hora_fin'] ?? '16:30:00'), 0, 5),
        ];
    }

    /** Devuelve #FFFFFF o la tinta #16181A, lo que tenga más contraste sobre el color dado. */
    public static function textoSobre(string $hex): string
    {
        $canal = function (int $valor): float {
            $c = $valor / 255;
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
        $luminancia = 0.2126 * $canal($r) + 0.7152 * $canal($g) + 0.0722 * $canal($b);
        $contrasteBlanco = 1.05 / ($luminancia + 0.05);
        $contrasteTinta = ($luminancia + 0.05) / 0.0598; // luminancia de #16181A ≈ 0.0098
        return $contrasteBlanco >= $contrasteTinta ? '#FFFFFF' : '#16181A';
    }

    /** Variables CSS de marca para incluir en el <head> de cada página. */
    public static function estiloMarca(): string
    {
        $d = self::datos();
        return '<style>:root{--institucion:' . $d['color'] . ';--sobre-institucion:' . $d['sobre'] . ';}</style>';
    }
}
