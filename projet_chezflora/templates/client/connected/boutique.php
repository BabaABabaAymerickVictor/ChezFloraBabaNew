<?php
// templates/client/connected/boutique.php
// Page de la boutique pour les clients connectés

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

// Gérer l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = 1; // Quantité par défaut

    try {
        $database = new Database();
        $conn = $database->connect();

        // Vérifier si le produit existe déjà dans le panier de l'utilisateur
        $query = "SELECT * FROM cart_items WHERE id_user = :id_user AND id_product = :id_product";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_user', $userId);
        $stmt->bindParam(':id_product', $productId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Si le produit est déjà dans le panier, augmenter la quantité
            $query = "UPDATE cart_items SET quantity = quantity + 1 WHERE id_user = :id_user AND id_product = :id_product";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_user', $userId);
            $stmt->bindParam(':id_product', $productId);
            $stmt->execute();
        } else {
            // Sinon, ajouter le produit au panier
            $query = "INSERT INTO cart_items (id_user, id_product, quantity) VALUES (:id_user, :id_product, :quantity)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_user', $userId);
            $stmt->bindParam(':id_product', $productId);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->execute();
        }

        $successMessage = "Produit ajouté au panier avec succès !";
    } catch (Exception $e) {
        $errorMessage = "Erreur lors de l'ajout au panier : " . $e->getMessage();
    }
}

// Récupérer les catégories non supprimées
try {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT id_categorie, nom_categorie FROM categorie WHERE is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération des catégories : " . $e->getMessage();
}

