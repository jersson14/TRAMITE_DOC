<?php
/**
 * Exportación de reportes a PDF, Excel y CSV con un formato común.
 *
 * Antes cada tabla se exportaba desde el navegador con los botones de
 * DataTables: el archivo salía sin el nombre de la institución, sin los filtros
 * aplicados, con la columna de botones «Ver» y solo con la página visible.
 * Ahora el archivo se arma en el servidor a partir de los datos completos.
 *
 * Uso:
 *   Exportador::entregar($formato, [
 *       'titulo'   => 'Reporte de trámites por fecha y área',
 *       'archivo'  => 'tramites_por_area',
 *       'filtros'  => ['Desde' => '01/01/2026', 'Área' => 'CONTABILIDAD'],
 *       'columnas' => [['clave' => 'documento_id', 'titulo' => 'Código', 'ancho' => 18]],
 *       'filas'    => [['documento_id' => 'D0000001', ...]],
 *       'resumen'  => ['Total de trámites' => 32],   // opcional
 *       'bloques'  => [['titulo' => '...', 'columnas' => [...], 'filas' => [...]]], // opcional
 *   ]);
 */
require_once __DIR__ . '/Institucion.php';
require_once __DIR__ . '/Seguridad.php';
require_once __DIR__ . '/../vendor/autoload.php';            // openspout (Excel)
require_once __DIR__ . '/../view/MPDF/vendor/autoload.php';  // mPDF (PDF)

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options as OpcionesXlsx;
use OpenSpout\Writer\XLSX\Writer as EscritorXlsx;

class Exportador
{
    const AZUL = '1E3A5F';
    const GRIS = 'F2F5F9';

    /** Manda el archivo al navegador en el formato pedido. */
    public static function entregar(string $formato, array $reporte): void
    {
        $formato = strtolower($formato);
        if ($formato === 'pdf') {
            self::pdf($reporte);
        } elseif ($formato === 'excel' || $formato === 'xlsx') {
            self::excel($reporte);
        } elseif ($formato === 'csv') {
            self::csv($reporte);
        } else {
            Seguridad::responderError(422, 'Formato de exportación no válido.');
        }
    }

    /** Quien exporta y cuándo: va al pie de cada archivo. */
    private static function pie(): string
    {
        $usuario = $_SESSION['S_NOMBRE'] ?? ($_SESSION['S_USU'] ?? 'usuario del sistema');
        return 'Emitido el ' . date('d/m/Y H:i') . ' por ' . $usuario;
    }

    private static function nombreArchivo(array $reporte, string $extension): string
    {
        $base = $reporte['archivo'] ?? 'reporte';
        return $base . '_' . date('Ymd_Hi') . '.' . $extension;
    }

    /** Todos los bloques del reporte: el principal y los adicionales. */
    private static function bloques(array $reporte): array
    {
        $bloques = [];
        if (!empty($reporte['columnas'])) {
            $bloques[] = [
                'titulo'   => $reporte['subtitulo'] ?? '',
                'columnas' => $reporte['columnas'],
                'filas'    => $reporte['filas'] ?? [],
            ];
        }
        foreach ($reporte['bloques'] ?? [] as $bloque) {
            $bloques[] = $bloque;
        }
        return $bloques;
    }

    private static function valor(array $fila, array $columna): string
    {
        // Con ?? '' un valor nulo se volvía texto vacío y el formato («—», «sin plazo») no se aplicaba
        $valor = array_key_exists($columna['clave'], $fila) ? $fila[$columna['clave']] : null;
        if (isset($columna['formato']) && is_callable($columna['formato'])) {
            $valor = $columna['formato']($valor, $fila);
        }
        return (string) ($valor === null ? '' : $valor);
    }

    // ------------------------------------------------------------------ PDF
    private static function pdf(array $reporte): void
    {
        $i = Institucion::datos();
        $logo = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $i['logo']);
        $e = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

        // mPDF no aplica márgenes a los elementos en línea: los separadores van en el texto
        $filtros = [];
        foreach ($reporte['filtros'] ?? [] as $rotulo => $valor) {
            $filtros[] = '<b>' . $e($rotulo) . ':</b> ' . $e($valor);
        }
        $filtros = implode(' &nbsp;·&nbsp; ', $filtros);

        $resumen = '';
        foreach ($reporte['resumen'] ?? [] as $rotulo => $valor) {
            $resumen .= '<td class="tarjeta"><span>' . $e($rotulo) . '</span><br><b>' . $e($valor) . '</b></td>';
        }
        if ($resumen !== '') {
            $resumen = '<table class="tarjetas"><tr>' . $resumen . '</tr></table>';
        }

