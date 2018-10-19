-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 23. Mrz 2018 um 23:05
-- Server-Version: 10.1.28-MariaDB
-- PHP-Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `rapportsystem`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `arbeitsort`
--

CREATE TABLE `arbeitsort` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `arbeitsort_mitarbeiter`
--

CREATE TABLE `arbeitsort_mitarbeiter` (
  `arbeitsort_id` int(11) NOT NULL,
  `mitarbeiter_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `arbeitstyp`
--

CREATE TABLE `arbeitstyp` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `berechtigung`
--

CREATE TABLE `berechtigung` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `berechtigung`
--

INSERT INTO `berechtigung` (`id`, `name`) VALUES
(1, 'kunde'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `einstellungen`
--

CREATE TABLE `einstellungen` (
  `id` int(11) NOT NULL,
  `ferienstunden` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eintritt_austritt`
--

CREATE TABLE `eintritt_austritt` (
  `id` int(11) NOT NULL,
  `mitarbeiter_id` int(11) DEFAULT NULL,
  `datum` datetime DEFAULT NULL,
  `isEintritt` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kultur`
--

CREATE TABLE `kultur` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunde`
--

CREATE TABLE `kunde` (
  `id` int(11) NOT NULL,
  `vorname` text,
  `nachname` text,
  `zusatz` text,
  `strasse` text,
  `ort` text,
  `mobile` text,
  `festnetz` text,
  `verpflegung` text,
  `kuechen_ausstattung` text,
  `max_verpflegung` int(11) DEFAULT NULL,
  `fahrer_info` text,
  `bemerkung` text,
  `maps` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunde_projekt`
--

CREATE TABLE `kunde_projekt` (
  `projekt_id` int(11) NOT NULL,
  `kunde_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `mitarbeiter`
--

CREATE TABLE `mitarbeiter` (
  `id` int(11) NOT NULL,
  `rufname` text,
  `vorname` text,
  `nachname` text,
  `nationalität` text,
  `isIntern` tinyint(1) DEFAULT NULL,
  `isFahrer` tinyint(1) DEFAULT NULL,
  `deutschkenntnisse` tinyint(1) DEFAULT NULL,
  `englischkenntnisse` tinyint(1) DEFAULT NULL,
  `geschlecht` text,
  `bemerkung` text,
  `erfahrung` text,
  `isAktiv` tinyint(1) DEFAULT NULL,
  `profilbild` text,
  `allergie` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `projekt`
--

CREATE TABLE `projekt` (
  `id` int(11) NOT NULL,
  `name` text,
  `beschreibung` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rapportdetail`
--

CREATE TABLE `rapportdetail` (
  `id` int(11) NOT NULL,
  `wochenrapport_id` int(11) DEFAULT NULL,
  `projekt_id` int(11) DEFAULT NULL,
  `mitarbeiter_id` int(11) DEFAULT NULL,
  `verpflegungsart_id` int(11) DEFAULT NULL,
  `stunden` double DEFAULT NULL,
  `bemerkung` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stunden`
--

CREATE TABLE `stunden` (
  `id` int(11) NOT NULL,
  `zeiterfassung_id` int(11) DEFAULT NULL,
  `von` time DEFAULT NULL,
  `bis` time DEFAULT NULL,
  `arbeitstyp_id` int(11) DEFAULT NULL,
  `bemerkung` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stundenangaben`
--

CREATE TABLE `stundenangaben` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `kultur_id` int(11) DEFAULT NULL,
  `monatsanfang` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` text,
  `vorname` text,
  `nachname` text,
  `berechtigung_id` int(11) DEFAULT NULL,
  `password` text,
  `ismahlzeit_default` tinyint(1) DEFAULT NULL,
  `remember_token` text,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `email`, `vorname`, `nachname`, `berechtigung_id`, `password`, `ismahlzeit_default`, `remember_token`, `updated_at`, `created_at`) VALUES
(12, 'benhu00@outlook.com', 'Benaja', 'Hunzinger', 1, '$2y$10$OfNyzH2mFpnzetv17TcTauSsqClpvdWgYGNep8zoaZ3YP/DM0./s6', NULL, 'r6o8KRiTrP8pQHCeVwGrMCdIUkOMlqsuMWwXzAw5ZmwasKWPo1ofJv5PIx4X', '2018-03-20 20:10:05', '2018-03-20 20:10:05'),
(13, 'admin@outlook.com', 'admin', 'admin', 2, '$2y$10$MJV/WP5/RAc41AbD/kg/xeOvmxixCHfh5B/MReXJu8HMecKEv2CeS', NULL, '1Nk8Xd9zBdewRqK7YZbdy5hXVPBnYyeRDSFv5bX0mqIpIi16avOUPdP3n4ua', '2018-03-20 20:10:31', '2018-03-20 20:10:31');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_kunde`
--

CREATE TABLE `user_kunde` (
  `user_id` int(11) NOT NULL,
  `kunde_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `verpflegungsart`
--

CREATE TABLE `verpflegungsart` (
  `id` int(11) NOT NULL,
  `name` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `wochenrapport`
--

CREATE TABLE `wochenrapport` (
  `id` int(11) NOT NULL,
  `kunde_id` int(11) DEFAULT NULL,
  `isabgeschlossen` tinyint(1) DEFAULT NULL,
  `datum` date DEFAULT NULL,
  `bemerkung` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `zeiterfassung`
--

CREATE TABLE `zeiterfassung` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `datum` date DEFAULT NULL,
  `mitagessen` tinyint(1) DEFAULT NULL,
  `bemerkung` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `arbeitsort`
--
ALTER TABLE `arbeitsort`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `arbeitsort_mitarbeiter`
--
ALTER TABLE `arbeitsort_mitarbeiter`
  ADD PRIMARY KEY (`arbeitsort_id`,`mitarbeiter_id`),
  ADD KEY `mitarbeiter_id` (`mitarbeiter_id`);

--
-- Indizes für die Tabelle `arbeitstyp`
--
ALTER TABLE `arbeitstyp`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `berechtigung`
--
ALTER TABLE `berechtigung`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `einstellungen`
--
ALTER TABLE `einstellungen`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `eintritt_austritt`
--
ALTER TABLE `eintritt_austritt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mitarbeiter_id` (`mitarbeiter_id`);

--
-- Indizes für die Tabelle `kultur`
--
ALTER TABLE `kultur`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kunde`
--
ALTER TABLE `kunde`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `kunde_projekt`
--
ALTER TABLE `kunde_projekt`
  ADD PRIMARY KEY (`projekt_id`,`kunde_id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `projekt`
--
ALTER TABLE `projekt`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `rapportdetail`
--
ALTER TABLE `rapportdetail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wochenrapport_id` (`wochenrapport_id`),
  ADD KEY `projekt_id` (`projekt_id`),
  ADD KEY `mitarbeiter_id` (`mitarbeiter_id`),
  ADD KEY `verpflegungsart_id` (`verpflegungsart_id`);

--
-- Indizes für die Tabelle `stunden`
--
ALTER TABLE `stunden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zeiterfassung_id` (`zeiterfassung_id`),
  ADD KEY `arbeitstyp_id` (`arbeitstyp_id`);

--
-- Indizes für die Tabelle `stundenangaben`
--
ALTER TABLE `stundenangaben`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kultur_id` (`kultur_id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `berechtigung` (`berechtigung_id`);

--
-- Indizes für die Tabelle `user_kunde`
--
ALTER TABLE `user_kunde`
  ADD PRIMARY KEY (`user_id`,`kunde_id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `verpflegungsart`
--
ALTER TABLE `verpflegungsart`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `wochenrapport`
--
ALTER TABLE `wochenrapport`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kunde_id` (`kunde_id`);

--
-- Indizes für die Tabelle `zeiterfassung`
--
ALTER TABLE `zeiterfassung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `arbeitsort`
--
ALTER TABLE `arbeitsort`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `arbeitstyp`
--
ALTER TABLE `arbeitstyp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `berechtigung`
--
ALTER TABLE `berechtigung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `einstellungen`
--
ALTER TABLE `einstellungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `eintritt_austritt`
--
ALTER TABLE `eintritt_austritt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kultur`
--
ALTER TABLE `kultur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `kunde`
--
ALTER TABLE `kunde`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `mitarbeiter`
--
ALTER TABLE `mitarbeiter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `projekt`
--
ALTER TABLE `projekt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `rapportdetail`
--
ALTER TABLE `rapportdetail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `stunden`
--
ALTER TABLE `stunden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `stundenangaben`
--
ALTER TABLE `stundenangaben`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT für Tabelle `verpflegungsart`
--
ALTER TABLE `verpflegungsart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `wochenrapport`
--
ALTER TABLE `wochenrapport`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `zeiterfassung`
--
ALTER TABLE `zeiterfassung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `arbeitsort_mitarbeiter`
--
ALTER TABLE `arbeitsort_mitarbeiter`
  ADD CONSTRAINT `arbeitsort_mitarbeiter_ibfk_1` FOREIGN KEY (`arbeitsort_id`) REFERENCES `arbeitsort` (`id`),
  ADD CONSTRAINT `arbeitsort_mitarbeiter_ibfk_2` FOREIGN KEY (`mitarbeiter_id`) REFERENCES `mitarbeiter` (`id`);

--
-- Constraints der Tabelle `eintritt_austritt`
--
ALTER TABLE `eintritt_austritt`
  ADD CONSTRAINT `eintritt_austritt_ibfk_1` FOREIGN KEY (`mitarbeiter_id`) REFERENCES `mitarbeiter` (`id`);

--
-- Constraints der Tabelle `kunde_projekt`
--
ALTER TABLE `kunde_projekt`
  ADD CONSTRAINT `kunde_projekt_ibfk_1` FOREIGN KEY (`projekt_id`) REFERENCES `projekt` (`id`),
  ADD CONSTRAINT `kunde_projekt_ibfk_2` FOREIGN KEY (`kunde_id`) REFERENCES `kunde` (`id`);

--
-- Constraints der Tabelle `rapportdetail`
--
ALTER TABLE `rapportdetail`
  ADD CONSTRAINT `rapportdetail_ibfk_1` FOREIGN KEY (`wochenrapport_id`) REFERENCES `wochenrapport` (`id`),
  ADD CONSTRAINT `rapportdetail_ibfk_2` FOREIGN KEY (`projekt_id`) REFERENCES `projekt` (`id`),
  ADD CONSTRAINT `rapportdetail_ibfk_3` FOREIGN KEY (`mitarbeiter_id`) REFERENCES `mitarbeiter` (`id`),
  ADD CONSTRAINT `rapportdetail_ibfk_4` FOREIGN KEY (`verpflegungsart_id`) REFERENCES `verpflegungsart` (`id`);

--
-- Constraints der Tabelle `stunden`
--
ALTER TABLE `stunden`
  ADD CONSTRAINT `stunden_ibfk_1` FOREIGN KEY (`zeiterfassung_id`) REFERENCES `zeiterfassung` (`id`),
  ADD CONSTRAINT `stunden_ibfk_2` FOREIGN KEY (`arbeitstyp_id`) REFERENCES `arbeitstyp` (`id`);

--
-- Constraints der Tabelle `stundenangaben`
--
ALTER TABLE `stundenangaben`
  ADD CONSTRAINT `stundenangaben_ibfk_1` FOREIGN KEY (`kultur_id`) REFERENCES `kultur` (`id`);

--
-- Constraints der Tabelle `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`berechtigung_id`) REFERENCES `berechtigung` (`id`);

--
-- Constraints der Tabelle `user_kunde`
--
ALTER TABLE `user_kunde`
  ADD CONSTRAINT `user_kunde_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_kunde_ibfk_2` FOREIGN KEY (`kunde_id`) REFERENCES `kunde` (`id`);

--
-- Constraints der Tabelle `wochenrapport`
--
ALTER TABLE `wochenrapport`
  ADD CONSTRAINT `wochenrapport_ibfk_1` FOREIGN KEY (`kunde_id`) REFERENCES `kunde` (`id`);

--
-- Constraints der Tabelle `zeiterfassung`
--
ALTER TABLE `zeiterfassung`
  ADD CONSTRAINT `zeiterfassung_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
