<div>

    <div x-data ="{
        destroyAsignacion(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `La asignación se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarAsignacion', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
                <span>Filtrar por:</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2  p-2">
                <flux:field>
                    <flux:label>Licenciatura</flux:label>
                    <flux:select wire:model.live="filtrar_licenciatura" class="p-2 mb-4 w-full">
                        <flux:select.option value="">--Selecciona una licenciatura---</flux:select.option>
                        @foreach($licenciaturas as $licenciatura)
                            <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
              </flux:field>

              <flux:field>
                <flux:label>Generación</flux:label>
                <flux:select wire:model.live="filtrar_generacion" class="p-2 mb-4 w-full">
                    <flux:select.option value="">--Selecciona una generación---</flux:select.option>
                    @foreach($generaciones as $generacion)
                        <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                    @endforeach
                </flux:select>
              </flux:field>

              <flux:field>
                <flux:label>Modalidad</flux:label>
                <flux:select wire:model.live="filtrar_modalidad" class="p-2 mb-4 w-full">
                    <flux:select.option value="">--Selecciona una modalidad---</flux:select.option>
                    @foreach($modalidades as $modalidad)
                        <flux:select.option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
              </flux:field>

              <flux:field>
                <flux:label>Activa / Inactiva</flux:label>
                <flux:select wire:model.live="filtrar_activa" class="p-2 mb-4 w-full">
                    <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                    <flux:select.option value="true">ACTIVA</flux:select.option>
                    <flux:select.option value="false">INACTIVA</flux:select.option>
                </flux:select>

              </flux:field>


            </div>

            <flux:input type="text" wire:model.live="search" placeholder="Buscar..." class="p-2 mb-4  w-full" />
            <div class="flex space-x-4 mb-4">

                @if($asignaciones->isNotEmpty())
                <button wire:click="exportarAsignacion"  class="text-white bg-green-700 hover:bg-green-800 focus:ring-4
                focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600
                 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                 <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>
                     <span> Exportar</span>
                </div>
                </button>

                @else
                <button disabled class="text-white bg-green-700 hover:bg-green-800 focus:ring-4
                focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600
                 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800 opacity-50 cursor-not-allowed">
                 <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>
                     <span> Exportar</span>
                </div>
                </button>
                @endif

            </div>


            <div wire:loading.delay
                wire:target="search, filtrar_licenciatura, filtrar_generacion, filtrar_modalidad, filtrar_activa"
               class="flex justify-center" >
                <img class="w-20 h-20  block m-auto" src="{{ asset('storage/loader.svg') }}" alt="">
            </div>


            <div wire:loading.remove
                 wire:target="search, filtrar_licenciatura, filtrar_generacion, filtrar_modalidad, filtrar_activa">

                        <table class="min-w-full border-collapse border border-gray-200 table-striped">
                            <thead>
                                <tr>
                                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 cursor-pointer"
                                        @click="$wire.sortBy('id')">
                                        <div class="flex items-center justify-between">
                                            <span>ID</span>
                                            @if($sortField === 'id')
                                                @if($sortDirection === 'asc')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                @endif
                                            @endif
                                        </div>
                                </th>
                                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 cursor-pointer"
                                    @click="$wire.sortBy('licenciatura_id')">
                                    <div class="flex items-center justify-between">
                                        <span>Licenciatura</span>
                                        @if($sortField === 'licenciatura_id')
                                        @if($sortDirection === 'asc')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </th>
                                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 cursor-pointer"
                                    @click="$wire.sortBy('generacion_id')">
                                    <div class="flex items-center justify-between">
                                        <span>Generación</span>
                                        @if($sortField === 'generacion_id')
                                            @if($sortDirection === 'asc')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </th>
                                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 cursor-pointer"
                                    @click="$wire.sortBy('modalidad_id')">
                                    <div class="flex items-center justify-between">
                                        <span>Modalidad</span>
                                        @if($sortField === 'modalidad_id')
                                            @if($sortDirection === 'asc')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            @endif
                                        @endif
                                    </div>
                                </th>

                                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">STATUS</th>
                                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($asignaciones->isEmpty())
                                <tr>
                                    <td colspan="6" class="border px-4 py-2 text-center">No hay asignaciones disponibles.</td>
                                </tr>
                                @else
                                @foreach($asignaciones as $key => $asignacion)
                                    <tr>
                                    <td class="border px-4 py-2">{{ $asignacion->id}}</td>
                                    <td class="border px-4 py-2">{{ $asignacion->licenciatura->nombre }}</td>
                                    <td class="border px-4 py-2">
                                        @if($asignacion->generacion != NULL)
                                            {{ $asignacion->generacion->generacion }}
                                        @else
                                        <flux:badge color="red">N/A</flux:badge>
                                        @endif

                                    </td>
                                    <td class="border px-4 py-2">{{ $asignacion->modalidad->nombre }}</td>
                                    <td class="border px-4 py-2">

                                        @if($asignacion->generacion != NULL)
                                            @if($asignacion->generacion->activa == "true")
                                            <flux:badge color="green">✔ ACTIVA</flux:badge>
                                            @else

                                                <flux:badge color="red">✘ INACTIVA</flux:badge>
                                            @endif
                                            @else
                                            <flux:badge color="red">N/A</flux:badge>
                                        @endif

                                    </td>


                                    <td class="border px-4 py-2">
                                        <flux:button  @click="Livewire.dispatch('abrirAsignacion', { id: {{ $asignacion->id }} })"
                                            class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                            <flux:button variant="danger"
                                        @click="destroyAsignacion({{ $asignacion->id }})"
                                        class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">
                                        Eliminar
                                        </flux:button>
                                    </td>
                                    </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>

             </div>
        </div>
        <div class="mt-4">
            {{ $asignaciones->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.asignar-generacion.editar-generacion />


</div>
