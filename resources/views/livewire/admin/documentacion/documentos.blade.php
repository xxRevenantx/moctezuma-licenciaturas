<div class="relative" x-data="{ open: false }">
    <flux:input
        wire:model.live.debounce.500ms="query"
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


    @if (!empty($alumnos))
        <ul
            x-show="open"
            x-transition
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
        <div class="mt-4 p-4 border rounded bg-gray-50">
            <p class="font-bold">
                {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
            </p>
            <p>Matrícula: {{ $selectedAlumno['matricula'] ?? '' }}</p>
            <p>CURP: {{ $selectedAlumno['CURP'] ?? '' }}</p>
        </div>
    @endif
</div>
