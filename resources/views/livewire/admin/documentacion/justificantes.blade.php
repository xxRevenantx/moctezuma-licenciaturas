 <div x-data="{
        destroyJustificante(id, nombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El justificante se eliminará de forma permanente`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarJustificante', id);
                }
            });
        },
    }">

     <form wire:submit.prevent="crearJustificante" class="mb-4">
             <div class="grid  md:grid-cols-5 gap-3 items-center " >
                <flux:input required
                label="Buscar alumno"
                wire:model.live.debounce.500ms="query"
                name="alumno_id"
                id="query"
                type="text"
                placeholder="Buscar alumno por nombre, matrícula o CURP:"
                @focus="open = true"
                @input="open = true"
                @blur="setTimeout(() => open = false, 150)"
                wire:keydown.arrow-down="selectIndexDown"
                wire:keydown.arrow-up="selectIndexUp"
                wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
                autocomplete="off"
            />

                <x-input wire:model="fechas" label="Selecciona las fechas de justificación" type="text" id="fecha_expedicion" placeholder="Selecciona las fechas de justificación" class="w-full" />

            <flux:radio.group label="Justificación" wire:model="justificaciones">
                <flux:radio
                    name="justificacion"
                    value="Asuntos personales"
                    label="Asuntos personales"
                />
                <flux:radio
                    name="justificacion"
                    value="Problemas de salud"
                    label="Problemas de salud"
                />
                <flux:radio
                    name="justificacion"
                    value="otro"
                    label="Otro"
                />
            </flux:radio.group>

             <x-input wire:model="fecha_expedicion" label="Fecha de expedición" type="date" class="w-full" />


                <flux:button type="submit" class="mt-6" variant="primary">Crear Justificante</flux:button>
                </div>
        </form>




    @if (!empty($alumnos))
        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto"
            style="display: none"
        >
            @forelse ($alumnos as $index => $alumno)
                <li
                    class="p-2 cursor-pointer {{ $selectedIndex === $index ? 'bg-blue-200' : '' }}"
                    wire:click="selectAlumno({{ $index }})"
                    @mouseenter="open = true"
                >
                    <p class="font-bold text-indigo-600">
                        {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                    </p>
                    <p class="text-gray-700">
                        Matrícula: {{ $alumno['matricula'] ?? '' }} | CURP: {{ $alumno['CURP'] ?? '' }}
                    </p>
                </li>
            @empty
                <li class="p-2">No se encontraron alumnos.</li>
            @endforelse
        </ul>
    @endif

    @if ($selectedAlumno)
        <div class="mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:text-white">
            <p class="font-bold">
                {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
            </p>
            <p>Matrícula: {{ $selectedAlumno['matricula'] ?? '' }}</p>
            <p>CURP: {{ $selectedAlumno['CURP'] ?? '' }}</p>
            <p>Folio: {{ $selectedAlumno["folio"] ?? '----' }}</p>
            <p>Licenciatura: {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</p>
        </div>
    @endif


    @if(isset($justificantes) && count($justificantes) > 0)
        <div class="mt-8">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow overflow-hidden">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Alumno</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fechas Justificación</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Justificación</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha Expedición</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($justificantes as $justificante)
                        <tr>
                            <td class="px-4 py-2 text-gray-900">
                                {{ $justificante->alumno->apellido_paterno ?? '' }} {{ $justificante->alumno->apellido_materno ?? '' }} {{ $justificante->alumno->nombre ?? '' }}
                            </td>
                            <td class="px-4 py-2 text-gray-900">
                                @php
                                $fechas = explode(',', $justificante->fechas_justificacion);
                            @endphp

                            @foreach ($fechas as $fecha)
                                <flux:badge color="indigo" class="mr-2 mb-2">
                                    {{ \Carbon\Carbon::parse(trim($fecha))->format('d/m/Y') }}
                                </flux:badge>
                            @endforeach


                            </td>
                            <td class="px-4 py-2 text-gray-900">{{ $justificante->justificacion }}</td>
                            <td class="px-4 py-2 text-gray-900">{{ $justificante->fecha_expedicion }}</td>
                            <td class="px-4 py-2 text-gray-900">

                                  <div class="flex space-x-2 items-center">
                                    <form action="{{ route('justificantes.download', $justificante->id) }}" method="GET"
                                   target="_blank">
                                      <flux:button variant="primary" type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded cursor-pointer">
                                        <flux:icon.download />
                                      </flux:button>
                                  </form>

                                <flux:button variant="primary" @click="Livewire.dispatch('abrirJustificante', { id: {{ $justificante->id }} })"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>

                                <flux:button variant="danger"
                                    @click="destroyJustificante({{ $justificante->id }}, '{{ $justificante->justificacion }}')"
                                    class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">
                                    Eliminar
                                </flux:button>
                                  </div>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="mt-8 text-gray-500 text-center">No hay justificantes registrados.</div>
    @endif

<livewire:admin.documentacion.editar-justificante />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    flatpickr("#fecha_expedicion", {
        mode: "multiple",
        dateFormat: "Y-m-d"
    });
</script>


</div>
