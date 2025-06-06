<div>
    <h3>Buscar Licenciatura:</h3>
    <div x-data ="{
        destroyLicenciatura(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `La Licenciatura en ${nombre} se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarLicenciatura', id);

                    }
                })
            }
    }">


    <flux:input type="text" wire:model.live="search" placeholder="Buscar Licenciatura..." class=" p-2 mb-4 w-full" />
        <div class="overflow-x-auto">
            <div class="flex space-x-4 mb-4 p-1">

                @if($licenciaturas->isNotEmpty())



                 <flux:button wire:click="exportarLicenciaturas" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
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
            <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">ID</th>
                <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Imagen</th>
                <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Licenciatura</th>
                <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Nombre corto</th>
                <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">RVOE</th>
                <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if($licenciaturas->isEmpty())
                <tr>
                    <td colspan="6" class="border px-4 py-2  text-center">No hay licenciaturas disponibles.</td>
                </tr>
                @else
                @foreach($licenciaturas as $key => $licenciatura)
                    <tr>
                    <td class="border px-4 py-2 text-center">{{ $key+1 }}</td>
                    <td class="border px-4 py-2 text-center">
                        @if ($licenciatura->imagen)
                            <img src="{{ asset('storage/licenciaturas/' . $licenciatura->imagen) }}" alt="{{ $licenciatura->nombre }}" class="w-10 h-10 block m-auto">
                        @else
                            <img src="{{ asset('storage/logo-moctezuma.jpg') }}" alt="Default" class="w-10 h-10 block m-auto">

                        @endif

                    </td>
                    <td class="border px-4 py-2 text-center">{{ $licenciatura->nombre }}</td>
                    <td class="border px-4 py-2 text-center">{{ $licenciatura->nombre_corto }}</td>
                    <td class="border px-4 py-2 text-center">{{ $licenciatura->RVOE }}</td>
                    <td class="border px-4 py-2 text-center">
                        <flux:button

                        @click="Livewire.dispatch('abrirModal', { id: {{ $licenciatura->id }} })"
                        variant="primary"
                            class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-yellow-600 transition duration-300 ease-in-out">
                            Editar</flux:button>
                        <flux:button variant="danger"
                        @click="destroyLicenciatura({{ $licenciatura->id }}, '{{ $licenciatura->nombre }}')"
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
            {{ $licenciaturas->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.licenciatura.editar-licenciatura />



</div>
