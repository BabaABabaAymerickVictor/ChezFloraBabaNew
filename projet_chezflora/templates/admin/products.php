<?php
// templates/admin/products.php
// Page de gestion des produits pour l'administrateur

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

// Gérer l'activation/désactivation d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['is_deleted'])) {
    $productId = $_POST['product_id'];
    $isDeleted = $_POST['is_deleted'] === '1' ? 1 : 0;

    try {
        $query = "UPDATE products SET is_deleted = :is_deleted WHERE id_product = :id_product";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':is_deleted', $isDeleted, PDO::PARAM_INT);
        $stmt->bindParam(':id_product', $productId, PDO::PARAM_INT);
        $stmt->execute();

        $successMessage = 'Statut du produit mis à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Gérer l'activation/désactivation d'une promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['promotion'])) {
    $productId = $_POST['product_id'];
    $promotion = $_POST['promotion'] === '1' ? 1 : 0;

    try {
        // Si la promotion est désactivée, réinitialiser le pourcentage et le prix promo
        $query = "UPDATE products SET promotion = :promotion, pourcentage_reduction = :pourcentage_reduction, prix_promo = :prix_promo WHERE id_product = :id_product";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':promotion', $promotion, PDO::PARAM_INT);
        $stmt->bindParam(':id_product', $productId, PDO::PARAM_INT);

        if ($promotion == 0) {
            $pourcentageReduction = 0.00;
            $prixPromo = 0.00;
        } else {
            // Récupérer le pourcentage actuel et recalculer le prix promo
            $queryProduct = "SELECT prix, pourcentage_reduction FROM products WHERE id_product = :id_product";
            $stmtProduct = $conn->prepare($queryProduct);
            $stmtProduct->bindParam(':id_product', $productId, PDO::PARAM_INT);
            $stmtProduct->execute();
            $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);

            $pourcentageReduction = $product['pourcentage_reduction'];
            $prixPromo = $product['prix'] - ($product['prix'] * $pourcentageReduction / 100);
        }

        $stmt->bindParam(':pourcentage_reduction', $pourcentageReduction);
        $stmt->bindParam(':prix_promo', $prixPromo);
        $stmt->execute();

        $successMessage = 'Promotion mise à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Récupérer la liste des produits avec leurs catégories
try {
    $query = "SELECT p.id_product, p.nom_product, p.image_product, p.prix, p.description, p.is_deleted, p.promotion, p.pourcentage_reduction, p.prix_promo, c.nom_categorie 
              FROM products p 
              JOIN categorie c ON p.id_categorie = c.id_categorie 
              ORDER BY p.id_product";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des produits : ' . $e->getMessage();
}

