<section class="overflow-hidden rounded-2xl border border-sky-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
    <div class="h-1 bg-gradient-to-r from-[#006492] to-[#88AC2E]"></div>
    <div class="flex flex-wrap items-center justify-between gap-3 p-5">
        <div>
            <h2 class="font-bold text-slate-900 dark:text-white">Reemplazar profesor masivamente</h2>
            <p class="text-sm text-slate-500">Reasigna sus materias en una, varias o todas las licenciaturas.</p>
        </div>
        <flux:button wire:click="$toggle('abierto')" class="cursor-pointer">{{ $abierto ? 'Ocultar panel' : 'Abrir reemplazo masivo' }}</flux:button>
    </div>
    @if($abierto)
        <div class="space-y-5 border-t border-slate-200 p-5 dark:border-neutral-700">
            @if($mensaje)<div role="status" class="rounded-xl bg-emerald-50 p-4 text-emerald-800">{{ $mensaje }}</div>@endif
            @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <div class="grid gap-4 md:grid-cols-2">
                <flux:select label="Profesor actual" wire:model.live="origen">
                    <option value="">Seleccionar profesor…</option>
                    @foreach($profesores as $p)<option value="{{ $p->id }}">{{ $p->apellido_paterno }} {{ $p->apellido_materno }} {{ $p->nombre }}{{ $p->status !== 'true' ? ' (inactivo)' : '' }}</option>@endforeach
                </flux:select>
                <flux:select label="Profesor de reemplazo" wire:model.live="destino">
                    <option value="">Seleccionar reemplazo…</option>
                    @foreach($profesores as $p)
                        @if($p->status === 'true' && (string)$p->id !== $origen)<option value="{{ $p->id }}">{{ $p->apellido_paterno }} {{ $p->apellido_materno }} {{ $p->nombre }}</option>@endif
                    @endforeach
                </flux:select>
                <flux:select label="Licenciaturas que se revisarán" wire:model.live="alcance">
                    @if($licenciaturaActual)<option value="actual">Licenciatura actual</option>@endif
                    <option value="seleccionadas">Elegir varias licenciaturas</option>
                    <option value="todas">Todas las licenciaturas</option>
                </flux:select>
                <flux:input label="Buscar materia o clave" wire:model.live.debounce.350ms="busqueda" placeholder="Nombre o clave de materia" />
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @if($alcance === 'seleccionadas')
                    <fieldset class="rounded-xl border border-slate-200 p-3 dark:border-neutral-700"><legend class="px-1 text-sm font-semibold">Licenciaturas</legend>
                        <div class="max-h-36 space-y-2 overflow-y-auto">@foreach($opcionesLicenciaturas as $op)<label class="flex items-start gap-2 text-sm"><input type="checkbox" wire:model.live="licenciaturas" value="{{ $op->id }}">{{ $op->nombre }}</label>@endforeach</div>
                    </fieldset>
                @endif
                @foreach(['modalidades' => ['Modalidades', $opcionesModalidades], 'cuatrimestres' => ['Cuatrimestres', $opcionesCuatrimestres], 'generaciones' => ['Localizar por generación', $opcionesGeneraciones]] as $campo => [$titulo, $opciones])
                    <fieldset class="rounded-xl border border-slate-200 p-3 dark:border-neutral-700"><legend class="px-1 text-sm font-semibold">{{ $titulo }}</legend>
                        <p class="mb-2 text-xs text-slate-500">Sin marcar = todas las opciones</p>
                        <div class="max-h-36 space-y-2 overflow-y-auto">@foreach($opciones as $op)<label class="flex items-start gap-2 text-sm"><input type="checkbox" wire:model.live="{{ $campo }}" value="{{ $op->id }}">{{ $campo === 'generaciones' ? $op->generacion : ($campo === 'cuatrimestres' ? $op->cuatrimestre.'° cuatrimestre' : $op->nombre) }}</label>@endforeach</div>
                    </fieldset>
                @endforeach
            </div>
            <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                <strong>Asignaciones compartidas:</strong> el filtro de generación ayuda a localizar materias; el reemplazo afecta a todas las generaciones que usan la asignación. Sus horarios mostrarán automáticamente al nuevo profesor, conservando días y horas. Las calificaciones y su autoría histórica se conservan.
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="incluirHorarios">Incluir materias con horarios (desmarca para trabajar solo con materias sin horarios).</label>
            <div class="flex flex-wrap items-center gap-3">
                <flux:button wire:click="seleccionarTodas" wire:loading.attr="disabled" class="cursor-pointer">Seleccionar todos los resultados ({{ $filas->total() }})</flux:button>
                <flux:button wire:click="limpiarSeleccion" variant="ghost" class="cursor-pointer">Quitar selección</flux:button>
                <span class="text-sm font-semibold text-[#006492]">{{ count($seleccionadas) }} seleccionadas</span>
                <span wire:loading class="text-sm text-slate-500">Actualizando…</span>
            </div>
            <p class="text-xs text-slate-500">La selección incluye todas las páginas. Cambiar filtros limpia la selección para evitar reemplazos fuera del alcance mostrado.</p>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-neutral-700">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-600 dark:bg-neutral-800 dark:text-slate-300"><tr><th class="p-3">Elegir</th><th class="p-3">Materia</th><th class="p-3">Licenciatura</th><th class="p-3">Modalidad</th><th class="p-3">Cuatrimestre</th><th class="p-3">Horarios / calificaciones</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-700">
                        @forelse($filas as $fila)
                            <tr wire:key="reemplazo-{{ $fila->id }}" class="hover:bg-sky-50 dark:hover:bg-neutral-800">
                                <td class="p-3"><input type="checkbox" wire:model.live="seleccionadas" value="{{ $fila->id }}" aria-label="Seleccionar {{ $fila->materia?->nombre }}"></td>
                                <td class="p-3"><div class="font-semibold">{{ $fila->materia?->nombre }}</div><div class="text-xs text-slate-500">{{ $fila->materia?->clave }}</div></td>
                                <td class="p-3">{{ $fila->licenciatura?->nombre }}</td><td class="p-3">{{ $fila->modalidad?->nombre }}</td><td class="p-3">{{ $fila->cuatrimestre?->cuatrimestre }}°</td><td class="p-3">{{ $fila->horarios_count }} / {{ $fila->calificaciones_count }}</td>
                            </tr>
                        @empty<tr><td colspan="6" class="p-8 text-center text-slate-500">{{ $origen ? 'No hay asignaciones con estos filtros.' : 'Selecciona al profesor actual para ver sus materias.' }}</td></tr>@endforelse
                    </tbody>
                </table>
            </div>
            {{ $filas->links() }}
            <flux:button wire:click="revisar" wire:loading.attr="disabled" variant="primary" class="cursor-pointer">Revisar reemplazo</flux:button>
        </div>
    @endif
    <flux:modal name="confirmar-reasignacion-profesor" wire:model="confirmacion" class="w-full md:max-w-3xl">
        @if($revision)
            <div class="space-y-4">
                <flux:heading size="lg">Confirmar reemplazo de profesor</flux:heading>
                @if($errors->any())<div role="alert" class="rounded-lg bg-red-50 p-3 text-red-800">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <p class="rounded-xl bg-sky-50 p-4 font-semibold text-[#006492]">{{ $revision['nombre_origen'] }} → {{ $revision['nombre_destino'] }}</p>
                <p>{{ count($revision['ids']) }} asignaciones · {{ $revision['impacto']['horarios'] }} horarios vinculados · {{ $revision['impacto']['calificaciones'] }} calificaciones conservadas.</p>
                <ul class="list-inside list-disc text-sm">@foreach($revision['resumen'] as $r)<li>{{ $r['licenciatura'] }}: {{ $r['total'] }}</li>@endforeach</ul>
                <p class="text-sm"><strong>Generaciones asociadas:</strong> {{ implode(', ', $revision['generaciones']) ?: 'Sin generaciones registradas' }}.</p>
                <details class="rounded-lg border border-slate-200 p-3"><summary class="cursor-pointer font-semibold">Ver todas las materias afectadas</summary><ul class="mt-2 max-h-48 space-y-2 overflow-y-auto text-sm">@foreach($revision['detalle'] as $r)<li>{{ $r['materia'] }} · {{ $r['licenciatura'] }} · {{ $r['modalidad'] }} · {{ $r['cuatrimestre'] }}°</li>@endforeach</ul></details>
                @if($revision['impacto']['calificaciones'] || $revision['impacto']['capturas'])
                    <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Hay registros académicos existentes. Se conservan valores, autoría histórica y estados de captura ({{ $revision['impacto']['entregadas'] }} entregadas, {{ $revision['impacto']['validadas'] }} validadas). El nuevo docente será el responsable actual.</p>
                @endif
                @if($revision['conflictos'])
                    <div class="rounded-lg bg-red-50 p-3 text-sm text-red-900"><strong>{{ count($revision['conflictos']) }} posibles traslapes de horario</strong>
                        <p>Revisa también si las generaciones coinciden en fechas; esta comparación utiliza día y hora.</p>
                        <ul class="mt-2 max-h-40 space-y-2 overflow-y-auto">@foreach($revision['conflictos'] as $c)<li>{{ $c['dia'] }}: {{ $c['materia_origen'] }} ({{ $c['licenciatura_origen'] }}, {{ $c['generacion_origen'] }}, {{ $c['hora_origen'] }}) / {{ $c['materia_conflicto'] }} ({{ $c['licenciatura_conflicto'] }}, {{ $c['generacion_conflicto'] }}, {{ $c['hora_conflicto'] }}).</li>@endforeach</ul>
                    </div>
                    <label class="flex items-start gap-2 text-sm"><input type="checkbox" wire:model="aceptaTraslapes">He revisado los posibles traslapes y deseo continuar.</label>
                @endif
                <label class="flex items-start gap-2 text-sm"><input type="checkbox" wire:model="aceptaVinculos">Confirmo el reemplazo en todas las generaciones y horarios vinculados a estas asignaciones, incluidas las generaciones fuera del filtro.</label>
                <p class="text-xs text-slate-500">Se registrarán el usuario, la fecha y hora, los profesores y las materias afectadas.</p>
                <div class="flex justify-end gap-3"><flux:button wire:click="cancelarRevision" wire:loading.attr="disabled" class="cursor-pointer">Cancelar</flux:button><flux:button wire:click="confirmar" wire:loading.attr="disabled" variant="primary" class="cursor-pointer">Confirmar reemplazo</flux:button></div>
            </div>
        @endif
    </flux:modal>
</section>
