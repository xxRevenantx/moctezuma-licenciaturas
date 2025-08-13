<div x-data="{ open: false }">
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
{{--
     <form action="{{ route('admin.pdf.horario-general-semiescolarizada') }}" method="GET" target="_blank" class="mb-4">
                            <flux:button variant="primary" type="submit"
                                   class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded cursor-pointer">
                                    <div class="flex items-center gap-2">
                                         <flux:icon.download /> Descargar Horario Semiescolarizada
                                    </div>
                             </flux:button>
                </form> --}}

                <!-- Botón que activa el modal -->
                        <x-button x-on:click="open = true" variant="primary" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-4">
                            <flux:icon.download />  Ver PDF
                        </x-button>

                        <!-- Modal Alpine.js, solo uno, fuera del v-for y NO redefinas x-data aquí -->
                        <div
                        x-show="open"
                        x-transition
                        @keydown.escape.window="open = false"
                        @click.self="open = false"
                        x-effect="document.body.classList.toggle('overflow-hidden', open)"
                        class="fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex justify-center items-center"
                        style="display: none;"
                        tabindex="0"
                    >
                        <div class="bg-white rounded-lg p-4 w-full max-w-7xl shadow-lg relative">
                            <iframe
                                src="{{ route('admin.pdf.horario-general-semiescolarizada') }}"
                                class="w-full h-[800px] rounded"
                            ></iframe>
                            <button
                                x-on:click="open = false"
                                class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>


<div wire:loading.flex class="justify-center items-center py-10">
    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
    <span class="text-blue-600 dark:text-blue-400">.</span>
</div>

    {{-- Tabla: solo visible cuando no está cargando --}}
<div wire:loading.remove>
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
</div>

  {{-- Materias del Profesor y Horas Totales --}}

  {{-- MATERIAS DEL PROFESOR Y HORAS TOTALES (SEMIESCOLARIZADA) --}}
<div class="mt-8">
    <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
        Materias del Profesor y Horas Totales
    </h4>

    {{-- Loader mientras recalcula --}}
    <div
        wire:loading.delay
        wire:target="busqueda"
        class="w-full flex flex-col items-center justify-center gap-3 p-6 border border-dashed border-gray-300 rounded-lg bg-white dark:bg-gray-800 text-center"
    >
        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4z"></path>
        </svg>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
            Recalculando materias y horas del profesorado…
        </span>
    </div>

    {{-- Contenido --}}
    <div
        wire:loading.remove
        wire:target="busqueda"
        class="overflow-x-auto"
    >
        @php
            // Construir agrupación por profesor a partir de $horarios (resultado ya filtrado por búsqueda)
            $porProfesor = [];     // [prof_id|'sin' => ['profesor' => Obj|array, 'color' => str, 'materias' => [materia_id => ['nombre','clave']], 'horas' => int]]

            foreach ($horarios as $h) {
                $asig = $h->asignacionMateria ?? null;
                $prof = optional($asig)->profesor;
                $mat  = optional($asig)->materia;

                $pid = $prof?->id ?? 'sin';
                if (!isset($porProfesor[$pid])) {
                    $porProfesor[$pid] = [
                        'profesor' => $prof
                            ? $prof
                            : (object)['nombre' => 'Sin asignar', 'apellido_paterno' => '', 'apellido_materno' => ''],
                        'color'    => $prof?->color ?? '#e5e7eb', // gris claro si no hay profesor
                        'materias' => [],
                        'horas'    => 0,
                    ];
                }

                // Contar slot horario
                $porProfesor[$pid]['horas']++;

                // Registrar materia única
                if ($mat) {
                    $porProfesor[$pid]['materias'][$mat->id] = [
                        'nombre' => $mat->nombre,
                        'clave'  => $mat->clave,
                        'licenciatura' => $mat->licenciatura?->nombre ?? 'N/A',
                        'licenciatura_id' => $mat->licenciatura?->id ?? 'N/A',
                    ];
                }
            }

            // Ordenar por apellido paterno (los "sin asignar" al final)
           // Ordenar por NOMBRE (ASC). "sin" al final
