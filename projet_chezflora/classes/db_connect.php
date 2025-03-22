<?php
// classes/db_connect.php
// Classe pour gérer la connexion à la base de données

class Database {
    private $host = 'localhost';
    private $db_name = 'flora';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    // Méthode pour établir la connexion
    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Erreur de connexion : " . $e->getMessage();
            return null;
        }
    }

    // Méthode pour fermer la connexion
    public function disconnect() {
        $this->conn = null;
    }
}
?>