// Récupérer la liste des catégories pour la modale d'ajout
try {
    $query = "SELECT id_categorie, nom_categorie, is_deleted FROM categorie ORDER BY nom_categorie";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des catégories : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Gestion des Produits</title>
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
        .products-container {
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

        .products-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .products-container::before {
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

        .products-container > * {
            position: relative;
            z-index: 1;
        }

        .products-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        /* Bouton pour ajouter un produit */
        .add-product-btn {
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

        .add-product-btn:hover {
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

        /* Tableau des produits */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
        }

        .products-table th {
            background-color: var(--vert-clair);
            color: var(--blanc-creme);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .products-table td {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .products-table tr:hover {
            background-color: rgba(92, 141, 118, 0.05);
        }

        /* Image du produit */
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        /* Description */
        .product-description {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        /* Prix en promotion */
        .prix-initial {
            text-decoration: line-through;
            color: #999;
            margin-right: 10px;
        }

        .prix-promo {
            color: #dc3545;
            font-weight: 600;
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

        /* Bouton Modifier */
        .edit-btn {
            background-color: var(--terre-cuite);
            border: none;
            border-radius: 20px;
            padding: 5px 15px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .edit-btn:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
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

        .form-control:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
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
        @media (max-width: 992px) {
            body {
                padding-left: 80px;
            }

            body.sidebar-collapsed {
                padding-left: 0;
            }

            .products-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
                transform: scale(0.8); /* Réduction à 80% */
                transform-origin: top left;
                width: 125%; /* Compense la réduction pour éviter un espace vide */
            }

            .products-table th,
            .products-table td {
                font-size: 0.85rem;
                padding: 10px;
            }

            .product-image {
                width: 40px;
                height: 40px;
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

            .edit-btn {
                padding: 4px 10px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .products-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .products-table th,
            .products-table td {
                font-size: 0.75rem;
                padding: 8px;
            }

            .product-image {
                width: 30px;
                height: 30px;
            }

            .switch {
                width: 36px;
                height: 18px;
            }

            .slider:before {
                height: 14px;
                width: 14px;
                bottom: 2px;
                left: 2px;
            }

            input:checked + .slider:before {
                transform: translateX(18px);
            }

            .edit-btn {
                padding: 3px 8px;
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="products-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Gestion des Produits</h2>
        
        <!-- Bouton pour ajouter un produit -->
        <button type="button" class="add-product-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-2"></i>Ajouter un Produit
        </button>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (empty($products)): ?>
            <p class="text-center">Aucun produit trouvé.</p>
        <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Promotion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['id_product']); ?></td>
                            <td><img src="<?php echo htmlspecialchars($product['image_product']); ?>" alt="<?php echo htmlspecialchars($product['nom_product']); ?>" class="product-image"></td>
                            <td><?php echo htmlspecialchars($product['nom_product']); ?></td>
                            <td><?php echo htmlspecialchars($product['nom_categorie']); ?></td>
                            <td>
                                <?php if ($product['promotion'] == 1): ?>
                                    <span class="prix-initial"><?php echo htmlspecialchars($product['prix']); ?> €</span>
                                    <span class="prix-promo"><?php echo htmlspecialchars($product['prix_promo']); ?> €</span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($product['prix']); ?> €
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="<?php echo $product['is_deleted'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $product['is_deleted'] == 0 ? 'Actif' : 'Désactivé'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="products.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                    <input type="hidden" name="promotion" value="<?php echo $product['promotion'] == 0 ? '1' : '0'; ?>">
                                    <label class="switch">
                                        <input type="checkbox" <?php echo $product['promotion'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="slider"></span>
                                    </label>
                                </form>
                                <?php if ($product['promotion'] == 1): ?>
                                    <br>(<?php echo htmlspecialchars($product['pourcentage_reduction']); ?>%)
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" action="products.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id_product']; ?>">
                                    <input type="hidden" name="is_deleted" value="<?php echo $product['is_deleted'] == 0 ? '1' : '0'; ?>">
                                    <label class="switch">
                                        <input type="checkbox" <?php echo $product['is_deleted'] == 0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="slider"></span>
                                    </label>
                                </form>
                                <a href="update_product.php?id=<?php echo $product['id_product']; ?>" class="edit-btn ms-2">
                                    <i class="fas fa-edit me-1"></i>Modifier
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Modale pour ajouter un produit -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Ajouter un Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="add_product.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="image_product" class="form-label">Image du produit</label>
                            <input type="file" class="form-control" id="image_product" name="image_product" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label for="nom_product" class="form-label">Nom du produit</label>
                            <input type="text" class="form-control" id="nom_product" name="nom_product" required>
                        </div>
                        <div class="mb-3">
                            <label for="id_categorie" class="form-label">Catégorie</label>
                            <select class="form-control" id="id_categorie" name="id_categorie" required>
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach ($categories as $categorie): ?>
                                    <option value="<?php echo $categorie['id_categorie']; ?>" 
                                            <?php echo $categorie['is_deleted'] == 1 ? 'disabled' : ''; ?>>
                                        <?php echo htmlspecialchars($categorie['nom_categorie']); ?>
                                        <?php echo $categorie['is_deleted'] == 1 ? '(Désactivée)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="prix" class="form-label">Prix (€)</label>
                            <input type="number" step="0.01" class="form-control" id="prix" name="prix" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="promotion" class="form-label">Activer la promotion</label>
                            <select class="form-control" id="promotion" name="promotion">
                                <option value="0">Non</option>
                                <option value="1">Oui</option>
                            </select>
                        </div>
                        <div class="mb-3" id="pourcentage_reduction_field" style="display: none;">
                            <label for="pourcentage_reduction" class="form-label">Pourcentage de réduction (%)</label>
                            <input type="number" step="0.01" class="form-control" id="pourcentage_reduction" name="pourcentage_reduction" value="0.00">
                        </div>
                        <button type="submit" name="add_product" class="btn btn-save">Enregistrer</button>
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
            const productsContainer = document.querySelector('.products-container');
            productsContainer.addEventListener('mouseenter', function() {
                this.classList.add('animate__animated', 'animate__pulse');
            });

            // Synchroniser l'état de la sidebar avec le padding du body
            const sidebar = document.getElementById('adminSidebar');
            const toggleBtn = document.getElementById('toggleBtn');

            toggleBtn.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-collapsed');
            });

            // Afficher/masquer le champ de pourcentage de réduction
            const promotionSelect = document.getElementById('promotion');
            const pourcentageField = document.getElementById('pourcentage_reduction_field');

            promotionSelect.addEventListener('change', function() {
                if (this.value == '1') {
                    pourcentageField.style.display = 'block';
                } else {
                    pourcentageField.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>