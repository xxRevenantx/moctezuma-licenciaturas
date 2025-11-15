<div class="space-y-6">

    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 via-sky-500 to-cyan-500 p-[1px] shadow-lg">
        <div class="rounded-2xl bg-white dark:bg-neutral-950/95 px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex items-center gap-3">
                <div class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-white/90 dark:bg-neutral-900 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-sky-400" viewBox="0 0 24 24" fill="none">
                        <path d="M8 7a4 4 0 118 0v1h1a3 3 0 013 3v3.5a2.5 2.5 0 01-2 2.45V19a2 2 0 01-2 2h-2.5a.75.75 0 010-1.5H16a.5.5 0 00.5-.5v-1.05a2.5 2.5 0 01-2-2.45V11a3 3 0 013-3h-1V7a2.5 2.5 0 10-5 0v1H9A3 3 0 006 11v3a3 3 0 003 3h.25a.75.75 0 010 1.5H9A4.5 4.5 0 014.5 14V11A4 4 0 018 7z" fill="currentColor"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base sm:text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                        Listado de materias por profesor
                    </h1>
                    <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                        Selecciona un profesor, el periodo y genera listas de asistencia y evaluación según sus materias con horario asignado.
                    </p>
                </div>
            </div>

            @if($selectedProfesor)
                <div class="sm:ml-auto flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-100 px-2.5 py-1 font-medium">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Profesor seleccionado
                    </span>
                    @if($periodo_id)
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-100 px-2.5 py-1 font-medium">
                            Periodo:
                            <span class="font-semibold">{{ $periodo_id }}</span>
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- SELECT PROFESOR + LOADER --}}
    <div x-data x-cloak class="relative">
        <x-searchable-select
            label="Profesor"
            placeholder="--Selecciona al profesor--"
            wire:model.live="query"
        >
            @foreach($profesores as $index => $profesor)
                <x-searchable-select.option value="{{ $profesor['id'] }}">
                    {{ $profesor['apellido_paterno'] ?? '' }} {{ $profesor['apellido_materno'] ?? '' }} {{ $profesor['nombre'] ?? '' }}
                </x-searchable-select.option>
            @endforeach
        </x-searchable-select>

        {{-- loader al cambiar profesor --}}
        <div
            wire:loading.flex
            wire:target="query"
            class="absolute inset-0 bg-white/60 dark:bg-neutral-900/60 backdrop-blur-sm rounded-xl z-10 justify-center items-center"
        >
            <div class="flex flex-col items-center gap-2">
                <svg class="animate-spin h-7 w-7 text-indigo-600 dark:text-sky-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-xs font-medium text-neutral-700 dark:text-neutral-200">
                    Cargando información del profesor…
                </span>
            </div>
        </div>
    </div>

    {{-- INFO PROFESOR SELECCIONADO --}}
    @if($selectedProfesor)
        <div class="rounded-2xl border border-neutral-200/80 dark:border-neutral-800 bg-white dark:bg-neutral-950 shadow-sm px-4 py-3 flex gap-3 items-center">
            @php
                $inic = mb_substr($selectedProfesor['nombre'] ?? '',0,1).mb_substr($selectedProfesor['apellido_paterno'] ?? '',0,1);
            @endphp
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-100 font-semibold uppercase text-sm">
                {{ $inic }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-50 truncate">
                    {{ $selectedProfesor['nombre'] }}
                    {{ $selectedProfesor['apellido_paterno'] }}
                    {{ $selectedProfesor['apellido_materno'] }}
                </p>
                <div class="mt-1 flex flex-wrap gap-1.5 text-[11px] text-neutral-500 dark:text-neutral-400">
                    @if(!empty($selectedProfesor['CURP']))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-neutral-100 dark:bg-neutral-800/70">
                            CURP:
                            <span class="font-mono text-[10px] tracking-tight">{{ $selectedProfesor['CURP'] }}</span>
                        </span>
                    @endif
                    @if(!empty($selectedProfesor['user']['email']))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                <path d="M4 6.75A2.75 2.75 0 016.75 4h10.5A2.75 2.75 0 0120 6.75v10.5A2.75 2.75 0 0117.25 20H6.75A2.75 2.75 0 014 17.25V6.75z" stroke="currentColor" stroke-width="1.5"/>
                                <path d="M5 7l7 5 7-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $selectedProfesor['user']['email'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- PERIODO --}}
    @if($selectedProfesor)
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex items-center gap-2 text-sm text-neutral-600 dark:text-neutral-300">
                <div class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                        <rect x="4" y="5" width="16" height="15" rx="2.5" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M9 3v4M15 3v4M4 10h16" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <span class="font-medium">Periodo de trabajo del profesor</span>
            </div>

            <div class="sm:ml-auto w-full sm:w-64">
                <flux:select label="Periodo" wire:model.live="periodo_id" required>
                    <option value="">--Selecciona el periodo---</option>
                    <option value="9-12">SEP/DIC</option>
                    <option value="1-4">ENE/ABR</option>
                    <option value="5-8">MAY/AGO</option>
                </flux:select>
            </div>
        </div>
    @endif

    {{-- MATERIAS ASIGNADAS --}}
{{-- MATERIAS ASIGNADAS --}}
@if($selectedProfesor)
    @php
        $totalMaterias = $this->materiasFiltradas->count();
    @endphp

    <div class="space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-50">
                Materias asignadas con horario
            </h2>

            {{-- Contador de materias --}}
            <span class="inline-flex items-center gap-1 rounded-full bg-white/60 dark:bg-neutral-900/70 px-2.5 py-1 text-[11px] font-medium text-neutral-700 dark:text-neutral-100 ring-1 ring-white/70 dark:ring-neutral-700/80 shadow-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                {{ $totalMaterias }} materia{{ $totalMaterias === 1 ? '' : 's' }} encontradas
            </span>

            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                Filtra por nombre de materia y ajusta las columnas que quieres ver.
            </span>
        </div>

        <div class="w-full sm:max-w-xs">
            <flux:input
                icon="magnifying-glass"
                wire:model.live="buscador_materia"
                placeholder="Buscar por nombre de materia..."
            />
        </div>

        <div class="relative">
            {{-- Loader de materias / filtros --}}
            <div class="absolute inset-0 bg-white/40 dark:bg-neutral-950/50 backdrop-blur-sm flex items-center justify-center z-10"
                 wire:loading.flex
                 wire:target="query,buscador_materia,cargarMateriasAsignadas,periodo_id">
                <div class="flex items-center gap-3 text-neutral-700 dark:text-neutral-100">
                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="text-sm font-medium">Cargando materias…</span>
                </div>
            </div>

            {{-- CARD GLASS DE LA TABLA --}}
            <div
                x-data="{
                    term: @entangle('buscador_materia'),
                    dense: false,
                    showCols: { modalidad: true, cuatrimestre: true, licenciatura: true },
                    highlight(t) {
                        if (!this.term) return t;
                        const esc = this.term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        return t.replace(new RegExp(esc, 'ig'), (m) => `<mark class='bg-yellow-200/80 dark:bg-yellow-500/40 px-0.5 rounded'>${m}</mark>`);
                    }
                }"
                class="relative rounded-2xl border border-white/35 dark:border-neutral-700/70 bg-white/10 dark:bg-neutral-900/55 backdrop-blur-2xl shadow-xl overflow-hidden"
            >
                {{-- Barra de utilidades glass --}}
                <div class="flex flex-wrap items-center gap-3 px-3 py-2.5 border-b border-white/20 dark:border-neutral-700/60 bg-gradient-to-r from-neutral-900/85 via-neutral-900/80 to-neutral-800/85 backdrop-blur">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] uppercase tracking-wide text-neutral-400">Densidad</span>
                        <button
                            @click="dense=false"
                            :class="dense ? 'bg-neutral-800/70 text-neutral-300' : 'bg-indigo-500 text-white shadow-sm'"
                            class="px-2 py-1 text-[11px] rounded-full border border-indigo-600/60"
                        >Cómoda</button>
                        <button
                            @click="dense=true"
                            :class="dense ? 'bg-indigo-500 text-white shadow-sm' : 'bg-neutral-800/70 text-neutral-300'"
                            class="px-2 py-1 text-[11px] rounded-full border border-indigo-600/60"
                        >Compacta</button>
                    </div>

                    <div class="ml-auto flex flex-wrap items-center gap-2">
                        <span class="text-[11px] uppercase tracking-wide text-neutral-400">Columnas</span>
                        <label class="inline-flex items-center gap-1 text-[11px] text-neutral-200">
                            <input type="checkbox" class="rounded bg-neutral-800 border-neutral-600" x-model="showCols.modalidad">
                            <span>Modalidad</span>
                        </label>
                        <label class="inline-flex items-center gap-1 text-[11px] text-neutral-200">
                            <input type="checkbox" class="rounded bg-neutral-800 border-neutral-600" x-model="showCols.cuatrimestre">
                            <span>Cuatrimestre</span>
                        </label>
                        <label class="inline-flex items-center gap-1 text-[11px] text-neutral-200">
                            <input type="checkbox" class="rounded bg-neutral-800 border-neutral-600" x-model="showCols.licenciatura">
                            <span>Licenciatura</span>
                        </label>
                    </div>
                </div>

                {{-- Tabla con efecto glass --}}
                <div class="max-h-[480px] overflow-auto bg-gradient-to-b from-white/10 via-white/5 to-white/0 dark:from-neutral-950/40 dark:via-neutral-950/20 dark:to-transparent">
                    <table class="min-w-full divide-y divide-white/25 dark:divide-neutral-800/80">
                        <thead class="bg-neutral-900/85 backdrop-blur sticky top-0 z-10">
                            <tr>
                                <th class="px-3 py-2.5">
                                    <span class="text-left text-[11px] font-medium text-neutral-300 uppercase tracking-wider">#</span>
                                </th>
                                <th class="px-3 py-2.5">
                                    <span class="text-left text-[11px] font-medium text-neutral-300 uppercase tracking-wider">Materia</span>
                                </th>
                                <th class="px-3 py-2.5" x-show="showCols.modalidad">
                                    <span class="text-left text-[11px] font-medium text-neutral-300 uppercase tracking-wider">Modalidad</span>
                                </th>
                                <th class="px-3 py-2.5" x-show="showCols.cuatrimestre">
                                    <span class="text-left text-[11px] font-medium text-neutral-300 uppercase tracking-wider">Cuatrimestre</span>
                                </th>
                                <th class="px-3 py-2.5" x-show="showCols.licenciatura">
                                    <span class="text-left text-[11px] font-medium text-neutral-300 uppercase tracking-wider">Licenciatura</span>
                                </th>
                                <th class="px-3 py-2.5">
                                    <span class="text-left text-[11px] font-medium text-neutral-300 uppercase tracking-wider">Listas</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="bg-white/5 dark:bg-neutral-950/20 divide-y divide-white/15 dark:divide-neutral-800/70 text-neutral-900 dark:text-neutral-50">
                            @forelse($this->materiasFiltradas as $i => $row)
                                @php
                                    $firstGen = $row->generaciones ? \Illuminate\Support\Str::of($row->generaciones)->explode(',')->first() : null;
                                @endphp

                                <tr class="group hover:bg-white/25 dark:hover:bg-indigo-900/35 transition-colors">
                                    {{-- # --}}
                                    <td class="px-3 align-top {{ $loop->last ? 'pb-3 pt-2.5' : 'py-2.5' }}">
                                        <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                            {{ $i + 1 }}
                                        </span>
                                    </td>

                                    {{-- Materia + chips modalidad / generaciones --}}
                                    <td class="px-3 align-top {{ $loop->last ? 'pb-3 pt-2.5' : 'py-2.5' }}">
                                        <div class="flex flex-col gap-1.5">
                                            <span class="text-sm font-semibold text-neutral-900 dark:text-neutral-50"
                                                  x-html="highlight(@js($row->materia))"></span>
                                            <div class="flex flex-wrap gap-1">
                                                @if($row->modalidad === 'SEMIESCOLARIZADA')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100/80 text-purple-800 dark:bg-purple-900/60 dark:text-purple-100">
                                                        Semiescolarizada
                                                    </span>
                                                @elseif($row->modalidad === 'ESCOLARIZADA')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100/80 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-100">
                                                        Escolarizada
                                                    </span>
                                                @endif

                                                @if($row->generaciones)
                                                    @php
                                                        $gens = \Illuminate\Support\Str::of($row->generaciones)->explode(',')->filter()->unique()->values();
                                                    @endphp
                                                    @foreach($gens as $g)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-sky-100/80 text-sky-800 dark:bg-sky-900/60 dark:text-sky-100">
                                                            Gen {{ $g }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Modalidad --}}
                                    <td class="px-3 align-top text-center {{ $loop->last ? 'pb-3 pt-2.5' : 'py-2.5' }}"
                                        x-show="showCols.modalidad">
                                        <span class="text-xs" x-html="highlight(@js($row->modalidad))"></span>
                                    </td>

                                    {{-- Cuatrimestre --}}
                                    <td class="px-3 align-top text-center {{ $loop->last ? 'pb-3 pt-2.5' : 'py-2.5' }}"
                                        x-show="showCols.cuatrimestre">
                                        <span class="text-xs font-medium">{{ $row->cuatrimestre }}</span>
                                    </td>

                                    {{-- Licenciatura --}}
                                    <td class="px-3 align-top text-center {{ $loop->last ? 'pb-3 pt-2.5' : 'py-2.5' }}"
                                        x-show="showCols.licenciatura">
                                        <span class="text-xs" x-html="highlight(@js($row->licenciatura))"></span>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-3 align-top {{ $loop->last ? 'pb-3 pt-2.5' : 'py-2.5' }}">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($row->modalidad === 'ESCOLARIZADA')
                                                <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_asistencia_escolarizada', [
                                                    'asignacion_materia' => $row->asignacion_materia_id,
                                                    'licenciatura_id' => $row->licenciatura_id,
                                                    'cuatrimestre_id' => $row->cuatrimestre,
                                                    'generacion_id' => $firstGen,
                                                    'modalidad_id' => $row->modalidad_id,
                                                    'periodo' => $periodo_id,
                                                ]) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-[11px] font-semibold rounded-full bg-indigo-600/90 text-white hover:bg-indigo-700 shadow-sm backdrop-blur">
                                                    Asistencia
                                                </a>
                                            @else
                                                <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_asistencia_semiescolarizada', [
                                                    'asignacion_materia' => $row->asignacion_materia_id,
                                                    'licenciatura_id' => $row->licenciatura_id,
                                                    'cuatrimestre_id' => $row->cuatrimestre,
                                                    'generacion_id' => $firstGen,
                                                    'modalidad_id' => $row->modalidad_id,
                                                    'periodo' => $periodo_id,
                                                ]) }}"
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-[11px] font-semibold rounded-full bg-indigo-600/90 text-white hover:bg-indigo-700 shadow-sm backdrop-blur">
                                                    Asistencia
                                                </a>
                                            @endif

                                            <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_evaluacion', [
                                                'asignacion_materia' => $row->asignacion_materia_id,
                                                'licenciatura_id' => $row->licenciatura_id,
                                                'cuatrimestre_id' => $row->cuatrimestre,
                                                'generacion_id' => $firstGen,
                                                'modalidad_id' => $row->modalidad_id,
                                                'periodo' => $periodo_id,
                                            ]) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 text-[11px] font-semibold rounded-full bg-cyan-600/90 text-white hover:bg-cyan-700 shadow-sm backdrop-blur">
                                                Evaluación
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-8">
                                        <div class="flex flex-col items-center justify-center gap-2 text-center">
                                            <div class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/70 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-300 backdrop-blur">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                                    <path d="M12 7v6m0 4h.01" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                                    <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.7"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                                                No hay materias asignadas para el periodo/filtro seleccionado.
                                            </p>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                Verifica el periodo, el profesor seleccionado o ajusta el buscador.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="rounded-2xl border border-dashed border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-950 px-5 py-6 text-center">
        <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-white text-neutral-500 shadow-sm dark:bg-neutral-900 dark:text-neutral-300 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                <path d="M12 6.75a3.25 3.25 0 013.25 3.25v.5a.75.75 0 01-1.5 0v-.5a1.75 1.75 0 10-3.5 0v.25c0 .466.184.913.513 1.243l2.774 2.774A3.25 3.25 0 0114.75 18H9.25a.75.75 0 010-1.5h5.5a1.75 1.75 0 00.596-3.389l-2.774-2.774A3.24 3.24 0 0110.5 10.25v-.25A3.25 3.25 0 0113.75 6.75z" fill="currentColor"/>
                <path d="M12 3.5a8.5 8.5 0 100 17 8.5 8.5 0 000-17z" stroke="currentColor" stroke-width="1.5"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
            Selecciona un profesor para habilitar la búsqueda y ver sus materias con horario.
        </p>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            Primero elige al profesor en el buscador superior; después podrás definir el periodo y generar las listas.
        </p>
    </div>
@endif


</div>
