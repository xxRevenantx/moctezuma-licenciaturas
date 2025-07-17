<div class="flex w-full flex-1 flex-col gap-4 rounded-xl">
    {{-- Sección del formulario --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-1">
        <div class="relative overflow-hidden bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">
            <form wire:submit.prevent="guardarDatos">
                <flux:field>
                    <flux:input wire:model.live="ciclo_escolar" :label="__('Ciclo escolar')" type="text" autofocus autocomplete="ciclo_escolar" />
                    <flux:input style="text-transform: uppercase" wire:model.live="periodo_escolar" :label="__('Periodo escolar')" type="text" autofocus autocomplete="periodo_escolar" />
                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <flux:button variant="primary" type="submit" class="w-full">{{ __('Guardar') }}</flux:button>
                        </div>
                    </div>
                </flux:field>
            </form>
        </div>
    </div>

                            <div class="grid gap-2 md:grid-cols-2">
                                <div class="relative bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">

                                    <div class="flex items-center justify-start">
                                        <flux:icon.user />
                                            <span> Profesores Activos:  <flux:badge color="indigo">{{ count($profesoresActivos) }}</flux:badge></span>
                                    </div>


                                </div>
                                <div class="relative bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800">Generaciones Activas:
                                    @foreach ($generacionesActivas as $generaciones )

                                            <flux:badge color="green"> {{ $generaciones->generacion }}</flux:badge>
                                    @endforeach

                                </div>

                            </div>

                            <div class="grid auto-rows-min md:grid-cols-2 gap-3">
                            {{-- Locales Activos --}}
                            <div
                                x-data="{
                                    open: JSON.parse(localStorage.getItem('localesActivos')) || false,
                                    toggle() {
                                        this.open = !this.open;
                                        localStorage.setItem('localesActivos', JSON.stringify(this.open));
                                    }
                                }"
                                class="relative bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800"
                            >
                                <div class="flex justify-between items-center ">
                                    <flux:callout icon="user" class="w-full" color="blue" inline>
                                        <h2 class="font-bold text-2xl">Alumnos Locales Activos</h2>
                                        <h2 class="text-4xl">{{ $totalLocalesActivos }}</h2>
                                        <h3>{{ $totalHombresLocalesActivos }} HOMBRES | {{ $totalMujeresLocalesActivos }} MUJERES</h3>


                                  <button @click="toggle" class="flex items-center justify-center gap-1 text-sm text-blue-600 hover:underline focus:outline-none">

                                        <template x-if="!open">
                                        <span class="flex items-center justify-center">
                                            Ver detalle
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="open">
                                        <span class="flex items-center">
                                            Ocultar
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </span>
                                    </template>
                                </button>
                                    </flux:callout>

                                </div>
                                <div x-show="open" x-collapse>
                                    <div class="space-y-4 mt-4">
                                        @foreach ($resumenPorLicenciatura as $resumen)
                                            <flux:badge class="w-full uppercase" color="indigo">
                                                <div class="flex flex-row items-center justify-between w-full">
                                                    <div class="flex flex-col align-left">
                                                        <p class="font-bold">{{ $resumen['licenciatura'] }}</p>
                                                        <flux:badge color="zinc">
                                                            {{ $resumen['hombres'] }} HOMBRES | {{ $resumen['mujeres'] }} MUJERES
                                                        </flux:badge>
                                                    </div>
                                                    <flux:badge color="green">Total: {{ $resumen['total'] }}</flux:badge>
                                                </div>
                                            </flux:badge>
                                            <flux:separator variant="subtle" />
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Locales Bajas --}}
                            <div
                                x-data="{
                                    open: JSON.parse(localStorage.getItem('localesBajas')) || false,
                                    toggle() {
                                        this.open = !this.open;
                                        localStorage.setItem('localesBajas', JSON.stringify(this.open));
                                    }
                                }"
                                class="relative bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800"
                            >
                                <div class="flex justify-between items-center mb-2">
                                    <flux:callout icon="user" class="w-full" color="red" inline>
                                        <h2 class="font-bold text-2xl">Alumnos Locales Bajas</h2>
                                        <h2 class="text-4xl">{{ $totalLocalesBaja }}</h2>
                                        <h3>{{ $totalHombresLocalesBaja }} HOMBRES | {{ $totalMujeresLocalesBaja }} MUJERES</h3>

                                    <button @click="toggle" class="flex items-center justify-center gap-1 text-sm text-blue-600 hover:underline focus:outline-none">

                                        <template x-if="!open">
                                        <span class="flex items-center justify-center">
                                            Ver detalle
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="open">
                                        <span class="flex items-center">
                                            Ocultar
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </span>
                                    </template>
                                </button>
                                    </flux:callout>

                                </div>
                                <div x-show="open" x-collapse>
                                    <div class="space-y-4 mt-4">
                                        @foreach ($resumenPorLicenciaturaBaja as $resumen)
                                            <flux:badge class="w-full uppercase" color="red">
                                                <div class="flex flex-row items-center justify-between w-full">
                                                    <div class="flex flex-col align-left">
                                                        <p class="font-bold">{{ $resumen['licenciatura'] }}</p>
                                                        <flux:badge color="red">
                                                            {{ $resumen['hombres'] }} HOMBRES | {{ $resumen['mujeres'] }} MUJERES
                                                        </flux:badge>
                                                    </div>
                                                    <flux:badge color="zinc">Total: {{ $resumen['total'] }}</flux:badge>
                                                </div>
                                            </flux:badge>
                                            <flux:separator variant="subtle" />
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                            {{-- Foráneos Activos --}}
                            <div
                                x-data="{
                                    open: JSON.parse(localStorage.getItem('foraneosActivos')) || false,
                                    toggle() {
                                        this.open = !this.open;
                                        localStorage.setItem('foraneosActivos', JSON.stringify(this.open));
                                    }
                                }"
                                class="relative bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800"
                            >
                                <div class="flex justify-between items-center mb-2">
                                    <flux:callout icon="user" class="w-full" color="blue" inline>
                                        <h2 class="font-bold text-2xl">Alumnos Foráneos Activos</h2>
                                        <h2 class="text-4xl">{{ $totalForaneosActivos ?? '0' }}</h2>
                                        <h3>{{ $totalHombresForaneosActivos }} HOMBRES | {{ $totalMujeresForaneosActivos }} MUJERES</h3>

                                    <button @click="toggle" class="flex items-center justify-center gap-1 text-sm text-blue-600 hover:underline focus:outline-none">

                                        <template x-if="!open">
                                        <span class="flex items-center justify-center">
                                            Ver detalle
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="open">
                                        <span class="flex items-center">
                                            Ocultar
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </span>
                                    </template>
                                </button>
                                    </flux:callout>

                                </div>
                                <div x-show="open" x-collapse>
                                    <div class="space-y-4 mt-4">
                                        @foreach ($resumenPorLicenciaturaForaneo as $resumen)
                                            <flux:badge class="w-full uppercase" color="indigo">
                                                <div class="flex flex-row items-center justify-between w-full">
                                                    <div class="flex flex-col align-left">
                                                        <p class="font-bold">{{ $resumen['licenciatura'] }}</p>
                                                        <flux:badge color="zinc">
                                                            {{ $resumen['hombres'] }} HOMBRES | {{ $resumen['mujeres'] }} MUJERES
                                                        </flux:badge>
                                                    </div>
                                                    <flux:badge color="green">Total: {{ $resumen['total'] }}</flux:badge>
                                                </div>
                                            </flux:badge>
                                            <flux:separator variant="subtle" />
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Foráneos Bajas --}}
                            <div
                                x-data="{
                                    open: JSON.parse(localStorage.getItem('foraneosBajas')) || false,
                                    toggle() {
                                        this.open = !this.open;
                                        localStorage.setItem('foraneosBajas', JSON.stringify(this.open));
                                    }
                                }"
                                class="relative bg-white rounded-xl border border-neutral-200 dark:border-neutral-700 p-5 dark:bg-neutral-800"
                            >
                                <div class="flex justify-between items-center mb-2">
                                    <flux:callout icon="user" class="w-full" color="red" inline>
                                        <h2 class="font-bold text-2xl">Alumnos Foráneos Bajas</h2>
                                        <h2 class="text-4xl">{{ $totalForaneosBaja ?? '0' }}</h2>
                                        <h3>{{ $totalHombresForaneosBaja }} HOMBRES | {{ $totalMujeresForaneosBaja }} MUJERES</h3>

                                    <button @click="toggle" class="flex items-center justify-center gap-1 text-sm text-blue-600 hover:underline focus:outline-none">

                                        <template x-if="!open">
                                        <span class="flex items-center justify-center">
                                            Ver detalle
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </span>
                                    </template>
                                    <template x-if="open">
                                        <span class="flex items-center">
                                            Ocultar
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </span>
                                    </template>
                                </button>

                                    </flux:callout>

                                </div>
                                <div x-show="open" x-collapse>
                                    <div class="space-y-4 mt-4">
                                        @foreach ($resumenPorLicenciaturaBajaForaneo as $resumen)
                                            <flux:badge class="w-full uppercase" color="red">
                                                <div class="flex flex-row items-center justify-between w-full">
                                                    <div class="flex flex-col align-left">
                                                        <p class="font-bold">{{ $resumen['licenciatura'] }}</p>
                                                        <flux:badge color="red">
                                                            {{ $resumen['hombres'] }} HOMBRES | {{ $resumen['mujeres'] }} MUJERES
                                                        </flux:badge>
                                                    </div>
                                                    <flux:badge color="zinc">Total: {{ $resumen['total'] }}</flux:badge>
                                                </div>
                                            </flux:badge>
                                            <flux:separator variant="subtle" />
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>


                   {{-- Puedes usar otro <x-placeholder-pattern /> o una gráfica --}}

                <<div
                x-data
                x-init="$nextTick(() => renderGraficaAlumnos())"
                class="bg-white rounded-xl p-6 shadow border dark:bg-neutral-800 dark:border-neutral-700 mt-6"
            >
                <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white">
                    Comparativa por Licenciatura (Locales y Foráneos)
                </h2>
                <canvas id="graficaAlumnos"></canvas>
            </div>




    {{-- Toast Message --}}
    @include('components.toast-message')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


  <script>
    function renderGraficaAlumnos() {
        const ctx = document.getElementById('graficaAlumnos');
        if (!ctx) return;

        // Si ya existe una gráfica previa, destrúyela
        if (window.graficaAlumnosInstance) {
            window.graficaAlumnosInstance.destroy();
        }

        const labels = @js($licenciaturas->pluck('nombre'));
        const dataHombresLocales = @js(collect($resumenPorLicenciatura)->pluck('hombres'));
        const dataMujeresLocales = @js(collect($resumenPorLicenciatura)->pluck('mujeres'));
        const dataHombresBajasLocales = @js(collect($resumenPorLicenciaturaBaja)->pluck('hombres'));
        const dataMujeresBajasLocales = @js(collect($resumenPorLicenciaturaBaja)->pluck('mujeres'));
        const dataHombresForaneos = @js(collect($resumenPorLicenciaturaForaneo)->pluck('hombres'));
        const dataMujeresForaneos = @js(collect($resumenPorLicenciaturaForaneo)->pluck('mujeres'));
        const dataHombresBajasForaneos = @js(collect($resumenPorLicenciaturaBajaForaneo)->pluck('hombres'));
        const dataMujeresBajasForaneos = @js(collect($resumenPorLicenciaturaBajaForaneo)->pluck('mujeres'));

        window.graficaAlumnosInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Locales Activos Hombres', data: dataHombresLocales, backgroundColor: '#3B82F6' },
                    { label: 'Locales Activos Mujeres', data: dataMujeresLocales, backgroundColor: '#60A5FA' },
                    { label: 'Locales Bajas Hombres', data: dataHombresBajasLocales, backgroundColor: '#F87171' },
                    { label: 'Locales Bajas Mujeres', data: dataMujeresBajasLocales, backgroundColor: '#FCA5A5' },
                    { label: 'Foráneos Activos Hombres', data: dataHombresForaneos, backgroundColor: '#10B981' },
                    { label: 'Foráneos Activos Mujeres', data: dataMujeresForaneos, backgroundColor: '#6EE7B7' },
                    { label: 'Foráneos Bajas Hombres', data: dataHombresBajasForaneos, backgroundColor: '#F59E0B' },
                    { label: 'Foráneos Bajas Mujeres', data: dataMujeresBajasForaneos, backgroundColor: '#FCD34D' },
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
    }
</script>




</div>
