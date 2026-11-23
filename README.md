# [NOMBRE_DE_LA_APP]

Aplicación escolar en Laravel 12 para PHP 8.3 y MariaDB 11.8. El registro pide configurar Google Authenticator y guardar los códigos de recuperación antes de entrar al inicio. La página de inicio muestra el usuario y el hostname del servidor.

## Despliegue en Rocky Linux 9

1. En MariaDB, crea la base y las dos cuentas. Concede a la cuenta de la aplicación `SELECT, INSERT, UPDATE, DELETE` sobre la base. Concede a la de migraciones todos los privilegios sobre esa base.
2. En cada nodo instala PHP-FPM 8.3 con los módulos indicados para la práctica y los módulos estándar de PHP requeridos por Laravel, incluidos OpenSSL, ctype, tokenizer, fileinfo e iconv. Clona el repositorio y ejecuta:

   ```sh
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   ```

   Completa el nombre, dominio, puerto y credenciales de la cuenta de aplicación. Genera la clave **una sola vez** en el primer nodo de una instalación nueva y configura el mismo `APP_KEY` en ambos nodos. Conserva la clave en despliegues posteriores. Ambos nodos usan la misma base, sesiones, caché y nombre de aplicación.

   ```sh
   php artisan key:generate
   ```

3. Ejecuta las migraciones una vez por despliegue desde un nodo, con la configuración de Laravel sin caché. Introduce las credenciales en el entorno de la consola y retíralas al terminar. Deja vacíos los valores de migración en el `.env` de PHP-FPM.

   ```bash
   php artisan config:clear
   read -r -p 'Usuario de migraciones: ' DB_MIGRATE_USERNAME
   read -rs -p 'Contraseña de migraciones: ' DB_MIGRATE_PASSWORD
   printf '\n'
   export DB_MIGRATE_USERNAME DB_MIGRATE_PASSWORD
   php artisan migrate --database=migraciones --force
   unset DB_MIGRATE_USERNAME DB_MIGRATE_PASSWORD
   ```

4. En cada nodo, configura nginx con raíz en `public/`, `try_files $uri $uri/ /index.php?$query_string` y PHP-FPM para `index.php`. Da a PHP-FPM acceso de escritura a `storage/` y `bootstrap/cache/`. Acepta tráfico web únicamente del balanceador `10.124.0.3`. En el balanceador, acepta tráfico de los rangos de Cloudflare y reconstruye los cuatro encabezados `X-Forwarded-*` con IP de cliente validada, host público, protocolo `https` y puerto `443`. Sincroniza el reloj de ambos nodos y excluye las páginas de autenticación y el inicio de la caché de Cloudflare. Ejecuta en ambos nodos:

   ```sh
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

Los assets compilados están en `public/build`. Cuando cambie el CSS de Vite, ejecuta `npm ci` y `npm run build` en desarrollo y versiona el resultado. PHP-FPM sirve la aplicación con los assets ya compilados y nginx sirve los archivos públicos.
