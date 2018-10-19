-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 11. Apr 2018 um 15:22
-- Server-Version: 10.1.26-MariaDB
-- PHP-Version: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `19911184_rapport2`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `authorization`
--

CREATE TABLE `authorization` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `authorization`
--

INSERT INTO `authorization` (`id`, `name`) VALUES
(1, 'customer'),
(2, 'admin'),
(3, 'worker');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `culture`
--

CREATE TABLE `culture` (
  `id` int(11) NOT NULL,
  `name` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `culture`
--

INSERT INTO `culture` (`id`, `name`, `updated_at`, `created_at`) VALUES
(1, 'fda', '2018-04-09 11:36:11', '2018-04-09 11:36:11'),
(2, 'dsf', '2018-04-09 11:36:11', '2018-04-09 11:36:11'),
(3, NULL, '2018-04-09 11:36:41', '2018-04-09 11:36:41'),
(4, 'd', '2018-04-09 11:37:13', '2018-04-09 11:37:13'),
(5, 'dfsf', '2018-04-09 11:37:16', '2018-04-09 11:37:16'),
(6, 'dfdsfdasf', '2018-04-09 11:39:38', '2018-04-09 11:39:38'),
(7, 'rüebli', '2018-04-09 11:39:55', '2018-04-09 11:39:55'),
(8, 'kartoffeln', '2018-04-09 13:27:20', '2018-04-09 13:27:20'),
(9, 'chirschi', '2018-04-10 07:37:20', '2018-04-10 07:37:20');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `customer`
--

CREATE TABLE `customer` (
  `id` int(11) NOT NULL,
  `firstname` text,
  `lastname` text,
  `addition` text,
  `street` text,
  `place` text,
  `plz` text,
  `mobile` text,
  `phone` text,
  `hasCatering` tinyint(1) DEFAULT NULL,
  `kitchen_infrastructure` text,
  `max_catering` int(11) DEFAULT NULL,
  `comment_catering` text,
  `driver_info` text,
  `comment` text,
  `maps` text,
  `secret` text,
  `customer_number` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `customer`
--

INSERT INTO `customer` (`id`, `firstname`, `lastname`, `addition`, `street`, `place`, `plz`, `mobile`, `phone`, `hasCatering`, `kitchen_infrastructure`, `max_catering`, `comment_catering`, `driver_info`, `comment`, `maps`, `secret`, `customer_number`, `user_id`, `updated_at`, `created_at`) VALUES
(1, 'Max', 'Muster', 'Test zusatz1', 'Musterstrasse 88', 'Musthausen', '3333', '079 111 11 11', '032 299 99 99', 1, 'sfd', 112, 'sdfdsf', 'langsam Fahren', 'bemerkugn', 'https://goo.gl/maps/BsGZHja67JC2', 'eyJpdiI6IllGZXJSM2FlNnZaUzVvdE9CT1dhQVE9PSIsInZhbHVlIjoiM0U0K3ZoTTBuY0Njem5uNFJqcm8rZz09IiwibWFjIjoiNjhlMTFiNmMyMGU0Mzk2MzZhYzFiZWRmMmM2YzA4Y2IwZDY2ZGQxNjM4YmViOWEyMWU2YTgyMjIxMGExZmY5OCJ9', 123, 2, '2018-04-09 15:21:41', '2018-04-09 11:32:53'),
(2, 'TEst', 'tester', NULL, 'ksfk', 'fsdkla', '23', NULL, NULL, 1, 'sfsa', 23423, 'sdfsf', NULL, NULL, NULL, 'eyJpdiI6IjZaeUdjR05NZ3U3UWNkeHpqYVYrTGc9PSIsInZhbHVlIjoiS2VFWUZIblNXMG1vTytBM243V3A5Zz09IiwibWFjIjoiMDIyOTdhOGZhYWZiMzY1MzNiNWNkYmRiMTA1ZjFjNmEzYTRlYzRiMjk2ZTY5NDg0Y2M1NjI3MDM5YWY3NGEzNiJ9', NULL, 3, '2018-04-09 15:23:44', '2018-04-09 15:23:44');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `entry_exit`
--

CREATE TABLE `entry_exit` (
  `id` int(11) NOT NULL,
  `personal_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `isEntry` tinyint(1) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `hourrecords`
--

CREATE TABLE `hourrecords` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `culture_id` int(11) DEFAULT NULL,
  `week` int(11) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `hours` double DEFAULT NULL,
  `comment` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `hourrecords`
--

INSERT INTO `hourrecords` (`id`, `customer_id`, `culture_id`, `week`, `year`, `hours`, `comment`, `updated_at`, `created_at`) VALUES
(1, 1, 7, 19, 2018, 12, 'sdf', '2018-04-09 11:39:55', '2018-04-09 11:36:11'),
(3, 1, 7, 35, 2018, 6, NULL, '2018-04-09 13:27:20', '2018-04-09 13:27:20'),
(4, 1, 8, 36, 2018, 35, 'testbemerkung', '2018-04-09 13:27:20', '2018-04-09 13:27:20'),
(5, 1, 8, 31, 2018, 200, 'sehr viel', '2018-04-11 12:54:41', '2018-04-09 13:29:15'),
(6, 1, 6, 32, 2018, 23, '2', '2018-04-09 13:39:01', '2018-04-09 13:29:36'),
(7, 2, 9, 19, 2018, 20, NULL, '2018-04-10 07:37:20', '2018-04-10 07:37:20'),
(8, 2, 9, 20, 2018, 50, NULL, '2018-04-10 07:37:20', '2018-04-10 07:37:20'),
(9, 2, 7, 35, 2018, 100, 'test', '2018-04-10 07:37:20', '2018-04-10 07:37:20'),
(10, 1, 7, 21, 2018, 21, 'test', '2018-04-11 12:33:06', '2018-04-11 12:33:06');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `call_name` text,
  `firstname` text,
  `lastname` text,
  `nationality` text,
  `isIntern` tinyint(1) DEFAULT NULL,
  `isDriver` tinyint(1) DEFAULT NULL,
  `german_knowledge` tinyint(1) DEFAULT NULL,
  `english_knowledge` tinyint(1) DEFAULT NULL,
  `sex` text,
  `comment` text,
  `experience` text,
  `isActive` tinyint(1) DEFAULT NULL,
  `profileimage` text,
  `allergy` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `email` text,
  `username` text,
  `firstname` text,
  `lastname` text,
  `authorization_id` int(11) DEFAULT NULL,
  `password` text,
  `ismealdefault` tinyint(1) DEFAULT NULL,
  `remember_token` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `email`, `username`, `firstname`, `lastname`, `authorization_id`, `password`, `ismealdefault`, `remember_token`, `updated_at`, `created_at`) VALUES
(1, 'admin@outlook.com', 'admin', 'Admin', 'Muster', 2, '$2y$10$MJV/WP5/RAc41AbD/kg/xeOvmxixCHfh5B/MReXJu8HMecKEv2CeS', NULL, '4vzL50NWzdKgAvOxs3323OfSgVOxULM5v5gSNgUSPyMt1ichO5dU1TFB8bdp', '2018-04-09 00:00:00', '2018-04-09 00:00:00'),
(2, 'max.muster@outlook.com', 'max.muster', 'Max', 'Muster', 1, '$2y$10$08c7slEsQDCUnWwdHkSa6OXJ/7JwzEsw9qetGwAZfVJ2YcxhkgZES', NULL, 'L3lYIp2KbBjWEJ2R4QXVSU03K4rgJt2a1GedACCZ4MzvfWfRg97RTTMr72AD', '2018-04-09 11:32:53', '2018-04-09 11:32:52'),
(3, NULL, 'test.tester', 'TEst', 'tester', 1, '$2y$10$w5ar7Ft0SaB7boaWJCFE1Odh05bIoyK8Q2cjz8Pvf/Ag4YYWfxLPK', NULL, 'uDf3BaXcCEtjFAllejMcjxrY5Aih6pdyREKQwuOXQhwBUtwDJTeaPElqWQTa', '2018-04-09 15:23:44', '2018-04-09 15:23:44');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `workplace`
--

CREATE TABLE `workplace` (
  `id` int(11) NOT NULL,
  `name` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `workplace_personal`
--

CREATE TABLE `workplace_personal` (
  `workplace_id` int(11) NOT NULL,
  `personal_id` int(11) NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `authorization`
--
ALTER TABLE `authorization`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `culture`
--
ALTER TABLE `culture`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `entry_exit`
--
ALTER TABLE `entry_exit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`);

--
-- Indizes für die Tabelle `hourrecords`
--
ALTER TABLE `hourrecords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `culture_id` (`culture_id`);

--
-- Indizes für die Tabelle `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `authorization_id` (`authorization_id`);

--
-- Indizes für die Tabelle `workplace`
--
ALTER TABLE `workplace`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `workplace_personal`
--
ALTER TABLE `workplace_personal`
  ADD PRIMARY KEY (`workplace_id`,`personal_id`),
  ADD KEY `personal_id` (`personal_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `authorization`
--
ALTER TABLE `authorization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `culture`
--
ALTER TABLE `culture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT für Tabelle `customer`
--
ALTER TABLE `customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `entry_exit`
--
ALTER TABLE `entry_exit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `hourrecords`
--
ALTER TABLE `hourrecords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT für Tabelle `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `workplace`
--
ALTER TABLE `workplace`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Constraints der Tabelle `entry_exit`
--
ALTER TABLE `entry_exit`
  ADD CONSTRAINT `entry_exit_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`);

--
-- Constraints der Tabelle `hourrecords`
--
ALTER TABLE `hourrecords`
  ADD CONSTRAINT `hourrecords_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`),
  ADD CONSTRAINT `hourrecords_ibfk_2` FOREIGN KEY (`culture_id`) REFERENCES `culture` (`id`);

--
-- Constraints der Tabelle `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`authorization_id`) REFERENCES `authorization` (`id`);

--
-- Constraints der Tabelle `workplace_personal`
--
ALTER TABLE `workplace_personal`
  ADD CONSTRAINT `workplace_personal_ibfk_1` FOREIGN KEY (`workplace_id`) REFERENCES `workplace` (`id`),
  ADD CONSTRAINT `workplace_personal_ibfk_2` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
