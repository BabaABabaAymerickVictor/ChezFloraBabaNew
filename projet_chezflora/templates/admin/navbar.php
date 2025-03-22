<?php
// templates/admin/navbar.php
// Barre de navigation verticale pour l'interface admin
?>

<!-- Navbar verticale -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <img src="../../public/images/logo-removebg-preview.png" alt="ChezFlora Logo" class="sidebar-logo">
        </div>
        <div class="admin-title-container">
            <i class="fas fa-leaf floral-icon"></i>
            <span class="sidebar-title">Administrateur</span>
        </div>
        <button class="toggle-btn" id="toggleBtn">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
    <ul class="sidebar-menu">
        <li class="menu-item">
            <a href="dashboard.php" class="menu-link">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de bord</span>
            </a>
        </li>
        <li class="menu-item dropdown">
            <a href="#" class="menu-link dropdown-toggle">
                <i class="fas fa-users"></i>
                <span>Comptes</span>
            </a>
            <ul class="dropdown-menu">
                <li><a href="admins.php" class="dropdown-link">Administrateurs</a></li>
                <li><a href="users.php" class="dropdown-link">Clients</a></li>
            </ul>
        </li>
        <li class="menu-item dropdown">
            <a href="#" class="menu-link dropdown-toggle">
                <i class="fas fa-box-open"></i>
                <span>Produits</span>
            </a>
            <ul class="dropdown-menu">
                <li><a href="products.php" class="dropdown-link">Gérer les produits</a></li>
                <li><a href="categories.php" class="dropdown-link">Catégories</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="services.php" class="menu-link">
                <i class="fas fa-concierge-bell"></i>
                <span>Services</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="orders.php" class="menu-link">
                <i class="fas fa-shopping-cart"></i>
                <span>Commandes</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="quotes.php" class="menu-link">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Devis</span>
            </a>
        </li>
        <li class="menu-item dropdown">
            <a href="#" class="menu-link dropdown-toggle">
                <i class="fas fa-blog"></i>
                <span>Blog</span>
            </a>
            <ul class="dropdown-menu">
                <li><a href="blog.php" class="dropdown-link">Articles</a></li>
                <li><a href="comments.php" class="dropdown-link">Commentaires</a></li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="settings.php" class="menu-link">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </li>
        <li class="menu-item">
            <a href="dashboard.php?logout=true" class="menu-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </li>
    </ul>
</div>

<style>
    /* Palette de couleurs (identique aux pages précédentes) */
    :root {
        --vert-principal: rgb(170, 86, 90);
        --vert-clair: #A3C9A8;
        --rose-doux: #EACBD2;
        --blanc-creme: #F9F7F4;
        --terre-cuite: #D9B391;
        --text-dark: #363636;
    }

    /* Style de la sidebar */
    .admin-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        height: 100vh;
        background-color: var(--blanc-creme);
        box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
        transition: width 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
    }

    .admin-sidebar.collapsed {
        width: 80px;
    }

    .sidebar-header {
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid rgba(92, 141, 118, 0.2);
    }

    .logo-container {
        display: flex;
        justify-content: center;
        margin-bottom: 10px;
    }

    .sidebar-logo {
        width: 100px; /* Ajuste la taille du logo ici */
        height: auto;
        transition: width 0.3s ease;
    }

    .admin-sidebar.collapsed .sidebar-logo {
        width: 40px;
    }

    .admin-title-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
    }

    .floral-icon {
        color: var(--vert-principal);
        font-size: 1.2rem;
        margin-right: 8px;
        animation: pulse 2s infinite;
    }

    .sidebar-title {
        font-family: 'Great Vibes', cursive;
        font-size: 1.8rem;
        color: var(--vert-principal);
        transition: opacity 0.3s ease;
    }

    .admin-sidebar.collapsed .sidebar-title,
    .admin-sidebar.collapsed .floral-icon {
        opacity: 0;
    }

    .toggle-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        color: var(--vert-principal);
        font-size: 1.2rem;
        transition: transform 0.3s ease;
    }

    .admin-sidebar.collapsed .toggle-btn i {
        transform: rotate(180deg);
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-item {
        position: relative;
    }

    .menu-link {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: var(--text-dark);
        text-decoration: none;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .menu-link i {
        margin-right: 15px;
        font-size: 1.2rem;
        color: var(--vert-principal);
        transition: transform 0.3s ease;
    }

    .menu-link:hover {
        background-color: var(--vert-clair);
        color: var(--blanc-creme);
    }

    .menu-link:hover i {
        transform: translateX(5px);
    }

    .admin-sidebar.collapsed .menu-link span {
        display: none;
    }

    .admin-sidebar.collapsed .menu-link i {
        margin-right: 0;
    }

    /* Dropdown */
    .dropdown-menu {
        display: none;
        list-style: none;
        padding: 0;
        margin: 0;
        background-color: rgba(92, 141, 118, 0.1);
        transition: all 0.3s ease;
        position: static;    /* Ajout important */
    }

    .dropdown-menu.active {
        display: block;
    }

    .dropdown-link {
        display: block;
        padding: 10px 20px 10px 50px;
        color: var(--text-dark);
        text-decoration: none;
        font-size: 0.9rem;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .dropdown-link:hover {
        background-color: var(--vert-clair);
        color: var(--blanc-creme);
    }

    .admin-sidebar.collapsed .dropdown-menu {
        display: none !important;
    }

    /* Animations */
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .admin-sidebar {
            width: 80px;
        }

        .admin-sidebar .sidebar-title,
        .admin-sidebar .floral-icon,
        .admin-sidebar .menu-link span {
            display: none;
        }

        .admin-sidebar .menu-link i {
            margin-right: 0;
        }

        .admin-sidebar.collapsed {
            width: 0;
        }
    }
</style>

<script>
    // JavaScript pour la sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

        // Toggle sidebar
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });

        // Toggle dropdown menus
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const dropdownMenu = this.nextElementSibling;
                const isActive = dropdownMenu.classList.contains('active');

                // Fermer tous les autres dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu !== dropdownMenu) {
                        menu.classList.remove('active');
                    }
                });

                // Toggle le dropdown actuel
                dropdownMenu.classList.toggle('active');
            });
        });
    });
</script>