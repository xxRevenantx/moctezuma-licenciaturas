<div class="school-dashboard flex w-full min-w-0 flex-1 flex-col gap-5 pb-8 sm:gap-6">
    {{-- Bienvenida y contexto del panel. --}}
    <section class="school-dashboard-welcome relative isolate overflow-hidden rounded-[24px] px-5 py-7 sm:px-8 sm:py-9">
        <div
            class="pointer-events-none absolute -right-10 -top-24 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl dark:bg-sky-700/15">
        </div>
        <div
            class="pointer-events-none absolute bottom-[-8rem] right-[24%] h-64 w-64 rounded-full bg-lime-300/20 blur-3xl dark:bg-lime-800/10">
        </div>
        <div class="relative flex flex-wrap items-center justify-between gap-5">
            <div>
                <span
                    class="inline-flex items-center gap-2 rounded-full border border-[#006492]/10 bg-white/75 px-3 py-1 text-[11px] font-extrabold uppercase tracking-[.14em] text-[#006492] shadow-sm dark:border-white/10 dark:bg-slate-800/70 dark:text-sky-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#88AC2E]"></span> Centro Universitario Moctezuma
                </span>
                <h1 class="mt-3 text-2xl font-black tracking-tight text-[#10254b] dark:text-white sm:text-4xl">Panel
                    académico</h1>
                <p class="mt-1 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-300 sm:text-base">Consulta los
                    indicadores de tu institución y administra el ciclo escolar desde un solo lugar.</p>
            </div>
            <div
                class="hidden shrink-0 items-center gap-3 rounded-2xl border border-white/80 bg-white/70 px-4 py-3 shadow-sm backdrop-blur-sm dark:border-white/10 dark:bg-slate-800/50 lg:flex">
                <div
                    class="flex size-11 items-center justify-center rounded-xl bg-[#006492]/10 text-[#006492] dark:bg-sky-900/40 dark:text-sky-300">
                    <flux:icon.academic-cap class="size-6" />
                </div>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Gestión
                        escolar</p>
                    <p class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Licenciaturas</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Los datos de alerta se preparan en Dashboard::render(), sin PHP ejecutable en la vista. --}}
    @if ($alertasAvance)
        <section class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 shadow-sm dark:border-amber-800 dark:bg-amber-950/20" role="alert">
            <div>
                <h2 class="font-bold text-amber-900 dark:text-amber-200">Control de avance académico</h2>
                <p class="mt-1 text-sm text-amber-800 dark:text-amber-300">
                    {{ $alertasAvance['total_pendientes'] }} pendientes ·
                    {{ $alertasAvance['total_criticos'] }} prioritarios ·
                    {{ $alertasAvance['total_proximos'] }} próximos ·
                    {{ count($alertasAvance['conflictos']) }} calendarios por revisar
                </p>
            </div>
            <flux:button href="{{ route('admin.avance-academico') }}" variant="primary">Revisar ahora</flux:button>
        </section>
    @endif

    {{-- Configuración existente: conserva la captura libre y guardarDatos. --}}
    <section
        class="school-cycle-card relative isolate overflow-hidden rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/40 dark:bg-slate-900 sm:p-7">
        <div
            class="pointer-events-none absolute -right-12 -top-16 h-72 w-72 rounded-full bg-sky-300/25 blur-3xl dark:bg-sky-800/15">
        </div>
        <div
            class="pointer-events-none absolute right-12 top-0 h-36 w-36 rotate-45 bg-emerald-300/10 dark:bg-emerald-700/10">
        </div>
        <div class="relative flex flex-col gap-5">
            <div class="flex flex-wrap items-center gap-4">
                <div
                    class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-[#008c7d] text-white shadow-lg shadow-emerald-600/20">
                    <flux:icon.academic-cap class="size-7" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-black text-[#10254b] dark:text-slate-50 sm:text-xl">Configuración del
                            ciclo escolar</h2>
                        <span
                            class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-800/60">Configuración
                            global</span>
                    </div>
                    <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300">El ciclo y periodo guardados se
                        muestran en la barra superior del sistema.</p>
                </div>
            </div>
            <form wire:submit.prevent="guardarDatos"
                class="grid items-end gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] lg:gap-4">
                <div class="school-cycle-control min-w-0">
                    <flux:input wire:model="ciclo_escolar" :label="__('Ciclo escolar')" type="text"
                        autocomplete="off" placeholder="Ej. 2026-2027" required />
                    @error('ciclo_escolar')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="school-cycle-control min-w-0">
                    <flux:input wire:model="periodo_escolar" :label="__('Periodo escolar')" type="text"
                        autocomplete="off" placeholder="Ej. SEP/DIC" required />
                    @error('periodo_escolar')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="guardarDatos"
                    class="school-save-button !min-h-10 !rounded-xl !px-6 !font-extrabold">
                    <span wire:loading.remove wire:target="guardarDatos" class="inline-flex items-center gap-2">
                        <flux:icon.check class="size-4" /> Guardar cambios
                    </span>
                    <span wire:loading wire:target="guardarDatos">Guardando...</span>
                </flux:button>
            </form>
        </div>
    </section>

    {{-- Indicadores superiores; solo cifras derivadas de datos existentes. --}}
    <section class="grid gap-4 lg:grid-cols-2" aria-label="Indicadores institucionales">
        <article
            class="school-summary-card school-summary-rose relative overflow-hidden rounded-[22px] border p-5 shadow-sm sm:p-6">
            <div class="relative flex items-start gap-4">
                <span
                    class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300"><flux:icon.user-group
                        class="size-7" /></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold text-pink-900 dark:text-pink-200">Profesores activos</p>
                    <p class="mt-1 text-4xl font-black leading-none tracking-tight text-[#10254b] dark:text-white">
                        {{ number_format($profesoresActivos) }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Docentes con cuenta activa</p>
                </div>
            </div>
            <div
                class="mt-5 flex items-center justify-between border-t border-pink-100/80 pt-3 text-xs font-semibold text-slate-600 dark:border-pink-900/40 dark:text-slate-300">
                <span>Docentes habilitados en el sistema</span>
                <span class="inline-flex items-center gap-1 font-extrabold text-pink-700 dark:text-pink-300"><span
                        class="size-1.5 rounded-full bg-emerald-500"></span> Activos</span>
            </div>
        </article>

        <article
            class="school-summary-card school-summary-blue relative overflow-hidden rounded-[22px] border p-5 shadow-sm sm:p-6">
            <div class="relative flex items-start gap-4">
                <span
                    class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-[#006492] dark:bg-sky-900/30 dark:text-sky-300"><flux:icon.rectangle-stack
                        class="size-7" /></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold text-blue-900 dark:text-blue-200">Generaciones activas</p>
                    <p class="mt-1 text-4xl font-black leading-none tracking-tight text-[#10254b] dark:text-white">
                        {{ $generacionesActivas->count() }}</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Generaciones actualmente habilitadas</p>
                </div>
            </div>
            <div class="mt-5 border-t border-sky-100/80 pt-3 dark:border-sky-900/40">
                <div class="flex flex-wrap gap-1.5">
                    @forelse ($generacionesActivas as $generacion)
                        <span
                            class="inline-flex items-center rounded-lg bg-white/80 px-2 py-1 text-[11px] font-bold text-[#006492] ring-1 ring-sky-100 dark:bg-slate-800 dark:text-sky-300 dark:ring-sky-900/60">{{ $generacion->generacion }}</span>
                    @empty
                        <span class="text-xs text-slate-500 dark:text-slate-400">No hay generaciones activas.</span>
                    @endforelse
                </div>
            </div>
        </article>
    </section>

    @php
        $tarjetasAlumnos = [
            [
                'id' => 'localesActivos',
                'titulo' => 'Alumnos locales activos',
                'descripcion' => 'Matrícula local activa',
                'total' => $totalLocalesActivos,
                'hombres' => $totalHombresLocalesActivos,
                'mujeres' => $totalMujeresLocalesActivos,
                'resumen' => $resumenPorLicenciatura,
                'tema' => 'verde',
            ],
            [
                'id' => 'localesBajas',
                'titulo' => 'Alumnos locales de baja',
                'descripcion' => 'Bajas de estudiantes locales',
                'total' => $totalLocalesBaja,
                'hombres' => $totalHombresLocalesBaja,
                'mujeres' => $totalMujeresLocalesBaja,
                'resumen' => $resumenPorLicenciaturaBaja,
                'tema' => 'naranja',
            ],
            [
                'id' => 'foraneosActivos',
                'titulo' => 'Alumnos foráneos activos',
                'descripcion' => 'Matrícula foránea activa',
                'total' => $totalForaneosActivos,
                'hombres' => $totalHombresForaneosActivos,
                'mujeres' => $totalMujeresForaneosActivos,
                'resumen' => $resumenPorLicenciaturaForaneo,
                'tema' => 'azul',
            ],
            [
                'id' => 'foraneosBajas',
                'titulo' => 'Alumnos foráneos de baja',
                'descripcion' => 'Bajas de estudiantes foráneos',
                'total' => $totalForaneosBaja,
                'hombres' => $totalHombresForaneosBaja,
                'mujeres' => $totalMujeresForaneosBaja,
                'resumen' => $resumenPorLicenciaturaBajaForaneo,
                'tema' => 'rosa',
            ],
        ];
    @endphp

    {{-- Cuatro tarjetas compactas con detalles reales por licenciatura y sexo. --}}
    <section class="grid auto-rows-min gap-4 md:grid-cols-2 2xl:grid-cols-4" aria-label="Estadísticas de alumnos">
        @foreach ($tarjetasAlumnos as $tarjeta)
            @php
                $maximo = max(1, ...array_merge([0], array_column($tarjeta['resumen'], 'total')));
            @endphp
            <article wire:key="dashboard-tarjeta-{{ $tarjeta['id'] }}" x-data="{
                open: false,
                iniciar() {
                    try { this.open = JSON.parse(localStorage.getItem('{{ $tarjeta['id'] }}') || 'false'); } catch (error) { this.open = false; }
                },
                alternar() {
                    this.open = !this.open;
                    try { localStorage.setItem('{{ $tarjeta['id'] }}', JSON.stringify(this.open)); } catch (error) {}
                }
            }" x-init="iniciar()"
                class="school-student-card school-student-{{ $tarjeta['tema'] }} min-w-0 overflow-hidden rounded-[22px] border p-4 shadow-sm transition-shadow hover:shadow-md sm:p-5">
                <div class="flex items-start gap-3">
                    <span
                        class="school-student-icon flex size-12 shrink-0 items-center justify-center rounded-2xl"><flux:icon.user-group
                            class="size-6" /></span>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold leading-5 text-[#10254b] dark:text-slate-100">
                            {{ $tarjeta['titulo'] }}</h3>
                        <p class="mt-2 text-4xl font-black leading-none tracking-tight text-[#10254b] dark:text-white">
                            {{ number_format($tarjeta['total']) }}</p>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $tarjeta['descripcion'] }}</p>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold">
                    <span
                        class="rounded-lg bg-white/75 px-2.5 py-1.5 text-slate-700 ring-1 ring-slate-200/70 dark:bg-slate-800/70 dark:text-slate-200 dark:ring-slate-700">{{ $tarjeta['hombres'] }}
                        hombres</span>
                    <span
                        class="rounded-lg bg-white/75 px-2.5 py-1.5 text-slate-700 ring-1 ring-slate-200/70 dark:bg-slate-800/70 dark:text-slate-200 dark:ring-slate-700">{{ $tarjeta['mujeres'] }}
                        mujeres</span>
                </div>
                <div class="school-student-chart mt-4">
                    <div
                        class="flex items-center justify-between gap-2 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <span>Por licenciatura</span><span>{{ count($tarjeta['resumen']) }} programas</span>
                    </div>
                    <div class="mt-2 flex h-12 items-end gap-1.5" aria-hidden="true">
                        @forelse ($tarjeta['resumen'] as $resumen)
                            <span class="school-student-bar min-w-0 flex-1 rounded-t"
                                style="height: {{ $resumen['total'] > 0 ? max(7, round(($resumen['total'] / $maximo) * 100)) : 0 }}%"
                                title="{{ $resumen['licenciatura'] }}: {{ $resumen['total'] }}"></span>
                        @empty
                            <span class="w-full border-b border-dashed border-slate-300 dark:border-slate-600"></span>
                        @endforelse
                    </div>
                </div>
                <button type="button" @click="alternar()" :aria-expanded="open.toString()"
                    aria-controls="detalle-{{ $tarjeta['id'] }}"
                    class="school-student-detail-button mt-4 flex w-full items-center justify-between border-t border-current/10 pt-3 text-left text-xs font-extrabold transition hover:opacity-75 focus-visible:outline-2 focus-visible:outline-offset-4">
                    <span x-text="open ? 'Ocultar desglose' : 'Ver desglose por licenciatura'"></span>
                    <flux:icon.chevron-down class="size-4 transition-transform"
                        x-bind:class="open ? 'rotate-180' : ''" />
                </button>
                <div id="detalle-{{ $tarjeta['id'] }}" x-cloak x-show="open" x-collapse
                    class="mt-4 space-y-2 border-t border-slate-200/70 pt-4 dark:border-slate-700/70">
                    @forelse ($tarjeta['resumen'] as $resumen)
                        @php
                            $totalSexo = (int) $resumen['hombres'] + (int) $resumen['mujeres'];
                            $porcentajeH = $totalSexo > 0 ? round(((int) $resumen['hombres'] / $totalSexo) * 100) : 0;
                            $porcentajeM = $totalSexo > 0 ? 100 - $porcentajeH : 0;
                        @endphp
                        <div
                            class="rounded-xl border border-slate-200/80 bg-white/75 p-3 dark:border-slate-700/80 dark:bg-slate-800/60">
                            <div class="flex items-start justify-between gap-2">
                                <p
                                    class="min-w-0 break-words text-xs font-extrabold leading-5 text-slate-800 dark:text-slate-100">
                                    {{ $resumen['licenciatura'] }}</p>
                                <span
                                    class="shrink-0 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-black text-slate-700 dark:bg-slate-700 dark:text-slate-100">{{ $resumen['total'] }}</span>
                            </div>
                            <p class="mt-1.5 text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ $resumen['hombres'] }} hombres · {{ $resumen['mujeres'] }} mujeres</p>
                            <div class="mt-2 flex h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700"
                                role="img"
                                aria-label="{{ $resumen['hombres'] }} hombres y {{ $resumen['mujeres'] }} mujeres">
                                <div class="bg-[#006492]" style="width: {{ $porcentajeH }}%"></div>
                                <div class="bg-pink-400" style="width: {{ $porcentajeM }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 dark:text-slate-400">No hay licenciaturas para mostrar.</p>
                    @endforelse
                </div>
            </article>
        @endforeach
    </section>

    {{-- Panel de progreso existente: se mantiene íntegro. --}}

    <livewire:admin.progreso.panel-progreso-calificaciones />


    {{-- 5) GRÁFICAS CON APEXCHARTS --}}
    <section class="grid gap-4 xl:grid-cols-3" aria-label="Gráficas académicas">
        <article
            class="school-chart-card rounded-[22px] border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-800 sm:p-6 xl:col-span-2">
            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">
                        Matrícula activa
                    </p>
                    <h2 class="mt-1 text-xl font-bold text-neutral-900 dark:text-white sm:text-2xl">
                        Alumnos activos por licenciatura
                    </h2>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Comparación por procedencia y sexo, considerando únicamente generaciones activas.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 text-xs">
                    <span
                        class="rounded-full bg-sky-50 px-3 py-1.5 font-semibold text-sky-700 dark:bg-sky-950/50 dark:text-sky-200">
                        {{ $chartData['totales']['activos'] ?? 0 }} activos
                    </span>
                    <span
                        class="rounded-full bg-neutral-100 px-3 py-1.5 font-semibold text-neutral-700 dark:bg-neutral-700 dark:text-neutral-200">
                        {{ $chartData['totales']['hombresActivos'] ?? 0 }} H ·
                        {{ $chartData['totales']['mujeresActivas'] ?? 0 }} M
                    </span>
                </div>
            </div>

            <div wire:ignore id="dashboard-alumnos-activos-chart" class="min-h-[390px] w-full"></div>
        </article>

        <article
            class="school-chart-card rounded-[22px] border border-neutral-200 bg-white p-5 shadow-sm dark:border-neutral-700 dark:bg-neutral-800 sm:p-6">
            <div class="mb-3">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">
                    Estado general
                </p>
                <h2 class="mt-1 text-xl font-bold text-neutral-900 dark:text-white">
                    Activos y bajas
                </h2>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Distribución total entre alumnado local y foráneo.
                </p>
            </div>

            <div wire:ignore id="dashboard-estado-general-chart" class="min-h-[330px] w-full"></div>

            <div class="mt-3 grid grid-cols-2 gap-3">
                <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/40">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">
                        Activos</p>
                    <p class="mt-1 text-2xl font-extrabold text-emerald-900 dark:text-emerald-100">
                        {{ $chartData['totales']['activos'] ?? 0 }}
                    </p>
                </div>
                <div class="rounded-xl bg-red-50 p-3 dark:bg-red-950/40">
                    <p class="text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">Bajas</p>
                    <p class="mt-1 text-2xl font-extrabold text-red-900 dark:text-red-100">
                        {{ $chartData['totales']['bajas'] ?? 0 }}
                    </p>
                </div>
            </div>
        </article>
    </section>

    @assets
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@6.5.0/dist/apexcharts.min.js"></script>
    @endassets

    @script
        <script>
            (() => {
                const chartData = @js($chartData);
                const isDark = document.documentElement.classList.contains('dark');
                const textColor = isDark ? '#e5e7eb' : '#374151';
                const mutedColor = isDark ? '#9ca3af' : '#6b7280';
                const gridColor = isDark ? '#374151' : '#e5e7eb';

                window.moctezumaDashboardCharts ??= {};

                Object.values(window.moctezumaDashboardCharts).forEach((chart) => {
                    if (chart && typeof chart.destroy === 'function') {
                        chart.destroy();
                    }
                });

                window.moctezumaDashboardCharts = {};

                const activeElement = document.querySelector('#dashboard-alumnos-activos-chart');
                const statusElement = document.querySelector('#dashboard-estado-general-chart');

                if (!window.ApexCharts) {
                    [activeElement, statusElement].filter(Boolean).forEach((element) => {
                        element.innerHTML =
                            '<div class="flex min-h-[260px] items-center justify-center text-sm text-neutral-500">No fue posible cargar las gráficas.</div>';
                    });
                    return;
                }

                if (activeElement) {
                    const activeChart = new ApexCharts(activeElement, {
                        chart: {
                            type: 'bar',
                            height: 410,
                            stacked: true,
                            fontFamily: 'Nunito, sans-serif',
                            foreColor: textColor,
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: false,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: false,
                                    reset: false,
                                },
                            },
                            animations: {
                                enabled: true,
                                speed: 450,
                            },
                        },
                        series: chartData.alumnosActivos ?? [],
                        colors: ['#006492', '#38bdf8', '#88AC2E', '#bef264'],
                        plotOptions: {
                            bar: {
                                borderRadius: 5,
                                borderRadiusApplication: 'end',
                                columnWidth: '62%',
                            },
                        },
                        dataLabels: {
                            enabled: false,
                        },
                        stroke: {
                            show: true,
                            width: 1,
                            colors: ['transparent'],
                        },
                        xaxis: {
                            categories: chartData.categorias ?? [],
                            labels: {
                                rotate: -25,
                                trim: true,
                                style: {
                                    colors: mutedColor,
                                    fontSize: '11px',
                                    fontWeight: 600,
                                },
                            },
                            axisBorder: {
                                color: gridColor,
                            },
                            axisTicks: {
                                color: gridColor,
                            },
                        },
                        yaxis: {
                            min: 0,
                            forceNiceScale: true,
                            labels: {
                                formatter: (value) => Math.round(value),
                                style: {
                                    colors: mutedColor,
                                },
                            },
                            title: {
                                text: 'Número de alumnos',
                                style: {
                                    color: mutedColor,
                                    fontWeight: 700,
                                },
                            },
                        },
                        grid: {
                            borderColor: gridColor,
                            strokeDashArray: 4,
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '12px',
                            labels: {
                                colors: textColor,
                            },
                            markers: {
                                size: 6,
                            },
                            itemMargin: {
                                horizontal: 10,
                                vertical: 5,
                            },
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            theme: isDark ? 'dark' : 'light',
                            y: {
                                formatter: (value) => `${value} alumno${value === 1 ? '' : 's'}`,
                            },
                        },
                        noData: {
                            text: 'No hay alumnos registrados para mostrar.',
                            align: 'center',
                            verticalAlign: 'middle',
                            style: {
                                color: mutedColor,
                            },
                        },
                        responsive: [{
                            breakpoint: 640,
                            options: {
                                chart: {
                                    height: 450,
                                },
                                plotOptions: {
                                    bar: {
                                        columnWidth: '76%',
                                    },
                                },
                                xaxis: {
                                    labels: {
                                        rotate: -45,
                                    },
                                },
                            },
                        }],
                    });

                    activeChart.render();
                    window.moctezumaDashboardCharts.active = activeChart;
                }

                if (statusElement) {
                    const originalSeries = chartData.estadoGeneral?.series ?? [];
                    const hasData = originalSeries.some((value) => Number(value) > 0);
                    const statusSeries = hasData ? originalSeries : [1];
                    const statusLabels = hasData ?
                        (chartData.estadoGeneral?.labels ?? []) : ['Sin registros'];

                    const statusChart = new ApexCharts(statusElement, {
                        chart: {
                            type: 'donut',
                            height: 340,
                            fontFamily: 'Nunito, sans-serif',
                            foreColor: textColor,
                            toolbar: {
                                show: false,
                            },
                        },
                        series: statusSeries,
                        labels: statusLabels,
                        colors: hasData ? ['#006492', '#88AC2E', '#ef4444', '#f59e0b'] : ['#d1d5db'],
                        stroke: {
                            width: 3,
                            colors: [isDark ? '#262626' : '#ffffff'],
                        },
                        dataLabels: {
                            enabled: hasData,
                            formatter: (value) => `${Math.round(value)}%`,
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: '12px',
                            labels: {
                                colors: textColor,
                            },
                            markers: {
                                size: 6,
                            },
                        },
                        tooltip: {
                            enabled: hasData,
                            theme: isDark ? 'dark' : 'light',
                            y: {
                                formatter: (value) => `${value} alumno${value === 1 ? '' : 's'}`,
                            },
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '68%',
                                    labels: {
                                        show: true,
                                        name: {
                                            show: true,
                                            color: mutedColor,
                                        },
                                        value: {
                                            show: true,
                                            color: textColor,
                                            fontSize: '28px',
                                            fontWeight: 800,
                                            formatter: (value) => hasData ? value : '0',
                                        },
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            color: mutedColor,
                                            formatter: () => hasData ?
                                                originalSeries.reduce((total, value) => total + Number(value || 0),
                                                    0) : 0,
                                        },
                                    },
                                },
                            },
                        },
                    });

                    statusChart.render();
                    window.moctezumaDashboardCharts.status = statusChart;
                }
            })();
        </script>
    @endscript

    {{-- Toast Message --}}
    @include('components.toast-message')
</div>
