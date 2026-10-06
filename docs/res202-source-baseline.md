# Línea base normativa — Resolución 202 de 2021

## Fuentes utilizadas

- `resolucion_minsaludps_0202_2021.pdf`
- `Lineamientos-anexo-tecnico-res-202-2021-v8.xlsx`
- `440900022701_30092026.xlsx`
- `440900022701_30092026.txt`

## Alcance confirmado

La Resolución 202 de 2021 modifica el artículo 10 de la Resolución 4505 de 2012 y sustituye su anexo técnico. Establece dos estructuras: Anexo Técnico 1, registro por persona de actividades de Protección Específica y Detección Temprana, y Anexo Técnico 2, registro de novedades por persona. La resolución indica que los registros se reportan mediante archivos planos y que los campos están separados por `|`.

Para el Anexo Técnico 1, el nombre del archivo sigue una estructura fija y el ejemplo oficial es `SGD280RPEDAAAAAMMDDNI999999999999S01.TXT`, con longitud 39. El archivo contiene un Registro Tipo 1 de control y registros Tipo 2 de detalle. El Registro Tipo 1 es obligatorio y debe ser el primero; su campo de total de registros debe corresponder a la cantidad de registros Tipo 2. El consecutivo del Tipo 2 inicia en 1 y aumenta de uno en uno.

## Catálogo de variables

La hoja `Lineamientos (2)` del archivo de lineamientos contiene 119 variables numeradas de 0 a 118. Para cada variable se dispone de nombre, longitud, tipo, valores permitidos, uso de valores permitidos y, cuando aplica, validación, código y descripción de inconsistencia.

El catálogo debe mantenerse parametrizado. No se implementarán las reglas directamente como condiciones dispersas en los controladores. La aplicación debe leer variables y reglas desde el catálogo para permitir mantenimiento y versionamiento.

## Reglas identificadas

La hoja `Lineamientos (2)` contiene códigos de inconsistencias asociados a las variables y sus validaciones. En la versión analizada se identifican 75 códigos con información de regla/descripción asociados a las variables.

La hoja `warn-err eliminados` se conserva como referencia histórica y no debe cargarse como reglas activas sin una revisión explícita de vigencia.

## Reglas estructurales iniciales

1. Validar nombre y estructura del archivo.
2. Validar existencia del Registro Tipo 1 y que sea el primer registro.
3. Validar registros Tipo 2.
4. Validar separación de campos mediante `|`.
5. Validar cantidad de campos de cada registro.
6. Validar consecutivo del Registro Tipo 2 desde 1, incrementando de uno en uno.
7. Validar que el total declarado en el Tipo 1 coincida con el número real de registros Tipo 2.
8. Validar longitud y tipo de cada variable.
9. Validar valores permitidos.
10. Ejecutar validaciones cruzadas entre variables cuando la regla lo indique.
11. Registrar código, variable, registro, valor recibido, mensaje y severidad.
12. Separar corrección automática de validación para conservar trazabilidad.

## Nota de implementación

El PDF oficial es la fuente normativa primaria. El archivo de lineamientos es la fuente operativa para parametrizar las variables y validaciones del sistema. Si existe una discrepancia entre una implementación propuesta y estas fuentes, no se debe resolver silenciosamente: debe documentarse y revisarse contra la fuente oficial.
