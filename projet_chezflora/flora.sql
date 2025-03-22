-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 22 mars 2025 à 15:47
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `flora`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int NOT NULL AUTO_INCREMENT,
  `nom_admin` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `is_deleted` int NOT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id_admin`, `nom_admin`, `password`, `is_deleted`) VALUES
(1, 'hba@gmail.com', '1234', 0),
(2, 'raman@gmail.com', '1234', 0);

-- --------------------------------------------------------

--
-- Structure de la table `blog`
--

DROP TABLE IF EXISTS `blog`;
CREATE TABLE IF NOT EXISTS `blog` (
  `id_blog` int NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_blog`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `blog`
--

INSERT INTO `blog` (`id_blog`, `titre`, `date_creation`, `is_deleted`) VALUES
(1, 'Organiser un mariage parfait avec ChezFlora - L\'art floral au service de votre jour J', '2025-03-22 05:27:56', 0);

-- --------------------------------------------------------

--
-- Structure de la table `blog_paragraph`
--

DROP TABLE IF EXISTS `blog_paragraph`;
CREATE TABLE IF NOT EXISTS `blog_paragraph` (
  `id_paragraph` int NOT NULL AUTO_INCREMENT,
  `id_blog` int NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `position` int NOT NULL,
  PRIMARY KEY (`id_paragraph`),
  KEY `id_blog` (`id_blog`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `blog_paragraph`
--

INSERT INTO `blog_paragraph` (`id_paragraph`, `id_blog`, `content`, `image_path`, `position`) VALUES
(1, 1, 'Le mariage est sans doute l\'un des moments les plus importants dans la vie d\'un couple. Chaque détail compte pour créer cette atmosphère magique que les mariés et leurs invités garderont en mémoire pendant des années. Chez ChezFlora, nous comprenons l\'importance des arrangements floraux pour sublimer cette journée spéciale.', '/public/images_blog/1742617676_heli.jpg', 1),
(2, 1, 'Les fleurs sont bien plus que de simples décorations. Elles racontent une histoire, créent une ambiance et peuvent transformer n\'importe quel espace en un lieu enchanteur. Qu\'il s\'agisse d\'un mariage intime ou d\'une grande réception, les compositions florales apportent couleur, parfum et émotion à votre célébration.\r\nNotre équipe de fleuristes passionnés chez ChezFlora travaille avec chaque couple pour comprendre leur vision et créer des arrangements personnalisés qui reflètent leur personnalité et le thème de leur mariage. Du bouquet de la mariée aux centres de table, en passant par la décoration de la cérémonie, nous veillons à ce que chaque élément floral s\'harmonise parfaitement.', '/public/images_blog/1742648380_deco 2.jpg', 2),
(3, 1, 'Cette année, nous observons plusieurs tendances florales qui séduisent nos mariés :\r\n\r\nLe retour au naturel : Des compositions qui évoquent des jardins sauvages, avec des fleurs qui semblent fraîchement cueillies\r\nLes palettes monochromatiques : Des arrangements ton sur ton qui créent une élégance subtile\r\nL\'intégration d\'éléments séchés : Pampas, fleurs séchées et herbes ornementales pour un style bohème et durable\r\nLes installations spectaculaires : Arches florales imposantes, plafonds fleuris et murs végétaux qui créent des points focaux impressionnants', '/public/images_blog/1742648380_a4bd064a7c9e324c2e73c6ba29a5e54c.jpg', 3),
(4, 1, 'Chez ChezFlora, nous privilégions les fleurs de saison pour leur fraîcheur, leur beauté optimale et leur impact environnemental réduit. Voici quelques suggestions selon la période de votre mariage :\r\nPrintemps : Pivoines, tulipes, lilas, muguet\r\nÉté : Roses, hortensias, tournesols, dahlias\r\nAutomne : Chrysanthèmes, astilbes, amarantes, baies\r\nHiver : Amaryllis, hellébores, camélias, branches de houx', NULL, 4);

-- --------------------------------------------------------

--
-- Structure de la table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id_cart_item` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_product` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_cart_item`),
  KEY `id_user` (`id_user`),
  KEY `id_product` (`id_product`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cart_items`
--

INSERT INTO `cart_items` (`id_cart_item`, `id_user`, `id_product`, `quantity`) VALUES
(9, 1, 1, 2);

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom_categorie`, `is_deleted`) VALUES
(2, 'Fleurs fraîches', 0),
(3, 'Bouquets personnalisés', 0),
(4, 'Bouquets saisonniers', 0),
(5, 'Bouquets pour mariage', 0),
(6, 'Plantes d’intérieur', 0),
(7, 'Cactus', 0),
(8, 'couronnes', 0),
(9, 'Compositions florales', 0),
(10, 'Accessoires déco', 0);

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

DROP TABLE IF EXISTS `commentaires`;
CREATE TABLE IF NOT EXISTS `commentaires` (
  `id_commentaire` int NOT NULL AUTO_INCREMENT,
  `id_blog` int NOT NULL,
  `id_user` int NOT NULL,
  `contenu_commentaire` text NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_commentaire`),
  KEY `id_blog` (`id_blog`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commentaires`
--

INSERT INTO `commentaires` (`id_commentaire`, `id_blog`, `id_user`, `contenu_commentaire`, `date_creation`, `is_deleted`) VALUES
(1, 1, 1, 'Ceci est un commentaire de test pour vérifier le fonctionnement.', '2025-03-22 06:07:53', 0),
(2, 1, 3, 'FreePalestine', '2025-03-22 10:35:21', 0),
(3, 1, 1, 'Salaam Aleykum', '2025-03-22 15:49:27', 0);

-- --------------------------------------------------------

--
-- Structure de la table `commentaire_reponses`
--

DROP TABLE IF EXISTS `commentaire_reponses`;
CREATE TABLE IF NOT EXISTS `commentaire_reponses` (
  `id_reponse` int NOT NULL AUTO_INCREMENT,
  `id_commentaire` int NOT NULL,
  `contenu_reponse` text NOT NULL,
  `id_admin` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_reponse`),
  KEY `id_commentaire` (`id_commentaire`),
  KEY `id_admin` (`id_admin`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `commentaire_reponses`
--

INSERT INTO `commentaire_reponses` (`id_reponse`, `id_commentaire`, `contenu_reponse`, `id_admin`, `date_creation`, `is_deleted`) VALUES
(1, 1, 'bonjour', 0, '2025-03-22 06:29:46', 0),
(2, 1, 'derrttrt', 0, '2025-03-22 06:33:59', 0),
(3, 1, 'derrttrt', 0, '2025-03-22 06:34:58', 0),
(4, 1, 'test2', 0, '2025-03-22 06:45:45', 0);

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id_contact` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_contact`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `contact`
--

INSERT INTO `contact` (`id_contact`, `email`, `phone`, `address`, `message`, `date_creation`, `is_deleted`) VALUES
(1, 'ramanabdou507@gmail.com', '658867346', 'irak', 'test ', '2025-03-22 11:39:32', 0);

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

DROP TABLE IF EXISTS `devis`;
CREATE TABLE IF NOT EXISTS `devis` (
  `id_devis` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `event_type` enum('mariage','anniversaire','entreprise') NOT NULL,
  `details` text NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_devis`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `devis`
--

INSERT INTO `devis` (`id_devis`, `name`, `email`, `phone`, `event_type`, `details`, `date_creation`, `is_deleted`) VALUES
(1, 'Jean Dupont', 'jean.dupont@example.com', '0123456789', 'mariage', 'Je souhaite un devis pour un mariage de 100 personnes avec des décorations florales et un bouquet de mariée.', '2025-03-22 06:18:31', 0),
(2, 'ABDOU', 'ramanabdou507@gmail.com', '65885544', 'entreprise', 'test', '2025-03-22 10:50:22', 0);

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id_order` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('En attente','Expédiée','Livrée','Annulée') DEFAULT 'En attente',
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_order`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `date_creation`, `total_amount`, `status`, `is_deleted`) VALUES
(1, 1, '2025-03-21 10:00:00', 35.00, 'Expédiée', 0),
(3, 3, '2025-03-22 12:09:41', 1725.00, 'En attente', 0);

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id_order_item` int NOT NULL AUTO_INCREMENT,
  `id_order` int NOT NULL,
  `id_product` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_order_item`),
  KEY `id_order` (`id_order`),
  KEY `id_product` (`id_product`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id_order_item`, `id_order`, `id_product`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 2, 10.00),
(2, 1, 1, 1, 15.00),
(5, 3, 2, 3, 500.00),
(6, 3, 1, 1, 225.00);

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id_product` int NOT NULL AUTO_INCREMENT,
  `nom_product` varchar(255) NOT NULL,
  `image_product` varchar(255) NOT NULL,
  `id_categorie` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` text,
  `is_deleted` tinyint(1) DEFAULT '0',
  `promotion` tinyint(1) DEFAULT '0',
  `pourcentage_reduction` decimal(5,2) DEFAULT '0.00',
  `prix_promo` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id_product`),
  KEY `id_categorie` (`id_categorie`)
) ENGINE=MyISAM AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id_product`, `nom_product`, `image_product`, `id_categorie`, `prix`, `description`, `is_deleted`, `promotion`, `pourcentage_reduction`, `prix_promo`) VALUES
(1, 'Dahlia', '../../public/images/products/1742587901_dahlia.jpg', 1, 450.00, 'Dahlia vibrant et coloré, parfait pour égayer votre jardin.', 0, 1, 50.00, 225.00),
(2, 'Tulipes', '../../public/images/products/1742635401_heli.jpg', 1, 500.00, 'Tulipes élégantes, idéales pour les bouquets printaniers.', 0, 0, 0.00, 0.00),
(3, 'Héliotrope Violet', '../../public/images/products/1742643321_helio violet.jpg', 2, 350.00, 'Violettes délicates, un ajout charmant à tout espace.', 0, 0, 0.00, 0.00),
(4, 'Héliotrope Blanche', '../../public/images/products/1742643410_helio.jpg', 2, 400.00, 'Héliotrope parfumé, attire les papillons dans votre jardin.', 0, 0, 0.00, 0.00),
(5, 'Souci Orange', '../../public/images/products/1742643437_souci.jpg', 2, 150.00, 'Souci lumineux, connu pour ses propriétés médicinales.', 0, 0, 0.00, 0.00),
(6, 'Tournesol', '../../public/images/products/1742643461_tournesol.jpg', 2, 1500.00, 'Tournesol majestueux, symbole de soleil et de bonheur.', 0, 0, 0.00, 0.00),
(7, 'Frangipanier Rose blanc', '../../public/images/products/1742643511_fangi rose blanc.jpg', 2, 1600.00, 'Fougère luxuriante, parfaite pour les zones ombragées.', 0, 0, 0.00, 0.00),
(8, 'Frangipanier Blanc jaune', '../../public/images/products/1742643536_frangi blanc jaune.jpg', 2, 800.00, 'Frangipanier exotique, avec un parfum envoûtant.', 0, 0, 0.00, 0.00),
(9, 'Hibiscus Bleu', '../../public/images/products/1742643564_hibiscus bleu.jpg', 2, 1800.00, 'Hibiscus éclatant, idéal pour les climats chauds.', 0, 0, 0.00, 0.00),
(10, 'Hibiscus Rose', '../../public/images/products/1742643599_hibiscus rose.jpg', 2, 2000.00, 'Hibiscus rouge profond, ajoute une touche de passion.', 0, 0, 0.00, 0.00),
(11, 'Hibiscus Rouge', '../../public/images/products/1742643619_hibiscus rouge.jpg', 2, 2500.00, 'Hibiscus rose tendre, parfait pour un jardin romantique.', 0, 0, 0.00, 0.00),
(12, 'Lotus Bleu', '../../public/images/products/1742643653_lotus bleu.jpg', 2, 800.00, 'Lotus bleu sacré, symbole de pureté et de sérénité.', 0, 0, 0.00, 0.00),
(13, 'Lotus Rose', '../../public/images/products/1742643675_lotus rose.jpg', 2, 1900.00, 'Lotus rose délicat, pour une touche d’élégance aquatique.', 0, 0, 0.00, 0.00),
(14, 'Oiseau de paradis Bleu', '../../public/images/products/1742643728_oiseau bleu.jpg', 2, 1700.00, 'Œillet bleu rare, parfait pour les compositions florales.', 0, 0, 0.00, 0.00),
(15, 'Oiseau de paradis Orange', '../../public/images/products/1742643746_oiseau.jpg', 2, 1700.00, 'Œillet jaune éclatant, apporte de la joie à tout bouquet.', 0, 0, 0.00, 0.00),
(16, 'Lys Blanche', '../../public/images/products/1742643771_lys blanche.jpg', 2, 1500.00, 'Lys blanc pur, symbole de paix et d’innocence.', 0, 0, 0.00, 0.00),
(17, 'Lys Jaune', '../../public/images/products/1742643796_lys jaune.jpg', 2, 2000.00, 'Lys jaune lumineux, pour une touche de soleil.', 0, 0, 0.00, 0.00),
(18, 'Lys Rose', '../../public/images/products/1742643871_lys rose.jpg', 2, 1900.00, 'Lys rose doux, idéal pour les occasions spéciales.', 0, 0, 0.00, 0.00),
(19, 'Lys Rouge', '../../public/images/products/1742643899_lys rouge.jpg', 2, 1400.00, 'Marguerite blanche classique, parfaite pour un look champêtre.', 0, 0, 0.00, 0.00),
(20, 'Marguerite Blanche', '../../public/images/products/1742643932_margarite blanche.jpg', 2, 1600.00, 'Marguerite jaune ensoleillée, pour un jardin joyeux.', 0, 0, 0.00, 0.00),
(21, 'Marguerite Bleu', '../../public/images/products/1742643945_margarite bleu.jpg', 2, 1600.00, 'Marguerite rose tendre, ajoute une touche de douceur.', 0, 0, 0.00, 0.00),
(22, 'Marguerite Jaune', '../../public/images/products/1742643979_margarite jaune.jpg', 2, 1600.00, 'Orchidée blanche élégante, parfaite pour les intérieurs.', 0, 0, 0.00, 0.00),
(23, 'Marguerite Rose', '../../public/images/products/1742643995_margarite rose.jpg', 2, 1600.00, 'Orchidée rose vibrante, un bijou pour votre maison.', 0, 0, 0.00, 0.00),
(24, 'Orchidée Blance', '../../public/images/products/1742644022_orchidee blanche.jpg', 2, 1200.00, 'Orchidée bleue rare, une beauté exotique.', 0, 0, 0.00, 0.00),
(25, 'Orchidée Bleu', '../../public/images/products/1742644043_orchidee bleu.jpg', 2, 1200.00, 'Orchidée violette, symbole de luxe et de raffinement.', 0, 0, 0.00, 0.00),
(26, 'Orchidée Jaune', '../../public/images/products/1742644057_orchidee jaune.jpg', 2, 1200.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(27, 'Orchidée Rose', '../../public/images/products/1742644073_orchidee rose.jpg', 2, 1200.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(28, 'Pivoine Rose', '../../public/images/products/1742644093_pivoine rose.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(29, 'Pivoine Rouge', '../../public/images/products/1742644110_pivoine rouge.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(30, 'Rose Blanche', '../../public/images/products/1742644176_rose blanche.jpg', 2, 1600.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(31, 'Rose rose', '../../public/images/products/1742644193_rose rose.jpg', 2, 1600.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(32, 'Rose Rouge', '../../public/images/products/1742644213_rose.jpg', 2, 1500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(33, 'Tulipe Jaune', '../../public/images/products/1742644242_tulipe jaune.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(34, 'Tulipe Orange', '../../public/images/products/1742644259_tulipe orange.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(35, 'Tulipe Rose', '../../public/images/products/1742644285_tulipe rose.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(36, 'Tulipe Violet', '../../public/images/products/1742644306_tulipe violet.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(37, 'Tulipes Violet', '../../public/images/products/1742644324_tulipeviolet.jpg', 2, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(38, 'Anémones', '../../public/images/products/1742644367_anemone.jpg', 2, 500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(39, 'Bleuet', '../../public/images/products/1742644393_blueut.jpg', 2, 600.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(40, 'Chardon', '../../public/images/products/1742644415_chardon.jpg', 2, 390.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(41, 'Coquelicot Orange', '../../public/images/products/1742644442_coauelicot orange.jpg', 2, 460.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(42, 'Coquelicots Orange', '../../public/images/products/1742644457_cquelit.jpg', 2, 460.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(43, 'Primevère', '../../public/images/products/1742644478_primevet.jpg', 2, 750.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(44, 'Bouquet 1', '../../public/images/products/1742644672_2.jpg', 4, 1420.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(45, 'Bouquet 2', '../../public/images/products/1742644695_3.jpg', 4, 1420.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(46, 'Bouquet Aster', '../../public/images/products/1742644727_aster.jpg', 4, 1600.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(47, 'Bouquet Dahlia', '../../public/images/products/1742644753_dahlia 2.jpg', 4, 1400.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(48, 'Bouquet 2 Dahlia', '../../public/images/products/1742644805_dahlia.jpg', 4, 1900.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(49, ' Bouquet Héliotrope Violet', '../../public/images/products/1742644870_heli 2.jpg', 4, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(50, ' Bouquet Héliotrope Bleu', '../../public/images/products/1742644896_heli.jpg', 4, 6000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(51, 'Bouquet Lavande ', '../../public/images/products/1742644941_lavande 2.jpg', 4, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(52, 'Bouquets Lavande ', '../../public/images/products/1742644957_lavande.jpg', 4, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(53, 'Bouquet Lys rose', '../../public/images/products/1742644981_lys 1.jpg', 4, 4500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(54, 'Bouquet Lys Blanche', '../../public/images/products/1742645005_lys 2.jpg', 4, 5200.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(55, 'Bouquets Lys Blanche', '../../public/images/products/1742645021_lys 3.jpg', 4, 5200.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(56, 'Bouquet Tournesol ', '../../public/images/products/1742645050_tournesols 2.jpg', 4, 7500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(57, 'Bouquets Tournesol ', '../../public/images/products/1742645062_tournesols.jpg', 4, 7500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(58, 'Bouquet Camelias', '../../public/images/products/1742645097_Camélias 1.jpg', 4, 6000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(59, 'Bouquet Noel', '../../public/images/products/1742645117_noel.jpg', 4, 2500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(60, 'Bouquets Noel ', '../../public/images/products/1742645132_rose noel.jpg', 4, 2500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(61, 'Bouquets Jacinthes', '../../public/images/products/1742645159_jacinthes.jpg', 4, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(62, 'Bouquet Jonquilles jaune', '../../public/images/products/1742645198_jonquilles 1.jpg', 4, 12000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(63, 'Bouquet Tulipes Rose', '../../public/images/products/1742645220_tulipe 2.jpg', 4, 6000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(64, 'Bouquet Tulipes Rose/Blanc', '../../public/images/products/1742645244_tulipe 3.jpg', 4, 6000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(65, 'Bouquet Tulipe', '../../public/images/products/1742645266_tulipes 1.jpg', 4, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(66, 'Bouquet 1', '../../public/images/products/1742645314_bouquet 1.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(67, 'Bouquet 2', '../../public/images/products/1742645336_bouquet 2.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(68, 'Bouquet 3', '../../public/images/products/1742645347_bouquet 3.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(69, 'Bouquet 4', '../../public/images/products/1742645358_bouquet 4.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(70, 'Bouquet 5', '../../public/images/products/1742645368_bouquet 5.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(71, 'Bouquet 6', '../../public/images/products/1742645379_bouquet 6.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(72, 'Bouquet 7', '../../public/images/products/1742645406_bouquet 7.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(73, 'Bouquet 8', '../../public/images/products/1742645417_bouquet 8.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(74, 'Bouquet 9', '../../public/images/products/1742645428_bouquet 9.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(75, 'Bouquet 10', '../../public/images/products/1742645453_bouquet 10.jpg', 5, 13000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(76, 'Bouquet 1', '../../public/images/products/1742645483_bouquet 1.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(77, 'Bouquet 2', '../../public/images/products/1742645495_bouquet 3.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(78, 'Bouquet 3', '../../public/images/products/1742645506_bouquet 4.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(79, 'Bouquet 4', '../../public/images/products/1742645517_bouquet 5.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(80, 'Bouquet 5', '../../public/images/products/1742645529_bouquet 6.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(81, 'Bouquet 6', '../../public/images/products/1742645542_bouquet 7.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(82, 'Bouquet 7', '../../public/images/products/1742645553_bouquet 6.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(83, 'Bouquet 8', '../../public/images/products/1742645565_bouquet 8.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(84, 'Bouquet 9', '../../public/images/products/1742645578_bouquet 9.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(85, 'Bouquet 10', '../../public/images/products/1742645617_bouquet 10.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(86, 'Bouquet 11', '../../public/images/products/1742645630_bouquet 10.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(87, 'Bouquet 12', '../../public/images/products/1742645654_bouquet 11.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(88, 'Bouquet 13', '../../public/images/products/1742645678_bouquet 12.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(89, 'Bouquet 14', '../../public/images/products/1742645689_bouquet 13.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(90, 'Bouquet 15', '../../public/images/products/1742645700_bouquet 14.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(91, 'Bouquet 16', '../../public/images/products/1742645712_bouquet 15.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(92, 'Bouquet 17', '../../public/images/products/1742645723_bouquet 16.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(93, 'Bouquet 18', '../../public/images/products/1742645735_bouquets 2.jpg', 3, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(94, 'Plante 1', '../../public/images/products/1742645769_plant 1.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(95, 'Plante 2', '../../public/images/products/1742645779_plant 2.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(96, 'Plante 3', '../../public/images/products/1742645788_plant 3.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(97, 'Plante 4', '../../public/images/products/1742645799_plant 4.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(98, 'Plante 5', '../../public/images/products/1742645808_plant 5.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(99, 'Plante 6', '../../public/images/products/1742645819_plant 6.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(100, 'Plante 7', '../../public/images/products/1742645828_plant 7.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(101, 'Plante 8', '../../public/images/products/1742645838_plant 8.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(102, 'Plante 9', '../../public/images/products/1742645850_plant 9.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(103, 'Plante 10', '../../public/images/products/1742645860_plant 10.jpg', 6, 9000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(104, 'Cactus 1', '../../public/images/products/1742646289_cactus 1.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(105, 'Cactus 2', '../../public/images/products/1742646299_cactus 2.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(106, 'Cactus 3', '../../public/images/products/1742646308_cactus 3.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(107, 'Cactus 4', '../../public/images/products/1742646318_cactus 4.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(108, 'Cactus 5', '../../public/images/products/1742646327_cactus 5.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(109, 'Cactus 6', '../../public/images/products/1742646340_cactus 6.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(110, 'Cactus 7', '../../public/images/products/1742646350_cactus.jpg', 7, 6500.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(111, 'Couronne 1', '../../public/images/products/1742646384_cor 1.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(112, 'Couronne 2', '../../public/images/products/1742646395_cor 2.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(113, 'Couronne 3', '../../public/images/products/1742646407_cor 3.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(114, 'Couronne 4', '../../public/images/products/1742646416_cor 4.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(115, 'Couronne 5', '../../public/images/products/1742646425_cor 5.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(116, 'Couronne 6', '../../public/images/products/1742646434_cor 6.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(117, 'Couronne 7', '../../public/images/products/1742646446_cor 7.jpg', 8, 8000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(118, 'Composition florale 1', '../../public/images/products/1742646481_cf 1.jpg', 9, 11100.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(119, 'Composition florale 2', '../../public/images/products/1742646493_cf 2.jpg', 9, 11100.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(120, 'Composition florale 3', '../../public/images/products/1742646503_cf 3.jpg', 9, 11100.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(121, 'Composition florale 4', '../../public/images/products/1742646513_cf 4.jpg', 9, 11100.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(122, 'Composition florale 5', '../../public/images/products/1742646526_cor 5.jpg', 9, 11100.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(123, 'Accessoire déco 1', '../../public/images/products/1742646567_dec 1.jpg', 10, 20000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(124, 'Accessoire déco 2', '../../public/images/products/1742646581_dec 2.jpg', 10, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(125, 'Accessoire déco 3', '../../public/images/products/1742646592_dec 3.jpg', 10, 15000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(126, 'Accessoire déco 4', '../../public/images/products/1742646624_dec 4.jpg', 10, 18000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(127, 'Accessoire déco 5', '../../public/images/products/1742646639_dec 5.jpg', 10, 18000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(128, 'Accessoire déco 6', '../../public/images/products/1742646655_dec 6.jpg', 10, 30000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(129, 'Accessoire déco 7', '../../public/images/products/1742646675_dec 7.jpg', 10, 12000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(130, 'Accessoire déco 8', '../../public/images/products/1742646686_dec.jpg', 10, 12000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(131, 'Composition florale 6', '../../public/images/products/1742648240_cf 5.jpg', 9, 25000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(132, 'Composition florale 7', '../../public/images/products/1742648258_cf 6.jpg', 9, 25000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(133, 'Composition florale 8', '../../public/images/products/1742648268_cf 7.jpg', 9, 25000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(134, 'Composition florale 9', '../../public/images/products/1742648282_cf 8.jpg', 9, 25000.00, 'Aucune description disponible', 0, 0, 0.00, 0.00),
(135, 'Couronnes Royales', '../../public/images/products/1742656781_cor 2.jpg', 3, 10.00, 'test test', 0, 0, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Structure de la table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id_service` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image_service` varchar(255) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_service`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `services`
--

INSERT INTO `services` (`id_service`, `nom_service`, `description`, `image_service`, `is_deleted`) VALUES
(1, 'Mariage', 'Pour toutes vos cérémonies de Mariages, ChezFlora vous accompagne jusqu\' au bout ', '../../public/images/services/1742647123_deco 1jpg.jpg', 0),
(2, 'Anniverssaire', 'Pour toutes vos cérémonies d\'anniverssaire, ChezFlora vous accompagne jusqu\' au bout', '../../public/images/services/1742647164_deco 1.jpg', 0),
(3, 'Evénements d\'entreprises', 'Pour toutes vos événements d\'entreprises, ChezFlora vous accompagne jusqu\' au bout', '../../public/images/services/1742647962_eve3.jpg', 0),
(4, 'Galerie Photo', 'Pour toutes vos photos, ChezFlora vous accompagne jusqu\' au bout', '../../public/images/services/1742648187_g2.jpg', 0);

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slogan` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `a_propos_entreprise` text NOT NULL,
  `date_modification` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`id`, `slogan`, `description`, `a_propos_entreprise`, `date_modification`) VALUES
(1, 'Fleurissez vos moments spéciaux avec ChezFlora', 'ChezFlora est votre partenaire pour des créations florales uniques et personnalisées, adaptées à tous vos événements : mariages, anniversaires, et plus encore.', 'ChezFlora a été fondée avec une passion pour les fleurs et un engagement envers la satisfaction de nos clients. Depuis nos débuts, nous avons transformé des milliers d’événements en moments inoubliables grâce à nos arrangements floraux sur mesure. Notre équipe d’experts travaille avec vous pour comprendre vos besoins et créer des compositions qui reflètent votre style et votre vision.', '2025-03-22 06:25:04');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `is_deleted` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `email`, `password`, `is_deleted`) VALUES
(1, 'client@flora.com', 'client1234', '0'),
(3, 'ramanabdou507@gmail.com', '1234', '0'),
(4, 'hbadir@gmail.com', '1234', '0');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
