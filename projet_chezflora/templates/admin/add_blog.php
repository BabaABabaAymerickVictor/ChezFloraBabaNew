<?php
// templates/admin/add_blog.php
// Page pour ajouter un article de blog

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
$numParagraphs = 0;
$showForm = false;

// Étape 1 : Demander le titre et le nombre de paragraphes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_paragraphs'])) {
    $titre = $_POST['titre'] ?? '';
    $numParagraphs = isset($_POST['num_paragraphs']) ? (int)$_POST['num_paragraphs'] : 0;

    if (empty($titre)) {
        $errorMessage = 'Le titre est requis.';
    } elseif ($numParagraphs < 1) {
        $errorMessage = 'Veuillez spécifier au moins un paragraphe.';
    } else {
        $showForm = true;
        $_SESSION['temp_titre'] = $titre;
        $_SESSION['temp_num_paragraphs'] = $numParagraphs;
    }
}

// Étape 2 : Gérer l'ajout du blog avec les paragraphes et images
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_blog'])) {
    $titre = $_SESSION['temp_titre'] ?? '';
    $numParagraphs = $_SESSION['temp_num_paragraphs'] ?? 0;

    try {
        // Insérer le blog
        $query = "INSERT INTO blog (titre, date_creation, is_deleted) VALUES (:titre, NOW(), 0)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':titre', $titre);
        $stmt->execute();
        $blogId = $conn->lastInsertId();

        // Insérer les paragraphes
        for ($i = 1; $i <= $numParagraphs; $i++) {
            $content = $_POST["paragraph_$i"] ?? '';
            $imagePath = null;

            // Gérer l'image si elle est téléversée
            if (isset($_FILES["image_$i"]) && $_FILES["image_$i"]['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../../public/images_blog/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $imageName = time() . '_' . basename($_FILES["image_$i"]['name']);
                $imagePath = $uploadDir . $imageName;

                if (!move_uploaded_file($_FILES["image_$i"]['tmp_name'], $imagePath)) {
                    throw new Exception("Erreur lors du téléversement de l'image pour le paragraphe $i.");
                }

                $imagePath = '/public/images_blog/' . $imageName;
            }

            // Insérer le paragraphe
            $query = "INSERT INTO blog_paragraph (id_blog, content, image_path, position) 
                      VALUES (:id_blog, :content, :image_path, :position)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_blog', $blogId, PDO::PARAM_INT);
            $stmt->bindParam(':content', $content);
            $stmt->bindParam(':image_path', $imagePath);
            $stmt->bindParam(':position', $i, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Nettoyer les variables de session
        unset($_SESSION['temp_titre']);
        unset($_SESSION['temp_num_paragraphs']);

        header('Location: blog.php');
        exit();
    } catch (Exception $e) {
        $errorMessage = 'Erreur lors de l\'ajout de l\'article : ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Ajouter un Article de Blog</title>
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
        .add-blog-container {
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

        .add-blog-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .add-blog-container::before {
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

        .add-blog-container > * {
            position: relative;
            z-index: 1;
        }

        .add-blog-container h2 {
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

            .add-blog-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }
        }

        @media (max-width: 576px) {
            .add-blog-container h2 {
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
    <div class="add-blog-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Ajouter un Article de Blog</h2>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (!$showForm): ?>
            <!-- Étape 1 : Demander le titre et le nombre de paragraphes -->
            <form method="POST" action="add_blog.php">
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre de l'article</label>
                    <input type="text" class="form-control" id="titre" name="titre" required>
                </div>
                <div class="mb-3">
                    <label for="num_paragraphs" class="form-label">Nombre de paragraphes</label>
                    <input type="number" class="form-control" id="num_paragraphs" name="num_paragraphs" min="1" required>
                </div>
                <button type="submit" name="set_paragraphs" class="btn btn-save">Suivant</button>
                <a href="blog.php" class="btn btn-back ms-2">Retour</a>
            </form>
        <?php else: ?>
            <!-- Étape 2 : Formulaire pour les paragraphes -->
            <form method="POST" action="add_blog.php" enctype="multipart/form-data">
                <h4>Titre : <?php echo htmlspecialchars($_SESSION['temp_titre']); ?></h4>
                <?php for ($i = 1; $i <= $numParagraphs; $i++): ?>
                    <div class="mb-3">
                        <label for="paragraph_<?php echo $i; ?>" class="form-label">Paragraphe <?php echo $i; ?></label>
                        <textarea class="form-control" id="paragraph_<?php echo $i; ?>" name="paragraph_<?php echo $i; ?>" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image_<?php echo $i; ?>" class="form-label">Image (optionnel)</label>
                        <input type="file" class="form-control" id="image_<?php echo $i; ?>" name="image_<?php echo $i; ?>" accept="image/*">
                    </div>
                <?php endfor; ?>
                <button type="submit" name="add_blog" class="btn btn-save">Enregistrer</button>
                <a href="blog.php" class="btn btn-back ms-2">Retour</a>
            </form>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const addBlogContainer = document.querySelector('.add-blog-container');
            addBlogContainer.addEventListener('mouseenter', function() {
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