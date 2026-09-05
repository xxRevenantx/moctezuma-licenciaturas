<div
    x-data="{ asignar:false, confirmar:false }"
    x-on:abrir-asignacion-global.window="asignar=true"
    x-on:cerrar-asignacion-global.window="asignar=false"
    x-on:abrir-confirmacion-global.window="confirmar=true"
    x-on:cerrar-confirmacion-global.window="confirmar=false"
    x-on:asignacion-global-ok.window="Swal.fire({icon:'success',title:'Listo',text:$event.detail.message,timer:2600,showConfirmButton:false})"
    x-on:asignacion-global-error.window="Swal.fire({icon:'error',title:'No se pudo continuar',text:$event.detail.message})"
    class="space-y-5"
>
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] via-sky-500 to-[#88AC2E]"></div>
        <div class="flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Asignación docente</h1>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-bold text-[#006492] ring-1 ring-sky-200 dark:bg-sky-950/30 dark:text-sky-300 dark:ring-sky-900">
                        {{ $ciclo_escolar }} · {{ $periodo_escolar }}
                    </span>
                </div>
                <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-slate-400">
                    Asigna profesores a materias de distintas licenciaturas y cuatrimestres desde una sola pantalla. El ciclo y periodo solo determinan la carga que se muestra; la estructura de <code>asignacion_materias</code> se conserva como está.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @if(count($seleccionadas))
                    <flux:button wire:click="abrirAsignacionMasiva" variant="primary" class="cursor-pointer">
                        Asignar {{ count($seleccionadas) }} seleccionadas
                    </flux:button>
                    <flux:button wire:click="limpiarSeleccion" variant="ghost" class="cursor-pointer">Limpiar selección</flux:button>
                @endif
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Total', $resumen['total'], 'Carga vigente'],
            ['Asignadas', $resumen['asignadas'], 'Con profesor'],
            ['Pendientes', $resumen['pendientes'], 'Sin profesor'],
            ['Duplicadas', $resumen['duplicadas'], 'Requieren revisión'],
            ['Licenciaturas', $resumen['licenciaturas'], 'Con alumnos activos'],
        ] as [$titulo,$valor,$detalle])
            <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                <p class="text-xs font-extrabold uppercase tracking-[.12em] text-slate-400">{{ $titulo }}</p>
                <p class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $valor }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $detalle }}</p>
            </article>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="border-b border-slate-200 p-4 dark:border-neutral-800 sm:p-5">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-7">
                <div class="xl:col-span-2">
                    <flux:input wire:model.live.debounce.300ms="busqueda" label="Buscar" placeholder="Materia, clave, licenciatura o profesor" icon="magnifying-glass" />
                </div>
                <flux:select wire:model.live="filtro_licenciatura" label="Licenciatura">
                    <flux:select.option value="">Todas</flux:select.option>
                    @foreach($opciones['licenciaturas'] as $op)
                        <flux:select.option value="{{ $op['id'] }}">{{ $op['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filtro_modalidad" label="Modalidad">
                    <flux:select.option value="">Todas</flux:select.option>
                    @foreach($opciones['modalidades'] as $op)
                        <flux:select.option value="{{ $op['id'] }}">{{ $op['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filtro_cuatrimestre" label="Cuatrimestre">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach($opciones['cuatrimestres'] as $op)
                        <flux:select.option value="{{ $op['id'] }}">{{ $op['nombre'] }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filtro_estado" label="Estado">
                    <flux:select.option value="">Todos</flux:select.option>
                    <flux:select.option value="pendiente">Pendientes</flux:select.option>
                    <flux:select.option value="asignada">Asignadas</flux:select.option>
                    <flux:select.option value="duplicada">Duplicadas</flux:select.option>
                </flux:select>
                <flux:select wire:model.live="filtro_profesor" label="Profesor">
                    <flux:select.option value="">Todos</flux:select.option>
                    @foreach($profesores as $profesor)
                        <flux:select.option value="{{ $profesor->id }}">{{ trim($profesor->apellido_paterno.' '.$profesor->apellido_materno.' '.$profesor->nombre) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500">{{ $filas->count() }} resultados · {{ count($seleccionadas) }} seleccionados</p>
                <div class="flex gap-2">
                    <flux:button wire:click="alternarSeleccionFiltrada" variant="ghost" class="cursor-pointer">Seleccionar filtrados</flux:button>
                    <flux:button wire:click="limpiarFiltros" variant="ghost" class="cursor-pointer">Limpiar filtros</flux:button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1180px] w-full text-sm">
                <thead class="bg-[#006492] text-white">
                    <tr>
                        <th class="w-12 px-3 py-3 text-center">Sel.</th>
                        <th class="px-3 py-3 text-left">Licenciatura</th>
                        <th class="px-3 py-3 text-left">Contexto académico</th>
                        <th class="px-3 py-3 text-left">Materia</th>
                        <th class="px-3 py-3 text-left">Profesor</th>
                        <th class="px-3 py-3 text-center">Estado</th>
                        <th class="px-3 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                    @forelse($filas as $fila)
                        <tr class="hover:bg-sky-50/40 dark:hover:bg-sky-950/10">
                            <td class="px-3 py-3 text-center">
                                @if($fila['estado'] !== 'duplicada')
                                    <input type="checkbox" wire:model.live="seleccionadas" value="{{ $fila['clave_objetivo'] }}" class="rounded border-slate-300 text-[#006492] focus:ring-[#006492]">
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $fila['licenciatura_corta'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $fila['modalidad'] }}</div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $fila['cuatrimestre'] }}° Cuatrimestre</div>
                                <div class="mt-1 text-xs text-slate-500">Gen. {{ implode(', ', $fila['generaciones']) }} · {{ $fila['alumnos'] }} alumnos activos</div>
                            </td>
                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $fila['materia'] }}</div>
                                <div class="mt-1 text-xs font-mono text-slate-400">{{ $fila['clave_materia'] ?: 'SIN CLAVE' }}</div>
                            </td>
                            <td class="px-3 py-3">
                                @if($fila['profesor'])
                                    <div class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full border border-black/10" style="background:{{ $fila['profesor_color'] }}"></span>
                                        <div>
                                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ $fila['profesor'] }}</div>
                                            @if(!$fila['profesor_activo'])<div class="text-xs font-bold text-amber-600">Profesor inactivo</div>@endif
                                        </div>
                                    </div>
                                @else
                                    <span class="text-slate-400">Sin profesor</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($fila['estado'] === 'asignada')
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">Asignada</span>
                                @elseif($fila['estado'] === 'duplicada')
                                    <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 ring-1 ring-rose-200">Duplicada</span>
                                @else
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-amber-200">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-right">
                                @if($fila['estado'] !== 'duplicada')
                                    <flux:button size="sm" variant="ghost" wire:click="abrirAsignacionIndividual('{{ $fila['clave_objetivo'] }}')" class="cursor-pointer">Asignar</flux:button>
                                @else
                                    <span class="text-xs text-rose-600">Corrige el duplicado antes de asignar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">No hay materias para los filtros actuales.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div x-show="asignar" x-cloak class="fixed inset-0 z-[130] grid place-items-center bg-black/45 p-4 backdrop-blur-sm" @click.self="asignar=false">
        <div class="w-full max-w-3xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="border-b border-slate-200 p-5 dark:border-neutral-800">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">{{ $modo_operacion === 'masiva' ? 'Asignación masiva' : 'Asignar profesor' }}</h2>
                <p class="mt-1 text-sm text-slate-500">Puedes asignar el mismo docente a materias de distintas licenciaturas y cuatrimestres. Las modalidades se mantienen independientes.</p>
            </div>
            <div class="grid gap-5 p-5 md:grid-cols-2">
                <div>
                    <flux:select wire:model.live="profesor_destino" label="Profesor destino">
                        <flux:select.option value="">Selecciona un profesor</flux:select.option>
                        @foreach($profesores as $profesor)
                            @if($profesor->status === 'true')
                                <flux:select.option value="{{ $profesor->id }}">{{ trim($profesor->apellido_paterno.' '.$profesor->apellido_materno.' '.$profesor->nombre) }}</flux:select.option>
                            @endif
                        @endforeach
                    </flux:select>
                    <p class="mt-2 text-xs text-slate-500">Antes de guardar se verifican traslapes, horarios, calificaciones y capturas por docente.</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-800 dark:bg-neutral-950/40">
                    @if($resumenProfesor)
                        <div class="flex items-start justify-between gap-3">
                            <div><p class="font-black text-slate-900 dark:text-white">{{ $resumenProfesor['nombre'] }}</p><p class="text-xs text-slate-500">{{ $resumenProfesor['perfil'] ?: 'Sin perfil registrado' }}</p></div>
                            <span class="rounded-full px-2 py-1 text-xs font-bold {{ $resumenProfesor['traslapes'] ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $resumenProfesor['traslapes'] }} traslapes actuales</span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-white p-2 dark:bg-neutral-900"><b>{{ $resumenProfesor['materias'] }}</b><span class="block text-[10px] text-slate-500">Materias</span></div>
                            <div class="rounded-lg bg-white p-2 dark:bg-neutral-900"><b>{{ $resumenProfesor['licenciaturas'] }}</b><span class="block text-[10px] text-slate-500">Licenciaturas</span></div>
                            <div class="rounded-lg bg-white p-2 dark:bg-neutral-900"><b>{{ $resumenProfesor['horas'] }}</b><span class="block text-[10px] text-slate-500">Horas programadas</span></div>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">Selecciona un profesor para consultar su carga actual antes de asignar.</p>
                    @endif
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 p-4 dark:border-neutral-800">
                <flux:button variant="ghost" @click="asignar=false" class="cursor-pointer">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="prepararAsignacion" class="cursor-pointer">Revisar y asignar</flux:button>
            </div>
        </div>
    </div>

    <div x-show="confirmar" x-cloak class="fixed inset-0 z-[140] grid place-items-center bg-black/50 p-4 backdrop-blur-sm" @click.self="confirmar=false">
        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
            <div class="border-b border-slate-200 p-5 dark:border-neutral-800">
                <h2 class="text-lg font-black text-slate-900 dark:text-white">Confirmación preventiva</h2>
                <p class="mt-1 text-sm text-slate-500">La operación afecta información relacionada o genera traslapes. Revisa antes de continuar.</p>
            </div>
            <div class="max-h-[60vh] space-y-4 overflow-y-auto p-5">
                <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                    @foreach(['horarios'=>'Horarios','calificaciones'=>'Calificaciones','capturas'=>'Capturas','entregadas'=>'Entregadas','validadas'=>'Validadas'] as $key=>$label)
                        <div class="rounded-xl border border-slate-200 p-3 text-center dark:border-neutral-800"><b class="text-xl">{{ $impacto_pendiente[$key] ?? 0 }}</b><span class="block text-xs text-slate-500">{{ $label }}</span></div>
                    @endforeach
                </div>
                @if(count($conflictos_pendientes))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                        <p class="font-black">{{ count($conflictos_pendientes) }} traslape(s) detectado(s)</p>
                        <div class="mt-3 space-y-2 text-sm">
                            @foreach($conflictos_pendientes as $c)
                                <div class="rounded-lg bg-white/70 p-3"><b>{{ $c['dia'] ?? 'Día' }} · {{ $c['hora_origen'] ?? '' }}</b><br>{{ $c['materia_origen'] ?? '' }} ↔ {{ $c['materia_conflicto'] ?? '' }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="flex justify-end gap-2 border-t border-slate-200 p-4 dark:border-neutral-800">
                <flux:button variant="ghost" wire:click="cancelarOperacion" class="cursor-pointer">Cancelar</flux:button>
                <flux:button variant="primary" wire:click="confirmarOperacion" class="cursor-pointer">Asignar de todos modos</flux:button>
            </div>
        </div>
    </div>
</div>
