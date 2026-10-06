# Cobertura de reglas RPED

Fuente: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en catálogos de validación | 113 |
| Reglas aún pendientes de implementación | 282 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |
| Reglas ejecutables del rango Error020-Error096 | 33 |
| Reglas ejecutables del bloque de fecha de corte | 26 |
| Reglas ejecutables del bloque fecha vs. nacimiento | 37 |
| Reglas ejecutables adicionales Error220-Error244 | 9 |

## Familias implementadas

### Error020-Error096

Implementadas y parametrizadas las 33 reglas ejecutables del rango, manteniendo `Error021`, `Error022` y `Error079` como pendientes por dependencia externa o ambigüedad de fuente.

### Fechas posteriores a la fecha de corte

Implementadas:

`Error120`, `Error121`, `Error122`, `Error123`, `Error124`, `Error125`, `Error126`, `Error127`, `Error128`, `Error129`, `Error130`, `Error131`, `Error139`, `Error144`, `Error145`, `Error146`, `Error147`, `Error148`, `Error149`, `Error150`, `Error151`, `Error152`, `Error155`, `Error157`, `Error158`, `Error159`.

El motor recibe la fecha de corte del registro tipo 1 y utiliza operaciones reutilizables para comparar fechas con la fecha de corte.

### Fecha de actividad vs. fecha de nacimiento

Implementadas las 37 reglas oficiales encontradas en este bloque:

`Error170`, `Error171`, `Error172`, `Error173`, `Error174`, `Error175`, `Error176`, `Error177`, `Error178`, `Error179`, `Error180`, `Error181`, `Error182`, `Error183`, `Error184`, `Error185`, `Error186`, `Error188`, `Error189`, `Error190`, `Error191`, `Error192`, `Error193`, `Error194`, `Error195`, `Error196`, `Error197`, `Error198`, `Error199`, `Error200`, `Error201`, `Error202`, `Error203`, `Error205`, `Error207`, `Error208`, `Error209`.

Los lineamientos presentan diferencias entre comparaciones estrictas (`<` o `>`) y no estrictas (`<=` o `>=`). El motor `date_before_birth` recibe `inclusive` para representar esa diferencia sin duplicar lógica. Las reglas `Error201` y `Error202` además aplican la condición fuente de considerar solamente fechas válidas posteriores a `1900-01-01`.

Los comodines de fecha definidos por el anexo (`1800-01-01`, `1805-01-01`, `1810-01-01`, `1825-01-01`, `1830-01-01`, `1835-01-01`, `1845-01-01`) se excluyen de esta comparación.

No aparecen `Error187`, `Error204` ni `Error206` como códigos de error en las filas de reglas `170-209` de la fuente RPED v8 consultada; por eso no se agregan artificialmente al catálogo ejecutable.

## Reglas ejecutables adicionales Error220-Error244

Implementadas:

`Error220`, `Error222`, `Error223`, `Error227`, `Error232`, `Error237`, `Error242`, `Error243`, `Error244`.

Estas reglas se encuentran en `RpedAdditionalRuleCatalog.php` y `RpedValidator` las incorpora automáticamente al conjunto recibido por el validador.

Operaciones nuevas del motor:

- `regex`
- `date_after_cutoff_plus_days`
- `date_relation`

`date_relation` permite comparar dos variables fecha y aplicar una fecha mínima válida, evitando interpretar comodines como fechas clínicas reales.

## Validación con TXT real

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro.

El bloque Error220-Error244 produjo:

| Regla | Violaciones |
|---|---:|
| Error220 | **1** |
| Error222 | 0 |
| Error223 | 0 |
| Error227 | 0 |
| Error232 | 0 |
| Error237 | 0 |
| Error242 | 0 |
| Error243 | 0 |
| Error244 | 0 |

La única inconsistencia detectada por `Error220` está en la línea 418 del TXT: el número de identificación aparece como `2.60874E+13`, formato que contiene caracteres no permitidos para la variable 4. Esta observación debe corregirse en el archivo fuente antes del reporte.

## Criterio de cobertura

El catálogo oficial contiene 395 códigos, pero no significa que los 395 estén implementados. Una regla se considera ejecutable solamente cuando existe una operación implementada y una prueba automatizada para sus casos relevantes.

Las reglas que dependen de catálogos externos, cruces con fuentes externas, reglas retiradas o reglas todavía no traducidas a operaciones ejecutables permanecen diferenciadas hasta disponer de una implementación verificable.
