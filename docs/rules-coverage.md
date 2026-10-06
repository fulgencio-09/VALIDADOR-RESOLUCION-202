# Cobertura de reglas RPED

Fuente única: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en catálogos de validación | 338 |
| Reglas aún pendientes de implementación/verificación | 57 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |
| Reglas ejecutables del rango Error020-Error096 | 33 |
| Reglas ejecutables del bloque de fecha de corte | 36 |
| Reglas ejecutables del bloque fecha vs. nacimiento | 37 |
| Reglas ejecutables adicionales Error220-Error244 | 9 |
| Reglas ejecutables del bloque Error294-Error399 | 50 |
| Reglas ejecutables del bloque Error535-Error540 | 6 |
| Reglas ejecutables del bloque Error638-Error652 | 14 |
| Reglas ejecutables del lote de fechas/contenido/comodines/valores | 106 |
| Reglas ejecutables del lote de consistencia | 50 |

## Fuente de verdad y control contra reglas ajenas

La aplicación utiliza el catálogo v8 de `database/catalog/validation_rules_rped.csv` como lista oficial de códigos. Se agregó una prueba de integridad para impedir que un catálogo ejecutable introduzca códigos que no existan en los 395 códigos oficiales.

También se agregó `tools/verify_rped_catalog_completeness.py`, que calcula códigos oficiales, ejecutables, pendientes, desconocidos y duplicados a partir de los catálogos PHP.

No se agregan códigos inventados ni reglas de otras resoluciones al conjunto RPED.

## Familias implementadas

### Error020-Error096

Implementadas y parametrizadas las 33 reglas ejecutables del rango, manteniendo `Error021`, `Error022` y `Error079` como pendientes por dependencia externa o inconsistencia documental.

### Fechas posteriores a la fecha de corte

Implementadas las 36 reglas correspondientes al bloque ya identificado en la fuente:

`Error120`, `Error121`, `Error122`, `Error123`, `Error124`, `Error125`, `Error126`, `Error127`, `Error128`, `Error129`, `Error130`, `Error131`, `Error132`, `Error133`, `Error134`, `Error135`, `Error136`, `Error138`, `Error139`, `Error140`, `Error141`, `Error142`, `Error143`, `Error144`, `Error145`, `Error146`, `Error147`, `Error148`, `Error149`, `Error150`, `Error151`, `Error152`, `Error155`, `Error157`, `Error158`, `Error159`.

### Fecha de actividad vs. fecha de nacimiento

Implementadas las 37 reglas oficiales encontradas en este bloque. Los comodines definidos por el anexo se excluyen de esta comparación.

No aparecen `Error187`, `Error204` ni `Error206` como códigos de error en las filas de reglas `170-209` de la fuente RPED v8 consultada; por eso no se agregan artificialmente.

## Lote de reglas fuente: fechas, comodines y valores

`RpedBatchCoreSourceRuleCatalog.php` contiene 106 reglas adicionales traducidas directamente de la hoja RPED v8, incluyendo contenido de fechas, comodines según la definición de cada variable, warnings y valores permitidos explícitos.

`Error433` no se implementa porque la fuente tiene una inconsistencia: ordena validar contenido de fecha en la variable 88, mientras la propia variable 88 está definida como resultado numérico del tamizaje de cáncer de cuello uterino. No se inventa una interpretación.

`Error021` y `Error022` dependen del catálogo externo REPS del MSPS. La aplicación no inventa valores REPS ni los obtiene de una fuente distinta a la documentación entregada.

`Error079` queda separado hasta resolver la contradicción documental entre variable 90 y variable 91.

## Lote de consistencia

`RpedBatchConsistencySourceRuleCatalog.php` agrega 50 reglas directamente traducidas de la columna `VALIDACIONES`, incluyendo coherencia entre resultado y fecha, restricciones por edad, sexo, comodines, actividades y condiciones entre variables.

Estas reglas usan exclusivamente variables, valores, edades y fechas descritos en el anexo v8; no introducen reglas clínicas externas.

## Reglas ejecutables adicionales Error220-Error244

Implementadas: `Error220`, `Error222`, `Error223`, `Error227`, `Error232`, `Error237`, `Error242`, `Error243`, `Error244`.

## Error294-Error399

Implementado el lote activo previamente documentado, incluyendo `Warning307` y las reglas de consistencia de ese bloque.

## Error535-Error540

Implementadas las 6 reglas activas: `Error535`, `Error536`, `Error537`, `Error538`, `Error539`, `Error540`.

## Error638-Error652

Implementadas 14 reglas activas, incluyendo `Error644` con los siete comodines explícitamente permitidos para la variable 105 en la fuente v8. `Error648` no aparece como código activo en el catálogo v8 utilizado y no se agrega artificialmente.

## Validación con TXT real

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro.

Los resultados previamente documentados sobre el TXT real se conservan como pruebas de integración de los lotes ya ejecutados. Las nuevas reglas deben someterse a una nueva ejecución integral antes de afirmar cantidades de errores o warnings producidas por ellas.

## Pendientes

Las 57 reglas restantes se mantienen identificadas por su código oficial y no se sustituyen por reglas aproximadas. La siguiente fase debe traducirlas una por una desde la columna `VALIDACIONES` de la misma hoja, con pruebas de caso positivo y negativo.

## Criterio de cobertura

El catálogo oficial contiene 395 códigos. Una regla se considera ejecutable solamente cuando existe una operación implementada y puede comprobarse contra la definición de la fuente.

Cuando una regla requiere una fuente externa que no fue entregada se mantiene como dependencia explícita. Cuando existe una inconsistencia dentro de la propia documentación, se mantiene como anomalía hasta que pueda resolverse con una fuente oficial de la misma versión.
