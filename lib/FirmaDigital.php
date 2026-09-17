<?php
require_once __DIR__ . '/../vendor/autoload.php';
// mPDF (con FPDI y generador de QR) ya viene con los reportes PDF
require_once __DIR__ . '/../view/MPDF/vendor/autoload.php';

use ddn\sapp\PDFDoc;

date_default_timezone_set('America/Lima');

/**
 * Firma digital de PDF con certificado en archivo (.pfx / .p12).
 *
 * - La firma se agrega por actualización incremental (ddn/sapp): el contenido
 *   original queda intacto y la firma CMS (SHA-256) cubre todo el archivo.
 *   Adobe Reader y cualquier validador PAdES/PKCS#7 la reconocen.
 * - El certificado y su contraseña solo existen en memoria durante la firma.
 * - Se probó con PDF reales 1.4 y 1.7 (con compresión de referencias), con
 *   varias firmas en el mismo archivo y detectando alteraciones de un byte.
 */
class FirmaDigital
{
    const FUENTE = __DIR__ . '/../view/MPDF/vendor/mpdf/mpdf/ttfonts/DejaVuSans.ttf';
    const FUENTE_NEGRITA = __DIR__ . '/../view/MPDF/vendor/mpdf/mpdf/ttfonts/DejaVuSans-Bold.ttf';

    /** Lee un .pfx/.p12. Lanza RuntimeException con un mensaje para el usuario. */
    public static function leerPfx(string $contenido, string $clave): array
    {
        if ($contenido === '') {
            throw new RuntimeException('Adjunte su certificado digital (.pfx o .p12).');
        }
        $certs = [];
        if (!openssl_pkcs12_read($contenido, $certs, $clave)) {
            while (openssl_error_string()) {
            }
            throw new RuntimeException('No se pudo abrir el certificado: la contraseña es incorrecta o el archivo no es un certificado .pfx/.p12 válido.');
        }
        if (empty($certs['cert']) || empty($certs['pkey'])) {
            throw new RuntimeException('El archivo no contiene un certificado con su clave privada.');
        }
        if (!openssl_x509_check_private_key($certs['cert'], $certs['pkey'])) {
            throw new RuntimeException('La clave privada no corresponde al certificado.');
        }
        return $certs;
    }

    /** Datos legibles del certificado. */
    public static function datosCertificado(string $certPem): array
    {
        $x = openssl_x509_parse($certPem);
        if (!$x) {
            throw new RuntimeException('No se pudo leer el certificado.');
        }
        $sujeto = $x['subject'] ?? [];
        $emisor = $x['issuer'] ?? [];
        $nombre = self::campo($sujeto, 'CN') ?: trim(self::campo($sujeto, 'GN') . ' ' . self::campo($sujeto, 'SN'));

        return [
            'nombre'      => $nombre !== '' ? $nombre : 'Titular sin nombre',
            'dni'         => self::dniDesdeSujeto($sujeto),
            'emisor'      => self::campo($emisor, 'CN') ?: self::campo($emisor, 'O'),
            'serie'       => $x['serialNumberHex'] ?? ($x['serialNumber'] ?? ''),
            'desde'       => date('Y-m-d H:i:s', $x['validFrom_time_t']),
            'hasta'       => date('Y-m-d H:i:s', $x['validTo_time_t']),
            'desde_ts'    => $x['validFrom_time_t'],
            'hasta_ts'    => $x['validTo_time_t'],
            'huella'      => openssl_x509_fingerprint($certPem, 'sha256') ?: '',
            'autofirmado' => ($x['subject'] ?? null) == ($x['issuer'] ?? null),
            'uso_clave'   => $x['extensions']['keyUsage'] ?? '',
        ];
    }

    /** El certificado debe estar vigente y servir para firmar. */
    public static function validarCertificado(array $datos): void
    {
        $ahora = time();
        if ($ahora < $datos['desde_ts']) {
            throw new RuntimeException('El certificado todavía no está vigente (válido desde ' . date('d/m/Y', $datos['desde_ts']) . ').');
        }
        if ($ahora > $datos['hasta_ts']) {
            throw new RuntimeException('El certificado venció el ' . date('d/m/Y', $datos['hasta_ts']) . '. Renueve su certificado digital.');
        }
        $uso = strtolower($datos['uso_clave']);
        if ($uso !== '' && strpos($uso, 'digital signature') === false && strpos($uso, 'non repudiation') === false) {
            throw new RuntimeException('Este certificado no está autorizado para firmar documentos.');
        }
    }

