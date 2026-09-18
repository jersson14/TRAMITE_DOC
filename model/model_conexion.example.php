<?php
/**
 * Conexión a base de datos - EJEMPLO
 * Copia este archivo como model_conexion.php. Los parámetros se leen de
 * config/database.php (copia de config/database.example.php).
 */
class conexionBD {
    private $pdo;

    public function conexionPDO() {
        $bd = require __DIR__ . '/../config/database.php';

        try {
            $this->pdo = new PDO("mysql:host={$bd['host']};port={$bd['puerto']};dbname={$bd['nombre']}", $bd['usuario'], $bd['clave']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("set names utf8");
            // Meses y días en español en DATE_FORMAT/MONTHNAME ("septiembre", no "September")
            $this->pdo->exec("SET lc_time_names = 'es_PE'");
            // Hora de Perú para NOW() y CURDATE() de los procedimientos, sea cual sea la
            // zona del servidor (un hosting suele estar en UTC: sin esto, fechas y plazos
            // se correrían 5 horas). Perú no tiene horario de verano: el desfase es fijo.
            $this->pdo->exec("SET time_zone = '-05:00'");
            return $this->pdo;
        } catch (PDOException $e) {
            error_log('[BD] ' . $e->getMessage());
            http_response_code(500);
            die('No se pudo conectar con la base de datos.');
        }
    }

    public function cerrar_conexion() {
        $this->pdo = null;
    }
}
?>
