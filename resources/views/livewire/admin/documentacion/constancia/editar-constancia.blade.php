<div
    x-data="{ show: @entangle('open'), searchOpen: false }"
    x-cloak
    x-trap.noscroll="show"
    x-show="show"
    x-effect="document.body.classList.toggle('overflow-hidden', show)"
    @keydown.escape.window="show=false; $wire.cerrarModal()"
    class="fixed inset-0 z-50 flex items-center justify-center"
    aria-live="polite"
>
    <!-- Overlay (modal-pro) -->
    <div
        class="absolute inset-0 bg-neutral-900/60 backdrop-blur-sm"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="show=false; $wire.cerrarModal()"
        aria-hidden="true"
    ></div>

    <!-- Modal (modal-pro) -->
    <div
        class="relative w-full max-w-2xl sm:max-w-3xl md:max-w-4xl mx-4 sm:mx-6 bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="titulo-modal-constancia"
        x-show="show"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        tabindex="-1"
        wire:ignore.self
    >
        <!-- Top accent -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Header -->
        <div class="px-5 sm:px-6 pt-4 pb-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 id="titulo-modal-constancia" class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">
                    Editar constancia
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Actualiza los datos y guarda los cambios.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl p-2 text-neutral-500 hover:text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                @click="show=false; $wire.cerrarModal()"
                aria-label="Cerrar"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 011.06 0L12 10.94l5.47-5.47a.75.75 0 111.06 1.06L13.06 12l5.47 5.47a.75.75 0 11-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 11-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 010-1.06z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <form wire:submit.prevent="actualizarConstancia" class="px-5 sm:px-6 pb-5">
            <flux:field>
                <div class="grid grid-cols-1 gap-4">
                    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                        <div class="p-4 sm:p-6 space-y-5">
                            <!-- Campos -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:input
                                    readonly
                                    variant="filled"
                                    label="No. de control"
                                    wire:model.live="no_constancia"
                                    placeholder="No."
                                />

                                <!-- Autocomplete Alumno -->
                                <div class="relative">
                                    <flux:input
                                        label="Buscar alumno"
                                        wire:model.live.debounce.500ms="query"
                                        name="alumno_id"
                                        id="query"
                                        type="text"
                                        placeholder="Nombre, matrícula o CURP"
                                        autocomplete="off"
                                        @focus="searchOpen = true"
                                        @input="searchOpen = true"
                                        @blur="setTimeout(() => searchOpen = false, 150)"
                                        wire:keydown.arrow-down="selectIndexDown"
                                        wire:keydown.arrow-up="selectIndexUp"
                                        wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
                                    />

                                    @if (!empty($alumnos))
                                        <ul
                                            x-show="searchOpen"
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-100"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            x-cloak
                                            class="absolute z-20 mt-1 w-full max-h-60 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-xl"
                                            style="display: none"
                                            role="listbox"
                                        >
                                            @forelse ($alumnos as $index => $alumno)
                                                <li
                                                    class="p-2 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ $selectedIndex === $index ? 'bg-blue-50 dark:bg-neutral-800' : '' }}"
                                                    wire:click="selectAlumno({{ $index }})"
                                                    @mouseenter="searchOpen = true"
                                                    role="option"
                                                >
                                                    <p class="font-semibold text-blue-700 dark:text-blue-300 truncate">
                                                        {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                                                    </p>
                                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                                        Matrícula: {{ $alumno['matricula'] ?? '' }} · CURP: {{ $alumno['CURP'] ?? '' }}
                                                    </p>
                                                </li>
                                            @empty
                                                <li class="p-2 text-sm text-neutral-600 dark:text-neutral-300">No se encontraron alumnos.</li>
                                            @endforelse
                                        </ul>
                                    @endif
                                </div>

                                <flux:select wire:model="tipo_constancia" label="Tipo de documento" placeholder="Selecciona un tipo de documento" class="w-full">
                                    <flux:select.option value="1">Constancia de Estudios</flux:select.option>
                                    <flux:select.option value="2">Constancia de Relaciones Exteriores</flux:select.option>
                                </flux:select>

                                <flux:input
                                    label="Fecha de expedición"
                                    type="date"
                                    wire:model="fecha_expedicion"
                                    placeholder="Selecciona una fecha"
                                    class="w-full"
                                />
                            </div>

                            <!-- Tarjeta alumno -->
                            <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 p-4">
                                @if ($selectedAlumno)
                                    <div class="space-y-1 text-sm">
                                        <p class="font-semibold text-neutral-900 dark:text-white">
                                            {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
                                        </p>
                                        <p class="text-neutral-700 dark:text-neutral-300">Matrícula: <span class="font-mono">{{ $selectedAlumno['matricula'] ?? '' }}</span></p>
                                        <p class="text-neutral-700 dark:text-neutral-300">CURP: <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '' }}</span></p>
                                        <p class="text-neutral-700 dark:text-neutral-300">Folio: {{ $selectedAlumno['folio'] ?? '----' }}</p>
                                        <p class="text-neutral-700 dark:text-neutral-300">Licenciatura: {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</p>
                                    </div>
                                @else
                                    <div class="space-y-1 text-sm">
                                        <p class="font-semibold text-neutral-900 dark:text-white">
                                            {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                                        </p>
                                        <p class="text-neutral-700 dark:text-neutral-300">Matrícula: <span class="font-mono">{{ $alumno['matricula'] ?? '' }}</span></p>
                                        <p class="text-neutral-700 dark:text-neutral-300">CURP: <span class="font-mono">{{ $alumno['CURP'] ?? '' }}</span></p>
                                        <p class="text-neutral-700 dark:text-neutral-300">Folio: {{ $alumno['folio'] ?? '----' }}</p>
                                        <p class="text-neutral-700 dark:text-neutral-300">Licenciatura: {{ $alumno['licenciatura']['nombre'] ?? '----' }}</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Acciones -->
                            <div class="flex flex-col sm:flex-row justify-end gap-2 pt-1">
                                <flux:button
                                    variant="primary"
                                    type="submit"
                                    class="w-full sm:w-auto cursor-pointer"
                                    wire:loading.attr="disabled"
                                    wire:target="actualizarConstancia"
                                >
                                    <div class="flex items-center gap-2">

                                        {{ __('Actualizar') }}
                                    </div>
                                </flux:button>

                                <flux:button
                                    type="button"
                                    class="w-full sm:w-auto cursor-pointer"
                                    @click="show=false; $wire.cerrarModal()"
                                >
                                    {{ __('Cancelar') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
            </flux:field>

            <!-- Loader interno -->
            <div
                wire:loading.flex
                wire:target="actualizarConstancia"
                class="absolute inset-0 z-20 items-center justify-center bg-white/70 dark:bg-neutral-900/70 backdrop-blur rounded-2xl"
            >
                <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-neutral-200 dark:ring-neutral-800 shadow">
                    <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm text-neutral-800 dark:text-neutral-200">Guardando cambios…</span>
                </div>
            </div>
        </form>
    </div>
</div>
