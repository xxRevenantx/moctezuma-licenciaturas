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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2  p-2 mb-4">

      <flux:field>
        <flux:label>Generación</flux:label>
        <flux:select wire:model.live="filtrar_generacion">
            <flux:select.option value="">--Selecciona una generación---</flux:select.option>
            @foreach($generaciones as $generacion)
                <flux:select.option value="{{ $generacion->generacion_id }}">{{ $generacion->generacion->generacion }}</flux:select.option>
            @endforeach
        </flux:select>
      </flux:field>


      <flux:field>
        <flux:label>Cuatrimestre</flux:label>
        <flux:select wire:model.live="filtrar_cuatrimestre">
            <flux:select.option value="">--Selecciona una cuatrimestre---</flux:select.option>
            @foreach($cuatrimestres as $cuatrimestre)
                <flux:select.option value="{{ $cuatrimestre->cuatrimestre_id }}">{{ $cuatrimestre->cuatrimestre->nombre_cuatrimestre}}</flux:select.option>
            @endforeach
        </flux:select>
      </flux:field>



        <flux:field>
              <flux:label>Filtros</flux:label>
                    <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center ">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>
        </flux:field>
    </div>
    <div>

        <div class="overflow-x-auto">
    @if($filtrar_generacion && $filtrar_cuatrimestre)
            <div class="flex justify-between items-center p-4 mb-4 text-sm text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg" role="alert">
              <div>
                <strong>Filtros aplicados:</strong>
                @if($filtrar_generacion)
                    Generación: {{ $generacion_filtrada->generacion->generacion }} |
                @endif
                @if($filtrar_cuatrimestre)
                    Cuatrimestre: {{ $filtrar_cuatrimestre }}
                @endif
            </div>
                 <div>

                    <form method="GET" action="{{ route('admin.pdf.horario-escolarizada') }}" target="_blank">

                    <input type="hidden" name="licenciatura_id" value="{{ $licenciatura->id }}">
                    <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                    <input type="hidden" name="filtrar_generacion" value="{{ $filtrar_generacion }}">
                    <input type="hidden" name="filtrar_cuatrimestre" value="{{ $filtrar_cuatrimestre }}">


                    <button type="submit" variant="primary" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                         <div class="flex items-center gap-1">
                            <flux:icon.file-text/>
                            <span>Horario PDF</span>
                            </div>
                    </button>
                </form>


                </div>


            </div>


        @endif

             <div wire:loading.delay
                wire:target="filtrar_generacion, filtrar_cuatrimestre"
                       class="flex justify-center">
                    <svg class="animate-spin h-20 w-20 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                 </div>

                 <div wire:loading.remove
                 wire:target="filtrar_generacion, filtrar_cuatrimestre">
                    <table class="min-w-full border-collapse border border-gray-200 table-striped">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Hora</th>
                            @foreach ($dias as $dia)
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">{{ $dia->dia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($horas as $hora)
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-b">{{ $hora }}</td>
                                @foreach ($dias as $dia)
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200 border-b">

                                <select
                                    wire:key="select-{{ $dia->id }}-{{ $hora }}"
                                    wire:model="horario.{{ $dia->id }}.{{ $hora }}"
                                    wire:change="actualizarHorario('{{ $dia->id }}', '{{ $hora }}', $event.target.value)"
                                    class="w-full px-2  py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 dark:text-white dark:border-gray-600 transition duration-150 ease-in-out"
                                >

                                    <option value="0" {{ empty($horario[$dia->id][$hora]) || $horario[$dia->id][$hora] == 0 ? 'selected' : '' }}>--Selecciona una opción--</option>
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
                                            <div class="mt-1 text-xs" style="background-color: {{ $profesorColor }}; color: {{ $textColor }}; padding: 0.25rem; border-radius: 0.375rem;">
                                                Profesor: {{ $profesor->nombre ?? 'Sin asignar' }} {{ $profesor->apellido_paterno ?? '' }} {{ $profesor->apellido_materno ?? '' }}
                                            </div>
                                        @else
                                            <div class="mt-1 text-xs text-gray-400 italic">Sin profesor asignado</div>
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

       {{-- MATERIAS DEL PROFESOR Y HORAS TOTALES --}}
        <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Materias del Profesor y Horas Totales</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200 rounded-lg shadow-sm bg-white dark:bg-gray-800 table-striped">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">#</th>
                            <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Profesor</th>
                            <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Materias</th>
                            <th class="px-4 py-2 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Total de Horas</th>
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
                           <tr>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 border-b text-center">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-2 text-sm border-b text-center">
                                <span class="inline-block px-2 py-1 rounded" style="background-color: {{ $profesorColor }}; color: {{ $textColor }};">
                                    {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-center border-b text-gray-900 dark:text-gray-100">
                                <ul class="list-disc pl-4">
                                    @foreach ($materiasDelProfesor as $mat)
                                        <li>
                                            {{ $mat->materia->nombre }} <span class="text-xs text-gray-500">({{ $mat->materia->clave }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-4 py-2 text-sm text-center border-b text-gray-700 dark:text-gray-200">
                                {{ $profesoresHorasEnHorario[$profesor->id] ?? 0 }}
                                <div class="text-xs text-gray-500">Total de horas</div>
                            </td>

                        </tr>

                        @endforeach
                        @php
                            $totalHoras = array_sum($profesoresHorasEnHorario);
                        @endphp
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-center text-sm font-semibold text-blue-700 dark:text-blue-300 border-b">
                                Total global de horas: {{ $totalHoras }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


</div>

</div>
