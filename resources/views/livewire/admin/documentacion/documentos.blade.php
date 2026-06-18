<div x-data x-cloak>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <div class="w-full mx-auto space-y-6">

        <!-- HEADER -->
        <div class="mb-2">
            <div
                class="relative overflow-hidden rounded-3xl shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 bg-white/95 dark:bg-neutral-900/90">

                <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

                <div
                    class="pointer-events-none absolute -left-10 top-6 h-32 w-32 rounded-full bg-violet-500/15 blur-3xl">
                </div>

                <div
                    class="pointer-events-none absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-fuchsia-500/10 blur-3xl">
                </div>

                <div
                    class="pointer-events-none absolute left-1/2 bottom-0 h-44 w-44 -translate-x-1/2 translate-y-1/3 rounded-full bg-indigo-500/10 blur-3xl">
                </div>

                <div class="relative px-4 sm:px-7 py-5 sm:py-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <span
                                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white shadow-lg shadow-fuchsia-900/40">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                </svg>
                            </span>

                            <div>
                                <h1
                                    class="text-xl sm:text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                                    Documentación interna del alumno
                                </h1>

                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 max-w-2xl">
                                    Genera constancias y gestiona la documentación por alumno, generación o licenciatura
                                    en un solo lugar.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 justify-start lg:justify-end">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700 px-2.5 py-1 text-[11px] font-medium dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Módulo activo
                            </span>

                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-neutral-900/5 px-2.5 py-1 text-[11px] text-neutral-600 dark:bg-neutral-700/60 dark:text-neutral-100">
                                PDF
                            </span>

                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-100 dark:ring-indigo-800/60">
                                Control escolar
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DOCUMENTO PERSONAL -->
        <div>
            <form action="{{ route('admin.pdf.documentacion.documento_personal') }}" method="GET" target="_blank"
                class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-5 sm:py-6 space-y-4">

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-0.5">
                        <h2 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-neutral-50">
                            Documento personal
                        </h2>

                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Selecciona si deseas generar el documento por alumno, licenciatura o generación.
                        </p>
                    </div>

                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
                        Los datos se obtienen de la ficha del alumno y sus calificaciones.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pt-2">

                    <!-- Modo de generación -->
                    <div class="md:col-span-3">
                        <flux:select label="Expedir documento por" wire:model.live="modo_documento"
                            name="modo_documento" class="w-full" required>
                            <flux:select.option value="alumno">Alumno</flux:select.option>
                            <flux:select.option value="licenciatura">Licenciatura</flux:select.option>
                            <flux:select.option value="generacion">Generación</flux:select.option>
                        </flux:select>
                    </div>

                    @if ($modo_documento === 'alumno')
                        <!-- Alumno -->
                        <div x-data="{ open: false }" class="relative md:col-span-5">
                            <flux:input label="Buscar alumno" wire:model.live.debounce.500ms="query"
                                name="buscar_alumno" id="buscar_alumno_documento" type="text"
                                placeholder="Buscar alumno por nombre, matrícula, CURP o folio" autocomplete="off"
                                @focus="open = true" @input="open = true" @blur="setTimeout(() => open = false, 180)"
                                wire:keydown.arrow-down="selectIndexDown" wire:keydown.arrow-up="selectIndexUp"
                                wire:keydown.enter.prevent="selectAlumno({{ $selectedIndex }})" />

                            <input type="hidden" name="alumno_id" value="{{ $alumno_id }}">

                            @if (!empty($alumnos))
                                <ul x-show="open" x-transition x-cloak
                                    class="absolute left-0 right-0 z-50 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-neutral-200 bg-white p-2 shadow-2xl ring-1 ring-black/5 dark:border-neutral-700 dark:bg-neutral-900">
                                    @foreach ($alumnos as $index => $alumno)
                                        <li wire:click="selectAlumno({{ $index }})"
                                            class="cursor-pointer rounded-xl px-3 py-3 transition
                                                {{ $selectedIndex === $index
                                                    ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:ring-indigo-800/60'
                                                    : 'text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-800/80' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-bold">
                                                        {{ $alumno['apellido_paterno'] ?? '' }}
                                                        {{ $alumno['apellido_materno'] ?? '' }}
                                                        {{ $alumno['nombre'] ?? '' }}
                                                    </p>

                                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px]">
                                                        <span
                                                            class="rounded-full bg-neutral-100 px-2 py-0.5 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                                            Matrícula:
                                                            <span
                                                                class="font-mono">{{ $alumno['matricula'] ?? '----' }}</span>
                                                        </span>

                                                        <span
                                                            class="rounded-full bg-neutral-100 px-2 py-0.5 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                                            CURP:
                                                            <span
                                                                class="font-mono">{{ $alumno['CURP'] ?? '----' }}</span>
                                                        </span>

                                                        <span
                                                            class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                                                            {{ $alumno['licenciatura']['nombre'] ?? 'Sin licenciatura' }}
                                                        </span>

                                                        <span
                                                            class="rounded-full bg-violet-50 px-2 py-0.5 text-violet-700 dark:bg-violet-900/30 dark:text-violet-200">
                                                            {{ $alumno['generacion']['generacion'] ?? 'Sin generación' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                @if ($selectedIndex === $index)
                                                    <span
                                                        class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <div wire:loading wire:target="query,selectAlumno,limpiarAlumno"
                                class="mt-2 flex items-center gap-2 text-xs text-indigo-600 dark:text-indigo-300">
                                <span
                                    class="h-3 w-3 animate-spin rounded-full border-2 border-indigo-300 border-t-indigo-600"></span>
                                Buscando alumno...
                            </div>
                        </div>
                    @endif

                    @if ($modo_documento === 'licenciatura')
                        <!-- Licenciatura -->
                        <div class="md:col-span-5">
                            <flux:select label="Licenciatura" wire:model.live="filtro_licenciatura_id"
                                name="licenciatura_id" placeholder="Selecciona una licenciatura" class="w-full"
                                required>
                                <flux:select.option value="">Selecciona una licenciatura</flux:select.option>

                                @foreach ($licenciaturas as $licenciatura)
                                    <flux:select.option value="{{ $licenciatura->id }}">
                                        {{ $licenciatura->nombre }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    @if ($modo_documento === 'generacion')
                        <!-- Generación -->
                        <div class="md:col-span-5">
                            <flux:select label="Generación" wire:model.live="filtro_generacion_id" name="generacion_id"
                                placeholder="Selecciona una generación" class="w-full" required>
                                <flux:select.option value="">Selecciona una generación</flux:select.option>

                                @foreach ($generaciones as $generacion)
                                    <flux:select.option value="{{ $generacion->id }}">
                                        {{ $generacion->generacion }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    <!-- Tipo documento -->
                    <div class="md:col-span-2">
                        <flux:select name="tipo_documento" required label="Tipo de documento"
                            placeholder="Tipo de documento" class="w-full">
                            <flux:select.option value="certificado-de-estudios">Certificado de estudios
                            </flux:select.option>
                            <flux:select.option value="historial-academico">Historial Académico</flux:select.option>
                            <flux:select.option value="diploma">Diploma</flux:select.option>
                            <flux:select.option value="kardex">Kardex</flux:select.option>
                            <flux:select.option value="carta-de-pasante">Carta de pasante</flux:select.option>
                            <flux:select.option value="constancia-de-termino">Constancia de término
                            </flux:select.option>
                        </flux:select>
                    </div>

                    <!-- Fecha -->
                    <div class="md:col-span-2">
                        <label for="fecha_expedicion_documento"
                            class="mb-1.5 block text-sm font-medium text-neutral-700 dark:text-neutral-200">
                            Fecha de expedición
                        </label>

                        <input required type="date" name="fecha_expedicion" id="fecha_expedicion_documento"
                            autocomplete="off"
                            class="w-full rounded-xl border border-neutral-300 bg-white px-3 py-2.5 text-sm text-neutral-900 shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-100">
                    </div>

                    <!-- Botón -->
                    <div class="flex md:col-span-12 md:items-end">
                        <flux:button type="submit"
                            class="w-full md:w-auto md:mt-2 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 hover:from-indigo-600 hover:via-violet-600 hover:to-fuchsia-600 shadow-lg shadow-fuchsia-900/30"
                            variant="primary">
                            Descargar
                        </flux:button>
                    </div>
                </div>

                @if ($selectedAlumno && $modo_documento === 'alumno')
                    <div
                        class="mt-5 rounded-3xl border border-indigo-100 bg-indigo-50/60 p-4 dark:border-indigo-900/50 dark:bg-indigo-900/10">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white shadow-lg shadow-indigo-900/20">
                                    <span class="text-lg font-bold">
                                        {{ mb_substr($selectedAlumno['nombre'] ?? 'A', 0, 1) }}
                                    </span>
                                </div>

                                <div>
                                    <p
                                        class="text-base sm:text-lg font-semibold text-neutral-900 dark:text-neutral-50">
                                        {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                                        {{ $selectedAlumno['apellido_materno'] ?? '' }}
                                        {{ $selectedAlumno['nombre'] ?? '' }}
                                    </p>

                                    <p class="mt-1 text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                                        {{ $selectedAlumno['licenciatura']['nombre'] ?? 'Licenciatura no registrada' }}
                                        —
                                        {{ $selectedAlumno['generacion']['generacion'] ?? 'Generación no registrada' }}
                                    </p>

                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-white text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100 px-3 py-1 text-xs">
                                            <span class="font-medium">Matrícula:</span>
                                            <span
                                                class="font-mono">{{ $selectedAlumno['matricula'] ?? '----' }}</span>
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-white text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100 px-3 py-1 text-xs">
                                            <span class="font-medium">CURP:</span>
                                            <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '----' }}</span>
                                        </span>

                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-white text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100 px-3 py-1 text-xs">
                                            <span class="font-medium">Folio:</span>
                                            <span>{{ $selectedAlumno['folio'] ?? '----' }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <button type="button" wire:click="limpiarAlumno"
                                class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-300 dark:hover:bg-red-900/20">
                                Limpiar alumno
                            </button>
                        </div>

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs sm:text-sm">
                            <div class="rounded-2xl bg-white dark:bg-neutral-800/70 px-4 py-3">
                                <p class="text-neutral-500 dark:text-neutral-400">Generación</p>
                                <p class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $selectedAlumno['generacion']['generacion'] ?? '----' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white dark:bg-neutral-800/70 px-4 py-3">
                                <p class="text-neutral-500 dark:text-neutral-400">Modalidad</p>
                                <p class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $selectedAlumno['modalidad']['nombre'] ?? '----' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-white dark:bg-neutral-800/70 px-4 py-3">
                                <p class="text-neutral-500 dark:text-neutral-400">Cuatrimestre</p>
                                <p class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100">
                                    {{ $selectedAlumno['cuatrimestre']['nombre_cuatrimestre'] ?? '----' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- ACCORDIONS -->
        <div class="space-y-3">

            <!-- Acta de examen -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_acta_examen') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_acta_examen', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 21h8M12 17l-4-4h8l-4 4zM4 7h16l-2 5H6L4 7z" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Acta de examen</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    {{-- <livewire:admin.documentacion.acta-examen /> --}}
                </div>
            </div>

            <!-- Titulación -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_titulacion') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_titulacion', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 21h8M12 17l-4-4h8l-4 4zM4 7h16l-2 5H6L4 7z" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Titulación</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <livewire:admin.documentacion.titulacion />
                </div>
            </div>

            <!-- Identidad -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_identidad') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_identidad', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 7h14M5 11h14M5 15h7M12 19h7" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Identidad (Documentación personal)</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <livewire:admin.documentacion.identidad />
                </div>
            </div>

            <!-- Credenciales -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_credenciales') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_credenciales', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h10M7 11h5M7 15h7M10 3h4a2 2 0 012 2v14l-4-2-4 2V5a2 2 0 012-2z" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Credenciales</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <livewire:admin.documentacion.credenciales />
                </div>
            </div>

            <!-- Etiquetas -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_etiquetas') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_etiquetas', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h10M7 11h10M7 15h6M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Etiquetas</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <livewire:admin.documentacion.etiquetas />
                </div>
            </div>

            <!-- Justificantes -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_justificantes') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_justificantes', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h8M7 11h10M9 15h6M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Justificantes</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <livewire:admin.documentacion.justificantes />
                </div>
            </div>

            <!-- Oficios de solicitud -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_oficios') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_oficios', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fuchsia-500/10 text-fuchsia-600 dark:text-fuchsia-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h10M7 11h7M7 15h6M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
                            </svg>
                        </span>
                        <span class="text-sm sm:text-base font-semibold">Oficios de solicitud</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
                        Para la expedición de oficios, selecciona la generación, el tipo de documento y la fecha de
                        expedición.
                    </p>

                    <form action="{{ route('admin.pdf.documentacion.documento_oficios') }}" method="GET"
                        target="_blank" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <flux:select name="generacion" label="Selecciona la generación" class="w-full" required>
                                <flux:select.option value="">Selecciona una generación</flux:select.option>

                                @foreach ($generaciones as $generacion)
                                    <flux:select.option value="{{ $generacion->id }}">
                                        {{ $generacion->generacion }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select name="tipo_documento" label="Selecciona el tipo de documento" class="w-full"
                                required>
                                <flux:select.option value="">Selecciona un tipo de documento</flux:select.option>
                                <flux:select.option value="matriculas">Matrículas</flux:select.option>
                                <flux:select.option value="kardex">Kardex</flux:select.option>
                                <flux:select.option value="registro-boletos">Registro y boletas</flux:select.option>
                                <flux:select.option value="folios">Folios</flux:select.option>
                                <flux:select.option value="certificados">Certificados</flux:select.option>
                            </flux:select>

                            <flux:input required label="Fecha de expedición" name="fecha_expedicion" id="fecha_of"
                                type="date" placeholder="Selecciona una fecha" class="w-full" />

                            <div class="flex md:items-end">
                                <flux:button type="submit"
                                    class="w-full md:w-auto md:mt-6 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 hover:from-indigo-600 hover:via-violet-600 hover:to-fuchsia-600 shadow-md shadow-fuchsia-900/30"
                                    variant="primary">
                                    Descargar
                                </flux:button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Expedición registros / actas -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_expedicion_registros') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_expedicion_registros', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                            </svg>
                        </span>

                        <span class="text-sm sm:text-base font-semibold">
                            Expedición de registros de escolaridad y actas de resultados
                        </span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
                        Para la expedición de Registros de Escolaridad y Actas de Resultados, selecciona la
                        licenciatura, generación y el tipo de documento que deseas descargar.
                    </p>

                    <livewire:admin.documentacion.expedicion-documentos />
                </div>
            </div>

            <!-- Expedición sábanas -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_sabanas') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_sabanas', this.openAccordion);
                }
            }">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h10M7 11h10M7 15h10M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
                            </svg>
                        </span>

                        <span class="text-sm sm:text-base font-semibold">
                            Expedición de sábanas para certificados
                        </span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
                        Para la expedición de sábanas, selecciona la licenciatura y la generación correspondiente.
                    </p>

                    <livewire:admin.documentacion.sabanas />
                </div>
            </div>

            <!-- Estadística -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_estadistica') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_estadistica', this.openAccordion);
                }
            }" class="mb-1">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 19h4V9H4v10zm6 0h4V5h-4v14zm6 0h4v-7h-4v7z" />
                            </svg>
                        </span>

                        <span class="text-sm sm:text-base font-semibold">Estadística</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
                        Para la expedición de estadísticas, selecciona la licenciatura, generación o un resumen general.
                    </p>

                    <livewire:admin.documentacion.estadistica />
                </div>
            </div>

            <!-- Promedios -->
            <div x-data="{
                openAccordion: localStorage.getItem('doc_promedios') === 'true',
                toggle() {
                    this.openAccordion = !this.openAccordion;
                    localStorage.setItem('doc_promedios', this.openAccordion);
                }
            }" class="mb-1 mt-3">
                <button type="button" @click="toggle()"
                    class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition group">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 19h4V9H4v10zm6 0h4V5h-4v14zm6 0h4v-7h-4v7z" />
                            </svg>
                        </span>

                        <span class="text-sm sm:text-base font-semibold">Promedios</span>
                    </div>

                    <svg :class="{ 'rotate-180': openAccordion }"
                        class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openAccordion" x-transition
                    class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
                    <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
                        Para la expedición de promedios, selecciona la licenciatura, generación o un resumen general.
                    </p>

                    <livewire:admin.documentacion.promedios />
                </div>
            </div>

        </div>
    </div>
</div>
