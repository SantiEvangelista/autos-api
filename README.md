<p align="center">
  <h1 align="center">Arg Autos API</h1>
</p>

<p align="center">
  API REST pública y gratuita de valuaciones del mercado automotor argentino.
</p>

<p align="center">
  <a href="https://argautos.com">Sitio web</a> ·
  <a href="https://argautos.com/docs/api">Documentación</a>
</p>

---

## Descripción

**Arg Autos API** permite consultar precios de vehículos del mercado automotor argentino por marca, modelo, versión y año-modelo, con conversión en tiempo real de USD a ARS (dólar oficial vía [Bluelytics](https://bluelytics.com.ar)).

### Datos disponibles

| Dato | Cantidad |
|------|----------|
| Marcas | 60+ |
| Modelos | 600+ |
| Versiones | 5.800+ |

## Endpoints

URL base: `https://argautos.com/api/v1`

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/brands` | Lista de marcas |
| `GET` | `/brands/{id}/models` | Modelos de una marca |
| `GET` | `/models/{id}/versions` | Versiones de un modelo |
| `GET` | `/versions/{id}/valuations` | Precios por año-modelo (USD) |
| `GET` | `/versions/{id}/valuations?currency=ars` | Precios convertidos a ARS |
| `GET` | `/search?q={term}` | Búsqueda full-text |
| `GET` | `/stats` | Estadísticas generales |
| `GET` | `/health` | Estado del servicio |

### Ejemplo de uso

```bash
# Listar marcas
curl https://argautos.com/api/v1/brands

# Modelos de una marca
curl https://argautos.com/api/v1/brands/1/models

# Valuaciones en ARS
curl https://argautos.com/api/v1/versions/42/valuations?currency=ars
```

### Ejemplo de respuesta

```json
{
  "data": [
    {
      "id": 1,
      "year": 2024,
      "price": "45000.00",
      "currency": "USD"
    },
    {
      "id": 2,
      "year": 2023,
      "price": "38500.00",
      "currency": "USD"
    }
  ],
  "links": {
    "next": null,
    "prev": null
  }
}
```

## Rate limiting

60 requests por minuto por IP. Al exceder el límite se retorna `429 Too Many Requests`.

## Stack técnico

| Componente | Tecnología |
|------------|-----------|
| Backend | Laravel 13 + PHP 8.3 |
| Base de datos | PostgreSQL 16 |
| Cache | Redis |
| Frontend | Vue 3 + Tailwind CSS 4 + Vite |
| Documentación | Scramble (OpenAPI 3.1) + Stoplight Elements |
| Hosting | Railway |

## Desarrollo local

Ver [DOCKER.md](DOCKER.md) para instrucciones completas con Docker.

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
npm install && npm run build
```

### Tests

```bash
docker compose exec app php artisan test
```

## Autor

Desarrollado por [Santiago Evangelista](https://github.com/SantiEvangelista).

## Licencia

Este proyecto es de código abierto.
