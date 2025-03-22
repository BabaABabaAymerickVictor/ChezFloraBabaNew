<?php
// templates/client/not_connected/contact.php
// Page de contact pour les utilisateurs non connectés

session_start();

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

// Gérer la soumission du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validation des champs
    if (empty($email) || empty($phone) || empty($address) || empty($message)) {
        $errorMessage = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Veuillez entrer un email valide.";
    } else {
        try {
            $query = "INSERT INTO contact (email, phone, address, message, date_creation, is_deleted) 
                      VALUES (:email, :phone, :address, :message, NOW(), 0)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':message', $message);
            $stmt->execute();

            $successMessage = "Votre message a été envoyé avec succès !";
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Contact</title>
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

        /* Formulaire de contact */
        .contact-container {
            max-width: 600px;
            margin: 160px auto 60px;
            padding: 35px;
            background-color: var(--blanc-creme);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(92, 141, 118, 0.15);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 1s ease-out;
        }

        .contact-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .contact-container::before {
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

        .contact-container > * {
            position: relative;
            z-index: 1;
        }

        .contact-container h2 {
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
            font-size: 1rem;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            font-size: 1rem;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating .form-control {
            border: 2px solid var(--vert-clair);
            border-radius: 15px;
            padding: 20px 15px;
            height: calc(3.5rem + 2px);
            background-color: rgba(255, 255, 255, 0.7);
            transition: all 0.3s ease;
        }

        .form-floating .form-control:focus {
            border-color: var(--vert-principal);
            box-shadow: 0 0 10px rgba(92, 141, 118, 0.3);
            background-color: white;
            transform: translateY(-3px);
        }

        .form-floating label {
            padding: 1rem 0.75rem;
            color: var(--text-dark);
        }

        .form-floating textarea.form-control {
            height: 150px;
        }

        .btn-submit {
            background-color: var(--vert-principal);
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-size: 1.1rem;
            width: 100%;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(92, 141, 118, 0.3);
        }

        .btn-submit:hover {
            background-color: var(--vert-clair);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(92, 141, 118, 0.4);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 3px 10px rgba(92, 141, 118, 0.3);
        }

        /* Effet d'onde au clic */
        .btn-submit::after {
            content: '';
            display: block;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform .5s, opacity 1s;
        }

        .btn-submit:active::after {
            transform: scale(0, 0);
            opacity: .3;
            transition: 0s;
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
            .contact-container {
                max-width: 90%;
                margin-top: 120px;
                padding: 25px;
            }

            .navbar-brand img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .contact-container h2 {
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
                        <a class="nav-link" href="../../../index.php"><i class="fas fa-home"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../../login.php"><i class="fas fa-store"></i> Boutique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php"><i class="fas fa-leaf"></i> Nos Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../../login.php"><i class="fas fa-blog"></i> Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><i class="fas fa-info-circle"></i> À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Formulaire de contact -->
    <div class="contact-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">Nous Contacter</h2>

        <?php if (isset($successMessage) && !empty($successMessage)): ?>
            <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if (isset($errorMessage) && !empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        
        <form action="contact.php" method="POST" id="contact-form">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="votre@email.com" required>
                <label for="email"><i class="fas fa-envelope me-2"></i>Adresse email</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="0123456789" required>
                <label for="phone"><i class="fas fa-phone me-2"></i>Téléphone</label>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="address" name="address" placeholder="Votre adresse" required>
                <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Adresse physique</label>
            </div>

            <div class="form-floating mb-4">
                <textarea class="form-control" id="message" name="message" placeholder="Votre message" required></textarea>
                <label for="message"><i class="fas fa-comment me-2"></i>Votre message</label>
            </div>
            
            <button type="submit" class="btn btn-submit">
                <i class="fas fa-paper-plane me-2"></i>Envoyer
            </button>
        </form>
    </div>

    <!-- Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Script personnalisé -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des champs de formulaire
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('animate__animated', 'animate__pulse');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('animate__animated', 'animate__pulse');
                });
            });
            
            // Effet de scroll pour la navbar
            const navbar = document.getElementById('main-navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
            
            // Animation au submit du formulaire
            const contactForm = document.getElementById('contact-form');
            const contactContainer = document.querySelector('.contact-container');
            
            contactForm.addEventListener('submit', function(e) {
                contactContainer.classList.add('animate__animated', 'animate__fadeOutUp');
            });
        });
    </script>
</body>
</html>