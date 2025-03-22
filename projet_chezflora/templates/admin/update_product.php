<?php
// templates/admin/update_product.php
// Page pour modifier un produit

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

// Vérifier si un ID de produit est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$productId = $_GET['id'];

// Récupérer les informations du produit
try {
    $query = "SELECT id_product, nom_product, image_product, id_categorie, prix, description, promotion, pourcentage_reduction, prix_promo 
              FROM products WHERE id_product = :id_product";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_product', $productId, PDO::PARAM_INT);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: products.php');
        exit();
    }
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération du produit : ' . $e->getMessage();
}

// Gérer la mise à jour du produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $nomProduct = $_POST['nom_product'] ?? '';
    $idCategorie = $_POST['id_categorie'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $description = $_POST['description'] ?? '';
    $promotion = $_POST['promotion'] ?? '0';
    $pourcentageReduction = $_POST['pourcentage_reduction'] ?? '0.00';

    // Validation des champs
    if (empty($nomProduct) || empty($idCategorie) || empty($prix)) {
        $errorMessage = 'Tous les champs obligatoires sont requis.';
    } elseif ($promotion == '1' && (empty($pourcentageReduction) || $pourcentageReduction <= 0)) {
        $errorMessage = 'Veuillez entrer un pourcentage de réduction valide.';
    } else {
        $imagePath = $product['image_product'];

        // Si une nouvelle image est téléversée
        if (isset($_FILES['image_product']) && $_FILES['image_product']['size'] > 0) {
            $imageProduct = $_FILES['image_product'];
            $imageName = time() . '_' . basename($imageProduct['name']);
            $imagePath = '../../public/images/products/' . $imageName;

            // Vérifier le type de fichier
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($imageProduct['type'], $allowedTypes)) {
                $errorMessage = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
            } elseif ($imageProduct['size'] > 5 * 1024 * 1024) { // Limite de 5 Mo
                $errorMessage = 'L\'image ne doit pas dépasser 5 Mo.';
            } else {
                // Supprimer l'ancienne image
                if (file_exists($product['image_product'])) {
                    unlink($product['image_product']);
                }

                // Déplacer la nouvelle image
                if (!move_uploaded_file($imageProduct['tmp_name'], $imagePath)) {
                    $errorMessage = 'Erreur lors du téléversement de l\'image.';
                    $imagePath = $product['image_product'];
                }
            }
        }

        if (empty($errorMessage)) {
            try {
                // Calculer le prix promo si une promotion est activée
                $prixPromo = $promotion == '1' ? $prix - ($prix * $pourcentageReduction / 100) : 0.00;

                $query = "UPDATE products SET nom_product = :nom_product, image_product = :image_product, 
                          id_categorie = :id_categorie, prix = :prix, description = :description, 
                          promotion = :promotion, pourcentage_reduction = :pourcentage_reduction, 
                          prix_promo = :prix_promo 
                          WHERE id_product = :id_product";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':nom_product', $nomProduct);
                $stmt->bindParam(':image_product', $imagePath);
                $stmt->bindParam(':id_categorie', $idCategorie, PDO::PARAM_INT);
                $stmt->bindParam(':prix', $prix);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':promotion', $promotion, PDO::PARAM_INT);
                $stmt->bindParam(':pourcentage_reduction', $pourcentageReduction);
                $stmt->bindParam(':prix_promo', $prixPromo);
                $stmt->bindParam(':id_product', $productId, PDO::PARAM_INT);
                $stmt->execute();

                header('Location: products.php');
                exit();
            } catch (PDOException $e) {
                $errorMessage = 'Erreur lors de la mise à jour du produit : ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Modifier un Produit</title>
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
        .update-product-container {
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

        .update-product-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .update-product-container::before {
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

        .update-product-container > * {
            position: relative;
            z-index: 1;
        }

        .update-product-container h2 {
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

        /* Image actuelle */
        .current-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        /* Formulaire */
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

        .btn-back {
            background-color: var(--terre-cuite);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
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

            .update-product-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .update-product-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .current-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="update-product-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Modifier le Produit</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="update_product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="image_product" class="form-label">Image actuelle</label><br>
                <img src="<?php echo htmlspecialchars($product['image_product']); ?>" alt="<?php echo htmlspecialchars($product['nom_product']); ?>" class="current-image">
            </div>
            <div class="mb-3">
                <label for="image_product" class="form-label">Nouvelle image (facultatif)</label>
                <input type="file" class="form-control" id="image_product" name="image_product" accept="image/*">
            </div>
            <div class="mb-3">
                <label for="nom_product" class="form-label">Nom du produit</label>
                <input type="text" class="form-control" id="nom_product" name="nom_product" value="<?php echo htmlspecialchars($product['nom_product']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="id_categorie" class="form-label">Catégorie</label>
                <select class="form-control" id="id_categorie" name="id_categorie" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php
                    $query = "SELECT id_categorie, nom_categorie, is_deleted FROM categorie ORDER BY nom_categorie";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categories as $categorie): ?>
                        <option value="<?php echo $categorie['id_categorie']; ?>" 
                                <?php echo $categorie['is_deleted'] == 1 ? 'disabled' : ''; ?>
                                <?php echo $categorie['id_categorie'] == $product['id_categorie'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categorie['nom_categorie']); ?>
                            <?php echo $categorie['is_deleted'] == 1 ? '(Désactivée)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="prix" class="form-label">Prix (€)</label>
                <input type="number" step="0.01" class="form-control" id="prix" name="prix" value="<?php echo htmlspecialchars($product['prix']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="promotion" class="form-label">Activer la promotion</label>
                <select class="form-control" id="promotion" name="promotion">
                    <option value="0" <?php echo $product['promotion'] == 0 ? 'selected' : ''; ?>>Non</option>
                    <option value="1" <?php echo $product['promotion'] == 1 ? 'selected' : ''; ?>>Oui</option>
                </select>
            </div>
            <div class="mb-3" id="pourcentage_reduction_field" style="display: <?php echo $product['promotion'] == 1 ? 'block' : 'none'; ?>;">
                <label for="pourcentage_reduction" class="form-label">Pourcentage de réduction (%)</label>
                <input type="number" step="0.01" class="form-control" id="pourcentage_reduction" name="pourcentage_reduction" value="<?php echo htmlspecialchars($product['pourcentage_reduction']); ?>">
            </div>
            <button type="submit" name="update_product" class="btn btn-save">Mettre à jour</button>
            <a href="products.php" class="btn btn-back ms-2">Retour</a>
        </form>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const updateProductContainer = document.querySelector('.update-product-container');
            updateProductContainer.addEventListener('mouseenter', function() {
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