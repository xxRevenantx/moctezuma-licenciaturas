<div>

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


    <table>
        <thead>
            <th>Alumno</th>
            <th></th>
        </thead>
    </table>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    flatpickr("#fecha_expedicion", {
        mode: "multiple",
        dateFormat: "Y-m-d"
    });
</script>


</div>
