use soft_dinner_ultimate_omega
go

alter procedure sp_mesa
@op tinyint,
@id_mesa int = 0,
@numero_mesa int = 0,
@ocupada bit = null,
@id_estado_mesa int = 0
as
begin

if @op =1
begin
select * from dbo.mesa where id_mesa = @id_mesa
end

if @op = 2
begin

	if not exists (
		select *
		from dbo.estado_mesa
		where id_estado_mesa = @id_estado_mesa
	)
	begin
		print 'el estado no existe'
		return
	end

	if not exists (select * from dbo.mesa where id_mesa = @id_mesa)
	begin
		declare @maxid int = (select isnull(max(id_mesa), 0) + 1 from dbo.mesa)

		insert into dbo.mesa
		values (@maxid, @numero_mesa, @ocupada, @id_estado_mesa)
	end

	else

	begin
		update dbo.mesa
		set numero_mesa = @numero_mesa,
			ocupada = @ocupada,
			id_estado_mesa = @id_estado_mesa
		where id_mesa = @id_mesa
	end

end	

if @op = 3

begin
delete from dbo.mesa where id_mesa = @id_mesa
end

end

go

use soft_dinner_ultimate_omega
exec sp_mesa
@op = 2,
@id_mesa = 6,
@numero_mesa = 6,
@ocupada = 0,
@id_estado_mesa = 3

select * from dbo.mesa