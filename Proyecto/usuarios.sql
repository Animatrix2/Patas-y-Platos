-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 16-08-2024 a las 01:23:34
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
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`IdHorarios`, `Hora`, `Minuto`, `ID_Usuario`) VALUES
(59, 15, 50, 11),
(58, 10, 20, 11),
(51, 12, 0, 7),
(52, 13, 30, 7),
(53, 12, 30, 7),
(57, 23, 5, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `porciones`
--

DROP TABLE IF EXISTS `porciones`;
CREATE TABLE IF NOT EXISTS `porciones` (
  `ID_Usuario` int NOT NULL AUTO_INCREMENT,
  `Porcion` int NOT NULL,
  PRIMARY KEY (`ID_Usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `porciones`
--

INSERT INTO `porciones` (`ID_Usuario`, `Porcion`) VALUES
(11, 5),
(7, 3);

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
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(6, 'Ramierez', '$2y$10$TTEHBBZyDQYjxoIyfgdhm.EuJs4FbU7P3eBj/1Ohl0aa3HWx7lcOu'),
(4, 'Gonzalo', '123456'),
(10, 'Lopez', '123456'),
(7, 'XD', '$2y$10$1egcjEIuHP8AtDd.eVm7Te77Pdws/WDq/aQCflf2Puoey6nJsYH/u'),
(11, 'main teemo', '$2y$10$VILJQSxRwpvvXxNQzBttd.TyRrncEhZTdfe/sENOQxlhSZcl7DBoK');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
