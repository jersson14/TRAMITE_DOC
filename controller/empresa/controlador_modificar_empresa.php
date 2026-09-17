<?php
    require_once __DIR__ . '/../_guard_admin.php';
    require '../../model/model_empresa.php';
    $ME = new Modelo_Empresa();//Instaciamos
    $id = strtoupper(htmlspecialchars($_POST['id'],ENT_QUOTES,'UTF-8'));
    $nom = strtoupper(htmlspecialchars($_POST['nom'],ENT_QUOTES,'UTF-8'));
    $email = strtoupper(htmlspecialchars($_POST['email'],ENT_QUOTES,'UTF-8'));
    $cod = strtoupper(htmlspecialchars($_POST['cod'],ENT_QUOTES,'UTF-8'));
    $tel = strtoupper(htmlspecialchars($_POST['tel'],ENT_QUOTES,'UTF-8'));
    $dir = strtoupper(htmlspecialchars($_POST['dir'],ENT_QUOTES,'UTF-8'));

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
