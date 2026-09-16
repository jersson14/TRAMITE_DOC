<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_empleado.php';
    $ME = new Modelo_Empleado();
    $id = htmlspecialchars($_POST['id'] ?? '',ENT_QUOTES,'UTF-8');
    $fotoactual = (string) ($_POST['fotoactual'] ?? '');

    try {
        $nombrefoto = Seguridad::guardarArchivo('foto', __DIR__ . '/FOTOS', Seguridad::MIME_IMAGEN, 5 * 1048576, 'IMG');
    } catch (RuntimeException $e) {
        Seguridad::responderError(422, $e->getMessage());
    }

    $ruta = $nombrefoto ? 'controller/empleado/FOTOS/'.$nombrefoto : 'controller/empleado/FOTOS/usuario.png';

    $consulta = $ME->Modificar_foto_empleado($id,$ruta);
    echo $consulta;
    if ($consulta==1 && $nombrefoto) {
        // Solo se borra dentro de FOTOS/, nunca una ruta arbitraria enviada por el cliente
        Seguridad::borrarArchivoEn(__DIR__ . '/FOTOS', $fotoactual, ['usuario.png']);
    }
?>