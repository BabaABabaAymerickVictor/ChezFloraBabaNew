<?php
// templates/client/connected/view_blog.php
// Page pour consulter un article de blog (et permettre aux clients de commenter)

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

// Connexion à la base de données
$database = new Database();
$conn = $database->connect();

if (!$conn) {
    die("Erreur de connexion à la base de données.");
}

$successMessage = '';
$errorMessage = '';

// Vérifier si un ID d'article est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: blog.php');
    exit();
}

$blogId = $_GET['id'];

// Gérer l'ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $contenuCommentaire = $_POST['contenu_commentaire'] ?? '';

    if (empty($contenuCommentaire)) {
        $errorMessage = 'Le commentaire ne peut pas être vide.';
    } else {
        try {
            $query = "INSERT INTO commentaires (id_blog, id_user, contenu_commentaire, date_creation, is_deleted) 
                      VALUES (:id_blog, :id_user, :contenu_commentaire, NOW(), 0)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_blog', $blogId, PDO::PARAM_INT);
            $stmt->bindParam(':id_user', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':contenu_commentaire', $contenuCommentaire);
            $stmt->execute();

            $successMessage = 'Commentaire ajouté avec succès.';
        } catch (PDOException $e) {
            $errorMessage = 'Erreur lors de l\'ajout du commentaire : ' . $e->getMessage();
        }
    }
}

