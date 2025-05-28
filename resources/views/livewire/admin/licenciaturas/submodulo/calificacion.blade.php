<div>

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
                <div class="overflow-x-auto rounded-lg shadow-md">
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
                                            <span>Calificaciones PDF</span>
                                            </div>
                                    </button>
                                </form>


                                </div>


                            </div>

                            <div class=" p-4 mb-4 text-sm text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg" role="alert">

                             <h2 class="text-1xl py-2"><strong>Periodo Cuatrimestral</strong></h2>
                             <hr class="py-2">


                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                     <x-input label="Ciclo Escolar" value="{{ $periodo->ciclo_escolar }}" variant="filled" readonly />
                                     <x-input label="Periodo Escolar" value="{{ $periodo->mes->meses }}" variant="filled" readonly />
                                    <x-input label="Inicio de Periodo" value="{{ \Carbon\Carbon::parse($periodo->inicio_periodo)->format('d/m/Y') }}" variant="filled" readonly />
                                    @if($periodo->termino_periodo)
                                        <x-input label="Término de Periodo" value="{{ \Carbon\Carbon::parse($periodo->termino_periodo)->format('d/m/Y') }}" variant="filled" readonly />
                                    @else
                                        <x-input label="Término de Periodo" value="No asignado" variant="filled" readonly />
                                    @endif
                                </div>
                           </div>



                                 <h3 class="mt-5 px-2">Buscar Estudiante:</h3>
                        <flux:input type="text" wire:model.live="search" placeholder="Buscar Estudiante (Nombre, Apellido Paterno, Apellido Materno, CURP)" class="p-2 mb-4 w-full" />


                        @endif



                   <div class="min-w-full divide-y divide-gray-200 bg-white dark:bg-gray-800">
                    <form wire:submit.prevent="guardarCalificaciones">
                    <table class="min-w-full divide-y divide-gray-200 bg-white dark:bg-gray-800 border">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Alumno</th>
                                @foreach ($materias as $materia)
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        {{ $materia->materia->nombre }}
                                        @if($materia->profesor)
                                            <div class="text-xs text-gray-500 font-normal mt-1">
                                                <span class="italic">({{ strtoupper($materia->profesor->nombre . ' ' . $materia->profesor->apellido_paterno . ' ' . $materia->profesor->apellido_materno) }})</span>
                                            </div>
                                        @else
                                            <div class="text-xs text-red-500 font-normal mt-1 italic">Sin profesor</div>
                                        @endif
                                    </th>
                                @endforeach
                                 <th class="px-4 py-3">Promedio</th>
                            </tr>
                        </thead>

                        <tbody>

                              @php
                            $global_sum = 0;
                            $global_count = 0;
                            $calificaciones_introducidas = 0;
                            $calificaciones_total = count($alumnos) * count($materias);
                        @endphp
                  @foreach($alumnos as $index => $alumno)
                                    @php
                                        $sum = 0;
                                        $count = 0;
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 border">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 border">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                                        @foreach ($materias as $colIndex => $materia)
                                            @php
                                                $valor = $calificaciones[$alumno->id][$materia->id] ?? '';
                                                $es_valido = (is_numeric($valor) && $valor !== '') || (strtoupper(trim($valor)) === 'NP');
                                                if($es_valido) {
                                                    $calificaciones_introducidas++;
                                                }
                                                if(is_numeric($valor) && $valor !== '') {
                                                    $sum += $valor;
                                                    $count++;
                                                    $global_sum += $valor;
                                                    $global_count++;
                                                }
                                            @endphp
                                            <td class="px-4 py-3 border">
                                                <input
                                                id="cell-{{ $index }}-{{ $colIndex }}"
                                                    type="text"
                                                    min="0"
                                                    max="100"
                                                    class="border rounded px-2 py-2 text-center w-full"
                                                    wire:model="calificaciones.{{ $alumno->id }}.{{ $materia->id }}"
                                                    placeholder="Calificación"
                                                    value="{{ $valor }}"
                                                >
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 border font-bold bg-gray-50">
                                            @if($count)
                                                {{ number_format($sum / $count, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach

                </tbody>
                   @if($filtrar_generacion && $filtrar_cuatrimestre)
                <tfoot>
                        <tr>
                            <td colspan="{{ 2 + count($materias) }}" class="px-4 py-3 text-right font-bold bg-gray-100">
                                Promedio global:
                            </td>
                            <td class="px-4 py-3 font-bold bg-gray-200">
                                @if($global_count)
                                    {{ number_format($global_sum / $global_count, 2) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                    </table>


                         @if($filtrar_generacion && $filtrar_cuatrimestre)
                    @php
                        $porcentaje = $calificaciones_total > 0 ? ($calificaciones_introducidas / $calificaciones_total) * 100 : 0;

                        // Color según avance
                        if ($porcentaje == 100) {
                            $barColor = 'bg-green-500';
                            $textColor = 'text-green-700';
                        } elseif ($porcentaje >= 60) {
                            $barColor = 'bg-yellow-400';
                            $textColor = 'text-yellow-700';
                        } elseif ($porcentaje > 0) {
                            $barColor = 'bg-red-400';
                            $textColor = 'text-red-700';
                        } else {
                            $barColor = 'bg-gray-300';
                            $textColor = 'text-gray-500';
                        }
                    @endphp

                    <div class="my-4 px-3">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-semibold {{ $textColor }}">
                                Calificaciones introducidas: {{ $calificaciones_introducidas }} de {{ $calificaciones_total }}
                                ({{ number_format($porcentaje, 1) }}%)
                            </span>
                            @if($porcentaje == 100)
                                <span class="ml-3 px-2 py-1 rounded text-xs bg-green-100 {{ $textColor }}">¡Completado!</span>
                            @endif
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div class="{{ $barColor }} h-4 rounded-full transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>



                    <div class="mt-4 flex justify-end p-3">
                     <x-button variant="primary" type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Guardar calificaciones
                        </x-button>
                    </div>
                    @endif





                </form>


                </div>
                </div>

        </div>
</div>
</div>

