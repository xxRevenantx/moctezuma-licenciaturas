<?php

declare(strict_types=1);

function fail(string $message): never
{
    fwrite(STDERR, "[ERROR] {$message}\n");
    exit(1);
}

function info(string $message): void
{
    fwrite(STDOUT, "[OK] {$message}\n");
}

function normalizePath(string $path): string
{
    return rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
}

function backupFile(string $root, string $relative, string $backupRoot): void
{
    $source = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

    if (!is_file($source)) {
        return;
    }

    $target = $backupRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $dir = dirname($target);

    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fail("No se pudo crear el directorio de respaldo {$dir}");
    }

    if (!copy($source, $target)) {
        fail("No se pudo respaldar {$relative}");
    }
}

function putPayloadFile(string $root, string $packageRoot, string $relative): void
{
    $source = $packageRoot.DIRECTORY_SEPARATOR.'payload'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

    if (!is_file($source)) {
        fail("Falta el archivo de integración: {$source}");
    }

    $dir = dirname($target);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fail("No se pudo crear {$dir}");
    }

    if (!copy($source, $target)) {
        fail("No se pudo copiar {$relative}");
    }

    info("Instalado {$relative}");
}

function replaceExact(string $path, string $from, string $to, string $label): void
{
    $content = file_get_contents($path);
    if ($content === false) {
        fail("No se pudo leer {$path}");
    }

    if (str_contains($content, $to)) {
        info("{$label}: ya estaba integrado.");
        return;
    }

    if (!str_contains($content, $from)) {
        fail("{$label}: no encontré el bloque esperado. No se modificó el archivo para evitar corrupción.");
    }

    $new = str_replace($from, $to, $content, $count);

    if ($count !== 1) {
        fail("{$label}: se esperaban 1 coincidencia y se encontraron {$count}.");
    }

    if (file_put_contents($path, $new) === false) {
        fail("No se pudo escribir {$path}");
    }

    info($label);
}

$packageRoot = __DIR__;
$root = normalizePath($argv[1] ?? getcwd());

if (!is_file($root.DIRECTORY_SEPARATOR.'artisan')) {
    fail('La ruta indicada no parece ser la raíz de Laravel. Debe contener el archivo artisan.');
}

$required = [
    'app/Services/Boletas/BoletaService.php',
    'app/Observers/PeriodoObserver.php',
    'app/Livewire/Admin/Periodo/CrearPeriodo.php',
    'app/Livewire/Admin/Periodo/EditarPeriodo.php',
    'app/Livewire/Admin/Documentacion/BoletasMasivas.php',
    'resources/views/livewire/admin/documentacion/boletas-masivas.blade.php',
    'app/Http/Controllers/ReporteGeneracionController.php',
    'routes/admin.php',
];

foreach ($required as $relative) {
    if (!is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        fail("Falta {$relative}. La integración se detuvo antes de cambiar archivos.");
    }
}

$stamp = date('Ymd-His');
$backupRoot = $root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'integration-backups'.DIRECTORY_SEPARATOR.'ciclos-periodos-'.$stamp;

foreach ($required as $relative) {
    backupFile($root, $relative, $backupRoot);
}

info("Respaldo creado en {$backupRoot}");

foreach ([
    'app/Exceptions/AcademicPeriodException.php',
    'app/Services/AcademicPeriodResolver.php',
    'app/Console/Commands/AuditAcademicPeriods.php',
    'app/Http/Controllers/AcademicDocumentController.php',
    'app/Http/Controllers/AcademicListaProfesorController.php',
] as $relative) {
    putPayloadFile($root, $packageRoot, $relative);
}

/* 1) BoletaService: el ciclo histórico sale del Periodo, no del Dashboard. */
$boletaService = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR.'Boletas'.DIRECTORY_SEPARATOR.'BoletaService.php';

replaceExact(
    $boletaService,
    <<<'PHP'
    public function periodo(int $generacionId, int $cuatrimestreId): Periodo
    {
        return Periodo::query()
            ->with(['cuatrimestre', 'mes', 'generacion'])
            ->where('generacion_id', $generacionId)
            ->where('cuatrimestre_id', $cuatrimestreId)
            ->firstOrFail();
    }
PHP,
    <<<'PHP'
    public function periodo(int $generacionId, int $cuatrimestreId): Periodo
    {
        return app(\App\Services\AcademicPeriodResolver::class)
            ->resolveFor($generacionId, $cuatrimestreId);
    }
PHP,
    'BoletaService: resolución histórica de periodo'
);

replaceExact(
    $boletaService,
    "'ciclo_escolar' => Dashboard::query()->latest('id')->first(),",
    "'ciclo_escolar' => \$periodo,",
    'BoletaService: ciclo escolar desde periodos'
);

/* 2) Observer: bloquea periodos incoherentes sin autocorregir. */
$observer = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Observers'.DIRECTORY_SEPARATOR.'PeriodoObserver.php';

replaceExact(
    $observer,
    <<<'PHP'
    public function deleted(Periodo $periodo)
PHP,
    <<<'PHP'
    public function saving(Periodo $periodo): void
    {
        try {
            app(\App\Services\AcademicPeriodResolver::class)->assertConsistent($periodo);
        } catch (\App\Exceptions\AcademicPeriodException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'ciclo_escolar' => $e->getMessage(),
            ]);
        }
    }

    public function deleted(Periodo $periodo)
