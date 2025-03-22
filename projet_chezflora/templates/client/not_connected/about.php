<?php
// templates/client/not_connected/about.php
// Page "À propos" pour les utilisateurs non connectés

// Démarrer la session pour gérer les utilisateurs (à implémenter plus tard)
session_start();

// Vérifier si l'utilisateur est déjà connecté (logique à ajouter avec la classe User.php)
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChezFlora - À propos</title>
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

        /* Contenu de la page À propos */
        .about-container {
            max-width: 900px;
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

        .about-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(92, 141, 118, 0.25);
        }

        /* Décoration florale */
        .about-container::before {
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

        .about-container > * {
            position: relative;
            z-index: 1;
        }

        .about-container h2 {
            color: var(--vert-principal);
            font-family: 'Great Vibes', cursive;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-out;
        }

        .about-container h3 {
            color: var(--vert-principal);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1.5rem;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .about-container p {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-dark);
            margin-bottom: 20px;
        }

        .about-container ul {
            list-style: none;
            padding-left: 0;
        }

        .about-container ul li {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-dark);
            margin-bottom: 10px;
            position: relative;
            padding-left: 25px;
        }

        .about-container ul li::before {
            content: '\f4d8'; /* Icône feuille de Font Awesome */
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            color: var(--vert-clair);
            position: absolute;
            left: 0;
            top: 2px;
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
            .about-container {
                max-width: 90%;
                margin-top: 120px;
                padding: 25px;
            }

            .navbar-brand img {
                height: 60px;
            }
        }

        @media (max-width: 576px) {
            .about-container h2 {
                font-size: 2.5rem;
            }

            .about-container h3 {
                font-size: 1.3rem;
            }

            .about-container p, .about-container ul li {
                font-size: 0.9rem;
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
                        <a class="nav-link active" href="about.php"><i class="fas fa-info-circle"></i> À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu de la page À propos -->
    <div class="about-container animate__animated animate__fadeInUp">
        <div class="floral-decoration floral-top-right"></div>
        <div class="floral-decoration floral-bottom-left"></div>
        
        <h2 class="animate__animated animate__fadeIn">À Propos de ChezFlora</h2>

        <!-- Historique -->
        <h3>Notre Histoire</h3>
        <p>
            Fondée en 2016, ChezFlora est une entreprise passionnée par l’art floral et la décoration événementielle. Depuis nos débuts, nous avons accompagné des milliers de clients dans leurs moments les plus précieux, des anniversaires intimistes aux grandes cérémonies comme les mariages ou les événements d’entreprise. Notre mission est de sublimer chaque instant avec des fleurs et des compositions qui racontent une histoire.
        </p>

        <!-- Valeurs -->
        <h3>Nos Valeurs</h3>
        <p>
            ChezFlora repose sur des valeurs fondamentales qui guident chacune de nos actions :
        </p>
        <ul>
            <li><strong>Passion pour les fleurs</strong> : Nous croyons que chaque fleur a une âme et peut transmettre des émotions uniques.</li>
            <li><strong>Qualité et élégance</strong> : Nous sélectionnons les plus belles fleurs et créons des compositions raffinées pour un rendu exceptionnel.</li>
            <li><strong>Proximité avec nos clients</strong> : Nous écoutons vos besoins pour proposer des solutions sur mesure, adaptées à chaque événement.</li>
            <li><strong>Respect de la nature</strong> : Nous intégrons des pratiques durables dans notre activité pour préserver la beauté de notre planète.</li>
        </ul>

        <!-- Engagements écologiques et fournisseurs -->
        <h3>Nos Engagements Écologiques et Fournisseurs</h3>
        <p>
            ChezFlora s’engage à minimiser son impact environnemental. Nous privilégions des fleurs issues de cultures responsables et locales autant que possible. Nos partenaires, soigneusement sélectionnés, partagent notre vision d’une floriculture durable. Par exemple :
        </p>
        <ul>
            <li>Collaboration avec des producteurs locaux pour réduire l’empreinte carbone liée au transport.</li>
            <li>Utilisation d’emballages biodégradables ou recyclables pour nos livraisons.</li>
            <li>Promotion de plantes et fleurs de saison pour respecter les cycles naturels.</li>
        </ul>
        <p>
            Nous travaillons main dans la main avec des fournisseurs qui respectent des normes éthiques strictes, garantissant des conditions de travail justes et un impact positif sur les communautés locales.
        </p>
    </div>

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