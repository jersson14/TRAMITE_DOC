<?php
require_once __DIR__ . '/Seguridad.php';
require_once __DIR__ . '/../model/model_conexion.php';

/**
 * Bitácora de auditoría: deja constancia de quién hizo qué y cuándo.
 *
 * Regla de oro: registrar nunca debe tumbar la operación real. Si la bitácora
 * falla (tabla ausente, base caída), se traga el error y el trámite continúa.
 */
class Bitacora
{
    // Acciones sobre la sesión
    const INGRESO          = 'INGRESO';
    const INGRESO_FALLIDO  = 'INGRESO_FALLIDO';
    const SALIDA           = 'SALIDA';

    // Acciones sobre trámites
    const REGISTRO_TRAMITE = 'REGISTRO_TRAMITE';
    const DERIVO_TRAMITE   = 'DERIVO_TRAMITE';
    const CAMBIO_ESTADO    = 'CAMBIO_ESTADO';
    const ELIMINO_TRAMITE  = 'ELIMINO_TRAMITE';
    const RECEPCION        = 'RECEPCION';
    const ATENCION_SOLICITADA = 'ATENCION_SOLICITADA';
    const ATENCION_RESPONDIDA = 'ATENCION_RESPONDIDA';
    const FIRMA_DIGITAL    = 'FIRMA_DIGITAL';
    const OBSERVACION      = 'OBSERVACION';   // el área observa el trámite (migración 025)
    const SUBSANACION      = 'SUBSANACION';   // el ciudadano subsana desde el portal

    // Acciones sobre el mantenimiento
    const REGISTRO         = 'REGISTRO';
    const MODIFICO         = 'MODIFICO';
    const ELIMINO          = 'ELIMINO';

    /**
     * Anota una acción. El usuario sale de la sesión activa; si no hay sesión
     * (por ejemplo un registro desde la mesa de partes virtual), se guarda como
     * anónimo con su IP, que es justamente lo que interesa auditar ahí.
     */
    public static function registrar(
        string $accion,
        ?string $entidad = null,
        ?string $entidadId = null,
        ?string $detalle = null,
        ?string $usuarioManual = null
    ): void {
        try {
            // Los modelos heredan de conexionBD y por eso la invocan de forma
            // estática; aquí no hay herencia, así que se usa una instancia.
            $conexion = (new conexionBD())->conexionPDO();
            $sql = 'INSERT INTO bitacora
                    (usuario_id, bit_usuario, bit_rol, bit_accion, bit_entidad, bit_entidad_id, bit_detalle, bit_ip)
                    VALUES (?,?,?,?,?,?,?,?)';

            $usuarioId = Seguridad::usuarioId();
            $usuario = $usuarioManual;
            if ($usuario === null) {
                $usuario = isset($_SESSION['S_USU']) ? (string) $_SESSION['S_USU'] : 'anónimo';
            }

            $consulta = $conexion->prepare($sql);
            $consulta->execute([
                $usuarioId > 0 ? $usuarioId : null,
                mb_substr($usuario, 0, 150),
                Seguridad::rol() !== '' ? Seguridad::rol() : null,
                mb_substr($accion, 0, 60),
                $entidad !== null ? mb_substr($entidad, 0, 40) : null,
                $entidadId !== null ? mb_substr($entidadId, 0, 40) : null,
                $detalle !== null ? mb_substr($detalle, 0, 500) : null,
                mb_substr(Seguridad::ipCliente(), 0, 45),
            ]);
        } catch (Throwable $e) {
            // La auditoría no puede interrumpir la operación que la originó.
            error_log('Bitacora: ' . $e->getMessage());
        }
    }
}
