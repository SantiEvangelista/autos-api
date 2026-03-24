# Docker - Guía de desarrollo

## Stack

| Servicio    | Imagen              | Puerto local | Puerto interno |
|-------------|---------------------|-------------|----------------|
| Nginx       | nginx:alpine        | 8080        | 80             |
| PHP-FPM     | php:8.3-fpm (custom)| -           | 9000           |
| PostgreSQL  | postgres:16-alpine  | 54320       | 5432           |
| Redis       | redis:alpine        | 63790       | 6379           |
| Queue       | php:8.3-fpm (custom)| -           | -              |
| Scheduler   | php:8.3-fpm (custom)| -           | -              |

> Los puertos locales están cambiados para no chocar con servicios nativos de Homebrew.

---

## Setup inicial (una sola vez)

```bash
# Copiar variables de entorno
cp .env.docker .env

# Buildear imágenes y levantar servicios
docker compose up -d --build

# Instalar dependencias
docker compose exec app composer install

# Generar app key
docker compose exec app php artisan key:generate

# Correr migraciones y seedear
docker compose exec app php artisan migrate --seed

# Instalar dependencias del frontend y buildear
docker compose exec app npm install
docker compose exec app npm run build

# Si hay problemas de permisos en storage
docker compose exec app chmod -R 775 storage bootstrap/cache
```

La app queda disponible en **http://localhost:8080**.

---

## Día a día

### Levantar el entorno

```bash
docker compose up -d
```

### Parar el entorno

```bash
docker compose down
```

### Ver logs

```bash
# Todos los servicios
docker compose logs -f

# Un servicio específico
docker compose logs -f app
docker compose logs -f queue
docker compose logs -f scheduler
docker compose logs -f nginx
docker compose logs -f pgsql
```

### Artisan

```bash
docker compose exec app php artisan <comando>

# Ejemplos
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:rollback
docker compose exec app php artisan make:model Subscription -mfc
docker compose exec app php artisan tinker
docker compose exec app php artisan route:list
docker compose exec app php artisan queue:restart
```

### Composer

```bash
docker compose exec app composer require <paquete>
docker compose exec app composer update
```

### NPM / Frontend (Vue + Vite)

Node.js 22 está instalado dentro del contenedor `app`. Todos los comandos npm deben correr dentro de Docker:

```bash
# Instalar dependencias
docker compose exec app npm install

# Build de producción (genera assets en public/build/)
docker compose exec app npm run build

# Dev server con hot reload (para desarrollo)
docker compose exec app npm run dev
```

> **Importante:** Después de cualquier cambio en archivos `.vue`, `.js` o `.css`, hay que re-buildear con `npm run build` para que los cambios se reflejen en el navegador. El contenedor NO tiene Node corriendo en modo watch por defecto.

### Tests

```bash
docker compose exec app php artisan test
docker compose exec app php artisan test --filter=NombreDelTest
```

### Entrar al contenedor

```bash
docker compose exec app bash
```

### PostgreSQL

```bash
# Desde dentro del contenedor
docker compose exec pgsql psql -U app_user -d gestion_subscripciones

# Desde tu Mac (ej: con TablePlus, DBeaver, etc.)
# Host: 127.0.0.1  Puerto: 54320  User: app_user  DB: gestion_subscripciones
```

### Redis

```bash
docker compose exec redis redis-cli
```

---

## Queue worker

El worker corre en su propio contenedor (`queue`). Si cambiás un job, reiniciá el worker:

```bash
docker compose restart queue
```

---

## Scheduler

El scheduler corre `php artisan schedule:run` cada 60 segundos en su propio contenedor.

```bash
docker compose logs -f scheduler
```

---

## Xdebug

Xdebug 3 viene preinstalado en el contenedor PHP. Por defecto está en modo `off` para no afectar performance.

### Activar Xdebug para debuggear

Cambiar `XDEBUG_MODE` en `.env`:

```bash
# .env
XDEBUG_MODE=debug
```

Reiniciar el contenedor app (no necesita rebuild):

```bash
docker compose up -d app
```

### Modos disponibles

| Modo | Uso |
|---|---|
| `off` | Desactivado (default, sin impacto en performance) |
| `debug` | Step debugging con breakpoints |
| `profile` | Genera cachegrind para profiling |
| `coverage` | Code coverage para tests |
| `debug,coverage` | Ambos a la vez |

### Configurar VS Code

Ya está creado `.vscode/launch.json`. Solo necesitás:

1. Instalar la extensión **PHP Debug** (xdebug.php-debug)
2. Poner `XDEBUG_MODE=debug` en `.env`
3. `docker compose up -d app`
4. En VS Code: Run > Start Debugging (F5) > "Listen for Xdebug (Docker)"
5. Poner un breakpoint en cualquier archivo PHP
6. Hacer un request a http://localhost:8080 — el debugger se frena en el breakpoint

### Configurar PHPStorm

1. Settings > PHP > Debug > Xdebug: puerto **9003**
2. Settings > PHP > Servers: agregar server `localhost` puerto `8080`, path mapping `/var/www` -> carpeta del proyecto
3. Poner `XDEBUG_MODE=debug` en `.env`
4. `docker compose up -d app`
5. Click en "Start Listening for PHP Debug Connections" (el icono del teléfono)
6. Poner breakpoint y hacer request

### Debug de tests

```bash
# Correr tests con Xdebug activado (el .env ya tiene el mode)
docker compose exec app php artisan test

# O un test específico
docker compose exec app php artisan test --filter=NombreDelTest
```

### Debug de artisan commands

```bash
docker compose exec app php artisan tu:comando
```

Con `XDEBUG_MODE=debug` activo, el debugger se conecta automáticamente a tu IDE al ejecutar cualquier comando.

### Desactivar Xdebug

```bash
# .env
XDEBUG_MODE=off
```

```bash
docker compose up -d app
```

### Detalles técnicos

- Puerto: **9003** (standard Xdebug 3)
- Client host: `host.docker.internal` (resuelve al host de Docker Desktop en Mac/Windows)
- IDE key: `VSCODE`
- `start_with_request=yes`: se conecta al IDE en cada request sin necesidad de cookie/trigger
- Queue y Scheduler siempre corren con `XDEBUG_MODE=off` para no afectar workers en background

---

## Rebuild

Si modificás el `Dockerfile` o agregás extensiones de PHP:

```bash
docker compose up -d --build
```

---

## Reset completo

Si necesitás arrancar de cero (borra la base de datos):

```bash
docker compose down -v
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```
