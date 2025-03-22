<?php
// templates/admin/comments.php
// Page de gestion des commentaires pour l'administrateur

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

// Gérer la désactivation d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id']) && isset($_POST['is_deleted'])) {
    $commentId = $_POST['comment_id'];
    $isDeleted = $_POST['is_deleted'] === '1' ? 1 : 0;

    try {
        $query = "UPDATE commentaires SET is_deleted = :is_deleted WHERE id_commentaire = :id_commentaire";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':is_deleted', $isDeleted, PDO::PARAM_INT);
        $stmt->bindParam(':id_commentaire', $commentId, PDO::PARAM_INT);
        $stmt->execute();

        $successMessage = 'Statut du commentaire mis à jour avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Gérer la désactivation d'un compte utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['disable_account'])) {
    $userId = $_POST['user_id'];

    try {
        $query = "UPDATE user SET is_deleted = 1 WHERE id_user = :id_user";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_user', $userId, PDO::PARAM_INT);
        $stmt->execute();

        $successMessage = 'Compte utilisateur désactivé avec succès.';
    } catch (PDOException $e) {
        $errorMessage = 'Erreur : ' . $e->getMessage();
    }
}

// Gérer l'ajout d'une réponse à un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_response']) && isset($_POST['comment_id']) && isset($_POST['contenu_reponse'])) {
    $commentId = $_POST['comment_id'];
    $contenuReponse = $_POST['contenu_reponse'];
    $adminId = $_SESSION['user_id'];

    if (empty($contenuReponse)) {
        $errorMessage = 'La réponse ne peut pas être vide.';
    } else {
        try {
            $query = "INSERT INTO commentaire_reponses (id_commentaire, contenu_reponse, id_admin, date_creation, is_deleted) 
                      VALUES (:id_commentaire, :contenu_reponse, :id_admin, NOW(), 0)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_commentaire', $commentId, PDO::PARAM_INT);
            $stmt->bindParam(':contenu_reponse', $contenuReponse);
            $stmt->bindParam(':id_admin', $adminId, PDO::PARAM_INT);
            $stmt->execute();

            $successMessage = 'Réponse ajoutée avec succès.';
        } catch (PDOException $e) {
            $errorMessage = 'Erreur lors de l\'ajout de la réponse : ' . $e->getMessage();
        }
    }
}

