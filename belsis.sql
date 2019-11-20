-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  mer. 20 nov. 2019 à 12:01
-- Version du serveur :  10.1.30-MariaDB
-- Version de PHP :  7.2.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `belsis`
--

-- --------------------------------------------------------

--
-- Structure de la table `appareil`
--

CREATE TABLE `appareil` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `marque` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imei` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_serie` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `appareil`
--

INSERT INTO `appareil` (`id`, `restaurant_id`, `marque`, `imei`, `type`, `num_serie`) VALUES
(1, 1, 'Samsung', '987456321025896', 'Tablette', '987456321025896'),
(2, 2, 'EPSON', '963258741597684', 'Imprimante', '963258741597684');

-- --------------------------------------------------------

--
-- Structure de la table `blog_post`
--

CREATE TABLE `blog_post` (
  `id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `blog_post_user`
--

CREATE TABLE `blog_post_user` (
  `blog_post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `image_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_size` int(11) DEFAULT NULL,
  `image_dimensions` longtext COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:simple_array)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `restaurant_id`, `nom`, `description`, `updated_at`, `image_name`, `image_original_name`, `image_mime_type`, `image_size`, `image_dimensions`) VALUES
(1, 1, 'Vin Rouge', 'Vin Rouge', '2019-11-13 11:15:09', 'depositphotos_119634224-stock-illustration-restaurant-logo-cutlery-design.jpg', 'depositphotos_119634224-stock-illustration-restaurant-logo-cutlery-design.jpg', 'image/jpeg', 54758, '1024,1024'),
(2, 1, 'Plats chauds', 'Plats chauds', '2019-11-13 11:12:02', 'default-image-categorie.jpg', 'default-image-categorie.jpg', 'image/jpeg', 33458, '500,375'),
(3, 1, 'Poisson et crustacé', 'Poisson et crustacé', '2019-10-02 18:21:51', 'depositphotos_87620648-stock-illustration-apply-form-icon.jpg', 'depositphotos_87620648-stock-illustration-apply-form-icon.jpg', 'image/jpeg', 57628, '1024,1024'),
(4, 1, 'Crudités', 'imageFile', '2019-10-02 18:22:11', 'le-titre-restaurant.jpg', 'le-titre-restaurant.jpg', 'image/jpeg', 10486, '800,800');

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id` int(11) NOT NULL,
  `modepaiement_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `etat` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id`, `modepaiement_id`, `restaurant_id`, `table_id`, `code`, `date`, `etat`, `user_id`) VALUES
(1, 1, 1, 1, 'cmd001', '2019-11-12 10:00:00', '', 1),
(2, 1, 1, 1, 'cmd002', '2019-11-14 00:00:00', 'paye', 1),
(8, NULL, 1, 1, 'cmd003', '2019-11-15 16:40:57', 'en_cours', 1),
(9, NULL, 1, 1, 'cmd005', '2019-11-15 17:03:06', 'en_cours', 1);

-- --------------------------------------------------------

--
-- Structure de la table `commande_produit`
--

CREATE TABLE `commande_produit` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_produit`
--

INSERT INTO `commande_produit` (`id`, `produit_id`, `commande_id`, `quantite`, `prix`) VALUES
(1, 5, 1, 2, 15000),
(2, 4, 1, 1, 20000),
(3, 5, 2, 3, 15000),
(4, 4, 9, 2, 25000),
(5, 5, 9, 2, 25000);

-- --------------------------------------------------------

--
-- Structure de la table `howard_access_token`
--

CREATE TABLE `howard_access_token` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `howard_access_token`
--

