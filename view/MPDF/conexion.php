<?php
$bd = require __DIR__ . '/../../config/database.php';
$mysqli = new mysqli($bd['host'], $bd['usuario'], $bd['clave'], $bd['nombre'], (int) $bd['puerto']);
$mysqli->set_charset('utf8');
$mysqli->query("SET lc_time_names = 'es_PE'"); // meses en español en los reportes
$mysqli->query("SET time_zone = '-05:00'");         // hora de Perú aunque el servidor esté en otra zona
?>