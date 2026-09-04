# Cambios técnicos

## Nueva fuente de verdad

`App\Services\AcademicPeriodResolver`

Responsabilidades:

- resolver `generación + cuatrimestre -> Periodo`;
- impedir elegir silenciosamente un `latest()` ambiguo;
- validar ciclo contra fechas;
- validar SEP/DIC, ENE/ABR y MAY/AGO;
- generar fechas reales de asistencia;
- producir etiquetas académicas inequívocas;
- auditar todos los periodos.

## Protección ante errores

`App\Exceptions\AcademicPeriodException`

Las inconsistencias académicas se consideran errores de validación documental (HTTP 422), no errores internos del servidor.

## Auditoría

`App\Console\Commands\AuditAcademicPeriods`

Comando:

```bash
php artisan academic-periods:audit
```

No modifica datos.

## Documentos redirigidos

`AcademicDocumentController` toma el control de las rutas históricas sensibles:

- constancia;
- lista de asistencia escolarizada;
- lista de asistencia semiescolarizada;
- lista de evaluación;
- calificaciones generales.

La boleta individual conserva su controlador, pero su dataset queda corregido en `BoletaService`.

## Listas masivas de profesor

`AcademicListaProfesorController` reemplaza únicamente la ruta del generador masivo. Cada combinación materia/generación resuelve su propio periodo histórico y usa las fechas reales registradas.

## Dashboard

No se elimina. Sigue representando el contexto académico activo del sistema. Lo que se elimina es su uso como fuente automática para reimpresiones históricas.
