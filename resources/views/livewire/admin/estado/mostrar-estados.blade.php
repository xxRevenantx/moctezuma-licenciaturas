<div>

    <div x-data ="{
        destroyEstado(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `El Estado ${nombre} se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarEstado', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Estado:</h3>
            <div class="relative">
                <flux:input type="text" wire:model.live="search" placeholder="Buscar Estado..." class="p-2 mb-4 w-full pr-10" />
                <span class="absolute right-3 top-2 text-gray-500 hover:text-gray-700 cursor-pointer" @click="$wire.set('search', '')">
                    &times;
                </span>
            </div>


            <table class="min-w-full border-collapse border border-gray-200 table-striped">
                <thead>
                    <tr>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Estado</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
                    </tr>
                </thead>
                <tbody>
                    @if($estados->isEmpty())
                    <tr>
                        <td colspan="6" class="border px-4 py-2 text-center">No hay estados disponibles.</td>
                    </tr>
                    @else

                    @foreach($estados as $key => $estado)

                        <tr>
                        <td class="border px-4 py-2">{{ $key+1}}</td>
                        <td class="border px-4 py-2">{{ $estado->nombre }}</td>


                        <td class="border px-4 py-2">
                            <flux:button  @click="Livewire.dispatch('abrirEstado', { id: {{ $estado->id }} })"
                                class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                <flux:button variant="danger"
                            @click="destroyEstado({{ $estado->id }}, '{{ $estado->nombre }}')"
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
            {{ $estados->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.estado.editar-estado />


</div>
