# Integración — ciclos escolares y periodos académicos

## Objetivo

Eliminar la dependencia del **Dashboard actual** en documentos académicos históricos.

A partir de esta integración, la fuente de verdad para una boleta/documento histórico es el registro de `periodos` correspondiente a:

- generación;
- cuatrimestre;
- periodo SEP/DIC, ENE/ABR o MAY/AGO;
- fechas `inicio_periodo` y `termino_periodo`;
- ciclo escolar.

## Regla institucional implementada

| Periodo | Ejemplo | Ciclo correcto |
|---|---|---|
| SEP/DIC | SEP/DIC 2023 | 2023-2024 |
| ENE/ABR | ENE/ABR 2024 | 2023-2024 |
| MAY/AGO | MAY/AGO 2024 | 2023-2024 |
| SEP/DIC | SEP/DIC 2024 | 2024-2025 |

En otras palabras:

- **SEP/DIC inicia un ciclo nuevo**.
- **ENE/ABR y MAY/AGO pertenecen al ciclo iniciado en septiembre anterior**.

## Qué corrige

1. Boleta individual.
2. Boletas masivas: consolidado, por alumno y ZIP.
3. Boletas enviadas por correo, porque reutilizan el mismo dataset de boleta.
4. Listas de evaluación.
5. Listas de asistencia escolarizada.
6. Listas de asistencia semiescolarizada.
7. Listas masivas por profesor.
8. Calificaciones generales.
9. Constancias que imprimen ciclo/periodo.
10. Reportes generales por generación PDF/Excel/Word.
11. Selector de boletas con etiqueta inequívoca:
   `SEP/DIC 2023 · Ciclo 2023-2024 · 1.º cuatrimestre`.
12. Creación/edición de periodos: fechas obligatorias y ciclo sugerido desde la fecha de inicio.
13. Auditoría de periodos existentes, **sin modificar automáticamente la BD**.

## Qué NO cambia

- No crea ni modifica tablas.
- No migra calificaciones.
- No altera boletas/certificados ya almacenados como archivos.
- No cambia el Dashboard: sigue siendo válido como contexto del ciclo actualmente activo.
- No modifica expedientes de identidad.
- No corrige automáticamente registros históricos; primero los reporta.

## Instalación

1. Haz una copia de seguridad del proyecto y de la BD.
2. Extrae este ZIP.
3. Ejecuta desde una terminal:

```bash
php aplicar_integracion_ciclos_periodos.php "C:\xampp4\htdocs\laravel\moctezuma-licenciaturas"
```

El instalador crea un respaldo de todos los archivos que toca dentro de:

```text
storage/app/integration-backups/ciclos-periodos-AAAAmmdd-HHMMSS/
```

4. Limpia cachés:

```bash
php artisan optimize:clear
```

5. Audita la BD:

```bash
php artisan academic-periods:audit
```

Para obtener salida JSON:

```bash
php artisan academic-periods:audit --json
```

## Importante sobre la auditoría

Si existe, por ejemplo:

```text
ciclo_escolar = 2025-2028
inicio_periodo = 2025-09-01
```

se reportará como inconsistente porque septiembre de 2025 debe pertenecer al ciclo **2025-2026**.

El comando no ejecuta `UPDATE`, no elimina filas y no intenta adivinar la intención administrativa.

## Pruebas funcionales mínimas

### Generación 2023-2026

Verifica que:

- 1.º · SEP/DIC 2023 → **2023-2024**
- 2.º · ENE/ABR 2024 → **2023-2024**
- 3.º · MAY/AGO 2024 → **2023-2024**
- 4.º · SEP/DIC 2024 → **2024-2025**
- 5.º · ENE/ABR 2025 → **2024-2025**
- 6.º · MAY/AGO 2025 → **2024-2025**
- 7.º · SEP/DIC 2025 → **2025-2026**
- 8.º · ENE/ABR 2026 → **2025-2026**
- 9.º · MAY/AGO 2026 → **2025-2026**

### Asistencia histórica

Abre una lista correspondiente a un periodo pasado y comprueba que los días se generan entre `inicio_periodo` y `termino_periodo`, no con el año actual del servidor.

### Inconsistencia controlada

Prueba un periodo cuyo ciclo no coincida con sus fechas. El sistema debe impedir usarlo como fuente histórica y mostrar el motivo, en vez de imprimir silenciosamente un ciclo incorrecto.

## Base técnica usada para preparar este paquete

El instalador fue preparado contra el estado de `main` del repositorio:

```text
xxRevenantx/moctezuma-licenciaturas
commit cf6cf63a04fe1c1b4b7d516815bbc7feaf18c165
```

El instalador usa reemplazos estrictos. Si un archivo de tu copia local difiere en un bloque crítico, **se detiene** en vez de inventar o sobrescribir código incompatible.
