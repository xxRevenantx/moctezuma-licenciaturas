<div>
    <flux:input required
        label="Buscar Profesor"
        wire:model.live.debounce.500ms="query"
        name="profesor_id"
        id="query"
        type="text"
        placeholder="Buscar profesor por nombre o apellidos"
        @focus="open = true"
        @input="open = true"
        @blur="setTimeout(() => open = false, 150)"
        wire:keydown.arrow-down="selectIndexDown"
        wire:keydown.arrow-up="selectIndexUp"
        wire:keydown.enter="selectProfesor({{ $selectedIndex }})"
        autocomplete="off"
    />

    @if (!empty($profesores))
        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto"
            style="display: none"
        >
            @forelse ($profesores as $index => $profesor)
                <li
                    class="p-2 cursor-pointer {{ $selectedIndex === $index ? 'bg-blue-200' : '' }}"
                    wire:click="selectProfesor({{ $index }})"
                    @mouseenter="open = true"
                >
                    <p class="font-bold text-indigo-600">
                        {{ $profesor['apellido_paterno'] ?? '' }} {{ $profesor['apellido_materno'] ?? '' }} {{ $profesor['nombre'] ?? '' }}
                    </p>
                </li>
            @empty
                <li class="p-2">No se encontraron profesores.</li>
            @endforelse
        </ul>
    @endif

    @if ($selectedProfesor)
        <div class="mt-4 p-4 border rounded bg-indigo-200 dark:bg-gray-800 dark:text-white">
            <p class="font-bold">
                Nombre del profesor: {{ $selectedProfesor['apellido_paterno'] ?? '' }} {{ $selectedProfesor['apellido_materno'] ?? '' }} {{ $selectedProfesor['nombre'] ?? '' }}
            </p>
            <p class="font-bold">
                CURP del profesor: {{ $selectedProfesor['CURP'] ?? '' }}
            </p>
        </div>
    @endif

    <br>

    <flux:select label="Periodo" wire:model.live="periodo_id">
        <option value="">--Selecciona el periodo---</option>
        <option value="1">SEP/DIC</option>
        <option value="2">ENE/ABR</option>
        <option value="3">MAY-AGO</option>
    </flux:select>

    {{-- TABLA DONDE SE MOSTRARÁN LAS MATERIAS ASIGNADAS AL PROFESOR --}}
    @if (!empty($materiasAsignadas))
        <div class="mt-4 p-4 border rounded bg-gray-100">
            <h3 class="font-bold text-lg mb-2">Materias Asignadas</h3>
            <ul class="list-disc list-inside">
                @foreach ($materiasAsignadas as $materia)
                    <li>{{ $materia->nombre }}</li>
                @endforeach
            </ul>
        </div>
    @else
        No se encuentran materias asignadas para el profesor seleccionado.
    @endif
</div>
