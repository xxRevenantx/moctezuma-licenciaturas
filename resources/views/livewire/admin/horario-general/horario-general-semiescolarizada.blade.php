<div
    x-data="{
        pdf: false,
        pdfUrl: 'about:blank',
        pdfLoaded: false,
        eliminar: false,
        abrirPdf(url) {
            if (!url) return;
            this.pdfLoaded = false;
            this.pdfUrl = url;
            this.pdf = true;
            document.documentElement.classList.add('overflow-hidden');
        },
        cerrarPdf() {
            this.pdf = false;
            this.pdfLoaded = false;
            this.pdfUrl = 'about:blank';
            document.documentElement.classList.remove('overflow-hidden');
        }
    }"
    x-on:abrir-eliminar-horario-general.window="eliminar=true"
    x-on:cerrar-eliminar-horario-general.window="eliminar=false"
    x-on:horario-general-ok.window="Swal.fire({icon:'success',title:'Listo',text:$event.detail.message,timer:2600,showConfirmButton:false})"
    x-on:horario-general-error.window="Swal.fire({icon:'error',title:'No se pudo continuar',text:$event.detail.message})"
    x-cloak
    class="space-y-4"
>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>
        <div class="p-4 sm:p-5">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Horario General Semiescolarizado</h2>
                        <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#006492] ring-1 ring-sky-200 dark:bg-sky-950/30 dark:text-sky-300 dark:ring-sky-900">{{ $ciclo_escolar }} · {{ $periodo_escolar }}</span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-neutral-800 dark:text-slate-300">{{ $columnasUnicas->count() }} grupos</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Filtra por periodo académico y genera una versión compacta o una versión legible del PDF.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <flux:button
                        type="button"
                        variant="primary"
                        class="cursor-pointer"
                        data-url="{{ $pdfLegible }}"
                        x-on:click.prevent="abrirPdf($el.dataset.url)"
                    >PDF legible</flux:button>
                    <flux:button
                        type="button"
                        variant="filled"
                        class="cursor-pointer"
                        data-url="{{ $pdfCompacto }}"
                        x-on:click.prevent="abrirPdf($el.dataset.url)"
                    >PDF compacto</flux:button>
                    <flux:button variant="danger" wire:click="abrirEliminarHorario" class="cursor-pointer">Administrar eliminación</flux:button>
                </div>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <flux:select wire:model.live="ciclo_escolar" label="Ciclo escolar">
                    @foreach($opciones['ciclos'] as $ciclo)
                        <flux:select.option value="{{ $ciclo }}">{{ $ciclo }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="periodo_escolar" label="Periodo">
                    @foreach($opciones['periodos'] as $periodo)
                        <flux:select.option value="{{ $periodo }}">{{ $periodo }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filtrar_cuatrimestre" label="Cuatrimestre">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach($opciones['cuatrimestres'] as $op)
                        <flux:select.option value="{{ $op['id'] }}">{{ $op['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filtrar_licenciatura" label="Licenciatura">
                    <flux:select.option value="">Todas</flux:select.option>
                    @foreach($opciones['licenciaturas'] as $op)
                        <flux:select.option value="{{ $op['id'] }}">{{ $op['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filtrar_generacion" label="Generación">
                    <flux:select.option value="">Todas</flux:select.option>
                    @foreach($opciones['generaciones'] as $op)
                        <flux:select.option value="{{ $op['id'] }}">{{ $op['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div>
                    <flux:input wire:model.live.debounce.300ms="busqueda" label="Buscar" placeholder="Materia o profesor" icon="magnifying-glass" />
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <div class="flex flex-wrap gap-2">
                    @foreach($opciones['cuatrimestres'] as $op)
                        <button type="button" wire:click="$set('filtrar_cuatrimestre','{{ $op['id'] }}')" class="cursor-pointer rounded-full border px-3 py-1.5 text-xs font-bold transition {{ (string)$filtrar_cuatrimestre === (string)$op['id'] ? 'border-[#006492] bg-[#006492] text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-sky-300 dark:border-neutral-700 dark:bg-neutral-900 dark:text-slate-300' }}">{{ $op['nombre'] }}</button>
                    @endforeach
                </div>
                <flux:button wire:click="limpiarFiltros" variant="ghost" class="cursor-pointer">Limpiar filtros</flux:button>
            </div>
        </div>
    </section>

    @if($horarios->isEmpty())
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center text-amber-800 dark:border-amber-900 dark:bg-amber-950/20 dark:text-amber-300">
            No hay horarios semiescolarizados para los filtros seleccionados.
        </section>
    @else
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="overflow-x-auto">
                <table class="min-w-max border-collapse text-[12px]">
                    <thead class="sticky top-0 z-20 bg-slate-100 dark:bg-neutral-800">
                        <tr>
                            <th class="sticky left-0 z-30 min-w-[120px] border-b border-r border-slate-200 bg-slate-100 px-3 py-3 text-left font-black text-slate-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">Hora</th>
                            @foreach($columnasUnicas as $col)
                                <th class="min-w-[190px] border-b border-r border-slate-200 px-3 py-3 text-center dark:border-neutral-700">
                                    <div class="font-black text-slate-900 dark:text-white">{{ $col['licenciatura_corta'] }}</div>
                                    <div class="mt-1 text-[11px] font-semibold text-slate-500">{{ $col['cuatrimestre'] }}° CUATRIMESTRE · {{ $col['generacion'] }}</div>
                                    <form action="{{ route('admin.pdf.horario-semiescolarizada') }}" method="GET" target="_blank" class="mt-2">
                                        <input type="hidden" name="licenciatura_id" value="{{ $col['licenciatura_id'] }}">
                                        <input type="hidden" name="modalidad_id" value="2">
                                        <input type="hidden" name="filtrar_generacion" value="{{ $col['generacion_id'] }}">
                                        <input type="hidden" name="filtrar_cuatrimestre" value="{{ $col['cuatrimestre_id'] }}">
                                        <button class="cursor-pointer rounded-lg bg-[#006492] px-2.5 py-1 text-[10px] font-bold text-white hover:bg-[#075a81]">Ver grupo</button>
                                    </form>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($horasUnicas as $hora)
                            @php
                                $partesHora = array_map('trim', explode('-', strtolower((string)$hora), 2));
                                $finTs = isset($partesHora[1]) ? strtotime($partesHora[1]) : false;
                                $insertarReceso = $finTs !== false && date('H:i', $finTs) === '10:00';
                            @endphp
                            <tr>
                                <td class="sticky left-0 z-10 border-b border-r border-slate-200 bg-white px-3 py-4 text-center font-black text-slate-700 dark:border-neutral-700 dark:bg-neutral-900 dark:text-slate-200">{{ strtoupper($hora) }}</td>
                                @foreach($columnasUnicas as $col)
                                    @php
                                        $key = $hora.'|'.$col['cuatrimestre_id'].'|'.$col['licenciatura_id'].'|'.$col['generacion_id'];
                                        $item = $celdas->get($key);
                                        $prof = $item?->asignacionMateria?->profesor;
                                        $materia = $item?->asignacionMateria?->materia;
                                        $color = $prof?->color ?: '#f8fafc';
                                    @endphp
                                    <td class="border-b border-r border-slate-200 p-2 text-center align-middle dark:border-neutral-700" style="background:{{ $item ? $color : '#ffffff' }};">
                                        @if($item)
                                            <div class="font-black leading-tight text-black drop-shadow-[0_1px_0_rgba(255,255,255,.3)]">{{ $materia?->nombre ?? 'Materia no disponible' }}</div>
                                            <div class="mt-1 text-[10px] font-bold italic leading-tight text-black/80">{{ $prof ? trim($prof->nombre.' '.$prof->apellido_paterno.' '.$prof->apellido_materno) : 'SIN PROFESOR' }}</div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @if($insertarReceso)
                                <tr>
                                    <td class="sticky left-0 z-10 border-b border-r border-slate-200 bg-slate-100 px-3 py-2 text-center font-black text-slate-700 dark:border-neutral-700 dark:bg-neutral-800 dark:text-slate-200">10:00AM-10:30AM</td>
                                    <td colspan="{{ $columnasUnicas->count() }}" class="border-b border-slate-200 bg-[repeating-linear-gradient(45deg,#f8fafc,#f8fafc_8px,#e2e8f0_8px,#e2e8f0_16px)] py-2 text-center text-xs font-black tracking-[.35em] text-slate-500">RECESO</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h3 class="font-black text-slate-900 dark:text-white">Carga programada por docente</h3><p class="text-xs text-slate-500">Las horas se calculan por duración real de cada bloque, no por cantidad de registros.</p></div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700 dark:bg-neutral-800 dark:text-slate-200">Total programado: {{ $totalHoras }} h</span>
            </div>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($horasPorDocente as $docente)
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 p-3 dark:border-neutral-800">
                        <div class="flex min-w-0 items-center gap-2"><span class="h-3 w-3 shrink-0 rounded-full" style="background:{{ $docente['color'] }}"></span><span class="truncate text-xs font-bold text-slate-700 dark:text-slate-200">{{ $docente['nombre'] }}</span></div>
                        <span class="ml-2 text-xs font-black text-[#006492] dark:text-sky-300">{{ $docente['horas'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div
        x-show="pdf"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[120] grid place-items-center bg-black/45 p-4 backdrop-blur-sm"
        x-on:click.self="cerrarPdf()"
        x-on:keydown.escape.window="if (pdf) cerrarPdf()"
        role="dialog"
        aria-modal="true"
    >
        <div class="w-full max-w-[1500px] overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3 dark:border-neutral-800">
                <div class="min-w-0">
                    <h3 class="font-black text-slate-900 dark:text-white">Vista previa · Horario General</h3>
                    <p class="text-xs text-slate-500">{{ $ciclo_escolar }} · {{ $periodo_escolar }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <a
                        x-bind:href="pdfUrl"
                        target="_blank"
                        rel="noopener"
                        class="cursor-pointer rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold text-[#006492] transition hover:bg-sky-50 dark:border-neutral-700 dark:text-sky-300 dark:hover:bg-neutral-800"
                    >Abrir en pestaña</a>
                    <button
                        type="button"
                        x-on:click="cerrarPdf()"
                        class="grid h-9 w-9 cursor-pointer place-items-center rounded-lg border border-slate-200 text-slate-600 dark:border-neutral-700 dark:text-slate-300"
                        aria-label="Cerrar vista previa"
                    >×</button>
                </div>
            </div>

            <div class="relative h-[82vh] bg-slate-100 dark:bg-neutral-950">
                <div
                    x-show="!pdfLoaded"
                    class="absolute inset-0 z-10 grid place-items-center bg-white/90 dark:bg-neutral-900/90"
                >
                    <div class="text-center">
                        <svg class="mx-auto h-8 w-8 animate-spin text-[#006492]" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <p class="mt-2 text-xs font-semibold text-slate-500">Generando vista previa del PDF…</p>
                    </div>
                </div>

                {{-- IMPORTANTE: el iframe solo existe cuando el modal está abierto.
                     Evita src="" (que recarga la página actual dentro del iframe y puede provocar carga recursiva). --}}
                <template x-if="pdf">
                    <iframe
                        x-bind:src="pdfUrl"
                        x-on:load="pdfLoaded = true"
                        class="h-full w-full border-0"
                        title="Vista previa del horario general semiescolarizado"
                    ></iframe>
                </template>
            </div>
        </div>
    </div>

    <div x-show="eliminar" x-cloak class="fixed inset-0 z-[130] grid place-items-center bg-black/50 p-4 backdrop-blur-sm" @click.self="eliminar=false">
        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="border-b border-slate-200 p-5 dark:border-neutral-800">
                <h3 class="text-lg font-black text-rose-700">Eliminar horario por grupo</h3>
                <p class="mt-1 text-sm text-slate-500">Ya no se elimina toda la modalidad. Solo se borrarán los grupos que selecciones dentro de {{ $ciclo_escolar }} · {{ $periodo_escolar }}.</p>
            </div>
            <div class="max-h-[55vh] overflow-y-auto p-5">
                <div class="mb-3 flex justify-between gap-2">
                    <span class="text-xs font-bold text-slate-500">{{ count($grupos_eliminar) }} seleccionados</span>
                    <flux:button wire:click="seleccionarTodosGruposEliminar" variant="ghost" size="sm" class="cursor-pointer">Seleccionar todos</flux:button>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach($gruposDisponibles as $grupo)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:border-rose-300 dark:border-neutral-800">
                            <input type="checkbox" wire:model.live="grupos_eliminar" value="{{ $grupo['clave_grupo'] }}" class="mt-1 rounded border-slate-300 text-rose-600 focus:ring-rose-600">
                            <span><b class="block text-sm text-slate-900 dark:text-white">{{ $grupo['licenciatura_corta'] }}</b><span class="text-xs text-slate-500">{{ $grupo['cuatrimestre'] }}° · Gen. {{ $grupo['generacion'] }} · {{ $grupo['registros'] }} registros</span></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 p-4 dark:border-neutral-800">
                <flux:button variant="ghost" @click="eliminar=false" class="cursor-pointer">Cancelar</flux:button>
                <flux:button variant="danger" wire:click="eliminarGruposSeleccionados" class="cursor-pointer">Eliminar seleccionados</flux:button>
            </div>
        </div>
    </div>
</div>
