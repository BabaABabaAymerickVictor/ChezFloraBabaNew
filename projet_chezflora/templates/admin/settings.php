<?php
// templates/admin/settings.php
// Page de gestion des paramètres du site pour l'administrateur

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

// Récupérer les paramètres actuels
try {
    $query = "SELECT slogan, description, a_propos_entreprise, date_modification 
              FROM settings 
              WHERE id = 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$settings) {
        $errorMessage = "Aucun paramètre trouvé. Veuillez insérer des données initiales.";
    }
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des paramètres : ' . $e->getMessage();
}

// Gérer la mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $slogan = $_POST['slogan'] ?? '';
    $description = $_POST['description'] ?? '';
    $a_propos_entreprise = $_POST['a_propos_entreprise'] ?? '';

    // Validation simple
    if (empty($slogan) || empty($description) || empty($a_propos_entreprise)) {
        $errorMessage = 'Tous les champs sont requis.';
    } else {
        try {
            $query = "UPDATE settings 
                      SET slogan = :slogan, description = :description, a_propos_entreprise = :a_propos_entreprise 
                      WHERE id = 1";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':slogan', $slogan);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':a_propos_entreprise', $a_propos_entreprise);
            $stmt->execute();

            $successMessage = 'Paramètres mis à jour avec succès !';

            // Rafraîchir les données après mise à jour
            $query = "SELECT slogan, description, a_propos_entreprise, date_modification 
                      FROM settings 
                      WHERE id = 1";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errorMessage = 'Erreur lors de la mise à jour des paramètres : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Paramètres du Site</title>
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
        /* Palette de couleurs */
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
        .settings-container {
            max-width: 800px;
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

        .settings-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .settings-container::before {
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

        .settings-container > * {
            position: relative;
            z-index: 1;
        }

        .settings-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
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

        /* Cadre pour les paramètres actuels */
        .current-settings {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 30px;
        }

        .current-settings h3 {
            color: var(--vert-principal);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .current-settings p {
            margin: 0 0 10px;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .last-modified {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: right;
        }

        /* Formulaire de modification */
        .settings-form .form-control {
            border: 2px solid var(--vert-clair);
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .settings-form .form-control:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 10px rgba(92, 141, 118, 0.3);
        }

        .btn-submit {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
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

            .settings-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .settings-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .current-settings h3 {
                font-size: 1.2rem;
            }

            .current-settings p {
                font-size: 0.9rem;
            }

            .last-modified {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="settings-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Paramètres du Site</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if ($settings): ?>
            <!-- Affichage des paramètres actuels -->
            <div class="current-settings">
                <h3>Paramètres Actuels</h3>
                <p><strong>Slogan :</strong> <?php echo htmlspecialchars($settings['slogan']); ?></p>
                <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($settings['description'])); ?></p>
                <p><strong>À propos de l'entreprise :</strong> <?php echo nl2br(htmlspecialchars($settings['a_propos_entreprise'])); ?></p>
                <div class="last-modified">
                    Dernière modification : <?php echo date('d/m/Y à H:i', strtotime($settings['date_modification'])); ?>
                </div>
            </div>

            <!-- Formulaire pour modifier les paramètres -->
            <h3>Modifier les Paramètres</h3>
            <div class="settings-form">
                <form method="POST" action="settings.php">
                    <div class="mb-3">
                        <label for="slogan" class="form-label">Slogan</label>
                        <input type="text" class="form-control" id="slogan" name="slogan" value="<?php echo htmlspecialchars($settings['slogan']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($settings['description']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="a_propos_entreprise" class="form-label">À propos de l'entreprise</label>
                        <textarea class="form-control" id="a_propos_entreprise" name="a_propos_entreprise" rows="5" required><?php echo htmlspecialchars($settings['a_propos_entreprise']); ?></textarea>
                    </div>
                    <button type="submit" name="update_settings" class="btn btn-submit">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </form>
            </div>
        <?php else: ?>
            <p class="text-center">Aucune donnée disponible. Veuillez insérer des paramètres initiaux.</p>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const settingsContainer = document.querySelector('.settings-container');
            settingsContainer.addEventListener('mouseenter', function() {
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