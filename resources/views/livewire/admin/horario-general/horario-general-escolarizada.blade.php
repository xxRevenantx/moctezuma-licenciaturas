<div x-data="{ open: false }">

    @php
        // Utilidad para contraste de color (texto legible sobre fondo)
        function esColorDark($hexColor) {
            $hexColor = ltrim($hexColor ?: '#ffffff', '#');
            if (strlen($hexColor) == 3) {
                $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
            }
            $r = hexdec(substr($hexColor, 0, 2));
            $g = hexdec(substr($hexColor, 2, 2));
            $b = hexdec(substr($hexColor, 4, 2));
            $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b);
            return $luminancia < 128; // true = fondo oscuro
        }
    @endphp




    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Horario Escolarizada</h2>



    {{-- Filtros --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- Licenciatura --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Licenciatura</label>
            <flux:select wire:model.live="filtroLicenciatura" class="w-full border-gray-300 rounded shadow-sm">
                <flux:select.option value="">-- Selecciona --</flux:select.option>
                @foreach($licenciaturas as $lic)
                    <flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Generación (se llena en cascada según licenciatura) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Generación</label>
            <flux:select wire:model.live="filtroGeneracion" class="w-full border-gray-300 rounded shadow-sm">
                <flux:select.option value="">-- Selecciona --</flux:select.option>
                @foreach($generaciones as $generacion)
                    <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Cuatrimestre (se llena según licenciatura + generación) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Cuatrimestre</label>
            <flux:select wire:model.live="filtroCuatrimestre" class="w-full border-gray-300 rounded shadow-sm">
                <flux:select.option value="">-- Selecciona --</flux:select.option>
                @foreach($cuatrimestres as $cuatrimestreId)
                    <flux:select.option value="{{ $cuatrimestreId }}">{{ $cuatrimestreId }}°</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    {{-- Loader global --}}
    <div wire:loading.flex class="justify-center items-center py-10">
        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
    </div>

            {{-- Buscador --}}
            <flux:input
                label="Buscar por profesor o materia"
                wire:model.live="busqueda"
                placeholder="Ej. Matemáticas o Juan Pérez"
                class="w-full my-3"
            />

    {{-- Botón PDF + modal (blur + bloqueo scroll) --}}

            <div
                x-data="{
                    open: false,
                    pdfUrl: '',
                    base: @js(route('admin.pdf.horario-escolarizada')),
                    // sincroniza con Livewire
                    filtroLicenciatura: @entangle('filtroLicenciatura').live,
                    filtroGeneracion:   @entangle('filtroGeneracion').live,
                    filtroCuatrimestre: @entangle('filtroCuatrimestre').live,
                    modalidad_id: @js($modalidadId),
                    abrir() {
                        if (!this.filtroLicenciatura || !this.filtroGeneracion || !this.filtroCuatrimestre) return;
                        const params = new URLSearchParams({
                            licenciatura_id: this.filtroLicenciatura,
                            generacion_id:   this.filtroGeneracion,
                            cuatrimestre_id: this.filtroCuatrimestre,
                            modalidad_id:    this.modalidad_id,
                        });
                        this.pdfUrl = `${this.base}?${params.toString()}`;
                        this.open = true;
                    }
                }"
                x-effect="document.body.classList.toggle('overflow-hidden', open)"
                x-init="document.addEventListener('keydown', e => { if (open && e.key === 'Escape') open = false })"
                class="mb-3"
            >
                <x-button
                    x-on:click="abrir()"
                    x-bind:disabled="!(filtroLicenciatura && filtroGeneracion && filtroCuatrimestre)"
                    variant="primary"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                >
                    <div class="flex items-center gap-1">
                        <flux:icon.download />
                        <span>Horario PDF</span>
                    </div>
                </x-button>

                <div
                    x-show="open"
                    x-transition.opacity
                    x-cloak
                    class="fixed inset-0 z-50 bg-black/40 backdrop-blur-md flex justify-center items-center"
                    style="display:none"
                    @click.self="open=false"
                    @keydown.escape.window="open=false"
                    tabindex="0"
                >
                    <div class="bg-white rounded-lg p-4 w-full max-w-7xl shadow-lg relative">
                        <iframe :src="pdfUrl" class="w-full h-[80vh] rounded"></iframe>
                        <button @click="open=false" class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">Cerrar</button>
                    </div>
                </div>
            </div>



    {{-- Tabla editable (se muestra cuando hay filtros completos) --}}
    <div
        wire:loading.remove
        wire:target="filtroLicenciatura, filtroGeneracion, filtroCuatrimestre, busqueda"
    >
        @if($horario && count($horario))
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
                                        class="w-full px-2 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 dark:text-white dark:border-gray-600 transition"
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
                                            $textoClaro = !esColorDark($profesorColor);
                                            $textColor = $textoClaro ? '#222222' : '#ffffff';
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
        @else
            <div class="text-center text-gray-500 mt-8">Selecciona licenciatura, generación y cuatrimestre para ver/editar el horario.</div>
        @endif



        {{-- MATERIAS DEL PROFESOR Y HORAS TOTALES --}}
<div class="mt-8">
    <h4 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">
        Materias del Profesor y Horas Totales
    </h4>

    {{-- Loader mientras recalcula --}}
    <div
        wire:loading.delay
        wire:target="actualizarHorario, filtroGeneracion, filtroCuatrimestre, filtroLicenciatura, busqueda"
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

    {{-- Contenido real --}}
    <div
        wire:loading.remove
        wire:target="actualizarHorario, filtroGeneracion, filtroCuatrimestre, filtroLicenciatura, busqueda"
        class="overflow-x-auto"
    >
        @php
            // Agrupar materias por profesor (catálogo completo de materias disponibles)
            $profesoresMaterias = [];                // [prof_id => ['profesor' => Obj, 'materias' => [AsignacionMateria,...]]]
            foreach ($materias as $m) {
                if (isset($m->profesor) && $m->profesor) {
                    $pid = $m->profesor->id;
                    if (!isset($profesoresMaterias[$pid])) {
                        $profesoresMaterias[$pid] = ['profesor' => $m->profesor, 'materias' => []];
                    }
                    $profesoresMaterias[$pid]['materias'][] = $m;
                }
            }

            // Materias y horas realmente colocadas en la matriz de horario
            $profesoresMateriasEnHorario = [];       // [prof_id => [asignacion_materia_id => AsignacionMateria]]
            $profesoresHorasEnHorario    = [];       // [prof_id => slots_totales]

            foreach ($horario as $diaId => $horasDia) {
                foreach ($horasDia as $horaTxt => $asignacionId) {
                    if (!empty($asignacionId) && $asignacionId !== "0") {
                        $mat = $materias->firstWhere('id', $asignacionId);
                        if ($mat && isset($mat->profesor) && $mat->profesor) {
                            $pid = $mat->profesor->id;
                            $profesoresMateriasEnHorario[$pid][$mat->id] = $mat;
                            $profesoresHorasEnHorario[$pid] = ($profesoresHorasEnHorario[$pid] ?? 0) + 1;
                        }
                    }
                }
            }

            $totalHoras = array_sum($profesoresHorasEnHorario);
        @endphp

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
                @php $i = 1; @endphp
                @foreach ($profesoresMaterias as $profData)
                    @php
                        $profesor = $profData['profesor'];
                        $materiasDelProfesor = $profData['materias'];
                        $profesorColor = $profesor->color ?? '#f3f4f6';
                        $textColor = esColorDark($profesorColor) ? '#ffffff' : '#222222';
                        $horasProf = $profesoresHorasEnHorario[$profesor->id] ?? 0;
                    @endphp
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100 border-b text-center">{{ $i++ }}</td>
                        <td class="px-4 py-2 text-sm border-b text-center">
                            <span class="inline-block px-2 py-1 rounded"
                                  style="background-color: {{ $profesorColor }}; color: {{ $textColor }};">
                                {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm text-center border-b text-gray-900 dark:text-gray-100">
                            <ul class="list-disc pl-4 text-left inline-block">
                                @foreach ($materiasDelProfesor as $m)
                                    <li>
                                        {{ $m->materia->nombre }}
                                        <span class="text-xs text-gray-500">({{ $m->materia->clave }})</span>
                                        @if(isset($profesoresMateriasEnHorario[$profesor->id][$m->id]))
                                            <span class="ml-2 inline-block text-[10px] px-2 py-0.5 rounded bg-green-100 text-green-800">
                                                en horario
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="px-4 py-2 text-sm text-center border-b text-gray-700 dark:text-gray-200">
                            {{ $horasProf }}
                            <div class="text-xs text-gray-500">Total de horas</div>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="4" class="px-4 py-2 text-center text-sm font-semibold text-blue-700 dark:text-blue-300 border-b">
                        Total global de horas: {{ $totalHoras }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

    </div>
</div>

