# Práctica de balanceo con Laravel 12

Este proyecto usa el starter kit oficial de Laravel para Livewire, con autenticación de Laravel Fortify y pruebas Pest. Se generó con `laravel new` desde la [revisión del kit para Laravel 12](https://github.com/laravel/livewire-starter-kit/tree/04789a1e1b211f149048f6a11a2bb040049ac88c). Activa TOTP en **Settings → Two-Factor Authentication**. La etiqueta **Atendido por** muestra el hostname también en páginas públicas. Para comprobar el balanceo, recarga la página y compara el hostname.

## Rocky Linux 9 · nginx · PHP-FPM 8.3 · MariaDB

1. Crea la base y dos usuarios. Concede al usuario de ejecución solo `SELECT, INSERT, UPDATE, DELETE` sobre esa base. Concede al de migraciones permisos para administrar el esquema.
2. En cada nodo instala las dependencias de producción y crea la configuración local.

   ```sh
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   ```

   Configura en el archivo local la contraseña de la cuenta de ejecución. Para enviar correos de recuperación y verificación, agrega las variables `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` y `MAIL_FROM_ADDRESS` de tu proveedor SMTP. En el primer nodo de una instalación nueva, ejecuta una sola vez:

   ```sh
   printf '\nAPP_KEY=\n' >> .env
   php artisan key:generate
   ```

   Configura el mismo `APP_KEY`, nombre de aplicación, base de datos y ajustes de sesión y caché en los dos nodos. En una instalación existente, conserva el `APP_KEY`.
3. Ejecuta las migraciones desde un nodo. Introduce las credenciales de migraciones temporalmente en la consola.

   ```bash
   php artisan config:clear
   read -r -p 'Usuario de migraciones: ' DB_MIGRATE_USERNAME
   read -rs -p 'Contraseña de migraciones: ' DB_MIGRATE_PASSWORD
   printf '\n'
   export DB_MIGRATE_USERNAME DB_MIGRATE_PASSWORD
   php artisan migrate --database=migraciones --force
   unset DB_MIGRATE_USERNAME DB_MIGRATE_PASSWORD
   ```

4. Usa `public/` como raíz de nginx y `try_files $uri $uri/ /index.php?$query_string`. Pasa `index.php` a PHP-FPM y concede al proceso escritura en `storage/` y `bootstrap/cache/`. Permite tráfico web a cada nodo desde el balanceador `10.124.0.3`. Configura el balanceador para aceptar tráfico de Cloudflare y establecer los cuatro encabezados `X-Forwarded-*` con IP de cliente validada, host público, protocolo HTTPS y puerto 443. Excluye de la caché perimetral las páginas y rutas dinámicas, incluida la portada. Sincroniza los relojes de ambos nodos para TOTP.
5. En cada nodo, genera las cachés de Laravel.

   ```sh
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

`public/build` contiene el resultado de `npm run build`. Ejecuta `npm ci` y `npm run build` en desarrollo al cambiar archivos de frontend y versiona el resultado. Los servidores usan los archivos compilados sin Node ni npm.
