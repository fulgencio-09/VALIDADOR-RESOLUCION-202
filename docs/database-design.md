# Modelo de datos inicial

## Entidades principales

- `users`: usuarios de la aplicación.
- `files`: archivos cargados y sus metadatos.
- `file_records`: registros físicos/lógicos del archivo.
- `file_record_values`: valores por variable de cada registro.
- `variables`: catálogo de las variables del anexo técnico.
- `validation_rules`: reglas parametrizadas.
- `validation_results`: ejecuciones de reglas sobre registros.
- `validation_errors`: catálogo de errores y advertencias.
- `correction_rules`: reglas de corrección autorizadas.
- `correction_history`: trazabilidad de modificaciones.
- `report_periods`: periodos reportados.
- `file_versions`: versiones de archivos generadas durante el proceso.

## Relación conceptual

```text
files
  |
  +-- file_records
         |
         +-- file_record_values -- variables
         |
         +-- validation_results -- validation_rules
                                      |
                                      +-- validation_errors
                                      +-- correction_rules
```

El diseño definitivo se construirá después de cargar y normalizar la matriz oficial de variables y reglas. No se deben inventar campos que no estén justificados por los requerimientos del proyecto.
