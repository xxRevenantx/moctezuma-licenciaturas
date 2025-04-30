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
            <div class="flex space-x-4 mb-4">

                <button wire:click="exportarDirectivos"  class="text-white bg-green-700 hover:bg-green-800 focus:ring-4
                focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600
                 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                 <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>
                     <span> Exportar</span>
                </div>
                </button>



                 <form wire:submit.prevent="importarDirectivos">
                    {{-- <input type="file" wire:model="archivo" accept=".xlsx,.xls,.csv" class="mb-2"> --}}
                    <flux:input type="file" wire:model="archivo" accept=".xlsx,.xls,.csv" class="mb-2" />

                    @error('archivo')
                        <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                    @enderror

                    <button type="submit"  class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4
                    focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-indigo-600
                     dark:hover:bg-indigo-700 focus:outline-none dark:focus:ring-indigo-800">
                     <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                          </svg>
                         <span> Importar</span>
                    </div>
                    </button>
                </form>

            </div>
            <table class="min-w-full border-collapse border border-gray-200">
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
                    <td colspan="6" class="border px-4 py-2 text-center">No hay directivos disponibles.</td>
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
