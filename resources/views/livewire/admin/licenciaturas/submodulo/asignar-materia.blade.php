<div>

    @php
function isColorLight($hexColor) {
    $hexColor = str_replace('#', '', $hexColor);

    // Si es de 3 caracteres (#abc) conviértelo a 6 caracteres (#aabbcc)
    if (strlen($hexColor) == 3) {
        $r = hexdec(str_repeat(substr($hexColor,0,1),2));
        $g = hexdec(str_repeat(substr($hexColor,1,1),2));
        $b = hexdec(str_repeat(substr($hexColor,2,1),2));
    } else {
        $r = hexdec(substr($hexColor,0,2));
        $g = hexdec(substr($hexColor,2,2));
        $b = hexdec(substr($hexColor,4,2));
    }

    // Luminosidad (algoritmo estándar)
    $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    return $luminance > 0.6; // > 0.6 es claro, < 0.6 es oscuro (ajusta el umbral si lo necesitas)
}
@endphp


    <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
       <span>Filtrar por:</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2  p-2">


    {{-- <flux:field >
        <flux:label></flux:label>
        <flux:select wire:model.live="filtrar_foraneo">
            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
            <flux:select.option value="true">Foráneo</flux:select.option>
            <flux:select.option value="false">Local</flux:select.option>
        </flux:select>
      </flux:field> --}}


    </div>

    <div class="overflow-x-auto">
        <h3 class="mt-5">Buscar Materia:</h3>
        <flux:input type="text" wire:model.live="search" placeholder="Buscar Materia" class="p-2 mb-4 w-full" />

                <div wire:loading.delay
                wire:target="search, filtrar_generacion, filtrar_foraneo"
                       class="flex justify-center">
                    <svg class="animate-spin h-20 w-20 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                 </div>

    <div wire:loading.remove
                 wire:target="search">
    <div class="my-3">
          {{ $materias->links() }}
    </div>
     <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">#</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Materia</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                    <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Asignar Profesor</th>

                </tr>
            </thead>
            <tbody>


        @if ($materias->isEmpty())
            <tr>
                <td colspan="2" class="border px-4 py-2 text-center text-gray-600 dark:bg-gray-500 dark:text-white">
                    No hay materias disponibles
                </td>
            </tr>
        @endif
        @foreach ($materias as $key => $materia)

            <tr>
                <td class="border px-4 py-2">{{ $key + 1 }}</td>
                <td class="border px-4 py-2">{{ $materia->nombre }}</td>
                <td class="border px-4 py-2">{{ $materia->cuatrimestre->nombre_cuatrimestre }}</td>
                <td class="border px-4 py-2">

                            @php
                    $color = '';
                    $textColor = '#fff';
                    if (!empty($profesor_seleccionado[$materia->id])) {
                        $profesor = $profesores->firstWhere('id', $profesor_seleccionado[$materia->id]);
                        $color = $profesor ? $profesor->color : '';
                        if ($color && isColorLight($color)) {
                            $textColor = '#000';
                        }
                    }
                @endphp

                <flux:select
                    wire:model.live="profesor_seleccionado.{{ $materia->id }}"
                    style='{{ $color ? "background-color: $color; color: $textColor;" : "" }}'
                >
                    <flux:select.option value="">--Seleccionar profesor--</flux:select.option>
                    @foreach ($profesores as $profesor)
                        <flux:select.option value="{{ $profesor->id }}">
                            {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }} {{ $profesor->nombre }}
                        </flux:select.option>
                    @endforeach
                </flux:select>


                </td>
             </tr>
         @endforeach


            </tbody>
        </table>
        </div>
        </div>
</div>
