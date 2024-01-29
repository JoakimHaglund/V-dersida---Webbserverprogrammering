-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Värd: 127.0.0.1
-- Tid vid skapande: 23 jul 2021 kl 03:03
-- Serverversion: 10.4.14-MariaDB
-- PHP-version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `forecast`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `data`
--

CREATE TABLE `data` (
  `pressure` float NOT NULL,
  `temp` float NOT NULL,
  `cloudArea` float NOT NULL,
  `humidity` float NOT NULL,
  `windDirection` float NOT NULL,
  `windSpeed` float NOT NULL,
  `locRef` int(5) NOT NULL,
  `timeStamp` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellstruktur `location`
--

CREATE TABLE `location` (
  `id` int(3) NOT NULL,
  `locationName` varchar(30) NOT NULL,
  `lat` text NOT NULL,
  `lng` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `data`
--
ALTER TABLE `data`
  ADD PRIMARY KEY (`timeStamp`),
  ADD UNIQUE KEY `timeStamp` (`timeStamp`),
  ADD KEY `locRef` (`locRef`);

--
-- Index för tabell `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `locationName` (`locationName`);

--
-- AUTO_INCREMENT för dumpade tabeller
--

--
-- AUTO_INCREMENT för tabell `location`
--
ALTER TABLE `location`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=387;

--
-- Restriktioner för dumpade tabeller
--

--
-- Restriktioner för tabell `data`
--
ALTER TABLE `data`
  ADD CONSTRAINT `data_ibfk_1` FOREIGN KEY (`locRef`) REFERENCES `location` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
