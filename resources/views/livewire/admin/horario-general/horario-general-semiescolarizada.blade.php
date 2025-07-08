<div>
     @php
                function esColorOscuro($hexColor) {
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



                <flux:input
                    label="Buscar por profesor o materia"
                    wire:model.live="busqueda"
                    placeholder="Ej. Matemáticas o Juan Pérez"
                    class="w-1/2 mb-4"
                />


            <div x-data="{ openAccordionDocentes: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordionDocentes = !openAccordionDocentes"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-700 text-w dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                    <h2 class="text-lg font-bold text-white dark:text-white py-2">Total de horas por docente</h2>
                    <svg :class="{'transform rotate-180': openAccordionDocentes}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordionDocentes" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                    @if($horasPorDocente->count())
                        <table class="table-auto w-full mb-6 text-sm border border-collapse">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="border px-2 py-1 text-left">Profesor</th>
                                    <th class="border px-2 py-1 text-center">Total de horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($horasPorDocente as $prof)
                                    <tr>
                                        <td class="border px-2 py-1">
                                            <span class="inline-block w-3 h-3 mr-2 rounded-full" style="background-color: {{ $prof['color'] }}"></span>
                                            {{ $prof['apellido_paterno'] }} {{ $prof['apellido_materno'] }} {{ $prof['nombre'] }}
                                        </td>
                                        <td class="border px-2 py-1 text-center">{{ $prof['total_horas'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif


                        @if($horasPorDocente->isNotEmpty())
                        <div class="text-right text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            Total general de horas: <span class="text-blue-600 dark:text-blue-400">{{ $totalHoras }}</span>
                        </div>
                    @endif


                </div>


            </div>






                @if ($horarios->isEmpty())
    <div class="mt-6 p-4 text-center text-red-600 bg-red-100 border border-red-300 rounded shadow">
        No se encontraron resultados.
    </div>
@else

     <form action="{{ route('admin.pdf.horario-general-semiescolarizada') }}" method="GET" target="_blank" class="mb-4">
                            <flux:button variant="primary" type="submit"
                                   class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded cursor-pointer">
                                    <div class="flex items-center gap-2">
                                         <flux:icon.download /> Descargar Horario Semiescolarizada
                                    </div>
                             </flux:button>
                        </form>


    <table class="table-auto w-full border border-collapse text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-2 py-1 dark:bg-gray-600 dark:text-gray-100">Hora</th>
                @foreach($columnasUnicas as $col)
                    <th class="border px-2 py-1 dark:bg-gray-600 dark:text-gray-100 ">{{ $col['etiqueta'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($horasUnicas as $hora)
                <tr>
                    <td class="border px-2 py-1 font-bold dark:bg-gray-600 dark:text-gray-100">{{ $hora }}</td>
                    @foreach ($columnasUnicas as $col)
                        @php
                            $item = $horarios->first(function ($h) use ($hora, $col) {
                                return $h->hora === $hora &&
                                    $h->cuatrimestre_id === $col['cuatrimestre_id'] &&
                                    $h->licenciatura_id === $col['licenciatura_id'];
                            });

                            $materia = optional(optional($item)->asignacionMateria)->materia?->nombre;
                            $profesorObj = optional(optional($item)->asignacionMateria)->profesor;
                            $profesor = $profesorObj
                                ? trim($profesorObj->nombre . ' ' . $profesorObj->apellido_paterno . ' ' . $profesorObj->apellido_materno)
                                : null;
                            $color = optional(optional($item)->asignacionMateria)->profesor?->color ?? '#ffffff';

                            $textColor = (hexdec(substr($color, 1, 2)) * 0.299 +
                                        hexdec(substr($color, 3, 2)) * 0.587 +
                                        hexdec(substr($color, 5, 2)) * 0.114) > 186
                                        ? 'black' : 'white';
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
