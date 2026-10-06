# Backend — Validador Resolución 202

API REST en Laravel 12 para validar archivos TXT del Anexo Técnico 1 (RPED).

## Flujo V1

`Vue 3 → POST /api/validations → Laravel → StructuralValidator + RuleEngine → JSON de resultados`

El endpoint ejecuta el catálogo base RPED y los bloques de reglas ya implementados en `RpedValidator`.

## Requisitos

- PHP 8.2+
- Composer 2+
- Node.js 20+ para el frontend

## Instalación local

Desde `backend/`:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve --host=127.0.0.1 --port=8000
```

La API quedará disponible en `http://localhost:8000/api`.

### Salud

```bash
curl http://localhost:8000/api/health
```

### Validación

```bash
curl -X POST http://localhost:8000/api/validations \
  -F "file=@/ruta/al/archivo.txt"
```

El límite de carga de la V1 es 50 MB. El archivo debe tener extensión `.txt`.

## Frontend

Desde `frontend/`:

```bash
npm install
npm run dev
```

El frontend usa `VITE_API_URL` y por defecto apunta a `http://localhost:8000/api`.

## Nota de arquitectura

La validación de negocio permanece fuera del controlador. El controlador solamente recibe el archivo, carga el catálogo de variables y entrega la respuesta HTTP. Las reglas continúan parametrizadas en los catálogos del dominio.
