# Liga de Básquetbol

Aplicación web PHP para administrar equipos, jugadores, partidos y estadísticas. Funciona en XAMPP con PHP 8.1 o posterior, MySQL/MariaDB y PDO MySQL.

## Ejecutar en XAMPP

1. Coloca el proyecto en `C:\xampp\htdocs\liga`.
2. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
3. Confirma que `pdo_mysql` esté habilitado en el `php.ini` que usa XAMPP.
4. Abre `http://localhost/liga/`. La aplicación crea la base `liga_basquetbol` y aplica sus migraciones al iniciar.
5. Registra una cuenta desde el acceso de la página principal. Las cuentas nuevas tienen rol de usuario normal.
6. Para asignar el rol de administrador, ejecuta en phpMyAdmin, sustituyendo `nombre_de_usuario` por la cuenta elegida:

   ```sql
   UPDATE usuarios SET rol = 'administrador' WHERE nombre_usuario = 'nombre_de_usuario';
   ```

   Confirma que la consulta afectó exactamente una cuenta. Las cuentas nuevas tienen el rol `usuario`; la migración traduce automáticamente los roles anteriores.

La conexión está en `config/conexion.php`. De forma predeterminada usa `localhost`, la base `liga_basquetbol`, el usuario `root` y contraseña vacía. Se pueden cambiar con `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`.

El esquema también está disponible en [`database/liga.sql`](database/liga.sql) para importarlo desde phpMyAdmin. Las tablas y columnas usan nombres en español. Las migraciones se ejecutan automáticamente y renombran tablas y columnas de una instalación existente sin borrar sus registros; también se pueden ejecutar desde la terminal con `php database/migrate.php`.

El archivo [`data/liga.db`](data/liga.db) se conserva únicamente como copia SQLite antigua. No es la base activa ni se importan sus datos a MySQL.

## Estructura

- `config/conexion.php`: conexión PDO a MySQL.
- `database/liga.sql` y `database/migrations/`: esquema inicial y cambios incrementales.
- `includes/`: inicio de sesión de aplicación, despacho, cabecera, pie y tabla reutilizable.
- `auth/`, `equipos/`, `jugadores/`, `partidos/` y `estadisticas/`: puntos de entrada PHP organizados por módulo.
- `app/`: controladores, validaciones, modelos y servicios internos compartidos por esos módulos.
- `resources/vistas/`: plantillas HTML.
- `assets/`: estilos y JavaScript de la interfaz.
- `index.php`: página principal y dashboard.

El registro solicita usuario, correo electrónico válido y contraseña; usuario y correo deben ser únicos. Las cuentas creadas antes de añadir el correo se conservan y pueden seguir iniciando sesión. Los administradores pueden gestionar equipos, jugadores y partidos. Los usuarios normales y los visitantes solo pueden consultar resultados y estadísticas. Los permisos de administración se validan en el servidor y las operaciones POST validan un token CSRF. Los jugadores se pueden filtrar por equipo; los partidos se programan y sus marcadores se registran por separado.
