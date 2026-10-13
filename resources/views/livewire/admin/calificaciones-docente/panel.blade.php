<div class="school-grades space-y-5">
    @php
        $estadoMeta = [
            'sin_captura' => [
                'label' => 'Sin captura',
                'class' => 'bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
            ],
            'parcial' => [
                'label' => 'Captura parcial',
                'class' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/50 dark:text-amber-200 dark:ring-amber-800',
            ],
            'completa' => [
                'label' => 'Completa',
                'class' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:ring-emerald-800',
            ],
            'entregada' => [
                'label' => 'Entregada',
                'class' => 'bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-200 dark:bg-sky-950/50 dark:text-sky-200 dark:ring-sky-800',
            ],
            'validada' => [
                'label' => 'Validada / cerrada',
                'class' => 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-950/50 dark:text-indigo-200 dark:ring-indigo-800',
            ],
        ];

        $statCards = [
            [
                'label' => 'Docentes',
                'value' => $resumen['docentes'],
                'caption' => 'Asignados',
                'icon' => 'users',
                'box' => 'from-blue-50 to-sky-50/50 border-blue-100 dark:from-blue-950/40 dark:to-sky-950/20 dark:border-blue-900/60',
                'iconBox' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
            ],
            [
                'label' => 'Materias / grupos',
                'value' => $resumen['materias'],
                'caption' => 'Activas',
                'icon' => 'book-open',
                'box' => 'from-emerald-50 to-green-50/50 border-emerald-100 dark:from-emerald-950/40 dark:to-green-950/20 dark:border-emerald-900/60',
                'iconBox' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
            ],
            [
                'label' => 'Esperadas',
                'value' => $resumen['esperadas'],
                'caption' => 'Calificaciones',
                'icon' => 'clipboard-document-list',
                'box' => 'from-amber-50 to-orange-50/40 border-amber-100 dark:from-amber-950/40 dark:to-orange-950/20 dark:border-amber-900/60',
                'iconBox' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
            ],
            [
                'label' => 'Capturadas',
                'value' => $resumen['capturadas'],
                'caption' => 'Registradas',
                'icon' => 'check-circle',
                'box' => 'from-violet-50 to-indigo-50/40 border-violet-100 dark:from-violet-950/40 dark:to-indigo-950/20 dark:border-violet-900/60',
                'iconBox' => 'bg-violet-100 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300',
            ],
            [
                'label' => 'Pendientes',
                'value' => $resumen['pendientes'],
                'caption' => 'Por capturar',
                'icon' => 'clock',
                'box' => 'from-rose-50 to-red-50/40 border-rose-100 dark:from-rose-950/40 dark:to-red-950/20 dark:border-rose-900/60',
                'iconBox' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
            ],
        ];

        $edicionesSinGuardar = 0;
        foreach ($calificaciones as $idAlumno => $notas) {
            foreach ((array) $notas as $idAsignacion => $valorNota) {
                if ((string) ($calificacionesGuardadas[$idAlumno][$idAsignacion] ?? '') !== (string) ($valorNota ?? '')) {
                    $edicionesSinGuardar++;
                }
            }
        }

        $courseStyles = [
            [
                'icon' => 'book-open',
                'border' => 'border-blue-200 hover:border-blue-400 dark:border-blue-900/70 dark:hover:border-blue-700',
                'iconBox' => 'bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-900',
                'chip' => 'bg-blue-50/80 dark:bg-blue-950/30',
                'progress' => 'from-blue-600 to-cyan-500',
            ],
            [
                'icon' => 'user-group',
                'border' => 'border-emerald-200 hover:border-emerald-400 dark:border-emerald-900/70 dark:hover:border-emerald-700',
                'iconBox' => 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900',
                'chip' => 'bg-emerald-50/80 dark:bg-emerald-950/30',
                'progress' => 'from-emerald-600 to-teal-500',
            ],
            [
                'icon' => 'light-bulb',
                'border' => 'border-cyan-200 hover:border-cyan-400 dark:border-cyan-900/70 dark:hover:border-cyan-700',
                'iconBox' => 'bg-cyan-50 text-cyan-700 ring-cyan-100 dark:bg-cyan-950/50 dark:text-cyan-300 dark:ring-cyan-900',
                'chip' => 'bg-cyan-50/80 dark:bg-cyan-950/30',
                'progress' => 'from-cyan-600 to-sky-500',
            ],
        ];
    @endphp

    {{-- Encabezado académico --}}
    <section class="school-hero relative overflow-hidden rounded-[24px] border border-slate-200/80 bg-white px-4 py-5 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:px-6">
        <div class="pointer-events-none absolute -right-12 -top-16 h-44 w-44 rounded-full bg-blue-100/50 blur-3xl dark:bg-blue-500/10"></div>
        <div class="pointer-events-none absolute -bottom-16 right-28 h-36 w-36 rounded-full bg-emerald-100/50 blur-3xl dark:bg-emerald-500/10"></div>

        <div class="relative flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-4">
                <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-lg shadow-blue-600/20 ring-4 ring-blue-50 dark:ring-blue-950">
                    <flux:icon.clipboard-document-check class="size-7" />
                </span>
                <div>
                    <div class="mb-1 flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-[28px]">Calificaciones por docente</h1>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900">Control académico</span>
                    </div>
                    <p class="max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Captura, importa y valida únicamente las materias calificables que están asignadas realmente en los horarios institucionales.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button wire:click="descargarPlantillaDocente" icon="arrow-down-tray" variant="primary" class="school-primary-action cursor-pointer">
                    Plantilla completa del docente
                </flux:button>
                <flux:button wire:click="limpiarFiltros" icon="funnel" class="school-secondary-action cursor-pointer">Limpiar filtros</flux:button>
            </div>
        </div>
    </section>

    {{-- Indicadores --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        @foreach($statCards as $stat)
            <article class="group relative overflow-hidden rounded-2xl border bg-gradient-to-br p-4 shadow-[0_8px_28px_rgba(15,23,42,0.04)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_12px_34px_rgba(15,23,42,0.08)] {{ $stat['box'] }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.13em] text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</div>
                        <div class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ number_format($stat['value']) }}</div>
                        <div class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $stat['caption'] }}</div>
                    </div>
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl {{ $stat['iconBox'] }} ring-1 ring-inset ring-white/70 dark:ring-white/5">
                        <flux:icon :icon="$stat['icon']" class="size-5" />
                    </span>
                </div>
            </article>
        @endforeach
    </div>

    {{-- Filtros --}}
    <section class="school-filter-panel overflow-hidden rounded-[22px] border border-slate-200 bg-white shadow-[0_8px_30px_rgba(15,23,42,0.05)] dark:border-slate-700 dark:bg-slate-900">
        <div class="h-1 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>
        <div class="space-y-4 p-4 sm:p-5">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                    <flux:icon.funnel class="size-4" />
                </span>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Filtros de asignaciones</h2>
                    <p class="text-xs text-slate-400">Ubica rápidamente las materias y grupos que vas a capturar.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-6">
                <flux:field>
                    <flux:label>Docente</flux:label>
                    <flux:select wire:model.live="profesorId" :disabled="$esProfesorRestringido">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach($opciones['profesores'] as $item)
                            <flux:select.option value="{{ $item['id'] }}">{{ $item['nombre'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Licenciatura</flux:label>
                    <flux:select wire:model.live="licenciaturaId">
                        <flux:select.option value="">Todas</flux:select.option>
                        @foreach($opciones['licenciaturas'] as $item)
                            <flux:select.option value="{{ $item['id'] }}">{{ $item['nombre'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Modalidad</flux:label>
                    <flux:select wire:model.live="modalidadId">
                        <flux:select.option value="">Todas</flux:select.option>
                        @foreach($opciones['modalidades'] as $item)
                            <flux:select.option value="{{ $item['id'] }}">{{ $item['nombre'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Generación</flux:label>
                    <flux:select wire:model.live="generacionId">
                        <flux:select.option value="">Todas</flux:select.option>
                        @foreach($opciones['generaciones'] as $item)
                            <flux:select.option value="{{ $item['id'] }}">{{ $item['nombre'] }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="cuatrimestreId">
                        <flux:select.option value="">Todos</flux:select.option>
                        @foreach($opciones['cuatrimestres'] as $item)
                            <flux:select.option value="{{ $item['id'] }}">{{ $item['nombre'] }}°</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Estado</flux:label>
                    <flux:select wire:model.live="estadoFiltro">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="sin_captura">Sin captura</flux:select.option>
                        <flux:select.option value="parcial">Parcial</flux:select.option>
                        <flux:select.option value="completa">Completa</flux:select.option>
                        <flux:select.option value="entregada">Entregada</flux:select.option>
                        <flux:select.option value="validada">Validada</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl bg-slate-50 px-3.5 py-3 dark:bg-slate-800/70 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex items-start gap-2 text-xs leading-5 text-slate-500 dark:text-slate-400 sm:text-sm">
                    <flux:icon.information-circle class="mt-0.5 size-4 shrink-0 text-blue-600 dark:text-blue-400" />
                    <span>La matriz masiva se habilita cuando seleccionas un único docente, licenciatura, modalidad, generación y cuatrimestre.</span>
                </p>
                <flux:button wire:click="abrirMatriz" icon="table-cells" variant="primary" class="school-primary-action shrink-0 cursor-pointer">
                    Captura masiva
                </flux:button>
            </div>
        </div>
    </section>

    {{-- Entrada individual, buscador acotado por permisos y filtros académicos --}}
    <section class="overflow-hidden rounded-[22px] border border-sky-200 bg-white shadow-sm dark:border-sky-900 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-sky-50 to-emerald-50/30 px-4 py-3 dark:from-sky-950/30 dark:to-emerald-950/10 sm:px-5">
            <div class="flex items-center gap-3">
                <span class="rounded-xl bg-white p-2 text-[#006492] shadow-sm dark:bg-slate-800 dark:text-sky-300"><flux:icon.user-circle class="size-5" /></span>
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">Captura individual por estudiante</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Encuentra al alumno y abre todas sus materias autorizadas del cuatrimestre.</p>
                </div>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-[10px] font-bold uppercase tracking-wide text-sky-700 dark:bg-slate-800 dark:text-sky-300">Sin duplicar calificaciones</span>
        </div>
        <div class="space-y-3 p-4 sm:p-5">
            <flux:input wire:model.live.debounce.350ms="buscarAlumnoGlobal" icon="magnifying-glass" placeholder="Buscar por nombre, apellidos, matrícula SEG o ID interno (mínimo 2 caracteres)" />
            @if(mb_strlen(trim($buscarAlumnoGlobal)) >= 2)
                <div wire:loading.flex wire:target="buscarAlumnoGlobal" class="items-center gap-2 text-xs text-sky-700"><flux:icon.arrow-path class="size-4 animate-spin" /> Buscando estudiantes autorizados...</div>
                <div class="max-h-80 space-y-1 overflow-y-auto" aria-label="Coincidencias de alumnos">
                    @forelse($coincidenciasAlumno as $alumnoEncontrado)
                        <button type="button"
                            wire:key="buscar-alumno-{{ $alumnoEncontrado['id'] }}"
                            wire:click="abrirAlumno({{ $alumnoEncontrado['id'] }}, {{ $alumnoEncontrado['asignacion_id'] }}, {{ $alumnoEncontrado['generacion_id'] }})"
                            class="flex w-full cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-100 px-3 py-3 text-left transition hover:border-sky-300 hover:bg-sky-50 dark:border-slate-800 dark:hover:border-sky-800 dark:hover:bg-sky-950/30">
                            <span class="min-w-0">
                                <strong class="block truncate text-sm text-slate-800 dark:text-slate-100">{{ $alumnoEncontrado['nombre'] }}</strong>
                                <span class="block truncate text-xs text-slate-500">ID {{ $alumnoEncontrado['matricula_interna'] ?: '—' }} · SEG {{ $alumnoEncontrado['matricula'] ?: '—' }} · {{ $alumnoEncontrado['grupo'] }}</span>
                            </span>
                            <span class="flex shrink-0 items-center gap-1 text-xs font-bold text-[#006492] dark:text-sky-300">Capturar <flux:icon.chevron-right class="size-4" /></span>
                        </button>
                    @empty
                        <div class="rounded-xl bg-slate-50 px-4 py-6 text-center text-xs text-slate-500 dark:bg-slate-800">No hay estudiantes activos en materias autorizadas con esa búsqueda y filtros.</div>
                    @endforelse
                </div>
            @else
                <p class="text-xs text-slate-400">El docente sólo encuentra alumnos de sus asignaciones en horarios; Control Escolar puede localizar alumnos de todos los docentes.</p>
            @endif
        </div>
    </section>

    {{-- Corrección de notas históricas: acceso administrativo, independiente de los filtros vigentes --}}
    @if($puedeAdministrar)
        <section class="overflow-hidden rounded-[22px] border border-emerald-200 bg-white shadow-sm dark:border-emerald-900 dark:bg-slate-900">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-[#006492]/10 to-[#88AC2E]/10 px-4 py-4 dark:from-sky-900/20 dark:to-lime-900/10 sm:px-5">
                <div class="flex items-center gap-3">
                    <span class="rounded-xl bg-white p-2 text-[#006492] shadow-sm dark:bg-slate-800 dark:text-sky-300"><flux:icon.clock class="size-5" /></span>
                    <div>
                        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">Historial académico completo · Corrección administrativa</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Edita calificaciones de cualquier cuatrimestre registrado, aunque el alumno ya curse otro o esté de baja.</p>
                    </div>
                </div>
                <span class="rounded-full border border-emerald-200 bg-white px-3 py-1 text-[10px] font-extrabold uppercase tracking-wide text-emerald-700 dark:border-emerald-900 dark:bg-slate-800 dark:text-emerald-300">Sólo Control Escolar</span>
            </div>
            <div class="space-y-3 p-4 sm:p-5">
                <flux:input wire:model.live.debounce.400ms="buscarAlumnoHistorico" icon="magnifying-glass"
                    placeholder="Buscar en todas las generaciones por nombre, matrícula o CURP (mínimo 2 caracteres)" />
                @if(mb_strlen(trim($buscarAlumnoHistorico)) >= 2)
                    <div class="max-h-64 space-y-1 overflow-auto">
                        @forelse($coincidenciasHistoricas as $coincidencia)
                            <button type="button" wire:key="historico-buscar-{{ $coincidencia->id }}"
                                wire:click="abrirHistorialAlumno({{ $coincidencia->id }})"
                                class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-left hover:border-[#006492] hover:bg-sky-50 dark:border-slate-700 dark:hover:bg-slate-800">
                                <span class="min-w-0">
                                    <strong class="block truncate text-sm text-slate-900 dark:text-white">{{ $coincidencia->apellido_paterno }} {{ $coincidencia->apellido_materno }} {{ $coincidencia->nombre }}</strong>
                                    <span class="block text-xs text-slate-500">ID: {{ $coincidencia->matricula_interna ?: '—' }} · SEG: {{ $coincidencia->matricula ?: '—' }} · {{ $coincidencia->status === 'true' ? 'Activo' : 'Inactivo' }}</span>
                                </span>
                                <span class="shrink-0 text-xs font-extrabold text-[#006492]">Abrir historial →</span>
                            </button>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-4 text-xs text-slate-500 dark:bg-slate-800">No se encontraron alumnos con esos datos.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </section>

        @if($modo === 'historial' && $alumnoHistorico)
            <section x-on:captura-docente-abierta.window="$nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
                class="scroll-mt-6 overflow-hidden rounded-[22px] border border-sky-200 bg-white shadow-md dark:border-sky-900 dark:bg-slate-900">
                <div class="h-1 bg-gradient-to-r from-[#006492] to-[#88AC2E]"></div>
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/40 sm:p-5">
                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-wider text-[#006492] dark:text-sky-300">Expediente académico histórico</p>
                        <h3 class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ $alumnoHistorico->apellido_paterno }} {{ $alumnoHistorico->apellido_materno }} {{ $alumnoHistorico->nombre }}</h3>
                        <p class="mt-1 text-xs text-slate-500">ID {{ $alumnoHistorico->matricula_interna ?: '—' }} · SEG {{ $alumnoHistorico->matricula ?: '—' }} · Cuatrimestre que cursa: {{ $alumnoHistorico->cuatrimestre?->cuatrimestre ?? '—' }}°</p>
                    </div>
                    <flux:button wire:click="cerrarHistorialAlumno" icon="x-mark" class="cursor-pointer">Cerrar historial</flux:button>
                </div>
                <div class="space-y-4 p-4 sm:p-5">
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs leading-5 text-amber-900 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                        <strong>Corrección extraordinaria:</strong> puedes modificar una nota registrada de cualquier periodo, incluso si fue entregada o validada. Cada corrección requiere un motivo, se registra en auditoría y no cambia la materia, generación, modalidad ni cuatrimestre original. Comprueba los documentos oficiales ya emitidos antes de corregirlos.
                    </div>
                    <flux:field>
                        <flux:label>Motivo de la corrección (obligatorio)</flux:label>
                        <flux:textarea wire:model="motivoCorreccionHistorica" rows="2"
                            placeholder="Ejemplo: Corrección autorizada por Control Escolar, según acta de rectificación..." />
                        <flux:error name="motivoCorreccionHistorica" />
                    </flux:field>
                    <p class="text-xs text-slate-400">Escribe un motivo de 10 a 500 caracteres. El botón de cada fila guarda únicamente esa calificación.</p>

                    @php
                        $gruposHistoricos = collect($historialRegistros)->groupBy(fn ($nota) => implode(' · ', [
                            ($nota['cuatrimestre'] ?? $nota['cuatrimestre_id']).'° cuatrimestre',
                            'Generación '.($nota['generacion'] ?? '#'.$nota['generacion_id']),
                            $nota['licenciatura'] ?? 'Licenciatura no disponible',
                            $nota['modalidad'] ?? 'Modalidad no disponible',
                        ]));
                    @endphp
                    @forelse($gruposHistoricos as $grupoHistorico => $materiasHistoricas)
                        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-100 px-4 py-3 dark:bg-slate-800">
                                <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">{{ $grupoHistorico }}</h4>
                                <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-bold text-[#006492] dark:bg-slate-900 dark:text-sky-300">{{ $materiasHistoricas->count() }} materias registradas</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[700px] text-left text-sm">
                                    <thead class="bg-white text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-900">
                                        <tr><th class="px-4 py-2.5">Materia</th><th class="px-4 py-2.5">Nota guardada</th><th class="px-4 py-2.5">Corrección</th><th class="px-4 py-2.5 text-right">Acción</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        @foreach($materiasHistoricas as $notaHistorica)
                                            @php
                                                $idNota = (int) $notaHistorica['id'];
                                                $cambioPendiente = (string) ($notasHistoricas[$idNota] ?? '') !== (string) ($notasHistoricasGuardadas[$idNota] ?? '');
                                            @endphp
                                            <tr wire:key="historico-nota-{{ $idNota }}">
                                                <td class="px-4 py-3">
                                                    <strong class="block text-slate-800 dark:text-white">{{ $notaHistorica['materia'] ?: 'Materia no disponible' }}</strong>
                                                    <span class="text-xs text-slate-400">{{ $notaHistorica['materia_clave'] ?: 'Sin clave' }} · Registro #{{ $idNota }}</span>
                                                </td>
                                                <td class="px-4 py-3 font-bold text-slate-600 dark:text-slate-300">{{ $notasHistoricasGuardadas[$idNota] ?? '—' }}</td>
                                                <td class="px-4 py-3">
                                                    <input type="text" maxlength="5" inputmode="decimal" placeholder="5–10 / NP"
                                                        aria-label="Calificación de {{ $notaHistorica['materia'] ?: 'materia' }}"
                                                        wire:model.blur="notasHistoricas.{{ $idNota }}"
                                                        class="w-28 rounded-xl border border-slate-300 bg-white px-3 py-2 text-center font-extrabold text-slate-900 outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
                                                    @if($cambioPendiente)<span class="ml-2 text-[10px] font-extrabold text-amber-700">Sin guardar</span>@endif
                                                    @error('notasHistoricas.'.$idNota)<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <flux:button size="sm" variant="primary" icon="check" class="cursor-pointer"
                                                        wire:click="guardarCalificacionHistorica({{ $idNota }})"
                                                        wire:confirm="¿Confirmas la corrección de esta calificación histórica? Se registrará el cambio y el motivo en auditoría.">
                                                        Guardar nota
                                                    </flux:button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">
                            Este alumno no tiene calificaciones previamente registradas. Para crear una nota pendiente utiliza la captura por materia o alumno, conforme a las asignaciones vigentes.
                        </div>
                    @endforelse

                    <details class="rounded-xl border border-slate-200 dark:border-slate-700">
                        <summary class="cursor-pointer p-3 text-sm font-extrabold text-slate-700 dark:text-slate-200">Auditoría de correcciones históricas ({{ $auditoriaHistorica->count() }})</summary>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[600px] text-xs">
                                <thead><tr class="bg-slate-50 dark:bg-slate-800"><th class="p-3 text-left">Fecha</th><th class="p-3 text-left">Usuario</th><th class="p-3 text-left">Cambio</th><th class="p-3 text-left">Motivo</th></tr></thead>
                                <tbody>
                                    @forelse($auditoriaHistorica as $audHist)
                                        <tr class="border-t border-slate-100 dark:border-slate-800">
                                            <td class="p-3">{{ $audHist->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="p-3">{{ $audHist->usuario?->username ?? 'Usuario eliminado' }}</td>
                                            <td class="p-3 font-bold">{{ $audHist->valor_anterior ?? '—' }} → {{ $audHist->valor_nuevo ?? '—' }}</td>
                                            <td class="p-3">{{ data_get($audHist->detalle, 'motivo') ?: 'Sin motivo registrado' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="p-4 text-center text-slate-500">Todavía no se han realizado correcciones históricas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </section>
        @endif
    @endif

    {{-- Materias en horario --}}
    <div class="relative">
        <div wire:loading.flex wire:target="profesorId,licenciaturaId,modalidadId,generacionId,cuatrimestreId,estadoFiltro,cargarMateria,abrirMatriz"
             class="absolute inset-0 z-30 items-start justify-center rounded-2xl bg-white/75 pt-16 backdrop-blur-sm dark:bg-slate-950/70">
            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">Actualizando materias…</span>
            </div>
        </div>

        @if($asignaciones->isEmpty())
            <div class="rounded-[22px] border border-dashed border-slate-300 bg-white/70 p-10 text-center dark:border-slate-700 dark:bg-slate-900/70">
                <span class="mx-auto inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <flux:icon.academic-cap class="size-7" />
                </span>
                <div class="mt-4 text-base font-bold text-slate-700 dark:text-slate-200">No hay materias en horario con los filtros seleccionados.</div>
                <p class="mt-1 text-sm text-slate-500">Las asignaciones sin horario o materias no calificables se excluyen automáticamente.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3">
                @foreach($asignaciones as $item)
                    @php
                        $meta = $estadoMeta[$item->estado] ?? $estadoMeta['sin_captura'];
                        $courseStyle = $courseStyles[$loop->index % count($courseStyles)];
                        $pct = $item->total_alumnos > 0 ? min(100, round(($item->capturadas / $item->total_alumnos) * 100)) : 0;
                    @endphp
                    <button type="button" wire:click="cargarMateria({{ $item->asignacion_materia_id }}, {{ $item->generacion_id }})"
                            wire:key="asig-{{ $item->asignacion_materia_id }}-{{ $item->generacion_id }}"
                            class="group relative overflow-hidden rounded-[20px] border bg-white p-4 text-left shadow-[0_7px_24px_rgba(15,23,42,0.045)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_14px_35px_rgba(15,23,42,0.09)] dark:bg-slate-900 {{ $courseStyle['border'] }}">
                        <div class="flex items-start gap-3">
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 ring-inset {{ $courseStyle['iconBox'] }}">
                                <flux:icon :icon="$courseStyle['icon']" class="size-6" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="line-clamp-2 text-[15px] font-extrabold leading-5 text-slate-900 dark:text-white">{{ $item->materia }}</div>
                                        <div class="mt-1 truncate text-[11px] font-medium text-slate-500">{{ $item->materia_clave ?: 'Sin clave' }} · {{ $item->licenciatura_corta ?: $item->licenciatura }}</div>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                            <div class="rounded-xl p-2.5 {{ $courseStyle['chip'] }}">
                                <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Generación</span>
                                <strong class="mt-0.5 block text-slate-800 dark:text-slate-100">{{ $item->generacion }}</strong>
                            </div>
                            <div class="rounded-xl p-2.5 {{ $courseStyle['chip'] }}">
                                <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Cuatrimestre</span>
                                <strong class="mt-0.5 block truncate text-slate-800 dark:text-slate-100">{{ $item->cuatrimestre }}° · {{ $item->modalidad }}</strong>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center gap-2 text-[11px] font-semibold text-slate-500 dark:text-slate-300">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                                <flux:icon.user class="size-3.5" />
                            </span>
                            <span class="truncate">{{ trim($item->profesor_nombre.' '.$item->profesor_apellido_paterno.' '.$item->profesor_apellido_materno) }}</span>
                        </div>

                        <div class="mt-3">
                            <div class="mb-1.5 flex justify-between text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                <span>Progreso</span><span>{{ $item->capturadas }}/{{ $item->total_alumnos }}</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-gradient-to-r {{ $courseStyle['progress'] }} transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        @if($item->fecha_limite)
                            <div class="mt-3 flex items-center gap-1.5 text-[10px] font-bold {{ $item->bloqueada_docente ? 'text-rose-600' : 'text-slate-400' }}">
                                <flux:icon.calendar-days class="size-3.5" />
                                Fecha límite: {{ \Carbon\Carbon::parse($item->fecha_limite)->format('d/m/Y') }}
                            </div>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Área de captura --}}
    @if(!empty($contextoSeleccionado))
        @php
            $seleccion = (object) $contextoSeleccionado;
            $estadoActual = $estadoSeleccionado['estado'] ?? ($seleccion->estado ?? 'sin_captura');
            $metaSeleccion = $estadoMeta[$estadoActual] ?? $estadoMeta['sin_captura'];
        @endphp

        <section x-on:captura-docente-abierta.window="$nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start' }))" class="school-gradebook scroll-mt-6 overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-[0_14px_45px_rgba(15,23,42,0.08)] dark:border-slate-700 dark:bg-slate-900">
            <div class="h-1 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>

            <div class="flex flex-col gap-4 border-b border-slate-200 bg-gradient-to-r from-slate-50/80 via-white to-blue-50/30 p-4 dark:border-slate-700 dark:from-slate-900 dark:via-slate-900 dark:to-blue-950/20 sm:p-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100 dark:bg-blue-950/50 dark:text-blue-300 dark:ring-blue-900">
                        <flux:icon.book-open class="size-5" />
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-[22px]">
                                {{ $modo === 'matriz' ? 'Matriz académica' : ($modo === 'alumno' ? 'Expediente de captura del alumno' : $seleccion->materia) }}
                            </h2>
                            @if($modo === 'materia')
                                <span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $metaSeleccion['class'] }}">{{ $metaSeleccion['label'] }}</span>
                            @else
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-[10px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-200">{{ $modo === 'alumno' ? 'Vista individual' : 'Varias materias' }}</span>
                            @endif
                        </div>
                        <p class="mt-1 text-xs leading-5 text-slate-500 sm:text-sm">
                            {{ $seleccion->licenciatura }} · {{ $seleccion->modalidad }} · Generación {{ $seleccion->generacion }} · {{ $seleccion->cuatrimestre }}° cuatrimestre
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($edicionesSinGuardar > 0)
                        <span class="rounded-xl bg-amber-100 px-3 py-2 text-xs font-extrabold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">{{ $edicionesSinGuardar }} cambio(s) sin guardar</span>
                    @endif
                    @if($modo === 'materia')
                        <flux:button wire:click="descargarPlantillaMateria" icon="arrow-down-tray" class="school-secondary-action cursor-pointer">Plantilla de materia</flux:button>
                        <a target="_blank" href="{{ route('admin.calificaciones-docente.pdf', ['asignacion_materia_id' => $seleccion->asignacion_materia_id, 'generacion_id' => $seleccion->generacion_id]) }}"
                           class="school-file-button school-file-pdf inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-bold transition">
                            <flux:icon.document-text class="size-4" /> PDF
                        </a>
                        <a href="{{ route('admin.calificaciones-docente.excel', ['asignacion_materia_id' => $seleccion->asignacion_materia_id, 'generacion_id' => $seleccion->generacion_id]) }}"
                           class="school-file-button school-file-excel inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-bold transition">
                            <flux:icon.table-cells class="size-4" /> Excel
                        </a>
                    @endif
                    @if($edicionesSinGuardar > 0)
                        <flux:button wire:click="cerrarCaptura" wire:confirm="Hay cambios sin guardar. ¿Cerrar y descartarlos?" icon="x-mark" class="school-secondary-action cursor-pointer">Cerrar</flux:button>
                    @else
                        <flux:button wire:click="cerrarCaptura" icon="x-mark" class="school-secondary-action cursor-pointer">Cerrar</flux:button>
                    @endif
                </div>
            </div>

            @if($modo === 'materia')
                <div class="grid grid-cols-2 gap-3 border-b border-slate-200 p-4 dark:border-slate-700 md:grid-cols-4 sm:p-5">
                    <div class="school-context-card col-span-2 md:col-span-1">
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"><flux:icon.user class="size-3.5" />Docente</div>
                        <div class="mt-1.5 line-clamp-2 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ trim($seleccion->profesor_nombre.' '.$seleccion->profesor_apellido_paterno.' '.$seleccion->profesor_apellido_materno) }}</div>
                    </div>
                    <div class="school-context-card">
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"><flux:icon.check-circle class="size-3.5" />Captura</div>
                        <div class="mt-1.5 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ $estadoSeleccionado['capturadas'] ?? 0 }}/{{ $estadoSeleccionado['total'] ?? 0 }} <span class="font-semibold text-slate-400">calificaciones</span></div>
                    </div>
                    <div class="school-context-card">
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"><flux:icon.clock class="size-3.5" />Pendientes</div>
                        <div class="mt-1.5 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ $estadoSeleccionado['pendientes'] ?? 0 }}</div>
                    </div>
                    <div class="school-context-card">
                        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"><flux:icon.calendar-days class="size-3.5" />Fecha límite</div>
                        <div class="mt-1.5 text-sm font-extrabold text-slate-800 dark:text-slate-100">{{ !empty($estadoSeleccionado['fecha_limite']) ? \Carbon\Carbon::parse($estadoSeleccionado['fecha_limite'])->format('d/m/Y') : 'Sin límite' }}</div>
                    </div>
                </div>

                @if($puedeAdministrar)
                    <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/60 p-4 dark:border-slate-700 dark:bg-slate-800/30 lg:flex-row lg:items-end lg:justify-between sm:p-5">
                        <div class="w-full max-w-xs">
                            <flux:field>
                                <flux:label>Fecha límite de captura</flux:label>
                                <flux:input type="date" wire:model="fechaLimite" />
                                <flux:error name="fechaLimite" />
                            </flux:field>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <flux:button wire:click="guardarFechaLimite" icon="calendar-days" class="school-secondary-action cursor-pointer">Guardar límite</flux:button>
                            @if(in_array($estadoActual, ['entregada', 'validada']))
                                <flux:button wire:click="reabrirCaptura" icon="arrow-path" class="cursor-pointer">Reabrir captura</flux:button>
                            @endif
                            @if($estadoActual === 'entregada')
                                <flux:button wire:click="validarEntrega" icon="check-badge" variant="primary" class="school-primary-action cursor-pointer">Validar entrega</flux:button>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            @if($modo !== 'alumno')
            <div class="grid grid-cols-1 gap-4 border-b border-slate-200 p-4 dark:border-slate-700 xl:grid-cols-12 sm:p-5">
                <div class="xl:col-span-5">
                    <flux:label>Buscar alumno</flux:label>
                    <flux:input wire:model.live.debounce.250ms="buscarAlumno" icon="magnifying-glass" placeholder="Matrícula, nombre o apellidos" />
                </div>
                <div class="xl:col-span-7">
                    <flux:label>Importar plantilla Excel</flux:label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <input type="file" wire:model="archivoExcel" accept=".xlsx,.xls"
                               class="school-file-input block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:font-bold file:text-blue-700 hover:file:bg-blue-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:file:bg-blue-950/50 dark:file:text-blue-300" />
                        <flux:button wire:click="previsualizarImportacion" icon="document-magnifying-glass" variant="primary" class="school-primary-action cursor-pointer whitespace-nowrap">Vista previa</flux:button>
                    </div>
                    <flux:error name="archivoExcel" />
                </div>
            </div>

            <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/60 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/20 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <p class="text-sm font-extrabold text-slate-800 dark:text-slate-100">Origen de estudiantes</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Se guardarán únicamente calificaciones de los {{ $alumnosVisibles->count() }} alumnos visibles.</p>
                </div>
                <div class="inline-flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-white p-1 dark:border-slate-600 dark:bg-slate-900" role="group" aria-label="Origen del estudiante">
                    @foreach (['todos' => 'Todos', 'local' => 'Locales', 'foraneo' => 'Foráneos'] as $clave => $etiqueta)
                        <button type="button" wire:click="$set('origenFiltro', '{{ $clave }}')"
                            aria-pressed="{{ $origenFiltro === $clave ? 'true' : 'false' }}"
                            class="cursor-pointer rounded-lg px-3 py-2 text-xs font-bold transition {{ $origenFiltro === $clave ? 'bg-[#006492] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                            {{ $etiqueta }} <span class="ml-1 opacity-70">{{ $conteosOrigen[$clave] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 bg-white px-4 py-3 dark:border-slate-700 dark:bg-slate-900 sm:px-5">
                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Mostrar estudiantes</span>
                <div class="inline-flex flex-wrap gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                    @foreach(['todos' => 'Todos', 'pendientes' => 'Con pendientes', 'reprobadas' => 'Con notas menores a 6'] as $clave => $etiqueta)
                        <button type="button" wire:click="$set('pendientesFiltro', '{{ $clave }}')"
                            class="cursor-pointer rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $pendientesFiltro === $clave ? 'bg-[#006492] text-white' : 'text-slate-600 hover:bg-white dark:text-slate-200 dark:hover:bg-slate-700' }}">{{ $etiqueta }}</button>
                    @endforeach
                </div>
            </div>
            @endif

            @if($modo === 'alumno')
                @php $alumnoIndividual = $alumnos[0] ?? null; @endphp
                @if($alumnoIndividual)
                    <div class="space-y-4 p-4 sm:p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900 dark:bg-blue-950/20">
                            <div>
                                <div class="text-[10px] font-extrabold uppercase tracking-widest text-sky-700 dark:text-sky-300">Estudiante seleccionado</div>
                                <div class="mt-1 text-lg font-black text-slate-900 dark:text-white">{{ $alumnoIndividual['apellido_paterno'] }} {{ $alumnoIndividual['apellido_materno'] }} {{ $alumnoIndividual['nombre'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">ID: {{ $alumnoIndividual['matricula_interna'] ?: '—' }} · SEG: {{ $alumnoIndividual['matricula'] ?: 'Pendiente' }} · {{ $alumnoIndividual['foraneo'] === 'true' ? 'Foráneo' : 'Local' }}</div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-xl bg-white px-3 py-2 text-sm font-bold text-[#006492] dark:bg-slate-800 dark:text-sky-300">{{ count($asignacionesMatriz) }} materias autorizadas</span>
                                @if($puedeAdministrar)
                                    <flux:button wire:click="abrirHistorialAlumno({{ $alumnoIndividual['id'] }})" icon="clock" class="cursor-pointer">Ver todos los cuatrimestres</flux:button>
                                @endif
                            </div>
                        </div>

                        @if($diagnosticoAlumno)
                            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 dark:border-slate-700 dark:bg-slate-800/50">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500">Promedio registrado del cuatrimestre</span>
                                    <strong class="mt-1 block text-2xl font-black text-[#006492] dark:text-sky-300">{{ $diagnosticoAlumno['actual']['promedio'] ?? '—' }}</strong>
                                    <span class="text-[11px] text-slate-400">Sólo notas numéricas registradas; no es promedio oficial de boleta</span>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 dark:border-slate-700 dark:bg-slate-800/50">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500">Notas bajo 6 en este cuatrimestre</span>
                                    <strong class="mt-1 block text-2xl font-black text-slate-800 dark:text-slate-100">{{ $diagnosticoAlumno['actual']['reprobadas'] ?? '—' }}</strong>
                                    <span class="text-[11px] text-slate-400">NP no se incluye como nota numérica</span>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3.5 dark:border-slate-700 dark:bg-slate-800/50">
                                    <span class="block text-[10px] font-bold uppercase text-slate-500">Cuatrimestres con historial</span>
                                    <strong class="mt-1 block text-2xl font-black text-slate-800 dark:text-slate-100">{{ count($diagnosticoAlumno['historial']) }}</strong>
                                    <span class="text-[11px] text-slate-400">Evolución académica observable</span>
                                </div>
                            </div>
                            @if(count($diagnosticoAlumno['alertas']))
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
                                    <strong class="flex items-center gap-2 text-sm text-amber-900 dark:text-amber-200"><flux:icon.exclamation-triangle class="size-4" /> Alertas académicas orientativas</strong>
                                    <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-amber-800 dark:text-amber-300">
                                        @foreach($diagnosticoAlumno['alertas'] as $alerta)<li>{{ $alerta }}</li>@endforeach
                                    </ul>
                                    <p class="mt-2 text-[11px] text-amber-700 dark:text-amber-400">Son reglas de seguimiento, no predicciones ni decisiones automáticas de aprobación.</p>
                                </div>
                            @elseif($diagnosticoAlumno['sin_datos'])
                                <div class="rounded-xl border border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700">Todavía no hay notas numéricas registradas para calcular indicadores del cuatrimestre.</div>
                            @endif
                        @endif

                        <div class="space-y-2">
                            @foreach($asignacionesMatriz as $materia)
                                @php
                                    $asigId = (int) $materia['asignacion_materia_id'];
                                    $valorIndividual = $calificaciones[$alumnoIndividual['id']][$asigId] ?? null;
                                    $bloqueada = in_array($materia['estado'] ?? 'sin_captura', ['entregada', 'validada'], true) || (!$puedeAdministrar && !empty($materia['bloqueada_docente']));
                                    $metaMateria = $estadoMeta[$materia['estado'] ?? 'sin_captura'] ?? $estadoMeta['sin_captura'];
                                @endphp
                                <div wire:key="individual-{{ $alumnoIndividual['id'] }}-{{ $asigId }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-3.5 dark:border-slate-700 dark:bg-slate-900 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0 lg:max-w-[52%]">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $materia['materia'] }}</div>
                                        <div class="mt-1 text-xs text-slate-500">{{ $materia['materia_clave'] ?: 'Sin clave' }} · {{ $materia['profesor_nombre'] }} {{ $materia['profesor_apellido_paterno'] }}</div>
                                        <span class="mt-2 inline-block rounded-full px-2 py-1 text-[10px] font-bold {{ $metaMateria['class'] }}">{{ $metaMateria['label'] }}</span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <label for="nota-alumno-{{ $asigId }}" class="text-xs font-bold text-slate-500">Calificación</label>
                                        <input id="nota-alumno-{{ $asigId }}" type="text" inputmode="decimal" maxlength="5"
                                            wire:model.blur="calificaciones.{{ $alumnoIndividual['id'] }}.{{ $asigId }}"
                                            @disabled($bloqueada) placeholder="5-10 / NP"
                                            class="school-grade-input w-28 rounded-xl border border-slate-300 px-3 py-2 text-center text-sm font-extrabold uppercase text-slate-800 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 disabled:opacity-50 dark:border-slate-600 dark:bg-slate-800 dark:text-white" />
                                        @if((string) ($calificacionesGuardadas[$alumnoIndividual['id']][$asigId] ?? '') !== (string) ($valorIndividual ?? ''))
                                            <span class="rounded-lg bg-amber-100 px-2 py-1 text-[10px] font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Sin guardar</span>
                                        @endif
                                        <flux:button wire:click="guardarCalificacionIndividual({{ $alumnoIndividual['id'] }}, {{ $asigId }})" :disabled="$bloqueada" icon="check" variant="primary" class="school-primary-action cursor-pointer">Guardar nota</flux:button>
                                        @if(($calificacionesGuardadas[$alumnoIndividual['id']][$asigId] ?? null) !== null && !$bloqueada)
                                            <button type="button" wire:click="eliminarCalificacion({{ $alumnoIndividual['id'] }}, {{ $asigId }})" wire:confirm="¿Eliminar esta nota del registro académico? Se registrará en auditoría." title="Eliminar nota guardada" class="cursor-pointer rounded-lg px-2 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30">Eliminar</button>
                                        @endif
                                        @if($bloqueada)
                                            <flux:button wire:click="cargarMateria({{ $asigId }}, {{ $materia['generacion_id'] }})" icon="lock-closed" size="sm" class="cursor-pointer">Ver estado</flux:button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/30">
                            <p class="text-xs text-slate-500">Cada botón guarda sólo esa materia. Las notas se reflejan en la misma tabla utilizada por boletas y certificados.</p>
                            <flux:button wire:click="guardarAlumnoFila({{ $alumnoIndividual['id'] }})" icon="check-circle" variant="primary" class="school-save-action cursor-pointer">Guardar todas las materias editables</flux:button>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-slate-500">El alumno ya no pertenece a este grupo activo.</div>
                @endif
            @else
            <div class="relative">
                <div wire:loading.flex wire:target="guardarCalificaciones,guardarAlumnoFila,guardarCalificacionIndividual,eliminarCalificacion,previsualizarImportacion,confirmarImportacion,entregar,validarEntrega,reabrirCaptura"
                     class="absolute inset-0 z-40 items-start justify-center bg-white/80 pt-20 backdrop-blur-sm dark:bg-slate-950/75">
                    <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-xl dark:border-slate-700 dark:bg-slate-900">
                        <svg class="h-5 w-5 animate-spin text-blue-600" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Procesando…</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="school-grade-table w-full min-w-[900px] text-sm">
                        <thead>
                            <tr>
                                <th class="w-14 px-4 py-3 text-left">#</th>
                                <th class="px-4 py-3 text-left">Identificadores</th>
                                <th class="px-4 py-3 text-left">Alumno</th>
                                <th class="min-w-[180px] px-3 py-3 text-center">Acciones individuales</th>
                                @foreach($asignacionesMatriz as $materia)
                                    <th class="min-w-[180px] px-3 py-3 text-center">
                                        <span class="block">{{ $materia['materia'] }}</span>
                                        @if(count($asignacionesMatriz) > 1)<span class="mt-0.5 block text-[10px] font-semibold normal-case tracking-normal text-slate-400">{{ $materia['materia_clave'] ?: 'Sin clave' }}</span>@endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alumnosVisibles as $index => $alumno)
                                <tr wire:key="alumno-cal-{{ $alumno['id'] }}">
                                    <td class="px-4 py-3 text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-slate-700 dark:text-slate-100">
                                        <div class="font-extrabold">ID: {{ $alumno['matricula_interna'] ?: '—' }}</div>
                                        <div class="mt-0.5 text-[10px] font-semibold text-slate-400">SEG: {{ $alumno['matricula'] ?: 'Pendiente' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-slate-800 dark:text-white">{{ $alumno['apellido_paterno'] }} {{ $alumno['apellido_materno'] }} {{ $alumno['nombre'] }}</div>
                                        <div class="mt-0.5 inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $alumno['foraneo'] === 'true' ? 'bg-amber-400' : 'bg-emerald-400' }}"></span>
                                            {{ $alumno['foraneo'] === 'true' ? 'Foráneo' : 'Local' }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap items-center justify-center gap-1.5">
                                            <button type="button" wire:click="abrirAlumno({{ $alumno['id'] }}, {{ $asignacionesMatriz[0]['asignacion_materia_id'] }}, {{ $asignacionesMatriz[0]['generacion_id'] }})"
                                                title="Abrir todas las materias del alumno" class="cursor-pointer rounded-lg border border-sky-200 px-2.5 py-2 text-[11px] font-bold text-[#006492] transition hover:bg-sky-50 dark:border-sky-900 dark:text-sky-300 dark:hover:bg-sky-950/30">Ver alumno</button>
                                            @if($puedeAdministrar)
                                                <button type="button" wire:click="abrirHistorialAlumno({{ $alumno['id'] }})"
                                                    class="cursor-pointer rounded-lg border border-violet-200 px-2.5 py-2 text-[11px] font-bold text-violet-700 transition hover:bg-violet-50 dark:border-violet-900 dark:text-violet-300 dark:hover:bg-violet-950/30">Historial</button>
                                            @endif
                                            <button type="button" wire:click="guardarAlumnoFila({{ $alumno['id'] }})"
                                                title="Guardar calificaciones editadas de este alumno" class="cursor-pointer rounded-lg border border-emerald-200 px-2.5 py-2 text-[11px] font-bold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900 dark:text-emerald-300 dark:hover:bg-emerald-950/30">Guardar fila</button>
                                        </div>
                                    </td>
                                    @foreach($asignacionesMatriz as $materia)
                                        @php
                                            $valor = $calificaciones[$alumno['id']][$materia['asignacion_materia_id']] ?? null;
                                            $bloqueada = in_array(($materia['estado'] ?? 'sin_captura'), ['entregada', 'validada'], true) || (!$puedeAdministrar && !empty($materia['bloqueada_docente']));
                                        @endphp
                                        <td class="px-3 py-3" wire:key="cal-{{ $alumno['id'] }}-{{ $materia['asignacion_materia_id'] }}">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <input type="text" inputmode="decimal" maxlength="5"
                                                       wire:model.blur="calificaciones.{{ $alumno['id'] }}.{{ $materia['asignacion_materia_id'] }}"
                                                       @disabled($bloqueada)
                                                       placeholder="5-10 / NP"
                                                       class="school-grade-input w-28 rounded-xl border border-slate-300 bg-white px-2.5 py-2.5 text-center text-sm font-extrabold uppercase text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-white dark:disabled:bg-slate-800/50" />
                                                @if(($calificacionesGuardadas[$alumno['id']][$materia['asignacion_materia_id']] ?? null) !== null && !$bloqueada)
                                                    <button type="button" wire:click="eliminarCalificacion({{ $alumno['id'] }}, {{ $materia['asignacion_materia_id'] }})"
                                                            wire:confirm="¿Eliminar esta nota del registro académico? Se registrará en auditoría."
                                                            title="Eliminar explícitamente la calificación"
                                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-300 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30">×</button>
                                                @endif
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="{{ 4 + count($asignacionesMatriz) }}" class="px-4 py-12 text-center text-slate-500">No hay alumnos activos que coincidan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/70 p-4 dark:border-slate-700 dark:bg-slate-800/30 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <p class="flex items-start gap-2 text-xs leading-5 text-slate-500">
                    <flux:icon.information-circle class="mt-0.5 size-4 shrink-0 text-blue-500" />
                    <span><strong>Vacío = no modificar.</strong> El guardado solo afecta alumnos visibles; para borrar una calificación existente usa ×.</span>
                </p>
                <div class="flex flex-wrap gap-2">
                    @if($modo === 'materia' && $estadoActual === 'completa')
                        <flux:button wire:click="entregar" icon="paper-airplane" class="cursor-pointer">Entregar calificaciones</flux:button>
                    @endif
                    <flux:button wire:click="guardarCalificaciones" icon="check" variant="primary" class="school-save-action cursor-pointer">Guardar cambios</flux:button>
                </div>
            </div>

            @endif {{-- / modo alumno --}}

            @if(in_array($modo, ['materia', 'alumno'], true))
                <details class="border-t border-slate-200 dark:border-slate-700">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/60 sm:px-5">
                        <span class="flex items-center gap-2"><flux:icon.clock class="size-4 text-slate-400" />Historial de cambios {{ $modo === 'alumno' ? 'del estudiante' : 'de la materia' }} ({{ $auditoriaReciente->count() }})</span>
                        <flux:icon.chevron-down class="size-4 text-slate-400" />
                    </summary>
                    <div class="overflow-x-auto border-t border-slate-200 dark:border-slate-700">
                        <table class="school-audit-table w-full min-w-[850px] text-xs">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2.5 text-left">Fecha</th>
                                    <th class="px-3 py-2.5 text-left">Alumno</th>
                                    <th class="px-3 py-2.5 text-left">Usuario</th>
                                    <th class="px-3 py-2.5 text-left">Origen</th>
                                    <th class="px-3 py-2.5 text-left">Acción</th>
                                    <th class="px-3 py-2.5 text-center">Cambio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auditoriaReciente as $audit)
                                    <tr>
                                        <td class="whitespace-nowrap px-3 py-2.5">{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                                        <td class="px-3 py-2.5">
                                            <strong>ID: {{ $audit->alumno?->matricula_interna ?? '—' }}</strong><br>
                                            <span class="text-[10px] text-slate-400">SEG: {{ $audit->alumno?->matricula ?? 'Pendiente' }}</span><br>
                                            {{ trim(($audit->alumno?->apellido_paterno ?? '').' '.($audit->alumno?->apellido_materno ?? '').' '.($audit->alumno?->nombre ?? '')) ?: 'Alumno eliminado' }}
                                        </td>
                                        <td class="px-3 py-2.5">{{ $audit->usuario?->username ?? 'Usuario eliminado' }}</td>
                                        <td class="px-3 py-2.5"><span class="rounded-full bg-slate-100 px-2 py-1 font-bold uppercase text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $audit->origen }}</span></td>
                                        <td class="px-3 py-2.5">{{ ucfirst($audit->accion) }}</td>
                                        <td class="px-3 py-2.5 text-center font-extrabold">{{ $audit->valor_anterior ?? '—' }} → {{ $audit->valor_nuevo ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-3 py-7 text-center text-slate-500">Aún no hay cambios registrados desde este módulo.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </section>
    @endif

    {{-- Vista previa de Excel --}}
    @if(!empty($vistaPreviaImportacion))
        <section class="overflow-hidden rounded-[22px] border border-sky-200 bg-white shadow-[0_10px_35px_rgba(14,165,233,0.08)] dark:border-sky-900 dark:bg-slate-900">
            <div class="flex flex-col gap-3 bg-gradient-to-r from-sky-50 to-blue-50/40 p-4 dark:from-sky-950/30 dark:to-blue-950/20 lg:flex-row lg:items-center lg:justify-between sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/50 dark:text-sky-300"><flux:icon.document-magnifying-glass class="size-5" /></span>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Vista previa de importación</h3>
                        <p class="text-sm text-slate-500">Nada se guarda hasta presionar “Confirmar importación”.</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-white px-3 py-1.5 text-emerald-700 ring-1 ring-inset ring-emerald-100 dark:bg-slate-900 dark:text-emerald-300 dark:ring-emerald-900">Nuevas: {{ $resumenImportacion['crear'] ?? 0 }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5 text-blue-700 ring-1 ring-inset ring-blue-100 dark:bg-slate-900 dark:text-blue-300 dark:ring-blue-900">Cambios: {{ $resumenImportacion['actualizar'] ?? 0 }}</span>
                    <span class="rounded-full bg-white px-3 py-1.5 text-slate-600 ring-1 ring-inset ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700">Sin cambio: {{ $resumenImportacion['sin_cambio'] ?? 0 }}</span>
                    <span class="rounded-full bg-rose-100 px-3 py-1.5 text-rose-700 dark:bg-rose-950/50 dark:text-rose-200">Errores: {{ $resumenImportacion['errores'] ?? 0 }}</span>
                </div>
            </div>

            <div class="max-h-[420px] overflow-auto border-y border-slate-200 dark:border-slate-700">
                <table class="school-audit-table w-full min-w-[950px] text-xs">
                    <thead class="sticky top-0 z-10">
                        <tr><th class="px-3 py-2.5 text-left">Hoja/fila</th><th class="px-3 py-2.5 text-left">Alumno</th><th class="px-3 py-2.5 text-left">Materia</th><th class="px-3 py-2.5 text-center">Anterior</th><th class="px-3 py-2.5 text-center">Nueva</th><th class="px-3 py-2.5 text-left">Resultado</th></tr>
                    </thead>
                    <tbody>
                        @foreach($vistaPreviaImportacion as $fila)
                            <tr class="{{ $fila['accion'] === 'error' ? '!bg-rose-50 dark:!bg-rose-950/20' : '' }}">
                                <td class="px-3 py-2.5">{{ $fila['hoja'] }} / {{ $fila['fila'] }}</td>
                                <td class="px-3 py-2.5"><strong>{{ $fila['identificador'] }}</strong><br>{{ $fila['alumno'] }}</td>
                                <td class="px-3 py-2.5">{{ $fila['materia'] }}</td>
                                <td class="px-3 py-2.5 text-center">{{ $fila['anterior'] ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-center font-extrabold">{{ $fila['nuevo'] ?? '—' }}</td>
                                <td class="px-3 py-2.5">{{ $fila['mensaje'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-2 p-4 sm:p-5">
                <flux:button wire:click="cancelarImportacion" class="cursor-pointer">Cancelar</flux:button>
                <flux:button wire:click="confirmarImportacion" variant="primary" icon="check-circle" class="school-primary-action cursor-pointer" :disabled="($resumenImportacion['errores'] ?? 0) > 0">Confirmar importación</flux:button>
            </div>
        </section>
    @endif
</div>
