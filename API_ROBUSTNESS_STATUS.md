# API Valuación de Autos — Estado Actual

> Última actualización: 2026-03-20
> Stack: Laravel 13 + PostgreSQL 16 + Redis (Docker)
> Tests: 60/60 passing (198 assertions)
> Documentación interactiva: `http://localhost:8080/docs`

---

## Arquitectura general

```
Cliente → Nginx (:8080) → ThrottleRequests → PHP-FPM (Laravel 13) → PostgreSQL 16
                                                                    → Redis (cache/sessions/queues)
                                                                    → Bluelytics API (tipo de cambio)
```

**Modelo de datos:** `Brand` → `CarModel` → `Version` → `Valuation`

---

## Endpoints

| Método | Ruta | Controller | FormRequest | Paginado | Auth |
|--------|------|------------|-------------|----------|------|
| GET | `/api/brands` | `BrandController@index` | — | `simplePaginate(50, max:100)` | Pública |
| GET | `/api/brands/{brand}/models` | `BrandController@models` | `BrandModelsRequest` | `simplePaginate(25, max:100)` | Pública |
| GET | `/api/models/{carModel}/versions` | `CarModelController@versions` | `ModelVersionsRequest` | `simplePaginate(25, max:100)` | Pública |
| GET | `/api/versions/{version}/valuations` | `VersionController@valuations` | `VersionValuationsRequest` | No | Pública |
| GET | `/api/search` | `SearchController` | `SearchRequest` | `simplePaginate(25, max:50)` | Pública |
| GET | `/api/health` | `HealthController` | — | No | Pública |
| GET | `/docs/api` | Scramble UI | — | — | Pública |
| GET | `/docs/api.json` | Scramble (OpenAPI 3.1) | — | — | Pública |

**Route model binding:** `{brand}`, `{carModel}` y `{version}` usan ID (default Eloquent, sin `getRouteKeyName` override).

---

## Middleware y seguridad

### Autenticación
- **Estado actual:** Todos los endpoints son públicos (API de catálogo de solo lectura).
- Laravel Sanctum está instalado. La tabla `personal_access_tokens` existe para uso futuro si se necesita proteger endpoints de escritura.

### Rate limiting
- **Configuración:** 60 requests/minuto por IP (`AppServiceProvider.php`)
- **Middleware:** `ThrottleRequests:api` prepended en todas las rutas API (`bootstrap/app.php`)
- **Respuesta 429:** JSON `{"message": "Too many requests. Please try again later."}`
- **Clase de excepción:** `Illuminate\Http\Exceptions\ThrottleRequestsException` (no la de Symfony)

### Exception handling (`bootstrap/app.php`)
- Fuerza respuestas JSON en rutas `api/*` y cuando el cliente envía `Accept: application/json`
- `ThrottleRequestsException` → 429 JSON con mensaje custom
- `NotFoundHttpException` → 404 JSON con mensaje contextual ("Resource not found." vs "Endpoint not found.")
- `ValidationException` (automático vía FormRequest) → 422 JSON con estructura `{message, errors}`

### CORS
- No hay configuración explícita (`config/cors.php` no existe). Depende del default de Laravel.

### Security headers
- No hay middleware custom de headers de seguridad. Se recomienda configurar a nivel de Nginx.

---

## Validación

Todos los controllers usan FormRequests para validación. Toda la entrada se procesa vía `validated()`.

| FormRequest | Reglas |
|-------------|--------|
| `BrandModelsRequest` | `relations[]`: array, valores permitidos: `brand` / `page`: integer, min:1 / `per_page`: integer, min:1, max:100 |
| `ModelVersionsRequest` | `relations[]`: array, valores permitidos: `model`, `brand` / `page`: integer, min:1 / `per_page`: integer, min:1, max:100 |
| `VersionValuationsRequest` | `currency`: in:ARS,USD (case-insensitive via `prepareForValidation`) / `format_price`: in:true,false,1,0 (leído vía `validated()`, no `filter_var`) / `relations[]`: array, valores permitidos: `version`, `model`, `brand` |
| `SearchRequest` | `q`: required, string, min:2 / `page`: integer, min:1 / `per_page`: integer, min:1, max:50 |

