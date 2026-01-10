<div x-data="{
    enviarCalificacion(alumno, cuatrimestre, generacion, modalidad) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `La calificación del alumno se enviará a su correo asignado.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, enviar'
            }).then((r) => {
                if (r.isConfirmed) {
                    @this.call('enviarCalificacion', alumno, cuatrimestre, generacion, modalidad);
                }
            });
        },
        enviarCalificacionesMasivasAlpine() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¿Deseas enviar las calificaciones a todos los alumnos?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, enviar'
            }).then((r) => {
                if (r.isConfirmed) {
                    @this.call('enviarCalificacionesMasivas');
                }
            });
        }
}" class="space-y-6">
    {{-- ======= TOOLBAR / FILTROS ======= --}}
    <div
        class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white/80 dark:bg-neutral-900/80 shadow-sm overflow-hidden">
        <div class="h-1.5 w-full bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>

        <div class="p-4 sm:p-6">
            <h3
                class="flex items-center gap-2 text-xl sm:text-2xl font-extrabold text-neutral-800 dark:text-neutral-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 opacity-80" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span>Filtrar por</span>
            </h3>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">
                <flux:field>
                    <flux:label>Generación</flux:label>
                    <flux:select wire:model.live="filtrar_generacion">
                        <flux:select.option value="">--Selecciona una generación---</flux:select.option>
                        @foreach ($generaciones as $g)
                            <flux:select.option value="{{ $g->generacion_id }}">
                                {{ $g->generacion->generacion }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>

                    {{-- ✅ FIX: NO uses :disabled="!gen" en un componente Blade (te da "Undefined constant gen")
                         Usa x-bind:disabled (Alpine) --}}
                    <flux:select x-data="{ gen: @entangle('filtrar_generacion') }" x-bind:disabled="!gen"
                        wire:model.live="filtrar_cuatrimestre">
                        <flux:select.option value="">--Selecciona un cuatrimestre---</flux:select.option>

                        @foreach ($cuatrimestres as $p)
                            <flux:select.option value="{{ $p->cuatrimestre_id }}">
                                {{ $p->cuatrimestre?->nombre_cuatrimestre ?? 'Cuatrimestre ' . $p->cuatrimestre_id }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field class="lg:col-span-1">
                    <flux:label>&nbsp;</flux:label>
                    <flux:button wire:click="limpiarFiltros" variant="primary"
                        class="w-full justify-center bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                               hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3c2.8 0 5.5.2 8.1.7.6.1.9.6.9 1.1V6a2 2 0 01-.6 1.5l-5.5 5.5a2 2 0 00-.6 1.5V17a2 2 0 01-1.2 1.9L9.8 21v-6.6a2 2 0 00-.6-1.5L3.7 7.4A2 2 0 013 5.8V4.8c0-.5.4-1 .9-1.1C6.5 3.2 9.2 3 12 3z" />
                            </svg>
                            <span>Limpiar filtros</span>
                        </div>
                    </flux:button>
                </flux:field>
            </div>
        </div>
    </div>

    {{-- ======= CONTENEDOR ======= --}}
    <div
        class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-sm overflow-hidden">
        <div class="p-3 sm:p-4 space-y-4">

            @if ($filtrar_generacion && $filtrar_cuatrimestre)

                {{-- ======= PERIODO ======= --}}
                @if ($periodo)
                    @php
                        $inicio = \Carbon\Carbon::parse($periodo->inicio_periodo);
                        $termino = $periodo->termino_periodo ? \Carbon\Carbon::parse($periodo->termino_periodo) : null;
                        $hoy = \Carbon\Carbon::now();

                        if ($termino) {
                            if ($hoy->lt($inicio)) {
                                $estado = [
                                    'Próximo',
                                    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                ];
                            } elseif ($hoy->lte($termino)) {
                                $estado = [
                                    'En curso',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                ];
                            } else {
                                $estado = [
                                    'Finalizado',
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                ];
                            }
                        } else {
                            $estado = $hoy->lt($inicio)
                                ? ['Próximo', 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300']
                                : ['En curso', 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'];
                        }
                    @endphp

                    <div
                        class="p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700
                                bg-gradient-to-br from-white via-neutral-50 to-white
                                dark:from-neutral-900 dark:via-neutral-900 dark:to-neutral-900
                                shadow-md overflow-hidden">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white grid place-items-center shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2
                                        class="text-base sm:text-lg font-extrabold uppercase tracking-wide text-neutral-800 dark:text-neutral-100">
                                        Periodo Cuatrimestral
                                    </h2>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ $periodo->cuatrimestre?->nombre_cuatrimestre }} · {{ $periodo->mes?->meses }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $estado[1] }}">
                                {{ $estado[0] }}
                            </span>
                        </div>

                        <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div
                                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm">
                                <div class="text-xs uppercase text-neutral-500 dark:text-neutral-400">Ciclo escolar
                                </div>
                                <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                    {{ $periodo->ciclo_escolar }}
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm">
                                <div class="text-xs uppercase text-neutral-500 dark:text-neutral-400">Inicio</div>
                                <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                    {{ $inicio->format('d/m/Y') }}
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm">
                                <div class="text-xs uppercase text-neutral-500 dark:text-neutral-400">Término</div>
                                <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                    {{ $termino ? $termino->format('d/m/Y') : 'No asignado' }}
                                </div>
                            </div>

                            <div
                                class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 shadow-sm">
                                <div class="text-xs uppercase text-neutral-500 dark:text-neutral-400">Generación</div>
                                <div class="mt-1 text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                    {{ $generacion_filtrada?->generacion?->generacion ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Buscador --}}
                <div class="px-1 sm:px-2">
                    <h3 class="mb-2 font-semibold">Buscar Estudiante:</h3>
                    <flux:input type="text" wire:model.live.debounce.500ms="search"
                        placeholder="Nombre, apellidos o matrícula" class="w-full" />
                </div>

                {{-- ======= SECCIÓN TABLA (loader SOLO aquí) ======= --}}
                <div class="relative">
                    {{-- ✅ Loader: SOLO cubre esta sección (tabla) --}}
                    {{-- Overlay loader (filtros / carga dataset) --}}
                    <div wire:loading.flex wire:target="filtrar_generacion, filtrar_cuatrimestre, search"
                        class="absolute inset-0 z-20 backdrop-blur-sm bg-white/40 dark:bg-black/30 items-center justify-center p-6">
                        <div class="flex flex-col items-center gap-2 text-neutral-700 dark:text-neutral-200">
                            <svg class="animate-spin h-8 w-8" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            <span class="text-sm">Cargando…</span>
                        </div>
                    </div>

                    {{-- ======= TABLA ======= --}}
                    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                        <table class="min-w-[1100px] w-full bg-white dark:bg-neutral-900 text-sm">
                            <thead class="sticky top-0 z-10">
                                <tr
                                    class="bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-neutral-800 dark:to-neutral-800 border-b border-neutral-200 dark:border-neutral-700">
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-14">
                                        #</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-40">
                                        F/L</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-40">
                                        Matrícula</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-72">
                                        Alumno</th>

                                    @foreach ($materias as $m)
                                        <th
                                            class="px-4 py-3 text-left text-[11px] font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider min-w-[180px] align-bottom">
                                            <div class="flex flex-col">
                                                <span class="leading-4">{{ $m->materia->nombre }}</span>
                                                @if ($m->profesor)
                                                    <span
                                                        class="mt-1 text-[10px] text-neutral-500 font-normal italic leading-3">
                                                        {{ strtoupper($m->profesor->nombre . ' ' . $m->profesor->apellido_paterno . ' ' . $m->profesor->apellido_materno) }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="mt-1 text-[10px] text-red-500 font-normal italic leading-3">Sin
                                                        profesor</span>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach

                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-40">
                                        Promedio</th>
                                    <th class="px-4 py-3 w-40"></th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-800">
                                @foreach ($alumnos as $index => $al)
                                    @php
                                        $sum = 0;
                                        $count = 0;
                                        $tieneNP = false; // ✅ si tiene NP en alguna materia, no mostrar promedio
                                    @endphp

                                    <tr wire:key="row-{{ $al->id }}"
                                        class="odd:bg-white even:bg-neutral-50 dark:odd:bg-neutral-900 dark:even:bg-neutral-800/60 hover:bg-sky-50/60 dark:hover:bg-neutral-800 transition">
                                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">
                                            {{ $index + 1 }}</td>

                                        <td class="px-4 py-3 font-medium whitespace-nowrap">
                                            @if ($al->foraneo === 'true')
                                                <flux:badge color="orange">Foraneo</flux:badge>
                                            @else
                                                <flux:badge color="indigo">Local</flux:badge>
                                            @endif
                                        </td>

                                        <td
                                            class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-100 whitespace-nowrap">
                                            {{ $al->matricula }}
                                        </td>

                                        <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-100">
                                            {{ $al->nombre }} {{ $al->apellido_paterno }}
                                            {{ $al->apellido_materno }}
                                        </td>

                                        @foreach ($materias as $m)
                                            @php
                                                $valor = $calificaciones[$al->id][$m->id] ?? null;

                                                $esNP = is_string($valor) && strtoupper(trim($valor)) === 'NP';
                                                $esNumeroValido =
                                                    is_numeric($valor) && (float) $valor >= 5 && (float) $valor <= 10;

                                                if ($esNP) {
                                                    $tieneNP = true;
                                                }

                                                if ($esNumeroValido) {
                                                    $sum += (float) $valor;
                                                    $count++;
                                                }

                                                // ✅ estilos input según valor
                                                $inputBase =
                                                    'w-full text-center rounded-lg border bg-white dark:bg-neutral-800 px-2 py-2 focus:outline-none focus:ring-2';
                                                $inputOk =
                                                    'border-emerald-300/80 dark:border-emerald-500/40 ring-1 ring-emerald-400/50 focus:ring-emerald-500';
                                                $inputNP =
                                                    'border-rose-400 dark:border-rose-500/60 ring-2 ring-rose-400/60 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-200 focus:ring-rose-500';
                                                $inputNone =
                                                    'border-neutral-300 dark:border-neutral-600 focus:ring-blue-500';

                                                if ($esNP) {
                                                    $inputClass = $inputNP;
                                                } elseif ($esNumeroValido) {
                                                    $inputClass = $inputOk;
                                                } else {
                                                    $inputClass = $inputNone;
                                                }
                                            @endphp

                                            <td class="px-3 py-3 align-middle"
                                                wire:key="cell-{{ $al->id }}-{{ $m->id }}">
                                                <div class="space-y-1">
                                                    <input type="text" inputmode="decimal"
                                                        pattern="^(10|[5-9](\.[0-9]+)?)$|^(np|NP)$"
                                                        title="Ingresa un número entre 5 y 10 (puede llevar decimales) o NP"
                                                        placeholder="5–10 o NP"
                                                        class="{{ $inputBase }} {{ $inputClass }}"
                                                        wire:model.live.debounce.350ms="calificaciones.{{ $al->id }}.{{ $m->id }}" />


                                                    {{-- ✅ Observación en la celda cuando es NP --}}
                                                    @if ($esNP)
                                                        <div
                                                            class="text-[10px] font-semibold text-rose-600 dark:text-rose-300 text-center">
                                                            NP • No aplica promedio
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach

                                        @php
                                            // ✅ Si tiene NP, NO mostramos promedio
                                            $avg = !$tieneNP && $count ? round($sum / $count, 2) : null;

                                            $chipClass =
                                                'bg-neutral-200 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100';
                                            if ($tieneNP) {
                                                $chipClass =
                                                    'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-200';
                                            } elseif (!is_null($avg)) {
                                                if ($avg >= 9) {
                                                    $chipClass =
                                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
                                                } elseif ($avg >= 7) {
                                                    $chipClass =
                                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
                                                } else {
                                                    $chipClass =
                                                        'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300';
                                                }
                                            }
                                        @endphp

                                        <td class="px-4 py-3">
                                            <div class="space-y-1">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $chipClass }}">
                                                    @if ($tieneNP)
                                                        NP
                                                    @else
                                                        {{ $avg !== null ? number_format($avg, 2) : '—' }}
                                                    @endif
                                                </span>

                                                {{-- ✅ Observación general --}}
                                                @if ($tieneNP)
                                                    <div class="text-[11px] text-rose-600 dark:text-rose-300">
                                                        Observación: tiene al menos una materia en <b>NP</b>, por lo
                                                        tanto <b>no se calcula el promedio</b>.
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-2">
                                                <form
                                                    action="{{ route('admin.pdf.documentacion.calificacion_alumno') }}"
                                                    method="GET" target="_blank" class="m-0">
                                                    <x-button type="submit" variant="primary"
                                                        class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                                        <div class="flex items-center gap-2">
                                                            <flux:icon.file-text />
                                                            <span>PDF</span>
                                                        </div>
                                                    </x-button>

                                                    <input type="hidden" name="modalidad_id"
                                                        value="{{ $modalidad->id }}">
                                                    <input type="hidden" name="alumno_id"
                                                        value="{{ $al->id }}">
                                                    <input type="hidden" name="generacion_id"
                                                        value="{{ $filtrar_generacion }}">
                                                    <input type="hidden" name="cuatrimestre_id"
                                                        value="{{ $filtrar_cuatrimestre }}">
                                                </form>

                                                <x-button variant="primary"
                                                    class="bg-green-600 hover:bg-green-700 text-white rounded-lg"
                                                    @click="enviarCalificacion({{ $al->id }}, '{{ $filtrar_cuatrimestre }}', '{{ $filtrar_generacion }}', '{{ $modalidad->id }}')">
                                                    <div class="flex items-center gap-2">
                                                        <flux:icon.send />
                                                        <span>Enviar</span>
                                                    </div>
                                                </x-button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Botones guardar / estado cambios --}}
                @if (count($alumnos) && count($materias))
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        <button type="button" wire:loading.attr="disabled"
                            @click="
                            Swal.fire({
                                title: '¿Qué deseas hacer?',
                                text: 'Guardar insertará solo los NO duplicados. Actualizar reemplazará todo el cuatrimestre.',
                                icon: 'question',
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: 'Guardar',
                                denyButtonText: 'Actualizar',
                                cancelButtonText: 'Cancelar',
                                confirmButtonColor: '#2563eb',
                                denyButtonColor: '#f59e0b',
                            }).then((r) => {
                                if (r.isConfirmed) {
                                    @this.set('modoGuardar', 'guardar');
                                    @this.call('guardarTodasLasCalificaciones');
                                } else if (r.isDenied) {
                                    @this.set('modoGuardar', 'actualizar');
                                    @this.call('guardarTodasLasCalificaciones');
                                }
                            });
                        "
                            class="px-6 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700">
                            Guardar / Actualizar
                        </button>



                        <span
                            class="text-sm {{ $hayCambios ? 'text-neutral-600 dark:text-neutral-400' : 'text-neutral-400 dark:text-neutral-500' }}">
                            {{ $hayCambios ? 'Existen cambios por guardar' : 'No hay cambios por guardar' }}
                        </span>
                    </div>

                    <div class="flex justify-end">
                        <x-button variant="primary" wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg"
                            @click="enviarCalificacionesMasivasAlpine()">
                            <flux:icon.send />
                            <span>Enviar a todos</span>
                        </x-button>
                    </div>
                @endif

            @endif
        </div>
    </div>
</div>
