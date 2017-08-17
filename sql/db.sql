-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-08-2017 a las 02:06:42
-- Versión del servidor: 10.1.19-MariaDB
-- Versión de PHP: 5.6.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lerp2dev_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lerp2dev_actions`
--

CREATE TABLE `lerp2dev_actions` (
  `id` int(11) NOT NULL,
  `prefix` text NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lerp2dev_apps`
--

CREATE TABLE `lerp2dev_apps` (
  `id` int(11) NOT NULL,
  `prefix` text NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lerp2dev_authkey`
--

CREATE TABLE `lerp2dev_authkey` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `auth_key` varchar(32) NOT NULL,
  `instance_key` varchar(32) NOT NULL,
  `creation_date` datetime NOT NULL,
  `valid_for` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lerp2dev_badges`
--

CREATE TABLE `lerp2dev_badges` (
  `id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `icon_url` text NOT NULL,
  `coins_reward` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lerp2dev_users`
--

CREATE TABLE `lerp2dev_users` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `ip` text NOT NULL,
  `creation_date` datetime NOT NULL,
  `last_activity` datetime NOT NULL,
  `conn_time` int(11) NOT NULL,
  `coins_balance` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lerp2dev_user_log`
--

CREATE TABLE `lerp2dev_user_log` (
  `id` int(11) NOT NULL,
  `action_id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `lerp2dev_actions`
--
ALTER TABLE `lerp2dev_actions`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lerp2dev_apps`
--
ALTER TABLE `lerp2dev_apps`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lerp2dev_authkey`
--
ALTER TABLE `lerp2dev_authkey`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lerp2dev_badges`
--
ALTER TABLE `lerp2dev_badges`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lerp2dev_users`
--
ALTER TABLE `lerp2dev_users`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lerp2dev_user_log`
--
ALTER TABLE `lerp2dev_user_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `hugelauncher_keys`
--
ALTER TABLE `hugelauncher_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `lerp2dev_actions`
--
ALTER TABLE `lerp2dev_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `lerp2dev_apps`
--
ALTER TABLE `lerp2dev_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `lerp2dev_authkey`
--
ALTER TABLE `lerp2dev_authkey`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `lerp2dev_badges`
--
ALTER TABLE `lerp2dev_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `lerp2dev_users`
--
ALTER TABLE `lerp2dev_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `lerp2dev_user_log`
--
ALTER TABLE `lerp2dev_user_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
