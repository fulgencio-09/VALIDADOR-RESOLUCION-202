# Cobertura de reglas RPED

Fuente: `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`, hoja `Lineamientos RPED`.

## Estado actual

| Métrica | Cantidad |
|---|---:|
| Reglas/códigos oficiales en catálogo | 395 |
| Reglas ejecutables en catálogos de validación | 163 |
| Reglas aún pendientes de implementación | 232 |
| Errores oficiales | 377 |
| Warnings oficiales | 18 |
| Reglas ejecutables del rango Error020-Error096 | 33 |
| Reglas ejecutables del bloque de fecha de corte | 26 |
| Reglas ejecutables del bloque fecha vs. nacimiento | 37 |
| Reglas ejecutables adicionales Error220-Error244 | 9 |
| Reglas ejecutables del bloque Error294-Error399 | 50 |

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

Los lineamientos presentan diferencias entre comparaciones estrictas (`<` o `>`) y no estrictas (`<=` o `>=`). El motor `date_before_birth` recibe `inclusive` para representar esa diferencia sin duplicar lógica.

Los comodines de fecha definidos por el anexo (`1800-01-01`, `1805-01-01`, `1810-01-01`, `1825-01-01`, `1830-01-01`, `1835-01-01`, `1845-01-01`) se excluyen de esta comparación.

No aparecen `Error187`, `Error204` ni `Error206` como códigos de error en las filas de reglas `170-209` de la fuente RPED v8 consultada; por eso no se agregan artificialmente al catálogo ejecutable.

## Reglas ejecutables adicionales Error220-Error244

Implementadas:

`Error220`, `Error222`, `Error223`, `Error227`, `Error232`, `Error237`, `Error242`, `Error243`, `Error244`.

Estas reglas se encuentran en `RpedAdditionalRuleCatalog.php` y `RpedValidator` las incorpora automáticamente al conjunto recibido por el validador.

## Bloque Error294-Error399

Se incorporó un lote de 50 reglas ejecutables directamente traducibles a operaciones del motor:

`Error294`, `Error296`, `Error299`, `Error300`, `Error301`, `Error304`, `Error305`, `Error306`, `Warning307`, `Error308`, `Error309`, `Error318`, `Error328`, `Error329`, `Error341`, `Error344`, `Error346`, `Error350`, `Error352`, `Error354`, `Error355`, `Error359`, `Error361`, `Error362`, `Error364`, `Error367`, `Error368`, `Error369`, `Error371`, `Error375`, `Error379`, `Error380`, `Error381`, `Error382`, `Error383`, `Error384`, `Error385`, `Error386`, `Error387`, `Error388`, `Error389`, `Error390`, `Error391`, `Error392`, `Error393`, `Error394`, `Error395`, `Error396`, `Error398`, `Error399`.

El lote está separado en `RpedBatch294RuleCatalog.php` para mantener el catálogo base estable. `RpedValidator` lo incorpora junto con el catálogo adicional existente.

Para soportar las reglas de comparación entre dos campos se añadieron al motor las operaciones de condición `gt_field`, `gte_field`, `lt_field`, `lte_field`, `eq_field` y `neq_field`.

También se reutiliza `wildcard_allowed` para las reglas de comodines de fechas específicas del anexo.

## Validación con TXT real

El archivo `440900022701_30092026.txt` contiene 431 registros tipo 2 y 119 campos por registro.

Los bloques anteriores ya fueron probados contra el TXT real. El nuevo lote Error294-Error399 queda incorporado al validador y cuenta con pruebas unitarias; la siguiente verificación de integración debe ejecutar el lote completo sobre el TXT real y documentar sus violaciones antes de declarar cerrada esta familia.

## Criterio de cobertura

El catálogo oficial contiene 395 códigos, pero no significa que los 395 estén implementados. Una regla se considera ejecutable solamente cuando existe una operación implementada y una prueba automatizada para sus casos relevantes.

Las reglas que dependen de catálogos externos, cruces con fuentes externas, reglas retiradas o reglas todavía no traducidas a operaciones ejecutables permanecen diferenciadas hasta disponer de una implementación verificable.