        $cuerpo = '';
        foreach (self::bloques($reporte) as $bloque) {
            $cuerpo .= $bloque['titulo'] !== '' ? '<h2>' . $e($bloque['titulo']) . '</h2>' : '';
            $cuerpo .= '<table class="datos"><thead><tr>';
            foreach ($bloque['columnas'] as $columna) {
                $ancho = isset($columna['ancho']) ? ' width="' . (int) $columna['ancho'] . '%"' : '';
                $cuerpo .= '<th' . $ancho . '>' . $e($columna['titulo']) . '</th>';
            }
            $cuerpo .= '</tr></thead><tbody>';
            if (!$bloque['filas']) {
                $cuerpo .= '<tr><td class="vacio" colspan="' . count($bloque['columnas']) . '">Sin resultados para los filtros elegidos.</td></tr>';
            }
            foreach ($bloque['filas'] as $n => $fila) {
                $cuerpo .= '<tr' . ($n % 2 ? ' class="par"' : '') . '>';
                foreach ($bloque['columnas'] as $columna) {
                    $clase = isset($columna['alineacion']) ? ' class="al-' . $e($columna['alineacion']) . '"' : '';
                    $cuerpo .= '<td' . $clase . '>' . $e(self::valor($fila, $columna)) . '</td>';
                }
                $cuerpo .= '</tr>';
            }
            $cuerpo .= '</tbody></table>';
        }

