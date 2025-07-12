<div>
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Credencial profesor</h1>
    </div>

     <form action="{{ route('admin.pdf.documentacion.credencial_profesor') }}" method="GET" target="_blank" class="mt-4">
    <div class="grid md:grid-cols-4 gap-4">
        <flux:input
            label="Buscar profesor(es)"
            wire:model.live.debounce.500ms="query"
            name="buscar_profesor"
            id="buscar_profesor"
            type="text"
            placeholder="Buscar profesor por nombre o CURP:"
            @focus="open = true"
            @input="open = true"
            @blur="setTimeout(() => open = false, 150)"
            wire:keydown.arrow-down="selectIndexDown"
            wire:keydown.arrow-up="selectIndexUp"
            wire:keydown.enter.prevent="selectProfesor({{ $selectedIndex }})"
            autocomplete="off"
        />
    </div>
 {{-- {{ var_export($profesores) }} --}}
    @if (!empty($profesores))
        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto"
        >



            @foreach ($profesores as $index => $profesor)
                <li
                    class="p-2 cursor-pointer {{ $selectedIndex === $index ? 'bg-blue-200' : '' }}"
                    wire:click="selectProfesor({{ $index }})"
                >
                    <p class="font-bold text-indigo-600">
                        {{ $profesor->nombre ?? '' }} {{ $profesor->apellido_materno ?? '' }} {{ $profesor->apellido_paterno ?? '' }}
                    </p>
                    <p class="text-gray-700">
                        CURP:  {{ $profesor->user->CURP ?? '' }}
                    </p>
                </li>
            @endforeach
        </ul>
    @endif

    @if (!empty($profesoresSeleccionados))
        <div class="mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:text-white">
            <p class="font-bold mb-2">Profesores seleccionados:</p>
            <ul class="space-y-2">
                @foreach ($profesoresSeleccionados as $profesor)
                    <li class="flex items-center justify-between">
                        <div>
                            {{ $profesor->apellido_paterno ?? '' }} {{ $profesor->apellido_materno ?? '' }} {{ $profesor->nombre ?? '' }}
                            <span class="text-sm text-gray-600">({{ $profesor->user->CURP ?? '' }})</span>
                        </div>
                        <button type="button" wire:click="eliminarProfesorSeleccionado({{ $profesor->id }})" class="text-red-600 hover:text-red-800">✕</button>
                        <input type="hidden" name="profesores_ids[]" value="{{ $profesor->id }}">
                    </li>
                @endforeach
            </ul>

            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Generar credenciales
            </button>
        </div>
    @endif
</form>
</div>
