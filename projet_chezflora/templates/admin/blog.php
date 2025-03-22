<?php
// templates/admin/blog.php
// Page de gestion des articles de blog pour l'administrateur

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

// Gérer l'activation/désactivation d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id']) && isset($_POST['is_deleted'])) {
    $blogId = $_POST['blog_id'];
    $isDeleted = $_POST['is_deleted'] === '1' ? 1 : 0;

    try {
        $query = "UPDATE blog SET is_deleted = :is_deleted WHERE id_blog = :id_blog";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':is_deleted', $isDeleted, PDO::PARAM_INT);
        $stmt->bindParam(':id_blog', $blogId, PDO::PARAM_INT);
        $stmt->execute();

        $successMessage = 'Statut de l\'article mis à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Récupérer la liste des articles
try {
    $query = "SELECT id_blog, titre, date_creation, is_deleted 
              FROM blog 
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
    <title>ChezFlora - Gestion des Articles de Blog</title>
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
        .blog-container {
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

        .blog-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .blog-container::before {
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

        .blog-container > * {
            position: relative;
            z-index: 1;
        }

        .blog-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        /* Bouton pour ajouter un article */
        .add-blog-btn {
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

        .add-blog-btn:hover {
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
            text-align: left;
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
        }

        .blog-table th {
            background-color: var(--vert-clair);
            color: var(--blanc-creme);
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

        /* Ajustement des largeurs des colonnes */
        .blog-table th:nth-child(1), .blog-table td:nth-child(1) { /* ID */
            width: 10%;
        }

        .blog-table th:nth-child(2), .blog-table td:nth-child(2) { /* Titre */
            width: 30%;
        }

        .blog-table th:nth-child(3), .blog-table td:nth-child(3) { /* Date */
            width: 20%;
        }

        .blog-table th:nth-child(4), .blog-table td:nth-child(4) { /* Statut */
            width: 10%;
        }

        .blog-table th:nth-child(5), .blog-table td:nth-child(5) { /* Actions */
            width: 30%;
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

        /* Boutons d'action */
        .edit-btn, .view-btn {
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
            margin-right: 5px;
        }

        .edit-btn {
            background-color: var(--terre-cuite);
        }

        .view-btn {
            background-color: var(--vert-principal);
        }

        .edit-btn:hover, .view-btn:hover {
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

            .blog-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }

            .blog-table th,
            .blog-table td {
                font-size: 0.85rem;
                padding: 10px;
            }
        }

        @media (max-width: 576px) {
            .blog-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .blog-table th,
            .blog-table td {
                font-size: 0.8rem;
                padding: 8px;
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
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="blog-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Gestion des Articles de Blog</h2>
        
        <!-- Bouton pour ajouter un article -->
        <a href="add_blog.php" class="add-blog-btn">
            <i class="fas fa-plus me-2"></i>Ajouter un Article
        </a>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (empty($blogs)): ?>
            <p class="text-center">Aucun article trouvé.</p>
        <?php else: ?>
            <table class="blog-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>TITRE</th>
                        <th>DATE</th>
                        <th>STATUT</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogs as $blog): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($blog['id_blog']); ?></td>
                            <td><?php echo htmlspecialchars($blog['titre']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($blog['date_creation'])); ?></td>
                            <td>
                                <span class="<?php echo $blog['is_deleted'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $blog['is_deleted'] == 0 ? 'Actif' : 'Désactivé'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="blog.php" style="display: inline;">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id_blog']; ?>">
                                    <input type="hidden" name="is_deleted" value="<?php echo $blog['is_deleted'] == 0 ? '1' : '0'; ?>">
                                    <label class="switch">
                                        <input type="checkbox" <?php echo $blog['is_deleted'] == 0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                        <span class="slider"></span>
                                    </label>
                                </form>
                                <a href="update_blog.php?id=<?php echo $blog['id_blog']; ?>" class="edit-btn ms-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="view_blog.php?id=<?php echo $blog['id_blog']; ?>" class="view-btn ms-2">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const blogContainer = document.querySelector('.blog-container');
            blogContainer.addEventListener('mouseenter', function() {
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