uksort($porProfesor, function ($a, $b) use ($porProfesor) {
    if ($a === 'sin' && $b === 'sin') return 0;
    if ($a === 'sin') return 1;
    if ($b === 'sin') return -1;

    $na = trim(($porProfesor[$a]['profesor']->nombre ?? '') . ' ' .
               ($porProfesor[$a]['profesor']->apellido_paterno ?? '') . ' ' .
               ($porProfesor[$a]['profesor']->apellido_materno ?? ''));

    $nb = trim(($porProfesor[$b]['profesor']->nombre ?? '') . ' ' .
               ($porProfesor[$b]['profesor']->apellido_paterno ?? '') . ' ' .
               ($porProfesor[$b]['profesor']->apellido_materno ?? ''));

    $na = mb_strtolower($na, 'UTF-8');
    $nb = mb_strtolower($nb, 'UTF-8');

    return $na <=> $nb; // ASC
});


            $totalHoras = array_sum(array_map(fn($x) => $x['horas'], $porProfesor));
        @endphp

        @if(count($porProfesor))
                    <table class="min-w-full border border-gray-200 rounded-lg shadow-sm bg-white dark:bg-gray-800">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">#</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Profesor</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Materias (únicas)</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Total de Horas</th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Horario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php $i = 1; @endphp
                    @foreach ($porProfesor as $pid => $data)
                        @php
                            $p = $data['profesor'];
                            $profColor = $data['color'] ?? '#e5e7eb';
                            $txtColor = esColorOscuro($profColor) ? '#ffffff' : '#222222';
                            $nombreCompleto = trim(($p->nombre ?? '') . ' ' . ($p->apellido_paterno ?? '') . ' ' . ($p->apellido_materno ?? ''));
                        @endphp
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 border-b text-center">{{ $i++ }}</td>
                            <td class="px-4 py-2 text-sm border-b ">
                                <span class="inline-block px-2 py-1 rounded" style="background-color: {{ $profColor }}; color: {{ $txtColor }};">
                                    {{ $nombreCompleto ?: 'Sin asignar' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm border-b text-gray-900 dark:text-gray-100">
                                @if(count($data['materias']))
                                    <ul class="list-disc pl-4 text-left inline-block">
                                        @foreach ($data['materias'] as $m)
                                            <li>
                                                {{ $m['nombre'] }}
                                                <span class="text-xs text-gray-500">({{ $m['clave'] }})</span>
                                                <span class="text-xs text-gray-500">({{ $m['licenciatura'] }})</span>


                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-gray-400 italic">Sin materias</span>
                                @endif
                            </td>



                            <td class="px-4 py-2 text-sm text-center border-b text-gray-700 dark:text-gray-200">
                                {{ $data['horas'] }}
                                <div class="text-xs text-gray-500">Total de horas</div>
                            </td>

                                                        {{-- NUEVA CELDA: botón para reporte de horas del docente --}}
                            <td class="px-4 py-2 text-sm text-center border-b">
                                @if($pid !== 'sin')
                                    <form action="{{ route('admin.pdf.horario-docente-semiescolarizada') }}" method="GET" target="_blank">
                                        <input type="hidden" name="profesor_id" value="{{ $pid }}">
                                        <input type="hidden" name="modalidad_id" value="2">
                                        {{-- (Opcional) Si quieres acotar por rango/periodo, agrega más hidden aquí --}}
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded">
                                            <flux:icon.file-text />
                                           Horario
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs italic">No disponible</span>
                                @endif
                            </td>


                        </tr>
                    @endforeach

                    {{-- Ajusta el colspan a 5 (ahora hay 5 columnas) --}}
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-center text-sm font-semibold text-blue-700 dark:text-blue-300 border-b">
                            Total global de horas: {{ $totalHoras }}
                        </td>
                    </tr>
                </tbody>
            </table>

        @else
            <div class="text-center text-gray-500 mt-4">
                No hay datos para mostrar.
            </div>
        @endif
    </div>
</div>



@endif
</div>
