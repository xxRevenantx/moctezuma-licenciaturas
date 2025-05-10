<div>

    <div x-data ="{
        destroyDirectivo(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `El directivo ${nombre} se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarDirectivo', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Directivo:</h3>
            <flux:input type="text" wire:model.live="search" placeholder="Buscar Personal..." class="p-2 mb-4  w-full" />
            <div class="flex space-x-4 mb-4 justify-between">
                <div>
                @if($directivos->isNotEmpty())


                <flux:button wire:click="exportarDirectivos" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
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

                <form wire:submit.prevent="importarDirectivos">
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

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Importar
                    </button>



                </div>
                </form>


            </div>
            <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Título</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Nombre completo</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cargo</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Teléfono</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Correo</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if($directivos->isEmpty())
                <tr>
                    <td colspan="7" class="border px-4 py-2 text-center">No hay directivos disponibles.</td>
                </tr>
                @else
                @foreach($directivos as $key => $directivo)
                    <tr>
                    <td class="border px-4 py-2">{{ $key+1 }}</td>
                    <td class="border px-4 py-2">{{ $directivo->titulo }}</td>
                    <td class="border px-4 py-2">{{ $directivo->nombre }} {{ $directivo->apellido_paterno }} {{ $directivo->apellido_materno }}</td>
                    <td class="border px-4 py-2">{{ $directivo->cargo }}</td>
                    <td class="border px-4 py-2">{{ $directivo->telefono }}</td>
                    <td class="border px-4 py-2">{{ $directivo->correo }}</td>

                    <td class="border px-4 py-2">
                        <flux:button  @click="Livewire.dispatch('abrirDirectivo', { id: {{ $directivo->id }} })"
                             class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                             <flux:button variant="danger"
                        @click="destroyDirectivo({{ $directivo->id }}, '{{ $directivo->nombre }}')"
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
            {{ $directivos->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.directivo.editar-directivo />


</div>
