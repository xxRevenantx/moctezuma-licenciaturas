<div class="space-y-5 pb-10">
    <section class="rounded-3xl border border-sky-200 bg-gradient-to-r from-sky-50 via-white to-lime-50 p-6 shadow-sm dark:border-sky-900 dark:from-sky-950/30 dark:via-neutral-900 dark:to-lime-950/20">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-[#006492] dark:text-sky-200">Control de Avance Académico PRO</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Detecta desfases con el calendario por generación. Ningún alumno cambia de cuatrimestre sin revisión y confirmación expresa.</p>
            </div>
            <span class="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-slate-500 dark:bg-neutral-800">Corte: {{ \Carbon\Carbon::parse($diagnostico['fecha_corte'])->format('d/m/Y') }}</span>
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-3">
            <flux:field><flux:label>Licenciatura</flux:label><flux:select wire:model.live="licenciaturaId"><flux:select.option value="">Todas</flux:select.option>@foreach($licenciaturas as $lic)<flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>@endforeach</flux:select></flux:field>
            <flux:field><flux:label>Modalidad</flux:label><flux:select wire:model.live="modalidadId"><flux:select.option value="">Todas</flux:select.option>@foreach($modalidades as $mod)<flux:select.option value="{{ $mod->id }}">{{ $mod->nombre }}</flux:select.option>@endforeach</flux:select></flux:field>
            <flux:field><flux:label>Generación</flux:label><flux:select wire:model.live="generacionId"><flux:select.option value="">Todas</flux:select.option>@foreach($generaciones as $gen)<flux:select.option value="{{ $gen->id }}">{{ $gen->generacion }}</flux:select.option>@endforeach</flux:select></flux:field>
        </div>
    </section>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach([['texto'=>'Por revisar','valor'=>$diagnostico['total_pendientes'],'color'=>'amber'],['texto'=>'Prioridad alta (+7 días)','valor'=>$diagnostico['total_criticos'],'color'=>'rose'],['texto'=>'Próximos 15 días','valor'=>$diagnostico['total_proximos'],'color'=>'sky'],['texto'=>'Justificados','valor'=>$diagnostico['total_justificados'],'color'=>'emerald']] as $indicador)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <div class="text-xs font-semibold uppercase text-slate-500">{{ $indicador['texto'] }}</div>
                <div class="mt-1 text-3xl font-black text-[#006492] dark:text-sky-200">{{ $indicador['valor'] }}</div>
            </div>
        @endforeach
    </div>

    @if(count($diagnostico['conflictos']))
        <div class="rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-800" role="alert">Hay {{ count($diagnostico['conflictos']) }} generación(es) con periodos superpuestos o ambiguos. No se propondrán avances para ellas hasta corregir el calendario.</div>
    @endif
    @if($mensaje)<div class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" role="status">{{ $mensaje }}</div>@endif
    @error('seleccionados')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
    @error('revision')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-[#006492] p-4 text-white dark:border-neutral-800">
            <div><h2 class="font-bold">Actualizaciones pendientes</h2><p class="mt-1 text-xs text-sky-100">Sólo alumnos activos cuyo cuatrimestre registrado es anterior al periodo vigente.</p></div>
            <div class="flex gap-2"><button type="button" wire:click="seleccionarPendientes" class="rounded-lg border border-white/40 px-3 py-2 text-xs font-bold hover:bg-white/10">Seleccionar hasta 100</button>
            <button type="button" wire:click="revisar" wire:loading.attr="disabled" class="rounded-lg bg-white px-4 py-2 text-xs font-bold text-[#006492] hover:bg-sky-100">Revisar {{ count($seleccionados) }} seleccionados</button></div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm dark:divide-neutral-800">
                <thead class="bg-slate-50 text-[11px] uppercase text-slate-500 dark:bg-neutral-800"><tr><th class="px-4 py-3">Sel.</th><th class="px-4 py-3">Alumno / matrícula</th><th class="px-4 py-3">Licenciatura / modalidad</th><th class="px-4 py-3">Generación</th><th class="px-4 py-3">Actual → esperado</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Acción</th></tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                @forelse($diagnostico['pendientes'] as $alumno)
                    <tr wire:key="avance-pendiente-{{ $alumno['id'] }}" class="hover:bg-sky-50/40 dark:hover:bg-neutral-800/50">
                        <td class="px-4 py-3"><input type="checkbox" wire:model.live="seleccionados" value="{{ $alumno['id'] }}" class="rounded border-slate-300 text-[#006492]" aria-label="Seleccionar {{ $alumno['nombre'] }}"></td>
                        <td class="px-4 py-3"><div class="font-semibold">{{ $alumno['nombre'] }}</div><div class="text-xs text-slate-500">{{ $alumno['matricula'] }}</div></td>
                        <td class="px-4 py-3"><div>{{ $alumno['licenciatura'] }}</div><div class="text-xs text-slate-500">{{ $alumno['modalidad'] }}</div></td>
                        <td class="px-4 py-3">{{ $alumno['generacion'] }}</td>
                        <td class="whitespace-nowrap px-4 py-3 font-bold">{{ $alumno['cuatrimestre_actual'] }}.º → {{ $alumno['cuatrimestre_esperado'] }}.º</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-bold {{ $alumno['prioridad'] === 'alta' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">{{ $alumno['prioridad'] === 'alta' ? 'Prioritaria' : 'Por revisar' }}</span></td>
                        <td class="px-4 py-3"><button type="button" wire:click="abrirExcepcion({{ $alumno['id'] }})" class="text-xs font-semibold text-[#006492] hover:underline">Justificar</button></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-emerald-700">No se detectaron alumnos con avance atrasado en los filtros seleccionados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($excepcionAlumnoId)
        <section class="rounded-2xl border border-amber-300 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-950/20">
            <h3 class="font-bold text-amber-900 dark:text-amber-100">Justificar permanencia en el cuatrimestre — Alumno #{{ $excepcionAlumnoId }}</h3>
            <p class="mb-3 text-xs text-amber-800">La excepción afecta solamente al periodo académico actual. Se conservará el cuatrimestre del alumno.</p>
            <flux:textarea wire:model="excepcionMotivo" label="Motivo académico o administrativo" placeholder="Describe la causa verificable: repetición, autorización, revisión de expediente…" rows="2" />
            @error('excepcionMotivo')<p class="mt-2 text-xs text-rose-700">{{ $message }}</p>@enderror
            <div class="mt-3 flex flex-wrap gap-2"><flux:button wire:click="guardarExcepcion" variant="primary">Guardar justificación</flux:button><flux:button wire:click="$set('excepcionAlumnoId', null)" variant="ghost">Cancelar</flux:button></div>
        </section>
    @endif

    @if($revision)
        <section class="rounded-2xl border border-sky-200 bg-sky-50 p-5 dark:border-sky-900 dark:bg-sky-950/25">
            <h3 class="font-bold text-[#006492] dark:text-sky-200">Revisión previa de {{ count($revision['ids']) }} estudiante(s)</h3>
            <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Cada grupo se validó contra materias, periodos y horarios con AcademicMovementService. El avance no mueve calificaciones históricas.</p>
            @foreach($revision['grupos'] as $grupo)
                <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 text-sm dark:border-neutral-700 dark:bg-neutral-900">
                    <div class="font-bold">{{ count($grupo['ids']) }} alumno(s) → cuatrimestre destino #{{ $grupo['destino_id'] }} {{ $grupo['valido'] ? '· Validado' : '· Bloqueado' }}</div>
                    @foreach($grupo['bloqueos'] as $bloqueo)<p class="mt-1 text-xs text-rose-700">Bloqueo: {{ $bloqueo }}</p>@endforeach
                    @foreach(array_slice($grupo['advertencias'], 0, 6) as $advertencia)<p class="mt-1 text-xs text-amber-700">Advertencia: {{ $advertencia }}</p>@endforeach
                </div>
            @endforeach
            @if($revision['valido'])
                <label class="mt-4 flex items-start gap-2 text-sm"><input type="checkbox" wire:model="confirmarRevision" class="mt-1 rounded">Confirmo que revisé los casos y autorizo su promoción académica.</label>
                @error('confirmarRevision')<p class="mt-2 text-xs text-rose-700">{{ $message }}</p>@enderror
                <flux:button wire:click="aplicar" wire:loading.attr="disabled" class="mt-3" variant="primary">Confirmar avances auditados</flux:button>
            @else
                <p class="mt-3 text-xs font-bold text-rose-700">Resuelve los bloqueos en Materias/Periodos antes de ejecutar cambios.</p>
            @endif
        </section>
    @endif

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-sky-200 bg-sky-50/50 p-4 dark:border-sky-900 dark:bg-sky-950/10">
            <h3 class="font-bold text-[#006492]">Próximos cambios (15 días)</h3>
            <p class="mt-1 text-xs text-slate-500">Aviso preventivo. No son movimientos pendientes hasta que inicie el periodo.</p>
            @forelse(array_slice($diagnostico['proximos'], 0, 20) as $a)<p class="mt-2 text-xs">{{ $a['nombre'] }} · {{ $a['generacion'] }} · {{ $a['cuatrimestre_actual'] }}.º → {{ $a['proximo_cuatrimestre'] }}.º ({{ $a['inicio_proximo'] }})</p>@empty<p class="mt-3 text-xs text-slate-500">Sin cambios próximos.</p>@endforelse
            @if($diagnostico['total_proximos'] > 20)<p class="mt-3 text-xs">Mostrando los primeros 20 de {{ $diagnostico['total_proximos'] }} avisos.</p>@endif
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-900 dark:bg-emerald-950/10">
            <div class="flex flex-wrap items-center justify-between gap-2"><h3 class="font-bold text-emerald-800 dark:text-emerald-200">Permanencias justificadas</h3><flux:button wire:click="$toggle('mostrarJustificados')" size="sm" variant="ghost">{{ $mostrarJustificados ? 'Ocultar' : 'Mostrar' }} ({{ $diagnostico['total_justificados'] }})</flux:button></div>
            <p class="mt-1 text-xs text-slate-500">Repeticiones o excepciones documentadas; vuelven a evaluarse al iniciar otro periodo.</p>
            @if($mostrarJustificados)
                @forelse($diagnostico['justificados'] as $a)<div class="mt-3 flex items-start justify-between gap-2 border-b border-emerald-200 pb-2"><div class="text-xs"><strong>{{ $a['nombre'] }}</strong> · {{ $a['cuatrimestre_actual'] }}.º / {{ $a['cuatrimestre_esperado'] }}.º<p class="mt-1">{{ $a['motivo'] }}</p></div><button type="button" wire:click="quitarExcepcion({{ $a['id'] }})" class="text-xs font-bold text-rose-700">Reactivar</button></div>@empty<p class="mt-3 text-xs">Sin justificaciones.</p>@endforelse
            @endif
        </div>
    </section>
</div>
