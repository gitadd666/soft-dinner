use soft_dinner_ultimate_omega
go
create procedure sp_metodo_pago
@op tinyint,
@id_metodo_pago int = 0,
@nombre varchar (50) = null  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
as
begin

if @op =1
begin
select * from dbo.metodo_pago where id_metodo_pago = @id_metodo_pago
end

if @op = 2
begin
	if not exists (select * from dbo.metodo_pago where id_metodo_pago = @id_metodo_pago)
	begin
		declare @maxid int = (select isnull(max(id_metodo_pago), 0) + 1 from dbo.metodo_pago) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.metodo_pago
		values (@maxid, @nombre)
	end

	else

	begin
		update dbo.metodo_pago
		set nombre = @nombre
		where id_metodo_pago = @id_metodo_pago
	end

end

if @op = 3

begin
delete from dbo.metodo_pago where id_metodo_pago=@id_metodo_pago
end

end

go

use soft_dinner_ultimate_omega

exec sp_metodo_pago
@op = 3,
@id_metodo_pago =4,
@nombre = ''

select * from dbo.metodo_pago

