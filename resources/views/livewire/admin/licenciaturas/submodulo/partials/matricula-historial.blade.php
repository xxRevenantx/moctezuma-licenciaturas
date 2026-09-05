@if($filtrar_generacion && $movimientosRecientes->isNotEmpty())
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
             x-data="{
                confirmarReversion(id, lote) {
                    Swal.fire({
                        title: 'Revertir movimiento ' + lote,
                        text: 'El sistema verificará que no existan calificaciones o datos nuevos incompatibles antes de revertir.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#006492',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Verificar y revertir',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) $wire.revertirMovimiento(id)
                    })
                }
             }">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-neutral-800 sm:px-6">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Historial de movimientos</h3>
                <p class="mt-0.5 text-xs text-slate-500">Últimos movimientos de esta licenciatura, generación y modalidad.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:bg-neutral-800 dark:text-neutral-300">Auditoría</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[1050px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:bg-neutral-800/80 dark:text-neutral-300">
                    <tr>
                        <th class="px-5 py-3">Lote</th>
                        <th class="px-3 py-3">Alumno</th>
                        <th class="px-3 py-3">Movimiento</th>
                        <th class="px-3 py-3">Motivo</th>
                        <th class="px-3 py-3">Calif.</th>
                        <th class="px-3 py-3">Usuario / Fecha</th>
                        <th class="px-3 py-3">Estado</th>
                        <th class="px-5 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                    @foreach($movimientosRecientes as $movimiento)
                        @php
                            $motivoLabel = match($movimiento->motivo) {
                                'solicitud_alumno' => 'Solicitud del alumno',
                                'ajuste_administrativo' => 'Ajuste administrativo',
                                'cambio_horario' => 'Cambio de horario',
                                'otro' => 'Otro',
                                default => $movimiento->motivo,
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-neutral-800/30">
                            <td class="px-5 py-3 font-mono text-xs font-bold text-[#006492] dark:text-sky-300">{{ $movimiento->lote }}</td>
                            <td class="px-3 py-3">
                                <div class="text-xs font-extrabold text-slate-800 dark:text-white">{{ $movimiento->alumno_snapshot }}</div>
                                <div class="mt-0.5 text-[10px] text-slate-400">SEG: {{ $movimiento->matricula_snapshot ?: 'Pendiente' }} · ID: {{ $movimiento->matricula_interna_snapshot ?: '—' }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs text-slate-600 dark:text-neutral-300">
                                @if($movimiento->tipo === 'cambio_modalidad')
                                    <div class="font-bold">Modalidad</div>
                                    <div class="mt-0.5 text-[10px] text-slate-400">{{ $movimiento->modalidadOrigen?->nombre }} → {{ $movimiento->modalidadDestino?->nombre }}</div>
                                @else
                                    <div class="font-bold">Cuatrimestre</div>
                                    <div class="mt-0.5 text-[10px] text-slate-400">{{ $movimiento->cuatrimestreOrigen?->nombre_cuatrimestre }} → {{ $movimiento->cuatrimestreDestino?->nombre_cuatrimestre }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-slate-600 dark:text-neutral-300">{{ $motivoLabel }}</td>
                            <td class="px-3 py-3 text-xs font-bold text-slate-700 dark:text-neutral-200">{{ $movimiento->calificaciones_trasladadas }}</td>
                            <td class="px-3 py-3 text-xs text-slate-600 dark:text-neutral-300">
                                <div>{{ $movimiento->ejecutor?->username ?: 'Sistema' }}</div>
                                <div class="mt-0.5 text-[10px] text-slate-400">{{ optional($movimiento->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-3 py-3">
                                @if($movimiento->estado === 'aplicado')
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Aplicado</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-500 dark:bg-neutral-800 dark:text-neutral-300">Revertido</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($movimiento->estado === 'aplicado')
                                    <button type="button" @click="confirmarReversion({{ $movimiento->id }}, @js($movimiento->lote))"
                                            class="rounded-xl border border-slate-200 px-3 py-2 text-[11px] font-bold text-slate-600 transition hover:border-amber-300 hover:text-amber-700 dark:border-neutral-700 dark:text-neutral-300">
                                        Revertir
                                    </button>
                                @else
                                    <span class="text-[11px] text-slate-400">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif
