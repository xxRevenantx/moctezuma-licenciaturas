<div x-data="{ show: @entangle('movimientoOpen').live }"
     x-show="show"
     x-cloak
     @keydown.escape.window="$wire.cerrarMovimiento()"
     class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/55 p-3 backdrop-blur-sm sm:p-6">
    <div @click.outside="if ($wire.movimientoPaso === 1) $wire.cerrarMovimiento()"
         class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl dark:border-neutral-700 dark:bg-neutral-900">

        <div class="flex items-start justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-[#006492] to-[#0879a8] px-5 py-5 text-white dark:border-neutral-800 sm:px-7">
            <div>
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-white/70">
                    Movimiento académico seguro
                    <span class="rounded-full bg-white/15 px-2 py-1">Paso {{ $movimientoPaso }} de 3</span>
                </div>
                <h3 class="mt-2 text-xl font-black">
                    {{ $movimientoTipo === 'cambio_modalidad' ? 'Cambio de modalidad' : 'Cambio de cuatrimestre' }}
                </h3>
                <p class="mt-1 text-sm text-white/80">
                    {{ count($movimientoAlumnoIds) }} estudiante(s) · validación previa obligatoria
                </p>
            </div>
            <button type="button" wire:click="cerrarMovimiento" class="rounded-xl bg-white/10 p-2 text-white transition hover:bg-white/20" aria-label="Cerrar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>

        <div class="overflow-y-auto p-5 sm:p-7">
            {{-- Stepper --}}
            <div class="mb-6 grid grid-cols-3 gap-2">
                @foreach([1 => 'Destino', 2 => 'Impacto', 3 => 'Confirmación'] as $paso => $etiqueta)
                    <div class="rounded-2xl border px-3 py-2.5 text-center text-xs font-bold {{ $movimientoPaso >= $paso ? 'border-[#006492]/30 bg-[#006492]/8 text-[#006492] dark:bg-[#006492]/15 dark:text-sky-200' : 'border-slate-200 bg-slate-50 text-slate-400 dark:border-neutral-700 dark:bg-neutral-800' }}">
                        {{ $paso }}. {{ $etiqueta }}
                    </div>
                @endforeach
            </div>

            @if($movimientoPaso === 1)
                <div class="grid gap-6 lg:grid-cols-5">
                    <div class="space-y-5 lg:col-span-3">
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">1. Define el destino</h4>
                            <p class="mt-1 text-xs text-slate-500">La licenciatura y la generación permanecen sin cambios.</p>

                            <div class="mt-4">
                                @if($movimientoTipo === 'cambio_modalidad')
                                    <flux:field>
                                        <flux:label>Modalidad destino</flux:label>
                                        <flux:select wire:model="movimientoModalidadDestinoId">
                                            <flux:select.option value="">Selecciona una modalidad</flux:select.option>
                                            @foreach($modalidadesDestino as $destino)
                                                <flux:select.option value="{{ $destino->id }}">{{ $destino->nombre }}</flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="movimientoModalidadDestinoId" />
                                    </flux:field>
                                @else
                                    <flux:field>
                                        <flux:label>Cuatrimestre destino</flux:label>
                                        <flux:select wire:model="movimientoCuatrimestreDestinoId">
                                            <flux:select.option value="">Selecciona un cuatrimestre</flux:select.option>
                                            @foreach($cuatrimestres as $periodo)
                                                <flux:select.option value="{{ $periodo->cuatrimestre_id }}">
                                                    {{ $periodo->cuatrimestre?->nombre_cuatrimestre }}
                                                </flux:select.option>
                                            @endforeach
                                        </flux:select>
                                        <flux:error name="movimientoCuatrimestreDestinoId" />
                                    </flux:field>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                            <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">2. Motivo y trazabilidad</h4>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <flux:field>
                                    <flux:label>Motivo</flux:label>
                                    <flux:select wire:model="movimientoMotivo">
                                        <flux:select.option value="">Selecciona un motivo</flux:select.option>
                                        <flux:select.option value="solicitud_alumno">Solicitud del alumno</flux:select.option>
                                        <flux:select.option value="ajuste_administrativo">Ajuste administrativo</flux:select.option>
                                        <flux:select.option value="cambio_horario">Cambio de horario</flux:select.option>
                                        <flux:select.option value="otro">Otro</flux:select.option>
                                    </flux:select>
                                    <flux:error name="movimientoMotivo" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>Observaciones</flux:label>
                                    <textarea wire:model="movimientoObservaciones" rows="3"
                                              class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-[#006492] focus:ring-2 focus:ring-[#006492]/10 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white"
                                              placeholder="Opcional, excepto cuando eliges Otro"></textarea>
                                    @error('movimientoObservaciones') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </flux:field>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 dark:border-neutral-700 dark:bg-neutral-800/60">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-extrabold text-slate-800 dark:text-white">Alumnos incluidos</h4>
                                <span class="rounded-full bg-[#006492]/10 px-2.5 py-1 text-xs font-bold text-[#006492] dark:text-sky-200">{{ $movimientoAlumnos->count() }}</span>
                            </div>
                            <div class="mt-3 max-h-80 space-y-2 overflow-y-auto pr-1">
                                @foreach($movimientoAlumnos as $alumno)
                                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-neutral-700 dark:bg-neutral-900">
                                        <div class="text-xs font-extrabold text-slate-800 dark:text-white">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</div>
                                        <div class="mt-1 flex flex-wrap gap-2 text-[10px] font-semibold text-slate-500">
                                            <span>{{ $alumno->matricula }}</span>
                                            <span>•</span>
                                            <span>{{ $alumno->modalidad?->nombre }}</span>
                                            <span>•</span>
                                            <span>{{ $alumno->cuatrimestre?->nombre_cuatrimestre }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($movimientoPaso === 2)
                @php($analisisValido = $movimientoAnalisis['valido'] ?? false)
                <div class="grid gap-5 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="rounded-2xl border p-5 {{ $analisisValido ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900 dark:bg-emerald-950/20' : 'border-rose-200 bg-rose-50/70 dark:border-rose-900 dark:bg-rose-950/20' }}">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $analisisValido ? 'bg-emerald-600' : 'bg-rose-600' }} text-white">
                                    @if($analisisValido)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" stroke-width="2"><path stroke-linecap="round" d="M12 8v5m0 3h.01"/><circle cx="12" cy="12" r="9"/></svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="font-black {{ $analisisValido ? 'text-emerald-800 dark:text-emerald-200' : 'text-rose-800 dark:text-rose-200' }}">
                                        {{ $analisisValido ? 'El movimiento puede ejecutarse de forma segura' : 'Movimiento bloqueado' }}
                                    </h4>
                                    <p class="mt-1 text-xs {{ $analisisValido ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                                        La operación se ejecutará dentro de una transacción; ante un error no quedarán cambios parciales.
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if(!empty($movimientoAnalisis['bloqueos']))
                            <div class="rounded-2xl border border-rose-200 p-4 dark:border-rose-900/60">
                                <h5 class="text-xs font-black uppercase tracking-wider text-rose-700 dark:text-rose-300">Bloqueos detectados</h5>
                                <ul class="mt-3 space-y-2 text-sm text-rose-700 dark:text-rose-200">
                                    @foreach($movimientoAnalisis['bloqueos'] as $bloqueo)
                                        <li class="flex gap-2"><span>•</span><span>{{ $bloqueo }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($movimientoAnalisis['advertencias']))
                            <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 dark:border-amber-900/60 dark:bg-amber-950/10">
                                <h5 class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-300">Acciones automáticas / advertencias</h5>
                                <ul class="mt-3 space-y-2 text-sm text-amber-700 dark:text-amber-200">
                                    @foreach($movimientoAnalisis['advertencias'] as $advertencia)
                                        <li class="flex gap-2"><span>•</span><span>{{ $advertencia }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($movimientoAnalisis['materias_faltantes']))
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                                <h5 class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-neutral-300">Materias que deben configurarse en destino</h5>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach($movimientoAnalisis['materias_faltantes'] as $materia)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-neutral-800 dark:text-neutral-300">{{ $materia }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                            <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Alumnos</div>
                            <div class="mt-1 text-3xl font-black text-slate-800 dark:text-white">{{ $movimientoAnalisis['total_alumnos'] ?? 0 }}</div>
                        </div>
                        @if($movimientoTipo === 'cambio_modalidad')
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Calificaciones a conservar</div>
                                <div class="mt-1 text-3xl font-black text-[#006492] dark:text-sky-300">{{ $movimientoAnalisis['calificaciones_total'] ?? 0 }}</div>
                                <div class="mt-1 text-xs text-slate-500">Se remapean por materia; el valor no cambia.</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Generación destino</div>
                                <div class="mt-2 text-sm font-extrabold {{ ($movimientoAnalisis['creara_asignacion_generacion'] ?? false) ? 'text-amber-600' : 'text-emerald-600' }}">
                                    {{ ($movimientoAnalisis['creara_asignacion_generacion'] ?? false) ? 'Se habilitará automáticamente' : 'Ya está habilitada' }}
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Calificaciones históricas</div>
                                <div class="mt-2 text-sm font-extrabold text-emerald-600">Sin cambios</div>
                                <div class="mt-1 text-xs text-slate-500">Solo cambia el cuatrimestre actual del alumno.</div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="mx-auto max-w-3xl">
                    <div class="rounded-3xl border border-[#006492]/20 bg-[#006492]/5 p-6 text-center dark:bg-[#006492]/10">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#006492] text-white">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-6 w-6" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007M10.3 4.3L2.8 18a1.5 1.5 0 001.31 2.25h15.78A1.5 1.5 0 0021.2 18L13.7 4.3a1.95 1.95 0 00-3.4 0z"/></svg>
                        </div>
                        <h4 class="mt-4 text-lg font-black text-slate-900 dark:text-white">Confirmación final</h4>
                        <p class="mx-auto mt-2 max-w-xl text-sm text-slate-600 dark:text-neutral-300">
                            Estás por aplicar el movimiento a <strong>{{ $movimientoAnalisis['total_alumnos'] ?? count($movimientoAlumnoIds) }} estudiante(s)</strong>.
                            @if($movimientoTipo === 'cambio_modalidad')
                                Se conservarán <strong>{{ $movimientoAnalisis['calificaciones_total'] ?? 0 }} calificaciones</strong> y se cambiarán a las asignaciones equivalentes de la modalidad destino.
                            @else
                                Las calificaciones históricas no serán modificadas.
                            @endif
                        </p>
                    </div>

                    <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                        <input type="checkbox" wire:model="movimientoConfirmado" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#006492] focus:ring-[#006492]">
                        <span class="text-sm text-slate-700 dark:text-neutral-200">
                            Confirmo que revisé el impacto, los alumnos seleccionados y el destino académico. Entiendo que el movimiento quedará registrado en la bitácora y podrá revertirse solo si no existen datos nuevos incompatibles.
                        </span>
                    </label>
                    @error('movimientoConfirmado') <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p> @enderror
                </div>
            @endif
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-950/40 sm:flex-row sm:items-center sm:justify-between sm:px-7">
            <div>
                @if($movimientoPaso > 1)
                    <button type="button" wire:click="volverPasoMovimiento"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-600 transition hover:border-[#006492]/30 hover:text-[#006492] dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-200">
                        Atrás
                    </button>
                @endif
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" wire:click="cerrarMovimiento"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-200/60 dark:hover:bg-neutral-800">
                    Cancelar
                </button>

                @if($movimientoPaso === 1)
                    <button type="button" wire:click="revisarImpacto" wire:loading.attr="disabled"
                            class="rounded-xl bg-[#006492] px-5 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#005477] disabled:opacity-60">
                        Revisar impacto
                    </button>
                @elseif($movimientoPaso === 2)
                    <button type="button" wire:click="irConfirmacionMovimiento" @disabled(!($movimientoAnalisis['valido'] ?? false))
                            class="rounded-xl bg-[#006492] px-5 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#005477] disabled:cursor-not-allowed disabled:bg-slate-300">
                        Continuar
                    </button>
                @else
                    <button type="button" wire:click="ejecutarMovimiento" wire:loading.attr="disabled" wire:target="ejecutarMovimiento"
                            class="rounded-xl bg-[#88AC2E] px-5 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:brightness-95 disabled:opacity-60">
                        <span wire:loading.remove wire:target="ejecutarMovimiento">Aplicar movimiento</span>
                        <span wire:loading wire:target="ejecutarMovimiento">Procesando…</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