// Récupérer tous les blogs avec leurs commentaires et réponses
try {
    $query = "SELECT b.id_blog, b.titre, 
                     (SELECT content FROM blog_paragraph WHERE id_blog = b.id_blog ORDER BY position LIMIT 1) AS preview_content
              FROM blog b
              WHERE b.is_deleted = 0
              ORDER BY b.date_creation DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Pour chaque blog, récupérer les commentaires associés et leurs réponses
    foreach ($blogs as &$blog) {
        $query = "SELECT c.id_commentaire, c.contenu_commentaire, c.date_creation, c.is_deleted, 
                         u.id_user, u.email
                  FROM commentaires c
                  JOIN user u ON c.id_user = u.id_user
                  WHERE c.id_blog = :id_blog
                  ORDER BY c.date_creation DESC";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_blog', $blog['id_blog'], PDO::PARAM_INT);
        $stmt->execute();
        $blog['commentaires'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Pour chaque commentaire, récupérer les réponses associées de l'admin connecté
        foreach ($blog['commentaires'] as &$comment) {
            $query = "SELECT cr.id_reponse, cr.contenu_reponse, cr.date_creation, u.email AS admin_email
                      FROM commentaire_reponses cr
                      JOIN user u ON cr.id_admin = u.id_user
                      WHERE cr.id_commentaire = :id_commentaire AND cr.id_admin = :id_admin AND cr.is_deleted = 0
                      ORDER BY cr.date_creation DESC";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id_commentaire', $comment['id_commentaire'], PDO::PARAM_INT);
            $stmt->bindParam(':id_admin', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $comment['reponses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    $errorMessage = 'Erreur lors de la récupération des commentaires : ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Gestion des Commentaires</title>
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
        .comments-container {
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

        .comments-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .comments-container::before {
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

        .comments-container > * {
            position: relative;
            z-index: 1;
        }

        .comments-container h2 {
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

        /* Avertissement de politique */
        .policy-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            color: #856404;
        }

        .policy-warning i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Cards pour chaque blog */
        .blog-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            padding: 20px;
        }

        .blog-card h3 {
            color: var(--vert-principal);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .blog-preview {
            font-size: 0.95rem;
            color: var(--text-dark);
            margin-bottom: 20px;
        }

        /* Liste des commentaires */
        .comment {
            border-bottom: 1px solid rgba(92, 141, 118, 0.2);
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .comment:last-child {
            border-bottom: none;
        }

        .comment-content {
            flex: 1;
        }

        .comment-content p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .comment-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .comment-actions {
            display: flex;
            align-items: center;
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

        /* Formulaire de réponse */
        .response-form {
            margin-top: 10px;
        }

        .response-form textarea {
            border: 2px solid var(--vert-clair);
            border-radius: 5px;
            padding: 5px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .response-form textarea:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 5px rgba(92, 141, 118, 0.3);
        }

        .btn-response {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            color: white;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        .btn-response:hover {
            background-color: var(--vert-clair);
            transform: translateY(-2px);
        }

        /* Toggle Switch pour les commentaires */
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

        /* Bouton pour désactiver le compte */
        .disable-account-btn {
            background-color: #dc3545;
            border: none;
            border-radius: 20px;
            padding: 5px 10px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .disable-account-btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
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

            .comments-container {
                max-width: 90%;
                margin: 20px auto;
                padding: 25px;
            }

            .comment {
                flex-direction: column;
                align-items: flex-start;
            }

            .comment-actions {
                margin-top: 10px;
            }
        }

        @media (max-width: 576px) {
            .comments-container h2 {
                font-size: 2.5rem;
            }

            .floral-decoration {
                width: 100px;
                height: 100px;
            }

            .blog-card h3 {
                font-size: 1.2rem;
            }

            .comment-content p {
                font-size: 0.9rem;
            }

            .comment-meta {
                font-size: 0.8rem;
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
    <div class="comments-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Gestion des Commentaires</h2>
        
        <!-- Avertissement de politique -->
        <div class="policy-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Les utilisateurs ne respectant pas la politique d'intégrité verront leur compte désactivé.</span>
        </div>
        
        <?php if (!empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <?php if (empty($blogs)): ?>
            <p class="text-center">Aucun article trouvé.</p>
        <?php else: ?>
            <?php foreach ($blogs as $blog): ?>
                <div class="blog-card">
                    <h3><?php echo htmlspecialchars($blog['titre']); ?></h3>
                    <div class="blog-preview">
                        <?php
                        $preview = $blog['preview_content'] ? htmlspecialchars(substr($blog['preview_content'], 0, 150)) . '...' : 'Aucun contenu disponible.';
                        echo $preview;
                        ?>
                        <a href="view_blog.php?id=<?php echo $blog['id_blog']; ?>" class="text-muted">Lire la suite</a>
                    </div>
                    
                    <?php if (empty($blog['commentaires'])): ?>
                        <p class="text-muted">Aucun commentaire pour cet article.</p>
                    <?php else: ?>
                        <h4>Commentaires</h4>
                        <?php foreach ($blog['commentaires'] as $comment): ?>
                            <div class="comment">
                                <div class="comment-content">
                                    <p><?php echo htmlspecialchars($comment['contenu_commentaire']); ?></p>
                                    <div class="comment-meta">
                                        Par <strong><?php echo htmlspecialchars($comment['email']); ?></strong> 
                                        le <?php echo date('d/m/Y à H:i', strtotime($comment['date_creation'])); ?> -
                                        Statut : <span class="<?php echo $comment['is_deleted'] == 0 ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $comment['is_deleted'] == 0 ? 'Actif' : 'Désactivé'; ?>
                                        </span>
                                    </div>

                                    <!-- Afficher les réponses de l'admin connecté -->
                                    <?php if (!empty($comment['reponses'])): ?>
                                        <div class="comment-reponses">
                                            <?php foreach ($comment['reponses'] as $reponse): ?>
                                                <div class="reponse">
                                                    <p><?php echo htmlspecialchars($reponse['contenu_reponse']); ?></p>
                                                    <div class="reponse-meta">
                                                        Votre réponse 
                                                        le <?php echo date('d/m/Y à H:i', strtotime($reponse['date_creation'])); ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Formulaire pour ajouter une réponse -->
                                    <div class="response-form">
                                        <form method="POST" action="comments.php">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id_commentaire']; ?>">
                                            <textarea name="contenu_reponse" rows="2" placeholder="Votre réponse..." required></textarea>
                                            <button type="submit" name="add_response" class="btn-response">
                                                <i class="fas fa-reply me-2"></i>Répondre
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="comment-actions">
                                    <form method="POST" action="comments.php" style="display: inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id_commentaire']; ?>">
                                        <input type="hidden" name="is_deleted" value="<?php echo $comment['is_deleted'] == 0 ? '1' : '0'; ?>">
                                        <label class="switch">
                                            <input type="checkbox" <?php echo $comment['is_deleted'] == 0 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                            <span class="slider"></span>
                                        </label>
                                    </form>
                                    <form method="POST" action="comments.php" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir désactiver ce compte ?');">
                                        <input type="hidden" name="user_id" value="<?php echo $comment['id_user']; ?>">
                                        <button type="submit" name="disable_account" class="disable-account-btn">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation au survol du conteneur
            const commentsContainer = document.querySelector('.comments-container');
            commentsContainer.addEventListener('mouseenter', function() {
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