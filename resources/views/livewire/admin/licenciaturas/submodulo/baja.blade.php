<div>
    <div x-data="{

        switchStatus(id, CURP) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El alumno con CURP: ${CURP} se activará nuevamente.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, activar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('switchStatus', id);
                }
            });
        }

    }">
    <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
       <span>Filtrar por:</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2  p-2">



      <flux:field>
        <flux:label>Generación</flux:label>
        <flux:select wire:model.live="filtrar_generacion">
            <flux:select.option value="">--Selecciona una generación---</flux:select.option>
            @foreach($generaciones as $generacion)
                <flux:select.option value="{{ $generacion->generacion_id }}">{{ $generacion->generacion->generacion }}</flux:select.option>
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


    </div>

    <div class="overflow-x-auto">
        <h3 class="mt-5">Buscar Estudiante:</h3>
        <flux:input type="text" wire:model.live="search" placeholder="Buscar Estudiante (Nombre, Apellido Paterno, Apellido Materno, CURP)" class="p-2 mb-4 w-full" />

                <div wire:loading.delay
                wire:target="search, filtrar_generacion, filtrar_foraneo"
                       class="flex justify-center">
                    <svg class="animate-spin h-20 w-20 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                 </div>

            <div wire:loading.remove
                 wire:target="search, filtrar_generacion, filtrar_foraneo">
                 <div class="flex justify-start items-center mb-4 gap-3">

                @if($baja->isNotEmpty())

            <flux:button wire:click="exportarbaja" variant="primary" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <div class="flex items-center gap-1">
                        <flux:icon.sheet />
                        <span>Exportar a Excel</span>
                        </div>
            </flux:button>


                <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>

                    @if ($contar_mujeres > 0)
                        <flux:badge color="pink">Mujeres: <flux:icon.venus /> {{ $contar_mujeres }} </flux:badge>
                        @else
                        <flux:badge color="pink">Mujeres: <flux:icon.venus /> 0 </flux:badge>
                    @endif
                    @if ($contar_hombres > 0)
                        <flux:badge color="blue">Hombres: <flux:icon.mars /> {{ $contar_hombres }}</flux:badge>
                        @else
                        <flux:badge color="blue">Hombres: <flux:icon.mars /> 0 </flux:badge>
                    @endif

                @else

                       <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>


                @endif
        </div>


<table class="min-w-full border-collapse border border-gray-200 table-striped">
    <thead>
        <tr>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Foráneo</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Foto</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Matrícula</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">CURP</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Nombre</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Género</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Generación</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Status</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Fecha de baja</th>
            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
        </tr>
    </thead>
    <tbody>
                    @php
                        $grouped = $baja->groupBy(fn($item) => $item->generacion->generacion );
                 @endphp
        @forelse ($grouped as $nombreGeneracion => $estudiantes)
            <tr>
                <td colspan="12" class="bg-gray-200 text-left font-bold px-4 py-2 dark:bg-gray-500">
                    Generación: {{ $nombreGeneracion }}

                </td>
            </tr>

            @foreach($estudiantes as $key => $estudiante)
                <tr class="{{ in_array($estudiante->id, $selected) ? 'bg-blue-100 dark:bg-blue-500' : '' }}">
                    <td class="border px-4 py-2">{{ $key + 1 }}</td>
                    <td class="border px-4 py-2">
                        @if($estudiante->foraneo === "true")
                            <flux:badge color="orange">Foráneo</flux:badge>
                        @else
                            <flux:badge color="indigo">Local</flux:badge>
                        @endif
                    </td>
                    <td class="border px-4 py-2 text-center m-auto">
                        @if($estudiante->foto)
                            <img src="{{ asset('storage/estudiantes/' . $estudiante->foto) }}" alt="Foto" class="w-10 h-10 rounded-full">
                        @else
                            <div class="flex items-center justify-center">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mb-2 mt-2">
                                    <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td class="border px-4 py-2">{{ $estudiante->matricula }}</td>
                    <td class="border px-4 py-2">{{ $estudiante->CURP }}</td>
                    <td class="border px-4 py-2">{{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }} {{ $estudiante->nombre }}</td>
                    <td class="border px-4 py-2 text-center">
                        @if($estudiante->sexo === "H")
                            <flux:badge color="blue">Hombre</flux:badge>
                        @else
                            <flux:badge color="pink">Mujer</flux:badge>
                        @endif
                    </td>
                    <td class="border px-4 py-2">{{ $estudiante->cuatrimestre->nombre_cuatrimestre }}</td>
                    <td class="border px-4 py-2">{{ $estudiante->generacion->generacion }}</td>
                    <td class="border px-4 py-2">
                        @if($estudiante->status === "true")
                            <flux:badge color="green">Activo</flux:badge>
                        @else
                            <flux:badge color="red">Baja</flux:badge>
                        @endif
                    </td>
                    <td class="border px-4 py-2">
                            {{ \Carbon\Carbon::parse($estudiante->fecha_baja)->format('d-m-y') }}
                    </td>
                    <td class="border px-4 py-2">

                        <flux:button square variant="primary"
                            @click="switchStatus({{ $estudiante->id }}, '{{ $estudiante->CURP }}')"
                            class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">
                          <flux:icon.pencil-square />
                        </flux:button>
                    </td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="11" class="text-center text-gray-600">No hay registros de estudiantes dados de baja.</td>
            </tr>
        @endforelse
    </tbody>
</table>

        <div class="mt-4">
            {{ $baja->links() }}
        </div>


           </div>
    </div>
</div>


</div>
