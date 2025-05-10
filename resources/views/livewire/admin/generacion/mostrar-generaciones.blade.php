<div>

    <div x-data ="{
        destroyGeneracion(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `La generación ${nombre} se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarGeneracion', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Generación:</h3>
            <flux:input type="text" wire:model.live="search" placeholder="Buscar Generación..." class="p-2 mb-4  w-full" />

            <div class="flex space-x-4 mb-4 justify-between">
            <div>
                @if($generaciones->isNotEmpty())



            <flux:button wire:click="exportarGeneraciones" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
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

                @endif

            </div>

            <form wire:submit.prevent="importarGeneraciones">
                {{-- <input type="file" wire:model="archivo" accept=".xlsx,.xls,.csv" class="mb-2"> --}}

                @error('archivo')
                    <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                @enderror


                <div class="flex gap-3">

                <div class="relative">
                    <label title="Click para importar" for="button2" class="cursor-pointer flex items-center gap-4 px-5 py-2.5 before:border-gray-400/60 hover:before:border-gray-300 group before:bg-gray-100 before:absolute before:inset-0 before:rounded-lg before:border before:border-dashed before:transition-transform before:duration-300 hover:before:scale-105 active:duration-75 active:before:scale-95">
                      <div class="w-max relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                          </svg>

                      </div>
                      <div class="relative">
                          <span class="block text-base font-semibold relative text-blue-900 group-hover:text-blue-500">
                              Selecciona un archivo
                          </span>
                          <span class="mt-0.5 block text-sm text-gray-500"></span>
                      </div>
                     </label>
                    <input hidden="" accept=".xlsx,.xls,.csv"  wire:model.live="archivo" type="file" name="button2" id="button2">

                </div>

                <flux:button variant="primary" type="submit" >Importar</flux:button>

                {{-- <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Importar
                </button> --}}

            </div>
            </form>
            </div>

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
                        @click="$wire.sortBy('generacion')">
                        <div class="flex items-center justify-between">
                            <span>Generación</span>
                            @if($sortField === 'generacion')
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
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Status</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if($generaciones->isEmpty())
                    <tr>
                        <td colspan="6" class="border px-4 py-2 text-center">No hay generaciones disponibles.</td>
                    </tr>
                    @else
                    @foreach($generaciones as $key => $generacion)
                        <tr>
                        <td class="border px-4 py-2">{{ $generacion->id}}</td>
                        <td class="border px-4 py-2">{{ $generacion->generacion }}</td>
                        <td class="border px-4 py-2 text-center">
                            @if($generacion->activa == "true")
                                <flux:badge color="green">✔ ACTIVA</flux:badge>

                            @else
                                <flux:badge color="red">✘ INACTIVA</flux:badge>

                            @endif

                        </td>

                        <td class="border px-4 py-2">
                            <flux:button  @click="Livewire.dispatch('abrirGeneracion', { id: {{ $generacion->id }} })"
                                class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                <flux:button variant="danger"
                            @click="destroyGeneracion({{ $generacion->id }}, '{{ $generacion->generacion }}')"
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
        <div class="mt-4">
            {{ $generaciones->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.generacion.editar-generacion />


</div>
