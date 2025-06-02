-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 02 juin 2025 à 20:21
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
-- Base de données : `shopcessory`
--
CREATE DATABASE IF NOT EXISTS `shopcessory` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `shopcessory`;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=283 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `user_id`, `title`, `description`, `price`, `image_path`, `created_at`, `updated_at`) VALUES
(233, 4, 'Bracelet Unique Série 284', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 36b8a7ef5d)', 357.48, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(234, 3, 'Chronomètre de Collection Série 633', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 64402b349b)', 106.30, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(235, 3, 'Bague Majestueuse Série 172', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 254a3534c4)', 286.74, 'sample1.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(236, 2, 'Collier Splendide Série 663', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 7c0dbe838d)', 157.75, 'sample1.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(237, 2, 'Boucles d\'Oreilles Élégantes Série 191', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 1b70974e7d)', 141.90, 'sample3.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(238, 3, 'Boucles d\'Oreilles Élégantes Série 387', 'Alliant robustesse et finesse, cet accessoire est conçu pour durer. Un excellent investissement pour un style impeccable. (Référence unique: 2495f7ad47)', 118.37, 'sample3.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(239, 1, 'Bijou Fait Main Série 285', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: d04e49760a)', 240.02, 'sample3.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(240, 3, 'Collier Splendide Série 630', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 9a39c09d80)', 151.04, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(241, 2, 'Montre Raffinée Série 248', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 361df03897)', 354.14, 'sample3.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(242, 3, 'Boucles d\'Oreilles Élégantes Série 867', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 1a66776efc)', 345.05, 'sample1.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(243, 2, 'Bague Majestueuse Série 607', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 1351c1a273)', 163.32, 'sample3.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(244, 3, 'Bracelet Unique Série 254', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: b4e1bb97bf)', 94.74, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(245, 4, 'Montre Raffinée Série 144', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 195c91262b)', 128.87, 'sample1.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(246, 4, 'Collier Splendide Série 785', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 7f564a6bb5)', 363.40, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(247, 3, 'Boucles d\'Oreilles Élégantes Série 687', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: a783df2ae1)', 309.17, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(248, 4, 'Bijou Fait Main Série 945', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 06731f1a6e)', 58.91, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(249, 3, 'Bague Majestueuse Série 307', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 36af4813b5)', 315.67, 'sample1.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(250, 3, 'Chronomètre de Collection Série 277', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 04780f0ba3)', 63.36, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(251, 1, 'Collier Splendide Série 619', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 094e696e07)', 67.35, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(252, 2, 'Pendentif Original Série 737', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 8a8ec1ad19)', 446.77, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(253, 2, 'Chronomètre de Collection Série 865', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 5f5d60c0c9)', 259.96, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(254, 3, 'Pendentif Original Série 370', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 0b0f9f0bc8)', 420.56, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(255, 2, 'Boucles d\'Oreilles Élégantes Série 175', 'Alliant robustesse et finesse, cet accessoire est conçu pour durer. Un excellent investissement pour un style impeccable. (Référence unique: 4ab502643e)', 360.85, 'sample2.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(256, 3, 'Accessoire Tendance Série 213', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 8ddb3321aa)', 138.02, 'sample4.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(257, 2, 'Pendentif Original Série 565', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 38c2a01d44)', 354.48, 'sample3.jpeg', '2025-06-02 16:50:47', '2025-06-02 16:50:47'),
(258, 2, 'Montre Raffinée Série 895', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: b349f381d5)', 322.18, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(259, 2, 'Bague Majestueuse Série 653', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 7b3ce8cd43)', 253.97, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(260, 1, 'Chronomètre de Collection Série 317', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 594b6a822b)', 157.89, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(261, 3, 'Bracelet Unique Série 917', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: c511ce42b4)', 288.03, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(262, 3, 'Montre Raffinée Série 668', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 39f7964139)', 393.60, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(263, 3, 'Boucles d\'Oreilles Élégantes Série 181', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 8b4a46a51b)', 483.17, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(264, 1, 'Accessoire Tendance Série 123', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 05a43f6e9a)', 228.71, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(265, 1, 'Bague Majestueuse Série 335', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 8a5d6b6772)', 26.49, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(266, 1, 'Boucles d\'Oreilles Élégantes Série 937', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 749ff3c6cd)', 361.66, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(267, 1, 'Collier Splendide Série 617', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: bd1161f328)', 79.22, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(268, 1, 'Accessoire Tendance Série 396', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: c6754014f4)', 474.58, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(269, 2, 'Pendentif Original Série 155', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 4011632fdb)', 482.29, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(270, 3, 'Bague Majestueuse Série 496', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 4a96a8d216)', 334.77, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(271, 1, 'Accessoire Tendance Série 442', 'Alliant robustesse et finesse, cet accessoire est conçu pour durer. Un excellent investissement pour un style impeccable. (Référence unique: fdad7d6627)', 463.27, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(272, 2, 'Boucles d\'Oreilles Élégantes Série 820', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: 2493ba3c3f)', 425.04, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(273, 2, 'Pendentif Original Série 755', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: 7bd4b23402)', 260.57, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(274, 3, 'Boucles d\'Oreilles Élégantes Série 741', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: bbdc55a6f2)', 328.20, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(275, 1, 'Montre Raffinée Série 219', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 1f10797515)', 489.18, 'sample4.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(276, 1, 'Accessoire Tendance Série 273', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: f58700519d)', 474.83, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(277, 2, 'Accessoire Tendance Série 400', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: 9d7192256a)', 464.02, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(278, 2, 'Montre Raffinée Série 733', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 72659cc575)', 402.81, 'sample3.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(279, 3, 'Chronomètre de Collection Série 867', 'Léger, confortable et discret, cet article est un plaisir à porter. C\'est aussi une excellente idée de cadeau. (Référence unique: e06425fb07)', 221.53, 'sample4.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(280, 2, 'Bijou Fait Main Série 367', 'Pièce de créateur au design exclusif, produite en série limitée. Une opportunité unique d\'acquérir un objet d\'exception. (Référence unique: 35faf0b1f0)', 64.13, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(281, 3, 'Chronomètre de Collection Série 113', 'Affirmez votre style avec cet accessoire au design moderne et audacieux. Idéal pour le quotidien ou les soirées. (Référence unique: e0894b342b)', 316.00, 'sample2.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39'),
(282, 4, 'Montre Raffinée Série 726', 'Un véritable bijou de technologie et d\'élégance, parfait pour toutes les occasions. Matériaux nobles et finition soignée. (Référence unique: e3b9289dc2)', 255.05, 'sample1.jpeg', '2025-06-02 16:51:39', '2025-06-02 16:51:39');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `reset_token_hash` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `password`, `role`, `reset_token_hash`, `reset_token_expires_at`, `created_at`) VALUES
(1, 'test', 'test', 'test', 'julienartisien63@gmail.com', '$2y$10$jzsgmK1Rk87mIEo1MB24sO84iL9hliVkkKG6JRaupmh6lDXO/WJ12', 'admin', NULL, NULL, '2025-06-02 13:39:38'),
(2, 'Alice', 'Martin', 'alicem', 'alice.martin@example.com', '$2y$10$DzWQN6.Iqzv6p.1SwATWEOs0QxIQgXAfGUNZzuUvt8Y3qCHrrhBGS', 'user', NULL, NULL, '2025-06-02 15:16:03'),
(3, 'Bob', 'Durand', 'bobd', 'bob.durand@example.com', '$2y$10$j4b.ZjNLr5N7.nKiz8FmYuiyeh80bD39kFdQklWVM/IFsvyascRU2', 'user', NULL, NULL, '2025-06-02 15:16:03'),
(4, 'Charles', 'Dupont', 'charlied', 'charles.d@example.com', '$2y$10$m.3x4fT/1hFc6Nxoc5FqWuq9cs38ulKxEeEtH3sBpjTcO9ve9NmB6', 'admin', NULL, NULL, '2025-06-02 15:16:04'),
(5, 'Sophie', 'Lefevre', 'sophiel', 'sophie.lefevre@example.com', '$2y$10$E16e9FjfzroAU.1tNNZb0O.t7UECdXXHXUGZ9NzKTr3Afq3FBufny', 'user', NULL, NULL, '2025-06-02 19:06:32'),
(6, 'Lucas', 'Moreau', 'lucasm', 'lucas.moreau@example.com', '$2y$10$FZIONoNPYxbDYKiOZvVvk./uhIceL74/XVzDUlzzr3IVeJRrWM5gS', 'user', NULL, NULL, '2025-06-02 19:06:32'),
(7, 'Chloe', 'Girard', 'chloeg', 'chloe.girard@example.com', '$2y$10$L66qLLr5jtt/a7uA4PWmQuueFWiyOp1rnKJL.Buv8RLZUpSds7YI.', 'user', NULL, NULL, '2025-06-02 19:06:32'),
(8, 'Gabriel', 'Petit', 'gabrielp', 'gabriel.petit@example.com', '$2y$10$WO7XctqqOzM0avgPVkh78uY4Ak//uMyv0Je47RcmXmsEna6ZtZVu2', 'admin', NULL, NULL, '2025-06-02 19:06:32'),
(9, 'Manon', 'Roux', 'manonr', 'manon.roux@example.com', '$2y$10$QHK/gBRx15fn.yFbU.TMw.B57GMvyC12V5PQL3E7MZzCZwWXNiIn.', 'user', NULL, NULL, '2025-06-02 19:06:33'),
(10, 'Louis', 'David', 'louisd', 'louis.david@example.com', '$2y$10$5vIp0Ga8sD1nbBy.l/tBXuEUxeyUDJLwemWnl7qOdxnL/fu1Ng8Wq', 'user', NULL, NULL, '2025-06-02 19:06:33'),
(11, 'Emma', 'Bertrand', 'emmab', 'emma.bertrand@example.com', '$2y$10$5ddWGJPQzbsVAYdXjTHVGOy2fkFvLsh9OSpw1DgeDK6G.SzKPpDwK', 'user', NULL, NULL, '2025-06-02 19:06:33'),
(12, 'Arthur', 'Lambert', 'arthurl', 'arthur.lambert@example.com', '$2y$10$DLmoYDIq2FjE9S1TYwTlzOyMDmhi4EYHNKIpBO0lU6E8PuF3rLEw6', 'user', NULL, NULL, '2025-06-02 19:06:33'),
(13, 'Alice', 'Fontaine', 'alicef', 'alice.fontaine@example.com', '$2y$10$UcNgDv/IXORRHJUYOjJR1eMrxCG4O/zpqSs7.eXuxGMBZcdiFiaoC', 'user', NULL, NULL, '2025-06-02 19:06:33'),
(14, 'Jules', 'Rousseau', 'julesr', 'jules.rousseau@example.com', '$2y$10$kPZOcTS//irtdUEZdaQkbOuUCdOSyWuGw6tpFb.6kd.Di9nl3BddW', 'user', NULL, NULL, '2025-06-02 19:06:33'),
(15, 'Louise', 'Vincent', 'louisev', 'louise.vincent@example.com', '$2y$10$Vjpv.9Jgm5jSCQHR9.Dc5OKi6y3VwvBTSuyB3llSGCvOU8f7WkJb6', 'user', NULL, NULL, '2025-06-02 19:06:34'),
(16, 'Hugo', 'Fournier', 'hugof', 'hugo.fournier@example.com', '$2y$10$EO03zIkHZcLod.4h6v37zeHvpKEaYMcPeNVXxtlXFvx4c.TP8QFj2', 'admin', NULL, NULL, '2025-06-02 19:06:34'),
(17, 'Lea', 'Morel', 'leam', 'lea.morel@example.com', '$2y$10$v16AhnX7JYrJrSVWyUawA.1/EIUsr4JyBwQWr3qbX/q6CExQwTvhy', 'user', NULL, NULL, '2025-06-02 19:06:34'),
(18, 'Adam', 'Andre', 'adama', 'adam.andre@example.com', '$2y$10$3Esuv4GAGuu0a5FVc/SIROgRELY3nasxiSYi812MYV1aNLcvD9clm', 'user', NULL, NULL, '2025-06-02 19:06:34'),
(19, 'Camille', 'Barbier', 'camilleb', 'camille.barbier@example.com', '$2y$10$tZnhOlO8tJxmg8fEmCHoz.ip9MDz7REgRqg6HhflWS.mSt7DZArAW', 'user', NULL, NULL, '2025-06-02 19:06:35');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
