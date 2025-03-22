# Flora - README

## Prérequis
- Serveur web (Apache via XAMPP/WAMP)
- PHP (7.4+)
- MySQL
- Navigateur web

## Installation
- **Base de données** :
  - Créez une base `flora` : `CREATE DATABASE flora;`
  - Importez `flora.sql` via phpMyAdmin ou commande : `mysql -u [user] -p flora < flora.sql`
- **Connexion** :
  - Modifiez `classes/db_connect.php` avec vos infos MySQL :
    - `$host = "localhost";`
    - `$db_name = "flora";`
    - `$username = "root";` (votre utilisateur)
    - `$password = "";` (votre mot de passe)
- **Déploiement** :
  - Placez le projet dans `htdocs` (ex. `C:\xampp\htdocs\flora`)
  - Accédez via `http://localhost/flora/`

## Utilisation
- **Accueil** : Page `accueil.php`, voir Boutique, Blog, Services.
- **Inscription/Connexion** :
  - Inscription : Cliquez "Inscription", remplissez le formulaire.
  - Connexion : Cliquez "Connexion", entrez email/mot de passe.
  - **Admin** :
  - Connexion par défaut : `admin@flora.com` / `admin1234`
  - Gérez produits, catégories, commandes.
- **Client connecté** :
  - Boutique : Parcourez, filtrez, ajoutez au panier.
  - Panier : Consultez, modifiez, commandez.
  - Mon Compte : Gérez vos infos.
- **Admin** :
  - Connexion par défaut : `admin@flora.com` / `admin1234`
  - Gérez produits, catégories, commandes.

## Remarques
- Vérifiez les permissions des dossiers (ex. `public/images`).
- Testez avec un compte admin en modifiant `user_role` dans la table `users`.