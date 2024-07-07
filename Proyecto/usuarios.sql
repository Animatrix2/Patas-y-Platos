-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 07-07-2024 a las 21:14:24
-- Versión del servidor: 8.2.0
-- Versión de PHP: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `usuarios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

DROP TABLE IF EXISTS `horarios`;
CREATE TABLE IF NOT EXISTS `horarios` (
  `IdHorarios` int NOT NULL AUTO_INCREMENT,
  `Hora` int NOT NULL,
  `Minuto` int NOT NULL,
  `ID_Usuario` int NOT NULL,
  PRIMARY KEY (`IdHorarios`)
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`IdHorarios`, `Hora`, `Minuto`, `ID_Usuario`) VALUES
(48, 12, 30, 0),
(47, 8, 55, 0),
(50, 13, 20, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `porciones`
--

DROP TABLE IF EXISTS `porciones`;
CREATE TABLE IF NOT EXISTS `porciones` (
  `IdPorcion` int NOT NULL AUTO_INCREMENT,
  `Porcion` int NOT NULL,
  PRIMARY KEY (`IdPorcion`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `porciones`
--

INSERT INTO `porciones` (`IdPorcion`, `Porcion`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(6, 'Ramierez', '$2y$10$TTEHBBZyDQYjxoIyfgdhm.EuJs4FbU7P3eBj/1Ohl0aa3HWx7lcOu'),
(4, 'Gonzalo', '123456'),
(10, 'Lopez', '123456'),
(7, 'XD', '$2y$10$1egcjEIuHP8AtDd.eVm7Te77Pdws/WDq/aQCflf2Puoey6nJsYH/u');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
