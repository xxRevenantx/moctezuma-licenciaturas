<div class="flex w-full flex-1 flex-col gap-6 ">




    {{-- 1) FORMULARIO --}}
<div class="grid gap-4 md:grid-cols-1">
  <div class="relative overflow-hidden rounded-2xl p-5 sm:p-6 hover:shadow-lg transition-shadow
              bg-gradient-to-br from-emerald-400 via-teal-500 to-sky-500">

      {{-- Burbujas decorativas --}}
      <span class="pointer-events-none absolute -right-16 -top-14 h-48 w-48 rounded-full bg-white/25 blur-3xl"></span>
      <span class="pointer-events-none absolute left-10 -bottom-10 h-56 w-56 rounded-full bg-white/20 blur-3xl"></span>
      <span class="pointer-events-none absolute right-28 bottom-6 h-32 w-32 rounded-full bg-white/20 blur-2xl"></span>

      {{-- Contenido en panel translúcido --}}
      <div class="relative rounded-xl border border-white/20 bg-white/70 dark:bg-neutral-900/50
                  backdrop-blur-md p-5 sm:p-6">
          <div class="mb-4 flex items-center justify-between">
              <h3 class="text-base sm:text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                  Configuración del ciclo
              </h3>
              <span class="text-xs text-neutral-700/80 dark:text-neutral-300/90">
                  Actualiza ciclo y periodo
              </span>
          </div>

          <form wire:submit.prevent="guardarDatos" class="space-y-4">
              <flux:field>
                  <div class="grid gap-4 sm:grid-cols-2">
                      <flux:input
                          wire:model.live="ciclo_escolar"
                          :label="__('Ciclo escolar')"
                          type="text"
                          autofocus
                          autocomplete="ciclo_escolar"
                      />
                      <flux:input
                          class="uppercase"
                          wire:model.live="periodo_escolar"
                          :label="__('Periodo escolar')"
                          type="text"
                          autocomplete="periodo_escolar"
                      />
                  </div>

                  <div class="flex items-center justify-end pt-2">
                      <flux:button variant="primary" type="submit" class="min-w-[140px]">
                          {{ __('Guardar') }}
                      </flux:button>
                  </div>
              </flux:field>
          </form>
      </div>
  </div>
</div>


    {{-- 2) TARJETAS RESUMEN SUPERIOR --}}
    <div class="grid gap-4 sm:grid-cols-2">
        {{-- Profesores activos --}}
       {{-- Profesores activos --}}
<div class="relative overflow-hidden rounded-2xl p-5 sm:p-6 hover:shadow-lg transition-shadow
            bg-gradient-to-br from-rose-400 via-pink-400 to-orange-300">
    {{-- burbujas decorativas --}}
    <span class="pointer-events-none absolute -right-8 -top-8 h-36 w-36 rounded-full bg-white/25 blur-2xl"></span>
    <span class="pointer-events-none absolute left-8 -bottom-10 h-48 w-48 rounded-full bg-white/20 blur-3xl"></span>
    <span class="pointer-events-none absolute right-20 bottom-6 h-28 w-28 rounded-full bg-white/20 blur-2xl"></span>

    <div class="relative flex items-center gap-3">
        <div class="p-2.5 rounded-xl bg-white/25 ring-1 ring-white/30 text-white">
            <flux:icon.user class="w-5 h-5" />
        </div>
        <div class="flex-1">
            <p class="text-sm text-white/90">Profesores Activos</p>
            <p class="text-3xl font-extrabold text-white leading-tight drop-shadow-sm">
                {{ count($profesoresActivos) }}
            </p>
            <p class="mt-1 text-xs font-medium text-white/80">Increased by 40%</p>
        </div>
    </div>
</div>



       {{-- Generaciones activas --}}
<div class="relative overflow-hidden rounded-2xl p-5 sm:p-6 hover:shadow-lg transition-shadow
            bg-gradient-to-br from-sky-400 via-blue-500 to-indigo-500">
    {{-- burbujas decorativas --}}
    <span class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/25 blur-2xl"></span>
    <span class="pointer-events-none absolute left-10 bottom-0 h-48 w-48 rounded-full bg-white/20 blur-3xl"></span>
    <span class="pointer-events-none absolute right-24 bottom-8 h-28 w-28 rounded-full bg-white/20 blur-2xl"></span>

    <p class="relative text-sm text-white/90 mb-3">Generaciones Activas</p>
    <div class="relative flex flex-wrap gap-2">
        @foreach ($generacionesActivas as $generaciones)
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase
                         bg-white/20 ring-1 ring-white/30 text-white">
                {{ $generaciones->generacion }}
            </span>
        @endforeach
    </div>