        $html = '
        <style>
            body { font-family: dejavusans, sans-serif; font-size: 8pt; color: #2D3748; }
            h1 { font-size: 13pt; color: #1E3A5F; margin: 0; }
            h2 { font-size: 9.5pt; color: #1E3A5F; margin: 5mm 0 1.5mm; }
            .institucion { font-size: 10pt; font-weight: bold; color: #1E3A5F; }
            .canal { font-size: 7.5pt; color: #718096; }
            .filtros { margin-top: 1.5mm; font-size: 7.5pt; color: #4A5568; }
            .filtro { margin-right: 4mm; }
            .tarjetas { width: 100%; margin-top: 3mm; border-collapse: separate; border-spacing: 2mm 0; }
            .tarjeta { background: #F2F5F9; border: 0.2mm solid #CBD5E0; border-radius: 2mm; padding: 2mm; text-align: center; }
            .tarjeta span { font-size: 6.5pt; color: #718096; }
            .tarjeta b { font-size: 11pt; color: #1E3A5F; }
            .datos { width: 100%; border-collapse: collapse; }
            .datos th { background: #1E3A5F; color: #FFFFFF; font-size: 7pt; text-align: left; padding: 1.6mm 1.4mm; }
            .datos td { border-bottom: 0.2mm solid #E2E8F0; padding: 1.4mm; font-size: 7.5pt; }
            .datos tr.par td { background: #F7FAFC; }
            .al-centro { text-align: center; }
            .al-derecha { text-align: right; }
            .vacio { text-align: center; color: #718096; padding: 5mm; }
        </style>
        <table width="100%"><tr>
            <td width="24%"><img src="' . $e($logo) . '" style="max-height: 14mm; max-width: 52mm;"></td>
            <td width="76%">
                <div class="institucion">' . $e($i['razon']) . '</div>
                <h1>' . $e($reporte['titulo']) . '</h1>
                <div class="canal">Sistema de Trámite Documentario</div>
            </td>
        </tr></table>
        <div class="filtros">' . $filtros . '</div>
        ' . $resumen . $cuerpo;

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => ($reporte['orientacion'] ?? 'horizontal') === 'vertical' ? 'A4' : 'A4-L',
            'margin_top' => 12, 'margin_bottom' => 16, 'margin_left' => 10, 'margin_right' => 10,
        ]);
        $mpdf->SetTitle($reporte['titulo']);
        $mpdf->SetAuthor($i['razon']);
        $mpdf->SetHTMLFooter(
            '<table width="100%" style="font-family: dejavusans; font-size: 7pt; color: #718096; border-top: 0.2mm solid #CBD5E0; padding-top: 1mm;">'
            . '<tr><td>' . $e(self::pie()) . '</td><td style="text-align: right;">Página {PAGENO} de {nbpg}</td></tr></table>'
        );
        $mpdf->WriteHTML($html);
        $mpdf->Output(self::nombreArchivo($reporte, 'pdf'), 'D');
        exit;
    }

    // ---------------------------------------------------------------- EXCEL
    private static function excel(array $reporte): void
    {
        $i = Institucion::datos();
        $opciones = new OpcionesXlsx();

        $bloques = self::bloques($reporte);
        $anchos = $bloques ? $bloques[0]['columnas'] : [];
        foreach ($anchos as $n => $columna) {
            $opciones->setColumnWidth(max(12, (int) (($columna['ancho'] ?? 12) * 1.2)), $n + 1);
        }

        $escritor = new EscritorXlsx($opciones);
        $escritor->openToBrowser(self::nombreArchivo($reporte, 'xlsx'));

        $titulo = (new Style())->setFontBold()->setFontSize(14)->setFontColor(self::AZUL);
        $normal = (new Style())->setFontSize(10);
        $suave = (new Style())->setFontSize(9)->setFontColor('718096');
        $encabezado = (new Style())->setFontBold()->setFontSize(10)->setFontColor(Color::WHITE)
            ->setBackgroundColor(self::AZUL)->setCellAlignment(CellAlignment::LEFT)
            ->setBorder(new Border(new BorderPart(Border::BOTTOM, self::AZUL, Border::WIDTH_THIN)));
        $celda = (new Style())->setFontSize(9)
            ->setBorder(new Border(new BorderPart(Border::BOTTOM, 'E2E8F0', Border::WIDTH_THIN)));
        $subtitulo = (new Style())->setFontBold()->setFontSize(11)->setFontColor(self::AZUL);

        $escritor->addRow(Row::fromValues([$i['razon']], $titulo));
        $escritor->addRow(Row::fromValues([$reporte['titulo']], $subtitulo));
        foreach ($reporte['filtros'] ?? [] as $rotulo => $valor) {
            $escritor->addRow(Row::fromValues([$rotulo . ': ' . $valor], $suave));
        }
        $escritor->addRow(Row::fromValues([self::pie()], $suave));
        $escritor->addRow(Row::fromValues([]));

        foreach ($reporte['resumen'] ?? [] as $rotulo => $valor) {
            $escritor->addRow(Row::fromValues([$rotulo, $valor], $normal));
        }
        if (!empty($reporte['resumen'])) {
            $escritor->addRow(Row::fromValues([]));
        }

        foreach ($bloques as $bloque) {
            if ($bloque['titulo'] !== '') {
                $escritor->addRow(Row::fromValues([$bloque['titulo']], $subtitulo));
            }
            $escritor->addRow(Row::fromValues(array_column($bloque['columnas'], 'titulo'), $encabezado));
            foreach ($bloque['filas'] as $fila) {
                $valores = [];
                foreach ($bloque['columnas'] as $columna) {
                    $texto = self::valor($fila, $columna);
                    // Los números se guardan como números para poder sumarlos en Excel
                    $valores[] = ($columna['numero'] ?? false) && is_numeric($texto) ? $texto + 0 : $texto;
                }
                $escritor->addRow(Row::fromValues($valores, $celda));
            }
            $escritor->addRow(Row::fromValues([]));
        }

        $escritor->close();
        exit;
    }

    // ------------------------------------------------------------------ CSV
    private static function csv(array $reporte): void
    {
        $i = Institucion::datos();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . self::nombreArchivo($reporte, 'csv') . '"');

        $salida = fopen('php://output', 'w');
        // BOM y punto y coma: así Excel en español abre el archivo con las columnas separadas
        fwrite($salida, "\xEF\xBB\xBF");
        $escribir = function (array $campos) use ($salida) {
            fputcsv($salida, $campos, ';');
        };

        $escribir([$i['razon']]);
        $escribir([$reporte['titulo']]);
        foreach ($reporte['filtros'] ?? [] as $rotulo => $valor) {
            $escribir([$rotulo, $valor]);
        }
        $escribir([self::pie()]);
        $escribir([]);
        foreach ($reporte['resumen'] ?? [] as $rotulo => $valor) {
            $escribir([$rotulo, $valor]);
        }
        if (!empty($reporte['resumen'])) {
            $escribir([]);
        }

        foreach (self::bloques($reporte) as $bloque) {
            if ($bloque['titulo'] !== '') {
                $escribir([$bloque['titulo']]);
            }
            $escribir(array_column($bloque['columnas'], 'titulo'));
            foreach ($bloque['filas'] as $fila) {
                $valores = [];
                foreach ($bloque['columnas'] as $columna) {
                    $valores[] = self::valor($fila, $columna);
                }
                $escribir($valores);
            }
            $escribir([]);
        }

        fclose($salida);
        exit;
    }
}
