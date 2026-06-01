use soft_dinner_ultimate_omega
go

create procedure sp_gasto
@op tinyint,
@id_gasto int = 0,
@id_tipo_gasto int = 0,
@id_usuario int = 0,
@fecha_inicio date = null,  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
@fecha_final date = null,
@monto decimal (12,2) = null,
@descripcion varchar (200) = null
as
begin

if @op =1
begin
select * from dbo.gasto where id_gasto = @id_gasto
end

if @op = 2
begin
	if not exists (select * from dbo.gasto where id_gasto = @id_gasto)
	begin
		declare @maxid int = (select isnull(max(id_gasto), 0) + 1 from dbo.gasto) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.gasto
		values (@maxid, @id_tipo_gasto, @id_usuario, @fecha_inicio, @fecha_final, @monto, @descripcion)
	end

	else

	begin
		update dbo.gasto
		set id_tipo_gasto = @id_tipo_gasto,
		id_usuario = @id_usuario,
		fecha_inicio = @fecha_inicio,
		fecha_final = @fecha_final,
		monto = @monto,
		descripcion = @descripcion
		where id_gasto = @id_gasto
	end

end

if @op = 3

begin
delete from dbo.gasto where id_gasto = @id_gasto
end

end

go

use soft_dinner_ultimate_omega

exec sp_gasto
@op = 3,
@id_gasto = 5,
@id_tipo_gasto = 1,
@id_usuario = 1,
@fecha_inicio = '2025-12-12',
@fecha_final = '2025-12-12',
@monto = 550,
@descripcion = 'utilidades'

select * from dbo.gasto