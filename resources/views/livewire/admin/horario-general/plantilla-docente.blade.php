<div class="space-y-5">
    <section class="overflow-hidden rounded-2xl border border-sky-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
        <div class="h-1 bg-gradient-to-r from-[#006492] via-cyan-400 to-[#88ac2e]"></div>
        <div class="flex flex-col gap-4 bg-gradient-to-r from-sky-50 to-lime-50/60 p-5 dark:from-sky-950/30 dark:to-neutral-900 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">Plantilla Docente por Licenciatura PRO</h2>
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-[10px] font-bold text-[#006492] dark:bg-sky-900 dark:text-sky-200">Profesores programados</span>
                </div>
                <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-slate-300">Reportes institucionales por licenciatura, sin duplicar docentes aunque tengan varias materias o grupos.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $urlPreview }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 dark:bg-neutral-800 dark:text-white">Vista previa PDF</a>
                <a href="{{ $urlPdf }}" target="_blank" rel="noopener" class="rounded-lg bg-blue-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-blue-700">Descargar PDF</a>
                <a href="{{ $urlWord }}" target="_blank" rel="noopener" class="rounded-lg bg-[#006492] px-4 py-2.5 text-xs font-bold text-white hover:bg-sky-800">Descargar Word</a>
                <a href="{{ $urlExcel }}" target="_blank" rel="noopener" class="rounded-lg bg-[#88ac2e] px-4 py-2.5 text-xs font-bold text-white hover:bg-lime-700">Descargar Excel</a>
            </div>
        </div>
        <div class="p-5">
            <div class="mb-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-300">Ciclo escolar</label>
                    <select wire:model.live="ciclo" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                        @foreach($ciclos as $opcion)
                            <option value="{{ $opcion }}">{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700 dark:text-slate-300">Periodo académico</label>
                    <select wire:model.live="periodo" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm dark:border-neutral-700 dark:bg-neutral-800 dark:text-white">
                        @foreach($periodos as $opcion)
                            <option value="{{ $opcion }}">{{ $opcion }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach(['licenciaturas'=>'Licenciaturas','modalidades'=>'Modalidades','generaciones'=>'Generaciones','cuatrimestres'=>'Cuatrimestres'] as $categoria=>$titulo)
                    @php
                        $opcionesCategoria = $opciones[$categoria] ?? [];
                        $ids = $seleccion[$categoria] ?? null;
                        $coincidencias = array_values(array_filter($opcionesCategoria, fn ($op) => mb_stripos($op['nombre'], trim($buscar[$categoria] ?? '')) !== false));
                    @endphp
                    <div wire:key="filtro-{{ $categoria }}" class="min-w-0 rounded-xl border border-slate-200 bg-slate-50/75 p-3 dark:border-neutral-700 dark:bg-neutral-800/70">
                        <div class="flex items-center justify-between gap-1">
                            <label class="text-xs font-extrabold text-slate-800 dark:text-slate-100">{{ $titulo }}</label>
                            <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold text-[#006492] dark:bg-neutral-700 dark:text-sky-200">
                                {{ $ids === null ? 'Todas' : count($ids).' de '.count($opcionesCategoria) }}
                            </span>
                        </div>
                        <div class="my-2 flex flex-wrap gap-1.5">
                            <button wire:click="todas('{{ $categoria }}')" type="button" class="rounded-md bg-white px-2.5 py-1 text-[11px] font-semibold text-sky-700 ring-1 ring-slate-200 dark:bg-neutral-700 dark:text-sky-200">Seleccionar todas</button>
                            <button wire:click="ninguna('{{ $categoria }}')" type="button" class="rounded-md bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200 dark:bg-neutral-700 dark:text-slate-200">Ninguna</button>
                        </div>
                        <input wire:model.live.debounce.250ms="buscar.{{ $categoria }}" placeholder="Buscar {{ mb_strtolower($titulo) }}" aria-label="Buscar {{ $titulo }}"
                            class="mb-2 w-full rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs dark:border-neutral-700 dark:bg-neutral-900 dark:text-white">
                        <div class="max-h-40 space-y-1 overflow-y-auto pr-1">
                            @forelse($coincidencias as $op)
                                <label wire:key="{{ $categoria }}-{{ $op['id'] }}" class="flex cursor-pointer items-start gap-2 rounded-md px-2 py-1 text-xs text-slate-700 hover:bg-white dark:text-slate-200 dark:hover:bg-neutral-700">
                                    <input type="checkbox" @checked($ids === null || in_array($op['id'], $ids, true)) wire:click="alternar('{{ $categoria }}', {{ $op['id'] }})"
                                        class="mt-0.5 rounded border-slate-300 text-[#006492]">
                                    <span>{{ $op['nombre'] }}</span>
                                </label>
                            @empty
                                <p class="px-2 py-2 text-xs text-slate-500">No hay opciones disponibles.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-5 rounded-lg border border-slate-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900">
                <span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">Columnas del reporte</span>
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"><input wire:model.live="materias" type="checkbox" class="rounded border-slate-300"> Materias</label>
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"><input wire:model.live="cargaHoraria" type="checkbox" class="rounded border-slate-300"> Carga horaria</label>
                <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"><input wire:model.live="mostrarGeneraciones" type="checkbox" class="rounded border-slate-300"> Generaciones</label>
            </div>
        </div>
    </section>

    <div wire:loading.delay wire:target="ciclo,periodo,alternar,todas,ninguna,materias,cargaHoraria,mostrarGeneraciones" class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-xs font-bold text-[#006492]">Actualizando plantilla docente…</div>
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(['profesores_unicos'=>'Docentes únicos','participaciones'=>'Docentes por licenciatura','licenciaturas'=>'Licenciaturas','horas'=>'Horas programadas'] as $campo=>$titulo)
            <div class="rounded-xl border border-sky-100 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $titulo }}</div>
                <div class="mt-1 text-2xl font-black text-[#006492] dark:text-sky-300">{{ $reporte['totales'][$campo] }}</div>
            </div>
        @endforeach
    </div>
    <p class="text-xs text-slate-500 dark:text-neutral-400">Cada docente se cuenta una vez por licenciatura. En el total institucional de docentes únicos se cuenta solo una vez. Las horas corresponden a la suma de los bloques en los horarios, no al número de materias.</p>

    @forelse($reporte['grupos'] as $grupo)
        <section wire:key="plantilla-lic-{{ $grupo['licenciatura_id'] }}" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-[#006492] px-4 py-3 text-white">
                <h3 class="text-sm font-extrabold uppercase tracking-wide">{{ $grupo['licenciatura'] }}</h3>
                <div class="flex gap-2 text-[11px] font-bold"><span class="rounded-full bg-white/15 px-2.5 py-1">{{ $grupo['total_docentes'] }} docentes</span><span class="rounded-full bg-white/15 px-2.5 py-1">{{ $grupo['horas'] }} h</span></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[820px] text-left text-xs">
                    <thead class="bg-slate-100 text-[10px] font-bold uppercase text-slate-600 dark:bg-neutral-800 dark:text-slate-300">
                        <tr><th class="p-3">No.</th><th class="p-3">Docente</th>
                            @if($materias)<th class="p-3">Materias</th>@endif
                            <th class="p-3">Cuatrimestres</th><th class="p-3">Modalidades</th>
                            @if($mostrarGeneraciones)<th class="p-3">Generaciones</th>@endif
                            @if($cargaHoraria)<th class="p-3">Bloques</th><th class="p-3">Horas</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                        @foreach($grupo['docentes'] as $numero=>$docente)
                        <tr class="hover:bg-slate-50 dark:hover:bg-neutral-800/60"><td class="p-3">{{ $numero + 1 }}</td><td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ $docente['nombre'] }}</td>
                            @if($materias)<td class="p-3 text-slate-600 dark:text-slate-300">{{ implode(', ', $docente['materias']) ?: '—' }}</td>@endif
                            <td class="p-3">{{ implode(', ', $docente['cuatrimestres']) }}</td><td class="p-3">{{ implode(', ', $docente['modalidades']) }}</td>
                            @if($mostrarGeneraciones)<td class="p-3">{{ implode(', ', $docente['generaciones']) }}</td>@endif
                            @if($cargaHoraria)<td class="p-3">{{ $docente['bloques'] }}</td><td class="p-3 font-bold">{{ $docente['horas'] }}</td>@endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @empty
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-9 text-center text-sm text-slate-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-slate-300">No hay profesores con horarios programados que coincidan con los filtros. Verifica el ciclo, el periodo y las opciones seleccionadas.</div>
    @endforelse
</div>
