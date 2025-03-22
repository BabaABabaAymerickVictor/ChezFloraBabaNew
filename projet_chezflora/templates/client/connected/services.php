<?php
// templates/client/connected/services.php
// Page des services pour les clients connectés, avec un formulaire de demande de devis

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

// Récupérer les services (seulement ceux non supprimés)
try {
    $query = "SELECT id_service, nom_service, description, image_service 
              FROM services 
              WHERE is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Erreur lors de la récupération des services : " . $e->getMessage();
}

// Gérer la soumission du formulaire de devis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_devis'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? $userEmail; // Pré-rempli avec l'email de la session
    $phone = $_POST['phone'] ?? '';
    $eventType = $_POST['event_type'] ?? '';
    $details = $_POST['details'] ?? '';

    // Validation des champs
    if (empty($name) || empty($email) || empty($phone) || empty($eventType) || empty($details)) {
        $errorMessage = "Tous les champs sont obligatoires.";
    } else {
        try {
            $query = "INSERT INTO devis (name, email, phone, event_type, details, date_creation, is_deleted) 
                      VALUES (:name, :email, :phone, :event_type, :details, NOW(), 0)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':event_type', $eventType);
            $stmt->bindParam(':details', $details);
            $stmt->execute();

            $successMessage = "Votre demande de devis a été envoyée avec succès !";
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de l'envoi de la demande de devis : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Nos Services</title>
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

        /* Section des services */
        .services-section {
            padding: 120px 0 60px;
            background-color: var(--blanc-creme);
        }

        .services-section h2 {
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

        /* Cartes des services */
        .service-card {
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(92, 141, 118, 0.15);
            overflow: hidden;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            margin-bottom: 30px;
            animation: fadeInUp 1s ease-out;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        .service-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 3px solid var(--vert-clair);
        }

        .service-card-body {
            padding: 20px;
            text-align: center;
        }

        .service-card-body h5 {
            color: var(--vert-principal);
            font-family: 'Montserrat', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .service-card-body p {
            color: var(--text-dark);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Section du formulaire de devis */
        .devis-section {
            padding: 60px 0;
            background-color: var(--rose-doux);
        }

        .devis-section h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        .devis-form {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(92, 141, 118, 0.15);
            animation: fadeInUp 1s ease-out;
        }

        .devis-form .form-control,
        .devis-form .form-select {
            border: 2px solid var(--vert-clair);
            border-radius: 10px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        .devis-form .form-control:focus,
        .devis-form .form-select:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 10px rgba(92, 141, 118, 0.3);
        }

        .devis-form .form-control[readonly] {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }

        .devis-form label {
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 5px;
        }

        .btn-submit-devis {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1rem;
            font-weight: 500;
        }

        .btn-submit-devis:hover {
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
            .services-section,
            .devis-section {
                padding: 100px 0 40px;
            }

            .services-section h2,
            .devis-section h2 {
                font-size: 2.5rem;
            }

            .service-card img {
                height: 150px;
            }

            .service-card-body h5 {
                font-size: 1.2rem;
            }

            .service-card-body p {
                font-size: 0.9rem;
            }

            .devis-form {
                max-width: 90%;
                padding: 20px;
            }

            .navbar-brand img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .services-section h2,
            .devis-section h2 {
                font-size: 2rem;
            }

            .service-card-body h5 {
                font-size: 1rem;
            }

            .service-card-body p {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" id="main-navbar">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="../../../index.php">
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
                        <a class="nav-link" href="blog.php"><i class="fas fa-blog"></i> Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="services.php"><i class="fas fa-leaf"></i> Nos Services</a>
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

    <!-- Section des services -->
    <section class="services-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Nos Services</h2>

            <?php if (isset($successMessage) && !empty($successMessage)): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <p class="text-center">Aucun service disponible pour le moment.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($services as $service): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="service-card animate__animated animate__fadeInUp">
                                <img src="../../../<?php echo htmlspecialchars($service['image_service']); ?>" alt="<?php echo htmlspecialchars($service['nom_service']); ?>">
                                <div class="service-card-body">
                                    <h5><?php echo htmlspecialchars($service['nom_service']); ?></h5>
                                    <p><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section du formulaire de devis -->
    <section class="devis-section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Demander un Devis</h2>
            <div class="devis-form">
                <form method="POST" action="services.php">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Téléphone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="event_type" class="form-label">Type d'événement</label>
                        <select class="form-select" id="event_type" name="event_type" required>
                            <option value="" disabled selected>Choisir un type d'événement</option>
                            <option value="mariage">Mariage</option>
                            <option value="anniversaire">Anniversaire</option>
                            <option value="entreprise">Événement d'entreprise</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="details" class="form-label">Détails de votre demande</label>
                        <textarea class="form-control" id="details" name="details" rows="4" required placeholder="Décrivez votre demande..."></textarea>
                    </div>
                    <button type="submit" name="submit_devis" class="btn btn-submit-devis">Envoyer la demande</button>
                </form>
            </div>
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