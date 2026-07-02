<div class="space-y-5">
    <div class="rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 to-lime-50 p-4 dark:border-sky-900/60 dark:from-sky-950/30 dark:to-lime-950/20">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                    Estadística completa de licenciaturas
                </h3>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                    Contabiliza alumnos activos, bajas y egresados por ciclo escolar, licenciatura, modalidad,
                    generación y cuatrimestre.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <flux:button href="{{ $this->pdfVistaUrl }}" target="_blank" variant="filled" icon="eye">
                    Vista previa PDF
                </flux:button>

                <flux:button href="{{ $this->pdfDescargaUrl }}" variant="primary" icon="download">
                    Descargar PDF
                </flux:button>

                <flux:button href="{{ $this->excelUrl }}" variant="primary" icon="sheet"
                    class="!bg-[#88AC2E] hover:!bg-[#779929]">
                    Descargar Excel
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <flux:select wire:model.live="filtrar_ciclo" label="Ciclo escolar">
            <flux:select.option value="">Todos los ciclos</flux:select.option>
            @foreach ($ciclosEscolares as $ciclo)
                <flux:select.option value="{{ $ciclo }}">{{ $ciclo }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtrar_licenciatura" label="Licenciatura">
            <flux:select.option value="">Todas las licenciaturas</flux:select.option>
            @foreach ($licenciaturas as $licenciatura)
                <flux:select.option value="{{ $licenciatura['id'] }}">
                    {{ $licenciatura['nombre'] }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtrar_modalidad" label="Modalidad">
            <flux:select.option value="">Todas las modalidades</flux:select.option>
            @foreach ($modalidades as $modalidad)
                <flux:select.option value="{{ $modalidad['id'] }}">
                    {{ $modalidad['nombre'] }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtrar_generacion" label="Generación">
            <flux:select.option value="">Todas las generaciones</flux:select.option>
            @foreach ($generaciones as $generacion)
                <flux:select.option value="{{ $generacion['id'] }}">
                    {{ $generacion['generacion'] }}{{ $generacion['activa'] === 'false' ? ' · Egresada' : '' }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="filtrar_cuatrimestre" label="Cuatrimestre">
            <flux:select.option value="">Todos los cuatrimestres</flux:select.option>
            @foreach ($cuatrimestres as $cuatrimestre)
                <flux:select.option value="{{ $cuatrimestre['id'] }}">
                    {{ $cuatrimestre['nombre_cuatrimestre'] }}
                </flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="flex flex-col gap-3 rounded-2xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-800 dark:bg-neutral-900/60 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap gap-5">
            <flux:switch wire:model.live="separar_modalidades" label="Separar modalidades" />
            <flux:switch wire:model.live="detalle_cuatrimestres" label="Mostrar cuatrimestres" />
        </div>

        <flux:button wire:click="limpiarFiltros" variant="ghost" icon="x-mark">
            Limpiar filtros
        </flux:button>
    </div>

    @php($reporte = $this->reporte)
    @php($totales = $reporte['totales'])

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">Activos</p>
            <p class="mt-1 text-3xl font-black text-emerald-900 dark:text-emerald-100">{{ $totales['activos_total'] }}</p>
            <p class="mt-1 text-xs text-emerald-700/80 dark:text-emerald-300/80">
                H: {{ $totales['activos_hombres'] }} · M: {{ $totales['activos_mujeres'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/60 dark:bg-red-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-red-700 dark:text-red-300">Bajas</p>
            <p class="mt-1 text-3xl font-black text-red-900 dark:text-red-100">{{ $totales['bajas_total'] }}</p>
            <p class="mt-1 text-xs text-red-700/80 dark:text-red-300/80">
                H: {{ $totales['bajas_hombres'] }} · M: {{ $totales['bajas_mujeres'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-300">Egresados</p>
            <p class="mt-1 text-3xl font-black text-amber-900 dark:text-amber-100">{{ $totales['egresados_total'] }}</p>
            <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-300/80">
                H: {{ $totales['egresados_hombres'] }} · M: {{ $totales['egresados_mujeres'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 dark:border-sky-900/60 dark:bg-sky-950/30">
            <p class="text-xs font-semibold uppercase tracking-wider text-sky-700 dark:text-sky-300">Total general</p>
            <p class="mt-1 text-3xl font-black text-sky-900 dark:text-sky-100">{{ $totales['total_general'] }}</p>
            <p class="mt-1 text-xs text-sky-700/80 dark:text-sky-300/80">
                H: {{ $totales['hombres_total'] }} · M: {{ $totales['mujeres_total'] }}
            </p>
        </div>
    </div>

    <div wire:loading.flex wire:target="filtrar_ciclo,filtrar_licenciatura,filtrar_modalidad,filtrar_generacion,filtrar_cuatrimestre,separar_modalidades,detalle_cuatrimestres,limpiarFiltros"
        class="items-center justify-center rounded-2xl border border-neutral-200 py-10 dark:border-neutral-800">
        <div class="text-center">
            <div class="mx-auto h-9 w-9 animate-spin rounded-full border-4 border-neutral-200 border-t-[#006492]"></div>
            <p class="mt-3 text-sm text-neutral-500">Actualizando estadística…</p>
        </div>
    </div>

    <div wire:loading.remove wire:target="filtrar_ciclo,filtrar_licenciatura,filtrar_modalidad,filtrar_generacion,filtrar_cuatrimestre,separar_modalidades,detalle_cuatrimestres,limpiarFiltros"
        class="space-y-6">
        @forelse ($reporte['secciones'] as $seccion)
            <section class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                <div class="flex flex-col gap-2 bg-[#006492] px-4 py-3 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="font-bold">Distribución escolar</h4>
                        <p class="text-sm text-white/80">Ciclo escolar {{ $seccion['ciclo_escolar'] }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-white/15 px-3 py-1">Activos: {{ $seccion['totales']['activos_total'] }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1">Bajas: {{ $seccion['totales']['bajas_total'] }}</span>
                        <span class="rounded-full bg-white/15 px-3 py-1">Egresados: {{ $seccion['totales']['egresados_total'] }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[1180px] w-full text-xs">
                        <thead>
                            <tr class="bg-[#88AC2E]/20 text-neutral-800 dark:bg-[#88AC2E]/15 dark:text-neutral-100">
                                <th rowspan="2" class="border-b border-r border-neutral-200 px-3 py-2 text-left dark:border-neutral-800">Licenciatura</th>
                                <th rowspan="2" class="border-b border-r border-neutral-200 px-3 py-2 text-left dark:border-neutral-800">RVOE</th>
                                <th rowspan="2" class="border-b border-r border-neutral-200 px-3 py-2 text-left dark:border-neutral-800">Modalidad</th>
                                <th rowspan="2" class="border-b border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">Generación</th>
                                <th rowspan="2" class="border-b border-r border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">Cuatrimestre</th>
                                <th colspan="3" class="border-b border-r border-neutral-200 px-3 py-2 text-center text-emerald-700 dark:border-neutral-800 dark:text-emerald-300">Activos</th>
                                <th colspan="3" class="border-b border-r border-neutral-200 px-3 py-2 text-center text-red-700 dark:border-neutral-800 dark:text-red-300">Bajas</th>
                                <th colspan="3" class="border-b border-r border-neutral-200 px-3 py-2 text-center text-amber-700 dark:border-neutral-800 dark:text-amber-300">Egresados</th>
                                <th rowspan="2" class="border-b border-neutral-200 px-3 py-2 text-center dark:border-neutral-800">Total</th>
                            </tr>
                            <tr class="bg-neutral-50 text-neutral-600 dark:bg-neutral-900 dark:text-neutral-300">
                                @foreach (['H', 'M', 'T', 'H', 'M', 'T', 'H', 'M', 'T'] as $encabezado)
                                    <th class="border-b border-r border-neutral-200 px-2 py-1.5 text-center dark:border-neutral-800">{{ $encabezado }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100 dark:divide-neutral-900">
                            @foreach ($seccion['filas'] as $fila)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-900/70">
                                    <td class="border-r border-neutral-100 px-3 py-2 font-medium text-neutral-900 dark:border-neutral-900 dark:text-neutral-100">{{ $fila['licenciatura'] }}</td>
                                    <td class="border-r border-neutral-100 px-3 py-2 text-neutral-600 dark:border-neutral-900 dark:text-neutral-400">{{ $fila['rvoe'] }}</td>
                                    <td class="border-r border-neutral-100 px-3 py-2 text-neutral-600 dark:border-neutral-900 dark:text-neutral-400">{{ $fila['modalidad'] }}</td>
                                    <td class="border-r border-neutral-100 px-3 py-2 text-center dark:border-neutral-900">{{ $fila['generacion'] }}</td>
                                    <td class="border-r border-neutral-100 px-3 py-2 text-center dark:border-neutral-900">{{ $fila['cuatrimestre'] }}</td>
                                    <td class="border-r border-neutral-100 px-2 py-2 text-center dark:border-neutral-900">{{ $fila['activos_hombres'] }}</td>
                                    <td class="border-r border-neutral-100 px-2 py-2 text-center dark:border-neutral-900">{{ $fila['activos_mujeres'] }}</td>
                                    <td class="border-r border-neutral-100 bg-emerald-50/60 px-2 py-2 text-center font-bold text-emerald-800 dark:border-neutral-900 dark:bg-emerald-950/20 dark:text-emerald-300">{{ $fila['activos_total'] }}</td>
                                    <td class="border-r border-neutral-100 px-2 py-2 text-center dark:border-neutral-900">{{ $fila['bajas_hombres'] }}</td>
                                    <td class="border-r border-neutral-100 px-2 py-2 text-center dark:border-neutral-900">{{ $fila['bajas_mujeres'] }}</td>
                                    <td class="border-r border-neutral-100 bg-red-50/60 px-2 py-2 text-center font-bold text-red-800 dark:border-neutral-900 dark:bg-red-950/20 dark:text-red-300">{{ $fila['bajas_total'] }}</td>
                                    <td class="border-r border-neutral-100 px-2 py-2 text-center dark:border-neutral-900">{{ $fila['egresados_hombres'] }}</td>
                                    <td class="border-r border-neutral-100 px-2 py-2 text-center dark:border-neutral-900">{{ $fila['egresados_mujeres'] }}</td>
                                    <td class="border-r border-neutral-100 bg-amber-50/60 px-2 py-2 text-center font-bold text-amber-800 dark:border-neutral-900 dark:bg-amber-950/20 dark:text-amber-300">{{ $fila['egresados_total'] }}</td>
                                    <td class="px-3 py-2 text-center font-black text-[#006492] dark:text-sky-300">{{ $fila['total_general'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-neutral-100 font-bold text-neutral-900 dark:bg-neutral-900 dark:text-white">
                                <td colspan="5" class="border-t border-r border-neutral-200 px-3 py-2 text-right dark:border-neutral-800">TOTAL DEL CICLO</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $seccion['totales']['activos_hombres'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $seccion['totales']['activos_mujeres'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center text-emerald-700 dark:border-neutral-800 dark:text-emerald-300">{{ $seccion['totales']['activos_total'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $seccion['totales']['bajas_hombres'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $seccion['totales']['bajas_mujeres'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center text-red-700 dark:border-neutral-800 dark:text-red-300">{{ $seccion['totales']['bajas_total'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $seccion['totales']['egresados_hombres'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center dark:border-neutral-800">{{ $seccion['totales']['egresados_mujeres'] }}</td>
                                <td class="border-t border-r border-neutral-200 px-2 py-2 text-center text-amber-700 dark:border-neutral-800 dark:text-amber-300">{{ $seccion['totales']['egresados_total'] }}</td>
                                <td class="border-t border-neutral-200 px-3 py-2 text-center text-[#006492] dark:border-neutral-800 dark:text-sky-300">{{ $seccion['totales']['total_general'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @empty
            <div class="rounded-2xl border border-dashed border-neutral-300 px-6 py-12 text-center dark:border-neutral-700">
                <p class="font-semibold text-neutral-700 dark:text-neutral-200">No hay alumnos que coincidan con los filtros.</p>
                <p class="mt-1 text-sm text-neutral-500">Limpia uno o más filtros para consultar nuevamente.</p>
            </div>
        @endforelse
    </div>

    <div class="rounded-xl border border-neutral-200 bg-neutral-50 px-4 py-3 text-xs text-neutral-600 dark:border-neutral-800 dark:bg-neutral-900/60 dark:text-neutral-400">
        <strong>Criterio:</strong> las bajas se identifican por <code>status = false</code> o fecha de baja;
        los egresados por <code>egresado = true</code>, generación inactiva o término del 9.º cuatrimestre. Un alumno dado de baja no se duplica como egresado.
    </div>
</div>
