<div>

    <div x-data ="{
        destroyAccion(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `La acción '${nombre}'  se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarAccion', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Acción:</h3>
            <flux:input type="text" wire:model.live="search" placeholder="Buscar Acción..." class="p-2 mb-4  w-full" />


            <table class="min-w-full border-collapse border border-gray-200 table-striped">
                <thead>
                    <tr>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">ID</th>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Acción</th>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">URL</th>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700">Ícono</th>
                    <th class="border px-4 py-2 text-center bg-gray-100 dark:bg-neutral-700"></th>
                    </tr>
                </thead>
                <tbody>
                    @if($acciones->isEmpty())
                    <tr>
                        <td colspan="6" class="border px-4 py-2  text-center">No hay acciones disponibles.</td>
                    </tr>
                    @else

                    @foreach($acciones as $key => $accion)

                        <tr>
                        <td class="border px-4 py-2 text-center">{{ $key+1}}</td>
                        <td class="border px-4 py-2 text-center">{{ $accion->accion }}</td>
                        <td class="border px-4 py-2 text-center">{{ $accion->slug }}</td>
                        <td class="border px-4 py-2 text-center ">
                            @if($accion->icono)
                                <img class="w-10 block m-auto" src="{{asset('storage/acciones/'.$accion->icono)}}" alt="">
                            @else
                                -------
                            @endif


                        </td>


                        <td class="border px-4 py-2">
                            <flux:button  @click="Livewire.dispatch('abrirAccion', { id: {{ $accion->id }} })"
                                class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                <flux:button variant="danger"
                            @click="destroyAccion({{ $accion->id }}, '{{ $accion->accion }}')"
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
            {{ $acciones->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.cuatrimestre.editar-cuatrimestre />


</div>
