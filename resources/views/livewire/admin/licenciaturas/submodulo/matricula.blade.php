<div>
    <div x-data="{
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

    }">
    <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
       <span>Filtrar por:</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2  p-2">

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

        <flux:field>
              <flux:label>Filtros</flux:label>
                    <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center ">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>
        </flux:field>
    </div>




    <div class="overflow-x-auto">
        <h3 class="mt-5 px-2">Buscar Estudiante:</h3>
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

                @if($matricula->isNotEmpty())




                                        <div x-data="{
                                            open: false,
                                            pdfUrl: '',
                                            licenciatura_id: '{{ $licenciatura->id }}',
                                            modalidad_id: '{{ $modalidad->id }}',
                                            filtrar_generacion: '{{ $filtrar_generacion }}',
                                            filtar_foraneo: '{{ $filtrar_foraneo }}',
                                            }"
                                            x-on:keydown.escape.window="open = false"
                                        >
                                            <x-button
                                            x-on:click="
                                                pdfUrl = '{{ route('admin.pdf.matricula') }}'
                                                + '?licenciatura_id=' + licenciatura_id
                                                + '&modalidad_id=' + modalidad_id
                                                + '&filtrar_generacion=' + filtrar_generacion
                                                + '&filtar_foraneo=' + filtar_foraneo;
                                                open = true;
                                            "
                                            variant="primary"
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-3"
                                            >
                                            <div class="flex items-center gap-1">
                                                <flux:icon.download />
                                                <span>Lista PDF</span>
                                            </div>
                                            </x-button>

                                            <div
                                            x-show="open"
                                            x-transition
                                            x-cloak
                                            class="fixed inset-0 z-50 bg-gray-200 bg-opacity-40 flex justify-center items-center"
                                            style="display: none;"
                                            >
                                            <div class="bg-white rounded p-4 w-full max-w-7xl shadow-lg relative">
                                                <iframe
                                                :src="pdfUrl"
                                                class="w-full h-[800px] rounded"
                                                ></iframe>
                                                <button x-on:click="open = false" class="absolute top-0 right-0 bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">
                                                Cerrar
                                                </button>
                                            </div>
                                            </div>
                                        </div>



            <flux:button wire:click="exportarMatricula" variant="primary" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    <div class="flex items-center gap-1">
                        <flux:icon.sheet />
                        <span>Exportar a Excel</span>
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






                @endif
        </div>



        <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                     @if ($filtrar_generacion)
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">
                        <input type="checkbox" wire:model.live="selectAll">
                    </th>
                    @endif
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">L o F</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Foto</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Matrícula</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">CURP</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Nombre</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Género</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Generación</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Status</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
                </tr>
            </thead>
            <tbody>


                @if (!$filtrar_generacion)
            <tr>
                <td colspan="12" class="border px-4 py-2 text-center text-gray-600 dark:bg-gray-500 dark:text-white">
                    Selecciona una generación para mostrar los estudiantes.
                </td>
            </tr>
        @elseif ($matricula->isEmpty())
            <tr>
                <td colspan="12" class="border px-4 py-2 text-center text-gray-600 dark:bg-gray-500 dark:text-white">
                    No hay estudiantes registrados en la generación seleccionada.
                </td>
            </tr>
        @else
            @foreach($matricula as $key => $estudiante)
            <tr class="{{ in_array($estudiante->id, $selected) ? 'bg-blue-100 dark:bg-blue-500' : '' }}">
            @if ($filtrar_generacion)
            <td class="border px-4 py-2">
                <input type="checkbox" wire:model.live="selected" value="{{ $estudiante->id }}">
            </td>
            @endif
            <td class="border px-4 py-2">{{ $key + 1 }}</td>
            <td class="border px-4 py-2">
                @if($estudiante->foraneo === "true")
                    <flux:badge color="orange">Foraneo</flux:badge>
                @else
                    <flux:badge color="indigo">Local</flux:badge>
                @endif
            </td>
            <td class="border px-4 py-2 text-center m-auto">
                @if($estudiante->foto)
                    <img src="{{ asset('storage/estudiantes/' . $estudiante->foto) }}" alt="Foto" class="w-10 h-10 rounded-full">
                @else
                    <div class="flex items-center justify-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mb-2 mt-2 ">
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
                <flux:button variant="primary" square @click="Livewire.dispatch('abrirEstudiante', { id: {{ $estudiante->id }} })"
                    class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-yellow-600">
                    <flux:icon.pencil-square />
                </flux:button>

                <flux:button square variant="danger"
                    @click="destroyEstudiante({{ $estudiante->id }}, '{{ $estudiante->CURP }}')"
                    class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">
                    <flux:icon.trash />
                </flux:button>
            </td>
        </tr>
    @endforeach
@endif

            </tbody>
        </table>


        <div class="mt-4">
            {{-- {{ $matricula->links() }} --}}
        </div>
   @if ($filtrar_generacion)
        <div class="mt-2 text-sm text-gray-600 flex justify-between items-center">

            <div class="mt-4">

                @if(count($selected) > 0)



                <flux:dropdown>
                        <flux:button  variant="primary"

                        class="bg-indigo-600 text-white px-4 py-2 rounded" icon:trailing="chevron-down">Cambio de cuatrimestre ({{ count($selected) }})</flux:button>
                        <flux:menu>
                            @foreach($cuatrimestres as $cuatrimestre)
                                <flux:menu.item @click="confirmarCambioSeleccionados({{ count($selected) }}, {{ $cuatrimestre->cuatrimestre_id }})">
                                    {{ $cuatrimestre->cuatrimestre->nombre_cuatrimestre}}
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                </flux:dropdown>


                @endif



            </div>


        </div>

          Estudiantes seleccionados: {{ count($selected) }}
          @endif

           </div>
    </div>
</div>

    <livewire:admin.licenciaturas.submodulo.matricula-editar>

</div>
