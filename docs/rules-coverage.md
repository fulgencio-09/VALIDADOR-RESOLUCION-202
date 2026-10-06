# Cobertura de reglas RPED

Fuente: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en `RpedRuleCatalog` | 12 |
| Reglas aún pendientes de implementación | 383 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |

## Reglas ejecutables

- Error020 — fecha de nacimiento requerida.
- Error030 — relación Gestante/Sexo.
- Error041 — relación peso/fecha de medición.
- Error043 — relación talla/fecha de medición.
- Error653 — valores permitidos de resultado de baciloscopia diagnóstico.
- Error655 — valores permitidos de clasificación de riesgo cardiovascular.
- Error656 — valor permitido de tratamiento para sífilis gestacional.
- Error657 — valor permitido de tratamiento para sífilis congénita.
- Error665 — valores permitidos de clasificación de riesgo metabólico.
- Error676 — longitud del documento según tipo de identificación.
- Error677 — fecha de nacimiento no anterior a 1900-01-01.
- Error678 — variable 102: `0`, `21` o 12 dígitos.

## Validación con TXT real

El archivo de prueba `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro. Las 12 reglas anteriores presentan 0 violaciones en ese archivo.

## Criterio de cobertura

Una regla solo se marca como ejecutable cuando su operación está implementada en el motor y existe una prueba automatizada que cubre al menos un caso válido y, cuando corresponde, un caso inválido.

El catálogo de 395 códigos no implica que las 395 reglas estén implementadas. Las reglas que dependen de catálogos externos (por ejemplo REPS o tablas de referencia) se mantendrán diferenciadas hasta disponer de la fuente y versión correspondiente.
