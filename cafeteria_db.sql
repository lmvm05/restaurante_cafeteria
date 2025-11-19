-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 19-11-2025 a las 10:23:19
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cafeteria_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comandas`
--

DROP TABLE IF EXISTS `comandas`;
CREATE TABLE IF NOT EXISTS `comandas` (
  `comanda_id` int NOT NULL AUTO_INCREMENT,
  `mesero_id` int DEFAULT NULL,
  `mesa_id` int DEFAULT NULL,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(50) DEFAULT 'Pendiente',
  `tipo_pedido` varchar(20) DEFAULT 'En Sitio',
  `total` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`comanda_id`),
  KEY `mesero_id` (`mesero_id`),
  KEY `mesa_id` (`mesa_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `comandas`
--

INSERT INTO `comandas` (`comanda_id`, `mesero_id`, `mesa_id`, `fecha_hora`, `estado`, `tipo_pedido`, `total`) VALUES
(1, 1, NULL, '2025-11-19 01:04:55', 'Pagada', 'Pickup', 85.00),
(2, 1, NULL, '2025-11-19 02:14:30', 'Pendiente', 'Pickup', 250.00),
(3, 1, NULL, '2025-11-19 02:20:09', 'Pendiente', 'Delivery', 92.00),
(4, 5, NULL, '2025-11-19 03:52:41', 'Pagada', 'Delivery', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalles_comanda`
--

DROP TABLE IF EXISTS `detalles_comanda`;
CREATE TABLE IF NOT EXISTS `detalles_comanda` (
  `detalle_id` int NOT NULL AUTO_INCREMENT,
  `comanda_id` int DEFAULT NULL,
  `producto_id` int DEFAULT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`detalle_id`),
  KEY `comanda_id` (`comanda_id`),
  KEY `producto_id` (`producto_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `detalles_comanda`
--

INSERT INTO `detalles_comanda` (`detalle_id`, `comanda_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 2, 1, 55.00, 55.00),
(2, 1, 11, 1, 30.00, 30.00),
(3, 2, 2, 1, 55.00, 55.00),
(4, 2, 8, 1, 80.00, 80.00),
(5, 2, 18, 1, 85.00, 85.00),
(6, 2, 11, 1, 30.00, 30.00),
(7, 3, 3, 1, 60.00, 60.00),
(8, 3, 15, 1, 32.00, 32.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

DROP TABLE IF EXISTS `empleados`;
CREATE TABLE IF NOT EXISTS `empleados` (
  `id_empleado` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `fecha_contratacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_empleado`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `rol`, `usuario`, `password_hash`, `fecha_contratacion`) VALUES
(2, 'Admin Principal', 'Administrador', 'admin', '$2y$10$gdQ5W/YGI8OXj.CRMPdSM.eT6p79cbiZS7rchB5gPs/xriFFhGPXe', '2025-11-19 09:04:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

DROP TABLE IF EXISTS `mesas`;
CREATE TABLE IF NOT EXISTS `mesas` (
  `mesa_id` int NOT NULL AUTO_INCREMENT,
  `numero_mesa` int NOT NULL,
  `capacidad` int DEFAULT '4',
  `estado` varchar(20) DEFAULT 'Disponible',
  PRIMARY KEY (`mesa_id`),
  UNIQUE KEY `numero_mesa` (`numero_mesa`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`mesa_id`, `numero_mesa`, `capacidad`, `estado`) VALUES
(1, 1, 2, 'Disponible'),
(2, 2, 4, 'Disponible'),
(3, 3, 2, 'Disponible'),
(4, 4, 6, 'Disponible'),
(5, 5, 4, 'Disponible'),
(6, 6, 8, 'Disponible'),
(7, 7, 2, 'Disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `meseros`
--

DROP TABLE IF EXISTS `meseros`;
CREATE TABLE IF NOT EXISTS `meseros` (
  `mesero_id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `puesto` varchar(50) DEFAULT 'Mesero',
  PRIMARY KEY (`mesero_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `meseros`
--

INSERT INTO `meseros` (`mesero_id`, `nombre`, `apellido`, `puesto`) VALUES
(1, 'Ana', 'Gómez', 'Barista'),
(2, 'Carlos', 'López', 'Mesero'),
(3, 'Sofía', 'Martínez', 'Cajero'),
(4, 'Pedro', 'Díaz', 'Mesero'),
(5, 'Laura', 'Ruiz', 'Gerente'),
(6, 'Hector', 'Martinez', 'Barista');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `producto_id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  PRIMARY KEY (`producto_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`producto_id`, `nombre`, `descripcion`, `precio`, `categoria`) VALUES
(1, 'Espresso Doble', 'Café concentrado de tueste medio.', 35.00, 'Bebidas Calientes'),
(2, 'Cappuccino', 'Espresso con leche texturizada y una capa de espuma.', 55.00, 'Bebidas Calientes'),
(3, 'Latte Vainilla', 'Espresso con leche al vapor y jarabe de vainilla.', 60.00, 'Bebidas Calientes'),
(4, 'Té Chai Latte', 'Concentrado de té negro con especias y leche al vapor.', 65.00, 'Bebidas Calientes'),
(5, 'Chocolate Caliente', 'Chocolate de la casa con leche entera.', 50.00, 'Bebidas Calientes'),
(6, 'Cold Brew', 'Café infusionado en frío por 18 horas.', 70.00, 'Bebidas Frías'),
(7, 'Iced Matcha Latte', 'Matcha japonés batido con leche fría y hielo.', 75.00, 'Bebidas Frías'),
(8, 'Frappuccino Caramelo', 'Bebida granizada de café, caramelo y crema batida.', 80.00, 'Bebidas Frías'),
(9, 'Limonada Natural', 'Jugo de limón fresco con agua mineral o natural.', 40.00, 'Bebidas Frías'),
(10, 'Smoothie de Mango', 'Bebida cremosa a base de mango y yogurt natural.', 78.00, 'Bebidas Frías'),
(11, 'Croissant de Mantequilla', 'Hojaldre clásico horneado diariamente.', 30.00, 'Repostería'),
(12, 'Muffin de Chocolate', 'Muffin esponjoso con trozos de chocolate semiamargo.', 45.00, 'Repostería'),
(13, 'Rebanada de Pastel de Zanahoria', 'Pastel húmedo con glaseado de queso crema.', 68.00, 'Repostería'),
(14, 'Scone de Queso y Romero', 'Panecillo salado ideal para acompañar el café.', 40.00, 'Repostería'),
(15, 'Dona Glaseada', 'Clásica dona cubierta con glaseado dulce.', 32.00, 'Repostería'),
(16, 'Bagel de Salmón', 'Bagel tostado con queso crema y salmón ahumado.', 120.00, 'Comida Salada'),
(17, 'Tostada de Aguacate', 'Pan de masa madre con aguacate, jitomate cherry y un toque de limón.', 95.00, 'Comida Salada'),
(18, 'Sándwich Pavo y Queso', 'Pechuga de pavo y queso panela en pan integral.', 85.00, 'Comida Salada'),
(19, 'Quiche Lorraine', 'Tarta salada rellena de tocino y queso.', 75.00, 'Comida Salada'),
(20, 'Ensalada del Día', 'Selección de verduras frescas de temporada.', 105.00, 'Comida Salada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservaciones`
--

DROP TABLE IF EXISTS `reservaciones`;
CREATE TABLE IF NOT EXISTS `reservaciones` (
  `reservacion_id` int NOT NULL AUTO_INCREMENT,
  `nombre_cliente` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `mesa_id` int DEFAULT NULL,
  `fecha_hora` datetime NOT NULL,
  `estado` varchar(20) DEFAULT 'Confirmada',
  PRIMARY KEY (`reservacion_id`),
  KEY `mesa_id` (`mesa_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
