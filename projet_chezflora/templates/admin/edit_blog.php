<?php
// templates/admin/edit_blog.php
// Page pour éditer un article de blog

session_start();

// Vérifier si l'utilisateur est connecté et est un admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../login.php');
    exit();
}

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: blog.php');
    exit();
}

$blog_id = intval($_GET['id']);

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

// Récupérer les données de l'article
try {
    $query = "SELECT id, titre, contenu 
              FROM blog 
              WHERE id = :blog_id AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$blog) {
        header('Location: blog.php');
        exit();
    }
} catch (PDOException $e) {
    die('Erreur lors de la récupération de l\'article : ' . $e->getMessage());
}

// Gérer la mise à jour de l'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_blog'])) {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';

    // Validation des champs
    if (empty($titre) || empty($contenu)) {
        $errorMessage = 'Tous les champs sont requis.';
    } else {
        try {
            $query = "UPDATE blog SET titre = :titre, contenu = :contenu WHERE id = :blog_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':titre', $titre);
            $stmt->bindParam(':contenu', $contenu);
            $stmt->bindParam(':blog_id', $blog_id, PDO::PARAM_INT);
            $stmt->execute();

            $successMessage = 'Article mis à jour avec succès.';
            
            // Mettre à jour les données locales
            $blog['titre'] = $titre;
            $blog['contenu'] = $contenu;
        } catch (PDOException $e) {
            $errorMessage = 'Erreur lors de la mise à jour de l\'article : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Modifier l'Article</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- TinyMCE avec la clé API -->
    <script src="https://cdn.tiny.cloud/1/8qhzh5i51jx8g8u08r92xfqwp1s3p8q942pavf5e2zttxify/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
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
        .edit-blog-container {
            max-width: 900px;
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

        .edit-blog-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .edit-blog-container::before {
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

        .edit-blog-container > * {
            position: relative;
            z-index: 1;
        }

        .edit-blog-container h2 {
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

        .btn-update {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
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

            .edit-blog-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .edit-blog-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <!-- Inclure la navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Contenu principal -->
    <div class="edit-blog-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Modifier l'Article</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="edit_blog.php?id=<?php echo $blog_id; ?>">
            <div class="mb-3">
                <label for="titre" class="form-label">Titre de l'article</label>
                <input type="text" class="form-control" id="titre" name="titre" value="<?php echo htmlspecialchars($blog['titre']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="contenu" class="form-label">Contenu</label>
                <textarea class="form-control" id="contenu" name="contenu" rows="10" required><?php echo htmlspecialchars($blog['contenu']); ?></textarea>
            </div>
            <button type="submit" name="update_blog" class="btn btn-update">Mettre à jour</button>
            <a href="blog.php" class="btn btn-back ms-2">Retour</a>
        </form>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script TinyMCE -->
    <script>
        tinymce.init({
            selector: '#contenu',
            plugins: 'image code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image',
            images_upload_url: 'upload_image.php',
            images_upload_base_path: '/public/images_blog/',
            automatic_uploads: true,
            file_picker_types: 'image',
            height: 500,
            content_style: 'body { font-family: Montserrat, sans-serif; color: #363636; }',
            setup: function (editor) {
                editor.on('submit', function () {
                    editor.save(); // Synchronise le contenu de l'éditeur avec le textarea
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Synchroniser l'état de la sidebar avec le padding du body
            const toggleBtn = document.getElementById('toggleBtn');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-collapsed');
                });
            }

            // Forcer la synchronisation de TinyMCE avant la soumission du formulaire
            const form = document.querySelector('form');
            form.addEventListener('submit', function() {
                tinymce.triggerSave(); // Synchronise le contenu de TinyMCE avec le textarea
            });
        });
    </script>
</body>
</html>