PHP,
    'PeriodoObserver: validación de consistencia'
);

/* 3) Formularios de periodos: fechas obligatorias + sugerencia automática del ciclo. */
foreach ([
    'app/Livewire/Admin/Periodo/CrearPeriodo.php',
    'app/Livewire/Admin/Periodo/EditarPeriodo.php',
] as $relative) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);

    replaceExact(
        $path,
        <<<'PHP'
            // dd($this->mesesPeriodo);
PHP,
        <<<'PHP'
            if ($propertyName === 'inicio_periodo' && $this->inicio_periodo) {
                $this->ciclo_escolar = app(\App\Services\AcademicPeriodResolver::class)
                    ->expectedCycleFromDate($this->inicio_periodo);
            }

            // dd($this->mesesPeriodo);
PHP,
        basename($relative).': ciclo sugerido desde la fecha'
    );

    replaceExact(
        $path,
        "'inicio_periodo' => 'nullable|date',",
        "'inicio_periodo' => 'required|date',",
        basename($relative).': fecha de inicio obligatoria'
    );

    replaceExact(
        $path,
        "'termino_periodo' => 'nullable|date|after_or_equal:inicio_periodo',",
        "'termino_periodo' => 'required|date|after_or_equal:inicio_periodo',",
        basename($relative).': fecha de término obligatoria'
    );
}

/* 4) Selector de boletas: etiqueta inequívoca periodo + año + ciclo + cuatrimestre. */
$boletasMasivas = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Livewire'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'Documentacion'.DIRECTORY_SEPARATOR.'BoletasMasivas.php';

replaceExact(
    $boletasMasivas,
    <<<'PHP'
                'periodo' => $periodo->mes?->meses_corto,
PHP,
    <<<'PHP'
                'periodo' => $periodo->mes?->meses_corto,
                'etiqueta_academica' => app(\App\Services\AcademicPeriodResolver::class)->label($periodo),
PHP,
    'BoletasMasivas: etiqueta académica'
);

$boletasView = $root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'livewire'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'documentacion'.DIRECTORY_SEPARATOR.'boletas-masivas.blade.php';

replaceExact(
    $boletasView,
    "{{ \$periodo['nombre'] }} · {{ \$periodo['ciclo_escolar'] }}",
    "{{ \$periodo['etiqueta_academica'] }}",
    'Vista de boletas: selector histórico'
);

/* 5) Reportes de generación: no toman el último Dashboard. */
$reporte = $root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'ReporteGeneracionController.php';

replaceExact(
    $reporte,
    <<<'PHP'
        $dashboard = Dashboard::query()->latest('id')->first();

        return [
PHP,
    <<<'PHP'
        $periodoAcademico = app(\App\Services\AcademicPeriodResolver::class)
            ->latestForGeneration($generacionId);

        return [
PHP,
    'ReporteGeneracion: contexto desde periodos'
);

replaceExact(
    $reporte,
    "'cicloEscolar' => \$dashboard?->ciclo_escolar ?? 'NO REGISTRADO',",
    "'cicloEscolar' => \$periodoAcademico?->ciclo_escolar ?? 'NO REGISTRADO',",
    'ReporteGeneracion: ciclo histórico'
);

replaceExact(
    $reporte,
    "'periodoEscolar' => \$dashboard?->periodo_escolar ?? 'NO REGISTRADO',",
    "'periodoEscolar' => \$periodoAcademico?->mes?->meses_corto ?? 'NO REGISTRADO',",
    'ReporteGeneracion: periodo histórico'
);

/* 6) Rutas: redirige documentos académicos sensibles a controladores corregidos. */
$routes = $root.DIRECTORY_SEPARATOR.'routes'.DIRECTORY_SEPARATOR.'admin.php';

replaceExact(
    $routes,
    "use App\\Http\\Controllers\\BoletaController;\n",
    "use App\\Http\\Controllers\\BoletaController;\nuse App\\Http\\Controllers\\AcademicDocumentController;\nuse App\\Http\\Controllers\\AcademicListaProfesorController;\n",
    'Rutas: imports académicos'
);

replaceExact(
    $routes,
    "[ListaProfesorController::class, 'masivas']",
    "[AcademicListaProfesorController::class, 'masivas']",
    'Rutas: listas masivas por profesor'
);

foreach ([
    'constancia',
    'lista_asistencia_escolarizada',
    'lista_asistencia_semiescolarizada',
    'lista_evaluacion',
    'calificaciones_generales',
] as $method) {
    replaceExact(
        $routes,
        "[PDFController::class, '{$method}']",
        "[AcademicDocumentController::class, '{$method}']",
        "Rutas: {$method}"
    );
}

info('Integración terminada.');
fwrite(STDOUT, "\nSiguientes comandos recomendados:\n");
fwrite(STDOUT, "  php artisan optimize:clear\n");
fwrite(STDOUT, "  php artisan academic-periods:audit\n");
fwrite(STDOUT, "\nSi la auditoría reporta inconsistencias, corrígelas manualmente en Periodos Escolares y vuelve a ejecutar la auditoría.\n");
