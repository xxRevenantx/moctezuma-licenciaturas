<div>

    <div x-data ="{
        destroyCuatrimestre(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `El ${nombre}° Cuatrimestre se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarCuatrimestre', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Cuatrimestre:</h3>
            <flux:input type="text" wire:model.live="search" placeholder="Buscar Cuatrimestre..." class="p-2 mb-4  w-full" />


            <table class="min-w-full border-collapse border border-gray-200 table-striped">
                <thead>
                    <tr>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Meses</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
                    </tr>
                </thead>
                <tbody>
                    @if($cuatrimestres->isEmpty())
                    <tr>
                        <td colspan="6" class="border px-4 py-2 text-center">No hay cuatrimestres disponibles.</td>
                    </tr>
                    @else

                    @foreach($cuatrimestres as $key => $cuatrimestre)

                        <tr>
                        <td class="border px-4 py-2">{{ $cuatrimestre->id}}</td>
                        <td class="border px-4 py-2">{{ $cuatrimestre->cuatrimestre }}° CUATRIMESTRE</td>
                        <td class="border px-4 py-2">{{ $cuatrimestre->mes->meses}}</td>


                        <td class="border px-4 py-2">
                            <flux:button  @click="Livewire.dispatch('abrirCuatrimestre', { id: {{ $cuatrimestre->id }} })"
                                class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                <flux:button variant="danger"
                            @click="destroyCuatrimestre({{ $cuatrimestre->id }}, '{{ $cuatrimestre->cuatrimestre }}')"
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
            {{ $cuatrimestres->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.cuatrimestre.editar-cuatrimestre />


</div>
