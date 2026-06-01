use soft_dinner_ultimate_omega
go

create procedure sp_producto
@op tinyint,
@id_producto int = 0,
@nombre varchar (150) = null,  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
@id_categoria int = 0,
@precio decimal (10,2) = null,
@descripcion varchar (500) = null
as
begin

if @op =1
begin
select * from dbo.producto where id_producto = @id_producto
end

if @op = 2
begin
	if not exists (select * from dbo.producto where id_producto = @id_producto)
	begin
		declare @maxid int = (select isnull(max(id_producto), 0) + 1 from dbo.producto) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.producto
		values (@maxid, @nombre, @id_categoria, @precio, @descripcion)
	end

	else

	begin
		update dbo.producto
		set nombre = @nombre,
		id_categoria = @id_categoria,
		precio = @precio,
		descripcion = @descripcion
		where id_producto = @id_producto
	end

end

if @op = 3

begin
delete from dbo.producto where id_producto = @id_producto
end

end

go

use soft_dinner_ultimate_omega

exec sp_producto
@op = 3,
@id_producto = 8,
@nombre = 'colacao',
@id_categoria = 1,
@precio = 40,
@descripcion = 'producto argentino'

select * from dbo.producto