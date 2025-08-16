<div x-data="{ open:false }" x-cloak>
    @php
        // Helper de contraste (evita redeclaración si se incluye varias veces)
        if (!function_exists('esColorOscuro')) {
            function esColorOscuro($hexColor) {
                $hexColor = ltrim($hexColor ?: '#ffffff', '#');
                if (strlen($hexColor) == 3) {
                    $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
                }
                $r = hexdec(substr($hexColor, 0, 2));
                $g = hexdec(substr($hexColor, 2, 2));
                $b = hexdec(substr($hexColor, 4, 2));
                $l = (0.299*$r + 0.587*$g + 0.114*$b);
                return $l < 128; // true si el fondo es oscuro
            }
        }
    @endphp

    <!-- Búsqueda -->
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm p-4 md:p-5 mb-4">
        <flux:input
            label="Buscar por profesor o materia"
            wire:model.live="busqueda"
            placeholder="Ej. Matemáticas o Juan Pérez"
            class="w-full md:w-1/2"
        />
    </div>



    {{-- Sin resultados --}}
    @if ($horarios->isEmpty())
        <div class="mt-6 p-4 text-center text-red-700 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl shadow-sm">
            No se encontraron resultados.
        </div>
    @else
        <!-- Botón PDF + Modal -->
        <div class="mb-4">
            <x-button x-on:click="open=true" variant="primary"
                      class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                <flux:icon.download />
                Ver PDF
            </x-button>
        </div>

        <div x-show="open"
             x-transition.opacity
             @keydown.escape.window="open=false"
             @click.self="open=false"
             x-effect="document.body.classList.toggle('overflow-hidden', open)"
             class="fixed inset-0 z-[120] grid place-items-center bg-black/40 dark:bg-black/50 backdrop-blur-sm p-4"
             style="display:none"
             tabindex="0"
        >
            <div class="relative w-full max-w-7xl rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-2xl">
                <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-800">
                    <h3 class="text-sm md:text-base font-semibold text-neutral-900 dark:text-neutral-100">Vista previa · Horario Semiescolarizada</h3>
                    <button @click="open=false"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white">
                        <span class="sr-only">Cerrar</span>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.3 5.71 12 12l6.3 6.29-1.41 1.42L10.59 13.4 4.29 19.7 2.88 18.3 9.17 12 2.88 5.71 4.29 4.3l6.3 6.29 6.29-6.3z"/></svg>
                    </button>
                </div>
                <div class="p-3">
                    <iframe src="{{ route('admin.pdf.horario-general-semiescolarizada') }}"
                            class="w-full h-[80vh] rounded-lg border border-neutral-200 dark:border-neutral-800"></iframe>
                </div>
            </div>
        </div>

        <!-- Loader general -->
        <div wire:loading.flex class="justify-center items-center py-10">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-neutral-600 dark:text-neutral-300 text-sm">Cargando…</span>
        </div>

        <!-- Tabla principal -->
        <div wire:loading.remove class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-neutral-100 dark:bg-neutral-700 sticky top-0 z-10">
                        <tr>
                            <th class="px-3 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Hora</th>
                            @foreach($columnasUnicas as $col)
                                <th class="px-3 py-2 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">
                                    {{ $col['etiqueta'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($horasUnicas as $hora)
                            <tr class="odd:bg-neutral-50/60 dark:odd:bg-neutral-800/60">
                                <td class="px-3 py-2 font-medium text-neutral-900 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">
                                    {{ $hora }}
                                </td>

                                @foreach ($columnasUnicas as $col)
                                    @php
                                        $item = $horarios->first(function ($h) use ($hora, $col) {
                                            return $h->hora === $hora &&
                                                   $h->cuatrimestre_id === $col['cuatrimestre_id'] &&
                                                   $h->licenciatura_id === $col['licenciatura_id'];
                                        });

                                        $materia     = optional(optional($item)->asignacionMateria)->materia?->nombre;
                                        $profesorObj = optional(optional($item)->asignacionMateria)->profesor;
                                        $profesor    = $profesorObj
                                            ? trim($profesorObj->nombre.' '.$profesorObj->apellido_paterno.' '.$profesorObj->apellido_materno)
                                            : null;
                                        $color = optional(optional($item)->asignacionMateria)->profesor?->color ?? '#ffffff';
                                        $txt   = esColorOscuro($color) ? '#ffffff' : '#111827'; // blanco o slate-900
                                    @endphp

                                    <td class="px-3 py-2 text-sm text-center align-middle border-b border-neutral-200 dark:border-neutral-700"
                                        style="background-color: {{ $color }}; color: {{ $txt }};">
                                        @if ($item)
                                            <div class="font-medium">{{ $materia }}</div>
                                            <div class="text-xs italic font-semibold">{{ $profesor }}</div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Materias del Profesor y Horas Totales (Semiescolarizada) -->
        <div class="mt-8">
            <h4 class="text-lg md:text-xl font-semibold text-neutral-900 dark:text-white mb-4">
                Materias del Profesor y Horas Totales
            </h4>

            <!-- Loader recalculo -->
            <div
                wire:loading.delay
                wire:target="busqueda"
                class="w-full flex flex-col items-center justify-center gap-3 p-6 border border-dashed border-neutral-300 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-800 text-center"
            >
                <svg class="animate-spin h-8 w-8 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4z"></path>
                </svg>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
                    Recalculando materias y horas del profesorado…
                </span>
            </div>

            @php
                // Preparación de datos para la tabla de profesores
                $ordenHoras = isset($horas) && is_array($horas) ? array_values($horas) : [];
                $posHora = $ordenHoras ? array_flip($ordenHoras) : [];
                $porProfesor = [];
                foreach ($horarios as $h) {
                    $asig = $h->asignacionMateria ?? null;
                    $prof = optional($asig)->profesor;
                    $mat  = optional($asig)->materia;

                    $pid = $prof?->id ?? 'sin';
                    if (!isset($porProfesor[$pid])) {
                        $porProfesor[$pid] = [
                            'profesor' => $prof ? $prof : (object)['nombre' => 'Sin asignar', 'apellido_paterno' => '', 'apellido_materno' => ''],
                            'color'    => $prof?->color ?? '#e5e7eb',
                            'horas'    => 0,
                            'materias' => [],
                        ];
                    }
                    $porProfesor[$pid]['horas']++;

                    if ($mat) {
                        if (!isset($porProfesor[$pid]['materias'][$mat->id])) {
                            $porProfesor[$pid]['materias'][$mat->id] = [
                                'nombre'        => $mat->nombre,
                                'clave'         => $mat->clave,
                                'licenciatura'  => $mat->licenciatura->nombre ?? 'N/A',
                                'count'         => 0,
                                'slots'         => [],
                            ];
                        }
                        $porProfesor[$pid]['materias'][$mat->id]['count']++;
                        $porProfesor[$pid]['materias'][$mat->id]['slots'][] = [
                            'dia'  => $h->dia->dia ?? ('Día '.$h->dia_id),
                            'hora' => $h->hora,
                        ];
                    }
                }
                // Orden alfabético (los 'sin' van al final)
                uksort($porProfesor, function($a,$b) use ($porProfesor){
                    if ($a==='sin' && $b==='sin') return 0;
                    if ($a==='sin') return 1;
                    if ($b==='sin') return -1;
                    $na = trim(($porProfesor[$a]['profesor']->nombre ?? '').' '.($porProfesor[$a]['profesor']->apellido_paterno ?? '').' '.($porProfesor[$a]['profesor']->apellido_materno ?? ''));
                    $nb = trim(($porProfesor[$b]['profesor']->nombre ?? '').' '.($porProfesor[$b]['profesor']->apellido_paterno ?? '').' '.($porProfesor[$b]['profesor']->apellido_materno ?? ''));
                    return mb_strtolower($na,'UTF-8') <=> mb_strtolower($nb,'UTF-8');
                });
                $totalHoras = array_sum(array_map(fn($x)=>$x['horas'],$porProfesor));

                $formatearChips = function(array $slots) use ($posHora){
                    $porDia = [];
                    foreach ($slots as $s) { $porDia[$s['dia']][] = $s['hora']; }
                    $chips = [];
                    foreach ($porDia as $dia => $horasDia) {
                        if ($posHora) { usort($horasDia, fn($a,$b)=>($posHora[$a]??999) <=> ($posHora[$b]??999)); }
                        else { sort($horasDia); }
                        $chips[] = ['dia'=>$dia,'horas'=>$horasDia];
                    }
                    return $chips;
                };
            @endphp

            <div
                wire:loading.remove
                wire:target="busqueda"
                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden"
            >
                @if(count($porProfesor))
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-neutral-100 dark:bg-neutral-700">
                                <tr>
                                    <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">#</th>
                                    <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Profesor</th>
                                    <th class="px-4 py-3 font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Materia (horas) y desglose por día</th>
                                    <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Total de Horas</th>
                                    <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Horario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=1; @endphp
                                @foreach ($porProfesor as $pid => $data)
                                    @php
                                        $p = $data['profesor'];
                                        $profColor = $data['color'] ?? '#e5e7eb';
                                        $txtColor  = esColorOscuro($profColor) ? '#ffffff' : '#222222';
                                        $nombre    = trim(($p->nombre ?? '').' '.($p->apellido_paterno ?? '').' '.($p->apellido_materno ?? ''));
                                    @endphp
                                    <tr class="odd:bg-neutral-50/60 dark:odd:bg-neutral-800/60">
                                        <td class="px-4 py-3 text-center font-medium text-neutral-900 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">{{ $i++ }}</td>
                                        <td class="px-4 py-3 text-center border-b border-neutral-200 dark:border-neutral-700">
                                            <span class="inline-block px-2 py-1 rounded-md" style="background-color: {{ $profColor }}; color: {{ $txtColor }};">
                                                {{ $nombre ?: 'Sin asignar' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
                                            @if(count($data['materias']))
                                                <ul class="space-y-2">
                                                    @foreach ($data['materias'] as $m)
                                                        @php $chips = $formatearChips($m['slots']); @endphp
                                                        <li class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-2">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                                                    {{ $m['nombre'] }}
                                                                    <span class="text-xs text-neutral-500">({{ $m['clave'] }})</span>
                                                                    <span class="text-xs text-neutral-500">({{ $m['licenciatura'] }})</span>
                                                                </div>
                                                                <span class="inline-flex items-center text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">
                                                                    {{ $m['count'] }} h
                                                                </span>
                                                            </div>
                                                            @if(count($chips))
                                                                <div class="mt-2 flex flex-wrap gap-1">
                                                                    @foreach ($chips as $c)
                                                                        <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded bg-neutral-100 dark:bg-neutral-700 text-neutral-800 dark:text-neutral-200">
                                                                            <strong class="mr-1">{{ $c['dia'] }}:</strong>
                                                                            {{ implode(', ', $c['horas']) }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-neutral-400 italic">Sin materias</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium text-neutral-900 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">
                                            {{ $data['horas'] }}
                                            <div class="text-[11px] text-neutral-500">Total de horas</div>
                                        </td>
                                        <td class="px-4 py-3 text-center border-b border-neutral-200 dark:border-neutral-700">
                                            @if($pid !== 'sin')
                                                <form action="{{ route('admin.pdf.horario-docente-semiescolarizada') }}" method="GET" target="_blank">
                                                    <input type="hidden" name="profesor_id" value="{{ $pid }}">
                                                    <input type="hidden" name="modalidad_id" value="2">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg">
                                                        <flux:icon.file-text />
                                                        Horario
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-neutral-400 text-xs italic">No disponible</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-sm font-semibold text-indigo-700 dark:text-indigo-300 border-t border-neutral-200 dark:border-neutral-700">
                                        Total global de horas: {{ $totalHoras }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center text-neutral-600 dark:text-neutral-300">No hay datos para mostrar.</div>
                @endif
            </div>
        </div>
    @endif
</div>