INSERT INTO `howard_access_token` (`id`, `client_id`, `user_id`, `token`, `expires_at`, `scope`) VALUES
(12, 6, 1, 'ZTZlZjVlZmIxYzgyOTJjZDBlOGY3MTc0ZWQ2M2FhYWE0ZjZlMjkxYzEwNTYyMzEyMTBhZjdhMmM1ODA4ZjBmNQ', 1574245596, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `howard_auth_code`
--

CREATE TABLE `howard_auth_code` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uri` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `howard_client`
--

CREATE TABLE `howard_client` (
  `id` int(11) NOT NULL,
  `random_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirect_uris` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowed_grant_types` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `howard_client`
--

INSERT INTO `howard_client` (`id`, `random_id`, `redirect_uris`, `secret`, `allowed_grant_types`) VALUES
(6, 'pe2my5pp9o0oww40c4okc8csg4ggcgsg48gkcokok8gkkw48k', 'a:1:{i:0;s:16:\"http://127.0.0.1\";}', '54myv8rrrkowso4kggsckok0ookkcsw0oko4w48owwk080kkwk', 'a:2:{i:0;s:8:\"password\";i:1;s:13:\"refresh_token\";}');

-- --------------------------------------------------------

--
-- Structure de la table `howard_refresh_token`
--

CREATE TABLE `howard_refresh_token` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `token` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `scope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `howard_refresh_token`
--

INSERT INTO `howard_refresh_token` (`id`, `client_id`, `user_id`, `token`, `expires_at`, `scope`) VALUES
(12, 6, 1, 'MTVlMmQzOTY1NDYwYTQwN2FhY2M1ODkwNWVlZTNhZjczNzliYzdjOGEwODIwZDc0ZTFlZWY0YjQwZDQyZDk4Yg', 1575455136, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `migration_versions`
--

CREATE TABLE `migration_versions` (
  `version` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migration_versions`
--

INSERT INTO `migration_versions` (`version`, `executed_at`) VALUES
('20190926104707', '2019-09-26 10:50:47'),
('20191001091606', '2019-10-01 09:19:18'),
('20191002151439', '2019-10-02 15:16:42'),
('20191002152337', '2019-10-02 15:25:17'),
('20191002161549', '2019-10-02 16:17:05'),
('20191112131416', '2019-11-12 13:18:35'),
('20191114160006', '2019-11-14 16:01:43'),
('20191115152843', '2019-11-15 15:30:56');

-- --------------------------------------------------------

--
-- Structure de la table `mode_paiement`
--

CREATE TABLE `mode_paiement` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mode_paiement`
--

INSERT INTO `mode_paiement` (`id`, `restaurant_id`, `nom`, `code`) VALUES
(1, 1, 'CASH', 'CASH');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`id`, `restaurant_id`, `nom`, `prix`, `categorie_id`, `image`) VALUES
(4, 1, 'Baron de Madrid', 20000, 1, NULL),
(5, 1, 'Baron de la vallée', 15000, 1, NULL),
(8, 1, 'émincés de tomates joyeuses farcies 123', 7555, 2, 'test123.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `produit_stock`
--

CREATE TABLE `produit_stock` (
  `produit_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produit_stock`
--

INSERT INTO `produit_stock` (`produit_id`, `stock_id`) VALUES
(4, 1),
(5, 2),
(8, 6);

-- --------------------------------------------------------

--
-- Structure de la table `restaurant`
--

CREATE TABLE `restaurant` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `devise` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `restaurant`
--

INSERT INTO `restaurant` (`id`, `nom`, `adresse`, `logo`, `devise`) VALUES
(1, 'Eat and Drink', 'Yaoundé', 'test.jpg', 'Euro'),
(2, 'Meat house', 'Paris', 'logo.png', 'Euro'),
(3, 'Fast Food', 'Bastos', '123.jpg', 'FCFA');

-- --------------------------------------------------------

--
-- Structure de la table `sortie_caisse`
--

CREATE TABLE `sortie_caisse` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sortie_caisse`
--

INSERT INTO `sortie_caisse` (`id`, `restaurant_id`, `date`, `description`, `montant`) VALUES
(1, 3, '2014-01-01 00:00:00', 'Facture Eau', 15000);

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantite` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `restaurant_id`, `nom`, `quantite`) VALUES
(1, 1, 'Baron de Madrid', 20),
(2, 1, 'Baron de la vallée', 30),
(3, 1, 'portion de poulet', 16),
(4, 1, 'pistache à l\'ail', 11),
(5, 1, 'pistache à l\'ail', 10),
(6, 1, 'émincés de tomates farcies', 257);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `username` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username_canonical` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_canonical` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `salt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `confirmation_token` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_requested_at` datetime DEFAULT NULL,
  `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:array)',
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `restaurant_id`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `nom`, `prenom`) VALUES
(1, 1, 'tester', 'tester', 'test00@ausiteodit.com', 'test00@ausiteodit.com', 1, NULL, '$2y$13$jE2w8keooa9CTruyA8j/Guafx3tgN37TwYpWu6X3mUGs9go2zDR32', '2019-11-20 10:24:33', NULL, NULL, 'a:1:{i:0;s:1:\"2\";}', 'new-tester', 'test test');

