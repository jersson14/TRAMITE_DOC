<?php
$bd = require __DIR__ . '/../../config/database.php';
$mysqli = new mysqli($bd['host'], $bd['usuario'], $bd['clave'], $bd['nombre'], (int) $bd['puerto']);
$mysqli->set_charset('utf8');
$mysqli->query("SET lc_time_names = 'es_PE'"); // meses en español en los reportes
?>