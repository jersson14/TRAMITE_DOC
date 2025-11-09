<?php
class conexionBD {
    private $pdo;

    public function conexionPDO() {
        $host = "localhost";
        $puerto = "3307"; // <--- AGREGA ESTO
        $usuario = "root";
        $contrasena = ""; // sin contraseña
        $bdName = "sistema_tramite";

        try {
            // Incluye el puerto en la cadena de conexión
            $this->pdo = new PDO("mysql:host=$host;port=$puerto;dbname=$bdName", $usuario, $contrasena);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec("set names utf8");
            return $this->pdo;
        } catch (PDOException $e) {
            die('Falló la conexión: ' . $e->getMessage());
        }
    }

    public function cerrar_conexion() {
        $this->pdo = null;
    }
}
?>
