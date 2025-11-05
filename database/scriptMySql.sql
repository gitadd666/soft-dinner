CREATE DATABASE IF NOT EXISTS soft_dinner;
USE soft_dinner;

-- 1. Usuarios del sistema
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL
    
);

-- 2. Mesas del restaurante
CREATE TABLE IF NOT EXISTS mesas (
    id_mesa INT AUTO_INCREMENT PRIMARY KEY,
    numero_mesa INT NOT NULL UNIQUE,
    numero_clientes INT DEFAULT 0,
    estado_mesa ENUM('LIBRE', 'OCUPADA', 'RESERVADA') DEFAULT 'LIBRE',
    estado_pedido ENUM('SIN_PEDIDOS', 'EN_PREPARACION', 'ENTREGADOS') DEFAULT 'SIN_PEDIDOS'
);

-- 3. Productos (Platillos y Bebidas)
CREATE TABLE IF NOT EXISTS productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50),
    tipo ENUM('platillo','bebida') NOT NULL,
    precio DECIMAL(10,2) NOT NULL
);

-- 4. Ordenes (pedido de una mesa)
CREATE TABLE IF NOT EXISTS ordenes (
    id_orden INT AUTO_INCREMENT PRIMARY KEY,
    id_mesa INT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'en_preparacion', 'servido', 'pagado') DEFAULT 'pendiente',
    FOREIGN KEY (id_mesa) REFERENCES mesas(id_mesa),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

-- 5. Detalle de cada orden (qué se pidió)
CREATE TABLE IF NOT EXISTS detalle_orden (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

-- Trigger para calcular subtotal automáticamente
DELIMITER //
CREATE TRIGGER calcular_subtotal
BEFORE INSERT ON detalle_orden
FOR EACH ROW
BEGIN
    DECLARE precio_producto DECIMAL(10,2);
    SELECT precio INTO precio_producto FROM productos WHERE id_producto = NEW.id_producto;
    SET NEW.subtotal = NEW.cantidad * precio_producto;
END;
//
DELIMITER ;

-- Trigger para actualizar subtotal al modificar cantidad o producto
DELIMITER //
CREATE TRIGGER actualizar_subtotal
BEFORE UPDATE ON detalle_orden
FOR EACH ROW
BEGIN
    DECLARE precio_producto DECIMAL(10,2);
    SELECT precio INTO precio_producto FROM productos WHERE id_producto = NEW.id_producto;
    SET NEW.subtotal = NEW.cantidad * precio_producto;
END;
//
DELIMITER ;

-- 6. Recibos (cuentas finales)
CREATE TABLE IF NOT EXISTS recibos (
    id_recibo INT AUTO_INCREMENT PRIMARY KEY,
    id_orden INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo','tarjeta','transferencia') DEFAULT 'efectivo',
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orden) REFERENCES ordenes(id_orden)
);
insert into Usuarios values (1, "vidal", "vidal@gmail.com", "654321");
