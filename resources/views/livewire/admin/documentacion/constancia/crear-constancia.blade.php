<div class="relative space-y-4" x-data="{ open: false }" x-cloak>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    <!-- Título -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            Constancias
        </h1>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            Genera constancias para alumnos registrados.
        </p>
    </div>

    <!-- Card / Formulario -->
    <div
        class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">

        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <form wire:submit.prevent="guardarConstancia" class="p-4 sm:p-6 lg:p-8">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <!-- No. de control -->
                <div class="md:col-span-3">
                    <flux:input readonly variant="filled" label="No. de control" wire:model.live="no_constancia"
                        placeholder="No." />
                </div>

                <!-- Buscar alumno -->
                <div class="md:col-span-5">
                    <label for="query" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
                        Buscar alumno
                    </label>

                    <div class="relative" role="combobox" aria-haspopup="listbox" :aria-expanded="open">
                        <div class="pointer-events-none absolute inset-y-0 left-3 z-10 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="none">
                                <path stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m21 21-4.3-4.3M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" />
                            </svg>
                        </div>

                        <flux:input class="pl-10" wire:model.live.debounce.500ms="query" id="query" type="text"
                            placeholder="Nombre, matrícula, CURP o folio..." autocomplete="off" @focus="open = true"
                            @input="open = true" @keydown.escape.stop.prevent="open = false"
                            @blur="setTimeout(() => open = false, 180)" wire:keydown.arrow-down="selectIndexDown"
                            wire:keydown.arrow-up="selectIndexUp"
                            wire:keydown.enter.prevent="selectAlumno({{ $selectedIndex }})" />

                        <!-- Loader de búsqueda -->
                        <div wire:loading.delay wire:target="query,selectAlumno,limpiarAlumno"
                            class="absolute right-3 top-1/2 -translate-y-1/2">
                            <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24"
                                fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                        </div>

                        <!-- Resultados -->
                        @if (!empty($alumnos))
                            <ul x-show="open" x-transition x-cloak
                                class="absolute left-0 right-0 mt-2 max-h-72 overflow-auto rounded-2xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-xl z-50 p-2"
                                role="listbox">
                                @foreach ($alumnos as $index => $alumno)
                                    <li role="option"
                                        aria-selected="{{ $selectedIndex === $index ? 'true' : 'false' }}"
                                        wire:click="selectAlumno({{ $index }})"
                                        class="cursor-pointer rounded-xl px-3 py-3 transition
                                            {{ $selectedIndex === $index
                                                ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-100 dark:bg-blue-900/30 dark:text-blue-200 dark:ring-blue-800/60'
                                                : 'text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-neutral-800' }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold">
                                                    {{ $alumno['apellido_paterno'] ?? '' }}
                                                    {{ $alumno['apellido_materno'] ?? '' }}
                                                    {{ $alumno['nombre'] ?? '' }}
                                                </p>

                                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px]">
                                                    <span
                                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600 dark:bg-neutral-800 dark:text-gray-300">
                                                        Matrícula:
                                                        <span class="font-mono">
                                                            {{ $alumno['matricula'] ?? '----' }}
                                                        </span>
                                                    </span>

                                                    <span
                                                        class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600 dark:bg-neutral-800 dark:text-gray-300">
                                                        CURP:
                                                        <span class="font-mono">
                                                            {{ $alumno['CURP'] ?? '----' }}
                                                        </span>
                                                    </span>

                                                    <span
                                                        class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                                                        {{ $alumno['licenciatura']['nombre'] ?? 'Sin licenciatura' }}
                                                    </span>

                                                    <span
                                                        class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                                                        {{ $alumno['modalidad']['nombre'] ?? 'Sin modalidad' }}
                                                    </span>
                                                </div>
                                            </div>

                                            @if ($selectedIndex === $index)
                                                <span
                                                    class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-white">
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
                    </div>
                </div>

                <!-- Tipo de documento -->
                <div class="md:col-span-2">
                    <flux:select wire:model="tipo_constancia" label="Tipo de documento"
                        placeholder="Selecciona un tipo">
                        <flux:select.option value="1">
                            Constancia de Estudios
                        </flux:select.option>

                        <flux:select.option value="2">
                            Constancia de Relaciones Exteriores
                        </flux:select.option>
                    </flux:select>
                </div>

                <!-- Fecha expedición -->
                <div class="md:col-span-2">
                    <flux:input label="Fecha de expedición" type="date" wire:model="fecha_expedicion"
                        placeholder="Selecciona una fecha" />
                </div>

                <!-- Botón submit -->
                <div class="md:col-span-12 flex justify-end">
                    <flux:button type="submit" variant="primary" class="w-full sm:w-auto cursor-pointer"
                        :disabled="!$selectedAlumno">
                        <span wire:loading.remove wire:target="guardarConstancia">
                            Crear constancia
                        </span>

                        <span wire:loading wire:target="guardarConstancia" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            Procesando...
                        </span>
                    </flux:button>
                </div>
            </div>

            <!-- Overlay al guardar -->
            <div wire:loading.delay wire:target="guardarConstancia"
                class="pointer-events-none absolute inset-0 grid place-items-center bg-white/60 dark:bg-neutral-900/60">
                <div
                    class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-gray-200 dark:ring-neutral-800 shadow">
                    <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24"
                        fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                    </svg>

                    <span class="text-sm text-gray-700 dark:text-gray-200">
                        Guardando constancia...
                    </span>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen del alumno seleccionado -->
    @if ($selectedAlumno)
        <div
            class="rounded-2xl border border-gray-200 dark:border-neutral-800 bg-gray-50/80 dark:bg-neutral-900 p-4 sm:p-5">

            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-2">
                        Alumno seleccionado
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Nombre
                            </div>

                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                                {{ $selectedAlumno['apellido_materno'] ?? '' }}
                                {{ $selectedAlumno['nombre'] ?? '' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Matrícula
                            </div>

                            <div class="font-mono text-gray-800 dark:text-gray-200">
                                {{ $selectedAlumno['matricula'] ?? '----' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                CURP
                            </div>

                            <div class="font-mono text-gray-800 dark:text-gray-200">
                                {{ $selectedAlumno['CURP'] ?? '----' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Folio
                            </div>

                            <div class="font-mono text-gray-800 dark:text-gray-200">
                                {{ $selectedAlumno['folio'] ?? '----' }}
                            </div>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-2">
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Licenciatura
                            </div>

                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Modalidad
                            </div>

                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $selectedAlumno['modalidad']['nombre'] ?? '----' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Generación
                            </div>

                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $selectedAlumno['generacion']['generacion'] ?? '----' }}
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" wire:click="limpiarAlumno"
                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-300 dark:hover:bg-red-900/20">
                    Limpiar alumno
                </button>
            </div>
        </div>
    @endif
</div>
