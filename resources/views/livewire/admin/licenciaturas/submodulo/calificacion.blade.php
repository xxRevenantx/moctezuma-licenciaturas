<div x-data="{
    focusSiguienteNota(el) {
        const td = el.closest('td');
        let siguiente = td?.nextElementSibling?.querySelector('input[data-nota]');
        if (!siguiente) siguiente = td?.closest('tr')?.nextElementSibling?.querySelector('input[data-nota]');
        siguiente?.focus();
    },
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
                text: `Se intentará enviar la boleta a {{ $conteosOrigen[$filtrar_origen] ?? 0 }} alumno(s) activos del origen seleccionado: {{ $filtrar_origen === 'todos' ? 'Todos' : ($filtrar_origen === 'local' ? 'Locales' : 'Foráneos') }}. La búsqueda por nombre no limita este envío.`,
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

                <div class="lg:col-span-2 flex items-end">
                    <a href="{{ route('admin.boletas', array_filter([
                            'licenciatura' => $licenciatura->id,
                            'modalidad' => $modalidad->id,
                            'generacion' => $filtrar_generacion,
                            'cuatrimestre' => $filtrar_cuatrimestre,
                            'origen' => $filtrar_origen !== 'todos' ? $filtrar_origen : null,
                        ])) }}"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#006492] bg-sky-50 px-4 py-2.5 text-sm font-extrabold text-[#006492] transition hover:bg-sky-100 dark:bg-sky-950/30 dark:text-sky-300 dark:hover:bg-sky-950/50"
                        @if(!$hayCambios) wire:navigate @endif
                        @if($hayCambios) x-on:click.prevent="Swal.fire({icon:'warning', title:'Hay cambios por guardar', text:'Guarda o descarta los cambios antes de generar las boletas.'})" @endif>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v15H6z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6M9 13h6M9 17h6" />
                        </svg>
                        Boletas masivas
                    </a>
                </div>
            </div>

            @if ($filtrar_generacion && $filtrar_cuatrimestre)
                {{-- El origen es una clasificación de inscripción, no la modalidad académica. --}}
                <div class="mt-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/50 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Origen del estudiante</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">El filtro se aplica a captura, boletas masivas y envíos por correo.</p>
                    </div>
                    <div class="inline-flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-1.5 dark:border-slate-700 dark:bg-slate-900" role="group" aria-label="Filtrar alumnos por origen">
                        @foreach ([
                            'todos' => ['Todos', $conteosOrigen['total']],
                            'local' => ['Locales', $conteosOrigen['local']],
                            'foraneo' => ['Foráneos', $conteosOrigen['foraneo']],
                        ] as $valor => [$etiqueta, $cantidad])
                            <button type="button" wire:click="cambiarOrigen('{{ $valor }}')"
                                wire:loading.attr="disabled" wire:target="cambiarOrigen,filtrar_generacion,filtrar_cuatrimestre"
                                aria-pressed="{{ $filtrar_origen === $valor ? 'true' : 'false' }}"
                                class="cursor-pointer rounded-lg px-3.5 py-2 text-xs font-extrabold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006492] {{ $filtrar_origen === $valor ? 'bg-[#006492] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700' }}">
                                {{ $etiqueta }} <span class="ml-1 {{ $filtrar_origen === $valor ? 'text-sky-100' : 'text-slate-400' }}">{{ $cantidad }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
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

                {{-- Indicadores de la lista seleccionada --}}
                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/40">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Inscritos activos</div>
                        <div class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">{{ $conteosOrigen['total'] }}</div>
                    </div>
                    <div class="rounded-xl border border-sky-200 bg-sky-50/50 px-4 py-3 dark:border-sky-900 dark:bg-sky-950/20">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Locales</div>
                        <div class="mt-1 text-2xl font-extrabold text-[#006492] dark:text-sky-300">{{ $conteosOrigen['local'] }}</div>
                    </div>
                    <div class="rounded-xl border border-lime-200 bg-lime-50/50 px-4 py-3 dark:border-lime-900 dark:bg-lime-950/20">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Foráneos</div>
                        <div class="mt-1 text-2xl font-extrabold text-[#57731a] dark:text-lime-300">{{ $conteosOrigen['foraneo'] }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900">
                        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Visibles en captura</div>
                        <div class="mt-1 text-2xl font-extrabold text-slate-900 dark:text-white">{{ count($alumnos) }}</div>
                    </div>
                </div>

                {{-- Dashboard de captura: los valores se calculan con las notas de esta pantalla. --}}
                <section aria-label="Indicadores de captura" class="rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 via-white to-lime-50/50 p-4 shadow-sm dark:border-sky-900 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Matriz académica inteligente</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Avance de captura del origen y la búsqueda seleccionados. Las notas pendientes no se contabilizan como cero.</p>
                        </div>
                        <span class="rounded-full bg-[#006492] px-3 py-1.5 text-sm font-extrabold text-white">{{ $indicadores['avance'] }}% capturado</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700" role="progressbar" aria-label="Avance de captura" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $indicadores['avance'] }}">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#006492] to-[#88AC2E] transition-all duration-300" style="width: {{ $indicadores['avance'] }}%"></div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        <div class="rounded-xl border border-sky-100 bg-white/85 px-3 py-2 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-300">Notas capturadas</p>
                            <p class="mt-0.5 text-xl font-extrabold text-[#006492] dark:text-sky-300">{{ $indicadores['capturadas'] }} <span class="text-xs font-normal text-slate-500">/ {{ $indicadores['celdas'] }}</span></p>
                        </div>
                        <div class="rounded-xl border border-amber-100 bg-white/85 px-3 py-2 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-300">Calificaciones pendientes</p>
                            <p class="mt-0.5 text-xl font-extrabold text-amber-700 dark:text-amber-300">{{ $indicadores['pendientes'] }}</p>
                        </div>
                        <div class="rounded-xl border border-rose-100 bg-white/85 px-3 py-2 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-300">Alumnos con alerta</p>
                            <p class="mt-0.5 text-xl font-extrabold text-rose-700 dark:text-rose-300">{{ $indicadores['riesgo'] }}</p>
                        </div>
                        <div class="rounded-xl border border-lime-100 bg-white/85 px-3 py-2 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-300">Alumnos con captura completa</p>
                            <p class="mt-0.5 text-xl font-extrabold text-[#57731a] dark:text-lime-300">{{ $indicadores['completos'] }}</p>
                        </div>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">Una alerta significa al menos una materia con NP o una nota inferior a 6; es un indicador orientativo, no un dictamen académico definitivo.</p>
                </section>

                {{-- Seguimiento histórico: reglas transparentes, NO IA entrenada, ni probabilidad de deserción. --}}
                <section aria-label="Prioridades de seguimiento académico" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Alertas académicas predictivas</h3>
                                <span class="rounded-full border border-[#006492]/30 px-2 py-0.5 text-[10px] font-extrabold text-[#006492] dark:text-sky-300">Análisis histórico</span>
                            </div>
                            <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Prioridades orientativas basadas en calificaciones <strong>guardadas de cuatrimestres anteriores</strong>.
                                No son probabilidades, diagnósticos definitivos ni decisiones automáticas sobre el alumno.
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ count($alumnos) }} alumno(s) analizado(s) de la selección</span>
                            <button type="button" wire:click="descargarReporteAlertas" wire:loading.attr="disabled"
                                class="cursor-pointer rounded-lg border border-[#006492] px-3 py-2 text-xs font-bold text-[#006492] transition hover:bg-sky-50 dark:text-sky-300 dark:hover:bg-slate-800">Exportar seguimiento (CSV)</button>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 md:grid-cols-4">
                        @foreach ([
                            'alto' => ['Prioridad alta', 'border-rose-200 text-rose-700 dark:border-rose-900 dark:text-rose-300'],
                            'medio' => ['Seguimiento preventivo', 'border-amber-200 text-amber-700 dark:border-amber-900 dark:text-amber-300'],
                            'bajo' => ['Seguimiento ordinario', 'border-lime-200 text-[#57731a] dark:border-lime-900 dark:text-lime-300'],
                            'sin_datos' => ['Datos insuficientes', 'border-slate-200 text-slate-600 dark:border-slate-700 dark:text-slate-300'],
                        ] as $nivel => [$texto, $estilo])
                            <button type="button" wire:click="cambiarFiltroPredictivo('{{ $nivel }}')"
                                wire:loading.attr="disabled" aria-pressed="{{ $filtroPredictivo === $nivel ? 'true' : 'false' }}"
                                class="cursor-pointer rounded-xl border p-3 text-left transition hover:shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#006492] {{ $estilo }} {{ $filtroPredictivo === $nivel ? 'ring-2 ring-[#006492] bg-sky-50/30 dark:bg-slate-800' : 'bg-transparent hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                                <span class="block text-[11px] font-semibold">{{ $texto }}</span>
                                <span class="mt-1 block text-2xl font-extrabold tabular-nums">{{ $conteosPredictivos[$nivel] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" wire:click="cambiarFiltroPredictivo('todos')"
                            aria-pressed="{{ $filtroPredictivo === 'todos' ? 'true' : 'false' }}"
                            class="cursor-pointer rounded-lg border px-3 py-1.5 text-xs font-bold {{ $filtroPredictivo === 'todos' ? 'bg-[#006492] text-white border-[#006492]' : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200' }}">Mostrar todas las prioridades</button>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">El filtro histórico se combina con Local/Foráneo y con los filtros de captura. Cambiarlo no modifica ninguna nota.</p>
                    </div>
                    @if($atencionPrioritaria->isNotEmpty())
                        <div class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-700">
                            <p class="mb-2 text-xs font-extrabold uppercase tracking-wide text-slate-600 dark:text-slate-300">Atención sugerida a Control Escolar · Primeros 5 casos</p>
                            <div class="grid gap-2 lg:grid-cols-2">
                                @foreach($atencionPrioritaria as $candidato)
                                    @php $alertaBreve = $alertasHistoricas[$candidato->id] ?? []; @endphp
                                    <div class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-extrabold text-slate-800 dark:text-slate-100">{{ $candidato->apellido_paterno }} {{ $candidato->apellido_materno }} {{ $candidato->nombre }}</p>
                                            <p class="mt-0.5 truncate text-[11px] {{ ($alertaBreve['nivel'] ?? '') === 'alto' ? 'text-rose-600 dark:text-rose-300' : 'text-amber-700 dark:text-amber-300' }}">
                                                {{ ($alertaBreve['nivel'] ?? '') === 'alto' ? 'Prioridad alta' : 'Seguimiento preventivo' }} · {{ $alertaBreve['motivos'][0] ?? 'Revisar historial' }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="verPronosticoAlumno({{ $candidato->id }})" wire:loading.attr="disabled"
                                            class="shrink-0 cursor-pointer rounded-lg border border-[#006492] px-2.5 py-1.5 text-[11px] font-extrabold text-[#006492] hover:bg-sky-50 dark:text-sky-300">Revisar</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">No se identificaron prioridades altas o preventivas con la evidencia histórica disponible.</p>
                    @endif
                </section>

                {{-- Filtro académico basado en calificaciones ya guardadas, sin desaparición de filas al editar. --}}
                <section class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-700 dark:bg-slate-800/50" aria-label="Filtro por situación académica">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Detección inteligente</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Filtra según notas guardadas. Las filas permanecen visibles mientras editas.</p>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach (['todos' => 'Todos', 'pendientes' => 'Con pendientes', 'riesgo' => 'Con alertas', 'completos' => 'Captura completa'] as $codigo => $texto)
                            <button type="button" wire:click="cambiarFiltroAcademico('{{ $codigo }}')" wire:loading.attr="disabled"
                                aria-pressed="{{ $filtroAcademico === $codigo ? 'true' : 'false' }}"
                                class="cursor-pointer rounded-lg px-3 py-2 text-xs font-bold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#006492] {{ $filtroAcademico === $codigo ? 'bg-[#006492] text-white shadow' : 'border border-slate-200 bg-white text-slate-600 hover:border-sky-300 hover:bg-sky-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300' }}">
                                {{ $texto }}
                            </button>
                        @endforeach
                    </div>
                </section>

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
                    <div wire:loading.flex wire:target="filtrar_generacion, filtrar_cuatrimestre, search, cambiarOrigen"
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

                    @if (!count($alumnosVista))
                        <div class="mb-3 rounded-xl border border-dashed border-sky-300 bg-sky-50 p-5 text-center text-sm font-medium text-[#006492] dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-200">
                            No hay alumnos que coincidan con los filtros actuales. Elige “Todos” en Detección inteligente, cambia el origen o limpia la búsqueda.
                        </div>
                    @endif
                    {{-- ======= TABLA ======= --}}
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 px-1">
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Mostrando {{ count($alumnosVista) }} de {{ count($alumnos) }} alumno(s) de la selección.</p>
                        <p class="text-[11px] text-[#006492] dark:text-sky-300">Tab ↹ para avanzar · Enter ↵ para la siguiente materia · Guarda por alumno o por selección</p>
                    </div>
                    <div class="max-h-[72vh] overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700" tabindex="0" aria-label="Tabla de captura de calificaciones con desplazamiento horizontal y vertical">
                        <table class="cum-matriz w-max min-w-full bg-white dark:bg-neutral-900 text-sm">
                            <thead class="sticky top-0 z-30">
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
                                        Matrícula SEG / ID interno</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase tracking-wider w-72">
                                        Alumno</th>

                                    <th class="w-40 min-w-40 px-3 py-3 text-left text-xs font-bold uppercase tracking-wide text-[#006492] dark:text-sky-300">Avance / Estado</th>

                                    <th class="min-w-[185px] px-3 py-3 text-left text-[11px] font-extrabold uppercase tracking-wide text-[#006492] dark:text-sky-300">Seguimiento histórico</th>

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
                                @foreach ($alumnosVista as $index => $al)
                                    @php
                                        $diagnostico = $diagnosticos[$al->id];
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
                                            <div class="font-bold">SEG: {{ $al->matricula ?: 'Pendiente' }}</div>
                                            <div class="mt-0.5 font-mono text-[10px] text-neutral-400">ID: {{ $al->matricula_interna ?: '—' }}</div>
                                        </td>

                                        <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-100">
                                            {{ $al->nombre }} {{ $al->apellido_paterno }}
                                            {{ $al->apellido_materno }}
                                        </td>

                                        <td class="px-3 py-3 align-middle">
                                            <div class="flex items-center justify-between gap-2 text-xs font-extrabold">
                                                <span class="text-slate-700 dark:text-slate-200">{{ $diagnostico['capturadas'] }}/{{ $diagnostico['total'] }}</span>
                                                <span class="text-[#006492] dark:text-sky-300">{{ $diagnostico['porcentaje'] }}%</span>
                                            </div>
                                            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                                <div class="h-full rounded-full {{ $diagnostico['riesgo'] ? 'bg-rose-500' : 'bg-[#88AC2E]' }}" style="width: {{ $diagnostico['porcentaje'] }}%"></div>
                                            </div>
                                            <div class="mt-1.5 flex flex-wrap gap-1">
                                                @if($diagnostico['riesgo'])
                                                    <span class="rounded-md bg-rose-100 px-1.5 py-0.5 text-[10px] font-bold text-rose-700 dark:bg-rose-900/30 dark:text-rose-300" title="Revisa NP o notas inferiores a 6">Alerta</span>
                                                @elseif($diagnostico['completo'])
                                                    <span class="rounded-md bg-lime-100 px-1.5 py-0.5 text-[10px] font-bold text-lime-800 dark:bg-lime-900/30 dark:text-lime-300">Completo</span>
                                                @endif
                                                @if($diagnostico['pendientes'])
                                                    <span class="rounded-md bg-amber-100 px-1.5 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">{{ $diagnostico['pendientes'] }} pendiente(s)</span>
                                                @endif
                                            </div>
                                        </td>

                                        @php
                                            $prediccion = $alertasHistoricas[$al->id] ?? [
                                                'nivel' => 'sin_datos', 'evidencia' => 'insuficiente',
                                                'motivos' => ['No hay calificaciones anteriores evaluables'], 'tendencia' => 'sin_datos'
                                            ];
                                            $etiquetasPred = ['alto' => 'Prioridad alta', 'medio' => 'Preventivo', 'bajo' => 'Ordinario', 'sin_datos' => 'Sin evidencia'];
                                            $estilosPred = [
                                                'alto' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300',
                                                'medio' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300',
                                                'bajo' => 'bg-lime-100 text-[#57731a] dark:bg-lime-950/40 dark:text-lime-300',
                                                'sin_datos' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
                                            ];
                                        @endphp
                                        <td class="min-w-[185px] px-3 py-3 align-middle">
                                            <div class="flex flex-col items-start gap-1.5">
                                                <span title="{{ implode(' · ', $prediccion['motivos']) }}"
                                                    class="rounded-lg px-2 py-1 text-[11px] font-extrabold {{ $estilosPred[$prediccion['nivel']] ?? $estilosPred['sin_datos'] }}">{{ $etiquetasPred[$prediccion['nivel']] ?? 'Sin evidencia' }}</span>
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400">Evidencia: {{ $prediccion['evidencia'] }} · {{ $prediccion['periodos_evaluables'] ?? 0 }} periodo(s)</span>
                                                <button type="button" wire:click="verPronosticoAlumno({{ $al->id }})" wire:loading.attr="disabled"
                                                    class="cursor-pointer text-[11px] font-bold text-[#006492] underline underline-offset-2 hover:text-sky-700 dark:text-sky-300">Ver factores y recomendación</button>
                                            </div>
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
                                                $inputBajo =
                                                    'border-amber-400 dark:border-amber-500 ring-1 ring-amber-400/60 bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-200 focus:ring-amber-500';
                                                $inputNP =
                                                    'border-rose-400 dark:border-rose-500/60 ring-2 ring-rose-400/60 bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-200 focus:ring-rose-500';
                                                $inputNone =
                                                    'border-neutral-300 dark:border-neutral-600 focus:ring-blue-500';

                                                if ($esNP) {
                                                    $inputClass = $inputNP;
                                                } elseif ($esNumeroValido && (float) $valor < 6) {
                                                    $inputClass = $inputBajo;
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
                                                        data-nota
                                                        aria-label="Calificación de {{ $al->nombre }} {{ $al->apellido_paterno }} en {{ $m->materia->nombre }}"
                                                        @keydown.enter.prevent="focusSiguienteNota($el)"
                                                        wire:model.blur="calificaciones.{{ $al->id }}.{{ $m->id }}" />


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
                                            <div class="flex flex-col gap-1.5 min-w-[140px]">
                                                <button type="button"
                                                    @click="Swal.fire({title:'¿Guardar las notas de este alumno?',text:'Solo se aplicarán celdas modificadas. Si vaciaste una nota existente, se eliminará el registro de esa materia.',icon:'question',showCancelButton:true,confirmButtonText:'Guardar alumno',cancelButtonText:'Cancelar',confirmButtonColor:'#006492'}).then(r => { if(r.isConfirmed) @this.call('guardarAlumno', {{ $al->id }}) })"
                                                    wire:loading.attr="disabled" wire:target="guardarAlumno({{ $al->id }})"
                                                    class="cursor-pointer rounded-lg border border-[#006492] bg-sky-50 px-3 py-1.5 text-xs font-extrabold text-[#006492] transition hover:bg-sky-100 disabled:opacity-50 dark:border-sky-600 dark:bg-sky-950/30 dark:text-sky-300"
                                                    title="Guarda solo las notas modificadas de este alumno">Guardar alumno</button>
                                                <button type="button" wire:click="verHistorialAlumno({{ $al->id }})"
                                                    class="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 transition hover:border-[#006492] hover:text-[#006492] dark:border-slate-700 dark:text-slate-300 dark:hover:text-sky-300"
                                                    title="Consulta las últimas 20 modificaciones registradas">Historial</button>
                                                <div class="flex flex-wrap justify-center gap-2">
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
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Las eliminaciones ahora son diferidas hasta Actualizar; no se borra nada al vaciar una celda. --}}
                {{-- Botones guardar / estado cambios --}}
                @if (count($alumnosVista) && count($materias))
                    <div class="flex flex-wrap items-center justify-end gap-3">
                        @if ($hayCambios)
                            <button type="button" wire:loading.attr="disabled"
                                @click="Swal.fire({title:'¿Descartar cambios?',text:'Se restaurarán las notas guardadas en la base de datos. Esta acción no borra registros.',icon:'warning',showCancelButton:true,confirmButtonText:'Sí, descartar',cancelButtonText:'Cancelar',confirmButtonColor:'#006492'}).then(r => { if(r.isConfirmed) @this.call('descartarCambios') })"
                                class="cursor-pointer rounded-lg border border-slate-300 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                Descartar edición
                            </button>
                        @endif
                        <button type="button" wire:loading.attr="disabled"
                            @click="
                            Swal.fire({
                                title: '¿Qué deseas hacer?',
                                text: `Se revisarán {{ count($alumnosVista) }} alumno(s) visibles. Guardar añade solo notas nuevas; Actualizar modifica únicamente las celdas cambiadas (incluidos borrados confirmados), sin tocar las demás.`,
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
                            {{ $hayCambios ? 'Existen ediciones pendientes. Guardar alumno o Actualizar registra solo las celdas modificadas.' : 'Sin ediciones pendientes de esta selección' }}
                        </span>
                    </div>

                    <div class="flex flex-col items-end gap-1">
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">El envío masivo sigue el filtro de origen, no el de pendientes/alertas ni la búsqueda.</p>
                        <x-button variant="primary" wire:loading.attr="disabled"
                            class="flex items-center gap-2 px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg"
                            @click="enviarCalificacionesMasivasAlpine()">
                            <flux:icon.send />
                            <span>Enviar a {{ $filtrar_origen === 'todos' ? 'todos' : ($filtrar_origen === 'local' ? 'locales' : 'foráneos') }} ({{ $conteosOrigen[$filtrar_origen] ?? 0 }})</span>
                        </x-button>
                    </div>
                @endif

            @endif
        </div>
    </div>

    @if ($alumnoHistorialId)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-3 backdrop-blur-sm" role="presentation" wire:key="matriz-historial-{{ $alumnoHistorialId }}">
            <section role="dialog" aria-modal="true" aria-label="Historial de calificaciones"
                class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-neutral-900">
                <div class="flex items-start justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-700">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Historial de calificaciones</h3>
                        <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $nombreAlumnoHistorial }} · Últimos 20 movimientos de este cuatrimestre</p>
                    </div>
                    <button type="button" wire:click="cerrarHistorialAlumno" aria-label="Cerrar historial"
                        class="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-extrabold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Cerrar ×</button>
                </div>
                <div class="overflow-auto p-4">
                    @forelse ($historialAlumnoNotas as $evento)
                        <div class="mb-2 rounded-xl border border-slate-200 bg-slate-50/80 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ $evento['materia'] }}</p>
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $evento['fecha'] }} · {{ $evento['usuario'] }}</span>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs">
                                <span class="rounded-lg bg-white px-2 py-1 font-bold text-slate-600 dark:bg-slate-900 dark:text-slate-300">{{ ucfirst($evento['accion']) }}</span>
                                <span class="text-slate-500 dark:text-slate-400">Anterior: <b>{{ $evento['anterior'] ?? '—' }}</b></span>
                                <span aria-hidden="true">→</span>
                                <span class="text-[#006492] dark:text-sky-300">Nuevo: <b>{{ $evento['nuevo'] ?? '—' }}</b></span>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500 dark:border-slate-700">Este alumno todavía no tiene movimientos registrados en la auditoría para el cuatrimestre seleccionado. Los registros antiguos sin auditoría no pueden reconstruirse automáticamente.</p>
                    @endforelse
                </div>
            </section>
        </div>
    @endif

    @if ($alumnoPronosticoId)
        <div class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/70 p-3 backdrop-blur-sm" role="presentation" wire:key="matriz-pronostico-{{ $alumnoPronosticoId }}">
            <section role="dialog" aria-modal="true" aria-label="Análisis histórico académico"
                class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-neutral-900">
                <div class="flex items-start justify-between gap-3 border-b border-slate-200 p-4 dark:border-slate-700">
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Análisis histórico y seguimiento sugerido</h3>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $nombreAlumnoPronostico }} · Solo cuatrimestres anteriores al seleccionado</p>
                    </div>
                    <button type="button" wire:click="cerrarPronosticoAlumno" aria-label="Cerrar análisis"
                        class="cursor-pointer rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-extrabold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Cerrar ×</button>
                </div>
                <div class="space-y-4 overflow-y-auto p-4">
                    @php
                        $p = $pronosticoSeleccionado;
                        $etiquetasFicha = ['alto' => 'Prioridad alta', 'medio' => 'Seguimiento preventivo', 'bajo' => 'Seguimiento ordinario', 'sin_datos' => 'Datos insuficientes'];
                    @endphp
                    <div class="grid gap-2 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="text-[11px] font-semibold text-slate-500">Prioridad sugerida</p>
                            <p class="mt-1 font-extrabold text-[#006492] dark:text-sky-300">{{ $etiquetasFicha[$p['nivel'] ?? 'sin_datos'] }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="text-[11px] font-semibold text-slate-500">Evidencia</p>
                            <p class="mt-1 font-extrabold text-slate-800 dark:text-white">{{ ucfirst($p['evidencia'] ?? 'Insuficiente') }} · {{ $p['periodos_evaluables'] ?? 0 }} periodo(s)</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                            <p class="text-[11px] font-semibold text-slate-500">Tendencia comparable</p>
                            <p class="mt-1 font-extrabold text-slate-800 dark:text-white">
                                {{ match($p['tendencia'] ?? 'sin_datos') {'descendente' => 'Descendente', 'ascendente' => 'Ascendente', 'estable' => 'Estable', default => 'No calculable'} }}
                                @if(($p['cambio'] ?? null) !== null) <span class="text-xs">({{ ($p['cambio'] > 0 ? '+' : '') . number_format($p['cambio'], 2) }} pts.)</span> @endif
                            </p>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        <h4 class="mb-2 text-sm font-extrabold text-slate-800 dark:text-white">Señales detectadas</h4>
                        <ul class="list-inside list-disc space-y-1 text-xs text-slate-600 dark:text-slate-300">
                            @foreach(($p['motivos'] ?? []) as $motivo)
                                <li>{{ $motivo }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="rounded-xl border-l-4 border-[#88AC2E] bg-lime-50/50 p-3 dark:bg-lime-950/10">
                        <h4 class="text-sm font-extrabold text-[#57731a] dark:text-lime-300">Acción sugerida para Control Escolar</h4>
                        <p class="mt-1 text-xs leading-5 text-slate-700 dark:text-slate-300">{{ $p['accion'] ?? 'Revisar el expediente del alumno.' }}</p>
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Historial de los últimos periodos con registro</h4>
                        <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full min-w-[550px] text-left text-xs">
                                <thead class="bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                    <tr><th class="p-2">Cuatrimestre</th><th class="p-2">Cobertura</th><th class="p-2">Media numérica</th><th class="p-2">Reprobadas</th><th class="p-2">NP</th><th class="p-2">Evaluable</th></tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                    @forelse(($p['periodos'] ?? []) as $periodoHistorico)
                                        <tr>
                                            <td class="p-2 font-bold">{{ $periodoHistorico['cuatrimestre'] }}.º</td>
                                            <td class="p-2">{{ $periodoHistorico['capturadas'] }}/{{ $periodoHistorico['esperadas'] }} ({{ $periodoHistorico['cobertura'] }}%)</td>
                                            <td class="p-2">{{ $periodoHistorico['promedio_numerico'] === null ? '—' : number_format($periodoHistorico['promedio_numerico'], 2) }}</td>
                                            <td class="p-2">{{ $periodoHistorico['reprobadas'] }}</td>
                                            <td class="p-2">{{ $periodoHistorico['np'] }}</td>
                                            <td class="p-2">{{ $periodoHistorico['suficiente'] ? 'Sí' : 'No' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="p-4 text-center text-slate-500">No existen calificaciones históricas utilizables en esta selección.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[11px] leading-4 text-slate-500 dark:text-slate-400">Para evaluar un periodo se exige 70% de cobertura y al menos 3 notas (o todas si el plan tiene menos de 3 materias). La media numérica es descriptiva, no sustituye el promedio oficial; con NP el promedio institucional puede no calcularse. Solo se comparan periodos consecutivos completos. Si cambió de modalidad, se utiliza el grupo de asignaturas con más notas registradas en cada periodo.</p>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Motor determinista y auditable. No emplea inteligencia artificial entrenada ni estima probabilidades de reprobación o deserción. El seguimiento requiere validación humana y no altera el expediente.</p>
                </div>
            </section>
        </div>
    @endif

    {{-- Columnas estables y encabezado fijo: ancho controlado para que no se desalineen al desplazar. --}}
    <style>
        .cum-matriz th:nth-child(1), .cum-matriz td:nth-child(1) { min-width: 3.5rem; width: 3.5rem; }
        .cum-matriz th:nth-child(2), .cum-matriz td:nth-child(2) { min-width: 6.25rem; width: 6.25rem; }
        .cum-matriz th:nth-child(3), .cum-matriz td:nth-child(3) { min-width: 11rem; width: 11rem; }
        .cum-matriz th:nth-child(4), .cum-matriz td:nth-child(4) { min-width: 14rem; width: 14rem; }
        .cum-matriz thead th { position: sticky; top: 0; background: #eff6ff; z-index: 20; }
        .dark .cum-matriz thead th { background: #1f2937; }
        @media (min-width: 1280px) {
            .cum-matriz th:nth-child(-n+4), .cum-matriz td:nth-child(-n+4) { position: sticky; z-index: 11; background: #ffffff; }
            .cum-matriz tbody tr:nth-child(even) td:nth-child(-n+4) { background: #fafafa; }
            .dark .cum-matriz th:nth-child(-n+4), .dark .cum-matriz td:nth-child(-n+4) { background: #171717; }
            .dark .cum-matriz tbody tr:nth-child(even) td:nth-child(-n+4) { background: #262626; }
            .cum-matriz th:nth-child(1), .cum-matriz td:nth-child(1) { left: 0; }
            .cum-matriz th:nth-child(2), .cum-matriz td:nth-child(2) { left: 3.5rem; }
            .cum-matriz th:nth-child(3), .cum-matriz td:nth-child(3) { left: 9.75rem; }
            .cum-matriz th:nth-child(4), .cum-matriz td:nth-child(4) { left: 20.75rem; box-shadow: 3px 0 7px -5px #475569; }
            .cum-matriz thead th:nth-child(-n+4) { z-index: 35; background: #eff6ff; }
            .dark .cum-matriz thead th:nth-child(-n+4) { background: #1f2937; }
        }
    </style>
</div>
