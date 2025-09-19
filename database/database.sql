CREATE DATABASE SoftDinner;

USE SoftDinner;

CREATE TABLE Recibo 
(
  idRecibo INT AUTO_INCREMENT PRIMARY KEY,
  fechaRecibo DATE NOT NULL,
  idOrden INT NOT NULL,
  idCliente INT NOT NULL,
  FOREIGN KEY (idOrden) REFERENCES Ordenes(idOrden),
  FOREIGN KEY (idCliente) REFERENCES Cliente(idCliente)
);

CREATE TABLE Mesa 
(
  idMesa INT AUTO_INCREMENT PRIMARY KEY,
  estatusMesa ENUM('Disponible', 'Ocupada', 'Reservada') NOT NULL,
  estatusPedidoMesa ENUM('Pendiente', 'En Proceso', 'Servido') NOT NULL,
  numeroClientes TINYINT UNSIGNED NOT NULL
);

CREATE TABLE Cliente 
(
  idCliente INT AUTO_INCREMENT PRIMARY KEY,
  fechaCompra DATE
);

CREATE TABLE reporteGanancia 
(
  idCostos INT AUTO_INCREMENT PRIMARY KEY,
  costAguaSemanal DECIMAL(10,2) NOT NULL,
  costRentaSemanal DECIMAL(10,2) NOT NULL,
  costInsumosSemanal DECIMAL(10,2) NOT NULL,
  idRecibo INT NOT NULL,
  FOREIGN KEY (idRecibo) REFERENCES Recibo(idRecibo)
);

CREATE TABLE cuentaUsuario 
(
  idUser INT AUTO_INCREMENT PRIMARY KEY,
  correoElectronico VARCHAR(100) UNIQUE NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Platillo 
(
  idPlatillo INT AUTO_INCREMENT PRIMARY KEY,
  precio DECIMAL(10,2) NOT NULL,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Bebida 
(
  idBebida INT AUTO_INCREMENT PRIMARY KEY,
  precio DECIMAL(10,2) NOT NULL,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Ordenes 
(
  idOrden INT AUTO_INCREMENT PRIMARY KEY,
  idBebida INT,
  idPlatillo INT,
  idMesa INT,
  FOREIGN KEY (idBebida) REFERENCES Bebida(idBebida),
  FOREIGN KEY (idPlatillo) REFERENCES Platillo(idPlatillo),
  FOREIGN KEY (idMesa) REFERENCES Mesa(idMesa)
);
