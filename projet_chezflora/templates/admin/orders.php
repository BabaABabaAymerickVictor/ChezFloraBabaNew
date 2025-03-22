<?php
// templates/admin/orders.php
// Page de gestion des commandes pour l'administrateur

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

// Récupérer toutes les commandes
try {
    $query = "SELECT o.id_order, o.date_creation, o.total_amount, o.status, u.email 
              FROM orders o 
              JOIN user u ON o.id_user = u.id_user 
              WHERE o.is_deleted = 0 
              ORDER BY o.date_creation DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commande, récupérer les produits associés
    foreach ($orders as &$order) {
        $query = "SELECT oi.quantity, oi.unit_price, p.nom_product, p.image_product 
                  FROM order_items oi 
                  JOIN products p ON oi.id_product = p.id_product 
                  WHERE oi.id_order = :id_order";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_order', $order['id_order'], PDO::PARAM_INT);
        $stmt->execute();
        $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des commandes : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Gestion des Commandes</title>
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
        .orders-container {
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

        .orders-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .orders-container::before {
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

        .orders-container > * {
            position: relative;
            z-index: 1;
        }

        .orders-container h2 {
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

        /* Tableau des commandes */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .orders-table th, .orders-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
        }

        .orders-table th {
            background-color: var(--vert-principal);
            color: white;
            font-weight: 500;
        }

        .orders-table tr:last-child td {
            border-bottom: none;
        }

        /* Boutons d'action */
        .btn-details, .btn-change-status {
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            color: white;
            transition: all 0.3s ease;
            margin-right: 5px;
        }

        .btn-details {
            background-color: #007bff;
        }

        .btn-details:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .btn-change-status {
            background-color: var(--vert-principal);
        }

        .btn-change-status:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
        }

        /* Style pour la modale */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: var(--vert-principal);
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-body img {
            max-width: 50px;
            height: auto;
            margin-right: 10px;
        }

        .modal-footer .btn-close-modal {
            background-color: var(--terre-cuite);
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            color: white;
            transition: all 0.3s ease;
        }

        .modal-footer .btn-close-modal:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
        }

        /* Style pour les éléments de la liste dans la modale */
        .modal-body ul {
            list-style-type: none;
            padding-left: 0;
        }

        .modal-body li {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .modal-body li:last-child {
            border-bottom: none;
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
        @media (max-width: 992px) {
            body {
                padding-left: 80px;
            }

            body.sidebar-collapsed {
                padding-left: 0;
            }

            .orders-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
                transform: scale(0.8); /* Réduction à 80% */
                transform-origin: top left;
                width: 125%; /* Compense la réduction pour éviter un espace vide */
            }

            .orders-table th, .orders-table td {
                font-size: 0.9rem;
                padding: 10px;
            }

            .btn-details, .btn-change-status {
                padding: 4px 8px;
                font-size: 0.85rem;
            }

            .modal-body img {
                max-width: 40px;
            }
        }

        @media (max-width: 576px) {
            .orders-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .orders-table th, .orders-table td {
                font-size: 0.75rem;
                padding: 8px;
            }

            .btn-details, .btn-change-status {
                padding: 3px 6px;
                font-size: 0.7rem;
            }

            .modal-body img {
                max-width: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="orders-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Gestion des Commandes</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <p class="text-center">Aucune commande trouvée.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo $order['id_order']; ?></td>
                            <td><?php echo htmlspecialchars($order['email']); ?></td>
                            <td><?php echo date('d/m/Y à H:i', strtotime($order['date_creation'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 2); ?> €</td>
                            <td><?php echo $order['status']; ?></td>
                            <td>
                                <button type="button" class="btn-details" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $order['id_order']; ?>">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                <a href="change_status.php?id=<?php echo $order['id_order']; ?>" class="btn-change-status">
                                    <i class="fas fa-edit"></i> Changer Statut
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modales pour les détails des commandes (placées en dehors du tableau) -->
    <?php foreach ($orders as $order): ?>
        <div class="modal fade" id="detailsModal<?php echo $order['id_order']; ?>" tabindex="-1" aria-labelledby="detailsModalLabel<?php echo $order['id_order']; ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel<?php echo $order['id_order']; ?>">Détails de la Commande #<?php echo $order['id_order']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Client :</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p><strong>Date :</strong> <?php echo date('d/m/Y à H:i', strtotime($order['date_creation'])); ?></p>
                        <p><strong>Montant total :</strong> <?php echo number_format($order['total_amount'], 2); ?> €</p>
                        <p><strong>Statut :</strong> <?php echo $order['status']; ?></p>
                        <h6>Produits commandés :</h6>
                        <ul>
                            <?php foreach ($order['items'] as $item): ?>
                                <li>
                                    <img src="<?php echo htmlspecialchars($item['image_product']); ?>" alt="<?php echo htmlspecialchars($item['nom_product']); ?>">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['nom_product']); ?></strong><br>
                                        Quantité : <?php echo $item['quantity']; ?><br>
                                        Prix unitaire : <?php echo number_format($item['unit_price'], 2); ?> €<br>
                                        Sous-total : <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?> €
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-close-modal" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const ordersContainer = document.querySelector('.orders-container');
            ordersContainer.addEventListener('mouseenter', function() {
                this.classList.add('animate__animated', 'animate__pulse');
            });
            ordersContainer.addEventListener('animationend', function() {
                this.classList.remove('animate__animated', 'animate__pulse');
            });

            // Synchroniser l'état de la sidebar avec le padding du body
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('toggleBtn');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-collapsed');
                });
            }
        });
    </script>
</body>
</html>