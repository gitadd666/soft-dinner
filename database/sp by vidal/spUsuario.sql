use soft_dinner_ultimate_omega
go

create procedure sp_usuario
@op tinyint,
@id_usuario int = 0,
@nombre varchar (100) = null,  /* se ha de poner como = a null o = "" para los valores que pueden llegar a ser nulos */
@apellido_primero varchar (100) = null,
@apellido_segundo varchar (100) = null,
@correo varchar (255) = null,
@contrasenia varchar (255) = null,
@activo bit = null
as
begin

if @op =1
begin
select * from dbo.usuario where id_usuario = @id_usuario
end

if @op = 2
begin
	if not exists (select * from dbo.usuario where id_usuario = @id_usuario)
	begin
		declare @maxid int = (select isnull(max(id_usuario), 0) + 1 from dbo.usuario) /*revisa si el id no es null,
		si lo es lo convierte a 0, vheca el max ve que es 0 le suma 1, si el id no es nulo toma el id maximo y le suma 1 para obetner el sig*/

		insert into dbo.usuario
		values (@maxid, @nombre, @apellido_primero, @apellido_segundo, @correo, @contrasenia, @activo)
	end

	else

	begin
		update dbo.usuario
		set nombre = @nombre,
		apellido_primero = @apellido_primero,
		apellido_segundo = @apellido_segundo,
		correo = @correo,
		contrasenia = @contrasenia,
		activo = @activo
		where id_usuario = @id_usuario
	end

end

if @op = 3

begin
delete from dbo.usuario where id_usuario = @id_usuario
end

end

go

use soft_dinner_ultimate_omega

exec sp_usuario
@op =3,
@id_usuario = 6,
@nombre = 'bianca',  
@apellido_primero = 'verdugo',
@apellido_segundo = 'arredondo',
@correo = 'bianca@gmail.com',
@contrasenia = '123456',
@activo = 1

select * from usuario

