use soft_dinner_ultimate_omega
go
create procedure sp_estado_mesa
@op tinyint,
@id_estado_mesa int = 0,
@nombre varchar (50) = null  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
as
begin

if @op =1
begin
select * from dbo.estado_mesa where id_estado_mesa = @id_estado_mesa
end

if @op = 2
begin
	if not exists (select * from dbo.estado_mesa where id_estado_mesa = @id_estado_mesa)
	begin
		declare @maxid int = (select isnull(max(id_estado_mesa), 0) + 1 from dbo.estado_mesa) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.estado_mesa
		values (@maxid, @nombre)
	end

	else

	begin
		update dbo.estado_mesa
		set nombre = @nombre
		where id_estado_mesa = @id_estado_mesa
	end

end

if @op = 3

begin
delete from dbo.estado_mesa where id_estado_mesa = @id_estado_mesa
end

end

go

use soft_dinner_ultimate_omega

exec sp_estado_mesa
@op = 3,
@id_estado_mesa =4,
@nombre = 'no definido'

select * from dbo.estado_mesa

