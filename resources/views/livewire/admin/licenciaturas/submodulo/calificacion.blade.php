<div>
    <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
        <!-- Icono SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
        <span>Filtrar por:</span>
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2 p-2 mb-4">
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
                <flux:select.option value="">--Selecciona un cuatrimestre---</flux:select.option>
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
        <div wire:loading.delay wire:target="filtrar_generacion, filtrar_cuatrimestre" class="flex justify-center">
            <svg class="animate-spin h-20 w-20 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
        </div>

        <div wire:loading.remove wire:target="filtrar_generacion, filtrar_cuatrimestre">
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

                    <div class="p-4 mb-4 text-sm text-gray-800 bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg" role="alert">
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
                    <flux:input type="text" wire:model.live="search" placeholder="Buscar Estudiante (Nombre, Apellido Paterno, Apellido Materno, Matrícula)" class="p-2 mb-4 w-full" />
                @endif

                <div class="min-w-full divide-y divide-gray-200 bg-white dark:bg-gray-800">
                    <div
                        x-data="{ show: false, message: '' }"
                        x-on:alerta.window="show = true; message = $event.detail; setTimeout(() => show = false, 2000)"
                        x-show="show"
                        x-transition
                        class="fixed top-6 right-6 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50"
                    >
                        <span x-text="message"></span>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 bg-white dark:bg-gray-800 border">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Matrícula</th>
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
                                <th class="px-4 py-3"></th>
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
                                    <td class="px-4 py-3 border">{{ $alumno->matricula }}</td>
                                    <td class="px-4 py-3 border">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</td>
                                    @foreach ($materias as $materia)
                                        @php
                                            $valor = $calificaciones[$alumno->id][$materia->id] ?? null;
                                            if (
                                                (is_numeric($valor) && $valor > 0) ||
                                                (strtoupper(trim($valor)) === 'NP')
                                            ) {
                                                $calificaciones_introducidas++;
                                            }
                                            if (is_numeric($valor) && $valor > 0) {
                                                $sum += $valor;
                                                $count++;
                                                $global_sum += $valor;
                                                $global_count++;
                                            }
                                        @endphp

                                                   <td class="px-4 py-3 border relative">
                                                <input
                                                    type="text"
                                                    class="border rounded px-2 py-2 text-center w-full focus:ring focus:ring-blue-300"
                                                   wire:model.blur="calificaciones.{{ $alumno->id }}.{{ $materia->id }}"
                                                    placeholder="Calificación"
                                                />
                                            </td>

                                    @endforeach
                                    <td class="px-4 py-3 border font-bold ">
                                        {{ $count ? number_format($sum / $count, 2) : '-' }}
                                    </td>
                                    <td class="px-4 py-3 border font-bold ">

                                    <form action="{{ route('admin.pdf.documentacion.calificacion_alumno') }}" method="GET" target="_blank" class="mt-4">
                                        <button type="submit" variant="primary" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                                            <div class="flex items-center gap-1">
                                                <flux:icon.file-text/>
                                                <span>PDF</span>
                                            </div>
                                        </button>
                                        <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                                        <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                                        <input type="hidden" name="generacion_id" value="{{ $filtrar_generacion }}">
                                        <input type="hidden" name="cuatrimestre_id" value="{{ $filtrar_cuatrimestre }}">
                                    </form>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if($filtrar_generacion && $filtrar_cuatrimestre)
                        <tfoot>
                            <tr>
                                <td colspan="{{ 3 + count($materias) }}" class="px-4 py-3 text-right font-bold">
                                    Promedio global:
                                </td>
                                <td class="px-4 py-3 font-bold ">
                                    {{ $global_count ? number_format($global_sum / $global_count, 2) : '-' }}
                                </td>
                            </tr>

                        </tfoot>
                        @endif
                    </table>

                        @if($filtrar_generacion && $filtrar_cuatrimestre && count($alumnos) && count($materias))
                        <div class="flex justify-end my-4 items-center">
                            <button
                                wire:click="guardarTodasLasCalificaciones"
                                wire:loading.attr="disabled"
                                class="{{ $todas_calificaciones_guardadas ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' }} text-white px-6 py-2 rounded font-semibold"
                                @if(!$hayCambios) disabled @endif
                            >
                                {{ $todas_calificaciones_guardadas ? 'Actualizar calificaciones' : 'Guardar calificaciones' }}
                            </button>
                            @if(!$hayCambios)
                                <span class="ml-2 text-gray-400 text-sm">No hay cambios por guardar</span>
                            @else
                                <span class="ml-2 text-gray-400 text-sm">Existen cambios por guardar</span>
                            @endif
                        </div>
                    @endif




                    @if($filtrar_generacion && $filtrar_cuatrimestre)
                        @php
                            $porcentaje = $calificaciones_total > 0 ? ($calificaciones_introducidas / $calificaciones_total) * 100 : 0;
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
