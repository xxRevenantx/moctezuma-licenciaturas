<div class="relative" x-data="{ open: false }" x-cloak>
    <div class="flex flex-col gap-4">

            <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Constancias</h1>
     </div>


         <form wire:submit.prevent="guardarConstancia"  class="mb-4">
             <div class="grid  md:grid-cols-6 gap-4 " >

                <flux:input readonly variant="filled"  label="No. de control" wire:model.live="no_constancia"  placeholder="No." />


                <flux:input
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

                <flux:select wire:model="tipo_constancia" label="Tipo de documento" placeholder="Selecciona un tipo de documento" class="w-full">
                        <flux:select.option value="1">Constancia de Estudios</flux:select.option>
                        <flux:select.option value="2">Constancia de Relaciones Exteriores</flux:select.option>

                    </flux:select>

                <flux:input
                    label="Fecha de expedición"
                    type="date"
                    wire:model="fecha"
                    placeholder="Selecciona una fecha"
                    class="w-full"
                />
                <flux:button type="submit" class="mt-6" variant="primary">Crear constancia</flux:button>
                </div>
        </form>




    @if (!empty($alumnos))
        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto dark:bg-gray-800 dark:text-white"
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

            </div>

