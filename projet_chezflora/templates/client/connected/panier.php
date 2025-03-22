<?php
// templates/client/connected/panier.php
// Page du panier pour les clients connectés

session_start();

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'client') {
    header('Location: ../../../login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];

// Inclure la classe de connexion à la base de données
require_once '../../../classes/db_connect.php';

// Gérer le paiement (enregistrement de la commande)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    try {
        $database = new Database();
        $conn = $database->connect();

        // Récupérer les articles du panier
        $query = "SELECT ci.*, p.nom_product, p.prix, p.promotion, p.pourcentage_reduction 
                  FROM cart_items ci 
                  JOIN products p ON ci.id_product = p.id_product 
                  WHERE ci.id_user = :id_user";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_user', $userId);
        $stmt->execute();
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cartItems)) {
            $errorMessage = "Votre panier est vide.";
        } else {
            // Calculer le montant total
            $totalAmount = 0;
            foreach ($cartItems as $item) {
                $prix = $item['prix'];
                if ($item['promotion'] == 1 && $item['pourcentage_reduction'] > 0) {
                    $prix = $prix - ($prix * $item['pourcentage_reduction'] / 100);
                }
                $totalAmount += $prix * $item['quantity'];
            }

            // Créer une nouvelle commande
            $query = "INSERT INTO orders (id_user, total_amount, status) VALUES (:id_user, :total_amount, 'En attente')";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_user', $userId);
            $stmt->bindParam(':total_amount', $totalAmount);
            $stmt->execute();

            // Récupérer l'ID de la commande créée
            $orderId = $conn->lastInsertId();

            // Ajouter les articles à order_items
            foreach ($cartItems as $item) {
                $prix = $item['prix'];
                if ($item['promotion'] == 1 && $item['pourcentage_reduction'] > 0) {
                    $prix = $prix - ($prix * $item['pourcentage_reduction'] / 100);
                }
                $query = "INSERT INTO order_items (id_order, id_product, quantity, unit_price) 
                          VALUES (:id_order, :id_product, :quantity, :unit_price)";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id_order', $orderId);
                $stmt->bindParam(':id_product', $item['id_product']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':unit_price', $prix);
                $stmt->execute();
            }

            // Vider le panier
            $query = "DELETE FROM cart_items WHERE id_user = :id_user";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_user', $userId);
            $stmt->execute();

            $successMessage = "Commande enregistrée avec succès ! Vous pouvez suivre son statut ci-dessous.";
        }
    } catch (Exception $e) {
        $errorMessage = "Erreur lors de l'enregistrement de la commande : " . $e->getMessage();
    }
}

// Gérer l'annulation d'une commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $orderId = $_POST['order_id'];

    try {
        $database = new Database();
        $conn = $database->connect();

        // Supprimer les éléments de la commande dans order_items
        $query = "DELETE FROM order_items WHERE id_order = :id_order";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_order', $orderId);
        $stmt->execute();

        // Supprimer la commande dans orders
        $query = "DELETE FROM orders WHERE id_order = :id_order AND id_user = :id_user AND status = 'En attente'";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_order', $orderId);
        $stmt->bindParam(':id_user', $userId);
        $stmt->execute();

        $successMessage = "Commande annulée avec succès !";
    } catch (Exception $e) {
        $errorMessage = "Erreur lors de l'annulation de la commande : " . $e->getMessage();
    }
}

// Récupérer les articles du panier
try {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT ci.*, p.nom_product, p.prix, p.promotion, p.pourcentage_reduction 
              FROM cart_items ci 
              JOIN products p ON ci.id_product = p.id_product 
              WHERE ci.id_user = :id_user";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_user', $userId);
    $stmt->execute();
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total
    $totalAmount = 0;
    foreach ($cartItems as $item) {
        $prix = $item['prix'];
        if ($item['promotion'] == 1 && $item['pourcentage_reduction'] > 0) {
            $prix = $prix - ($prix * $item['pourcentage_reduction'] / 100);
        }
        $totalAmount += $prix * $item['quantity'];
    }
} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération du panier : " . $e->getMessage();
}

