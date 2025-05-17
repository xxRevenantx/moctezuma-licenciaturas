<div>

    <div x-data ="{
        destroyCiudad(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `La ciudad ${nombre} se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarCiudad', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Ciudad:</h3>
            <div class="relative">
                <flux:input type="text" wire:model.live="search" placeholder="Buscar Ciudad..." class="p-2 mb-4 w-full pr-10" />
                <span class="absolute right-3 top-2 text-gray-500 hover:text-gray-700 cursor-pointer" @click="$wire.set('search', '')">
                    &times;
                </span>
            </div>


            <table class="min-w-full border-collapse border border-gray-200 table-striped">
                <thead>
                    <tr>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">ID</th>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Ciudad</th>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700"></th>
                    </tr>
                </thead>
                <tbody>
                    @if($ciudades->isEmpty())
                    <tr>
                        <td colspan="6" class="border px-4 py-2 text-center text-center">No hay ciudades disponibles.</td>
                    </tr>
                    @else

                    @foreach($ciudades as $key => $ciudad)

                        <tr>
                        <td class="border px-4 py-2 text-center">{{ $key+1}}</td>
                        <td class="border px-4 py-2 text-center">{{ $ciudad->nombre }}</td>
                        <td class="border px-4 py-2 text-center">
                            <flux:button
                            variant="primary"
                             @click="Livewire.dispatch('abrirCiudad', { id: {{ $ciudad->id }} })"
                                class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer
                                hover:bg-yellow-600 transition duration-300 ease-in-out
                                ">Editar</flux:button>

                                <flux:button variant="danger"
                            @click="destroyCiudad({{ $ciudad->id }}, '{{ $ciudad->nombre }}')"
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
            {{ $ciudades->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.ciudad.editar-ciudad />


</div>
