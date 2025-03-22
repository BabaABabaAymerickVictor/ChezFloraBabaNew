<?php
// templates/admin/upload_image.php
// Script pour gérer l'upload d'images pour TinyMCE

session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Vérifier si un fichier a été envoyé
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Aucun fichier reçu ou erreur de téléchargement']);
    exit();
}

// Configurer le dossier de destination
$upload_dir = '../../public/images_blog/';

// Créer le répertoire s'il n'existe pas
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Obtenir les informations du fichier
$file_name = $_FILES['file']['name'];
$file_tmp = $_FILES['file']['tmp_name'];
$file_size = $_FILES['file']['size'];
$file_error = $_FILES['file']['error'];

// Extraire l'extension du fichier
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Définir les extensions autorisées
$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Vérifier l'extension
if (!in_array($file_ext, $allowed_ext)) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Type de fichier non autorisé. Veuillez choisir une image (jpg, jpeg, png, gif, webp)']);
    exit();
}

// Vérifier la taille du fichier (5 Mo max)
if ($file_size > 5242880) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Le fichier est trop volumineux (max 5 Mo)']);
    exit();
}

// Générer un nom de fichier unique
$new_file_name = uniqid('blog_') . '.' . $file_ext;
$upload_path = $upload_dir . $new_file_name;

// Déplacer le fichier vers le dossier de destination
if (move_uploaded_file($file_tmp, $upload_path)) {
    // Construire l'URL pour TinyMCE
    $site_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $site_url .= $_SERVER['HTTP_HOST'];
    
    // URL relative pour rester compatible entre environnements
    $image_url = '/public/images_blog/' . $new_file_name;
    
    // Répondre avec l'URL de l'image pour TinyMCE
    echo json_encode(['location' => $image_url]);
    exit();
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Erreur lors du téléchargement de l\'image']);
    exit();
}