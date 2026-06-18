<?php
/**
 * Conexión a base de datos - EJEMPLO
 * Copia este archivo como model_conexion.php y ajusta
 * los datos a tu entorno local. Ese archivo NO se sube
 * al repositorio (ver .gitignore).
 */
class conexionBD {
    private $pdo;

    public function conexionPDO() {
        $host = "localhost";
        $puerto = "3306";
        $usuario = "root";
        $contrasena = "";
        $bdName = "sistema_tramite";

        try {
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
