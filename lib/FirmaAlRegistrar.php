<?php
require_once __DIR__ . '/FirmaDigital.php';

/**
 * Firma los PDF de un trámite en el momento de registrarlo.
 *
 * El documento que redacta la entidad debe salir FIRMADO: así nunca circula una
 * versión sin firma y no hay que ir después al panel de archivos a firmarlo.
 *
 * A diferencia del panel, aquí se firma EN SITIO: el archivo guardado pasa a ser
 * el firmado, sin dejar una copia previa. Tiene sentido porque el trámite se está
 * creando en ese mismo momento: no hay una versión anterior que ya haya circulado
 * y que valga como evidencia.
 *
 * La firma es opcional por decisión de la entidad: si no se adjunta certificado,
 * el trámite se registra igual y queda constancia en la bitácora.
 */
class FirmaAlRegistrar
{
    /**
     * ¿El formulario pidió firmar? (adjuntó certificado)
     */
    public static function solicitada(): bool
    {
        return !empty($_FILES['certificado']['name']);
    }

    /**
     * Lee el certificado y comprueba que sirva y que sea del propio usuario.
     * Se hace ANTES de crear el trámite: si el certificado está mal, no se
     * registra nada y el usuario corrige.
     *
     * Devuelve ['certs' => ..., 'cert' => datos legibles].
     * @throws RuntimeException con un mensaje para el usuario.
     */
    public static function prepararCertificado(string $clave, ?string $dniUsuario): array
    {
        $subida = $_FILES['certificado'] ?? null;
        if (!$subida || ($subida['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($subida['tmp_name'])) {
            throw new RuntimeException('Adjunte su certificado digital (.pfx o .p12).');
        }
        if ($subida['size'] > 1048576) {
            @unlink($subida['tmp_name']);
            throw new RuntimeException('El certificado no debe pesar más de 1 MB.');
        }
        $contenido = (string) file_get_contents($subida['tmp_name']);
        @unlink($subida['tmp_name']);

        $certs = FirmaDigital::leerPfx($contenido, $clave);
        $contenido = null;
        $cert = FirmaDigital::datosCertificado($certs['cert']);
        FirmaDigital::validarCertificado($cert);

        $dniUsuario = $dniUsuario === null ? '' : preg_replace('/\D/', '', $dniUsuario);
        if ($cert['dni'] && $dniUsuario !== '' && $cert['dni'] !== $dniUsuario) {
            throw new RuntimeException('El certificado pertenece a otra persona (DNI ' . $cert['dni'] . '). Solo puede firmar con su propio certificado.');
        }

        return ['certs' => $certs, 'cert' => $cert];
    }

    /**
     * Firma en sitio cada archivo y registra la firma.
     *
     * $archivos: lista de ['ruta' => ruta relativa, 'absoluta' => ruta en disco,
     *                      'anexo_id' => id o null, 'etiqueta' => para la bitácora].
     * Devuelve ['firmados' => [...], 'errores' => [...]].
     *
     * Un archivo que falla no detiene a los demás: el trámite ya existe y no tiene
     * sentido perder las firmas que sí salieron.
     */
    public static function firmarArchivos(array $archivos, array $preparado, Modelo_Firma $MFI,
                                          string $documentoId, string $motivo,
                                          ?int $usuarioId, ?int $areaId): array
    {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $raizWeb = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'], 3)), '/');
        $cert = $preparado['cert'];

        $firmados = [];
        $errores = [];

        foreach ($archivos as $a) {
            try {
                if (!is_file($a['absoluta'])) {
                    throw new RuntimeException('El archivo no se encuentra en el servidor.');
                }
                $pdf = (string) file_get_contents($a['absoluta']);
                if (strncmp($pdf, '%PDF', 4) !== 0) {
                    throw new RuntimeException('Solo se pueden firmar documentos PDF.');
                }

                $codigo = $MFI->Codigo_Libre();
                $urlValidar = $protocolo . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $raizWeb
                    . '/validar_firma.php?codigo=' . $codigo;

                $sello = FirmaDigital::sello($cert['nombre'], $cert['dni'], $motivo, date('d/m/Y H:i'), $codigo, $urlValidar);
                $firmado = FirmaDigital::firmar($pdf, $preparado['certs'], [
                    'nombre' => $cert['nombre'],
                    'motivo' => $motivo,
                    'lugar'  => 'Abancay, Apurímac',
                    'sello'  => $sello,
                ]);

                if (file_put_contents($a['absoluta'], $firmado['pdf']) === false) {
                    throw new RuntimeException('No se pudo guardar el documento firmado.');
                }

                // archivo_origen == archivo_firmado: se firmó en sitio, no hay copia
                // aparte. Origenes_Ya_Firmados() lo excluye por eso.
                $MFI->Registrar_Firma_Archivo([
                    'codigo'           => $codigo,
                    'documento_id'     => $documentoId,
                    'orden'            => $firmado['orden'],
                    'archivo_origen'   => $a['ruta'],
                    'archivo_firmado'  => $a['ruta'],
                    'hash_origen'      => hash('sha256', $pdf),
                    'hash_firmado'     => hash('sha256', $firmado['pdf']),
                    'motivo'           => $motivo !== '' ? $motivo : null,
                    'firmante_nombre'  => mb_substr($cert['nombre'], 0, 200),
                    'firmante_dni'     => $cert['dni'],
                    'cert_emisor'      => mb_substr((string) $cert['emisor'], 0, 255),
                    'cert_serie'       => mb_substr((string) $cert['serie'], 0, 80),
                    'cert_desde'       => $cert['desde'],
                    'cert_hasta'       => $cert['hasta'],
                    'cert_huella'      => $cert['huella'],
                    'cert_autofirmado' => $cert['autofirmado'],
                    'usuario_id'       => $usuarioId,
                    'area_id'          => $areaId,
                ], $a['anexo_id']);

                // El PDF creció al firmarlo: el panel mostraría el peso anterior
                if ($a['anexo_id']) {
                    $MFI->Actualizar_Bytes_Anexo($a['anexo_id'], strlen($firmado['pdf']));
                }

                $firmados[] = ['etiqueta' => $a['etiqueta'], 'codigo' => $codigo];
            } catch (Throwable $e) {
                error_log('[FIRMA] al registrar: ' . $e->getMessage());
                $errores[] = [
                    'etiqueta' => $a['etiqueta'],
                    'mensaje'  => $e instanceof RuntimeException
                        ? $e->getMessage()
                        : 'No se pudo firmar este PDF.',
                ];
            }
        }

        return ['firmados' => $firmados, 'errores' => $errores];
    }

    /** Una línea para la bitácora con lo que se firmó al registrar. */
    public static function resumenBitacora(array $r): string
    {
        if (!$r['firmados'] && !$r['errores']) {
            return '';
        }
        $texto = '';
        if ($r['firmados']) {
            $codigos = array_map(function ($f) { return $f['codigo']; }, $r['firmados']);
            $texto .= ' · firmado al registrar (' . count($r['firmados']) . ': ' . implode(', ', $codigos) . ')';
        }
        if ($r['errores']) {
            $texto .= ' · ' . count($r['errores']) . ' archivo(s) no se pudieron firmar';
        }
        return $texto;
    }
}
