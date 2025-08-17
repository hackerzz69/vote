-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 23, 2019 at 03:23 AM
-- Server version: 5.7.27-0ubuntu0.16.04.1
-- PHP Version: 7.2.22-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `foxyvote`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `super_admin` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_login` bigint(20) DEFAULT NULL,
  `last_ip` varchar(255) DEFAULT NULL,
  `mfa_secret` varchar(255) DEFAULT NULL,
  `scopes` text DEFAULT NULL,
  `created` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `super_admin`, `enabled`, `last_login`, `last_ip`, `mfa_secret`, `scopes`, `created`) VALUES
(8, 'King Fox', '$2y$10$ZrZJ5bXBYdoaIY/qgZoI3utiKkx9JWvD.lamVoozR.d6JdtB/l6qO', 1, 1, 1569227016, '::1', NULL, '{\"admin\": [\"index\", \"voters\", \"mfa\", \"votes\"], \"links\": [\"add\", \"index\", \"edit\", \"delete\", \"toggle\"], \"users\": [\"index\", \"delete\", \"edit\", \"add\"]}', 1569144080);

-- --------------------------------------------------------

--
-- Table structure for table `users_sessions`
--

CREATE TABLE `users_sessions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `expires` int(11) NOT NULL DEFAULT '-1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_sessions`
--

INSERT INTO `users_sessions` (`id`, `user_id`, `access_key`, `ip_address`, `expires`) VALUES
(1, '8', 'KfIjmWQ106OdBg92bK373FyHL', '::1', 1569313416);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `vote_key` varchar(255) NOT NULL,
  `site_id` int(11) NOT NULL,
  `voted_on` int(11) DEFAULT '-1',
  `started_on` int(11) NOT NULL DEFAULT '-1',
  `claimed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `vote_links`
--

CREATE TABLE `vote_links` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `site_id` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vote_links`
--

INSERT INTO `vote_links` (`id`, `title`, `url`, `site_id`, `active`) VALUES
(1, 'Rune-Locus', 'http://www.runelocus.com/top-rsps-list/vote-{sid}/{incentive}', '41511', 1),
(2, 'Top100Arena', 'http://www.top100arena.com/in.asp?id={sid}&incentive={incentive}', '88957', 0),
(3, 'RSPS-List', 'http://www.rsps-list.com/index.php?a=in&u={sid}&id={incentive}', 'Azanku', 0),
(4, 'Rune-Server', 'http://www.rune-server.org/toplist.php?do=vote&sid={sid}&incentive={incentive}', '10226', 0),
(5, 'TopG', 'http://topg.org/Runescape/in-{sid}-{incentive}', '419541', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users_sessions`
--
ALTER TABLE `users_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vote_links`
--
ALTER TABLE `vote_links`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users_sessions`
--
ALTER TABLE `users_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vote_links`
--
ALTER TABLE `vote_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
