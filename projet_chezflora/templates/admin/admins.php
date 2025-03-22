<?php
// templates/admin/admins.php
// Page de gestion des administrateurs pour le super admin

session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Inclure la classe de connexion à la base de données
require_once '../../classes/db_connect.php';

// Connexion à la base de données
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("Erreur de connexion à la base de données.");
}

$successMessage = '';
$errorMessage = '';

// Gérer l'ajout d'un nouvel administrateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $nomAdmin = $_POST['nom_admin'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validation des champs
    if (empty($nomAdmin) || empty($password)) {
        $errorMessage = 'Tous les champs sont requis.';
    } else {
        try {
            // Insérer le nouvel administrateur (is_deleted = 0 par défaut)
            $query = "INSERT INTO admin (nom_admin, password, is_deleted) VALUES (:nom_admin, :password, 0)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nom_admin', $nomAdmin);
            $stmt->bindParam(':password', $password); // Note : dans une application réelle, hacher le mot de passe avec password_hash()
            $stmt->execute();

            $successMessage = 'Administrateur ajouté avec succès.';
        } catch (PDOException $e) {
            $errorMessage = 'Erreur lors de l\'ajout de l\'administrateur : ' . $e->getMessage();
        }
    }
}

// Gérer l'activation/désactivation d'un administrateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id']) && isset($_POST['is_deleted'])) {
    $adminId = $_POST['admin_id'];
    $isDeleted = $_POST['is_deleted'] === '1' ? 1 : 0;

    try {
        $query = "UPDATE admin SET is_deleted = :is_deleted WHERE id_admin = :id_admin";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':is_deleted', $isDeleted, PDO::PARAM_INT);
        $stmt->bindParam(':id_admin', $adminId, PDO::PARAM_INT);
        $stmt->execute();

        $successMessage = 'Statut du compte mis à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Récupérer la liste des administrateurs
try {
    $query = "SELECT id_admin, nom_admin, is_deleted FROM admin ORDER BY id_admin";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des administrateurs : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Gestion des Administrateurs</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- CSS personnalisé -->
    <style>
        /* Palette de couleurs (identique aux pages précédentes) */
        :root {
            --vert-principal: rgb(170, 86, 90);
            --vert-clair: #A3C9A8;
            --rose-doux: #EACBD2;
            --blanc-creme: #F9F7F4;
            --terre-cuite: #D9B391;
            --text-dark: #363636;
        }

        /* Style général */
        body {
            background-color: var(--blanc-creme);
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            margin: 0;
            padding-left: 250px;
            transition: padding-left 0.3s ease;
        }

        body.sidebar-collapsed {
            padding-left: 80px;
        }

        /* Contenu principal */
        .admins-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 35px;
            background-color: var(--blanc-creme);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(92, 141, 118, 0.15);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1s ease-out;
        }

        .admins-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .admins-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 100 100"><path fill="%235C8D76" fill-opacity="0.05" d="M30,10 C35,25 45,30 60,30 C45,35 35,45 30,60 C25,45 15,35 0,30 C15,25 25,15 30,10 Z"/></svg>') repeat;
            opacity: 0.3;
            z-index: 0;
        }

        .admins-container > * {
            position: relative;
            z-index: 1;
        }

        .admins-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        /* Bouton pour ajouter un admin */
        .add-admin-btn {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            display: inline-block;
        }

        .add-admin-btn:hover {
            background-color: var(--vert-clair);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(92, 141, 118, 0.4);
        }

        /* Messages de succès et d'erreur */
        .success-message {
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        /* Tableau des administrateurs */
        .admins-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .admins-table th,
        .admins-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
        }

        .admins-table th {
            background-color: var(--vert-clair);
            color: var(--blanc-creme);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .admins-table td {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .admins-table tr:hover {
            background-color: rgba(92, 141, 118, 0.05);
        }

        /* Statut */
        .status-active {
            color: #28a745;
            font-weight: 500;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: 500;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #dc3545;
            transition: 0.4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: 0.4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #28a745;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Modale */
        .modal-content {
            border-radius: 15px;
            background-color: var(--blanc-creme);
            box-shadow: 0 10px 30px rgba(92, 141, 118, 0.15);
        }

        .modal-header {
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
            background-color: var(--vert-clair);
            color: var(--blanc-creme);
        }

        .modal-title {
            font-family: 'Great Vibes', cursive;
            font-size: 2rem;
        }

        .modal-body {
            padding: 20px;
        }

        .form-control {
            border: 2px solid var(--vert-clair);
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 10px rgba(92, 141, 118, 0.3);
        }

        .btn-save {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
        }

        /* Logo floral décoratif */
        .floral-decoration {
            position: absolute;
            width: 150px;
            height: 150px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path fill="%235C8D76" fill-opacity="0.1" d="M50,0 C55,35 65,45 100,50 C65,55 55,65 50,100 C45,65 35,55 0,50 C35,45 45,35 50,0 Z"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            z-index: 0;
            opacity: 0.2;
            animation: rotate 20s linear infinite;
        }

        .floral-top-right {
            top: -50px;
            right: -50px;
        }

        .floral-bottom-left {
            bottom: -50px;
            left: -50px;
            animation-direction: reverse;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding-left: 80px;
            }

            body.sidebar-collapsed {
                padding-left: 0;
            }

            .admins-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }

            .admins-table th,
            .admins-table td {
                font-size: 0.85rem;
                padding: 10px;
            }
        }

        @media (max-width: 576px) {
            .admins-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .admins-table th,
            .admins-table td {
                font-size: 0.8rem;
                padding: 8px;
            }

            .switch {
                width: 40px;
                height: 20px;
            }

            .slider:before {
                height: 16px;
                width: 16px;
                bottom: 2px;
                left: 2px;
            }

            input:checked + .slider:before {
                transform: translateX(20px);
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="admins-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Gestion des Administrateurs</h2>
        
        <!-- Bouton pour ajouter un admin -->
        <button type="button" class="add-admin-btn" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="fas fa-user-plus me-2"></i>Ajouter un Administrateur
        </button>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (empty($admins)): ?>
            <p class="text-center">Aucun administrateur trouvé.</p>
        <?php else: ?>
            <table class="admins-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['nom_admin']); ?></td>
                            <td>
                                <span class="<?php echo $admin['is_deleted'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $admin['is_deleted'] == 0 ? 'Actif' : 'Désactivé'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="admins.php" style="display: inline;">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id_admin']; ?>">
                                    <input type="hidden" name="is_deleted" value="<?php echo $admin['is_deleted'] == 0 ? '1' : '0'; ?>">
                                    <label class="switch">
                                        <input type="checkbox" <?php echo $admin['is_deleted'] == 0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="slider"></span>
                                    </label>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modale pour ajouter un administrateur -->
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAdminModalLabel">Ajouter un Administrateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="admins.php">
                        <div class="mb-3">
                            <label for="nom_admin" class="form-label">Nom de l'administrateur</label>
                            <input type="text" class="form-control" id="nom_admin" name="nom_admin" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="add_admin" class="btn btn-save">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const adminsContainer = document.querySelector('.admins-container');
            adminsContainer.addEventListener('mouseenter', function() {
                this.classList.add('animate__animated', 'animate__pulse');
            });

            // Synchroniser l'état de la sidebar avec le padding du body
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('toggleBtn');

            toggleBtn.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>