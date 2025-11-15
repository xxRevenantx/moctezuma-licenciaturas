<div>
    <div
        x-data="{
            destroyEstudiante(id, CURP) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `El estudiante con CURP ${CURP} se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarEstudiante', id);
                    }
                });
            },

            confirmarCambioSeleccionados(selected, id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Se cambiaran ${selected} estudiantes al ${id}° Cuatrimestre`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, cambiar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('cambiarCuatrimestreSeleccionados', id);
                    }
                });
            }
        }"
        class="space-y-6"
    >
        {{-- ENCABEZADO + FILTROS --}}
        <div class="mt-4 rounded-3xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white/90 dark:bg-neutral-900/90 shadow-sm backdrop-blur">
            {{-- Encabezado --}}
            <div class="flex items-center justify-between gap-3 px-4 pt-4 pb-2 sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-200 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800 dark:text-white">
                            Filtrar matrícula
                        </h3>
                        <p class="text-xs sm:text-[13px] text-gray-500 dark:text-gray-400">
                            Acota la lista por generación y si el estudiante es foráneo o local.
                        </p>
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-2 text-[11px] text-gray-500 dark:text-gray-400">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span>Filtros activos según tu selección</span>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="border-t border-neutral-200/70 dark:border-neutral-800/70 mt-2">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 p-4 sm:p-5 bg-neutral-50/80 dark:bg-neutral-900/80 rounded-b-3xl">
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
                        <flux:label>Foráneo</flux:label>
                        <flux:select wire:model.live="filtrar_foraneo">
                            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                            <flux:select.option value="true">Foráneo</flux:select.option>
                            <flux:select.option value="false">Local</flux:select.option>
                        </flux:select>
                    </flux:field>

                    <flux:field class="lg:col-span-1 flex items-end">
                        <flux:label>Filtros</flux:label>
                        <flux:button
                            wire:click="limpiarFiltros"
                            variant="primary"
                            class="mt-1  sm:w-auto flex items-center justify-center gap-2"
                        >

                            <div class="flex gap-3 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                 class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                            </svg>
                            <span>Limpiar filtros</span>
                            </div>



                        </flux:button>
                    </flux:field>
                </div>
            </div>
        </div>

        {{-- BUSCADOR + EXPORTS + CONTADOR H/M --}}
        <div class="rounded-3xl border border-neutral-200/80 dark:border-neutral-800/80 bg-white/95 dark:bg-neutral-900/95 shadow-sm backdrop-blur">
            <div class="px-4 pt-4 sm:px-6 flex flex-col gap-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                            Buscar estudiante
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Nombre, apellidos o CURP.
                        </p>
                    </div>
                </div>

                <flux:input
                    type="text"
                    wire:model.live="search"
                    placeholder="Buscar Estudiante (Nombre, Apellido Paterno, Apellido Materno, CURP)"
                    class="w-full text-sm"
                />
            </div>

            {{-- Loader búsqueda/filtros --}}
            <div
                wire:loading.flex
                wire:target="search, filtrar_generacion, filtrar_foraneo"
                class="justify-center items-center py-8"
            >
                <div class="flex flex-col items-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-indigo-600"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-xs sm:text-sm text-indigo-600 dark:text-indigo-300">
                        Actualizando listado de estudiantes…
                    </span>
                </div>
            </div>

            <div
                wire:loading.remove
                wire:target="search, filtrar_generacion, filtrar_foraneo"
                class="px-4 pb-4 sm:px-6"
            >
                {{-- Toolbar export / badges --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex flex-wrap items-center gap-3">
                        @if($matricula->isNotEmpty())
                            {{-- PDF --}}
                            <form
                                method="get"
                                action="{{ route('admin.pdf.matricula') }}"
                                target="_blank"
                                class="inline-block"
                            >
                                @csrf
                                <input type="hidden" name="licenciatura_id" value="{{ $licenciatura->id }}">
                                <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                                <input type="hidden" name="filtrar_generacion" value="{{ $filtrar_generacion }}">
                                <input type="hidden" name="filtrar_foraneo" value="{{ $filtrar_foraneo }}">

                                <x-button
                                    type="submit"
                                    variant="primary"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3.5 py-2 rounded-full text-xs sm:text-sm flex items-center gap-2 shadow-sm mt-3"
                                >
                                     <div class="flex gap-3 items-center">
                                    <flux:icon.download class="h-4 w-4" />
                                    <span>Exportar PDF</span>
                                    </div>

                                </x-button>
                            </form>

                            {{-- Excel --}}
                            <flux:button
                                wire:click="exportarMatricula"
                                variant="primary"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-full text-xs sm:text-sm flex items-center gap-2 shadow-sm mt-3"
                            >
                             <div class="flex gap-3 items-center">
                                <flux:icon.sheet class="h-4 w-4" />
                                <span>Exportar Excel</span>
                            </div>
                            </flux:button>
                        @endif
                    </div>

                    {{-- Resumen H/M --}}
                    @if($matricula->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 text-xs sm:text-[13px]">
                            @if ($contar_mujeres > 0)
                                <flux:badge color="pink">
                                    Mujeres:
                                    <flux:icon.venus class="h-3.5 w-3.5" />
                                    {{ $contar_mujeres }}
                                </flux:badge>
                            @else
                                <flux:badge color="pink">
                                    Mujeres:
                                    <flux:icon.venus class="h-3.5 w-3.5" />
                                    0
                                </flux:badge>
                            @endif

                            @if ($contar_hombres > 0)
                                <flux:badge color="blue">
                                    Hombres:
                                    <flux:icon.mars class="h-3.5 w-3.5" />
                                    {{ $contar_hombres }}
                                </flux:badge>
                            @else
                                <flux:badge color="blue">
                                    Hombres:
                                    <flux:icon.mars class="h-3.5 w-3.5" />
                                    0
                                </flux:badge>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- TABLA ESTUDIANTES --}}
                <div class="mt-2 rounded-2xl border border-neutral-200/80 dark:border-neutral-800/80 overflow-hidden bg-white dark:bg-neutral-900 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-neutral-100 dark:bg-neutral-800/90">
                                <tr>
                                    @if ($filtrar_generacion)
                                        <th class="border border-neutral-200 dark:border-neutral-700 px-3 py-2 text-center text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">
                                            <input type="checkbox" wire:model.live="selectAll">
                                        </th>
                                    @endif
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">ID</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">L o F</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Foto</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Matrícula</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Folio</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">CURP</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Nombre</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Género</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Cuatrimestre</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Generación</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800 text-[11px] font-semibold text-neutral-600 dark:text-neutral-200 uppercase tracking-wide">Status</th>
                                    <th class="border px-3 py-2 bg-neutral-100 dark:bg-neutral-800"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-neutral-900">
                                @if (!$filtrar_generacion)
                                    <tr>
                                        <td colspan="13" class="border px-4 py-4 text-center text-sm text-gray-600 dark:bg-neutral-800 dark:text-neutral-100">
                                            Selecciona una generación para mostrar los estudiantes.
                                        </td>
                                    </tr>
                                @elseif ($matricula->isEmpty())
                                    <tr>
                                        <td colspan="13" class="border px-4 py-4 text-center text-sm text-gray-600 dark:bg-neutral-800 dark:text-neutral-100">
                                            No hay estudiantes registrados en la generación seleccionada.
                                        </td>
                                    </tr>
                                @else
                                    @foreach($matricula as $key => $estudiante)
                                        <tr class="transition-colors {{ in_array($estudiante->id, $selected) ? 'bg-indigo-50 dark:bg-indigo-900/50' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800/60' }}">
                                            @if ($filtrar_generacion)
                                                <td class="border px-3 py-2 text-center align-middle">
                                                    <input type="checkbox" wire:model.live="selected" value="{{ $estudiante->id }}">
                                                </td>
                                            @endif
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                {{ $key + 1 }}
                                            </td>
                                            <td class="border px-3 py-2 text-xs">
                                                @if($estudiante->foraneo === "true")
                                                    <flux:badge color="orange">Foráneo</flux:badge>
                                                @else
                                                    <flux:badge color="indigo">Local</flux:badge>
                                                @endif
                                            </td>
                                            <td class="border px-3 py-2 text-center align-middle">
                                                @if($estudiante->foto)
                                                    <img
                                                        src="{{ asset('storage/estudiantes/' . $estudiante->foto) }}"
                                                        alt="Foto"
                                                        class="w-10 h-10 rounded-full object-cover mx-auto"
                                                    >
                                                @else
                                                    <div class="flex items-center justify-center">
                                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-neutral-800 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-300" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                {{ $estudiante->matricula }}
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                {{ $estudiante->folio }}
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                {{ $estudiante->CURP }}
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-800 dark:text-neutral-50">
                                                {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }} {{ $estudiante->nombre }}
                                            </td>
                                            <td class="border px-3 py-2 text-center text-xs">
                                                @if($estudiante->sexo === "H")
                                                    <flux:badge color="blue">Hombre</flux:badge>
                                                @else
                                                    <flux:badge color="pink">Mujer</flux:badge>
                                                @endif
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                {{ $estudiante->cuatrimestre->nombre_cuatrimestre }}
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                {{ $estudiante->generacion->generacion }}
                                            </td>
                                            <td class="border px-3 py-2 text-xs text-neutral-700 dark:text-neutral-100">
                                                @if($estudiante->status === "true")
                                                    <flux:badge color="green">Activo</flux:badge>
                                                @else
                                                    <flux:badge color="red">Baja</flux:badge>
                                                @endif
                                            </td>
                                            <td class="border px-3 py-2 text-center">
                                                <div class="flex items-center justify-center gap-1.5">
                                                    <flux:button
                                                        variant="primary"
                                                        square
                                                        @click="Livewire.dispatch('abrirEstudiante', { id: {{ $estudiante->id }} })"
                                                        class="bg-amber-500 hover:bg-amber-600 text-white p-1.5 rounded-full shadow-sm"
                                                    >
                                                        <flux:icon.pencil-square class="h-4 w-4" />
                                                    </flux:button>

                                                    <flux:button
                                                        square
                                                        variant="danger"
                                                        @click="destroyEstudiante({{ $estudiante->id }}, '{{ $estudiante->CURP }}')"
                                                        class="bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-full shadow-sm"
                                                    >
                                                        <flux:icon.trash class="h-4 w-4" />
                                                    </flux:button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Paginación (si se activa) --}}
                <div class="mt-4">
                    {{-- {{ $matricula->links() }} --}}
                </div>

                {{-- ACCIONES MASIVAS --}}
                @if ($filtrar_generacion)
                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-gray-600 dark:text-neutral-300">
                        <div>
                            @if(count($selected) > 0)
                                <flux:dropdown>
                                    <flux:button
                                        variant="primary"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-full text-xs sm:text-sm flex items-center gap-2"
                                        icon:trailing="chevron-down"
                                    >
                                        Cambio de cuatrimestre ({{ count($selected) }})
                                    </flux:button>
                                    <flux:menu>
                                        @foreach($cuatrimestres as $cuatrimestre)
                                            <flux:menu.item
                                                @click="confirmarCambioSeleccionados({{ count($selected) }}, {{ $cuatrimestre->cuatrimestre_id }})"
                                            >
                                                {{ $cuatrimestre->cuatrimestre->nombre_cuatrimestre}}
                                            </flux:menu.item>
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            @endif
                        </div>

                        <div class="text-xs sm:text-sm text-right">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">
                                Estudiantes seleccionados:
                                <span class="font-semibold text-indigo-600 dark:text-indigo-300">
                                    {{ count($selected) }}
                                </span>
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <livewire:admin.licenciaturas.submodulo.matricula-editar>
</div>
