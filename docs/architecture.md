# Arquitectura del sistema

## Flujo principal

```text
Usuario
  |
  v
Vue 3 + Vite
  |
  | HTTPS / REST API
  v
Laravel 12
  |
  +--> Motor de reglas de Resolución 202
  |
  +--> MySQL
  |
  +--> Redis / Queue
  |
  +--> Storage de archivos
```

## Capas del backend

1. **HTTP/API:** autenticación, carga de archivos, consultas y descargas.
2. **Application:** casos de uso de validación y corrección.
3. **Domain:** variables, reglas, resultados, severidades y correcciones.
4. **Infrastructure:** base de datos, almacenamiento y colas.

## Motor de validación

Cada variable debe poder definirse mediante metadatos y reglas, evitando condicionales dispersos en controladores.

Conceptualmente:

```text
Variable
 -> tipo
 -> longitud
 -> obligatorio
 -> valores permitidos
 -> formato
 -> regla de negocio
 -> variables relacionadas
 -> código de error/advertencia
 -> severidad
 -> corrección permitida
```

## Resultado de una validación

Una ejecución debe registrar como mínimo:

- archivo y versión
- fecha de ejecución
- número de registros
- variables evaluadas
- errores
- advertencias
- registros correctos
- reglas ejecutadas
- correcciones aplicadas
- estado final

## Correcciones

Las correcciones automáticas se limitarán a transformaciones determinísticas y autorizadas por la regla. Cuando no sea posible determinar el valor correcto, el sistema debe marcar el registro para revisión y no inventar información.
