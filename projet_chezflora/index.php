<?php
// index.php
// Page d'accueil (landing page) pour tous les utilisateurs

// Démarrer la session pour gérer les utilisateurs (à implémenter plus tard)
session_start();

// Vérifier si l'utilisateur est déjà connecté (logique à ajouter avec la classe User.php)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - Bienvenue</title>
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
            --vert-principal: rgb(170, 86, 90); /* Vert sauge/émeraude pour le thème floral */
            --vert-clair: #A3C9A8;          /* Vert menthe pour accents */
            --rose-doux: #EACBD2;           /* Rose pâle pour les touches féminines */
            --blanc-creme: #F9F7F4;         /* Fond crème chaleureux */
            --terre-cuite: #D9B391;         /* Couleur terre pour évoquer les pots */
            --text-dark: #363636;           /* Pour le texte principal */
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

        /* Section Hero */
        .hero-section {
            position: relative;
            height: 100vh;
            background: url('public/images/sakura.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-content h1 {
            font-family: 'Great Vibes', cursive;
            font-size: 4.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1.5s ease-out;
        }

        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            animation: fadeInUp 1.5s ease-out;
        }

        .hero-content .btn-explore {
            background-color: var(--vert-principal);
            color: white;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(92, 141, 118, 0.3);
        }

        .hero-content .btn-explore:hover {
            background-color: var(--vert-clair);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(92, 141, 118, 0.4);
        }

        /* Sections générales */
        .section {
            padding: 80px 0;
            position: relative;
        }

        .section h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 40px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        /* Produits et services mis en avant */
        .products-services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: var(--blanc-creme);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            padding: 20px;
            text-align: center;
        }

        .card-title {
            color: var(--vert-principal);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        /* Promotions */
        .promotions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .promotion-card {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .promotion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .promotion-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .promotion-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .promotion-card:hover .promotion-overlay {
            opacity: 1;
        }

        .promotion-overlay h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .promotion-overlay p {
            font-size: 1rem;
        }

        /* Témoignages */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .testimonial-card {
            background-color: var(--blanc-creme);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .testimonial-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .testimonial-card p {
            font-size: 0.9rem;
            color: var(--text-dark);
            font-style: italic;
            margin-bottom: 10px;
        }

        .testimonial-card h5 {
            color: var(--vert-principal);
            font-weight: 600;
            font-size: 1rem;
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
            .hero-content h1 {
                font-size: 3rem;
            }

            .hero-content p {
                font-size: 1rem;
            }

            .section h2 {
                font-size: 2.5rem;
            }

            .card img, .promotion-card img {
                height: 180px;
            }
        }

        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 0.9rem;
            }

            .section h2 {
                font-size: 2rem;
            }

            .card img, .promotion-card img {
                height: 150px;
            }

            .testimonial-card img {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light" id="main-navbar">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                <img src="public/images/logo-removebg-preview.png" alt="ChezFlora Logo" class="animate__animated animate__fadeIn">
            </a>
            <!-- Bouton pour mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Liens de navigation -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-home"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-store"></i> Boutique</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="templates/client/not_connected/services.php"><i class="fas fa-leaf"></i> Nos Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-blog"></i> Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="templates/client/not_connected/about.php"><i class="fas fa-info-circle"></i> À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="templates/client/not_connected/contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section Hero -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="animate__animated animate__fadeIn">Bienvenue chez ChezFlora</h1>
            <p class="animate__animated animate__fadeInUp">Sublimez vos événements avec nos créations florales uniques et élégantes.</p>
            <a href="templates/client/not_connected/services.php" class="btn btn-explore animate__animated animate__fadeInUp">Découvrir nos services</a>
        </div>
    </section>

    <!-- Section Produits et Services -->
    <section class="section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Nos Produits et Services</h2>
            <div class="products-services-grid">
                <div class="card animate__animated animate__fadeInUp">
                    <img src="public/images/f1.jpg" alt="Fleurs Fraîches">
                    <div class="card-body">
                        <h5 class="card-title">Fleurs Fraîches</h5>
                        <p class="card-text">Découvrez notre sélection de roses, lys, tulipes et bien plus, parfaites pour toutes les occasions.</p>
                    </div>
                </div>
                <div class="card animate__animated animate__fadeInUp">
                    <img src="public/images/f2.jpg" alt="Bouquets">
                    <div class="card-body">
                        <h5 class="card-title">Bouquets Personnalisés</h5>
                        <p class="card-text">Créez des bouquets uniques pour mariages, anniversaires ou simplement pour faire plaisir.</p>
                    </div>
                </div>
                <div class="card animate__animated animate__fadeInUp">
                    <img src="public/images/f3.jpg" alt="Décoration Événementielle">
                    <div class="card-body">
                        <h5 class="card-title">Décoration Événementielle</h5>
                        <p class="card-text">Sublimez vos événements avec nos décorations florales sur mesure.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Promotions -->
    <section class="section" style="background-color: #f5f5f5;">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Offres Spéciales</h2>
            <div class="promotions-grid">
                <div class="promotion-card animate__animated animate__fadeInUp">
                    <img src="public/images/o1.jpg" alt="Promotion 1">
                    <div class="promotion-overlay">
                        <h4>20% de Réduction</h4>
                        <p>Sur tous les bouquets de mariage ce mois-ci !</p>
                    </div>
                </div>
                <div class="promotion-card animate__animated animate__fadeInUp">
                    <img src="public/images/o2.jpg" alt="Promotion 2">
                    <div class="promotion-overlay">
                        <h4>Livraison Gratuite</h4>
                        <p>Pour toute commande supérieure à 50€.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Témoignages -->
    <section class="section">
        <div class="container">
            <h2 class="animate__animated animate__fadeIn">Ce que disent nos clients</h2>
            <div class="testimonials-grid">
                <div class="testimonial-card animate__animated animate__fadeInUp">
                    <img src="public/images/w1.png" alt="Client 1">
                    <p>"ChezFlora a transformé notre mariage en un véritable conte de fées avec leurs décorations florales !"</p>
                    <h5>Habiba D.</h5>
                </div>
                <div class="testimonial-card animate__animated animate__fadeInUp">
                    <img src="public/images/m1.png" alt="Client 2">
                    <p>"Les bouquets sont toujours frais et magnifiques. Un service client exceptionnel !"</p>
                    <h5>Abdouramane I.</h5>
                </div>
                <div class="testimonial-card animate__animated animate__fadeInUp">
                    <img src="public/images/w2.png" alt="Client 3">
                    <p>"Pour notre événement d’entreprise, tout était parfait. Merci ChezFlora !"</p>
                    <h5>Arya F.</h5>
                </div>
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