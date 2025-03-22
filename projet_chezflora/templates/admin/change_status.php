<?php
// templates/admin/change_status.php
// Page pour changer le statut d'une commande

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

// Vérifier si un ID de commande est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$orderId = $_GET['id'];

// Récupérer les informations de la commande
try {
    $query = "SELECT o.id_order, o.status, u.email 
              FROM orders o 
              JOIN user u ON o.id_user = u.id_user 
              WHERE o.id_order = :id_order AND o.is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_order', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header('Location: orders.php');
        exit();
    }
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération de la commande : ' . $e->getMessage();
}

// Gérer la mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';

    // Vérifier que le statut est valide
    $validStatuses = ['En attente', 'Expédiée', 'Livrée'];
    if (!in_array($newStatus, $validStatuses)) {
        $errorMessage = 'Statut invalide.';
    } else {
        try {
            $query = "UPDATE orders SET status = :status WHERE id_order = :id_order";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id_order', $orderId, PDO::PARAM_INT);
            $stmt->execute();

            $successMessage = 'Statut mis à jour avec succès.';
            header('Location: orders.php?success=' . urlencode($successMessage));
            exit();
        } catch (PDOException $e) {
            $errorMessage = 'Erreur lors de la mise à jour du statut : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Changer le Statut de la Commande</title>
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
        .change-status-container {
            max-width: 600px;
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

        .change-status-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .change-status-container::before {
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

        .change-status-container > * {
            position: relative;
            z-index: 1;
        }

        .change-status-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 20px;
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

        /* Formulaire */
        .status-form select {
            border: 2px solid var(--vert-clair);
            border-radius: 5px;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .status-form select:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 5px rgba(92, 141, 118, 0.3);
        }

        .btn-submit {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
        }

        .btn-back {
            background-color: var(--terre-cuite);
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
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

            .change-status-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .change-status-container h2 {
                font-size: 2rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="change-status-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Changer le Statut de la Commande</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <p><strong>Commande #<?php echo $order['id_order']; ?></strong> - Client : <?php echo htmlspecialchars($order['email']); ?></p>
        <p>Statut actuel : <strong><?php echo $order['status']; ?></strong></p>

        <form method="POST" action="change_status.php?id=<?php echo $orderId; ?>" class="status-form">
            <div class="mb-3">
                <label for="status" class="form-label">Nouveau Statut :</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="En attente" <?php echo $order['status'] === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                    <option value="Expédiée" <?php echo $order['status'] === 'Expédiée' ? 'selected' : ''; ?>>Expédiée</option>
                    <option value="Livrée" <?php echo $order['status'] === 'Livrée' ? 'selected' : ''; ?>>Livrée</option>
                </select>
            </div>
            <button type="submit" name="update_status" class="btn btn-submit">Mettre à jour</button>
            <a href="orders.php" class="btn btn-back">Retour</a>
        </form>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const changeStatusContainer = document.querySelector('.change-status-container');
            changeStatusContainer.addEventListener('mouseenter', function() {
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