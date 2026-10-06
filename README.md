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

## Arquitectura inicial

- **Frontend:** Vue 3 + Vite.
- **Backend:** Laravel 12 / API REST.
- **Persistencia:** MySQL.
- **Procesamiento:** colas con Redis para archivos grandes.
- **Reglas:** motor de validación parametrizado y versionado.

## Fuente normativa

La base funcional se construye a partir de la Resolución 202 de 2021 y del archivo oficial de lineamientos v8 entregado para el proyecto. El Anexo Técnico 1 define el Registro por Persona (RPED) y el Anexo Técnico 2 el Registro de Novedades por Persona (NPED).

## Catálogo RPED

El catálogo reproducible se genera con `tools/import_res202_catalog.py` a partir de la hoja `Lineamientos RPED` del Excel oficial.

- 119 variables RPED, numeradas de 0 a 118.
- Catálogo de reglas `Error` y `Warning` separado del código de aplicación.
- Reglas versionadas con `source_version=v8`.
- Corrección automática desactivada por defecto; se habilitará regla por regla después de verificar que sea segura.

## Motor de validación

Se incorporaron:

- `StructuralValidator.php`: valida la estructura física del TXT.
- `RuleEngine.php`: ejecuta reglas parametrizadas sin hard-codear cada regla.
- `RpedValidator.php`: compone validación estructural y reglas de negocio por registro.
- `database/catalog/validation_rules_rped.csv`: catálogo inicial de reglas RPED normalizado por código oficial.

El catálogo inicial es deliberadamente una primera cobertura funcional. La cobertura completa de reglas se seguirá importando desde el Excel oficial antes de considerar terminada la validación de contenido.

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

Esta capa es independiente del catálogo de reglas de negocio para permitir que el motor posterior sea parametrizado.

## Principios

1. Las reglas se derivan del anexo técnico oficial.
2. No se inventan valores para corregir información faltante.
3. Las correcciones automáticas deben ser trazables.
4. Los errores y advertencias conservan su código oficial cuando corresponda.
5. El sistema funciona como herramienta de prevalidación y control de calidad; no sustituye la validación oficial de PISIS.

## Estructura

```text
backend/       API Laravel
afrontend/     SPA Vue 3
database/      SQL y catálogos
docs/          Arquitectura, reglas y decisiones técnicas
tools/         Importadores reproducibles
tests/         Casos de prueba
```

## Estado

**Fase 1 en desarrollo:** catálogo oficial RPED, validación estructural y primer motor parametrizado implementados. El siguiente paso es ampliar el catálogo completo y crear pruebas automatizadas contra archivos TXT reales.