    /**
     * DNI del titular. Los certificados peruanos lo llevan en serialNumber con
     * formatos como "PNOPE-12345678", "DNI:12345678" o solo "12345678".
     */
    public static function dniDesdeSujeto(array $sujeto): ?string
    {
        $serie = (string) self::campo($sujeto, 'serialNumber');
        if (preg_match('/(?:DNI|PNOPE|IDCPE)?[^0-9]*(\d{8})(?!\d)/i', $serie, $m)) {
            return $m[1];
        }
        return null;
    }

    /** Código corto de verificación que va en el sello. */
    public static function codigo(): string
    {
        return strtoupper(bin2hex(random_bytes(5)));
    }

    /** Sello visible (PNG): texto del firmante, fecha, código y QR de verificación. */
    public static function sello(string $nombre, ?string $dni, string $motivo, string $fecha, string $codigo, string $url): string
    {
        $ancho = 1000;
        $alto = 300;
        $img = imagecreatetruecolor($ancho, $alto);
        $blanco = imagecolorallocate($img, 255, 255, 255);
        $azul = imagecolorallocate($img, 30, 58, 95);
        $gris = imagecolorallocate($img, 74, 85, 104);
        imagefill($img, 0, 0, $blanco);
        imagesetthickness($img, 4);
        imagerectangle($img, 2, 2, $ancho - 3, $alto - 3, $azul);

        // QR a la derecha
        $lado = $alto - 40;
        $png = (new \Mpdf\QrCode\Output\Png())->output(new \Mpdf\QrCode\QrCode($url, 'M'), $lado, [255, 255, 255], [30, 58, 95]);
        $qrImg = $png ? @imagecreatefromstring($png) : false;
        if ($qrImg) {
            imagecopy($img, $qrImg, $ancho - $lado - 20, 20, 0, 0, imagesx($qrImg), imagesy($qrImg));
        }

        $x = 28;
        imagettftext($img, 22, 0, $x, 52, $gris, self::FUENTE, 'Firmado digitalmente por:');
        imagettftext($img, 30, 0, $x, 98, $azul, self::FUENTE_NEGRITA, mb_strimwidth($nombre, 0, 34, '…'));
        $linea = ($dni ? 'DNI ' . $dni . ' · ' : '') . $fecha;
        imagettftext($img, 22, 0, $x, 146, $gris, self::FUENTE, $linea);
        if ($motivo !== '') {
            imagettftext($img, 22, 0, $x, 188, $gris, self::FUENTE, 'Motivo: ' . mb_strimwidth($motivo, 0, 40, '…'));
        }
        imagettftext($img, 22, 0, $x, 246, $azul, self::FUENTE_NEGRITA, 'Código de verificación: ' . $codigo);

        // JPEG: más liviano que PNG dentro del PDF y sin capa alfa
        ob_start();
        imagejpeg($img, null, 95);
        imagedestroy($img);
        return ob_get_clean();
    }

    /**
     * Firma un PDF. $op: nombre, motivo, lugar, sello (imagen).
     * El sello va en la última página, abajo a la izquierda; si ya hay firmas,
     * se corre a la derecha o sube de fila para no taparlas.
     * Devuelve ['pdf' => bytes firmados, 'orden' => número de esta firma,
     *           'normalizado' => si hubo que reconstruir el PDF].
     */
    public static function firmar(string $pdf, array $certs, array $op): array
    {
        try {
            return self::firmarConSapp($pdf, $certs, $op) + ['normalizado' => false];
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException && $e->getCode() === self::ERROR_CERTIFICADO) {
                throw $e;
            }
            // Algunos PDF antiguos tienen una estructura que SAPP no acepta. Si todavía
            // no tienen firmas, se reconstruyen página por página (misma apariencia)
            // y se firma esa versión; con firmas previas no se puede: las invalidaría.
            if (preg_match('/\/ByteRange\s*\[/', $pdf)) {
                error_log('[FIRMA] cofirma: ' . $e->getMessage());
                throw new RuntimeException('No se pudo agregar otra firma a este PDF: su estructura no es compatible.');
            }
            error_log('[FIRMA] se reconstruye el PDF: ' . $e->getMessage());
        }

