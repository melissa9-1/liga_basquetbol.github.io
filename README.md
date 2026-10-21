# Liga de Básquetbol

Aplicación web en PHP con una arquitectura MVC ligera, sin dependencias de framework. Requiere PHP 8.1 o superior y MySQL/MariaDB.

## Ejecutar en XAMPP

1. Copia el proyecto en `htdocs` y activa Apache y MySQL.
2. Opcionalmente configura `DB_HOST`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`. Los valores predeterminados son `localhost`, `liga_basquetbol`, `root` y contraseña vacía.
3. Abre `http://localhost/liga/`. La aplicación crea la base de datos y aplica las migraciones al iniciar.
4. Registra una cuenta desde el acceso administrativo de la página principal.

También puedes aplicar las migraciones desde la terminal con `php database/migrate.php`. Las rutas de administración requieren iniciar sesión y las operaciones POST validan un token CSRF.
Para insertar equipos de demostración en una liga vacía, ejecuta `php database/seed.php`.

## Estructura

- `app/Models/`: acceso a usuarios, equipos, jugadores y partidos.
- `app/Http/Controllers/`: controladores de páginas y operaciones.
- `app/Http/Requests/`: validación de formularios.
- `database/migrations/`: creación del esquema e importación de tablas antiguas en español.
- `database/seeders/` y `database/factories/`: datos de ejemplo opcionales.
- `resources/views/`: páginas PHP y parciales.
- `routes/web.php`: definición de rutas.
- `index.php`: punto de entrada HTTP.

Las tablas `users`, `teams`, `players` y `matches` conservan el esquema anterior. No se necesita una migración destructiva; si existen las tablas antiguas `usuarios`, `equipos`, `jugadores` y `partidos`, se importan a las tablas actuales sin duplicar registros coincidentes.
