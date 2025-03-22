<?php
// templates/admin/quotes.php
// Page de gestion des devis pour l'administrateur

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

// Gérer la suppression d'un devis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devis_id']) && isset($_POST['is_deleted'])) {
    $devisId = $_POST['devis_id'];
    $isDeleted = $_POST['is_deleted'] === '1' ? 1 : 0;

    try {
        $query = "UPDATE devis SET is_deleted = :is_deleted WHERE id_devis = :id_devis";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':is_deleted', $isDeleted, PDO::PARAM_INT);
        $stmt->bindParam(':id_devis', $devisId, PDO::PARAM_INT);
        $stmt->execute();

        $successMessage = 'Statut du devis mis à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Récupérer tous les devis
try {
    $query = "SELECT id_devis, name, email, phone, event_type, details, date_creation, is_deleted 
              FROM devis 
              ORDER BY date_creation DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $devisList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des devis : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Gestion des Devis</title>
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
        .quotes-container {
            max-width: 1200px;
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

        .quotes-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .quotes-container::before {
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

        .quotes-container > * {
            position: relative;
            z-index: 1;
        }

        .quotes-container h2 {
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

        /* Cards pour chaque devis */
        .devis-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            padding: 20px;
        }

        .devis-card h3 {
            color: var(--vert-principal);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .devis-details {
            font-size: 0.95rem;
            color: var(--text-dark);
            margin-bottom: 20px;
        }

        .devis-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Toggle Switch pour les devis */
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

        /* Statut */
        .status-active {
            color: #28a745;
            font-weight: 500;
        }

        .status-inactive {
            color: #dc3545;
            font-weight: 500;
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

            .quotes-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .quotes-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .devis-card h3 {
                font-size: 1.2rem;
            }

            .devis-details {
                font-size: 0.9rem;
            }

            .devis-meta {
                font-size: 0.8rem;
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
    <div class="quotes-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Gestion des Devis</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (empty($devisList)): ?>
            <p class="text-center">Aucun devis trouvé.</p>
        <?php else: ?>
            <?php foreach ($devisList as $devis): ?>
                <div class="devis-card">
                    <h3>Devis de <?php echo htmlspecialchars($devis['name']); ?></h3>
                    <div class="devis-details">
                        <p><strong>Email :</strong> <?php echo htmlspecialchars($devis['email']); ?></p>
                        <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($devis['phone']); ?></p>
                        <p><strong>Type d'événement :</strong> <?php echo htmlspecialchars($devis['event_type']); ?></p>
                        <p><strong>Détails :</strong> <?php echo nl2br(htmlspecialchars($devis['details'])); ?></p>
                    </div>
                    <div class="devis-meta">
                        Soumis le <?php echo date('d/m/Y à H:i', strtotime($devis['date_creation'])); ?> -
                        Statut : <span class="<?php echo $devis['is_deleted'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $devis['is_deleted'] == 0 ? 'Actif' : 'Supprimé'; ?>
                        </span>
                    </div>
                    <form method="POST" action="quotes.php" style="display: inline;">
                        <input type="hidden" name="devis_id" value="<?php echo $devis['id_devis']; ?>">
                        <input type="hidden" name="is_deleted" value="<?php echo $devis['is_deleted'] == 0 ? '1' : '0'; ?>">
                        <label class="switch">
                            <input type="checkbox" <?php echo $devis['is_deleted'] == 0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span class="slider"></span>
                        </label>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const quotesContainer = document.querySelector('.quotes-container');
            quotesContainer.addEventListener('mouseenter', function() {
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