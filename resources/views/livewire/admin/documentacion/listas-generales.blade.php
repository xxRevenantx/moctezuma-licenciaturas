<div>
    @php
        $alumnosColeccion = collect($alumnos);
        $alumnosPorGeneracion = $alumnosColeccion->groupBy('generacion_id');

        $textoFiltro = match ($filtrar_foraneo) {
            'true' => 'Foráneos',
            'false' => 'Locales',
            default => 'Todos',
        };

        $textoTotal = match ($filtrar_foraneo) {
            'true' => 'alumnos foráneos',
            'false' => 'alumnos locales',
            default => 'alumnos',
        };

        $tituloDescarga = match ($filtrar_foraneo) {
            'true' => 'Descargar lista de foráneos',
            'false' => 'Descargar lista de locales',
            default => 'Descargar lista general',
        };
    @endphp

    <div class="flex flex-col gap-3 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                Listas Generales
            </h1>

            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Consulta listas por licenciatura o visualiza el concentrado general de alumnos foráneos.
            </p>
        </div>

        <form
            target="_blank"
            method="GET"
            action="{{ route('admin.pdf.matricula-foraneos-licenciaturas') }}"
        >
            <flux:button
                type="submit"
                variant="primary"
                icon="eye"
                class="w-full sm:w-auto"
            >
                Ver todos los foráneos por licenciatura
            </flux:button>
        </form>
    </div>

    {{-- Filtros principales --}}
    <form
        wire:submit.prevent="consultarListas"
        class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <flux:select
                wire:model.live="licenciatura_id"
                label="Selecciona una licenciatura"
            >
                <flux:select.option value="">
                    -- Selecciona una licenciatura --
                </flux:select.option>

                @foreach ($licenciaturas as $licenciatura)
                    <flux:select.option value="{{ $licenciatura->id }}">
                        {{ $licenciatura->nombre }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model.live="filtrar_foraneo"
                label="Procedencia del alumno"
            >
                <flux:select.option value="">
                    Todos
                </flux:select.option>

                <flux:select.option value="true">
                    Solo foráneos
                </flux:select.option>

                <flux:select.option value="false">
                    Solo locales
                </flux:select.option>
            </flux:select>
        </div>

        <div class="mt-5 flex justify-end">
            <flux:button
                type="submit"
                variant="primary"
                icon="magnifying-glass"
            >
                Consultar listas
            </flux:button>
        </div>
    </form>

    {{-- Indicador de carga --}}
    <div
        wire:loading.flex
        wire:target="consultarListas,search,filtrar_foraneo"
        class="items-center justify-center py-10"
    >
        <div class="flex flex-col items-center gap-3">
            <svg
                class="h-14 w-14 animate-spin text-blue-600"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8z"
                ></path>
            </svg>

            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                Consultando alumnos...
            </span>
        </div>
    </div>

    <div
        wire:loading.remove
        wire:target="consultarListas,search,filtrar_foraneo"
    >
        @if ($consultado && $licenciatura_id)
            {{-- Resumen y buscador --}}
            <div class="mt-5 rounded-2xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            Licenciatura
                        </p>

                        <h2 class="mt-1 text-lg font-bold uppercase text-zinc-800 dark:text-white">
                            Licenciatura en {{ $licenciatura_nombre }}
                        </h2>

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @if ($filtrar_foraneo === 'true')
                                <flux:badge color="red">Foráneos</flux:badge>
                            @elseif ($filtrar_foraneo === 'false')
                                <flux:badge color="purple">Locales</flux:badge>
                            @else
                                <flux:badge color="blue">Todos</flux:badge>
                            @endif

                            <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                Filtro actual: {{ $textoFiltro }}
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        @if ($alumnosColeccion->isNotEmpty())
                            <form
                                target="_blank"
                                method="GET"
                                action="{{ route('admin.pdf.matricula-todas') }}"
                            >
                                <input
                                    type="hidden"
                                    name="licenciatura_id"
                                    value="{{ $licenciatura_id }}"
                                >

                                <input
                                    type="hidden"
                                    name="filtrar_foraneo"
                                    value="{{ $filtrar_foraneo }}"
                                >

                                <flux:button
                                    type="submit"
                                    variant="primary"
                                    icon="eye"
                                    class="w-full sm:w-auto"
                                >
                                    Ver todas las listas
                                </flux:button>
                            </form>
                        @else
                            <flux:button
                                type="button"
                                variant="primary"
                                icon="eye"
                                disabled
                                class="w-full sm:w-auto"
                            >
                                Ver todas las listas
                            </flux:button>
                        @endif

                        <div class="rounded-xl bg-white px-6 py-3 shadow-sm dark:bg-zinc-900">
                            <p class="text-center text-xs font-semibold uppercase text-zinc-500">
                                Total
                            </p>

                            <p class="text-center text-3xl font-bold text-zinc-900 dark:text-white">
                                {{ $alumnosColeccion->count() }}
                            </p>

                            <p class="text-center text-xs text-zinc-500">
                                {{ $textoTotal }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-5 flex justify-end">
                <div class="w-full md:w-96">
                    <flux:input
                        wire:model.live.debounce.400ms="search"
                        icon="magnifying-glass"
                        label="Buscar alumno"
                        placeholder="Nombre, apellidos, matrícula o CURP..."
                        clearable
                    />
                </div>
            </div>
        @endif

        @if ($consultado && $alumnosPorGeneracion->isNotEmpty())
            @foreach ($alumnosPorGeneracion as $generacionId => $grupoGeneracion)
                <section
                    wire:key="generacion-{{ $generacionId }}-{{ $filtrar_foraneo }}"
                    class="mt-8 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    {{-- Generación --}}
                    <div class="border-l-4 border-indigo-500 bg-indigo-50 p-4 dark:bg-indigo-950/30">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-indigo-800 dark:text-indigo-300">
                                    Generación
                                    {{ $grupoGeneracion->first()->generacion->generacion ?? $generacionId }}
                                </h3>

                                <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-400">
                                    Se muestran {{ strtolower($textoTotal) }} según el filtro seleccionado.
                                </p>
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <flux:badge color="indigo">
                                    Total: {{ $grupoGeneracion->count() }}
                                </flux:badge>

                                <form
                                    target="_blank"
                                    method="GET"
                                    action="{{ route('admin.pdf.matricula-generacion') }}"
                                >
                                    <input
                                        type="hidden"
                                        name="licenciatura_id"
                                        value="{{ $licenciatura_id }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="generacion_id"
                                        value="{{ $generacionId }}"
                                    >

                                    <input
                                        type="hidden"
                                        name="filtrar_foraneo"
                                        value="{{ $filtrar_foraneo }}"
                                    >

                                    <flux:button
                                        type="submit"
                                        variant="primary"
                                        icon="download"
                                    >
                                        {{ $tituloDescarga }}
                                    </flux:button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @php
                        $cuatrimestresGrupo = $grupoGeneracion->groupBy('cuatrimestre_id');
                    @endphp

                    @foreach ($cuatrimestresGrupo as $cuatrimestreId => $alumnosCuatrimestre)
                        <div
                            wire:key="cuatrimestre-{{ $generacionId }}-{{ $cuatrimestreId }}"
                            class="p-4"
                        >
                            <div class="mb-4 border-l-4 border-orange-500 bg-orange-50 p-3 dark:bg-orange-950/30">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-bold text-orange-800 dark:text-orange-300">
                                        {{ $alumnosCuatrimestre->first()->cuatrimestre->nombre_cuatrimestre ?? $cuatrimestreId . '° CUATRIMESTRE' }}
                                    </p>

                                    <flux:badge color="orange">
                                        {{ $alumnosCuatrimestre->count() }} alumnos
                                    </flux:badge>
                                </div>
                            </div>

                            @php
                                $porModalidad = $alumnosCuatrimestre->groupBy('modalidad_id');
                            @endphp

                            @foreach ($porModalidad as $modalidadId => $alumnosModalidad)
                                <div
                                    wire:key="modalidad-{{ $generacionId }}-{{ $cuatrimestreId }}-{{ $modalidadId }}"
                                    class="mb-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700"
                                >
                                    {{-- Modalidad --}}
                                    <div class="border-l-4 border-green-500 bg-green-50 p-4 dark:bg-green-950/30">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                            <div>
                                                <h4 class="font-semibold text-green-800 dark:text-green-300">
                                                    Modalidad:
                                                    {{ $alumnosModalidad->first()->modalidad->nombre ?? $modalidadId }}
                                                </h4>

                                                <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                                                    Filtro de procedencia: {{ $textoFiltro }}.
                                                </p>
                                            </div>

                                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <flux:badge color="green">
                                                    Total: {{ $alumnosModalidad->count() }}
                                                </flux:badge>

                                                <form
                                                    target="_blank"
                                                    method="GET"
                                                    action="{{ route('admin.pdf.matricula') }}"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="licenciatura_id"
                                                        value="{{ $licenciatura_id }}"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="filtrar_generacion"
                                                        value="{{ $generacionId }}"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="modalidad_id"
                                                        value="{{ $alumnosModalidad->first()->modalidad_id ?? $modalidadId }}"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="filtrar_foraneo"
                                                        value="{{ $filtrar_foraneo }}"
                                                    >

                                                    <flux:button
                                                        type="submit"
                                                        variant="primary"
                                                        icon="download"
                                                    >
                                                        {{ $tituloDescarga }}
                                                    </flux:button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tabla --}}
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">#</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Nombre</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Apellido paterno</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Apellido materno</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Matrícula</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Generación</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Modalidad</th>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-zinc-500">Procedencia</th>
                                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-zinc-500">Acciones</th>
                                                </tr>
                                            </thead>

                                            <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                                                @foreach ($alumnosModalidad as $alumno)
                                                    <tr
                                                        wire:key="alumno-lista-{{ $alumno->id }}"
                                                        class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/70"
                                                    >
                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                            {{ $loop->iteration }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-zinc-900 dark:text-white">
                                                            {{ $alumno->nombre }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                            {{ $alumno->apellido_paterno }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                            {{ $alumno->apellido_materno }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                            {{ $alumno->matricula }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                            {{ $alumno->generacion->generacion ?? '-' }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                                            {{ $alumno->modalidad->nombre ?? '-' }}
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3">
                                                            @if ($alumno->foraneo === 'true')
                                                                <flux:badge color="red">Foráneo</flux:badge>
                                                            @else
                                                                <flux:badge color="purple">Local</flux:badge>
                                                            @endif
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                                            <flux:button
                                                                type="button"
                                                                variant="primary"
                                                                square
                                                                icon="pencil-square"
                                                                x-on:click="Livewire.dispatch('abrirEstudiante', { id: {{ $alumno->id }} })"
                                                            />
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </section>
            @endforeach
        @elseif ($consultado && $licenciatura_id)
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6 text-center dark:border-red-900 dark:bg-red-950/30">
                <flux:icon.exclamation-triangle class="mx-auto mb-3 size-10 text-red-500" />

                @if (trim($search) !== '')
                    <p class="font-bold uppercase text-red-700 dark:text-red-300">
                        No se encontró al alumno “{{ $search }}” con el filtro {{ $textoFiltro }}.
                    </p>
                @else
                    <p class="font-bold uppercase text-red-700 dark:text-red-300">
                        No hay {{ $textoTotal }} registrados en esta licenciatura.
                    </p>
                @endif
            </div>
        @endif
    </div>

    <livewire:admin.licenciaturas.submodulo.matricula-editar />
</div>
