# Cobertura de reglas RPED

Fuente: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en `RpedRuleCatalog` | 41 |
| Reglas aún pendientes de implementación | 354 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |
| Reglas ejecutables del rango Error020-Error096 | 33 |

## Familia implementada: Error020-Error096

Implementadas y parametrizadas:

`Error020`, `Error030`, `Error037`, `Error038`, `Error041`, `Error043`, `Error047`, `Error049`, `Error050`, `Error051`, `Error063`, `Error064`, `Error069`, `Error070`, `Error071`, `Error072`, `Error073`, `Error074`, `Error075`, `Error076`, `Error077`, `Error078`, `Error082`, `Error083`, `Error084`, `Error085`, `Error088`, `Error089`, `Error090`, `Error091`, `Error094`, `Error095`, `Error096`.

Las reglas `Error021` y `Error022` permanecen pendientes porque requieren consulta al catálogo externo REPS. `Error079` permanece pendiente porque la validación de la fuente indica variable 90, mientras la descripción y las variables relacionadas indican variable 91; la discrepancia se documenta en `RpedRuleCatalog::pendingSourceAmbiguities()` y no se corrige silenciosamente.

## Reglas ejecutables previas

- Error653 — valores permitidos de resultado de baciloscopia diagnóstico.
- Error655 — valores permitidos de clasificación de riesgo cardiovascular.
- Error656 — valor permitido de tratamiento para sífilis gestacional.
- Error657 — valor permitido de tratamiento para sífilis congénita.
- Error665 — valores permitidos de clasificación de riesgo metabólico.
- Error676 — longitud del documento según tipo de identificación.
- Error677 — fecha de nacimiento no anterior a 1900-01-01.
- Error678 — variable 102: `0`, `21` o 12 dígitos.

## Validación con TXT real

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro. La prueba automatizada `tools/test_rped_txt.py` incorpora ahora las 33 reglas ejecutables del rango Error020-Error096 y las 8 reglas ejecutables previas; el conjunto presenta 0 violaciones sobre el TXT de prueba.

## Criterio de cobertura

Una regla solo se marca como ejecutable cuando su operación está implementada en el motor y existe una prueba automatizada que cubre al menos un caso válido y, cuando corresponde, un caso inválido.

El catálogo de 395 códigos no implica que las 395 reglas estén implementadas. Las reglas que dependen de catálogos externos, reglas con ambigüedad en la fuente y reglas aún no traducidas a operaciones ejecutables permanecen diferenciadas hasta que exista una implementación y prueba verificable.
