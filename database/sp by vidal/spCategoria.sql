use soft_dinner_ultimate_omega
go
create procedure sp_categoria
@op tinyint,
@id_categoria int = 0,
@nombre varchar (100) = null  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
as
begin

if @op =1
begin
select * from dbo.categoria where id_categoria = @id_categoria
end

if @op = 2
begin
	if not exists (select * from dbo.categoria where id_categoria = @id_categoria)
	begin
		declare @maxid int = (select isnull(max(id_categoria), 0) + 1 from dbo.categoria) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.categoria
		values (@maxid, @nombre)
	end

	else

	begin
		update dbo.categoria
		set nombre = @nombre
		where id_categoria = @id_categoria
	end

end

if @op = 3

begin
delete from dbo.categoria where id_categoria=@id_categoria
end

end

go

use soft_dinner_ultimate_omega

exec sp_categoria
@op = 3,
@id_categoria =9,
@nombre = 'cerveza'

select * from dbo.categoria

