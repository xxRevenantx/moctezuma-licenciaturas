<div class="space-y-5">
    <div class="rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 via-white to-lime-50 p-4 shadow-sm dark:border-sky-900/50 dark:from-sky-950/25 dark:via-neutral-900 dark:to-lime-950/20">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#006492] text-white shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 15v3m5-8v8m5-13v13" /></svg>
                </span>
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white">Estadística general por grados</h3>
                    <p class="mt-1 max-w-2xl text-sm text-neutral-600 dark:text-neutral-300">
                        Matrícula vigente por licenciatura y grado escolar, desglosada en hombres y mujeres.
                        Sólo generaciones con periodo académico en curso.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ $this->pdfVistaUrl }}" target="_blank" variant="filled" icon="eye">Vista previa PDF</flux:button>
                <flux:button href="{{ $this->pdfDescargaUrl }}" variant="primary" icon="download">Descargar PDF</flux:button>
                <flux:button href="{{ $this->wordUrl }}" variant="primary" icon="document-text" class="!bg-[#006492] hover:!bg-[#00547b]">Descargar Word</flux:button>
                <flux:button href="{{ $this->excelUrl }}" variant="primary" icon="sheet" class="!bg-[#88AC2E] hover:!bg-[#779929]">Descargar Excel</flux:button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        @include('livewire.admin.documentacion.partials.filtro-multiple-estadistica-grados', [
            'titulo' => 'Licenciaturas', 'grupo' => 'licenciaturas', 'opciones' => $licenciaturas,
            'seleccion' => $licenciaturasSeleccionadas, 'propiedad' => 'licenciaturasSeleccionadas', 'campo' => 'nombre',
        ])
        @include('livewire.admin.documentacion.partials.filtro-multiple-estadistica-grados', [
            'titulo' => 'Modalidades', 'grupo' => 'modalidades', 'opciones' => $modalidades,
            'seleccion' => $modalidadesSeleccionadas, 'propiedad' => 'modalidadesSeleccionadas', 'campo' => 'nombre',
        ])
        @include('livewire.admin.documentacion.partials.filtro-multiple-estadistica-grados', [
            'titulo' => 'Generaciones vigentes', 'grupo' => 'generaciones', 'opciones' => $generaciones,
            'seleccion' => $generacionesSeleccionadas, 'propiedad' => 'generacionesSeleccionadas', 'campo' => 'generacion',
        ])
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-sky-100 bg-sky-50/70 px-3 py-2 text-xs text-sky-900 dark:border-sky-900/50 dark:bg-sky-950/25 dark:text-sky-200">
        <span class="flex items-center gap-1.5 font-medium">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            La vista previa, PDF, Word y Excel utilizarán exactamente esta selección.
        </span>
        <span class="font-semibold">
            {{ count($licenciaturasSeleccionadas) }} licenciatura(s) · {{ count($modalidadesSeleccionadas) }} modalidad(es) · {{ count($generacionesSeleccionadas) }} generación(es)
        </span>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-neutral-800 dark:bg-neutral-900/60">
        <div class="flex flex-wrap items-center gap-4">
            <flux:switch wire:model.live="mostrar_grados_vacios" label="Mostrar grados sin alumnos" />
            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                1.º: 1–3 · 2.º: 4–6 · 3.º: 7–9 cuatrimestre
            </span>
        </div>
        <flux:button wire:click="limpiarFiltros" variant="ghost" icon="x-mark">Limpiar filtros</flux:button>
    </div>

    @php($reporte = $this->reporte)
    @php($totales = $reporte['totales'])
    @if(($reporte['alertas_avance']['total_pendientes'] ?? 0) > 0)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:bg-amber-950/25" role="alert">
            <div><p class="font-bold text-amber-900 dark:text-amber-200">Advertencia: {{ $reporte['alertas_avance']['total_pendientes'] }} alumnos requieren revisión de cuatrimestre</p>
                <p class="text-xs text-amber-800 dark:text-amber-300">Los alumnos siguen contándose en el grado registrado. Revisa los desfases antes de presentar o descargar el reporte oficial.</p></div>
            <flux:button href="{{ route('admin.avance-academico') }}" variant="filled" icon="exclamation-triangle">Revisar avance</flux:button>
        </div>
    @endif
    @if(count($reporte['alertas_avance']['conflictos'] ?? []))
        <div class="rounded-xl border border-rose-300 bg-rose-50 p-3 text-sm text-rose-700">Se detectaron calendarios con periodos superpuestos. Verifica las fechas en Administración → Periodos.</div>
    @endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/50 dark:bg-sky-950/25">
            <div class="text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">Hombres</div>
            <div class="mt-1 text-3xl font-black text-sky-950 dark:text-sky-100">{{ $totales['hombres'] }}</div>
        </div>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-4 dark:border-violet-900/50 dark:bg-violet-950/25">
            <div class="text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">Mujeres</div>
            <div class="mt-1 text-3xl font-black text-violet-950 dark:text-violet-100">{{ $totales['mujeres'] }}</div>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/25">
            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total de alumnos</div>
            <div class="mt-1 text-3xl font-black text-emerald-950 dark:text-emerald-100">{{ $totales['total'] }}</div>
        </div>
        <div class="rounded-2xl border border-lime-200 bg-lime-50 p-4 dark:border-lime-900/50 dark:bg-lime-950/25">
            <div class="text-xs font-semibold uppercase tracking-wide text-lime-700 dark:text-lime-300">Licenciaturas</div>
            <div class="mt-1 text-3xl font-black text-lime-950 dark:text-lime-100">{{ count($reporte['licenciaturas']) }}</div>
        </div>
    </div>

    <div wire:loading.flex wire:target="licenciaturasSeleccionadas,modalidadesSeleccionadas,generacionesSeleccionadas,seleccionarTodo,quitarTodo,mostrar_grados_vacios,limpiarFiltros"
        class="items-center justify-center rounded-2xl border border-neutral-200 py-9 dark:border-neutral-800">
        <div class="text-center text-sm text-neutral-500">Actualizando matrícula vigente…</div>
    </div>

    <div wire:loading.remove wire:target="licenciaturasSeleccionadas,modalidadesSeleccionadas,generacionesSeleccionadas,seleccionarTodo,quitarTodo,mostrar_grados_vacios,limpiarFiltros" class="space-y-4">
        @forelse ($reporte['licenciaturas'] as $licenciatura)
            <section wire:key="estadistica-grados-{{ $licenciatura['id'] }}"
                class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-[#006492] px-4 py-3 text-white">
                    <h4 class="text-sm font-bold uppercase tracking-wide">{{ $licenciatura['nombre'] }}</h4>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $licenciatura['totales']['total'] }} alumnos</span>
                </div>
                <div class="grid gap-3 p-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($licenciatura['grados'] as $grado)
                        <div class="overflow-hidden rounded-lg border border-neutral-300 dark:border-neutral-700">
                            <div class="bg-[#bedaa9] py-1.5 text-center text-sm font-bold text-neutral-900">{{ $grado['grado'] }}.º GRADO</div>
                            <div class="grid grid-cols-3 border-y border-neutral-300 bg-[#b7c9e7] text-center text-xs font-bold text-neutral-900">
                                <div class="border-r border-neutral-300 py-1.5">HOMBRES</div>
                                <div class="border-r border-neutral-300 py-1.5">MUJERES</div>
                                <div class="py-1.5">TOTAL</div>
                            </div>
                            <div class="grid grid-cols-3 text-center text-base font-bold text-neutral-900 dark:text-neutral-100">
                                <div class="border-r border-neutral-200 py-2 dark:border-neutral-700">{{ $grado['hombres'] }}</div>
                                <div class="border-r border-neutral-200 py-2 dark:border-neutral-700">{{ $grado['mujeres'] }}</div>
                                <div class="bg-lime-50 py-2 text-[#305a10] dark:bg-lime-950/30 dark:text-lime-200">{{ $grado['total'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-neutral-300 p-10 text-center dark:border-neutral-700">
                <p class="font-semibold text-neutral-700 dark:text-neutral-200">Sin alumnos activos para la selección actual</p>
                <p class="mt-2 text-sm text-neutral-500">Selecciona al menos una licenciatura, modalidad y generación vigente; comprueba también los periodos académicos y las inscripciones.</p>
            </div>
        @endforelse
        <div class="flex flex-wrap justify-between gap-2 rounded-xl bg-neutral-100 px-4 py-3 text-sm font-semibold text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100">
            <span>Total general de alumnos: {{ $totales['total'] }}</span>
            <span class="text-xs font-normal text-neutral-500 dark:text-neutral-400">Corte: {{ \Carbon\Carbon::parse($reporte['fecha_corte'])->format('d/m/Y') }} · Solo matrícula vigente</span>
        </div>
    </div>
</div>