        try {
            return self::firmarConSapp(self::reconstruir($pdf), $certs, $op) + ['normalizado' => true];
        } catch (UnexpectedValueException $e) {
            error_log('[FIRMA] reconstruido: ' . $e->getMessage());
            throw new RuntimeException('No se pudo firmar este PDF: el archivo está dañado, protegido con contraseña o tiene un formato no compatible.');
        } catch (RuntimeException $e) {
            throw $e; // mensajes pensados para el usuario (certificado, lectura)
        } catch (Throwable $e) {
            error_log('[FIRMA] ' . $e->getMessage());
            throw new RuntimeException('No se pudo firmar este PDF: el archivo está dañado, protegido con contraseña o tiene un formato no compatible.');
        }
    }

    const ERROR_CERTIFICADO = 10;

    private static function firmarConSapp(string $pdf, array $certs, array $op): array
    {
        $doc = self::abrir($pdf);
        if ($doc === null) {
            throw new UnexpectedValueException('SAPP no pudo leer el PDF');
        }

        $previas = (int) $doc->get_signature_count();
        $pagina = max(0, $doc->get_page_count() - 1);
        [$pAncho, $pAlto] = self::tamanoPagina($doc, $pagina);

        $w = 250;
        $h = 75;
        $margen = 30;
        $porFila = max(1, (int) floor(($pAncho - $margen) / ($w + 10)));
        $col = $previas % $porFila;
        $fila = intdiv($previas, $porFila);
        $x0 = $margen + $col * ($w + 10);
        // SAPP mide la altura desde el borde superior de la página
        $y0 = $pAlto - $margen - $h - $fila * ($h + 10);

        $doc->set_signature_appearance($pagina, [$x0, $y0, $x0 + $w, $y0 + $h], '@' . $op['sello']);
        $doc->set_metadata_props($op['nombre'], $op['motivo'] ?: 'Firma digital', $op['lugar'] ?? '', '');
        if (!$doc->set_signature_certificate($certs)) {
            throw new RuntimeException('El certificado no se pudo usar para firmar.', self::ERROR_CERTIFICADO);
        }

        ob_start();
        try {
            $firmado = $doc->to_pdf_file_s();
        } finally {
            ob_end_clean();
        }
        if ($firmado === false || $firmado === '') {
            throw new UnexpectedValueException('SAPP no generó el PDF firmado');
        }

        // Comprobación antes de entregarlo: la firma nueva debe verificar
        $verificacion = self::verificar($firmado);
        $ultima = end($verificacion);
        if (!$ultima || !$ultima['valida']) {
            throw new UnexpectedValueException('La firma generada no superó la verificación');
        }

        return ['pdf' => $firmado, 'orden' => count($verificacion)];
    }

    /**
     * Verifica criptográficamente todas las firmas de un PDF.
     * Devuelve una lista con, por firma: valida, firmante, dni, emisor, cubre_todo.
     */
    public static function verificar(string $pdf): array
    {
        preg_match_all('/\/ByteRange\s*\[\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*\]/', $pdf, $rangos, PREG_SET_ORDER);
        $resultado = [];
        foreach ($rangos as $r) {
            [$a, $b, $c, $d] = array_map('intval', array_slice($r, 1));
            $item = ['valida' => false, 'firmante' => null, 'dni' => null, 'emisor' => null, 'cubre_todo' => false];
            if ($a !== 0 || $c <= $b || $c + $d > strlen($pdf)) {
                $resultado[] = $item;
                continue;
            }
            $hex = substr($pdf, $a + $b + 1, $c - $b - 2);
            $der = self::derExacto(@hex2bin($hex) ?: '');
            if ($der === '') {
                $resultado[] = $item;
                continue;
            }

            $tmpDatos = tempnam(sys_get_temp_dir(), 'fdd');
            $tmpFirma = tempnam(sys_get_temp_dir(), 'fdf');
            $tmpCert = tempnam(sys_get_temp_dir(), 'fdc');
            file_put_contents($tmpDatos, substr($pdf, $a, $b) . substr($pdf, $c, $d));
            file_put_contents($tmpFirma, $der);
            while (openssl_error_string()) {
            }
            // En firmas separadas, openssl_cms_verify recibe primero los DATOS y la firma en $sigfile
            $ok = openssl_cms_verify($tmpDatos, OPENSSL_CMS_NOVERIFY | OPENSSL_CMS_BINARY | OPENSSL_CMS_DETACHED,
                $tmpCert, [], null, null, null, $tmpFirma, OPENSSL_ENCODING_DER);
            while (openssl_error_string()) {
            }
            $cert = filesize($tmpCert) ? file_get_contents($tmpCert) : '';
            @unlink($tmpDatos);
            @unlink($tmpFirma);
            @unlink($tmpCert);

            $item['valida'] = $ok === true;
            // La última firma debe cubrir hasta el final del archivo; las anteriores, su revisión
            $item['cubre_todo'] = ($c + $d) === strlen($pdf);
            if ($cert !== '') {
                try {
                    $datos = self::datosCertificado($cert);
                    $item['firmante'] = $datos['nombre'];
                    $item['dni'] = $datos['dni'];
                    $item['emisor'] = $datos['emisor'];
                } catch (Throwable $e) {
                    // certificado ilegible: la firma se informa sin datos del titular
                }
            }
            $resultado[] = $item;
        }
        return $resultado;
    }

    /**
     * Resumen legible de las firmas que ya trae un PDF, para mostrarlo al
     * registrar un documento que llega de afuera (no se firma: se comprueba).
     *
     * Devuelve ['total', 'validas', 'firmantes' => [...], 'todas_validas'].
     * Un PDF sin firmas devuelve total 0, que no es un error: muchos documentos
     * llegan en papel escaneado.
     */
    public static function resumenFirmas(string $pdf): array
    {
        $firmas = self::verificar($pdf);
        $validas = 0;
        $firmantes = [];
        foreach ($firmas as $f) {
            if ($f['valida']) {
                $validas++;
            }
            $firmantes[] = [
                'nombre' => $f['firmante'] ?: 'Firmante no identificado',
                'dni'    => $f['dni'],
                'emisor' => $f['emisor'],
                'valida' => (bool) $f['valida'],
            ];
        }
        return [
            'total'         => count($firmas),
            'validas'       => $validas,
            'firmantes'     => $firmantes,
            'todas_validas' => count($firmas) > 0 && $validas === count($firmas),
        ];
    }

    /** Una línea para la bitácora: "2 firmas válidas (JUAN PEREZ, ANA DIAZ)". */
    public static function resumenTexto(array $r): string
    {
        if ($r['total'] === 0) {
            return 'sin firma digital';
        }
        $nombres = array_slice(array_column($r['firmantes'], 'nombre'), 0, 3);
        $texto = $r['total'] . ($r['total'] === 1 ? ' firma' : ' firmas');
        $texto .= $r['todas_validas'] ? ' válida' . ($r['total'] === 1 ? '' : 's') : ' (alguna no verifica)';
        return $texto . ' (' . implode(', ', $nombres) . ')';
    }

    /**
     * Firma un PDF con el certificado que el usuario acaba de subir.
     *
     * Reúne los pasos que comparten "firmar un archivo del trámite" y "firmar la
     * respuesta antes de enviarla": leer el .pfx en memoria, comprobar que el
     * certificado esté vigente y que sea del propio usuario, dibujar el sello y
     * firmar. El certificado y la contraseña se descartan antes de devolver.
     *
     * $subida es el elemento de $_FILES con el .pfx. $dniUsuario es el DNI de la
     * ficha de empleado: si el certificado trae uno distinto, se rechaza.
     *
     * Devuelve ['pdf', 'orden', 'normalizado', 'cert'].
     * @throws RuntimeException con un mensaje pensado para el usuario.
     */
    public static function firmarConCertificadoSubido(string $pdf, $subida, string $clave, ?string $dniUsuario,
                                                      string $motivo, string $codigo, string $urlValidar, string $lugar = ''): array
    {
        if (!$subida || ($subida['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($subida['tmp_name'])) {
            throw new RuntimeException('Adjunte su certificado digital (.pfx o .p12).');
        }
        if ($subida['size'] > 1048576) {
            @unlink($subida['tmp_name']);
            throw new RuntimeException('El certificado no debe pesar más de 1 MB.');
        }
        $contenidoPfx = (string) file_get_contents($subida['tmp_name']);
        @unlink($subida['tmp_name']);

        $certs = self::leerPfx($contenidoPfx, $clave);
        $contenidoPfx = null;
        $cert = self::datosCertificado($certs['cert']);
        self::validarCertificado($cert);

        // El certificado debe ser del propio usuario: se compara con su ficha de empleado
        $dniUsuario = $dniUsuario === null ? '' : preg_replace('/\D/', '', $dniUsuario);
        if ($cert['dni'] && $dniUsuario !== '' && $cert['dni'] !== $dniUsuario) {
            throw new RuntimeException('El certificado pertenece a otra persona (DNI ' . $cert['dni'] . '). Solo puede firmar con su propio certificado.');
        }

        $sello = self::sello($cert['nombre'], $cert['dni'], $motivo, date('d/m/Y H:i'), $codigo, $urlValidar);
        $firmado = self::firmar($pdf, $certs, [
            'nombre' => $cert['nombre'],
            'motivo' => $motivo,
            'lugar'  => $lugar,
            'sello'  => $sello,
        ]);
        $certs = null;

        return $firmado + ['cert' => $cert];
    }

    /** Estado de Firma Perú según config/firmaperu.php. */
    public static function firmaPeruConfigurado(): bool
    {
        $archivo = __DIR__ . '/../config/firmaperu.php';
        if (!is_file($archivo)) {
            return false;
        }
        $c = require $archivo;
        return !empty($c['habilitado']) && !empty($c['client_id']) && !empty($c['client_secret']);
    }

    /** SAPP devuelve false o lanza excepción según el defecto del archivo; aquí ambos son null. */
    private static function abrir(string $pdf): ?PDFDoc
    {
        try {
            ob_start();
            $doc = PDFDoc::from_string($pdf);
        } catch (Throwable $e) {
            $doc = false;
        } finally {
            ob_end_clean(); // SAPP puede imprimir avisos que romperían la respuesta JSON
        }
        return $doc === false ? null : $doc;
    }

    /** Vuelve a armar el PDF importando cada página como plantilla (mPDF con FPDI). */
    private static function reconstruir(string $pdf): string
    {
        try {
            $nuevo = new \Mpdf\Mpdf(['mode' => 'utf-8', 'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0]);
            $nuevo->SetAutoPageBreak(false);
            $paginas = $nuevo->setSourceFile(\setasign\Fpdi\PdfParser\StreamReader::createByString($pdf));
            for ($i = 1; $i <= $paginas; $i++) {
                $plantilla = $nuevo->importPage($i);
                $tam = $nuevo->getTemplateSize($plantilla);
                $nuevo->AddPageByArray([
                    'orientation' => $tam['orientation'],
                    'sheet-size'  => [$tam['width'], $tam['height']],
                ]);
                $nuevo->useTemplate($plantilla, 0, 0, $tam['width'], $tam['height']);
            }
            return $nuevo->Output('', 'S');
        } catch (Throwable $e) {
            error_log('[FIRMA] reconstruir: ' . $e->getMessage());
            throw new RuntimeException('No se pudo leer el PDF: el archivo está dañado, protegido con contraseña o tiene un formato no compatible.');
        }
    }

    private static function campo(array $partes, string $clave): string
    {
        $v = $partes[$clave] ?? '';
        return is_array($v) ? (string) reset($v) : (string) $v;
    }

    /** Los ceros de relleno del hueco /Contents no son parte de la firma. */
    private static function derExacto(string $bin): string
    {
        if (strlen($bin) < 2 || ord($bin[0]) !== 0x30) {
            return '';
        }
        $l = ord($bin[1]);
        if ($l < 0x80) {
            return substr($bin, 0, 2 + $l);
        }
        if ($l === 0x80) {
            return $bin;
        }
        $n = $l & 0x7f;
        $len = 0;
        for ($i = 0; $i < $n; $i++) {
            $len = ($len << 8) | ord($bin[2 + $i]);
        }
        return substr($bin, 0, 2 + $n + $len);
    }

    private static function tamanoPagina(PDFDoc $doc, int $pagina): array
    {
        $t = $doc->get_page_size($pagina);
        $texto = is_array($t) && isset($t[0]) && is_object($t[0]) ? (string) $t[0]->val() : '';
        $n = array_map('floatval', preg_split('/\s+/', $texto));
        if (count($n) >= 4) {
            return [abs($n[2] - $n[0]), abs($n[3] - $n[1])];
        }
        return [595.0, 842.0];
    }
}