// Récupérer les commandes en cours
try {
    $query = "SELECT o.*, GROUP_CONCAT(p.nom_product SEPARATOR ', ') as products 
              FROM orders o 
              JOIN order_items oi ON o.id_order = oi.id_order 
              JOIN products p ON oi.id_product = p.id_product 
              WHERE o.id_user = :id_user AND o.status = 'En attente' AND o.is_deleted = 0 
              GROUP BY o.id_order";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_user', $userId);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération des commandes : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Mon Panier</title>
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
            transition: background-color 0.5s ease;
        }

        /* Barre de navigation */
        .navbar {
            background-color: var(--blanc-creme);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 0;
            height: 80px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            height: 60px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            height: 80px;
            padding: 0;
        }

        .navbar-brand img {
            height: 140px;
            transition: height 0.3s ease;
            margin-left: 15px;
        }

        .navbar.scrolled .navbar-brand img {
            height: 50px;
        }

        .navbar-nav .nav-link {
            color: var(--vert-principal) !important;
            font-weight: 500;
            margin: 0 8px;
            padding: 8px 12px;
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .navbar-nav .nav-link:hover {
            color: var(--blanc-creme) !important;
            background-color: var(--vert-principal);
            transform: translateY(-2px);
        }

        .navbar-nav .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background-color: var(--vert-principal);
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover::after {
            width: 80%;
            left: 10%;
        }

        .navbar-nav .nav-link i {
            margin-right: 5px;
            transition: transform 0.3s ease;
        }

        .navbar-nav .nav-link:hover i {
            transform: translateY(-2px);
        }

        /* Section du panier */
        .cart-section {
            padding: 120px 0 60px;
            background-color: var(--blanc-creme);
        }

        .cart-section h2, .orders-section h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .cart-table th, .cart-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .cart-table th {
            background-color: var(--vert-clair);
            color: white;
        }

        .cart-table td {
            background-color: white;
        }

        .total-amount {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .btn-pay {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-size: 1.1rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(92, 141, 118, 0.3);
            display: block;
            margin: 0 auto;
        }

        .btn-pay:hover {
            background-color: var(--vert-clair);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(92, 141, 118, 0.4);
        }

        .btn-pay:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(92, 141, 118, 0.3);
        }

        /* Section des commandes en cours */
        .orders-section {
            padding: 60px 0;
            background-color: #f5f5f5;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th, .orders-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .orders-table th {
            background-color: var(--vert-clair);
            color: white;
        }

        .orders-table td {
            background-color: white;
        }

        .btn-cancel {
            background-color: #dc3545;
            border: none;
            border-radius: 20px;
            padding: 8px 15px;
            font-size: 0.9rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-cancel:hover {
            background-color: #c82333;
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(220, 53, 69, 0.4);
        }

        .btn-cancel:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(220, 53, 69, 0.3);
        }

        /* Messages de succès et d'erreur */
        .success-message {
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1rem;
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

        /* Responsive Design */
        @media (max-width: 992px) {
            .navbar-collapse {
                background-color: var(--blanc-creme);
                padding: 20px;
                border-radius: 0 0 20px 20px;
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
                position: absolute;
                top: 80px;
                left: 0;
                right: 0;
            }

            .navbar-nav {
                text-align: center;
            }

            .navbar-nav .nav-link {
                margin: 8px 0;
                display: inline-block;
            }
        }

        @media (max-width: 768px) {
            .cart-section, .orders-section {
                padding: 100px 0 40px;
            }

            .cart-section h2, .orders-section h2 {
                font-size: 2.5rem;
            }

            .cart-table, .orders-table {
                font-size: 0.9rem;
            }

            .navbar-brand img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .cart-section h2, .orders-section h2 {
                font-size: 2rem;
            }

            .cart-table, .orders-table {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" id="main-navbar">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="accueil.php">
                <img src="../../../public/images/logo-removebg-preview.png" alt="ChezFlora Logo" class="animate__animated animate__fadeIn">
            </a>
            <!-- Bouton pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Liens de navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="accueil.php"><i class="fas fa-home"></i> Tableau de Bord</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="boutique.php"><i class="fas fa-store"></i> Boutique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="blog.php"><i class="fas fa-blog"></i> Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php"><i class="fas fa-leaf"></i> Nos Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="compte.php"><i class="fas fa-user"></i> Mon Compte</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="panier.php"><i class="fas fa-shopping-cart"></i> Panier</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="accueil.php?logout=true"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section du panier -->
    <section class="cart-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Mon Panier</h2>

            <?php if (isset($successMessage)): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($cartItems)): ?>
                <p class="text-center">Votre panier est vide.</p>
            <?php else: ?>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix Unitaire</th>
                            <th>Quantité</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            $prix = $item['prix'];
                            if ($item['promotion'] == 1 && $item['pourcentage_reduction'] > 0) {
                                $prix = $prix - ($prix * $item['pourcentage_reduction'] / 100);
                            }
                            $totalItem = $prix * $item['quantity'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nom_product']); ?></td>
                                <td><?php echo number_format($prix, 2); ?> €</td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($totalItem, 2); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="total-amount">
                    Total : <?php echo number_format($totalAmount, 2); ?> €
                </div>

                <form method="POST" action="panier.php">
                    <button type="submit" name="pay" class="btn btn-pay">
                        <i class="fas fa-credit-card me-2"></i>Payer
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section des commandes en cours -->
    <section class="orders-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Mes Commandes en Cours</h2>

            <?php if (empty($orders)): ?>
                <p class="text-center">Aucune commande en cours.</p>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>ID Commande</th>
                            <th>Date</th>
                            <th>Produits</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id_order']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['date_creation'])); ?></td>
                                <td><?php echo htmlspecialchars($order['products']); ?></td>
                                <td><?php echo number_format($order['total_amount'], 2); ?> €</td>
                                <td><?php echo $order['status']; ?></td>
                                <td>
                                    <form method="POST" action="panier.php" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?');">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id_order']; ?>">
                                        <button type="submit" name="cancel_order" class="btn btn-cancel">
                                            <i class="fas fa-times me-2"></i>Annuler
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Effet de scroll pour la navbar
            const navbar = document.getElementById('main-navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>
</body>
</html>