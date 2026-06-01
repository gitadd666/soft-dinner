use soft_dinner_ultimate_omega
go
create procedure sp_tipo_gasto
@op tinyint,
@id_tipo_gasto int = 0,
@nombre varchar (100) = null,  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
@descripcion varchar (255) = null
as
begin

if @op =1
begin
select * from dbo.tipo_gasto where id_tipo_gasto = @id_tipo_gasto
end

if @op = 2
begin
	if not exists (select * from dbo.tipo_gasto where id_tipo_gasto = @id_tipo_gasto)
	begin
		declare @maxid int = (select isnull(max(id_tipo_gasto), 0) + 1 from dbo.tipo_gasto) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.tipo_gasto
		values (@maxid, @nombre, @descripcion)
	end

	else

	begin
		update dbo.tipo_gasto
		set nombre = @nombre, 
		descripcion=@descripcion
		where id_tipo_gasto = @id_tipo_gasto
	end

end

if @op = 3

begin
delete from dbo.tipo_gasto where id_tipo_gasto=@id_tipo_gasto
end

end

go

use soft_dinner_ultimate_omega

exec sp_tipo_gasto
@op = 2,
@id_tipo_gasto =2,
@nombre = 'agua',
@descripcion = 'gasto de agua conagua'

select * from tipo_gasto