// Récupérer les informations du blog
try {
    $query = "SELECT id_blog, titre, date_creation 
              FROM blog 
              WHERE id_blog = :id_blog AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_blog', $blogId, PDO::PARAM_INT);
    $stmt->execute();
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$blog) {
        header('Location: blog.php');
        exit();
    }

    // Récupérer les paragraphes
    $query = "SELECT content, image_path 
              FROM blog_paragraph 
              WHERE id_blog = :id_blog 
              ORDER BY position";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_blog', $blogId, PDO::PARAM_INT);
    $stmt->execute();
    $paragraphs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les commentaires (seulement ceux actifs) et leurs réponses
    $query = "SELECT c.id_commentaire, c.contenu_commentaire, c.date_creation, u.email 
              FROM commentaires c 
              JOIN user u ON c.id_user = u.id_user 
              WHERE c.id_blog = :id_blog AND c.is_deleted = 0 
              ORDER BY c.date_creation DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_blog', $blogId, PDO::PARAM_INT);
    $stmt->execute();
    $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque commentaire, récupérer les réponses associées
    foreach ($commentaires as &$commentaire) {
        $query = "SELECT cr.contenu_reponse, cr.date_creation, u.email AS admin_email
                  FROM commentaire_reponses cr
                  JOIN user u ON cr.id_admin = u.id_user
                  WHERE cr.id_commentaire = :id_commentaire AND cr.is_deleted = 0
                  ORDER BY cr.date_creation DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_commentaire', $commentaire['id_commentaire'], PDO::PARAM_INT);
        $stmt->execute();
        $commentaire['reponses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération de l\'article : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Consulter l'Article</title>
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

        /* Section de l'article */
        .view-blog-section {
            padding: 120px 0 60px;
            background-color: var(--blanc-creme);
        }

        .view-blog-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 35px;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(92, 141, 118, 0.15);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1s ease-out;
        }

        .view-blog-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        .view-blog-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        .blog-meta {
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 30px;
        }

        /* Contenu de l'article */
        .blog-content {
            font-size: 1rem;
            line-height: 1.8;
            color: var(--text-dark);
        }

        .blog-content p {
            margin-bottom: 20px;
        }

        .blog-content img {
            display: block;
            max-width: 100%;
            height: auto;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        /* Formulaire de commentaire */
        .comment-form {
            margin-top: 40px;
        }

        .comment-form textarea {
            border: 2px solid var(--vert-clair);
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .comment-form textarea:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 10px rgba(92, 141, 118, 0.3);
        }

        .btn-comment {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-comment:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
        }

        /* Liste des commentaires */
        .comments-section {
            margin-top: 40px;
        }

        .comments-section h3 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .comment {
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
            padding: 15px 0;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .comment-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Style pour les réponses */
        .comment-reponses {
            margin-top: 10px;
            padding-left: 20px;
            border-left: 2px solid var(--vert-clair);
        }

        .reponse {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .reponse p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .reponse-meta {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Bouton Retour */
        .btn-back {
            background-color: var(--terre-cuite);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
            display: inline-block;
            margin-top: 20px;
        }

        .btn-back:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
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
            .view-blog-section {
                padding: 100px 0 40px;
            }

            .view-blog-container {
                max-width: 90%;
                padding: 25px;
            }

            .view-blog-container h2 {
                font-size: 2.5rem;
            }

            .navbar-brand img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .view-blog-container h2 {
                font-size: 2rem;
            }

            .blog-content {
                font-size: 0.9rem;
            }

            .comments-section h3 {
                font-size: 1.5rem;
            }

            .comment p {
                font-size: 0.9rem;
            }

            .comment-meta {
                font-size: 0.8rem;
            }

            .reponse p {
                font-size: 0.85rem;
            }

            .reponse-meta {
                font-size: 0.75rem;
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
                        <a class="nav-link active" href="blog.php"><i class="fas fa-blog"></i> Blog</a>
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

    <!-- Section de l'article -->
    <section class="view-blog-section">
        <div class="view-blog-container animate__animated animate__fadeInUp">
            <h2 class="animate__animated animate__fadeIn"><?php echo htmlspecialchars($blog['titre']); ?></h2>
            
            <div class="blog-meta">
                Publié le <?php echo date('d/m/Y à H:i', strtotime($blog['date_creation'])); ?>
            </div>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            
            <div class="blog-content">
                <?php foreach ($paragraphs as $paragraph): ?>
                    <p><?php echo nl2br(htmlspecialchars($paragraph['content'])); ?></p>
                    <?php if ($paragraph['image_path']): ?>
                        <img src="../../../<?php echo htmlspecialchars($paragraph['image_path']); ?>" alt="Image du paragraphe">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Section des commentaires -->
            <div class="comments-section">
                <h3>Commentaires</h3>
                <?php if (empty($commentaires)): ?>
                    <p class="text-muted">Aucun commentaire pour cet article.</p>
                <?php else: ?>
                    <?php foreach ($commentaires as $comment): ?>
                        <div class="comment">
                            <p><?php echo htmlspecialchars($comment['contenu_commentaire']); ?></p>
                            <div class="comment-meta">
                                Par <strong><?php echo htmlspecialchars($comment['email']); ?></strong> 
                                le <?php echo date('d/m/Y à H:i', strtotime($comment['date_creation'])); ?>
                            </div>

                            <!-- Afficher les réponses de l'administrateur -->
                            <?php if (!empty($comment['reponses'])): ?>
                                <div class="comment-reponses">
                                    <?php foreach ($comment['reponses'] as $reponse): ?>
                                        <div class="reponse">
                                            <p><?php echo htmlspecialchars($reponse['contenu_reponse']); ?></p>
                                            <div class="reponse-meta">
                                                Réponse par <strong><?php echo htmlspecialchars($reponse['admin_email']); ?></strong>
                                                le <?php echo date('d/m/Y à H:i', strtotime($reponse['date_creation'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Formulaire pour ajouter un commentaire -->
            <div class="comment-form">
                <h3>Laisser un commentaire</h3>
                <form method="POST" action="view_blog.php?id=<?php echo $blogId; ?>">
                    <div class="mb-3">
                        <textarea class="form-control" name="contenu_commentaire" rows="3" required placeholder="Votre commentaire..."></textarea>
                    </div>
                    <button type="submit" name="add_comment" class="btn btn-comment">Envoyer</button>
                </form>
            </div>

            <a href="blog.php" class="btn btn-back">Retour</a>
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