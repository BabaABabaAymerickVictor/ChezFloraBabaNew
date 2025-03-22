<?php
// templates/admin/add_product.php
// Page pour ajouter un produit

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

// Gérer l'ajout du produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $nomProduct = $_POST['nom_product'] ?? '';
    $idCategorie = $_POST['id_categorie'] ?? '';
    $prix = $_POST['prix'] ?? '';
    $description = $_POST['description'] ?? '';
    $promotion = $_POST['promotion'] ?? '0';
    $pourcentageReduction = $_POST['pourcentage_reduction'] ?? '0.00';

    // Validation des champs
    if (empty($nomProduct) || empty($idCategorie) || empty($prix) || !isset($_FILES['image_product'])) {
        $_SESSION['error_message'] = 'Tous les champs obligatoires sont requis.';
        header('Location: products.php');
        exit();
    } elseif ($promotion == '1' && (empty($pourcentageReduction) || $pourcentageReduction <= 0)) {
        $_SESSION['error_message'] = 'Veuillez entrer un pourcentage de réduction valide.';
        header('Location: products.php');
        exit();
    }

    // Gestion de l'image
    $imageProduct = $_FILES['image_product'];
    $imageName = time() . '_' . basename($imageProduct['name']);
    $imagePath = '../../public/images/products/' . $imageName;

    // Vérifier le type de fichier
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($imageProduct['type'], $allowedTypes)) {
        $_SESSION['error_message'] = 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.';
        header('Location: products.php');
        exit();
    } elseif ($imageProduct['size'] > 5 * 1024 * 1024) { // Limite de 5 Mo
        $_SESSION['error_message'] = 'L\'image ne doit pas dépasser 5 Mo.';
        header('Location: products.php');
        exit();
    }

    // Déplacer l'image
    if (!move_uploaded_file($imageProduct['tmp_name'], $imagePath)) {
        $_SESSION['error_message'] = 'Erreur lors du téléversement de l\'image.';
        header('Location: products.php');
        exit();
    }

    // Calculer le prix promo si une promotion est activée
    $prixPromo = $promotion == '1' ? $prix - ($prix * $pourcentageReduction / 100) : 0.00;

    // Insérer le produit dans la base de données
    try {
        $query = "INSERT INTO products (nom_product, image_product, id_categorie, prix, description, promotion, pourcentage_reduction, prix_promo) 
                  VALUES (:nom_product, :image_product, :id_categorie, :prix, :description, :promotion, :pourcentage_reduction, :prix_promo)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nom_product', $nomProduct);
        $stmt->bindParam(':image_product', $imagePath);
        $stmt->bindParam(':id_categorie', $idCategorie, PDO::PARAM_INT);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':promotion', $promotion, PDO::PARAM_INT);
        $stmt->bindParam(':pourcentage_reduction', $pourcentageReduction);
        $stmt->bindParam(':prix_promo', $prixPromo);
        $stmt->execute();

        $_SESSION['success_message'] = 'Produit ajouté avec succès.';
        header('Location: products.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Erreur lors de l\'ajout du produit : ' . $e->getMessage();
        header('Location: products.php');
        exit();
    }
} else {
    header('Location: products.php');
    exit();
}
?>