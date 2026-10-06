<div>
   <form action="{{ route('admin.pdf.documentacion.etiquetas') }}" method="GET" target="_blank" class="mb-4">
    <div class="grid md:grid-cols-4 gap-4">
        <flux:input
            label="Buscar alumno(s)"
            wire:model.live.debounce.500ms="query"
            name="buscar_alumno"
            id="buscar_alumno"
            type="text"
            placeholder="Buscar alumno por nombre, matrícula o CURP:"
            @focus="open = true"
            @input="open = true"
            @blur="setTimeout(() => open = false, 150)"
            wire:keydown.arrow-down="selectIndexDown"
            wire:keydown.arrow-up="selectIndexUp"
            wire:keydown.enter.prevent="selectAlumno({{ $selectedIndex }})"
            autocomplete="off"
        />
    </div>

    @if (!empty($alumnos))
        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto"
        >
            @foreach ($alumnos as $index => $alumno)
                <li
                    class="p-2 cursor-pointer {{ $selectedIndex === $index ? 'bg-blue-200' : '' }}"
                    wire:click="selectAlumno({{ $index }})"
                >
                    <p class="font-bold text-indigo-600">
                        {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                    </p>
                    <p class="text-gray-700">
                        Matrícula: {{ $alumno['matricula'] ?? '' }} | CURP: {{ $alumno['CURP'] ?? '' }}
                    </p>
                </li>
            @endforeach
        </ul>
    @endif

    @if (!empty($alumnosSeleccionados))
        <div class="mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:text-white">
            <p class="font-bold mb-2">Alumnos seleccionados:</p>
            <ul class="space-y-2">
                @foreach ($alumnosSeleccionados as $alumno)
                    <li class="flex items-center justify-between">
                        <div>
                            {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                            <span class="text-sm text-gray-600">({{ $alumno['matricula'] ?? '' }})</span>
                        </div>
                        <button type="button" wire:click="eliminarAlumnoSeleccionado({{ $alumno['id'] }})" class="text-red-600 hover:text-red-800">✕</button>
                        <input type="hidden" name="alumnos_ids[]" value="{{ $alumno['id'] }}">
                    </li>
                @endforeach
            </ul>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="submit" formaction="{{ route('admin.pdf.documentacion.etiquetas') }}"
                    class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                    Generar PDF
                </button>
                <button type="submit" formaction="{{ route('admin.word.documentacion.etiquetas') }}"
                    class="rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#00547b]">
                    Generar Word
                </button>
            </div>
        </div>
    @endif
</form>

</div>
