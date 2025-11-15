<div class="mt-8 space-y-8">

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

    {{-- ENCABEZADO --}}
    <div class="flex items-center gap-3">
        <div class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200 shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
            </svg>
        </div>
        <div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-white">
                Horario Semiescolarizada
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Filtra por generación y cuatrimestre para construir y exportar el horario.
            </p>
        </div>
    </div>

    {{-- CARD DE FILTROS --}}
    <div class="rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white/90 dark:bg-gray-900/90 shadow-sm p-4 sm:p-5">
        <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">
            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                     viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 5.25h16.5M6.75 9.75h10.5M10 14.25h4M11.25 18.75h1.5" />
                </svg>
            </span>
            <span>Filtrar por</span>
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/60">
            <flux:field>
                <flux:label>Generación</flux:label>
                <flux:select wire:model.live="filtrar_generacion">
                    <flux:select.option value="">--Selecciona una generación---</flux:select.option>
                    @foreach($generaciones as $generacion)
                        <flux:select.option value="{{ $generacion->generacion_id }}">
                            {{ $generacion->generacion->generacion }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>Cuatrimestre</flux:label>
                <flux:select wire:model.live="filtrar_cuatrimestre">
                    <flux:select.option value="">--Selecciona una cuatrimestre---</flux:select.option>
                    @foreach($cuatrimestres as $cuatrimestre)
                        <flux:select.option value="{{ $cuatrimestre->cuatrimestre_id }}">
                            {{ $cuatrimestre->cuatrimestre->nombre_cuatrimestre}}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field class="lg:col-span-2 flex items-end">
                <flux:label>Filtros</flux:label>
                <flux:button wire:click="limpiarFiltros" variant="primary" class="mt-1 w-full lg:w-auto">
                    <div class="flex items-center justify-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                             class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>
                        <span>Limpiar filtros</span>
                    </div>
                </flux:button>
            </flux:field>
        </div>
    </div>

    {{-- CARD HORARIO + PDF --}}
    <div class="rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white/80 dark:bg-gray-900/90 shadow-sm p-4 sm:p-5">
        <div class="overflow-x-auto">

            {{-- Filtros aplicados + botón PDF --}}
            @if($filtrar_generacion && $filtrar_cuatrimestre)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 mb-4 text-sm rounded-2xl bg-gradient-to-r from-sky-50 via-blue-50 to-indigo-50 dark:from-sky-900/40 dark:via-indigo-900/40 dark:to-indigo-900/40 border border-blue-200/60 dark:border-blue-800/70" role="alert">
                    <div class="flex items-start gap-2 text-gray-800 dark:text-gray-100">
                        <div class="mt-0.5">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-white dark:bg-blue-500 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                     viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12l4 4L19 6"
                                          stroke="currentColor" stroke-width="1.7"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                        <div>
                            <p class="font-semibold">Filtros aplicados:</p>
                            <p class="mt-1 text-xs sm:text-sm">
                                @if($filtrar_generacion)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/80 dark:bg-gray-900/80 border border-blue-200/80 dark:border-blue-700/80 text-[11px] font-medium mr-1">
                                        Generación:
                                        <span class="ml-1 text-blue-700 dark:text-blue-200">
                                            {{ $generacion_filtrada->generacion->generacion }}
                                        </span>
                                    </span>
                                @endif
                                @if($filtrar_cuatrimestre)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-white/80 dark:bg-gray-900/80 border border-indigo-200/80 dark:border-indigo-700/80 text-[11px] font-medium">
                                        Cuatrimestre:
                                        <span class="ml-1 text-indigo-700 dark:text-indigo-200">
                                            {{ $filtrar_cuatrimestre }}
                                        </span>
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <form method="GET" action="{{ route('admin.pdf.horario-semiescolarizada') }}" target="_blank" class="inline-flex">
                            <input type="hidden" name="licenciatura_id" value="{{ $licenciatura->id }}">
                            <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                            <input type="hidden" name="filtrar_generacion" value="{{ $filtrar_generacion }}">
                            <input type="hidden" name="filtrar_cuatrimestre" value="{{ $filtrar_cuatrimestre }}">

                            <button type="submit"
                                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-full text-xs sm:text-sm font-semibold shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500">
                                <flux:icon.file-text class="h-4 w-4"/>
                                <span>Horario PDF</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Loader horario --}}
            <div wire:loading.delay
                 wire:target="filtrar_generacion, filtrar_cuatrimestre"
                 class="flex flex-col items-center justify-center w-full py-10">
                <svg class="animate-spin h-12 w-12 text-blue-500 mx-auto"
                     xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="mt-4 text-sm sm:text-base text-gray-700 dark:text-gray-200 text-center">
                    Cargando horario…
                </span>
            </div>

            {{-- TABLA HORARIO --}}
            <div wire:loading.remove
                 wire:target="filtrar_generacion, filtrar_cuatrimestre">
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/90 shadow-sm overflow-hidden">
                    <table class="min-w-full border-collapse table-striped">
                        <thead class="bg-gray-100 dark:bg-gray-800/90">
                            <tr>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                    Hora
                                </th>
                                @foreach ($dias as $dia)
                                    <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                                        {{ $dia->dia }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($horas as $hora)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/70 transition-colors">
                                    <td class="px-4 py-2 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/80">
                                        {{ $hora }}
                                    </td>
                                    @foreach ($dias as $dia)
                                        <td class="px-4 py-2 align-top text-xs sm:text-sm text-gray-700 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">
                                            <select
                                                wire:key="select-{{ $dia->id }}-{{ $hora }}"
                                                wire:model="horario.{{ $dia->id }}.{{ $hora }}"
                                                wire:change="actualizarHorario('{{ $dia->id }}', '{{ $hora }}', $event.target.value)"
                                                class="w-full px-2 py-1.5 rounded-md border border-gray-300/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 dark:text-white dark:border-gray-600 text-xs sm:text-sm shadow-sm"
                                            >
                                                <option value="0" {{ empty($horario[$dia->id][$hora]) || $horario[$dia->id][$hora] == 0 ? 'selected' : '' }}>
                                                    --Selecciona una opción--
                                                </option>
                                                @foreach ($materias as $mat)
                                                    <option value="{{ (string)$mat->id }}" {{ (isset($horario[$dia->id][$hora]) && $horario[$dia->id][$hora] == (string)$mat->id) ? 'selected' : '' }}>
                                                        {{ $mat->materia->nombre }} ({{ $mat->materia->clave }})
                                                    </option>
                                                @endforeach
                                            </select>

                                            {{-- Profesor --}}
                                            @if(isset($horario[$dia->id][$hora]) && $horario[$dia->id][$hora] && $horario[$dia->id][$hora] != 0)
                                                @php
                                                    $materiaSeleccionada = $materias->firstWhere('id', $horario[$dia->id][$hora]);
                                                    $profesor = $materiaSeleccionada && isset($materiaSeleccionada->profesor) ? $materiaSeleccionada->profesor : null;
                                                    $profesorColor = $profesor->color ?? '#f3f4f6';
                                                    $isLight = isColorLight($profesorColor);
                                                    $textColor = $isLight ? '#222222' : '#ffffff';
                                                @endphp
                                                @if($profesor)
                                                    <div class="mt-1 text-[11px] sm:text-xs font-medium"
                                                         style="background-color: {{ $profesorColor }}; color: {{ $textColor }}; padding: 0.25rem 0.35rem; border-radius: 0.5rem;">
                                                        Profesor:
                                                        {{ $profesor->nombre ?? 'Sin asignar' }}
                                                        {{ $profesor->apellido_paterno ?? '' }}
                                                        {{ $profesor->apellido_materno ?? '' }}
                                                    </div>
                                                @else
                                                    <div class="mt-1 text-[11px] text-gray-400 italic">
                                                        Sin profesor asignado
                                                    </div>
                                                @endif
                                            @endif

                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- MATERIAS DEL PROFESOR Y HORAS TOTALES --}}
    <div class="mt-4 rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white/90 dark:bg-gray-900/95 shadow-sm p-4 sm:p-5">
        <div class="flex items-center justify-between gap-2 mb-4">
            <h4 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white">
                Materias del Profesor y Horas Totales
            </h4>
            <span class="text-[11px] sm:text-xs text-gray-500 dark:text-gray-400">
                Resumen automático según el horario asignado.
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm bg-white dark:bg-gray-800 table-striped overflow-hidden">
                <thead class="bg-gray-100 dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                            #
                        </th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                            Profesor
                        </th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                            Materias
                        </th>
                        <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                            Total de Horas
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @php
                        $profesoresMaterias = [];
                        $profesoresMateriasEnHorario = [];
                        $profesoresHorasEnHorario = [];

                        // Recorre el horario para contar materias y horas por profesor en el horario
                        foreach ($horario as $diaId => $horasDia) {
                            foreach ($horasDia as $hora => $materiaId) {
                                if ($materiaId && $materiaId != 0) {
                                    $materia = $materias->firstWhere('id', $materiaId);
                                    if ($materia && isset($materia->profesor) && $materia->profesor) {
                                        $profId = $materia->profesor->id;
                                        if (!isset($profesoresMateriasEnHorario[$profId])) {
                                            $profesoresMateriasEnHorario[$profId] = [];
                                        }
                                        // Solo cuenta una vez cada materia por profesor (materias distintas)
                                        $profesoresMateriasEnHorario[$profId][$materiaId] = $materia;

                                        // Cuenta el total de horas (todas las repeticiones)
                                        if (!isset($profesoresHorasEnHorario[$profId])) {
                                            $profesoresHorasEnHorario[$profId] = 0;
                                        }
                                        $profesoresHorasEnHorario[$profId]++;
                                    }
                                }
                            }
                        }

                        // Agrupa materias por profesor (todas las materias asignadas)
                        foreach ($materias as $materia) {
                            if (isset($materia->profesor) && $materia->profesor) {
                                $profId = $materia->profesor->id;
                                if (!isset($profesoresMaterias[$profId])) {
                                    $profesoresMaterias[$profId] = [
                                        'profesor' => $materia->profesor,
                                        'materias' => [],
                                    ];
                                }
                                $profesoresMaterias[$profId]['materias'][] = $materia;
                            }
                        }
                    @endphp

                    @foreach ($profesoresMaterias as $profesorData)
                        @php
                            $profesor = $profesorData['profesor'];
                            $materiasDelProfesor = $profesorData['materias'];
                            $profesorColor = $profesor->color ?? '#f3f4f6';
                            $isLight = isColorLight($profesorColor);
                            $textColor = $isLight ? '#222222' : '#ffffff';
                            $materiasEnHorario = isset($profesoresMateriasEnHorario[$profesor->id]) ? $profesoresMateriasEnHorario[$profesor->id] : [];
                        @endphp
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/80 transition-colors">
                            <td class="px-4 py-2 text-xs sm:text-sm text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 text-center">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 text-xs sm:text-sm border-b border-gray-200 dark:border-gray-700 text-center">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-medium shadow-sm"
                                      style="background-color: {{ $profesorColor }}; color: {{ $textColor }};">
                                    {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs sm:text-sm text-center border-b border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                <ul class="inline-flex flex-col items-start gap-1 text-left">
                                    @foreach ($materiasDelProfesor as $mat)
                                        <li>
                                            {{ $mat->materia->nombre }}
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                                ({{ $mat->materia->clave }})
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-4 py-2 text-xs sm:text-sm text-center border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                                <span class="font-semibold">
                                    {{ $profesoresHorasEnHorario[$profesor->id] ?? 0 }}
                                </span>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Total de horas
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @php
                        $totalHoras = array_sum($profesoresHorasEnHorario);
                    @endphp
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-center text-xs sm:text-sm font-semibold text-blue-700 dark:text-blue-300 border-t border-gray-200 dark:border-gray-700 bg-blue-50/70 dark:bg-blue-900/40">
                            Total global de horas: {{ $totalHoras }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
