<div>
    <div x-data="{
        destroyCuatrimestre(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El ${nombre}° Cuatrimestre se eliminará de forma permanente`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarCuatrimestre', id);
                }
            });
        },

        confirmarEliminacionSeleccionados(selected) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se eliminarán ${selected} cuatrimestres de forma permanente`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarCuatrimestreSeleccionados');
                }
            });
        }

    }">

        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Cuatrimestre:</h3>
            <flux:input type="text" wire:model.live="search" placeholder="Buscar Cuatrimestre..." class="p-2 mb-4 w-full" />

            <table class="min-w-full border-collapse border border-gray-200 table-striped">
                <thead>
                    <tr>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">
                            <input type="checkbox" wire:model.live="selectAll">
                        </th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Meses</th>
                        <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuatrimestres as $cuatrimestre)
                        <tr :class="{'bg-blue-100': @js(in_array($cuatrimestre->id, $selected))}">
                            <td class="border px-4 py-2">
                                <input type="checkbox" wire:model.live="selected" value="{{ $cuatrimestre->id }}">
                            </td>
                            <td class="border px-4 py-2">{{ $cuatrimestre->id }}</td>
                            <td class="border px-4 py-2">{{ $cuatrimestre->cuatrimestre }}° CUATRIMESTRE</td>
                            <td class="border px-4 py-2">{{ $cuatrimestre->mes->meses }}</td>
                            <td class="border px-4 py-2">
                                <flux:button @click="Livewire.dispatch('abrirCuatrimestre', { id: {{ $cuatrimestre->id }} })"
                                    class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                <flux:button variant="danger"
                                    @click="destroyCuatrimestre({{ $cuatrimestre->id }}, '{{ $cuatrimestre->cuatrimestre }}')"
                                    class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">
                                    Eliminar
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="border px-4 py-2 text-center">No hay cuatrimestres disponibles.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">
                {{ $cuatrimestres->links() }}
            </div>

            <div class="mt-2 text-sm text-gray-600">
                <div class="mt-4">
                    @if(count($selected) > 0)
                        <flux:button
                            variant="danger"
                            class="bg-red-600 text-white px-4 py-2 rounded"
                            @click="confirmarEliminacionSeleccionados({{ count($selected) }})">
                            Eliminar seleccionados ({{ count($selected) }})
                        </flux:button>
                    @endif
                </div>

                Cuatrimestres seleccionados: {{ count($selected) }}
            </div>
        </div>

        <livewire:admin.cuatrimestre.editar-cuatrimestre />
    </div>
</div>
