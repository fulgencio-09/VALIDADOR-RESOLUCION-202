# Cobertura de reglas RPED

Fuente: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en `RpedRuleCatalog` | 104 |
| Reglas aún pendientes de implementación | 291 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |
| Reglas ejecutables del rango Error020-Error096 | 33 |
| Reglas ejecutables del bloque de fecha de corte | 26 |
| Reglas ejecutables del bloque fecha vs. nacimiento | 37 |

## Familias implementadas

### Error020-Error096

Implementadas y parametrizadas las 33 reglas ejecutables del rango, manteniendo `Error021`, `Error022` y `Error079` como pendientes por dependencia externa o ambigüedad de fuente.

### Fechas posteriores a la fecha de corte

Implementadas:

`Error120`, `Error121`, `Error122`, `Error123`, `Error124`, `Error125`, `Error126`, `Error127`, `Error128`, `Error129`, `Error130`, `Error131`, `Error139`, `Error144`, `Error145`, `Error146`, `Error147`, `Error148`, `Error149`, `Error150`, `Error151`, `Error152`, `Error155`, `Error157`, `Error158`, `Error159`.

El motor recibe la fecha de corte del registro tipo 1 y utiliza la operación reutilizable `date_after_cutoff`.

### Fecha de actividad vs. fecha de nacimiento

Implementadas las 37 reglas oficiales encontradas en este bloque:

`Error170`, `Error171`, `Error172`, `Error173`, `Error174`, `Error175`, `Error176`, `Error177`, `Error178`, `Error179`, `Error180`, `Error181`, `Error182`, `Error183`, `Error184`, `Error185`, `Error186`, `Error188`, `Error189`, `Error190`, `Error191`, `Error192`, `Error193`, `Error194`, `Error195`, `Error196`, `Error197`, `Error198`, `Error199`, `Error200`, `Error201`, `Error202`, `Error203`, `Error205`, `Error207`, `Error208`, `Error209`.

Los lineamientos presentan diferencias entre comparaciones estrictas (`<` o `>`) y no estrictas (`<=` o `>=`). El motor `date_before_birth` ahora recibe `inclusive` para representar esa diferencia sin duplicar lógica. Las reglas `Error201` y `Error202` además aplican la condición fuente de considerar solamente fechas válidas posteriores a `1900-01-01`.

Los comodines de fecha definidos por el anexo (`1800-01-01`, `1805-01-01`, `1810-01-01`, `1825-01-01`, `1830-01-01`, `1835-01-01`, `1845-01-01`) se excluyen de esta comparación.

No aparecen `Error187`, `Error204` ni `Error206` como códigos de error en las filas de reglas `170-209` de la fuente RPED v8 consultada; por eso no se agregan artificialmente al catálogo ejecutable.

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

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro. El bloque completo `Error170-Error209` que existe en la fuente fue evaluado sobre los 431 registros: **37 reglas, 0 violaciones**.

La validación equivalente ejecutada sobre el TXT real confirmó cero violaciones para cada una de las 37 reglas, respetando los comodines y las comparaciones estrictas/no estrictas de la fuente.

El criterio de cobertura exige que una regla sea ejecutable solo cuando su operación está implementada y existe una prueba automatizada para casos válidos e inválidos cuando corresponde.

El catálogo de 395 códigos no implica que las 395 reglas estén implementadas. Las reglas que dependen de catálogos externos, reglas con ambigüedad en la fuente y reglas aún no traducidas a operaciones ejecutables permanecen diferenciadas hasta que exista una implementación y prueba verificable.
