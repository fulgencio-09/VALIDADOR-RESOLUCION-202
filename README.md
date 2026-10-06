# VALIDADOR-RESOLUCION-202

Aplicación web para validar, analizar y corregir archivos asociados a la Resolución 202 de 2021.

## Objetivo

Construir un validador web basado en la estructura oficial del anexo técnico, con un motor de reglas versionado que permita:

- Cargar archivos para validación.
- Validar estructura, tipos, longitudes y valores permitidos.
- Ejecutar reglas de negocio y consistencia entre variables.
- Identificar errores y advertencias con código y descripción.
- Aplicar correcciones automáticas únicamente cuando sean seguras.
- Mantener historial de validaciones y correcciones.
- Generar archivos corregidos y reportes de resultados.

## Arquitectura

- **Frontend:** Vue 3 + Vite.
- **Backend:** Laravel 12 / API REST.
- **Persistencia:** MySQL.
- **Procesamiento:** colas con Redis para archivos grandes.
- **Reglas:** motor de validación parametrizado y versionado.

Flujo actual:

```text
Vue 3
  ↓
POST /api/validations
  ↓
Laravel 12
  ↓
Validación estructural + motor RPED
  ↓
Persistencia en validation_runs
  ↓
Resultados JSON
  ↓
Dashboard + historial + reporte CSV
```

## Fuente normativa

La base funcional se construye a partir de la Resolución 202 de 2021 y del archivo oficial de lineamientos v8 entregado para el proyecto. El Anexo Técnico 1 define el Registro por Persona (RPED) y el Anexo Técnico 2 el Registro de Novedades por Persona (NPED).

## Catálogo RPED

El catálogo reproducible se genera con `tools/import_res202_catalog.py` a partir de la hoja `Lineamientos RPED` del Excel oficial.

- 119 variables RPED, numeradas de 0 a 118.
- Catálogo de reglas `Error` y `Warning` separado del código de aplicación.
- Reglas versionadas con `source_version=v8`.
- Corrección automática desactivada por defecto; se habilitará regla por regla después de verificar que sea segura.

## API disponible

| Método | Endpoint | Función |
|---|---|---|
| GET | `/api/health` | Estado de la API |
| POST | `/api/validations` | Cargar y validar TXT RPED |
| GET | `/api/validations` | Historial de las últimas 50 validaciones |
| GET | `/api/validations/{id}` | Detalle de una validación |

La carga acepta TXT de hasta 50 MB. El resultado incluye registros, reglas ejecutadas, errores, advertencias, línea, variable, código y valor observado.

## Persistencia

La tabla `validation_runs` conserva el historial de validaciones, incluyendo:

- nombre y SHA-256 del archivo;
- tamaño;
- anexo;
- estado;
- cantidad de registros;
- reglas ejecutadas;
- errores y advertencias;
- resultados completos de la validación;
- fecha de procesamiento.

Para crearla en el entorno Laravel:

```bash
cd backend
composer install
php artisan migrate
php artisan serve
```

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Configurar opcionalmente:

```env
VITE_API_URL=http://localhost:8000/api
```

## Motor de validación

Se incorporaron:

- `StructuralValidator.php`: valida la estructura física del TXT.
- `RuleEngine.php`: ejecuta reglas parametrizadas sin hard-codear cada regla.
- `RpedValidator.php`: compone validación estructural y reglas de negocio por registro.
- `RpedCatalogLoader.php`: carga las 119 variables desde el catálogo reproducible.
- `database/catalog/validation_rules_rped.csv`: catálogo oficial normalizado por código.

La cobertura de reglas seguirá ampliándose desde el Excel oficial. La aplicación no debe considerarse terminada hasta completar y probar la cobertura requerida.

## Validación estructural

La primera capa contempla:

1. Archivo no vacío.
2. Registro tipo 1 obligatorio y primero.
3. Registro tipo 1 con 5 campos.
4. Registros tipo 2 con 119 campos.
5. Consecutivo de detalle desde 1 y en orden.
6. Cantidad declarada en el registro de control contra registros tipo 2.
7. Longitud máxima por variable.
8. Validación básica de tipos N, D y F.
9. Fechas con formato `AAAA-MM-DD`.
10. Detección de caracteres especiales de fin de archivo/registro.

## Principios

1. Las reglas se derivan del anexo técnico oficial.
2. No se inventan valores para corregir información faltante.
3. Las correcciones automáticas deben ser trazables.
4. Los errores y advertencias conservan su código oficial cuando corresponda.
5. El sistema funciona como herramienta de prevalidación y control de calidad; no sustituye la validación oficial de PISIS.

## Estado

**Fase 1 funcional:** carga TXT, validación estructural, motor RPED, resultados, reporte CSV e historial persistente implementados.

**Siguiente bloque:** corrección segura y trazable, descarga del TXT corregido, procesamiento de archivos grandes con Redis/colas y ampliación de cobertura de reglas pendientes.
