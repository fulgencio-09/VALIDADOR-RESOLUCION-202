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

## Principios

1. Las reglas se derivan del anexo técnico oficial.
2. No se inventan valores para corregir información faltante.
3. Las correcciones automáticas deben ser trazables.
4. Los errores y advertencias conservan su código oficial cuando corresponda.
5. El sistema funciona como herramienta de prevalidación y control de calidad; no sustituye la validación oficial de PISIS.

## Estructura

```text
backend/       API Laravel
frontend/      SPA Vue 3
 database/     SQL y documentación de base de datos
docs/          Arquitectura, reglas y decisiones técnicas
tests/         Casos de prueba
```

## Estado

Proyecto inicializado. El siguiente paso es cargar la matriz oficial de variables y construir el catálogo de reglas de la Resolución 202.
