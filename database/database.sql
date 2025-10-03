CREATE DATABASE SoftDinner;
GO

USE SoftDinner;
GO

CREATE TABLE Mesa 
(
  idMesa INT IDENTITY(1,1) PRIMARY KEY,
  estatusMesa VARCHAR(20) NOT NULL CHECK (estatusMesa IN ('Disponible', 'Ocupada', 'Reservada')),
  estatusPedidoMesa VARCHAR(20) NOT NULL CHECK (estatusPedidoMesa IN ('Pendiente', 'En Proceso', 'Servido')),
  numeroClientes TINYINT NOT NULL
);

CREATE TABLE Cliente 
(
  idCliente INT IDENTITY(1,1) PRIMARY KEY,
  fechaCompra DATE
);

CREATE TABLE cuentaUsuario 
(
  idUser INT IDENTITY(1,1) PRIMARY KEY,
  correoElectronico VARCHAR(100) UNIQUE NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Platillo 
(
  idPlatillo INT IDENTITY(1,1) PRIMARY KEY,
  precio DECIMAL(10,2) NOT NULL,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Bebida 
(
  idBebida INT IDENTITY(1,1) PRIMARY KEY,
  precio DECIMAL(10,2) NOT NULL,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE Ordenes 
(
  idOrden INT IDENTITY(1,1) PRIMARY KEY,
  idBebida INT,
  idPlatillo INT,
  idMesa INT,
  FOREIGN KEY (idBebida) REFERENCES Bebida(idBebida),
  FOREIGN KEY (idPlatillo) REFERENCES Platillo(idPlatillo),
  FOREIGN KEY (idMesa) REFERENCES Mesa(idMesa)
);

CREATE TABLE Recibo 
(
  idRecibo INT IDENTITY(1,1) PRIMARY KEY,
  fechaRecibo DATE NOT NULL,
  idOrden INT NOT NULL,
  idCliente INT NOT NULL,
  FOREIGN KEY (idOrden) REFERENCES Ordenes(idOrden),
  FOREIGN KEY (idCliente) REFERENCES Cliente(idCliente)
);

CREATE TABLE reporteGanancia 
(
  idCostos INT IDENTITY(1,1) PRIMARY KEY,
  costAguaSemanal DECIMAL(10,2) NOT NULL,
  costRentaSemanal DECIMAL(10,2) NOT NULL,
  costInsumosSemanal DECIMAL(10,2) NOT NULL,
  idRecibo INT NOT NULL,
  FOREIGN KEY (idRecibo) REFERENCES Recibo(idRecibo)
);
