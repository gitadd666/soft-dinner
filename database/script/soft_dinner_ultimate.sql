create database soft_dinner_ultimate
go

use soft_dinner_ultimate
go

create table usuario
(
id_usuario int primary key,
nombre varchar(100) not null,
apellido_primero varchar(100) not null,
apellido_segundo varchar(100),
correo varchar(255) not null,
contrasenia varchar(255) not null,
activo bit not null
)
go

create table estado_mesa
(
id_estado_mesa int primary key,
nombre varchar(50) not null
)
go

create table metodo_pago
(
id_metodo_pago int primary key,
nombre varchar(50) not null
)
go

create table estado_orden
(
id_estado_orden int primary key,
nombre varchar(50) not null
)
go

create table categoria
(
id_categoria int primary key,
nombre varchar(100) not null
)
go

create table tipo_gasto
(
id_tipo_gasto int primary key,
nombre varchar(100) not null,
descripcion varchar(255)
)
go

create table mesa
(
id_mesa int primary key,
numero_mesa int not null,
ocupada bit not null,
id_estado_mesa int not null,

constraint fk_mesa_estado_mesa
foreign key (id_estado_mesa)
references estado_mesa (id_estado_mesa)
)
go

create table producto
(
id_producto int primary key,
nombre varchar(150) not null,
id_categoria int not null,
precio decimal(10,2) not null,
descripcion varchar(500),

constraint fk_producto_categoria
foreign key (id_categoria)
references categoria (id_categoria)
)
go

create table orden
(
id_orden int primary key,
id_mesa int not null,
id_usuario int not null,
id_estado_orden int not null,
id_metodo_pago int not null,
total decimal(12,2) not null,
fecha datetime not null,

constraint fk_orden_mesa
foreign key (id_mesa)
references mesa (id_mesa),

constraint fk_orden_usuario
foreign key (id_usuario)
references usuario (id_usuario),

constraint fk_orden_estado_orden
foreign key (id_estado_orden)
references estado_orden (id_estado_orden),

constraint fk_orden_metodo_pago
foreign key (id_metodo_pago)
references metodo_pago (id_metodo_pago)
)
go

create table gasto
(
id_gasto int primary key,
id_tipo_gasto int not null,
id_usuario int not null,
fecha_inicio date not null,
fecha_final date not null,
monto decimal(12,2) not null,
descripcion varchar(200),

constraint fk_gasto_tipo_gasto
foreign key (id_tipo_gasto)
references tipo_gasto (id_tipo_gasto),

constraint fk_gasto_usuario
foreign key (id_usuario)
references usuario (id_usuario)
)
go

create table detalle_orden
(
id_detalle_orden int primary key,
id_orden int not null,
id_producto int not null,
cantidad decimal(10,2) not null,
precio_unitario decimal(10,2) not null,
subtotal decimal(12,2) not null,

constraint fk_detalle_orden_orden
foreign key (id_orden)
references orden (id_orden),

constraint fk_detalle_orden_producto
foreign key (id_producto)
references producto (id_producto)
)
go