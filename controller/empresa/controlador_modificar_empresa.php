<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_empresa.php';
    $ME = new Modelo_Empresa();//Instaciamos
    // Se guarda como lo escribió el administrador: antes se forzaba a MAYÚSCULAS
    // y el nombre de la institución aparece así en el menú, el portal y los PDF.
    $id = (int) ($_POST['id'] ?? 0);
    $nom = trim(htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'));
    $cod = trim(htmlspecialchars($_POST['cod'] ?? '', ENT_QUOTES, 'UTF-8'));
    $tel = trim(htmlspecialchars($_POST['tel'] ?? '', ENT_QUOTES, 'UTF-8'));
    $dir = trim(htmlspecialchars($_POST['dir'] ?? '', ENT_QUOTES, 'UTF-8'));

    if ($id <= 0 || $nom === '' || $email === '' || $cod === '' || $tel === '' || $dir === '') {
        Seguridad::responderError(422, 'Complete los datos de la institución.');
    }

    // Horario de recepción (migración 015): HH:MM, apertura antes del cierre
    $hini = (string) ($_POST['hini'] ?? '');
    $hfin = (string) ($_POST['hfin'] ?? '');
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hini) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hfin) || $hini >= $hfin) {
        Seguridad::responderError(422, 'Indique un horario de recepción válido: la hora de inicio debe ser anterior a la de cierre.');
    }

    $consulta = $ME->Modificar_Empresa($id,$nom,$email,$cod,$tel,$dir);
    if ($consulta) {
        $ME->Modificar_Horario_Recepcion($id, $hini, $hfin);
    }
    echo $consulta;
?>