</div>

    </div>

    {{-- 3) LOCALES: ACTIVOS / BAJAS --}}
    <div class="grid auto-rows-min md:grid-cols-2 gap-4">
        {{-- Locales Activos --}}
        <div
            x-data="{
                open: JSON.parse(localStorage.getItem('localesActivos')) ?? false,
                toggle(){ this.open = !this.open; localStorage.setItem('localesActivos', JSON.stringify(this.open)); }
            }"
            class="relative rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 sm:p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <flux:callout icon="user" class="w-full" color="blue" inline>
                    <h2 class="font-bold text-xl sm:text-2xl">Alumnos Locales Activos</h2>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold">{{ $totalLocalesActivos }}</span>
                        <span class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $totalHombresLocalesActivos }} H | {{ $totalMujeresLocalesActivos }} M
                        </span>
                    </div>
                </flux:callout>

                <button @click="toggle"
                    class="shrink-0 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200 focus:outline-none">
                    <template x-if="!open"><span class="flex items-center">Ver detalle
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span></template>
                    <template x-if="open"><span class="flex items-center">Ocultar
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </span></template>
                </button>
            </div>

            <div x-show="open" x-collapse class="mt-4">
                <div class="space-y-3">
                    @foreach ($resumenPorLicenciatura as $resumen)
                        @php
                            $t = max(1, (int)$resumen['hombres'] + (int)$resumen['mujeres']);
                            $pctH = round(($resumen['hombres'] / $t) * 100);
                            $pctM = 100 - $pctH;
                        @endphp
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-3 sm:p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate text-neutral-900 dark:text-neutral-100">{{ $resumen['licenciatura'] }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <flux:badge color="zinc">{{ $resumen['hombres'] }} H</flux:badge>
                                        <flux:badge color="zinc">{{ $resumen['mujeres'] }} M</flux:badge>
                                    </div>
                                </div>
                                <flux:badge color="green">Total: {{ $resumen['total'] }}</flux:badge>
                            </div>
                            <div class="mt-3 h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden flex">
                                <div class="h-full bg-blue-500" style="width: {{ $pctH }}%"></div>
                                <div class="h-full bg-pink-500" style="width: {{ $pctM }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Locales Bajas --}}
        <div
            x-data="{
                open: JSON.parse(localStorage.getItem('localesBajas')) ?? false,
                toggle(){ this.open = !this.open; localStorage.setItem('localesBajas', JSON.stringify(this.open)); }
            }"
            class="relative rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 sm:p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <flux:callout icon="user" class="w-full" color="red" inline>
                    <h2 class="font-bold text-xl sm:text-2xl">Alumnos Locales Bajas</h2>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold">{{ $totalLocalesBaja }}</span>
                        <span class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $totalHombresLocalesBaja }} H | {{ $totalMujeresLocalesBaja }} M
                        </span>
                    </div>
                </flux:callout>

                <button @click="toggle"
                    class="shrink-0 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200 focus:outline-none">
                    <template x-if="!open"><span class="flex items-center">Ver detalle
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span></template>
                    <template x-if="open"><span class="flex items-center">Ocultar
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </span></template>
                </button>
            </div>

            <div x-show="open" x-collapse class="mt-4">
                <div class="space-y-3">
                    @foreach ($resumenPorLicenciaturaBaja as $resumen)
                        @php
                            $t = max(1, (int)$resumen['hombres'] + (int)$resumen['mujeres']);
                            $pctH = round(($resumen['hombres'] / $t) * 100);
                            $pctM = 100 - $pctH;
                        @endphp
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-3 sm:p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate text-neutral-900 dark:text-neutral-100">{{ $resumen['licenciatura'] }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <flux:badge color="red">{{ $resumen['hombres'] }} H</flux:badge>
                                        <flux:badge color="red">{{ $resumen['mujeres'] }} M</flux:badge>
                                    </div>
                                </div>
                                <flux:badge color="zinc">Total: {{ $resumen['total'] }}</flux:badge>
                            </div>
                            <div class="mt-3 h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden flex">
                                <div class="h-full bg-rose-500" style="width: {{ $pctH }}%"></div>
                                <div class="h-full bg-amber-400" style="width: {{ $pctM }}%"></div>
                            </div>
                        </div>
                        <flux:separator variant="subtle" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 4) FORÁNEOS: ACTIVOS / BAJAS --}}
    <div class="grid auto-rows-min md:grid-cols-2 gap-4">
        {{-- Foráneos Activos --}}
        <div
            x-data="{
                open: JSON.parse(localStorage.getItem('foraneosActivos')) ?? false,
                toggle(){ this.open = !this.open; localStorage.setItem('foraneosActivos', JSON.stringify(this.open)); }
            }"
            class="relative rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 sm:p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <flux:callout icon="user" class="w-full" color="blue" inline>
                    <h2 class="font-bold text-xl sm:text-2xl">Alumnos Foráneos Activos</h2>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold">{{ $totalForaneosActivos ?? '0' }}</span>
                        <span class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $totalHombresForaneosActivos }} H | {{ $totalMujeresForaneosActivos }} M
                        </span>
                    </div>
                </flux:callout>

                <button @click="toggle"
                    class="shrink-0 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200 focus:outline-none">
                    <template x-if="!open"><span class="flex items-center">Ver detalle
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span></template>
                    <template x-if="open"><span class="flex items-center">Ocultar
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </span></template>
                </button>
            </div>

            <div x-show="open" x-collapse class="mt-4">
                <div class="space-y-3 mt-4">
                    @foreach ($resumenPorLicenciaturaForaneo as $resumen)
                        @php
                            $t = max(1, (int)$resumen['hombres'] + (int)$resumen['mujeres']);
                            $pctH = round(($resumen['hombres'] / $t) * 100);
                            $pctM = 100 - $pctH;
                        @endphp
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-3 sm:p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate text-neutral-900 dark:text-neutral-100">{{ $resumen['licenciatura'] }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <flux:badge color="zinc">{{ $resumen['hombres'] }} H</flux:badge>
                                        <flux:badge color="zinc">{{ $resumen['mujeres'] }} M</flux:badge>
                                    </div>
                                </div>
                                <flux:badge color="green">Total: {{ $resumen['total'] }}</flux:badge>
                            </div>
                            <div class="mt-3 h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden flex">
                                <div class="h-full bg-emerald-500" style="width: {{ $pctH }}%"></div>
                                <div class="h-full bg-teal-400" style="width: {{ $pctM }}%"></div>
                            </div>
                        </div>
                        <flux:separator variant="subtle" />
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Foráneos Bajas --}}
        <div
            x-data="{
                open: JSON.parse(localStorage.getItem('foraneosBajas')) ?? false,
                toggle(){ this.open = !this.open; localStorage.setItem('foraneosBajas', JSON.stringify(this.open)); }
            }"
            class="relative rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 sm:p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <flux:callout icon="user" class="w-full" color="red" inline>
                    <h2 class="font-bold text-xl sm:text-2xl">Alumnos Foráneos Bajas</h2>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-3xl sm:text-4xl font-extrabold">{{ $totalForaneosBaja ?? '0' }}</span>
                        <span class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $totalHombresForaneosBaja }} H | {{ $totalMujeresForaneosBaja }} M
                        </span>
                    </div>
                </flux:callout>

                <button @click="toggle"
                    class="shrink-0 inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200 focus:outline-none">
                    <template x-if="!open"><span class="flex items-center">Ver detalle
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span></template>
                    <template x-if="open"><span class="flex items-center">Ocultar
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </span></template>
                </button>
            </div>

            <div x-show="open" x-collapse class="mt-4">
                <div class="space-y-3">
                    @foreach ($resumenPorLicenciaturaBajaForaneo as $resumen)
                        @php
                            $t = max(1, (int)$resumen['hombres'] + (int)$resumen['mujeres']);
                            $pctH = round(($resumen['hombres'] / $t) * 100);
                            $pctM = 100 - $pctH;
                        @endphp
                        <div class="rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 p-3 sm:p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-semibold truncate text-neutral-900 dark:text-neutral-100">{{ $resumen['licenciatura'] }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                        <flux:badge color="red">{{ $resumen['hombres'] }} H</flux:badge>
                                        <flux:badge color="red">{{ $resumen['mujeres'] }} M</flux:badge>
                                    </div>
                                </div>
                                <flux:badge color="zinc">Total: {{ $resumen['total'] }}</flux:badge>
                            </div>
                            <div class="mt-3 h-2 w-full rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden flex">
                                <div class="h-full bg-amber-500" style="width: {{ $pctH }}%"></div>
                                <div class="h-full bg-yellow-300" style="width: {{ $pctM }}%"></div>
                            </div>
                        </div>
                        <flux:separator variant="subtle" />
                    @endforeach
                </div>
            </div>
        </div>
    </div>



    {{-- PANEL DE CALIFICACIONES Y BARRA DE PROGRESO --}}

    <livewire:admin.progreso.panel-progreso-calificaciones />


    {{-- 5) GRÁFICA (Chart.js) --}}
    <div
        x-data
        x-init="$nextTick(() => renderGraficaAlumnos())"
        class="bg-white rounded-2xl p-6 shadow border border-neutral-200 dark:bg-neutral-800 dark:border-neutral-700 mt-2"
    >
        <h2 class="text-xl sm:text-2xl font-bold mb-4 text-neutral-800 dark:text-white">
            Comparativa por Licenciatura (Locales y Foráneos)
        </h2>
        <div class="relative h-[360px] sm:h-[420px] lg:h-[520px]">
            <canvas id="graficaAlumnos" class="!w-full !h-full"></canvas>
        </div>
    </div>

    {{-- Toast Message --}}
    @include('components.toast-message')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function renderGraficaAlumnos() {
            const ctx = document.getElementById('graficaAlumnos');
            if (!ctx) return;

            if (window.graficaAlumnosInstance) {
                window.graficaAlumnosInstance.destroy();
            }

            const labels = @js($licenciaturas->pluck('nombre'));
            const dataHombresLocales         = @js(collect($resumenPorLicenciatura)->pluck('hombres'));
            const dataMujeresLocales         = @js(collect($resumenPorLicenciatura)->pluck('mujeres'));
            const dataHombresBajasLocales    = @js(collect($resumenPorLicenciaturaBaja)->pluck('hombres'));
            const dataMujeresBajasLocales    = @js(collect($resumenPorLicenciaturaBaja)->pluck('mujeres'));
            const dataHombresForaneos        = @js(collect($resumenPorLicenciaturaForaneo)->pluck('hombres'));
            const dataMujeresForaneos        = @js(collect($resumenPorLicenciaturaForaneo)->pluck('mujeres'));
            const dataHombresBajasForaneos   = @js(collect($resumenPorLicenciaturaBajaForaneo)->pluck('hombres'));
            const dataMujeresBajasForaneos   = @js(collect($resumenPorLicenciaturaBajaForaneo)->pluck('mujeres'));

            window.graficaAlumnosInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [
                        { label: 'Locales Activos Hombres', data: dataHombresLocales,      backgroundColor: '#3B82F6' },
                        { label: 'Locales Activos Mujeres', data: dataMujeresLocales,      backgroundColor: '#60A5FA' },
                        { label: 'Locales Bajas Hombres',   data: dataHombresBajasLocales, backgroundColor: '#F87171' },
                        { label: 'Locales Bajas Mujeres',   data: dataMujeresBajasLocales, backgroundColor: '#FCA5A5' },
                        { label: 'Foráneos Activos Hombres',data: dataHombresForaneos,     backgroundColor: '#10B981' },
                        { label: 'Foráneos Activos Mujeres',data: dataMujeresForaneos,     backgroundColor: '#6EE7B7' },
                        { label: 'Foráneos Bajas Hombres',  data: dataHombresBajasForaneos,backgroundColor: '#F59E0B' },
                        { label: 'Foráneos Bajas Mujeres',  data: dataMujeresBajasForaneos,backgroundColor: '#FCD34D' },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                        tooltip: { callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y ?? 0}` } }
                    },
                    scales: {
                        x: { stacked: true, ticks: { maxRotation: 0, autoSkip: true } },
                        y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    </script>
</div>
