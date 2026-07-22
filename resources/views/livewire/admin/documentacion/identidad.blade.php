<div x-data="{ buscadorAbierto: false }" class="space-y-6">
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] to-[#88AC2E]"></div>
        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-[#006492]/10 text-[#006492] dark:bg-[#006492]/20 dark:text-sky-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 9h3.75A2.25 2.25 0 0121 11.25v7.5A2.25 2.25 0 0118.75 21H5.25A2.25 2.25 0 013 18.75v-7.5A2.25 2.25 0 015.25 9H9m6 0V5.25A2.25 2.25 0 0012.75 3h-1.5A2.25 2.25 0 009 5.25V9m6 0H9m3 3.75v4.5m-2.25-2.25h4.5"/></svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Expediente de identidad</h1>
                            <p class="mt-1 text-sm text-slate-600 dark:text-neutral-400">Carga segura, validación de PDF, conversión automática de imágenes e historial de versiones.</p>
                        </div>
                    </div>
                </div>

                @can('documentos-identidad.auditar')
                    <a
                        href="{{ route('admin.pdf.documentacion.alumnos.documentacion', [
                            'licenciatura' => $selectedLicenciatura ?: 0,
                            'generacion' => $selectedGeneracion ?: 0,
                            'estado' => $estado,
                        ]) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00547b] focus:outline-none focus:ring-2 focus:ring-[#006492]/30"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V5.625A3.375 3.375 0 0011.25 2.25h-4.5A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-5.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 2.25V6a2.25 2.25 0 002.25 2.25H19.5M9 15.75h6m-6 3h3"/></svg>
                        Control documental
                    </a>
                @endcan
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <flux:select label="Licenciatura" wire:model.live="selectedLicenciatura" class="w-full">
                    <option value="">Todas las licenciaturas</option>
                    @foreach ($licenciaturas as $licenciatura)
                        <option value="{{ $licenciatura['id'] }}">{{ $licenciatura['nombre'] }}</option>
                    @endforeach
                </flux:select>

                <flux:select label="Generación" wire:model.live="selectedGeneracion" class="w-full">
                    <option value="">Todas las generaciones</option>
                    @foreach ($generaciones as $generacion)
                        <option value="{{ $generacion['id'] }}">{{ $generacion['generacion'] }}</option>
                    @endforeach
                </flux:select>

                <flux:select label="Estado del alumno" wire:model.live="estado" class="w-full">
                    <option value="activos">Activos</option>
                    <option value="egresados">Egresados</option>
                    <option value="bajas">Bajas</option>
                    <option value="todos">Todos</option>
                </flux:select>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
        <div class="relative max-w-4xl">
            <flux:input
                id="buscar-alumno-identidad"
                label="Buscar alumno"
                wire:model.live.debounce.400ms="query"
                type="search"
                placeholder="Nombre, apellidos, matrícula, folio o CURP…"
                autocomplete="off"
                @focus="buscadorAbierto = true"
                @input="buscadorAbierto = true"
                @blur="setTimeout(() => buscadorAbierto = false, 180)"
                wire:keydown.arrow-down="selectIndexDown"
                wire:keydown.arrow-up="selectIndexUp"
                wire:keydown.enter.prevent="selectAlumno({{ $selectedIndex }})"
            />

            @if ($query !== '' && mb_strlen(trim($query)) < 2)
                <p class="mt-2 text-xs text-slate-500 dark:text-neutral-400">Escribe al menos dos caracteres.</p>
            @endif

            @if ($alumnos !== [])
                <ul
                    x-cloak
                    x-show="buscadorAbierto"
                    x-transition
                    role="listbox"
                    class="absolute z-30 mt-2 max-h-80 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl dark:border-neutral-700 dark:bg-neutral-900"
                >
                    @foreach ($alumnos as $index => $alumno)
                        <li
                            role="option"
                            wire:click="selectAlumno({{ $index }})"
                            @mouseenter="buscadorAbierto = true"
                            class="cursor-pointer rounded-xl p-3 transition {{ $selectedIndex === $index ? 'bg-[#006492]/10 ring-1 ring-[#006492]/20' : 'hover:bg-slate-50 dark:hover:bg-neutral-800' }}"
                        >
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">
                                        {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-neutral-400">
                                        {{ $alumno['licenciatura']['nombre'] ?? 'Sin licenciatura' }} · {{ $alumno['generacion']['generacion'] ?? 'Sin generación' }}
                                    </p>
                                </div>
                                <div class="text-xs text-slate-600 dark:text-neutral-300 sm:text-right">
                                    <div class="font-mono">{{ $alumno['matricula'] ?? 'Sin matrícula' }}</div>
                                    <div class="font-mono">{{ $alumno['CURP'] ?? 'Sin CURP' }}</div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @elseif (mb_strlen(trim($query)) >= 2 && ! $selectedAlumno)
                <div class="mt-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:border-neutral-700 dark:bg-neutral-800/50 dark:text-neutral-300">
                    No se encontraron alumnos con los filtros actuales.
                </div>
            @endif
        </div>

        <div wire:loading.delay wire:target="query,selectAlumno,selectedLicenciatura,selectedGeneracion,estado" class="mt-4">
            <div class="flex items-center gap-2 rounded-xl bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800 dark:bg-sky-950/30 dark:text-sky-200">
                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                Consultando expediente…
            </div>
        </div>
    </section>

    @if ($selectedAlumno)
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-950 dark:text-white">
                            {{ $selectedAlumno['nombre'] ?? '' }} {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }}
                        </h2>
                        @if (($selectedAlumno['status'] ?? 'true') === 'false')
                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">Baja</span>
                        @elseif (($selectedAlumno['egresado'] ?? 'false') === 'true')
                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">Egresado</span>
                        @else
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Activo</span>
                        @endif
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-x-8 gap-y-2 text-sm text-slate-600 dark:text-neutral-300 sm:grid-cols-2">
                        <p><span class="font-semibold text-slate-800 dark:text-neutral-100">Matrícula:</span> <span class="font-mono">{{ $selectedAlumno['matricula'] ?? '—' }}</span></p>
                        <p><span class="font-semibold text-slate-800 dark:text-neutral-100">Folio:</span> {{ $selectedAlumno['folio'] ?: '—' }}</p>
                        <p><span class="font-semibold text-slate-800 dark:text-neutral-100">CURP:</span> <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '—' }}</span></p>
                        <p><span class="font-semibold text-slate-800 dark:text-neutral-100">Generación:</span> {{ $selectedAlumno['generacion']['generacion'] ?? '—' }}</p>
                        <p class="sm:col-span-2"><span class="font-semibold text-slate-800 dark:text-neutral-100">Licenciatura:</span> {{ $selectedAlumno['licenciatura']['nombre'] ?? '—' }}</p>
                    </div>
                </div>

                @can('documentos-identidad.descargar')
                    <a
                        href="{{ $tieneDocumentos ? route('admin.alumnos.documentos.unificar', $selectedAlumno['id']) : '#' }}"
                        target="{{ $tieneDocumentos ? '_blank' : '_self' }}"
                        @class([
                            'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                            'bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-neutral-200' => $tieneDocumentos,
                            'pointer-events-none cursor-not-allowed bg-slate-100 text-slate-400 dark:bg-neutral-800 dark:text-neutral-500' => ! $tieneDocumentos,
                        ])
                        aria-disabled="{{ $tieneDocumentos ? 'false' : 'true' }}"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-6L12 15m0 0l4.5-4.5M12 15V3"/></svg>
                        Descargar expediente unificado
                    </a>
                @endcan
            </div>

            <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_320px]">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-700 dark:bg-neutral-800/50">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Avance documental</p>
                            <p class="text-xs text-slate-500 dark:text-neutral-400">{{ $documentosEntregados }} de {{ $documentosTotales }} documentos · {{ $obligatoriosEntregados }} de {{ $obligatoriosTotales }} obligatorios</p>
                        </div>
                        <span class="text-2xl font-bold text-[#006492] dark:text-sky-300">{{ $porcentaje }}%</span>
                    </div>
                    <div class="mt-3 h-3 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-700">
                        <div class="h-full rounded-full bg-gradient-to-r from-[#006492] to-[#88AC2E] transition-all duration-500" style="width: {{ $porcentaje }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Pendientes</p>
                    @if ($pendientes === [])
                        <p class="mt-2 text-sm text-emerald-700 dark:text-emerald-300">Expediente documental completo.</p>
                    @else
                        <p class="mt-2 text-sm text-slate-600 dark:text-neutral-300">{{ implode(', ', $pendientes) }}.</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @foreach (config('documentos_identidad.types') as $tipo => $config)
                <livewire:admin.documentacion.carga-documentos
                    :inscripcion-id="$selectedAlumno['id']"
                    :tipo="$tipo"
                    :key="$selectedAlumno['id'].'_'.$tipo"
                />
            @endforeach
        </section>
    @else
        <section class="rounded-2xl border border-dashed border-[#006492]/40 bg-[#006492]/5 p-8 text-center dark:border-sky-700/50 dark:bg-sky-950/20">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#006492] shadow-sm dark:bg-neutral-900 dark:text-sky-300">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 15.75l-2.489-2.489m0 0a5.25 5.25 0 10-7.424-7.424 5.25 5.25 0 007.424 7.424zM21 21l-5.197-5.197"/></svg>
            </div>
            <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">Selecciona un alumno</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-neutral-400">Los filtros se aplican también al buscador para evitar cargar documentación en una licenciatura o generación incorrecta.</p>
        </section>
    @endif
</div>
