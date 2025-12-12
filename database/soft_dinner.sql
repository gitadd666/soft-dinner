-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-12-2025 a las 10:42:27
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `soft_dinner`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden`
--

CREATE TABLE `detalle_orden` (
  `id_detalle` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_orden`
--

INSERT INTO `detalle_orden` (`id_detalle`, `id_orden`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 3, 2, '51.00'),
(2, 1, 2, 2, '240.00'),
(3, 1, 4, 1, '30.00'),
(4, 1, 5, 1, '130.00'),
(5, 2, 9, 1, '50.00'),
(6, 2, 6, 1, '50.00'),
(7, 3, 7, 1, '120.00'),
(8, 3, 3, 2, '51.00'),
(9, 4, 5, 1, '130.00'),
(10, 4, 4, 2, '60.00'),
(11, 4, 10, 1, '80.00'),
(12, 5, 2, 1, '120.00'),
(13, 5, 6, 1, '50.00'),
(14, 5, 4, 2, '60.00'),
(15, 6, 2, 2, '240.00'),
(16, 6, 3, 1, '25.50'),
(17, 7, 7, 1, '120.00'),
(18, 7, 3, 1, '25.50'),
(19, 7, 11, 1, '60.00'),
(20, 7, 1, 1, '80.00'),
(21, 8, 2, 5, '600.00'),
(22, 8, 4, 1, '30.00');

--
-- Disparadores `detalle_orden`
--
DELIMITER $$
CREATE TRIGGER `actualizar_subtotal` BEFORE UPDATE ON `detalle_orden` FOR EACH ROW BEGIN
    DECLARE precio_producto DECIMAL(10,2);
    SELECT precio INTO precio_producto FROM productos WHERE id_producto = NEW.id_producto;
    SET NEW.subtotal = NEW.cantidad * precio_producto;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `calcular_subtotal` BEFORE INSERT ON `detalle_orden` FOR EACH ROW BEGIN
    DECLARE precio_producto DECIMAL(10,2);
    SELECT precio INTO precio_producto FROM productos WHERE id_producto = NEW.id_producto;
    SET NEW.subtotal = NEW.cantidad * precio_producto;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos_extra`
--

CREATE TABLE `gastos_extra` (
  `id_gastos` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `luz` decimal(10,2) DEFAULT NULL,
  `agua` decimal(10,2) DEFAULT NULL,
  `renta` decimal(10,2) DEFAULT NULL,
  `salarios` decimal(10,2) DEFAULT NULL,
  `insumos` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (ifnull(`luz`,0) + ifnull(`agua`,0) + ifnull(`renta`,0) + ifnull(`salarios`,0) + ifnull(`insumos`,0)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `gastos_extra`
--

INSERT INTO `gastos_extra` (`id_gastos`, `fecha`, `luz`, `agua`, `renta`, `salarios`, `insumos`) VALUES
(1, '2025-12-10 00:00:00', '150.00', '120.00', '1000.00', '800.00', '2000.00'),
(2, '2026-01-01 00:00:00', '1546.00', '1169.00', '333333.00', '800.00', '5.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id_mesa` int(11) NOT NULL,
  `numero_mesa` int(11) NOT NULL,
  `estado_mesa` enum('LIBRE','OCUPADA','RESERVADA') DEFAULT 'LIBRE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id_mesa`, `numero_mesa`, `estado_mesa`) VALUES
