<?php
$bd = require __DIR__ . '/../../config/database.php';
$mysqli = new mysqli($bd['host'], $bd['usuario'], $bd['clave'], $bd['nombre'], (int) $bd['puerto']);
$mysqli->set_charset('utf8');
?>