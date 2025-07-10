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
    <div wire:loading.delay wire:target="query" class="flex justify-center items-center mt-2">
    <div class="flex items-center space-x-2">
        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span class="text-sm text-gray-600">Buscando profesor...</span>
    </div>
</div>


        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-100 bg-white border mt-1 rounded shadow z-50 max-h-60 overflow-y-auto"
            style="display: none; z-index: 99999;"
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
        <option value="9-12">SEP/DIC</option>
        <option value="1-4">ENE/ABR</option>
        <option value="5-8">MAY-AGO</option>
    </flux:select>

    {{-- TABLA DONDE SE MOSTRARÁN LAS MATERIAS ASIGNADAS AL PROFESOR --}}
 @if (!empty($materiasAsignadas))
    <div class="mt-6">


        {{-- Tabla --}}
       {{-- LOADER centrado mientras se carga --}}

{{-- CONTENIDO de la tabla --}}

    @if (!empty($materiasAsignadas))
        <div class="mt-6">
            <h3 class="font-bold text-lg mb-2">Materias Asignadas</h3>

            {{-- Buscador --}}
            <div class="mb-4">
                <label for="buscador" class="block font-semibold text-gray-700 mb-1">Buscar materia:</label>
                <input
                    id="buscador"
                    type="text"
                    wire:model.live="buscador_materia"
                    placeholder="Filtrar por nombre de materia..."
                    class="w-full px-4 py-2 border rounded focus:outline-none focus:ring focus:border-blue-300"
                />
            </div>

            <div wire:loading.delay wire:target="buscador_materia,selectProfesor,periodo_id" class="flex justify-center items-center h-40">
    <div class="flex flex-col items-center space-y-2">
        <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <p class="text-sm text-gray-600">Cargando materias...</p>
    </div>
</div>


            <div wire:loading.remove wire:target="buscador_materia,selectProfesor,periodo_id">

            {{-- Tabla --}}
           @if ($this->materiasFiltradas->isNotEmpty())
    <table class="w-full table-auto border border-gray-300 bg-white shadow rounded">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th class="px-4 py-2 border-b text-center">#</th>
                <th class="px-4 py-2 border-b text-center">Materia</th>
                <th class="px-4 py-2 border-b text-center">Modalidad</th>
                <th class="px-4 py-2 border-b text-center">Cuatrimestre</th>
                <th class="px-4 py-2 border-b text-center">Licenciatura</th>
                <th class="px-4 py-2 border-b text-center">Lista de</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($this->materiasFiltradas as $index => $materia)

                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 border-b text-center">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 border-b text-center">{{ $materia->materia }}</td>
                    <td class="px-4 py-2 border-b text-center">{{ $materia->modalidad }}</td>
                    <td class="px-4 py-2 border-b text-center">{{ $materia->cuatrimestre }}</td>
                    <td class="px-4 py-2 border-b text-center">{{ $materia->licenciatura }}</td>
                    <td class="px-4 py-2 border-b text-center">

                        <div class="grid grid-cols-2 gap-2">
                            <form action="{{ route('admin.pdf.documentacion.lista_asistencia') }}" method="GET" target="_blank">
                                <input type="hidden" name="materia_id"  value="{{ $materia->materia_id }}">
                                <input type="hidden" name="licenciatura_id"  value="{{ $materia->licenciatura_id }}">
                                <input type="hidden" name="cuatrimestre_id"  value="{{ $materia->cuatrimestre }}">
                                <input type="hidden" name="generacion_id"  value="{{ $materia->generacion_id }}">
                                <input type="hidden" name="modalidad_id"  value="{{ $materia->modalidad_id }}">
                                <input type="hidden" name="periodo"  value="{{ $periodo_id }}">


                                <flux:button variant="primary" type="submit"
                                    class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 rounded cursor-pointer w-25">
                                    Asistencia
                                </flux:button>
                            </form>
                            <form action="{{ route('admin.pdf.documentacion.lista_evaluacion') }}" method="GET" target="_blank">
                                <input type="hidden" name="materia_id"  value="{{ $materia->materia_id }}">
                                <input type="hidden" name="licenciatura_id"  value="{{ $materia->licenciatura_id }}">
                                <input type="hidden" name="cuatrimestre_id"  value="{{ $materia->cuatrimestre }}">
                                <input type="hidden" name="generacion_id"  value="{{ $materia->generacion_id }}">
                                <input type="hidden" name="modalidad_id"  value="{{ $materia->modalidad_id }}">
                                <input type="hidden" name="periodo"  value="{{ $periodo_id }}">
                                <flux:button variant="primary" type="submit"
                                    class="bg-cyan-500 hover:bg-cyan-600 text-white  py-2 rounded cursor-pointer w-25">
                                    Evaluación
                                </flux:button>
                            </form>
                        </div>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="text-center mt-4 text-gray-500">
        No hay resultados que coincidan con tu búsqueda.
    </div>
@endif

        </div>
    @else
        @if ($selectedProfesor)
            <p class="mt-4 text-gray-600">No se encuentran materias asignadas para el profesor seleccionado.</p>
        @endif
    @endif
</div>

    </div>
@else
    @if ($selectedProfesor)
        <p class="mt-4 text-gray-600">No se encuentran materias asignadas para el profesor seleccionado.</p>
    @endif
@endif




</div>
