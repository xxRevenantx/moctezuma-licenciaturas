<div>

    <div x-data="{

        activarProfesor(cantidad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Activar ${cantidad} profesor(es).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.activarprofesoresSeleccionados();
                }
            });
        },
        inactivarProfesor(cantidad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Inactivar ${cantidad} profesor(es).`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $wire.inactivarprofesoresSeleccionados();
                }
            });
        }
    }">


    <div class="flex flex-col md:flex-row gap-4 mb-4">

         <flux:field>
        <flux:label>Status</flux:label>
        <flux:select wire:model.live="filtrar_status">
            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
            <flux:select.option value="Activo">Activo</flux:select.option>
            <flux:select.option value="Inactivo">Inactivo</flux:select.option>
        </flux:select>
      </flux:field>

    </div>



    <h3>Buscar profesor:</h3>
    <flux:input type="text" wire:model.live="search" placeholder="Buscar Profesores..." class=" p-2 mb-4 w-full" />

     <div wire:loading.delay
                wire:target="search, filtrar_status"
                       class="flex justify-center">
                    <svg class="animate-spin h-20 w-20 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                 </div>


        <div wire:loading.remove
                 wire:target="search, filtrar_status">

        <div class="overflow-x-auto">
            <div class="flex space-x-4 mb-4 p-1">

                @if($profesores->isNotEmpty())

                     <flux:button wire:click="exportarProfesores" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
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


                @else

                    <flux:button disabled variant="primary"  class="bg-gray-100 hover:bg-gray-200 focus:ring-4 text-black">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
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

                @endif
            </div>


          @if ($profesores->count())
           <table class="min-w-full border-collapse border border-gray-200 table-striped">
                <thead>
                    <tr>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700  text-center"><input type="checkbox" wire:model.live="selectAll"></th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">#</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Foto</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Nombre del profesor</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">CURP</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Email</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Perfil</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Teléfono</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Status</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 ">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($profesores as $key => $profesor)
                        <tr class="{{ in_array($profesor->id, $selected) ? 'bg-blue-100 dark:bg-[#00659e]' : '' }}">
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white"><input type="checkbox" wire:model.live="selected" value="{{ $profesor->id }}"></td>
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">{{ $key + 1 }}</td>
                            <td class="border px-4 py-2 text-gray-600  dark:text-white">
                                @if ($profesor->foto)
                                    <img src="{{ asset('storage/profesores/' . $profesor->foto) }}" alt="Foto de {{ $profesor->nombre }}" class="w-16 h-16 rounded-full">
                                @else
                                    <div class="flex items-center justify-center">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mb-2 mt-2">
                                        <svg class="w-7 h-7 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                        </div>
                                    </div>
                                @endif

                            </td>
                            <td class="border px-4 py-2 text-gray-600  dark:text-white">{{ $profesor->apellido_paterno }} {{$profesor->apellido_materno}}  {{$profesor->nombre}}</td>
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">{{ $profesor->user->CURP }}</td>
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">{{ $profesor->user->email }}</td>
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">{{ $profesor->perfil }}</td>
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">{{ $profesor->telefono }}</td>

                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">
                                @if ($profesor->user->status == 'true')
                                    <flux:badge color="green">Activo</flux:badge>
                                @else
                                    <flux:badge color="red">Inactivo</flux:badge>
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-center text-gray-600  dark:text-white">
                                <flux:button variant="primary" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
                                 @click="Livewire.dispatch('abrirProfesor', { id: {{ $profesor->id }} })">
                                    Editar
                                </flux:button>


                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


            <div class="mt-4">
                {{ $profesores->links() }}
            </div>
        @else
            <table class="min-w-full border-collapse border border-gray-200">
                <thead>
                    <tr>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 text-center">#</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Foto</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Nombre del profesor</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">CURP</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Email</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Perfil</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Teléfono</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Status</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="9" class="text-center py-2 text-gray-500 dark:text-gray-300">
                            No se encontraron profesores con los filtros actuales.
                        </td>
                    </tr>
                </tbody>
            </table>
        @endif
        </div>
        </div>

          <div class="mt-2 text-sm text-gray-600 flex justify-between items-center">

            <div class="mt-4">

                @if(count($selected) > 0)
            Profesores seleccionados: {{ count($selected) }}
                @endif



            </div>


        </div>



    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.profesor.editar-profesor />


</div>
