# Cobertura de reglas RPED

Fuente: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en `RpedRuleCatalog` | 38 |
| Reglas aún pendientes de implementación | 357 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |
| Reglas ejecutables del rango Error020-Error096 | 33 |
| Reglas ejecutables del bloque de fechas Error120-Error159 | 26 |

## Familia implementada: Error020-Error096

Implementadas y parametrizadas:

`Error020`, `Error030`, `Error037`, `Error038`, `Error041`, `Error043`, `Error047`, `Error049`, `Error050`, `Error051`, `Error063`, `Error064`, `Error069`, `Error070`, `Error071`, `Error072`, `Error073`, `Error074`, `Error075`, `Error076`, `Error077`, `Error078`, `Error082`, `Error083`, `Error084`, `Error085`, `Error088`, `Error089`, `Error090`, `Error091`, `Error094`, `Error095`, `Error096`.

Las reglas `Error021` y `Error022` permanecen pendientes porque requieren consulta al catálogo externo REPS. `Error079` permanece pendiente porque la validación de la fuente indica variable 90, mientras la descripción y las variables relacionadas indican variable 91; la discrepancia se documenta en `RpedRuleCatalog::pendingSourceAmbiguities()` y no se corrige silenciosamente.

## Familia implementada: Error120-Error159

Se implementaron las reglas de fecha de corte que aparecen activas en el catálogo para este bloque:

`Error120`, `Error121`, `Error122`, `Error123`, `Error124`, `Error125`, `Error126`, `Error127`, `Error128`, `Error129`, `Error130`, `Error131`, `Error139`, `Error144`, `Error145`, `Error146`, `Error147`, `Error148`, `Error149`, `Error150`, `Error151`, `Error152`, `Error155`, `Error157`, `Error158`, `Error159`.

La operación reutilizable es `date_after_cutoff`. El motor recibe la fecha de corte del registro tipo 1 mediante `RpedValidator` y compara las fechas con objetos `DateTimeImmutable`, evitando depender de comparación textual de fechas.

Los códigos del intervalo que no aparecen en esta familia activa no se inventan ni se marcan como implementados; se continuarán abordando según las validaciones vigentes de la versión 8.

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

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro. Las pruebas estructurales y de reglas verifican que las fechas de las variables cubiertas por `Error120-Error159` no superen la fecha de corte `2026-09-30`.

## Criterio de cobertura

Una regla solo se marca como ejecutable cuando su operación está implementada en el motor y existe una prueba automatizada que cubre al menos un caso válido y, cuando corresponde, un caso inválido.

El catálogo de 395 códigos no implica que las 395 reglas estén implementadas. Las reglas que dependen de catálogos externos, reglas con ambigüedad en la fuente y reglas aún no traducidas a operaciones ejecutables permanecen diferenciadas hasta que exista una implementación y prueba verificable.
