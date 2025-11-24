<div
    x-data="{
        open: false,
        destroyJustificante(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El justificante se eliminará de forma permanente`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarJustificante', id);
                }
            });
        },
    }"
    class="w-full "
>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="w-full mx-auto space-y-6">

        <!-- ENCABEZADO -->
        <div class="relative overflow-hidden rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800">
            <!-- Barrita superior -->
            <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-sky-500 to-indigo-500"></div>

            <!-- Fondos decorativos -->
            <div class="pointer-events-none absolute -left-10 top-6 h-32 w-32 rounded-full bg-emerald-500/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-sky-500/15 blur-3xl"></div>

            <div class="relative px-4 sm:px-7 py-5 sm:py-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 via-sky-500 to-indigo-500 text-white shadow-lg shadow-emerald-900/40">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 7h10M7 11h10M7 15h7M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z"/>
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                                Gestión de justificantes
                            </h1>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 max-w-2xl">
                                Crea justificantes por alumno, registra las fechas justificadas y controla la descarga
                                de los documentos en PDF.
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 justify-start lg:justify-end">
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700 px-2.5 py-1 text-[11px] font-medium dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Módulo activo
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-neutral-900/5 px-2.5 py-1 text-[11px] text-neutral-600 dark:bg-neutral-800/80 dark:text-neutral-100">
                            Justificantes
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULARIO: CREAR JUSTIFICANTE -->
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-5 sm:py-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h2 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-neutral-50">
                        Nuevo justificante
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Busca al alumno, selecciona las fechas a justificar, el motivo y la fecha de expedición.
                    </p>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                    Las fechas se guardan y se muestran en forma de etiquetas.
                </p>
            </div>

            <form wire:submit.prevent="crearJustificante" class="space-y-4">
                <div class="grid gap-3 md:grid-cols-5 md:items-end">

                    <!-- Buscador de alumno + dropdown -->
                    <div class="md:col-span-2 relative">
                        <flux:input required
                            label="Buscar alumno"
                            wire:model.live.debounce.500ms="query"
                            name="alumno_id"
                            id="query"
                            type="text"
                            placeholder="Nombre, matrícula o CURP"
                            @focus="open = true"
                            @input="open = true"
                            @blur="setTimeout(() => open = false, 150)"
                            wire:keydown.arrow-down="selectIndexDown"
                            wire:keydown.arrow-up="selectIndexUp"
                            wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
                            autocomplete="off"
                        />

                        @if (!empty($alumnos))
                            <ul
                                x-show="open"
                                x-transition
                                x-cloak
                                class="absolute left-0 right-0 mt-1 max-h-60 overflow-auto rounded-2xl border border-neutral-200 bg-white shadow-lg z-20 dark:bg-neutral-900 dark:border-neutral-700"
                                style="display: none"
                            >
                                @forelse ($alumnos as $index => $alumno)
                                    <li
                                        class="px-3 py-2.5 cursor-pointer text-sm
                                               {{ $selectedIndex === $index ? 'bg-indigo-50 dark:bg-indigo-900/40' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800' }}"
                                        wire:click="selectAlumno({{ $index }})"
                                        @mouseenter="open = true"
                                    >
                                        <p class="font-semibold text-indigo-700 dark:text-indigo-300">
                                            {{ $alumno['apellido_paterno'] ?? '' }}
                                            {{ $alumno['apellido_materno'] ?? '' }}
                                            {{ $alumno['nombre'] ?? '' }}
                                        </p>
                                        <p class="text-[11px] text-neutral-600 dark:text-neutral-300 mt-0.5">
                                            Matrícula:
                                            <span class="font-mono">{{ $alumno['matricula'] ?? '' }}</span>
                                            &nbsp;|&nbsp;
                                            CURP:
                                            <span class="font-mono">{{ $alumno['CURP'] ?? '' }}</span>
                                        </p>
                                    </li>
                                @empty
                                    <li class="px-3 py-2 text-sm text-neutral-500">
                                        No se encontraron alumnos.
                                    </li>
                                @endforelse
                            </ul>
                        @endif
                    </div>

                    <!-- Fechas de justificación (flatpickr multiple) -->
                    <div class="md:col-span-2">
                        <x-input
                            wire:model="fechas"
                            label="Fechas de justificación"
                            type="text"
                            id="fecha_expedicion"
                            placeholder="Selecciona una o varias fechas"
                            class="w-full"
                        />
                    </div>

                    <!-- Fecha de expedición -->
                    <div class="md:col-span-1">
                        <x-input
                            wire:model="fecha_expedicion"
                            label="Fecha de expedición"
                            type="date"
                            class="w-full"
                        />
                    </div>
                </div>

                <!-- Motivo de justificación + botón -->
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 pt-1">
                    <flux:radio.group
                        label="Motivo de la justificación"
                        wire:model="justificaciones"
                        class="flex-1"
                    >
                        <flux:radio
                            name="justificacion"
                            value="Asuntos personales"
                            label="Asuntos personales"
                        />
                        <flux:radio
                            name="justificacion"
                            value="Problemas de salud"
                            label="Problemas de salud"
                        />
                        <flux:radio
                            name="justificacion"
                            value="Otro"
                            label="Otro"
                        />
                    </flux:radio.group>

                    <div class="md:w-auto">
                        <flux:button
                            type="submit"
                            class="w-full md:w-auto mt-1 md:mt-0 bg-gradient-to-r from-emerald-500 via-sky-500 to-indigo-500 hover:from-emerald-600 hover:via-sky-600 hover:to-indigo-600 shadow-md shadow-emerald-900/30"
                            variant="primary"
                        >
                            Crear justificante
                        </flux:button>
                    </div>
                </div>
            </form>

            <!-- Alumno seleccionado -->
            @if ($selectedAlumno)
                <div class="mt-4 rounded-2xl border border-dashed border-neutral-200 bg-neutral-50/80 px-4 py-3 text-sm
                            dark:border-neutral-700 dark:bg-neutral-900/80 dark:text-neutral-100">
                    <p class="font-semibold text-neutral-900 dark:text-white">
                        {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                        {{ $selectedAlumno['apellido_materno'] ?? '' }}
                        {{ $selectedAlumno['nombre'] ?? '' }}
                    </p>
                    <div class="mt-1 flex flex-wrap gap-2 text-xs sm:text-sm">
                        <span class="inline-flex items-center gap-1 rounded-full bg-white text-neutral-800 px-2.5 py-0.5 border border-neutral-200 dark:bg-neutral-800 dark:text-neutral-100 dark:border-neutral-600">
                            Matrícula:
                            <span class="font-mono">{{ $selectedAlumno['matricula'] ?? '' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-white text-neutral-800 px-2.5 py-0.5 border border-neutral-200 dark:bg-neutral-800 dark:text-neutral-100 dark:border-neutral-600">
                            CURP:
                            <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-2.5 py-0.5 border border-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:border-indigo-800/60">
                            Folio:
                            <span class="font-semibold">{{ $selectedAlumno["folio"] ?? '----' }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-neutral-900/5 text-neutral-700 px-2.5 py-0.5 border border-neutral-200 dark:bg-neutral-800 dark:text-neutral-100 dark:border-neutral-700">
                            Licenciatura:
                            <span class="font-medium">{{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</span>
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- BUSCADOR DE JUSTIFICANTES -->
        @if (count($justificantes) > 0)
            <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-md ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">
                            Justificantes registrados
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Filtra los justificantes por nombre, matrícula o CURP del alumno.
                        </p>
                    </div>
                    <div class="w-full sm:w-80">
                        <input
                            type="text"
                            wire:model.live="busqueda_justificante"
                            placeholder="Buscar justificante por alumno..."
                            class="w-full px-3 py-2.5 rounded-2xl border border-neutral-300 bg-white text-sm shadow-sm
                                   focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
                                   dark:bg-neutral-900 dark:text-white dark:border-neutral-700 dark:focus:ring-emerald-400"
                        />
                    </div>
                </div>
            </div>
        @endif

        <!-- TABLA DE JUSTIFICANTES -->
        @if(isset($justificantes) && count($justificantes) > 0)
            <div class="mt-2 rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-neutral-100/90 dark:bg-neutral-800/90">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
                                    Alumno
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
                                    Fechas de justificación
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
                                    Motivo
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
                                    Fecha de expedición
                                </th>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-neutral-600 dark:text-neutral-300">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                            @foreach($justificantes as $justificante)
                                <tr class="hover:bg-neutral-50/80 dark:hover:bg-neutral-800/70 transition">
                                    <!-- Alumno -->
                                    <td class="px-4 py-3 align-top text-neutral-900 dark:text-neutral-50">
                                        <div class="font-semibold">
                                            {{ $justificante->alumno->apellido_paterno ?? '' }}
                                            {{ $justificante->alumno->apellido_materno ?? '' }}
                                            {{ $justificante->alumno->nombre ?? '' }}
                                        </div>
                                        <div class="mt-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                                            Matrícula:
                                            <span class="font-mono">{{ $justificante->alumno->matricula ?? '' }}</span>
                                            &nbsp;|&nbsp;
                                            CURP:
                                            <span class="font-mono">{{ $justificante->alumno->CURP ?? '' }}</span>
                                        </div>
                                    </td>

                                    <!-- Fechas -->
                                    <td class="px-4 py-3 align-top text-neutral-900 dark:text-neutral-50">
                                        @php
                                            $fechas = explode(',', $justificante->fechas_justificacion);
                                        @endphp
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($fechas as $fecha)
                                                <flux:badge color="indigo" class="mr-0 mb-1">
                                                    {{ \Carbon\Carbon::parse(trim($fecha))->format('d/m/Y') }}
                                                </flux:badge>
                                            @endforeach
                                        </div>
                                    </td>

                                    <!-- Motivo -->
                                    <td class="px-4 py-3 align-top text-neutral-900 dark:text-neutral-50">
                                        {{ $justificante->justificacion }}
                                    </td>

                                    <!-- Fecha expedición -->
                                    <td class="px-4 py-3 align-top text-neutral-900 dark:text-neutral-50">
                                        {{ \Carbon\Carbon::parse(trim($justificante->fecha_expedicion))->format('d/m/Y') }}
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-4 py-3 align-top text-neutral-900 dark:text-neutral-50">
                                        <div class="flex flex-wrap gap-2 items-center">
                                            <!-- Descargar -->
                                            <form
                                                action="{{ route('admin.pdf.documentacion.justificantes', $justificante->id) }}"
                                                method="GET"
                                                target="_blank"
                                            >
                                                <flux:button
                                                    variant="primary"
                                                    type="submit"
                                                    class="px-3 py-2 rounded-xl cursor-pointer bg-indigo-500 hover:bg-indigo-600 text-white shadow-sm"
                                                >
                                                    <flux:icon.download class="w-4 h-4" />
                                                </flux:button>
                                            </form>

                                            <!-- Editar -->
                                            <flux:button
                                                variant="primary"
                                                @click="Livewire.dispatch('abrirJustificante', { id: {{ $justificante->id }} })"
                                                class="px-3 py-2 rounded-xl cursor-pointer bg-amber-500 hover:bg-amber-600 text-white shadow-sm"
                                            >
                                                Editar
                                            </flux:button>

                                            <!-- Eliminar -->
                                            <flux:button
                                                variant="danger"
                                                @click="destroyJustificante({{ $justificante->id }}, '{{ $justificante->justificacion }}')"
                                                class="px-3 py-2 rounded-xl cursor-pointer bg-rose-500 hover:bg-rose-600 text-white shadow-sm"
                                            >
                                                Eliminar
                                            </flux:button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="mt-4 rounded-3xl border border-dashed border-neutral-300 bg-white/90 px-4 py-6 text-center text-sm text-neutral-500 shadow-sm
                        dark:border-neutral-700 dark:bg-neutral-900/90 dark:text-neutral-400">
                No hay justificantes registrados aún. Crea el primero usando el formulario superior.
            </div>
        @endif

        <!-- MODAL EDITAR JUSTIFICANTE -->
        <livewire:admin.documentacion.editar-justificante />
    </div>

    <!-- FLATPICKR -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        flatpickr("#fecha_expedicion", {
            mode: "multiple",
            dateFormat: "Y-m-d"
        });
    </script>
</div>
