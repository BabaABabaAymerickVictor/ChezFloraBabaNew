<?php
// templates/client/connected/blog.php
// Page de la liste des articles de blog pour les clients connectés

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

// Récupérer la liste des articles (seulement ceux non supprimés)
try {
    $query = "SELECT id_blog, titre, date_creation 
              FROM blog 
              WHERE is_deleted = 0 
              ORDER BY date_creation DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des articles : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Blog</title>
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

        /* Section des articles */
        .blog-section {
            padding: 120px 0 60px;
            background-color: var(--blanc-creme);
        }

        .blog-section h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
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

        /* Tableau des articles */
        .blog-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .blog-table th,
        .blog-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
        }

        .blog-table th {
            background-color: var(--vert-clair);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .blog-table td {
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .blog-table tr:hover {
            background-color: rgba(92, 141, 118, 0.05);
        }

        /* Bouton pour consulter */
        .view-btn {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 20px;
            padding: 5px 10px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .view-btn:hover {
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
            .blog-section {
                padding: 100px 0 40px;
            }

            .blog-section h2 {
                font-size: 2.5rem;
            }

            .blog-table th,
            .blog-table td {
                font-size: 0.9rem;
            }

            .navbar-brand img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .blog-section h2 {
                font-size: 2rem;
            }

            .blog-table th,
            .blog-table td {
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

    <!-- Section des articles -->
    <section class="blog-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Notre Blog</h2>

            <?php if (isset($successMessage) && !empty($successMessage)): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($blogs)): ?>
                <p class="text-center">Aucun article disponible pour le moment.</p>
            <?php else: ?>
                <table class="blog-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blogs as $blog): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($blog['id_blog']); ?></td>
                                <td><?php echo htmlspecialchars($blog['titre']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($blog['date_creation'])); ?></td>
                                <td>
                                    <a href="view_blog.php?id=<?php echo $blog['id_blog']; ?>" class="view-btn">
                                        <i class="fas fa-eye me-2"></i>Consulter
                                    </a>
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