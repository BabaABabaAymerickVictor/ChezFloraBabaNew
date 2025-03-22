<?php
// classes/User.php
// Classe pour gérer les utilisateurs (connexion admin et client)

class User {
    private $db;

    public function __construct() {
        // Inclure la classe de connexion à la base de données
        require_once 'db_connect.php';
        $database = new Database();
        $this->db = $database->connect();
    }

    public function login($email, $password) {
        // Étape 1 : Vérifier si c'est le super admin avec les identifiants prédéfinis
        if ($email === 'admin@flora.com' && $password === 'admin1234') {
            $_SESSION['user_id'] = 0; // ID 0 pour le super admin
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'admin';
            return ['success' => true, 'message' => 'Connexion super admin réussie', 'redirect' => 'templates/admin/dashboard.php'];
        }

        // Étape 2 : Vérifier si c'est un administrateur dans la table admin
        try {
            $query = "SELECT * FROM admin WHERE nom_admin = :nom_admin AND is_deleted = 0";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nom_admin', $email); // On utilise l'email comme nom_admin
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($password === $admin['password']) { // Comparaison directe (non sécurisée, voir note ci-dessous)
                    $_SESSION['user_id'] = $admin['id_admin'];
                    $_SESSION['user_email'] = $admin['nom_admin'];
                    $_SESSION['user_role'] = 'admin';
                    return ['success' => true, 'message' => 'Connexion admin réussie', 'redirect' => 'templates/admin/dashboard.php'];
                } else {
                    return ['success' => false, 'message' => 'Mot de passe incorrect pour l\'administrateur'];
                }
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la vérification admin : ' . $e->getMessage()];
        }

        // Étape 3 : Si ce n'est pas un admin, vérifier si c'est un client dans la table user
        try {
            $query = "SELECT * FROM user WHERE email = :email AND is_deleted = 0";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($password === $user['password']) { // Comparaison directe (non sécurisée, voir note ci-dessous)
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'client';
                    return ['success' => true, 'message' => 'Connexion client réussie', 'redirect' => 'templates/client/connected/accueil.php'];
                } else {
                    return ['success' => false, 'message' => 'Mot de passe incorrect'];
                }
            } else {
                return ['success' => false, 'message' => 'Utilisateur non trouvé ou compte désactivé'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Erreur lors de la vérification client : ' . $e->getMessage()];
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ../../index.php');
        exit();
    }
}
?>