# Cobertura de reglas RPED

Fuente única: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en catálogos de validación | 288 |
| Reglas aún pendientes de implementación/verificación | 107 |
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

El motor recibe la fecha de corte del registro tipo 1 y utiliza operaciones reutilizables para comparar fechas con la fecha de corte.

### Fecha de actividad vs. fecha de nacimiento

Implementadas las 37 reglas oficiales encontradas en este bloque:

`Error170`, `Error171`, `Error172`, `Error173`, `Error174`, `Error175`, `Error176`, `Error177`, `Error178`, `Error179`, `Error180`, `Error181`, `Error182`, `Error183`, `Error184`, `Error185`, `Error186`, `Error188`, `Error189`, `Error190`, `Error191`, `Error192`, `Error193`, `Error194`, `Error195`, `Error196`, `Error197`, `Error198`, `Error199`, `Error200`, `Error201`, `Error202`, `Error203`, `Error205`, `Error207`, `Error208`, `Error209`.

Los comodines de fecha definidos por el anexo (`1800-01-01`, `1805-01-01`, `1810-01-01`, `1825-01-01`, `1830-01-01`, `1835-01-01`, `1845-01-01`) se excluyen de esta comparación.

No aparecen `Error187`, `Error204` ni `Error206` como códigos de error en las filas de reglas `170-209` de la fuente RPED v8 consultada; por eso no se agregan artificialmente al catálogo ejecutable.

## Lote de reglas fuente: fechas, comodines y valores

Se agregó `RpedBatchCoreSourceRuleCatalog.php` con 106 reglas adicionales traducidas directamente de la hoja RPED v8, incluyendo:

- contenido válido de fechas para las variables definidas como fecha;
- fechas posteriores a la fecha de corte en `Error132`–`Error143` que estaban pendientes;
- validación de comodines según los valores permitidos de cada variable;
- warnings `Warning040`, `Warning042`, `Warning674` y `Warning675`;
- valores permitidos explícitos de variables como sífilis, mini-mental, tacto rectal, agudeza visual, hepatitis C, escalas de desarrollo, gestación y otras variables del anexo.

`Error433` no se implementa porque la fuente tiene una inconsistencia: ordena validar contenido de fecha en la variable 88, mientras la propia variable 88 está definida como resultado numérico del tamizaje de cáncer de cuello uterino. No se inventa una interpretación.

### Error020-Error096 y dependencias

`Error021` y `Error022` dependen del catálogo externo REPS del MSPS. La aplicación mantiene la operación de catálogo preparada, pero no inventa valores REPS ni los obtiene de una fuente distinta a la documentación entregada.

`Error079` queda separado hasta resolver la contradicción documental entre variable 90 y variable 91.

## Reglas ejecutables adicionales Error220-Error244

Implementadas:

`Error220`, `Error222`, `Error223`, `Error227`, `Error232`, `Error237`, `Error242`, `Error243`, `Error244`.

## Bloque Error294-Error399

Se incorporó un lote de 50 reglas ejecutables directamente traducibles a operaciones del motor.

## Error535-Error540

Implementadas las 6 reglas activas:

`Error535`, `Error536`, `Error537`, `Error538`, `Error539`, `Error540`.

## Error638-Error652

Implementadas 14 reglas activas del bloque, incluyendo `Error644`, cuya condición de comodines se parametrizó usando los siete comodines explícitamente permitidos para la variable 105 en la fuente v8:

`Error638`, `Error639`, `Error640`, `Error641`, `Error642`, `Error643`, `Error644`, `Error645`, `Error646`, `Error647`, `Error649`, `Error650`, `Error651`, `Error652`.

`Error648` no aparece como código activo en el catálogo v8 utilizado para el proyecto y no se agrega artificialmente.

## Validación con TXT real

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro.

Los resultados previamente documentados sobre el TXT real se conservan como pruebas de integración de los lotes ya ejecutados. Las nuevas reglas de este lote deben someterse a una nueva ejecución integral antes de afirmar cantidades de errores o warnings producidas por ellas.

## Pendientes

Las 107 reglas restantes se mantienen identificadas por su código oficial y no se sustituyen por reglas aproximadas. La siguiente fase debe traducirlas una por una desde la columna `VALIDACIONES` de la misma hoja, con pruebas de caso positivo y negativo.

## Criterio de cobertura

El catálogo oficial contiene 395 códigos. Una regla se considera ejecutable solamente cuando existe una operación implementada y puede comprobarse contra la definición de la fuente.

Cuando una regla requiere una fuente externa que no fue entregada —por ejemplo, REPS— se mantiene como dependencia explícita en lugar de inventar datos. Cuando existe una inconsistencia dentro de la propia documentación, se mantiene como anomalía hasta que pueda resolverse con una fuente oficial de la misma versión.
