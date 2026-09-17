<?php
    require_once __DIR__ . '/../../lib/Seguridad.php';
    require '../../model/model_usuario.php';
    require '../../model/model_tramite.php';
    require_once __DIR__ . '/../../lib/OrigenTramite.php';
    require_once __DIR__ . '/../../model/model_conexion.php';
    Seguridad::iniciarSesion();
    $MU = new Modelo_Usuario();//Instaciamos
    $codigo = strtoupper(htmlspecialchars($_POST['codigo'] ?? '',ENT_QUOTES,'UTF-8'));
    $dni = strtoupper(htmlspecialchars($_POST['dni'] ?? '',ENT_QUOTES,'UTF-8'));

    // El detalle se entrega a quien conoce el código y el DNI del remitente (ciudadano o
    // personal que lo atiende), al administrador, o al área por la que pasó el trámite.
    $conoceDatos = $dni !== '' && count($MU->Cargar_Select_Datos_Seguimiento($codigo, $dni)) > 0;
    $autorizado = $conoceDatos
        || (Seguridad::autenticado() && (Seguridad::esAdmin() || (new Modelo_Tramite())->Area_Puede_Ver($codigo, Seguridad::areaId())));
    if (!$autorizado) {
        echo json_encode([]);
        exit;
    }

    $consulta = $MU->Traer_Datos_Detalle_Seguimiento($codigo);

    /*
     * El recorrido debe EMPEZAR por de dónde viene el trámite (un área de la
     * entidad, otra entidad o un ciudadano) y recién después las derivaciones.
     * Antes no se decía nunca: si el primer envío era de un área a sí misma se
     * leía "ORIGEN: MESA DE PARTES -> DESTINO: MESA DE PARTES", y si el trámite
     * arrancaba directamente con una derivación, el origen no aparecía.
     *
     * Por eso el origen se adjunta SIEMPRE a la primera fila (la pantalla lo pinta
     * como una tarjeta propia antes de los movimientos), y aparte se marca con
     * es_recepcion el envío de un área a sí misma, que es el ingreso del documento
     * y no una derivación.
     *
     * Las claves se AGREGAN por nombre: las filas traen índices numéricos y la
     * pantalla los usa por posición, así que no se puede insertar ni reordenar nada.
     */
    if ($consulta) {
        $origen = OrigenTramite::describir(
            OrigenTramite::consultar((new conexionBD())->conexionPDO(), $codigo)
        );
        $consulta[0]['origen_tipo'] = $origen['tipo'];
        $consulta[0]['origen_rotulo'] = $origen['rotulo'];
        $consulta[0]['origen_titulo'] = $origen['titulo'];
        $consulta[0]['origen_persona'] = $origen['persona'];
        $consulta[0]['origen_ruc'] = $origen['ruc'];
        $consulta[0]['origen_enlace'] = $origen['enlace'];

        foreach ($consulta as $i => $fila) {
            // area_origen_nombre [3] y area_destino_nombre [4]
            $consulta[$i]['es_recepcion'] = $i === 0 && (string) $fila[3] === (string) $fila[4];
        }
    }

    echo json_encode($consulta);
?>
