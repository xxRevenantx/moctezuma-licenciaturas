<div>

    @if (session()->has('error'))
        <div x-data="{ open: true }" x-show="open" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-90"
            class="rounded-3xl bg-red-500/10 dark:bg-red-500/20 shadow-xl ring-1 ring-red-500/20 dark:ring-red-500/30 px-5 sm:px-6 py-4 mb-6">
            <div class="flex items-center gap-3">
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-red-500/20 text-red-600 dark:text-red-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </span>
                <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ session('error') }}</p>
                <button type="button" @click="open = false"
                    class="ml-auto -mx-1.5 -my-1.5 bg-red-500/20 text-red-600 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex h-8 w-8 dark:bg-red-500/20 dark:text-red-300 dark:hover:bg-red-300/20"
                    aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif


    <form wire:submit.prevent="guardarActaExamen" class="w-full mx-auto space-y-6">

        {{-- ENCABEZADO --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-7 py-5 sm:py-6">
            <!-- Barrita superior -->
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500">
            </div>

            <!-- Fondos decorativos -->
            <div class="pointer-events-none absolute -left-10 top-10 h-24 w-24 rounded-full bg-sky-500/15 blur-3xl">
            </div>
            <div
                class="pointer-events-none absolute -right-10 -bottom-12 h-32 w-32 rounded-full bg-indigo-500/15 blur-3xl">
            </div>

            <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3 sm:gap-4">
                    <span
                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-indigo-500 to-violet-500 text-white shadow-lg shadow-sky-900/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-9H3v9a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                            Acta de examen profesional
                        </h2>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 max-w-xl">
                            Registra la fecha del acto protocolario y a los sínodos que participan en el jurado.
                        </p>
                    </div>
                </div>
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-neutral-900/5 px-2.5 py-1 text-[11px] text-neutral-600 dark:bg-neutral-800 dark:text-neutral-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Configuración del acta
                </span>
            </div>
        </div>

        {{-- BLOQUE: FECHA DEL EXAMEN --}}
        <div
            class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-3">

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-9H3v9a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Alumno</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Registra el nombre del alumno.
                    </p>
                </div>
            </div>

            {{-- SKELETON mientras se carga el alumno seleccionado --}}
            <div wire:loading wire:target="query"
                class="w-full relative overflow-hidden rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-4 sm:py-5">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500">
                </div>

                <div class="flex items-center gap-4 animate-pulse">
                    <div class="h-12 w-12 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>

                    <div class="flex-1 space-y-2">
                        <div class="h-3 w-2/3 rounded bg-neutral-200 dark:bg-neutral-700"></div>
                        <div class="flex flex-wrap gap-2">
                            <div class="h-4 w-24 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
                            <div class="h-4 w-28 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
                            <div class="h-4 w-20 rounded-full bg-neutral-200 dark:bg-neutral-700"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TARJETA REAL: solo cuando hay alumno seleccionado y NO se está cargando --}}
            @if ($selectedAlumno)
                <div wire:loading.remove wire:target="query"
                    class="relative overflow-hidden rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-4 sm:py-5">
                    {{-- Barrita superior decorativa --}}
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500">
                    </div>

                    {{-- Fondos difuminados --}}
                    <div class="pointer-events-none absolute -left-10 top-6 h-24 w-24 rounded-full bg-sky-500/15 blur-3xl">
                    </div>
                    <div
                        class="pointer-events-none absolute -right-10 -bottom-10 h-28 w-28 rounded-full bg-indigo-500/15 blur-3xl">
                    </div>

                    <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        {{-- Columna izquierda: datos principales --}}
                        <div class="flex items-start gap-3 sm:gap-4">
                            {{-- Avatar con iniciales --}}
                            @if ($selectedAlumno['foto'])
                                <img src="{{ asset('fotos/' . $selectedAlumno['foto']) }}" alt="{{ $selectedAlumno['nombre'] }}"
                                    class="w-12 h-12 object-cover rounded-full">
                            @else
                                <div
                                    class="mt-1 flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-indigo-600 text-sm sm:text-base font-semibold text-white shadow-lg">
                                    {{ mb_substr($selectedAlumno['nombre'] ?? 'A', 0, 1) }}{{ mb_substr($selectedAlumno['apellido_paterno'] ?? 'A', 0, 1) }}
                                </div>
                            @endif

                            <div class="space-y-1.5">
                                {{-- Nombre completo --}}
                                <p
                                    class="font-semibold text-neutral-900 dark:text-neutral-50 text-sm sm:text-base leading-snug">
                                    {{ $selectedAlumno['nombre'] ?? '' }}
                                    {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                                    {{ $selectedAlumno['apellido_materno'] ?? '' }}
                                </p>

                                {{-- Chips de info rápida --}}
                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs sm:text-[13px]">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-800/80 dark:text-neutral-100 px-2.5 py-0.5">
                                        <span
                                            class="text-[10px] uppercase tracking-wide text-neutral-500 dark:text-neutral-400">
                                            Matrícula
                                        </span>
                                        <span class="font-mono text-xs">
                                            {{ $selectedAlumno['matricula'] ?? '----' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-800/80 dark:text-neutral-100 px-2.5 py-0.5">
                                        <span
                                            class="text-[10px] uppercase tracking-wide text-neutral-500 dark:text-neutral-400">
                                            CURP
                                        </span>
                                        <span class="font-mono text-[11px] truncate max-w-[150px] sm:max-w-xs">
                                            {{ $selectedAlumno['CURP'] ?? '----' }}
                                        </span>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200/80 dark:bg-indigo-900/20 dark:text-indigo-100 dark:ring-indigo-700/70 px-2.5 py-0.5">
                                        <span class="text-[10px] uppercase tracking-wide">
                                            Folio:
                                        </span>
                                        <span class="font-semibold text-xs">
                                            {{ $selectedAlumno['folio'] ?? '----' }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Columna derecha: info académica --}}
                        <div
                            class="flex flex-col items-start sm:items-end gap-1 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300">
                            <div class="flex flex-col items-start sm:items-end">
                                <span class="text-[11px] uppercase tracking-wide text-neutral-500 dark:text-neutral-400">
                                    Licenciatura
                                </span>
                                <span class="font-medium text-neutral-900 dark:text-neutral-50">
                                    {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}
                                </span>
                            </div>

                            <div
                                class="hidden sm:block h-px w-24 bg-gradient-to-r from-transparent via-neutral-300/60 to-transparent dark:via-neutral-700/60">
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-1">
                <flux:field>
                    <!-- Select de búsqueda -->
                    <x-searchable-select label="Estudiante" placeholder="--Selecciona al estudiante--"
                        wire:model.live="query" {{-- query ahora es el ID del alumno --}}>
                        @foreach($alumnos as $index => $alumno)
                            @php
                                $nombreAlumno = $alumno['apellido_paterno'] . ' ' . $alumno['apellido_materno'] . ' ' . $alumno['nombre'] . ' - ' . $alumno['matricula'];
                            @endphp
                            <x-searchable-select.option value="{{ $alumno['id'] }}">
                                {{ $nombreAlumno }}
                            </x-searchable-select.option>
                        @endforeach
                    </x-searchable-select>
                    @error('query')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:select label="Generación" wire:model="generacion_id">
                        <option value="">--Selecciona una generación--</option>
                        @foreach($generaciones as $generacion)
                            <option value="{{ $generacion->id }}">{{ $generacion->generacion }}</option>
                        @endforeach
                    </flux:select>
                    @error('generacion_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </flux:field>
                <flux:field>
                    <flux:input label="Número de Autorización" wire:model="numero_autorizacion"
                        placeholder="Número de autorización" />

                    @error('numero_autorizacion')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </flux:field>

                <flux:field>
                    <flux:input type="datetime-local" label="Fecha de autorización" wire:model="fecha_autorizacion"
                        placeholder="Fecha de autorización" />

                    @error('fecha_autorizacion')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </flux:field>

            </div>

        </div>



        {{-- BLOQUE: SÍNODOS DINÁMICOS (COLLAPSE) --}}
        <div x-data="{ openSinodos: true }" class="mb-6">

            {{-- HEADER DEL COLLAPSE --}}
            <div @click="openSinodos = !openSinodos" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3
                       bg-neutral-100 dark:bg-neutral-800/70
                       text-neutral-800 dark:text-neutral-100
                       ring-1 ring-neutral-200 dark:ring-neutral-700
                       hover:bg-neutral-50 dark:hover:bg-neutral-800
                       transition cursor-pointer">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl
                               bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 10l5-5 5 5M7 14l5 5 5-5" />
                        </svg>
                    </span>
                    <div class="text-left">
                        <h3 class="text-sm font-semibold">Sínodos</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Agrega, ordena o elimina a los docentes que integran el jurado del examen.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">


                    <svg :class="{ 'rotate-180': openSinodos }"
                        class="w-5 h-5 text-neutral-500 dark:text-neutral-300 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {{-- CONTENIDO DEL COLLAPSE --}}
            <div x-show="openSinodos" x-transition x-cloak class="mt-2 rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl
                       ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">

                {{-- Botón agregar sínodo en el header (desktop) --}}
                <flux:button wire:click="addSinodo" @click.stop variant="primary" size="sm"
                    class="hidden sm:inline-flex bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500
                               hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                    + Agregar sínodo
                </flux:button>
                {{-- Botón agregar sínodo en móvil --}}


                <div class="flex justify-end mb-1 sm:hidden">
                    <flux:button wire:click="addSinodo" @click.stop variant="primary" size="sm"
                        class="w-full bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500
                               hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                        + Agregar sínodo
                    </flux:button>
                </div>

                <div class="space-y-3">
                    @foreach($sinodos as $index => $sinodo)
                        <div
                            class="rounded-2xl border border-neutral-200/80 dark:border-neutral-800
                                                                                                                                                                                                                                                       bg-neutral-50/70 dark:bg-neutral-900/70 px-3 sm:px-4 py-3 sm:py-3.5">
                            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 md:gap-4 items-start">
                                {{-- Campo nombre sínodo --}}
                                <flux:field>
                                    <flux:input label="Sínodo {{ $index + 1 }}" type="text" name="sinodos[{{ $index }}]"
                                        wire:model.defer="sinodos.{{ $index }}" placeholder="Nombre completo del sínodo" />
                                    @error("sinodos.$index")
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </flux:field>

                                {{-- Controles: subir / bajar / eliminar --}}
                                <div class="flex items-center justify-end gap-1 mt-4">
                                    {{-- Subir --}}
                                    <flux:button type="button" size="xs" variant="ghost"
                                        wire:click="moveSinodoUp({{ $index }})"
                                        class="rounded-full p-1.5 hover:bg-neutral-200/80 dark:hover:bg-neutral-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18" />
                                        </svg>
                                    </flux:button>

                                    {{-- Bajar --}}
                                    <flux:button type="button" size="xs" variant="ghost"
                                        wire:click="moveSinodoDown({{ $index }})"
                                        class="rounded-full p-1.5 hover:bg-neutral-200/80 dark:hover:bg-neutral-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 17.25 12 21m0 0-3.75-3.75M12 21V3" />
                                        </svg>
                                    </flux:button>

                                    {{-- Eliminar --}}
                                    @if(count($sinodos) > 1)
                                        <flux:button type="button" size="xs" variant="ghost"
                                            wire:click="removeSinodo({{ $index }})"
                                            class="rounded-full p-1.5 hover:bg-rose-100/80 text-rose-600
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           dark:hover:bg-rose-900/40 dark:text-rose-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-between mb-3 gap-2">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-50 flex items-center gap-2">
                    <span
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </span>
                    Tabla de Datos
                </h3>

                <span
                    class="hidden sm:inline-flex items-center rounded-full bg-neutral-100 dark:bg-neutral-800 px-3 py-1 text-xs font-medium text-neutral-600 dark:text-neutral-300">
                    2 registros
                </span>
            </div>

            <div
                class="relative overflow-hidden rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white/95 dark:bg-neutral-950/95 shadow-sm">
                <!-- barrita superior -->
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500">
                </div>

                <!-- decoraciones suaves -->
                <div
                    class="pointer-events-none absolute -left-16 top-6 h-24 w-24 rounded-full bg-sky-500/10 blur-3xl dark:bg-sky-500/15">
                </div>
                <div
                    class="pointer-events-none absolute -right-10 -bottom-10 h-28 w-28 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-500/15">
                </div>

                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-800">
                        <thead class="bg-neutral-50/90 dark:bg-neutral-900/90 backdrop-blur">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-300 uppercase tracking-[0.12em]">
                                    Columna 1
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-300 uppercase tracking-[0.12em]">
                                    Columna 2
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 dark:text-neutral-300 uppercase tracking-[0.12em]">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white/80 dark:bg-neutral-950/80 divide-y divide-neutral-100 dark:divide-neutral-800 text-sm">
                            <tr class="hover:bg-sky-50/70 dark:hover:bg-neutral-900 transition-colors">
                                <td
                                    class="px-6 py-4 whitespace-nowrap font-semibold text-neutral-900 dark:text-neutral-50">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                                        <span>Fila 1, Dato 1</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-600 dark:text-neutral-300">
                                    Fila 1, Dato 2
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-600 dark:text-neutral-300">
                                    Fila 1, Dato 3
                                </td>
                            </tr>
                            <tr class="hover:bg-sky-50/70 dark:hover:bg-neutral-900 transition-colors">
                                <td
                                    class="px-6 py-4 whitespace-nowrap font-semibold text-neutral-900 dark:text-neutral-50">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                        <span>Fila 2, Dato 1</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-600 dark:text-neutral-300">
                                    Fila 2, Dato 2
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-neutral-600 dark:text-neutral-300">
                                    <flux:button target="_blank"
                                        href="{{ route('admin.pdf.documentacion.acta-examen', 23) }}"
                                        icon:trailing="arrow-up-right" type="button" variant="primary"
                                        class="rounded-2xl px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500 hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                                        Acta de examen PDF
                                    </flux:button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>



        {{-- BOTÓN GUARDAR --}}
        <div class="flex items-center justify-end pt-2">
            <flux:button type="submit" variant="primary"
                class="rounded-2xl px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500 hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                Guardar acta
            </flux:button>
        </div>
    </form>
</div>