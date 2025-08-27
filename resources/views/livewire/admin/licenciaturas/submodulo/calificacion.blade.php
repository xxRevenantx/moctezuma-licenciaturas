<div
    x-data="{
        enviarCalificacion(alumno, cuatrimestre, generacion, modalidad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `La calificación del alumno en el ${cuatrimestre}° cuatrimestre se enviará a su correo asignado.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, enviar'
            }).then((r) => { if (r.isConfirmed) { @this.call('enviarCalificacion', alumno, cuatrimestre, generacion, modalidad); }});
        },
        enviarCalificacionesMasivasAlpine() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas enviar las calificaciones a todos los alumnos?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, enviar'
            }).then((r) => { if (r.isConfirmed) { @this.call('enviarCalificacionesMasivas'); }});
        }
    }"
    class="space-y-6"
>

    {{-- ======= TOOLBAR / FILTROS ======= --}}
    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white/80 dark:bg-neutral-900/80 shadow-sm overflow-hidden">
        <div class="h-1.5 w-full bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>

        <div class="p-4 sm:p-6">
            <h3 class="flex items-center gap-2 text-xl sm:text-2xl font-extrabold text-neutral-800 dark:text-neutral-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span>Filtrar por</span>
            </h3>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                <flux:field>
                    <flux:label>Generación</flux:label>
                    <flux:select wire:model.live="filtrar_generacion">
                        <flux:select.option value="">--Selecciona una generación---</flux:select.option>
                        @foreach($generaciones as $generacion)
                            <flux:select.option value="{{ $generacion->generacion_id }}">
                                {{ $generacion->generacion->generacion }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="filtrar_cuatrimestre">
                        <flux:select.option value="">--Selecciona un cuatrimestre---</flux:select.option>
                        @foreach($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->cuatrimestre_id }}">
                                {{ $cuatrimestre->cuatrimestre->nombre_cuatrimestre}}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field class="lg:col-span-1">
                    <flux:label>&nbsp;</flux:label>
                    <flux:button wire:click="limpiarFiltros" variant="primary"
                        class="w-full justify-center bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                               hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 3c2.8 0 5.5.2 8.1.7.6.1.9.6.9 1.1V6a2 2 0 01-.6 1.5l-5.5 5.5a2 2 0 00-.6 1.5V17a2 2 0 01-1.2 1.9L9.8 21v-6.6a2 2 0 00-.6-1.5L3.7 7.4A2 2 0 013 5.8V4.8c0-.5.4-1 .9-1.1C6.5 3.2 9.2 3 12 3z"/>
                            </svg>
                            <span>Limpiar filtros</span>
                        </div>
                    </flux:button>
                </flux:field>
            </div>
        </div>
    </div>

    {{-- ======= LISTADO / TABLA ======= --}}
    <div class="relative rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">

        {{-- Overlay loader (filtros) --}}
        <div wire:loading.flex wire:target="filtrar_generacion, filtrar_cuatrimestre"
             class="absolute inset-0 z-20 backdrop-blur-sm bg-white/40 dark:bg-black/30 items-center justify-center">
            <div class="flex flex-col items-center gap-2 text-neutral-700 dark:text-neutral-200">
                <svg class="animate-spin h-8 w-8" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <span class="text-sm">Cargando…</span>
            </div>
        </div>

        <div class="p-3 sm:p-4">
            @if($filtrar_generacion && $filtrar_cuatrimestre)

                            {{-- Filtros aplicados (diseño pro + responsive, misma estructura) --}}
                <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-4 sm:p-5 mb-4
                            rounded-2xl border border-neutral-200 dark:border-neutral-700
                            bg-gradient-to-br from-white via-neutral-50 to-white
                            dark:from-neutral-900 dark:via-neutral-900 dark:to-neutral-900
                            shadow-sm overflow-hidden">

                    {{-- Barra decorativa superior --}}
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>

                    {{-- Izquierda: resumen de filtros --}}
                    <div class="text-sm">
                        <div class="flex items-center gap-2 text-neutral-700 dark:text-neutral-200">
                            {{-- Icono filtros --}}
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg
                                        bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5h18M6 10h12M9 15h6"/>
                                </svg>
                            </span>
                            <strong class="font-semibold">Filtros aplicados:</strong>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @if($filtrar_generacion)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{-- Icono generación --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5 12.083 12.083 0 015.84 10.578L12 14z"/>
                                    </svg>
                                    Generación: {{ $generacion_filtrada->generacion->generacion }}
                                </span>
                            @endif

                            @if($filtrar_cuatrimestre)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">
                                    {{-- Icono cuatrimestre --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12z"/>
                                    </svg>
                                    Cuatrimestre: {{ $filtrar_cuatrimestre }}
                                </span>
                            @endif

                            @if(!$filtrar_generacion && !$filtrar_cuatrimestre)
                                <span class="text-neutral-500 dark:text-neutral-400">Sin filtros aplicados</span>
                            @endif
                        </div>
                    </div>

                    {{-- Derecha: acción (PDF) --}}
                    <form method="GET"
                        action="{{ route('admin.pdf.documentacion.calificaciones_generales') }}"
                        target="_blank"
                        class="shrink-0">
                        <input type="hidden" name="licenciatura_id" value="{{ $licenciatura->id }}">
                        <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                        <input type="hidden" name="generacion_id" value="{{ $filtrar_generacion }}">
                        <input type="hidden" name="cuatrimestre_id" value="{{ $filtrar_cuatrimestre }}">

                        <x-button type="submit" variant="primary"
                                class="w-full sm:w-auto justify-center px-4 py-2 rounded-xl
                                        bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                                        hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700
                                        text-white shadow-lg hover:shadow-xl focus:outline-none
                                        focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <div class="flex items-center gap-2">
                                <flux:icon.file-text/>
                                <span>Calificaciones generales</span>
                            </div>
                        </x-button>
                    </form>

                    {{-- Brillos sutiles --}}
                    <div class="pointer-events-none absolute -left-10 -bottom-10 h-24 w-24 rounded-full bg-sky-400/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -right-10 -top-10 h-24 w-24 rounded-full bg-indigo-500/10 blur-2xl"></div>
                </div>



                        @php


                            $inicio  =  Carbon\Carbon::parse($periodo->inicio_periodo);
                            $termino = $periodo->termino_periodo ?  Carbon\Carbon::parse($periodo->termino_periodo) : null;
                            $hoy     =  Carbon\Carbon::now();

                            // Estado del periodo
                            if ($termino) {
                                if ($hoy->lt($inicio))      { $estado = ['Próximo',    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300']; }
                                elseif ($hoy->lte($termino)){ $estado = ['En curso',   'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300']; }
                                else                        { $estado = ['Finalizado', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300']; }
                            } else {
                                $estado = $hoy->lt($inicio)
                                    ? ['Próximo',  'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300']
                                    : ['En curso', 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'];
                            }

                            // Progreso (si hay término)
                            $progress = null;
                            if ($termino) {
                                $totalDays   = max(1, $inicio->diffInDays($termino));
                                $elapsedDays = min($totalDays, max(0, $inicio->diffInDays($hoy)));
                                $progress    = round(($elapsedDays / $totalDays) * 100);
                            }
                        @endphp

                        <div class="p-5 sm:p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700
                                    bg-gradient-to-br from-white via-neutral-50 to-white
                                    dark:from-neutral-900 dark:via-neutral-900 dark:to-neutral-900
                                    shadow-md overflow-hidden">

                            {{-- Header con icono y estado --}}
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white
                                                grid place-items-center shadow">
                                        {{-- Icono calendario --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-base sm:text-lg font-extrabold uppercase tracking-wide
                                                text-neutral-800 dark:text-neutral-100">
                                            Periodo Cuatrimestral
                                        </h2>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                            Información vigente del periodo académico
                                        </p>
                                    </div>
                                </div>

                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $estado[1] }}">
                                    {{ $estado[0] }}
                                </span>
                            </div>

                            {{-- Tarjetas de datos --}}
                            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                                {{-- Ciclo escolar --}}
                                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-center gap-2 text-xs uppercase text-neutral-500 dark:text-neutral-400">
                                        <span class="inline-block h-2 w-2 rounded-full bg-sky-500"></span> Ciclo escolar
                                    </div>
                                    <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                        {{ $periodo->ciclo_escolar }}
                                    </div>
                                </div>

                                {{-- Periodo escolar --}}
                                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-center gap-2 text-xs uppercase text-neutral-500 dark:text-neutral-400">
                                        <span class="inline-block h-2 w-2 rounded-full bg-indigo-500"></span> Periodo escolar
                                    </div>
                                    <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                        {{ $periodo->mes->meses }}
                                    </div>
                                </div>

                                {{-- Inicio --}}
                                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-center gap-2 text-xs uppercase text-neutral-500 dark:text-neutral-400">
                                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span> Inicio de periodo
                                    </div>
                                    <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                        {{ $inicio->format('d/m/Y') }}
                                    </div>
                                </div>

                                {{-- Término --}}
                                <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm hover:shadow-md transition">
                                    <div class="flex items-center gap-2 text-xs uppercase text-neutral-500 dark:text-neutral-400">
                                        <span class="inline-block h-2 w-2 rounded-full bg-rose-500"></span> Término de periodo
                                    </div>
                                    <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                        @if($termino)
                                            {{ $termino->format('d/m/Y') }}
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                                        bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300">
                                                No asignado
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Línea de tiempo / Progreso --}}
                            <div class="mt-6">
                                @if(!is_null($progress))
                                    <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                                        <span>{{ $inicio->format('d/m/Y') }}</span>
                                        <span>{{ $termino->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="mt-2 h-2 rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"
                                            style="width: {{ $progress }}%"></div>
                                    </div>
                                    <div class="mt-1 text-right text-xs text-neutral-500 dark:text-neutral-400">
                                        Avance {{ $progress }}%
                                    </div>
                                @else
                                    <div class="h-2 rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
                                        <div class="w-1/3 h-full animate-pulse bg-gradient-to-r from-sky-500/40 via-blue-600/40 to-indigo-600/40"></div>
                                    </div>
                                    <div class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                        Término no asignado
                                    </div>
                                @endif
                            </div>
                        </div>


                {{-- Buscador --}}
                <div class="px-1 sm:px-2">
                    <h3 class="mb-2 font-semibold">Buscar Estudiante:</h3>
                    <flux:input type="text" wire:model.live="search"
                                placeholder="Nombre, Apellido Paterno, Apellido Materno, Matrícula"
                                class="w-full mb-4"/>
                </div>
            @endif

            {{-- Toast Livewire --}}
            <div x-data="{ show: false, message: '' }"
                 x-on:alerta.window="show = true; message = $event.detail; setTimeout(() => show = false, 2200)"
                 x-show="show" x-cloak
                 class="fixed top-6 right-6 z-50">
                <div class="rounded-xl bg-green-600 text-white px-4 py-2 shadow-lg">
                    <span x-text="message"></span>
                </div>
            </div>

            {{-- ======= TABLA ATRACTIVA ======= --}}
            <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="min-w-[1100px] w-full bg-white dark:bg-neutral-900 text-sm">
                    {{-- Encabezado pegajoso con estilo --}}
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-neutral-800 dark:to-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-14">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-40 whitespace-nowrap">Matrícula</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-64">Alumno</th>

                            @foreach ($materias as $materia)
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider min-w-[180px] align-bottom">
                                    <div class="flex flex-col">
                                        <span class="leading-4">{{ $materia->materia->nombre }}</span>
                                        @if($materia->profesor)
                                            <span class="mt-1 text-[10px] text-neutral-500 font-normal italic leading-3">
                                                {{ strtoupper($materia->profesor->nombre . ' ' . $materia->profesor->apellido_paterno . ' ' . $materia->profesor->apellido_materno) }}
                                            </span>
                                        @else
                                            <span class="mt-1 text-[10px] text-red-500 font-normal italic leading-3">Sin profesor</span>
                                        @endif
                                    </div>
                                </th>
                            @endforeach

                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-32">Promedio</th>
                            <th class="px-4 py-3 w-40"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                        @php
                            $global_sum = 0; $global_count = 0;
                            $calificaciones_introducidas = 0;
                            $calificaciones_total = count($alumnos) * count($materias);
                        @endphp

                        @foreach($alumnos as $index => $alumno)
                            @php $sum = 0; $count = 0; @endphp

                            <tr class="odd:bg-white even:bg-neutral-50 dark:odd:bg-neutral-900 dark:even:bg-neutral-800/60 hover:bg-sky-50/60 dark:hover:bg-neutral-800 transition">
                                {{-- # --}}
                                <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $index + 1 }}</td>

                                {{-- Matrícula --}}
                                <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-100 whitespace-nowrap">
                                    {{ $alumno->matricula }}
                                </td>

                                {{-- Alumno --}}
                                <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-100 ">
                                    {{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
                                </td>

                                {{-- Calificaciones por materia --}}
                                @foreach ($materias as $materia)
                                    @php
                                        $valor = $calificaciones[$alumno->id][$materia->id] ?? null;

                                        // Contadores de avance
                                        if ((is_numeric($valor) && $valor > 0) || (is_string($valor) && strtoupper(trim($valor)) === 'NP')) {
                                            $calificaciones_introducidas++;
                                        }

                                        if (is_numeric($valor) && $valor > 0) {
                                            $sum += $valor; $count++; $global_sum += $valor; $global_count++;
                                        }

                                        // Estilo del input si hay valor o NP
                                        $tieneValor = (is_numeric($valor) && $valor > 0) || (is_string($valor) && strtoupper(trim($valor)) === 'NP');
                                        $inputRing  = $tieneValor ? 'ring-1 ring-emerald-400/60' : 'ring-0';
                                    @endphp

                                    <td class="px-3 py-3 align-middle">
                                        <div class="relative">
                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                pattern="^([0-9]{1,2}|100|np|NP)?$"
                                                title="Ingresa 0-100 o NP"
                                                placeholder="0–10 o NP"
                                                class="w-full text-center rounded-lg border border-neutral-300 dark:border-neutral-600
                                                       bg-white dark:bg-neutral-800 px-2 py-2 focus:outline-none
                                                       focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-500
                                                       {{ $inputRing }}"
                                                wire:model.blur="calificaciones.{{ $alumno->id }}.{{ $materia->id }}"
                                            />

                                        </div>
                                    </td>
                                @endforeach

                                {{-- Promedio (chip por color) --}}
                                @php
                                    $avg = $count ? round($sum / $count, 2) : null;
                                    $chipClass = 'bg-neutral-200 text-neutral-800';
                                    if ($avg !== null) {
                                        if ($avg >= 90)      $chipClass = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
                                        elseif ($avg >= 70) $chipClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                                        else                $chipClass = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
                                    }
                                @endphp
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $chipClass }}">
                                        {{ $avg !== null ? number_format($avg, 2) : '—' }}
                                    </span>
                                </td>

                                {{-- Acciones --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-center gap-2">
                                        {{-- PDF alumno --}}
                                        <form action="{{ route('admin.pdf.documentacion.calificacion_alumno') }}" method="GET" target="_blank" class="m-0">
                                            <x-button type="submit" variant="primary"
                                                      class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                                <div class="flex items-center gap-2">
                                                    <flux:icon.file-text/>
                                                    <span>PDF</span>
                                                </div>
                                            </x-button>
                                            <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                                            <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                            <input type="hidden" name="generacion_id" value="{{ $filtrar_generacion }}">
                                            <input type="hidden" name="cuatrimestre_id" value="{{ $filtrar_cuatrimestre }}">
                                        </form>

                                        {{-- Enviar alumno --}}
                                        <x-button variant="primary"
                                                  class="bg-green-600 hover:bg-green-700 text-white rounded-lg"
                                                  @click="enviarCalificacion({{ $alumno->id }}, '{{ $filtrar_cuatrimestre }}', '{{ $filtrar_generacion }}', '{{ $modalidad->id }}')">
                                            <div class="flex items-center gap-2">
                                                <flux:icon.send/>
                                                <span>Enviar</span>
                                            </div>
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- Pie con promedio global --}}
                    @if($filtrar_generacion && $filtrar_cuatrimestre)
                        @php
                            $globalAvg = $global_count ? round($global_sum / $global_count, 2) : null;
                            $globalChip = 'bg-neutral-200 text-neutral-800';
                            if ($globalAvg !== null) {
                                if ($globalAvg >= 90)      $globalChip = 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
                                elseif ($globalAvg >= 70) $globalChip = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                                else                      $globalChip = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
                            }
                        @endphp
                        <tfoot class="bg-neutral-50 dark:bg-neutral-800/60">
                            <tr>
                                <td colspan="{{ 3 + count($materias) }}" class="px-4 py-3 text-right font-bold">Promedio global:</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $globalChip }}">
                                        {{ $globalAvg !== null ? number_format($globalAvg, 2) : '—' }}
                                    </span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Botones guardar / estado cambios --}}
            @if($filtrar_generacion && $filtrar_cuatrimestre && count($alumnos) && count($materias))
                <div class="flex flex-wrap items-center justify-end gap-3 my-5">
                    <button
                        wire:click="guardarTodasLasCalificaciones"
                        wire:loading.attr="disabled"
                        class="px-6 py-2 rounded-lg font-semibold text-white
                               {{ $todas_calificaciones_guardadas ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' }}">
                        {{ $todas_calificaciones_guardadas ? 'Actualizar calificaciones' : 'Guardar calificaciones' }}
                    </button>

                    <span class="text-sm {{ $hayCambios ? 'text-neutral-600 dark:text-neutral-400' : 'text-neutral-400 dark:text-neutral-500' }}">
                        {{ $hayCambios ? 'Existen cambios por guardar' : 'No hay cambios por guardar' }}
                    </span>
                </div>
            @endif

            {{-- Barra de progreso --}}
            @if($filtrar_generacion && $filtrar_cuatrimestre)
                @php
                    $porcentaje = $calificaciones_total > 0 ? ($calificaciones_introducidas / $calificaciones_total) * 100 : 0;
                    if ($porcentaje == 100)      { $barColor = 'bg-emerald-500';  $textColor = 'text-emerald-700'; }
                    elseif ($porcentaje >= 60)  { $barColor = 'bg-amber-400';    $textColor = 'text-amber-700'; }
                    elseif ($porcentaje > 0)    { $barColor = 'bg-rose-400';     $textColor = 'text-rose-700'; }
                    else                        { $barColor = 'bg-neutral-300';  $textColor = 'text-neutral-500'; }
                @endphp

                <div class="my-6 px-1 sm:px-2">
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-semibold {{ $textColor }}">
                            Calificaciones introducidas: {{ $calificaciones_introducidas }} de {{ $calificaciones_total }}
                            ({{ number_format($porcentaje, 1) }}%)
                        </span>
                        @if($porcentaje == 100)
                            <span class="ml-3 px-2 py-1 rounded text-xs bg-emerald-100 {{ $textColor }}">¡Completado!</span>
                        @endif
                    </div>
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-4 overflow-hidden">
                        <div class="{{ $barColor }} h-4 rounded-full transition-[width] duration-700 ease-out"
                             style="width: {{ $porcentaje }}%"></div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <x-button variant="primary"
                              wire:loading.attr="disabled"
                              class="flex items-center gap-2 px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg"
                              @click="enviarCalificacionesMasivasAlpine()">
                        <flux:icon.send/>
                        <span>Enviar a todos</span>
                    </x-button>
                </div>
            @endif
        </div>
    </div>
</div>