-- --------------------------------------------------------

--
-- Structure de la table `user00`
--

CREATE TABLE `user00` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `_table`
--

CREATE TABLE `_table` (
  `id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coord_x` double NOT NULL,
  `coord_y` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `_table`
--

INSERT INTO `_table` (`id`, `restaurant_id`, `nom`, `description`, `coord_x`, `coord_y`) VALUES
(1, 1, 'Table 1', 'Table 1', 15, 26),
(2, 2, 'Table 1', 'Table 1', 85, 26);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `appareil`
--
ALTER TABLE `appareil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_456A601AB1E7706E` (`restaurant_id`);

--
-- Index pour la table `blog_post`
--
ALTER TABLE `blog_post`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `blog_post_user`
--
ALTER TABLE `blog_post_user`
  ADD PRIMARY KEY (`blog_post_id`,`user_id`),
  ADD KEY `IDX_E1B8590DA77FBEAF` (`blog_post_id`),
  ADD KEY `IDX_E1B8590DA76ED395` (`user_id`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_497DD634B1E7706E` (`restaurant_id`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6EEAA67D8CDA5193` (`modepaiement_id`),
  ADD KEY `IDX_6EEAA67DB1E7706E` (`restaurant_id`),
  ADD KEY `IDX_6EEAA67DECFF285C` (`table_id`),
  ADD KEY `IDX_6EEAA67DA76ED395` (`user_id`);

--
-- Index pour la table `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_DF1E9E87F347EFB` (`produit_id`),
  ADD KEY `IDX_DF1E9E8782EA2E54` (`commande_id`);

--
-- Index pour la table `howard_access_token`
--
ALTER TABLE `howard_access_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_A2B81E8C5F37A13B` (`token`),
  ADD KEY `IDX_A2B81E8C19EB6921` (`client_id`),
  ADD KEY `IDX_A2B81E8CA76ED395` (`user_id`);

--
-- Index pour la table `howard_auth_code`
--
ALTER TABLE `howard_auth_code`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_33C1262B5F37A13B` (`token`),
  ADD KEY `IDX_33C1262B19EB6921` (`client_id`),
  ADD KEY `IDX_33C1262BA76ED395` (`user_id`);

--
-- Index pour la table `howard_client`
--
ALTER TABLE `howard_client`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `howard_refresh_token`
--
ALTER TABLE `howard_refresh_token`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_603C1D375F37A13B` (`token`),
  ADD KEY `IDX_603C1D3719EB6921` (`client_id`),
  ADD KEY `IDX_603C1D37A76ED395` (`user_id`);

--
-- Index pour la table `migration_versions`
--
ALTER TABLE `migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `mode_paiement`
--
ALTER TABLE `mode_paiement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B2BB0E85B1E7706E` (`restaurant_id`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_29A5EC27B1E7706E` (`restaurant_id`),
  ADD KEY `IDX_29A5EC27BCF5E72D` (`categorie_id`);

--
-- Index pour la table `produit_stock`
--
ALTER TABLE `produit_stock`
  ADD PRIMARY KEY (`produit_id`,`stock_id`),
  ADD KEY `IDX_7BAA31F4F347EFB` (`produit_id`),
  ADD KEY `IDX_7BAA31F4DCD6110` (`stock_id`);

--
-- Index pour la table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `sortie_caisse`
--
ALTER TABLE `sortie_caisse`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_B5579974B1E7706E` (`restaurant_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4B365660B1E7706E` (`restaurant_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D64992FC23A8` (`username_canonical`),
  ADD UNIQUE KEY `UNIQ_8D93D649A0D96FBF` (`email_canonical`),
  ADD UNIQUE KEY `UNIQ_8D93D649C05FB297` (`confirmation_token`),
  ADD KEY `IDX_8D93D649B1E7706E` (`restaurant_id`);

--
-- Index pour la table `user00`
--
ALTER TABLE `user00`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `_table`
--
ALTER TABLE `_table`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7C1163DAB1E7706E` (`restaurant_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `appareil`
--
ALTER TABLE `appareil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `blog_post`
--
ALTER TABLE `blog_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `commande_produit`
--
ALTER TABLE `commande_produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `howard_access_token`
--
ALTER TABLE `howard_access_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `howard_auth_code`
--
ALTER TABLE `howard_auth_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `howard_client`
--
ALTER TABLE `howard_client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `howard_refresh_token`
--
ALTER TABLE `howard_refresh_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `mode_paiement`
--
ALTER TABLE `mode_paiement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `restaurant`
--
ALTER TABLE `restaurant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `sortie_caisse`
--
ALTER TABLE `sortie_caisse`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `user00`
--
ALTER TABLE `user00`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `_table`
--
ALTER TABLE `_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `appareil`
--
ALTER TABLE `appareil`
  ADD CONSTRAINT `FK_456A601AB1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);

--
-- Contraintes pour la table `blog_post_user`
--
ALTER TABLE `blog_post_user`
  ADD CONSTRAINT `FK_E1B8590DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_E1B8590DA77FBEAF` FOREIGN KEY (`blog_post_id`) REFERENCES `blog_post` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD CONSTRAINT `FK_497DD634B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `FK_6EEAA67D8CDA5193` FOREIGN KEY (`modepaiement_id`) REFERENCES `mode_paiement` (`id`),
  ADD CONSTRAINT `FK_6EEAA67DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_6EEAA67DB1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`),
  ADD CONSTRAINT `FK_6EEAA67DECFF285C` FOREIGN KEY (`table_id`) REFERENCES `_table` (`id`);

--
-- Contraintes pour la table `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD CONSTRAINT `FK_DF1E9E8782EA2E54` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`id`),
  ADD CONSTRAINT `FK_DF1E9E87F347EFB` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`);

--
-- Contraintes pour la table `howard_access_token`
--
ALTER TABLE `howard_access_token`
  ADD CONSTRAINT `FK_A2B81E8C19EB6921` FOREIGN KEY (`client_id`) REFERENCES `howard_client` (`id`),
  ADD CONSTRAINT `FK_A2B81E8CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `howard_auth_code`
--
ALTER TABLE `howard_auth_code`
  ADD CONSTRAINT `FK_33C1262B19EB6921` FOREIGN KEY (`client_id`) REFERENCES `howard_client` (`id`),
  ADD CONSTRAINT `FK_33C1262BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `howard_refresh_token`
--
ALTER TABLE `howard_refresh_token`
  ADD CONSTRAINT `FK_603C1D3719EB6921` FOREIGN KEY (`client_id`) REFERENCES `howard_client` (`id`),
  ADD CONSTRAINT `FK_603C1D37A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `mode_paiement`
--
ALTER TABLE `mode_paiement`
  ADD CONSTRAINT `FK_B2BB0E85B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `FK_29A5EC27B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`),
  ADD CONSTRAINT `FK_29A5EC27BCF5E72D` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `produit_stock`
--
ALTER TABLE `produit_stock`
  ADD CONSTRAINT `FK_7BAA31F4DCD6110` FOREIGN KEY (`stock_id`) REFERENCES `stock` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_7BAA31F4F347EFB` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sortie_caisse`
--
ALTER TABLE `sortie_caisse`
  ADD CONSTRAINT `FK_B5579974B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `FK_4B365660B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D649B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);

--
-- Contraintes pour la table `_table`
--
ALTER TABLE `_table`
  ADD CONSTRAINT `FK_7C1163DAB1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