// Récupérer les produits avec leur catégorie
try {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT p.*, c.nom_categorie 
              FROM products p 
              LEFT JOIN categorie c ON p.id_categorie = c.id_categorie 
              WHERE p.is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $errorMessage = "Erreur lors de la récupération des produits : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Boutique</title>
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

        /* Section des produits */
        .products-section {
            padding: 120px 0 60px;
            background-color: var(--blanc-creme);
        }

        .products-section h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: white;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .product-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .card-title {
            color: var(--vert-principal);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .card-price {
            font-size: 1rem;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .card-price .original-price {
            text-decoration: line-through;
            color: #888;
            margin-right: 10px;
        }

        .card-price .promo-price {
            color: #dc3545;
            font-weight: bold;
        }

        .card-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .btn-add-to-cart {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(92, 141, 118, 0.3);
        }

        .btn-add-to-cart:hover {
            background-color: var(--vert-clair);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(92, 141, 118, 0.4);
        }

        .btn-add-to-cart:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(92, 141, 118, 0.3);
        }

        .btn-details {
            background-color: var(--rose-doux);
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(234, 203, 210, 0.3);
        }

        .btn-details:hover {
            background-color: #d8aebc;
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(234, 203, 210, 0.4);
        }

        .btn-details:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(234, 203, 210, 0.3);
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

        /* Style pour le modal */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }

        .modal-header {
            background-color: var(--vert-principal);
            color: white;
            border-bottom: none;
        }

        .modal-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .modal-body {
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .product-modal-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            float: left;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 3px solid var(--vert-clair);
            transition: transform 0.3s ease;
        }

        .product-modal-img:hover {
            transform: scale(1.05);
        }

        .product-details {
            flex: 1;
        }

        .product-details h5 {
            color: var(--vert-principal);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .product-details p {
            margin-bottom: 10px;
            font-size: 1rem;
            color: var(--text-dark);
        }

        /* Style pour la barre de recherche avec icône de loupe */
        .search-container {
            position: relative;
            max-width: 500px;
            margin: 0 auto 20px;
        }

        .search-container .form-control {
            padding-left: 40px; /* Espace pour l'icône */
            border-radius: 30px;
            border: 2px solid var(--vert-clair);
            transition: all 0.3s ease;
        }

        .search-container .form-control:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 10px rgba(92, 141, 118, 0.3);
        }

        .search-container .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--vert-principal);
            font-size: 1.2rem;
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
            .products-section {
                padding: 100px 0 40px;
            }

            .products-section h2 {
                font-size: 2.5rem;
            }

            .product-card img {
                height: 180px;
            }

            .navbar-brand img {
                height: 60px;
            }
            
            .card-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .modal-body {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .product-modal-img {
                float: none;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .products-section h2 {
                font-size: 2rem;
            }

            .product-card img {
                height: 150px;
            }

            .product-modal-img {
                width: 120px;
                height: 120px;
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
                        <a class="nav-link active" href="boutique.php"><i class="fas fa-store"></i> Boutique</a>
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
                        <a class="nav-link" href="panier.php"><i class="fas fa-shopping-cart"></i> Panier</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="accueil.php?logout=true"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section des produits -->
    <section class="products-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Notre Boutique</h2>

            <!-- Barre de recherche avec icône de loupe -->
            <div class="search-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search-bar" class="form-control" placeholder="Rechercher un produit...">
            </div>

            <!-- Filtres de catégorie -->
            <div class="mb-4">
                <label for="category-filter" class="form-label">Filtrer par catégorie :</label>
                <select id="category-filter" class="form-select">
                    <option value="all">Toutes les catégories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id_categorie']); ?>">
                            <?php echo htmlspecialchars($category['nom_categorie']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (isset($successMessage)): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <p class="text-center">Aucun produit disponible pour le moment.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card animate__animated animate__fadeInUp" data-category="<?php echo htmlspecialchars($product['id_categorie']); ?>">
                            <div class="product-image-container">
                                <img src="../../../<?php echo htmlspecialchars($product['image_product']); ?>" alt="<?php echo htmlspecialchars($product['nom_product']); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['nom_product']); ?></h5>
                                <div class="card-price">
                                    <?php if ($product['promotion'] == 1 && $product['pourcentage_reduction'] > 0): ?>
                                        <?php
                                        $prixOriginal = $product['prix'];
                                        $reduction = $product['pourcentage_reduction'];
                                        $prixPromo = $prixOriginal - ($prixOriginal * $reduction / 100);
                                        ?>
                                        <span class="original-price"><?php echo number_format($prixOriginal, 2); ?> €</span>
                                        <span class="promo-price"><?php echo number_format($prixPromo, 2); ?> €</span>
                                    <?php else: ?>
                                        <span><?php echo number_format($product['prix'], 2); ?> €</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-buttons">
                                    <form method="POST" action="boutique.php">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-add-to-cart">
                                            <i class="fas fa-cart-plus me-2"></i>Ajouter au Panier
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-details" data-bs-toggle="modal" data-bs-target="#productDetailsModal" onclick='showProductDetails(<?php echo json_encode($product); ?>)'>
                                        <i class="fas fa-info-circle me-2"></i>Fiche Produit
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal pour les détails du produit -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productDetailsModalLabel">Détails du Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="productDetailsBody">
                    <!-- Les détails du produit seront affichés ici -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
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
            // Effet de scroll pour la navbar
            const navbar = document.getElementById('main-navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Fonction pour afficher les détails du produit
            window.showProductDetails = function(product) {
                const modalBody = document.getElementById('productDetailsBody');
                
                // Créer le HTML pour le modal avec l'image et les informations, y compris la description
                modalBody.innerHTML = `
                    <img src="../../../${product.image_product}" alt="${product.nom_product}" class="product-modal-img">
                    <div class="product-details">
                        <h5>${product.nom_product}</h5>
                        <p><strong>Prix :</strong> ${
                            product.promotion == 1 && product.pourcentage_reduction > 0 ? 
                            `<span class="original-price">${product.prix} €</span> <span class="promo-price">${(product.prix - (product.prix * product.pourcentage_reduction / 100)).toFixed(2)} €</span>` : 
                            `${product.prix} €`
                        }</p>
                        <p><strong>Catégorie :</strong> ${product.nom_categorie || 'Non spécifiée'}</p>
                        <p><strong>Description :</strong> ${product.description || 'Aucune description disponible.'}</p>
                    </div>
                `;
            };

            // Fonction pour filtrer les produits par catégorie
            const categoryFilter = document.getElementById('category-filter');
            categoryFilter.addEventListener('change', function() {
                const selectedCategory = categoryFilter.value;
                const productCards = document.querySelectorAll('.product-card');
                productCards.forEach(card => {
                    const productCategory = card.dataset.category;
                    if (selectedCategory === 'all' || productCategory === selectedCategory) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Fonction pour rechercher des produits
            const searchBar = document.getElementById('search-bar');
            searchBar.addEventListener('input', function() {
                const query = searchBar.value.toLowerCase();
                const productCards = document.querySelectorAll('.product-card');
                productCards.forEach(card => {
                    const productName = card.querySelector('.card-title').textContent.toLowerCase();
                    if (productName.includes(query)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>