(1, 1, 'LIBRE'),
(2, 2, 'LIBRE'),
(3, 3, 'LIBRE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes`
--

CREATE TABLE `ordenes` (
  `id_orden` int(11) NOT NULL,
  `id_mesa` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','pagado') DEFAULT 'pagado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ordenes`
--

INSERT INTO `ordenes` (`id_orden`, `id_mesa`, `id_usuario`, `fecha`, `estado`) VALUES
(1, 2, 7, '2025-12-09 22:19:29', 'pagado'),
(2, 1, 7, '2025-12-09 22:32:58', 'pagado'),
(3, 3, 7, '2025-12-09 22:34:11', 'pagado'),
(4, 2, 6, '2025-12-10 01:18:08', 'pagado'),
(5, 1, 7, '2025-12-12 01:33:25', 'pagado'),
(6, 3, 7, '2025-12-12 01:49:02', 'pagado'),
(7, 2, 7, '2025-12-12 01:49:53', 'pagado'),
(8, 1, 7, '2025-12-12 01:59:26', 'pagado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('Comida','Bebida') DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `categoria`, `precio`) VALUES
(1, 'Hamburguesa', 'Comida', '80.00'),
(2, 'Pizza', 'Comida', '120.00'),
(3, 'Te Chai', 'Bebida', '25.50'),
(4, 'Coca-Cola', 'Bebida', '30.00'),
(5, 'Ceviche', 'Comida', '130.00'),
(6, 'Spritte', 'Bebida', '50.00'),
(7, 'Comida China', 'Comida', '120.00'),
(8, 'Ramen', 'Comida', '150.00'),
(9, 'Hot Dog', 'Comida', '50.00'),
(10, 'Pescado Cocido', 'Comida', '80.00'),
(11, 'Espaguetti', 'Comida', '60.00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibos`
--

CREATE TABLE `recibos` (
  `id_recibo` int(11) NOT NULL,
  `id_orden` int(11) NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta') DEFAULT 'efectivo',
  `nombre_empleado` varchar(100) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `cantidad_pagada` decimal(10,2) NOT NULL,
  `cambio` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recibos`
--

INSERT INTO `recibos` (`id_recibo`, `id_orden`, `metodo_pago`, `nombre_empleado`, `total`, `cantidad_pagada`, `cambio`, `fecha`) VALUES
(1, 1, 'tarjeta', 'Jose Eduardo Garcia Garcia', '451.00', '500.00', '49.00', '2025-12-10 00:47:54'),
(2, 3, 'efectivo', 'Jose Eduardo Garcia Garcia', '171.00', '200.00', '29.00', '2025-12-10 01:15:26'),
(3, 4, 'tarjeta', 'Eduardo Garcia Garcia', '270.00', '300.00', '30.00', '2025-12-10 01:19:16'),
(4, 2, 'efectivo', 'Jose Eduardo Garcia Garcia', '100.00', '100.00', '0.00', '2025-12-10 01:24:58'),
(5, 5, 'efectivo', 'Jose Eduardo Garcia Garcia', '230.00', '250.00', '20.00', '2025-12-12 01:50:51'),
(6, 6, 'efectivo', 'Jose Eduardo Garcia Garcia', '265.50', '300.00', '34.50', '2025-12-12 01:53:13'),
(7, 7, 'efectivo', 'Jose Eduardo Garcia Garcia', '285.50', '300.00', '14.50', '2025-12-12 01:53:40'),
(8, 8, 'tarjeta', 'Jose Eduardo Garcia Garcia', '630.00', '700.00', '70.00', '2025-12-12 02:00:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `contrasena`) VALUES
(6, 'Eduardo Garcia Garcia', 'joseeduardogarciagarcia81@gmail.com', 'ContraseñaNueva'),
(7, 'Jose Eduardo Garcia Garcia', 'joseeduardogarcua1738@gmail.com', 'Eduardo2');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_orden` (`id_orden`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `gastos_extra`
--
ALTER TABLE `gastos_extra`
  ADD PRIMARY KEY (`id_gastos`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id_mesa`),
  ADD UNIQUE KEY `numero_mesa` (`numero_mesa`);

--
-- Indices de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `id_mesa` (`id_mesa`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `recibos`
--
ALTER TABLE `recibos`
  ADD PRIMARY KEY (`id_recibo`),
  ADD KEY `id_orden` (`id_orden`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `gastos_extra`
--
ALTER TABLE `gastos_extra`
  MODIFY `id_gastos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id_mesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ordenes`
--
ALTER TABLE `ordenes`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `recibos`
--
ALTER TABLE `recibos`
  MODIFY `id_recibo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_orden`
--
ALTER TABLE `detalle_orden`
  ADD CONSTRAINT `detalle_orden_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`),
  ADD CONSTRAINT `detalle_orden_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `ordenes`
--
ALTER TABLE `ordenes`
  ADD CONSTRAINT `ordenes_ibfk_1` FOREIGN KEY (`id_mesa`) REFERENCES `mesas` (`id_mesa`),
  ADD CONSTRAINT `ordenes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `recibos`
--
ALTER TABLE `recibos`
  ADD CONSTRAINT `recibos_ibfk_1` FOREIGN KEY (`id_orden`) REFERENCES `ordenes` (`id_orden`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
