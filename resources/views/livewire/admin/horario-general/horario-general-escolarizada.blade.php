<div x-data="{ open: false }">

    @php
    function esColorDark($hexColor) {
        $hexColor = ltrim($hexColor, '#');
        if (strlen($hexColor) == 3) {
            $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
        }
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b);
        return $luminancia < 128;
    }
@endphp


   <div>
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Horario Escolarizada</h2>



            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Licenciatura</label>
                <flux:select wire:model.live="filtroLicenciatura" class="w-full border-gray-300 rounded shadow-sm">
                    <flux:select.option>-- Selecciona --</flux:select.option>
                    @foreach($licenciaturasDisponibles as $lic)
                        <flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>



             <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Generación</label>
                <flux:select wire:model.live="filtroGeneracion" class="w-full border-gray-300 rounded shadow-sm">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($generacionesDisponibles as $generacion)
                        <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Cuatrimestre</label>
                <flux:select wire:model.live="filtroCuatrimestre" class="w-full border-gray-300 rounded shadow-sm">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($cuatrimestresDisponibles as $cuatrimestre)
                        <flux:select.option value="{{ $cuatrimestre }}">{{ $cuatrimestre }}°</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

                    <flux:input
            label="Buscar por profesor o materia"
            wire:model.live="busqueda"
            placeholder="Ej. Matemáticas o Juan Pérez"
            class="w-full my-3"
        />




    @if($horarios->isEmpty())
        <div class="text-center text-gray-500 mt-10">No hay horarios que coincidan con los filtros seleccionados.</div>
    @else

                            <form method="GET" action="{{ route('admin.pdf.horario-escolarizada') }}" target="_blank" class="my-3">
                            <input type="hidden" name="licenciatura_id" value="{{ $filtroLicenciatura }}">
                            <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">
                            <input type="hidden" name="filtrar_generacion" value="{{ $filtroGeneracion }}">
                            <input type="hidden" name="filtrar_cuatrimestre" value="{{ $filtroCuatrimestre }}">



                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                <div class="flex items-center gap-1">
                                    <flux:icon.file-text/>
                                    <span>Horario PDF</span>
                                </div>
                            </button>
                        </form>

  <!-- Botón que activa el modal -->
                        {{-- <button x-on:click="open = true" class="mb-4 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded">
                            Ver PDF
                        </button>

                        <!-- Modal Alpine.js, solo uno, fuera del v-for y NO redefinas x-data aquí -->
                        <div
                            x-show="open"
                            x-transition
                            class="fixed inset-0 z-50 bg-gray-200 bg-opacity-40 flex justify-center items-center"
                            style="display: none;"
                        >
                            <div class="bg-white rounded p-4 w-full max-w-7xl shadow-lg relative">
                                <iframe
                                    src="{{ route('admin.pdf.horario-escolarizada') }}"
                                    class="w-full h-[800px]"
                                ></iframe>
                                <button x-on:click="open = false" class="mt-2 absolute top-2 right-2 bg-gray-200 hover:bg-gray-300 rounded px-3 py-1">
                                    Cerrar
                                </button>
                            </div>
                        </div> --}}


        <table class="table-auto w-full border border-collapse text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">Hora</th>
                    @foreach($columnasUnicas as $col)
                        <th class="border px-2 py-1">{{ $col['etiqueta'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($horasUnicas as $hora)
                    <tr>
                        <td class="border px-2 py-1 font-bold">{{ $hora }}</td>
                        @foreach ($columnasUnicas as $col)
                            @php
                                $item = $horarios->first(fn ($h) =>
                                    $h->hora === $hora &&
                                    $h->dia_id === $col['dia_id']
                                );

                                $materia = optional(optional($item)->asignacionMateria)->materia?->nombre;
                                $profesorObj = optional(optional($item)->asignacionMateria)->profesor;
                                $profesor = $profesorObj
                                    ? trim($profesorObj->nombre . ' ' . $profesorObj->apellido_paterno . ' ' . $profesorObj->apellido_materno)
                                    : null;
                                $color = $profesorObj?->color ?? '#ffffff';
                                $textColor = esColorDark($color) ? 'white' : 'black';
                            @endphp
                            <td class="border px-2 py-1 text-sm" style="background-color: {{ $color }}; color: {{ $textColor }}">
                                @if ($item)
                                    <div class="text-center">{{ $materia }}</div>
                                    <div class="text-xs italic text-center font-bold">{{ $profesor }}</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</div>

