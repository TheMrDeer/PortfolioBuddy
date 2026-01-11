-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Jan 2026 um 15:30
-- Server-Version: 10.4.24-MariaDB
-- PHP-Version: 8.1.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Datenbank: `portfoliobuddy`
--
CREATE DATABASE IF NOT EXISTS `portfoliobuddy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `portfoliobuddy`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `isin` varchar(20) NOT NULL,
  `quantity` decimal(10,4) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `asset_type` varchar(50) DEFAULT 'Stock',
  `ticker` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `assets`
--

INSERT INTO `assets` (`id`, `user_id`, `name`, `isin`, `quantity`, `purchase_price`, `purchase_date`, `asset_type`, `ticker`) VALUES
(1, 1, 'sds', 'DE34DFDFWFWE', '10.0000', '150.34', '2300-05-22', 'stock', NULL),
(2, 1, 'asda', 'US3453454353', '100.5400', '324.34', '2000-05-22', 'stock', NULL),
(3, 1, 'sdv', 'CV1212121212', '232.0000', '0.23', '0023-03-22', 'stock', NULL),
(4, 3, 'Apple Inc', 'US1234503992', '10.0000', '304.00', '2025-12-18', 'stock', ''),
(5, 3, 'Apple Inc', 'US0378331005', '10.0000', '305.00', '2000-05-22', 'stock', 'AAPL'),
(6, 3, 'iShares Core DAX® ETF', 'DE0005933931', '10.0000', '1089.80', '2025-12-05', 'stock', 'DAXXF'),
(8, 4, 'Apple Inc', 'US0378331005', '10.0000', '150.00', '2026-01-10', 'stock', 'AAPL'),
(9, 4, 'Apple Inc', 'US0378331005', '10.0000', '150.00', '2026-01-10', 'stock', 'AAPL'),
(10, 4, 'Apple Inc', 'US0378331005', '10.0000', '150.00', '2026-01-10', 'stock', 'AAPL'),
(11, 3, 'BlabliBlub Corp.', 'DE0005933931', '21.5000', '245.21', '2026-01-11', 'stock', 'DAXXF'),
(12, 5, 'Nvidia', 'US67066G1040', '10.0000', '184.36', '2026-01-11', 'stock', 'NVDA'),
(13, 5, 'Siemens Energy', 'DE000ENER6Y0', '15.0000', '125.85', '2026-01-11', 'stock', 'ENR.DE'),
(14, 5, 'Infineon', 'DE0006231004', '9.5000', '41.00', '2026-01-11', 'stock', 'IFX.DE'),
(15, 6, 'Infineon', 'DE0006231004', '18.5000', '41.00', '2026-01-11', 'stock', 'IFX.DE'),
(16, 6, 'Siemens Energy', 'DE000ENER6Y0', '320.1200', '125.84', '2026-01-11', 'stock', 'ENR.DE'),
(17, 6, 'Micron Tech.', 'US5951121038', '98.0000', '345.09', '2025-11-05', 'stock', 'MU');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(125) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Benjamin Hirsch', 'hirsch.b.work@gmail.com', '$2y$10$eLtXU7FOrm7fiG0HSxfHRew8dnDyQ7uL1JgfYh9Y1BZNmA2mkELMi', 'user', '2025-12-05 12:41:40'),
(2, 'Falscher Hase', 'falscher.hase@apollo13.schuh', '$2y$10$o/IBu4cIH1sxdvl1eibgIOLX8EVmfde6B/qqpk8mrOGemIdehrynq', 'user', '2025-12-05 12:43:55'),
(3, 'Benjamin Hirsch', 'wi24b064@technikum-wien.at', '$2y$10$6/FgFa2DVVlb20VacAOt2ucsHSc3Fe0Q/fmTV0HQe9SjOQviyuh6y', 'admin', '2025-12-05 20:18:19'),
(4, 'Test User', 'efvadffvd@sdcksc.com', '$2y$10$K8EVa21ftBWupiNPR2aQn.x5BIJHt89wwc0cka/xX1s0GAds0FUeu', 'user', '2026-01-10 11:07:39'),
(5, 'Test User', 'testuser@testmail.com', '$2y$10$5KPxyQ9Sqsz77iLD/j77Vu.Qw/lsAfegVDmaomN87qCIL3HCSIBua', 'user', '2026-01-11 14:06:18'),
(6, 'Test Admin', 'testadmin@testmail.com', '$2y$10$4SLY8hdx3GpqcW74sLD/ou0fkvWbcA3ZQThaJBvdwqAZFUgyvBb.W', 'admin', '2026-01-11 14:12:41');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;