**Formato de error uniforme (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "campo": ["Mensaje de error"]
  }
}
```

---

## Paginación

Todos los endpoints de listado usan `simplePaginate()`. **No incluye `total`** para evitar `COUNT(*)` costoso.

| Endpoint | Default `per_page` | Max `per_page` |
|----------|--------------------|----------------|
| `/api/brands` | 50 | 100 |
| `/api/brands/{brand}/models` | 25 | 100 |
| `/api/models/{carModel}/versions` | 25 | 100 |
| `/api/search` | 25 | 50 |

**Estructura de respuesta:**
```json
{
  "data": [...],
  "links": {
    "first": "/api/brands?page=1",
    "last": null,
    "prev": null,
    "next": "/api/brands?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "path": "/api/brands",
    "per_page": 50,
    "to": 50
  }
}
```

Los clientes deben navegar usando `links.next` / `links.prev`.

---

## Modelos y casts

| Modelo | Campo | Cast | Notas |
|--------|-------|------|-------|
| `Valuation` | `year` | `integer` | `0` = vehículo 0km |
| `Valuation` | `price` | `decimal:2` | Almacenado en USD. Se serializa como string `"40000.00"` en JSON |

---

## Servicio de tipo de cambio (`ExchangeRateService`)

- **Fuente:** API Bluelytics (`https://api.bluelytics.com.ar/v2/latest`) — tipo de cambio oficial venta
- **Cache:** 15 minutos. Solo cachea resultados exitosos (no cachea `null` en caso de fallo)
- **Resiliencia:** `connectTimeout(2s)`, `timeout(5s)`, `retry(3, 100ms)`, catch de excepciones
- **Fallo graceful:** Si la API externa no responde, retorna `null` → controller responde 503

---

## Búsqueda (`/api/search`)

- **Tipo:** Full-text search con `ILIKE '%term%'` sobre 3 tablas (versions, car_models, brands)
- **Eager loading:** `with(['carModel.brand'])` para evitar N+1
- **Índices:** Trigram GIN (`pg_trgm`) en `brands.name`, `car_models.name`, `versions.name`

---

## Health check (`/api/health`)

Verifica las 3 dependencias del servicio.

| Check | Qué valida |
|-------|------------|
| `database` | Conexión PDO a PostgreSQL |
| `redis` | `ping()` a Redis |
| `exchange_rate_cached` | Si existe el tipo de cambio en cache |

- **200:** `database=ok` y `redis=ok` → `status: "healthy"`
- **503:** Alguna dependencia crítica falla → `status: "degraded"`

---

## Documentación API (Scramble)

- **Acceso:** `http://localhost:8080/docs/api`
- **Spec:** OpenAPI 3.1 auto-generada en `/docs/api.json` via [dedoc/scramble](https://github.com/dedoc/scramble)
- **UI:** Stoplight Elements (incluido en Scramble)
- **Cobertura:** Todos los endpoints documentados con PHPDoc en los controllers
- **Config:** `config/scramble.php` — acceso público (sin `RestrictedDocsAccess`)

---

## Tests

```
60 tests, 198 assertions — 100% passing
```

| Suite | Tests | Cobertura |
|-------|-------|-----------|
| `ExchangeRateServiceTest` | 3 | Cache, API exitosa, API fallida |
| `ModelRelationsTest` | 8 | Relaciones Eloquent, route key, casts de Valuation |
| `RateLimitTest` | 1 | 429 con mensaje custom al exceder rate limit |
| `BrandApiTest` | 13 | Listado, paginación modelos (default/custom/max), relations, empty states |
| `CarModelApiTest` | 8 | Versiones, paginación, relations[]=model&brand, 404 |
| `SearchApiTest` | 10 | Búsqueda multi-tabla, case-insensitive, validación, paginación |
| `VersionApiTest` | 17 | Valuaciones, conversión USD/ARS, format_price (valid/invalid), relations parciales, errores |

**Ejecución:** `docker exec subs_app php artisan test`

---

## Infraestructura Docker

| Servicio | Container | Puerto expuesto |
|----------|-----------|-----------------|
| PHP-FPM | `subs_app` | — (interno) |
| Nginx | `subs_nginx` | `8080:80` |
| PostgreSQL 16 | `subs_pgsql` | `54320:5432` |
| Redis | `subs_redis` | `63790:6379` |
| Queue Worker | `subs_queue` | — |
| Scheduler | `subs_scheduler` | — |

---

## Issues pendientes (por prioridad)

| # | Severidad | Issue | Recomendación |
|---|-----------|-------|---------------|
| 9 | Moderada | Sin CORS explícito | Publicar `config/cors.php` |
| 10 | Moderada | Mutación directa de modelos Eloquent en `VersionController` | Usar DTOs o campos virtuales en Resource |
| 12 | Menor | Sin API versioning (`/api/v1/`) | Prefijo de versión en rutas |
| 13 | Menor | Sin ETag / Cache-Control headers | `Cache-Control: public, max-age=86400` en `/api/brands` y `/api/brands/{brand}/models` (datos casi estáticos, cambian solo al importar XLS). Cache server-side con Redis solo si hay alto tráfico. |
