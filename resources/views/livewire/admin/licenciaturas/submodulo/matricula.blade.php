<div class="space-y-5"
     x-data="{
        confirmarBaja(id, nombre) {
            Swal.fire({
                title: 'Registrar baja académica',
                text: `Se dará de baja a ${nombre}. Su expediente y calificaciones permanecerán intactos.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#006492',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, registrar baja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) $wire.darBajaEstudiante(id)
            })
        }
     }">

    {{-- Panel de control --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="border-b border-slate-100 bg-gradient-to-r from-[#006492]/10 via-white to-[#88AC2E]/10 px-5 py-5 dark:border-neutral-800 dark:from-[#006492]/20 dark:via-neutral-900 dark:to-[#88AC2E]/15 sm:px-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex items-start gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#006492] text-white shadow-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">Gestión de matrícula</h3>
                            <span class="rounded-full bg-[#006492]/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-[#006492] dark:bg-[#006492]/25 dark:text-sky-200">
                                {{ $modalidad->nombre }}
                            </span>
                        </div>
                        <p class="mt-1 max-w-3xl text-sm text-slate-500 dark:text-neutral-400">
                            Filtra, selecciona y realiza movimientos académicos auditables sin perder calificaciones históricas.
                        </p>
                    </div>
                </div>

                @if($filtrar_generacion)
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-5">
                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-center shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Activos</div>
                            <div class="text-lg font-black text-slate-800 dark:text-white">{{ $resumen['total'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-center shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Seleccionados</div>
                            <div class="text-lg font-black text-[#006492] dark:text-sky-300">{{ count($selected) }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-center shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Locales</div>
                            <div class="text-lg font-black text-slate-800 dark:text-white">{{ $resumen['locales'] }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-center shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Foráneos</div>
                            <div class="text-lg font-black text-slate-800 dark:text-white">{{ $resumen['foraneos'] }}</div>
                        </div>
                        <div class="col-span-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-center shadow-sm dark:border-neutral-700 dark:bg-neutral-800 sm:col-span-1">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">H / M</div>
                            <div class="text-sm font-black text-slate-800 dark:text-white">{{ $resumen['hombres'] }} / {{ $resumen['mujeres'] }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-12 sm:p-6">
            <div class="lg:col-span-3">
                <flux:field>
                    <flux:label>Generación</flux:label>
                    <flux:select wire:model.live="filtrar_generacion">
                        <flux:select.option value="">Selecciona una generación</flux:select.option>
                        @foreach($generaciones as $generacion)
                            <flux:select.option value="{{ $generacion->generacion_id }}">
                                {{ $generacion->generacion?->generacion }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="lg:col-span-2">
                <flux:field>
                    <flux:label>Origen</flux:label>
                    <flux:select wire:model.live="filtrar_foraneo">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="false">Local</flux:select.option>
                        <flux:select.option value="true">Foráneo</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="lg:col-span-5">
                <flux:field>
                    <flux:label>Buscar estudiante</flux:label>
                    <flux:input
                        wire:model.live.debounce.350ms="search"
                        placeholder="Nombre, apellidos, matrícula, folio o CURP"
                    />
                </flux:field>
            </div>

            <div class="lg:col-span-1">
                <flux:field>
                    <flux:label>Filas</flux:label>
                    <flux:select wire:model.live="perPage">
                        <flux:select.option value="25">25</flux:select.option>
                        <flux:select.option value="50">50</flux:select.option>
                        <flux:select.option value="100">100</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex items-end lg:col-span-1">
                <button type="button" wire:click="limpiarFiltros"
                        class="inline-flex h-10 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:border-[#006492]/40 hover:text-[#006492] dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200">
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- Barra de selección y acciones masivas --}}
    @if($filtrar_generacion)
        <div class="sticky top-2 z-20 rounded-2xl border border-slate-200/90 bg-white/95 p-3 shadow-lg shadow-slate-200/40 backdrop-blur dark:border-neutral-700 dark:bg-neutral-900/95 dark:shadow-none">
            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="seleccionarPagina"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-[#006492]/40 hover:text-[#006492] dark:border-neutral-700 dark:text-neutral-200">
                        Seleccionar página
                    </button>
                    <button type="button" wire:click="seleccionarTodosFiltrados"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-[#006492]/40 hover:text-[#006492] dark:border-neutral-700 dark:text-neutral-200">
                        Seleccionar todos los filtrados
                    </button>
                    @if(count($selected) > 0)
                        <button type="button" wire:click="limpiarSeleccionados"
                                class="rounded-xl px-3 py-2 text-xs font-bold text-slate-500 transition hover:bg-slate-100 dark:hover:bg-neutral-800">
                            Limpiar selección
                        </button>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if(count($selected) > 0)
                        <span class="mr-1 rounded-full bg-[#006492]/10 px-3 py-2 text-xs font-extrabold text-[#006492] dark:bg-[#006492]/25 dark:text-sky-200">
                            {{ count($selected) }} seleccionado(s)
                        </span>
                        <button type="button" wire:click="abrirMovimientoAcademico(null, 'cambio_modalidad')"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#006492] px-4 py-2 text-xs font-extrabold text-white shadow-sm transition hover:bg-[#005477]">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" stroke-width="1.9">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3 3m-3-3 3-3"/>
                            </svg>
                            Cambiar modalidad
                        </button>
                        <button type="button" wire:click="abrirMovimientoAcademico(null, 'cambio_cuatrimestre')"
                                class="inline-flex items-center gap-2 rounded-xl bg-[#88AC2E] px-4 py-2 text-xs font-extrabold text-white shadow-sm transition hover:brightness-95">
                            Cambiar cuatrimestre
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Tabla --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 dark:border-neutral-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Estudiantes activos</h3>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-neutral-400">
                    Los cambios de modalidad se validan contra materias, equivalencias y calificaciones antes de ejecutarse.
                </p>
            </div>

            @if($matricula->count() > 0)
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="exportarMatriculaPDF" wire:loading.attr="disabled" wire:target="exportarMatriculaPDF"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-[#006492]/40 hover:text-[#006492] disabled:opacity-60 dark:border-neutral-700 dark:text-neutral-200">
                        PDF
                    </button>
                    <button type="button" wire:click="exportarMatricula"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-neutral-700 dark:text-neutral-200">
                        Excel
                    </button>
                </div>
            @endif
        </div>

        <div wire:loading.flex wire:target="search,filtrar_generacion,filtrar_foraneo,perPage" class="items-center justify-center gap-3 py-12 text-sm font-semibold text-[#006492]">
            <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            Actualizando matrícula…
        </div>

        <div wire:loading.remove wire:target="search,filtrar_generacion,filtrar_foraneo,perPage">
            <div class="overflow-x-auto">
                <table class="min-w-[1250px] w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] font-black uppercase tracking-wider text-slate-500 dark:bg-neutral-800/80 dark:text-neutral-300">
                        <tr>
                            <th class="px-4 py-3 text-center">Sel.</th>
                            <th class="px-3 py-3">#</th>
                            <th class="px-3 py-3">Alumno</th>
                            <th class="px-3 py-3">Matrícula / Folio</th>
                            <th class="px-3 py-3">CURP</th>
                            <th class="px-3 py-3">Origen</th>
                            <th class="px-3 py-3">Género</th>
                            <th class="px-3 py-3">Cuatrimestre</th>
                            <th class="px-3 py-3">Generación</th>
                            <th class="px-3 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                        @if(!$filtrar_generacion)
                            <tr>
                                <td colspan="10" class="px-6 py-14 text-center">
                                    <div class="mx-auto max-w-md">
                                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-neutral-800">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-6 w-6" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M6 21h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <h4 class="mt-3 font-bold text-slate-700 dark:text-neutral-200">Selecciona una generación</h4>
                                        <p class="mt-1 text-xs text-slate-500">La matrícula se carga únicamente cuando existe un contexto académico seleccionado.</p>
                                    </div>
                                </td>
                            </tr>
                        @elseif($matricula->count() === 0)
                            <tr>
                                <td colspan="10" class="px-6 py-14 text-center text-sm text-slate-500">
                                    No hay estudiantes activos que coincidan con los filtros.
                                </td>
                            </tr>
                        @else
                            @foreach($matricula as $key => $estudiante)
                                @php($estaSeleccionado = in_array((int) $estudiante->id, array_map('intval', $selected), true))
                                <tr wire:key="matricula-{{ $estudiante->id }}"
                                    class="transition {{ $estaSeleccionado ? 'bg-sky-50/80 dark:bg-sky-950/20' : 'hover:bg-slate-50/80 dark:hover:bg-neutral-800/40' }}">
                                    <td class="px-4 py-3 text-center align-middle">
                                        <input type="checkbox" wire:model.live="selected" value="{{ $estudiante->id }}"
                                               class="h-4 w-4 rounded border-slate-300 text-[#006492] focus:ring-[#006492]">
                                    </td>
                                    <td class="px-3 py-3 text-xs font-semibold text-slate-400">
                                        {{ ($matricula->firstItem() ?? 1) + $key }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <div class="flex min-w-[260px] items-center gap-3">
                                            @if($estudiante->foto)
                                                <img src="{{ asset('storage/estudiantes/' . $estudiante->foto) }}" alt="Foto" class="h-10 w-10 rounded-xl object-cover ring-1 ring-slate-200">
                                            @else
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#006492]/10 font-extrabold text-[#006492]">
                                                    {{ mb_substr($estudiante->nombre, 0, 1) }}{{ mb_substr($estudiante->apellido_paterno, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-extrabold text-slate-800 dark:text-white">
                                                    {{ $estudiante->apellido_paterno }} {{ $estudiante->apellido_materno }} {{ $estudiante->nombre }}
                                                </div>
                                                <div class="mt-1 flex items-center gap-1.5">
                                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">Activo</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-slate-600 dark:text-neutral-300">
                                        <div class="font-bold text-slate-700 dark:text-neutral-200">{{ $estudiante->matricula }}</div>
                                        <div class="mt-0.5 text-slate-400">{{ $estudiante->folio ?: 'Sin folio' }}</div>
                                    </td>
                                    <td class="px-3 py-3 font-mono text-[11px] text-slate-600 dark:text-neutral-300">{{ $estudiante->CURP }}</td>
                                    <td class="px-3 py-3">
                                        @if($estudiante->foraneo === 'true')
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Foráneo</span>
                                        @else
                                            <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-[10px] font-bold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">Local</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="text-xs font-bold {{ $estudiante->sexo === 'H' ? 'text-blue-600' : 'text-pink-600' }}">
                                            {{ $estudiante->sexo === 'H' ? 'Hombre' : 'Mujer' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-xs font-semibold text-slate-700 dark:text-neutral-200">{{ $estudiante->cuatrimestre?->nombre_cuatrimestre }}</td>
                                    <td class="px-3 py-3 text-xs font-semibold text-slate-700 dark:text-neutral-200">{{ $estudiante->generacion?->generacion }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" wire:click="abrirMovimientoAcademico({{ $estudiante->id }}, 'cambio_modalidad')"
                                                    title="Movimiento académico"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#006492]/10 text-[#006492] transition hover:bg-[#006492] hover:text-white">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" stroke-width="1.9">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3 3m-3-3 3-3"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="Livewire.dispatch('abrirEstudiante', { id: {{ $estudiante->id }} })"
                                                    title="Editar datos personales"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 transition hover:bg-amber-500 hover:text-white dark:bg-amber-900/30 dark:text-amber-300">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                </svg>
                                            </button>
                                            <button type="button"
                                                    @click="confirmarBaja({{ $estudiante->id }}, @js(trim($estudiante->apellido_paterno.' '.$estudiante->apellido_materno.' '.$estudiante->nombre)))"
                                                    title="Registrar baja"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-700 transition hover:bg-rose-600 hover:text-white dark:bg-rose-900/30 dark:text-rose-300">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            @if($matricula->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 dark:border-neutral-800">
                    {{ $matricula->links() }}
                </div>
            @endif
        </div>
    </section>

    @include('livewire.admin.licenciaturas.submodulo.partials.matricula-historial')
    @include('livewire.admin.licenciaturas.submodulo.partials.matricula-movimiento')

    <livewire:admin.licenciaturas.submodulo.matricula-editar :permitir-movimiento-academico="true" />
</div>
