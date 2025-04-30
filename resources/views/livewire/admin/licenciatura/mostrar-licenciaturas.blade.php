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
            <div class="flex space-x-4 mb-4">


                <button wire:click="exportarLicenciaturas"  class="text-white bg-green-700 hover:bg-green-800 focus:ring-4
                focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600
                 dark:hover:bg-green-700 focus:outline-none dark:focus:ring-green-800">
                 <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>
                     <span> Exportar</span>
                </div>
                </button>

            </div>
            <table class="min-w-full border-collapse border border-gray-200">
            <thead>
                <tr>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Imagen</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Licenciatura</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Nombre corto</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">RVOE</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if($licenciaturas->isEmpty())
                <tr>
                    <td colspan="6" class="border px-4 py-2 text-center">No hay licenciaturas disponibles.</td>
                </tr>
                @else
                @foreach($licenciaturas as $key => $licenciatura)
                    <tr>
                    <td class="border px-4 py-2">{{ $key+1 }}</td>
                    <td class="border px-4 py-2">
                        @if ($licenciatura->imagen)
                            <img src="{{ asset('storage/licenciaturas/' . $licenciatura->imagen) }}" alt="{{ $licenciatura->nombre }}" class="w-10 h-10 block m-auto">
                        @else
                            <img src="{{ asset('storage/logo-moctezuma.jpg') }}" alt="Default" class="w-10 h-10 block m-auto">

                        @endif

                    </td>
                    <td class="border px-4 py-2">{{ $licenciatura->nombre }}</td>
                    <td class="border px-4 py-2">{{ $licenciatura->nombre_corto }}</td>
                    <td class="border px-4 py-2">{{ $licenciatura->RVOE }}</td>
                    <td class="border px-4 py-2">
                        <flux:button

                        @click="Livewire.dispatch('abrirModal', { id: {{ $licenciatura->id }} })"

                            class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>
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
