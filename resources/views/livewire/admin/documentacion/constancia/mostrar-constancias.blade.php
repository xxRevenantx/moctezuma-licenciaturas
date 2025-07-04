<div>

    <div x-data ="{
        destroyConstancia(id, nombre) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `La constancia se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarConstancia', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5">Buscar Constancia:</h3>
            <flux:input type="text" wire:model.live="search" placeholder="Buscar constancia..." icon="magnifying-glass"  class="p-2 mb-4  w-full" />

            {{-- <flux:input wire:model.live="search"  placeholder="Search..." icon="magnifying-glass" kbd="⌘K" /> --}}


            <div class="flex space-x-4 mb-4 justify-between">


            </div>
            <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Folio</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Matrícula</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Nombre del alumno</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Tipo de constancia</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Fecha de expedición</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @if($constancias->isEmpty())
                <tr>
                    <td colspan="7" class="border px-4 py-2 text-center">No hay constancias disponibles.</td>
                </tr>
                @else
                @foreach($constancias as $key => $constancia)
                    <tr>
                    <td class="border px-4 py-2 text-center">{{ $key+1 }}</td>
                    <td class="border px-4 py-2 text-center">
                        @if ($constancia->no_constancia < 9)
                            0{{ $constancia->no_constancia }}
                        @else
                          {{ $constancia->no_constancia }}

                        @endif
                    </td>
                    <td class="border px-4 py-2 text-center">{{ $constancia->alumno->matricula }}</td>
                    <td class="border px-4 py-2 text-center">{{ $constancia->alumno->nombre }} {{ $constancia->alumno->apellido_paterno }} {{ $constancia->alumno->apellido_materno }}</td>
                    <td class="border px-4 py-2 text-center">
                        @if ($constancia->tipo_constancia == 1)
                            Constancia de Estudios
                        @else
                           Constancia de Relaciones Exteriores
                        @endif
                    </td>
                    <td class="border px-4 py-2 text-center">{{ \Carbon\Carbon::parse($constancia->fecha_expedicion)->format('d/m/Y') }}</td>


                    <td class="border px-4 py-2 text-center">
                        <div class="flex gap-x-2">

                         <form action="{{ route('admin.pdf.documentacion.constancia') }}" method="GET" target="_blank" class="mb-4">
                            <input type="hidden" name="constancia_id" value="{{ $constancia->id }}">
                            <flux:button variant="primary" type="submit"
                                   class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded cursor-pointer">
                            <flux:icon.download />
                             </flux:button>
                        </form>


                        <flux:button variant="primary"
                            @click="Livewire.dispatch('abrirConstancia', { id: {{ $constancia->id }} })"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded cursor-pointer">
                            <flux:icon.pencil />
                        </flux:button>

                        <flux:button variant="danger"

                            @click="destroyConstancia({{ $constancia->id }}, '{{ $constancia->nombre }}')"
                            class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">
                            <flux:icon.trash  />
                        </flux:button>
                    </div>

                    </td>
                    </tr>
                @endforeach
                @endif
            </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $constancias->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.documentacion.constancia.editar-constancia  />


</div>
