{{-- resources/views/livewire/admin/horario-general/horario-general-escolarizada.blade.php --}}
<div x-data="{ open:false }">
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

    <!-- Encabezado -->
    <div class="mb-4">
        <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
            Horario Escolarizada
        </h2>
        <p class="text-sm text-neutral-500 dark:text-neutral-400">
            Configura filtros, visualiza y exporta el horario.
        </p>
    </div>

    <!-- Card: Filtros -->
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm p-4 md:p-5 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Licenciatura -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Licenciatura</label>
                <flux:select wire:model.live="licenciatura_id" class="w-full">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($licenciaturas as $lic)
                        <flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Generación -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Generación</label>
                <flux:select wire:model.live="filtrar_generacion" class="w-full">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($generaciones as $generacion)
                        <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Cuatrimestre -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-200 mb-1">Cuatrimestre</label>
                <flux:select wire:model.live="filtrar_cuatrimestre" class="w-full">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($cuatrimestres as $cuatrimestreId)
                        <flux:select.option value="{{ $cuatrimestreId }}">{{ $cuatrimestreId }}°</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <!-- Loader global de filtros -->
        <div wire:loading.flex class="justify-center items-center py-8">
            <svg class="animate-spin h-8 w-8 text-indigo-600 dark:text-indigo-400 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            <span class="text-sm text-neutral-600 dark:text-neutral-300">Cargando…</span>
        </div>

        <!-- Buscador -->
        <div class="mt-4">
            <flux:input
                label="Buscar por profesor o materia"
                wire:model.live="busqueda"
                placeholder="Ej. Matemáticas o Juan Pérez"
                class="w-full"
            />
        </div>

        <!-- Botón PDF + Modal (con Alpine; sin :disabled de Blade) -->
        <div
            x-data="{
                openPdf:false,
                pdfUrl:'',
                base:@js(route('admin.pdf.horario-escolarizada')),
                // Estados sincronizados con Livewire
                licenciatura_id:@entangle('licenciatura_id').live,
                filtrar_generacion:@entangle('filtrar_generacion').live,
                filtrar_cuatrimestre:@entangle('filtrar_cuatrimestre').live,
                modalidad_id:@js($modalidadId ?? null),
                get listo(){ return !!(this.licenciatura_id && this.filtrar_generacion && this.filtrar_cuatrimestre); },
                abrir(){
                    if(!this.listo) return;
                    const params=new URLSearchParams({
                        licenciatura_id:this.licenciatura_id,
                        filtrar_generacion:this.filtrar_generacion,
                        filtrar_cuatrimestre:this.filtrar_cuatrimestre,
                        modalidad_id:this.modalidad_id ?? ''
                    });
                    this.pdfUrl=`${this.base}?${params.toString()}`;
                    this.openPdf=true;
                }
            }"
            x-effect="document.body.classList.toggle('overflow-hidden', openPdf)"
            class="mt-4"
        >
            <x-button
                x-on:click="abrir()"
                x-bind:disabled="!listo"
                variant="primary"
                type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg"
            >
                <flux:icon.download />
                <span>Horario PDF</span>
            </x-button>

            <!-- Modal PDF -->
            <div
                x-cloak
                x-show="openPdf"
                x-transition.opacity
                @click.self="openPdf=false"
                @keydown.escape.window="openPdf=false"
                class="fixed inset-0 z-[120] grid place-items-center bg-black/40 dark:bg-black/50 backdrop-blur-sm p-4"
            >
                <div class="relative w-full max-w-7xl rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-2xl">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200 dark:border-neutral-800">
                        <h3 class="text-sm md:text-base font-semibold text-neutral-900 dark:text-neutral-100">Vista previa · PDF del horario</h3>
                        <button @click="openPdf=false"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.3 5.71 12 12l6.3 6.29-1.41 1.42L10.59 13.4 4.29 19.7 2.88 18.3 9.17 12 2.88 5.71 4.29 4.3l6.3 6.29 6.29-6.3z"/></svg>
                        </button>
                    </div>
                    <div class="p-3">
                        <iframe :src="pdfUrl" class="w-full h-[75vh] rounded-lg border border-neutral-200 dark:border-neutral-800"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla editable -->
    <div
        wire:loading.remove
        wire:target="licenciatura_id, filtrar_generacion, filtrar_cuatrimestre, busqueda, actualizarHorario, cargarHorario"
    >
        @if($horario && count($horario))
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-neutral-50 dark:bg-neutral-700 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Hora</th>
                                @foreach ($dias as $dia)
                                    <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">{{ $dia->dia }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($horas as $hora)
                                <tr class="odd:bg-neutral-50/60 dark:odd:bg-neutral-800/60">
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-neutral-900 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">
                                        {{ $hora }}
                                    </td>
                                    @foreach ($dias as $dia)
                                        <td class="px-4 py-3 align-top border-b border-neutral-200 dark:border-neutral-700">
                                            <select
                                                wire:key="select-{{ $dia->id }}-{{ $hora }}"
                                                wire:model="horario.{{ $dia->id }}.{{ $hora }}"
                                                wire:change="actualizarHorario('{{ $dia->id }}', '{{ $hora }}', $event.target.value)"
                                                class="w-full px-3 py-2 rounded-md border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100
                                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                            >
                                                <option value="0" {{ empty($horario[$dia->id][$hora]) || $horario[$dia->id][$hora] == 0 ? 'selected' : '' }}>--Selecciona una opción--</option>
                                                @foreach ($materias as $mat)
                                                    <option value="{{ (string)$mat->id }}" {{ (isset($horario[$dia->id][$hora]) && $horario[$dia->id][$hora] == (string)$mat->id) ? 'selected' : '' }}>
                                                        {{ $mat->materia->nombre }} ({{ $mat->materia->clave }})
                                                    </option>
                                                @endforeach
                                            </select>

                                            {{-- Profesor chip --}}
                                            @if(isset($horario[$dia->id][$hora]) && $horario[$dia->id][$hora] && $horario[$dia->id][$hora] != 0)
                                                @php
                                                    $materiaSeleccionada = $materias->firstWhere('id', $horario[$dia->id][$hora]);
                                                    $profesor = $materiaSeleccionada->profesor ?? null;
                                                    $profesorColor = $profesor->color ?? '#f3f4f6';
                                                    $textoClaro = !esColorDark($profesorColor);
                                                    $textColor = $textoClaro ? '#222222' : '#ffffff';
                                                @endphp
                                                @if($profesor)
                                                    <div class="mt-2 text-xs rounded-md px-2 py-1"
                                                         style="background-color: {{ $profesorColor }}; color: {{ $textColor }};">
                                                        Profesor: {{ $profesor->nombre ?? 'Sin asignar' }} {{ $profesor->apellido_paterno ?? '' }} {{ $profesor->apellido_materno ?? '' }}
                                                    </div>
                                                @else
                                                    <div class="mt-1 text-xs text-neutral-400 italic">Sin profesor asignado</div>
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
        @else
            <div class="rounded-xl border border-dashed border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm p-8 text-center text-neutral-600 dark:text-neutral-300">
                Selecciona <strong>licenciatura</strong>, <strong>generación</strong> y <strong>cuatrimestre</strong> para ver/editar el horario.
            </div>
        @endif
    </div>

    <!-- Materias del Profesor y Horas Totales -->
    <div class="mt-8">
        <h4 class="text-lg md:text-xl font-semibold text-neutral-900 dark:text-white mb-4">
            Materias del Profesor y Horas Totales
        </h4>

        <!-- Loader mientras recalcula -->
        <div
            wire:loading.delay
            wire:target="actualizarHorario, cargarHorario, licenciatura_id, filtrar_generacion, filtrar_cuatrimestre, busqueda"
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

        <!-- Contenido real -->
        <div
            wire:loading.remove
            wire:target="actualizarHorario, cargarHorario, licenciatura_id, filtrar_generacion, filtrar_cuatrimestre, busqueda"
            class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden"
        >
            @php
                // === Mapas y agrupaciones ===
                $diasMap = collect($dias)->keyBy('id');

                $profesoresMaterias = [];
                foreach ($materias as $m) {
                    if (($m->profesor ?? null) && ($m->materia ?? null)) {
                        $pid = $m->profesor->id;
                        $profesoresMaterias[$pid] ??= ['profesor' => $m->profesor, 'materias' => []];
                        $profesoresMaterias[$pid]['materias'][] = $m;
                    }
                }

                $profesoresHorasEnHorario = [];
                $desgloseMateria = [];

                foreach ($horario as $diaId => $horasDia) {
                    foreach ($horasDia as $horaTxt => $asignacionId) {
                        if (!empty($asignacionId) && $asignacionId !== "0") {
                            $mat = $materias->firstWhere('id', $asignacionId);
                            if ($mat && ($mat->profesor ?? null)) {
                                $pid = $mat->profesor->id;
                                $profesoresHorasEnHorario[$pid] = ($profesoresHorasEnHorario[$pid] ?? 0) + 1;

                                $desgloseMateria[$mat->id] ??= ['count' => 0, 'slots' => [], 'prof_id' => $pid];
                                $desgloseMateria[$mat->id]['count']++;
                                $desgloseMateria[$mat->id]['slots'][] = [
                                    'dia'  => $diasMap[$diaId]->dia ?? ('Día '.$diaId),
                                    'hora' => $horaTxt,
                                ];
                            }
                        }
                    }
                }

                $totalHoras = array_sum($profesoresHorasEnHorario);

                $ordenHoras = array_values($horas);
                $posHora = array_flip($ordenHoras);
                $formatearSlots = function(array $slots) use ($posHora) {
                    $porDia = [];
                    foreach ($slots as $s) {
                        $porDia[$s['dia']][] = $s['hora'];
                    }
                    $chips = [];
                    foreach ($porDia as $dia => $horasDia) {
                        usort($horasDia, fn($a,$b) => ($posHora[$a] ?? 999) <=> ($posHora[$b] ?? 999));
                        $chips[] = ['dia' => $dia, 'horas' => $horasDia];
                    }
                    return $chips;
                };
            @endphp

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-700">
                        <tr>
                            <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">#</th>
                            <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Profesor</th>
                            <th class="px-4 py-3 font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Materia (horas) y desglose por día</th>
                            <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Total de Horas</th>
                            <th class="px-4 py-3 text-center font-semibold text-neutral-700 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">Horario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach ($profesoresMaterias as $profData)
                            @php
                                $profesor = $profData['profesor'];
                                $materiasDelProfesor = $profData['materias'];
                                $profesorColor = $profesor->color ?? '#f3f4f6';
                                $textColor = esColorDark($profesorColor) ? '#ffffff' : '#222222';
                                $horasProf = $profesoresHorasEnHorario[$profesor->id] ?? 0;
                            @endphp
                            <tr class="odd:bg-neutral-50/60 dark:odd:bg-neutral-800/60">
                                <td class="px-4 py-3 text-center font-medium text-neutral-900 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">{{ $i++ }}</td>
                                <td class="px-4 py-3 text-center border-b border-neutral-200 dark:border-neutral-700">
                                    <span class="inline-block px-2 py-1 rounded-md"
                                          style="background-color: {{ $profesorColor }}; color: {{ $textColor }};">
                                        {{ $profesor->nombre }} {{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-700">
                                    <ul class="space-y-2">
                                        @foreach ($materiasDelProfesor as $m)
                                            @php
                                                $enHorario = isset($desgloseMateria[$m->id]);
                                                $count = $enHorario ? $desgloseMateria[$m->id]['count'] : 0;
                                                $chips = $enHorario ? $formatearSlots($desgloseMateria[$m->id]['slots']) : [];
                                            @endphp
                                            <li class="rounded-lg border border-neutral-200 dark:border-neutral-700 p-2">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="font-medium text-neutral-900 dark:text-neutral-100">
                                                        {{ $m->materia->nombre }}
                                                        <span class="text-xs text-neutral-500">({{ $m->materia->clave }})</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        @if($enHorario)
                                                            <span class="inline-flex items-center text-[10px] px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">en horario</span>
                                                        @else
                                                            <span class="inline-flex items-center text-[10px] px-2 py-0.5 rounded bg-amber-100 text-amber-700">sin horas</span>
                                                        @endif
                                                        <span class="inline-flex items-center text-xs px-2 py-0.5 rounded bg-indigo-100 text-indigo-700">{{ $count }} h</span>
                                                    </div>
                                                </div>

                                                @if($enHorario)
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
                                </td>
                                <td class="px-4 py-3 text-center font-medium text-neutral-900 dark:text-neutral-100 border-b border-neutral-200 dark:border-neutral-700">
                                    {{ $horasProf }}
                                    <div class="text-[11px] text-neutral-500">Total de horas</div>
                                </td>
                                <td class="px-4 py-3 text-center border-b border-neutral-200 dark:border-neutral-700">
                                    <form action="{{ route('admin.pdf.horario-docente-escolarizada') }}" method="GET" target="_blank">
                                        <input type="hidden" name="profesor_id" value="{{ $profesor->id }}">
                                        <input type="hidden" name="modalidad_id" value="1">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg">
                                            <flux:icon.file-text />
                                            Horario
                                        </button>
                                    </form>
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
        </div>
    </div>
</div>
