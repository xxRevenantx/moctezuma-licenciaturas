<div class="space-y-5">
    <div class="rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 via-white to-lime-50 p-4 shadow-sm dark:border-sky-900/50 dark:from-sky-950/30 dark:via-neutral-900 dark:to-lime-950/20">
        <div class="flex flex-col justify-between gap-3 xl:flex-row xl:items-center">
            <div class="flex items-start gap-3">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#006492] text-white">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h1m-1 6h1m-1 6h1" /></svg>
                </span>
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white">Lista nominal por licenciatura y grado</h3>
                    <p class="mt-1 max-w-xl text-sm text-neutral-600 dark:text-neutral-300">Alumnos activos de generaciones vigentes, ordenados por apellido paterno, materno y nombre. Edad al día de emisión.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ $this->pdfVistaUrl }}" target="_blank" variant="filled" icon="eye">Vista previa PDF</flux:button>
                <flux:button href="{{ $this->pdfDescargaUrl }}" variant="primary" icon="download">PDF</flux:button>
                <flux:button href="{{ $this->wordUrl }}" variant="primary" icon="document-text" class="!bg-[#006492] hover:!bg-[#00547b]">Word</flux:button>
                <flux:button href="{{ $this->excelUrl }}" variant="primary" icon="sheet" class="!bg-[#88AC2E] hover:!bg-[#779929]">Excel</flux:button>
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

    <div class="flex flex-wrap items-end justify-between gap-4 rounded-2xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-800 dark:bg-neutral-900/60">
        <div>
            <p class="mb-2 text-sm font-semibold text-neutral-800 dark:text-neutral-200">Grados que aparecerán en los documentos</p>
            <div class="flex flex-wrap gap-4">
                @foreach ([1 => '1.º · Cuatrimestres 1–3', 2 => '2.º · Cuatrimestres 4–6', 3 => '3.º · Cuatrimestres 7–9'] as $numero => $texto)
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-neutral-700 dark:text-neutral-300">
                        <input type="checkbox" wire:model.live="gradosSeleccionados" value="{{ $numero }}" class="size-4 rounded border-neutral-300 text-[#006492] focus:ring-sky-500" />
                        {{ $texto }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <flux:switch wire:model.live="mostrar_grados_vacios" label="Grados sin alumnos" />
            <label class="flex items-center gap-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200">
                Renglones vacíos
                <select wire:model.live="renglones_vacios" class="rounded-lg border border-neutral-300 bg-white px-2 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800">
                    @foreach ([0, 2, 5, 10] as $num)<option value="{{ $num }}">{{ $num }}</option>@endforeach
                </select>
            </label>
            <flux:button wire:click="limpiarFiltros" variant="ghost" icon="x-mark">Limpiar filtros</flux:button>
        </div>
    </div>
    @php($reporte = $this->reporte)
    @if(($reporte['alertas_avance']['total_pendientes'] ?? 0) > 0)
        <div role="alert" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 dark:border-amber-800 dark:bg-amber-950/25">
            <p class="text-sm font-medium text-amber-900 dark:text-amber-200">{{ $reporte['alertas_avance']['total_pendientes'] }} alumno(s) con posible desfase académico. La lista usa el cuatrimestre registrado, sin promover automáticamente.</p>
            <flux:button href="{{ route('admin.avance-academico') }}" variant="filled" icon="exclamation-triangle">Revisar avance</flux:button>
        </div>
    @endif
    @if(count($reporte['alertas_avance']['conflictos'] ?? []))
        <div class="rounded-lg border border-rose-300 bg-rose-50 p-3 text-sm text-rose-800">Algunas generaciones tienen periodos superpuestos. Revisa el calendario en Administración → Periodos.</div>
    @endif
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([['Licenciaturas', $reporte['licenciaturas_count'], 'text-sky-700', 'bg-sky-50 border-sky-200'], ['Listas / Grados', count($reporte['grupos']), 'text-[#006492]', 'bg-slate-50 border-slate-200'], ['Hombres / Mujeres', $reporte['totales']['hombres'].' / '.$reporte['totales']['mujeres'], 'text-violet-700', 'bg-violet-50 border-violet-200'], ['Total alumnos', $reporte['totales']['total'], 'text-emerald-700', 'bg-emerald-50 border-emerald-200']] as $kpi)
            <div class="rounded-xl border p-3 {{ $kpi[3] }} dark:bg-neutral-900 dark:border-neutral-800">
                <p class="text-xs font-semibold uppercase {{ $kpi[2] }}">{{ $kpi[0] }}</p>
                <p class="mt-1 text-2xl font-black text-neutral-900 dark:text-white">{{ $kpi[1] }}</p>
            </div>
        @endforeach
    </div>
    <p class="text-xs text-neutral-500 dark:text-neutral-400">Cada combinación de licenciatura y grado se imprimirá por separado. En Excel se genera una pestaña por lista y un resumen general. Los renglones adicionales son sólo para anotaciones y no afectan los totales.</p>
    <div wire:loading.flex wire:target="licenciaturasSeleccionadas,modalidadesSeleccionadas,generacionesSeleccionadas,gradosSeleccionados,mostrar_grados_vacios,renglones_vacios,limpiarFiltros,seleccionarTodo,quitarTodo" class="items-center justify-center rounded-xl border border-neutral-200 py-8 dark:border-neutral-700">
        <span class="text-sm text-neutral-500">Actualizando listado nominal…</span>
    </div>
    <div wire:loading.remove wire:target="licenciaturasSeleccionadas,modalidadesSeleccionadas,generacionesSeleccionadas,gradosSeleccionados,mostrar_grados_vacios,renglones_vacios,limpiarFiltros,seleccionarTodo,quitarTodo" class="space-y-4">
        @forelse ($reporte['grupos'] as $grupo)
            <section wire:key="nominal-{{ $grupo['licenciatura_id'] }}-{{ $grupo['grado'] }}" class="overflow-hidden rounded-xl border border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-[#006492] px-4 py-3 text-white">
                    <h4 class="text-sm font-bold uppercase">{{ $grupo['licenciatura'] }} · {{ $grupo['grado'] }}.º GRADO</h4>
                    <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold">{{ $grupo['totales']['total'] }} estudiantes</span>
                </div>
                <div class="overflow-x-auto p-3">
                    <table class="w-full min-w-[600px] border-collapse text-left text-xs text-neutral-800 dark:text-neutral-100">
                        <thead class="bg-[#b8cae8] text-neutral-900"><tr><th class="border border-neutral-300 px-2 py-2 text-center">No.</th><th class="border border-neutral-300 px-3 py-2">Apellido paterno</th><th class="border border-neutral-300 px-3 py-2">Apellido materno</th><th class="border border-neutral-300 px-3 py-2">Nombre</th><th class="border border-neutral-300 px-2 py-2 text-center">Edad</th></tr></thead>
                        <tbody>
                            @forelse(array_slice($grupo['alumnos'], 0, 6) as $numero => $alumno)
                                <tr class="odd:bg-white even:bg-slate-50 dark:odd:bg-neutral-950 dark:even:bg-neutral-900"><td class="border border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $numero + 1 }}</td><td class="border border-neutral-200 px-3 py-2 dark:border-neutral-800">{{ $alumno['apellido_paterno'] }}</td><td class="border border-neutral-200 px-3 py-2 dark:border-neutral-800">{{ $alumno['apellido_materno'] }}</td><td class="border border-neutral-200 px-3 py-2 dark:border-neutral-800">{{ $alumno['nombre'] }}</td><td class="border border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $alumno['edad'] ?? '—' }}</td></tr>
                            @empty <tr><td colspan="5" class="border border-neutral-200 p-3 text-center text-neutral-500">Sin estudiantes en este grado</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if(count($grupo['alumnos']) > 6)<p class="mt-2 text-xs text-neutral-500">Vista preliminar: primeros 6 de {{ count($grupo['alumnos']) }} estudiantes. Las descargas incluyen la lista completa.</p>@endif
                    <div class="mt-3 grid max-w-md grid-cols-3 overflow-hidden rounded-md border border-neutral-300 text-center text-xs dark:border-neutral-700">
                        <div class="border-r border-neutral-300 bg-[#c8e2ba] p-2 text-neutral-900">Hombres <strong class="block text-lg">{{ $grupo['totales']['hombres'] }}</strong></div>
                        <div class="border-r border-neutral-300 bg-[#c8e2ba] p-2 text-neutral-900">Mujeres <strong class="block text-lg">{{ $grupo['totales']['mujeres'] }}</strong></div>
                        <div class="bg-lime-50 p-2 font-semibold text-neutral-900">Total <strong class="block text-lg">{{ $grupo['totales']['total'] }}</strong></div>
                    </div>
                </div>
            </section>
        @empty
            <div class="rounded-xl border border-dashed border-neutral-300 p-8 text-center text-sm text-neutral-500 dark:border-neutral-700">No se encontraron alumnos para los filtros seleccionados.</div>
        @endforelse
    </div>
</div>
