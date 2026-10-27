<div x-data="{ buscadorAbierto: false }" class="space-y-4">
    <section class="overflow-visible rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
        <div class="h-1.5 rounded-t-2xl bg-gradient-to-r from-[#006492] via-[#159bb6] to-[#88AC2E]"></div>
        <div class="p-4 sm:p-6">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="flex min-w-0 items-start gap-3">
                    <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#006492]/10 text-[#006492] dark:bg-sky-950/50 dark:text-sky-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M4.5 6.75V5.25A2.25 2.25 0 016.75 3h6.879c.597 0 1.17.237 1.591.659L19.5 7.94v10.81A2.25 2.25 0 0117.25 21H6.75a2.25 2.25 0 01-2.25-2.25V6.75zm3.75 5.25h7.5m-7.5 3.75h7.5"/></svg>
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-[#88AC2E]/15 px-2.5 py-0.5 text-[10px] font-bold tracking-wider text-[#638324] dark:text-lime-300">CONTROL ESCOLAR · PRO</span>
                            <span class="text-xs text-slate-400">Documentación institucional</span>
                        </div>
                        <h2 class="mt-1 text-xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-2xl">Expediente de identidad</h2>
                        <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-neutral-400">Consulta y organiza la documentación oficial del estudiante, con control de versiones y exportación segura.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @can('documentos-identidad.descargar')
                        <flux:modal.trigger name="descargar-expedientes-identidad">
                            <button
                                type="button"
                                wire:click="prepararDescarga"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#88AC2E] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#769827] focus:outline-none focus:ring-2 focus:ring-[#88AC2E]/30"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-6L12 15m0 0l4.5-4.5M12 15V3"/></svg>
                                Descargar expedientes
                            </button>
                        </flux:modal.trigger>
                    @endcan

                    @can('documentos-identidad.auditar')
                        <a
                            href="{{ route('admin.pdf.documentacion.alumnos.documentacion', [
                                'licenciatura' => $selectedLicenciatura ?: 0,
                                'generacion' => $selectedGeneracion ?: 0,
                                'estado' => $estado,
                            ]) }}"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600/30"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V5.625A3.375 3.375 0 0011.25 2.25h-4.5A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-5.25z"/></svg>
                            Control PDF
                        </a>
                        <a
                            href="{{ route('admin.word.documentacion.alumnos.documentacion', [
                                'licenciatura' => $selectedLicenciatura ?: 0,
                                'generacion' => $selectedGeneracion ?: 0,
                                'estado' => $estado,
                            ]) }}"
                            target="_blank"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00547b] focus:outline-none focus:ring-2 focus:ring-[#006492]/30"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 3.75h6l3 3v13.5H7.5zM13.5 3.75v3h3M9.75 11.25h4.5m-4.5 3h4.5"/></svg>
                            Control Word
                        </a>
                    @endcan
                </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-2 border-b border-slate-100 pb-4 text-xs dark:border-neutral-800">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 font-medium text-slate-700 dark:bg-neutral-800 dark:text-neutral-200"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#006492] text-[10px] text-white">1</span> Filtrar</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 font-medium text-slate-700 dark:bg-neutral-800 dark:text-neutral-200"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#006492] text-[10px] text-white">2</span> Seleccionar alumno</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 font-medium text-slate-700 dark:bg-neutral-800 dark:text-neutral-200"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#006492] text-[10px] text-white">3</span> Validar documentos</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 font-medium text-slate-700 dark:bg-neutral-800 dark:text-neutral-200"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full bg-[#88AC2E] text-[10px] text-white">4</span> Exportar</span>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-3">
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
                    <option value="todos">Todos los estados</option>
                </flux:select>
            </div>

            <div class="relative mt-4 rounded-xl border border-[#006492]/20 bg-[#006492]/[0.035] p-3 dark:border-sky-900/50 dark:bg-sky-950/10 sm:p-4">
                <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                    <label for="buscar-alumno-identidad" class="text-sm font-bold text-slate-800 dark:text-white">Localizar expediente estudiantil</label>
                    @if ($selectedAlumno)
                        <button type="button" wire:click="limpiarAlumno" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-[#006492] hover:bg-sky-50 dark:border-neutral-700 dark:bg-neutral-900 dark:text-sky-300">Cambiar alumno</button>
                    @else
                        <span class="text-xs text-slate-500 dark:text-neutral-400">Hasta 12 coincidencias · usa ↑ ↓ y Enter</span>
                    @endif
                </div>
                <div class="max-w-5xl">
                    <flux:input
                        id="buscar-alumno-identidad"
                        wire:model.live.debounce.400ms="query"
                        type="search"
                        icon="magnifying-glass"
                        placeholder="Nombre, apellidos, matrícula, folio o CURP…"
                        autocomplete="off"
                        @focus="buscadorAbierto = true"
                        @input="buscadorAbierto = true"
                        @blur="setTimeout(() => buscadorAbierto = false, 180)"
                        wire:keydown.arrow-down="selectIndexDown"
                        wire:keydown.arrow-up="selectIndexUp"
                        wire:keydown.enter.prevent="selectAlumno({{ $selectedIndex }})"
                    />
                </div>
                @if ($query !== '' && mb_strlen(trim($query)) < 2)
                    <p class="mt-2 text-xs text-slate-500">Escribe al menos dos caracteres para iniciar la búsqueda.</p>
                @endif
                @if ($alumnos !== [])
                    <ul x-cloak x-show="buscadorAbierto" x-transition role="listbox" aria-label="Resultados de alumnos"
                        class="absolute left-3 right-3 top-full z-40 mt-2 max-h-80 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-2xl dark:border-neutral-700 dark:bg-neutral-900 sm:left-4 sm:right-4">
                        @foreach ($alumnos as $index => $alumno)
                            <li role="option" wire:key="identidad-opcion-{{ $alumno['id'] }}" aria-selected="{{ $selectedIndex === $index ? 'true' : 'false' }}"
                                class="rounded-lg transition {{ $selectedIndex === $index ? 'bg-sky-50 ring-1 ring-sky-200 dark:bg-sky-950/40' : 'hover:bg-slate-50 dark:hover:bg-neutral-800' }}">
                                <button type="button" wire:click="seleccionarAlumnoPorId({{ (int) $alumno['id'] }})" class="flex w-full flex-col gap-1 p-3 text-left sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-neutral-400">{{ $alumno['licenciatura']['nombre'] ?? 'Sin licenciatura' }} · Gen. {{ $alumno['generacion']['generacion'] ?? '—' }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs text-slate-600 dark:text-neutral-300 sm:text-right"><span class="block font-mono">{{ $alumno['matricula'] ?: 'Sin matrícula' }}</span><span class="block font-mono">{{ $alumno['CURP'] ?: 'Sin CURP' }}</span></span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @elseif (mb_strlen(trim($query)) >= 2 && ! $selectedAlumno)
                    <p class="mt-3 text-sm font-medium text-amber-700 dark:text-amber-300">No hay coincidencias con los filtros actuales. Prueba otra búsqueda o cambia los filtros.</p>
                @endif
                <div wire:loading.delay wire:target="query,seleccionarAlumnoPorId,selectedLicenciatura,selectedGeneracion,estado" class="mt-3 text-xs font-semibold text-[#006492] dark:text-sky-300">Buscando y comprobando expediente…</div>
            </div>
        </div>
    </section>

    @if ($selectedAlumno)
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-950 dark:text-white">
                            {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
                        </h2>
                        @if (($selectedAlumno['egresado'] ?? 'false') === 'true')
                            <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-950/40 dark:text-violet-300">Egresado</span>
                        @elseif (($selectedAlumno['status'] ?? 'true') === 'false')
                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">Baja</span>
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

                @canany(['documentos-identidad.descargar', 'documentos-identidad.subir', 'documentos-identidad.reemplazar'])
                    <div class="flex flex-col gap-2 sm:flex-row lg:flex-col xl:flex-row">
                        @can('documentos-identidad.descargar')
                        <a
                            href="{{ $tieneDocumentosExportables ? route('admin.alumnos.documentos.unificar', $selectedAlumno['id']) : '#' }}"
                            @class([
                                'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-neutral-200' => $tieneDocumentosExportables,
                                'pointer-events-none cursor-not-allowed bg-slate-100 text-slate-400 dark:bg-neutral-800 dark:text-neutral-500' => ! $tieneDocumentosExportables,
                            ])
                            aria-disabled="{{ $tieneDocumentosExportables ? 'false' : 'true' }}"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V5.625A3.375 3.375 0 0011.25 2.25h-4.5A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-5.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 2.25V6a2.25 2.25 0 002.25 2.25H19.5"/></svg>
                            Descargar PDF
                        </a>

                        <a
                            href="{{ $tieneDocumentosExportables ? route('admin.expedientes-identidad.alumno.zip', $selectedAlumno['id']) : '#' }}"
                            @class([
                                'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition',
                                'bg-[#006492] text-white hover:bg-[#00547b]' => $tieneDocumentosExportables,
                                'pointer-events-none cursor-not-allowed bg-slate-100 text-slate-400 dark:bg-neutral-800 dark:text-neutral-500' => ! $tieneDocumentosExportables,
                            ])
                            aria-disabled="{{ $tieneDocumentosExportables ? 'false' : 'true' }}"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.245 2.118H6.62a2.25 2.25 0 01-2.245-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25M12 13.875V6.75m-6.75.75h13.5c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H5.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                            Descargar ZIP
                        </a>
                        @endcan

                        @if ($fuentesDocumentales > 0)
                            @canany(['documentos-identidad.subir', 'documentos-identidad.reemplazar'])
                                <button
                                    type="button"
                                    wire:click="abrirOrganizadorGeneral"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#88AC2E]/40 bg-[#88AC2E]/10 px-4 py-2.5 text-sm font-semibold text-[#65861f] transition hover:bg-[#88AC2E]/20 dark:border-lime-800 dark:text-lime-300"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75h15M4.5 12h15m-15 5.25h15"/></svg>
                                    Organizador general
                                </button>
                            @endcanany
                        @endif
                    </div>
                @endcanany
            </div>

            <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-200">
                El PDF y el ZIP individual combinan únicamente, en este orden: <strong>CURP, acta de nacimiento y certificado de estudios</strong>. Cada documento puede tener una o varias páginas, incluso provenientes de diferentes archivos. El archivo se nombra como <span class="font-mono">APELLIDO_PATERNO_APELLIDO_MATERNO_NOMBRE_GEN_XXXX_XXXX.pdf</span>.
            </div>

            @if ($organizacionPendiente)
                <div class="mt-3 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/20 dark:text-amber-200 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-semibold">Hay una organización de páginas pendiente de confirmar.</p>
                        <p class="mt-0.5 text-xs">{{ $paginasSinClasificar }} página(s) están sin clasificar. Las descargas siguen usando la última versión confirmada.</p>
                    </div>
                    @canany(['documentos-identidad.subir', 'documentos-identidad.reemplazar'])
                        <button type="button" wire:click="abrirOrganizadorGeneral" class="shrink-0 rounded-xl bg-amber-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-amber-700">Revisar páginas</button>
                    @endcanany
                </div>
            @endif

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 dark:border-neutral-700 dark:bg-neutral-800/60">
                    <p class="text-xs font-semibold text-slate-500 dark:text-neutral-400">Documentos registrados</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $documentosEntregados }} <span class="text-base font-normal text-slate-400">/ {{ $documentosTotales }}</span></p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $porcentaje }}% de cobertura total</p>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Documentos obligatorios</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-800 dark:text-emerald-200">{{ $obligatoriosEntregados }} <span class="text-base font-normal text-emerald-600/70">/ {{ $obligatoriosTotales }}</span></p>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-emerald-100 dark:bg-emerald-950"><div class="h-full rounded-full bg-[#88AC2E]" style="width: {{ $porcentajeObligatorios }}%"></div></div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/60 dark:bg-amber-950/20">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-300">Pendientes</p>
                    <p class="mt-1 text-2xl font-bold text-amber-800 dark:text-amber-200">{{ $documentosTotales - $documentosEntregados }}</p>
                    <p class="mt-1 text-xs text-amber-800/80 dark:text-amber-300">{{ $obligatoriosTotales - $obligatoriosEntregados }} obligatorio(s) por entregar</p>
                </div>
                <div class="rounded-xl border border-sky-200 bg-sky-50/80 p-4 dark:border-sky-900/60 dark:bg-sky-950/20">
                    <p class="text-xs font-semibold text-[#006492] dark:text-sky-300">Archivos fuente</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ $fuentesDocumentales }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $paginasSinClasificar }} páginas por clasificar</p>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-slate-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900">
                <p class="mr-auto text-sm font-semibold text-slate-800 dark:text-neutral-200">Lista de comprobación</p>
                @if ($obligatoriosEntregados === $obligatoriosTotales)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Obligatorios completos</span>
                @else
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">Faltan {{ $obligatoriosTotales - $obligatoriosEntregados }} obligatorios</span>
                @endif
            </div>
            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($documentosResumen as $tipo => $documento)
                    <div wire:key="resumen-{{ $selectedAlumno['id'] }}-{{ $tipo }}" class="flex min-w-0 items-center gap-2.5 rounded-xl border px-3 py-2.5 {{ $documento['entregado'] ? 'border-emerald-200 bg-emerald-50/30 dark:border-emerald-900/50 dark:bg-emerald-950/10' : 'border-amber-200 bg-amber-50/30 dark:border-amber-900/50 dark:bg-amber-950/10' }}">
                        <span aria-hidden="true" class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-xs font-bold {{ $documento['entregado'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' }}">{{ $documento['entregado'] ? '✓' : '!' }}</span>
                        <span class="min-w-0"><span class="block truncate text-xs font-semibold text-slate-800 dark:text-neutral-100">{{ $documento['label'] }}</span><span class="block text-[11px] text-slate-500 dark:text-neutral-400">{{ $documento['obligatorio'] ? 'Obligatorio' : 'Opcional' }} · {{ $documento['entregado'] ? 'Entregado' : 'Pendiente' }}</span></span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-900 sm:p-5">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Gestor de documentos</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">Cada tarjeta permite cargar, consultar, reorganizar y revisar versiones anteriores del documento.</p>
                </div>
                <div class="inline-flex w-fit rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-neutral-700 dark:bg-neutral-800">
                    <button type="button" wire:click="cambiarVistaDocumentos(false)" @class(['rounded-lg px-3 py-2 text-xs font-bold transition', 'bg-white text-[#006492] shadow-sm dark:bg-neutral-700 dark:text-sky-300' => ! $mostrarSoloPendientes, 'text-slate-500 hover:text-slate-700 dark:text-neutral-300' => $mostrarSoloPendientes])>Todos ({{ $documentosTotales }})</button>
                    <button type="button" wire:click="cambiarVistaDocumentos(true)" @class(['rounded-lg px-3 py-2 text-xs font-bold transition', 'bg-white text-[#006492] shadow-sm dark:bg-neutral-700 dark:text-sky-300' => $mostrarSoloPendientes, 'text-slate-500 hover:text-slate-700 dark:text-neutral-300' => ! $mostrarSoloPendientes])>Pendientes ({{ $documentosTotales - $documentosEntregados }})</button>
                </div>
            </div>
            @if ($mostrarSoloPendientes && $documentosEntregados === $documentosTotales)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-center text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/20 dark:text-emerald-300">Todos los documentos están registrados. Puedes cambiar a “Todos” para consultar los archivos e historiales.</div>
            @else
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                    @foreach (config('documentos_identidad.types') as $tipo => $config)
                        @if (! $mostrarSoloPendientes || ! ($documentosResumen[$tipo]['entregado'] ?? false))
                            <livewire:admin.documentacion.carga-documentos
                                :inscripcion-id="$selectedAlumno['id']"
                                :tipo="$tipo"
                                :key="$selectedAlumno['id'].'_'.$tipo"
                            />
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

        <livewire:admin.documentacion.organizador-paginas-identidad
            :inscripcion-id="$selectedAlumno['id']"
            :key="'organizador_identidad_'.$selectedAlumno['id']"
        />
    @else
        <section class="rounded-2xl border border-dashed border-sky-200 bg-white p-5 shadow-sm dark:border-sky-900/50 dark:bg-neutral-900 sm:p-8">
            <div class="flex flex-col items-center gap-4 text-center md:flex-row md:items-center md:text-left">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#006492]/10 text-[#006492] dark:text-sky-300">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.5 6H6.75A2.25 2.25 0 004.5 8.25v10.5A2.25 2.25 0 006.75 21h10.5a2.25 2.25 0 002.25-2.25V12M10.5 6l4.5-4.5 4.5 4.5M15 1.5v12"/></svg>
                </span>
                <div class="flex-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Comienza seleccionando un estudiante</h3>
                    <p class="mt-1 max-w-2xl text-sm text-slate-600 dark:text-neutral-400">Filtra por licenciatura, generación y estado; después busca por nombre, matrícula o CURP. El expediente mostrará documentos entregados, faltantes y su historial.</p>
                </div>
                <div class="shrink-0 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs dark:border-neutral-800 dark:bg-neutral-800">
                    <p class="font-bold text-slate-700 dark:text-neutral-200">{{ count(config('documentos_identidad.types', [])) }} tipos documentales</p>
                    <p class="mt-1 text-slate-500 dark:text-neutral-400">{{ collect(config('documentos_identidad.types', []))->where('required', true)->count() }} obligatorios · {{ collect(config('documentos_identidad.types', []))->where('required', false)->count() }} opcionales</p>
                </div>
            </div>
        </section>
    @endif

    @can('documentos-identidad.descargar')
        <flux:modal name="descargar-expedientes-identidad" class="w-full max-w-6xl">
            <div class="space-y-6">
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#88AC2E]/15 text-[#65861f] dark:text-lime-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-6L12 15m0 0l4.5-4.5M12 15V3"/></svg>
                    </span>
                    <div>
                        <flux:heading size="lg">Descargar expedientes de identidad</flux:heading>
                        <flux:text class="mt-1">Los alumnos se ordenan por apellido paterno. Cada expediente combina CURP, acta de nacimiento y certificado de estudios.</flux:text>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        'generacion' => ['Generación', 'Una o varias generaciones'],
                        'licenciatura' => ['Licenciatura', 'Una o varias licenciaturas'],
                        'alumno' => ['Alumno', 'PDF o ZIP individual'],
                    ] as $valor => [$titulo, $descripcion])
                        <button
                            type="button"
                            wire:click="$set('tipoExportacion', '{{ $valor }}')"
                            @class([
                                'rounded-2xl border p-4 text-left transition',
                                'border-[#006492] bg-[#006492]/8 ring-2 ring-[#006492]/15' => $tipoExportacion === $valor,
                                'border-slate-200 hover:border-slate-300 hover:bg-slate-50 dark:border-neutral-700 dark:hover:bg-neutral-800' => $tipoExportacion !== $valor,
                            ])
                        >
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $titulo }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $descripcion }}</p>
                        </button>
                    @endforeach
                </div>

                @error('tipoExportacion')
                    <p class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:bg-rose-950/30 dark:text-rose-300">{{ $message }}</p>
                @enderror

                @if ($tipoExportacion === 'alumno')
                    @if ($selectedAlumno)
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-neutral-700 dark:bg-neutral-800/50">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">Alumno seleccionado</p>
                            <p class="mt-1 text-lg font-bold text-slate-950 dark:text-white">
                                {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
                            </p>
                            <p class="mt-1 text-sm text-slate-600 dark:text-neutral-300">
                                {{ $selectedAlumno['licenciatura']['nombre'] ?? 'Sin licenciatura' }} · Generación {{ $selectedAlumno['generacion']['generacion'] ?? '—' }}
                            </p>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                                <a
                                    href="{{ $tieneDocumentosExportables ? route('admin.alumnos.documentos.unificar', $selectedAlumno['id']) : '#' }}"
                                    @class([
                                        'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold',
                                        'bg-slate-900 text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900' => $tieneDocumentosExportables,
                                        'pointer-events-none bg-slate-200 text-slate-400 dark:bg-neutral-700' => ! $tieneDocumentosExportables,
                                    ])
                                >
                                    Descargar PDF combinado
                                </a>
                                <a
                                    href="{{ $tieneDocumentosExportables ? route('admin.expedientes-identidad.alumno.zip', $selectedAlumno['id']) : '#' }}"
                                    @class([
                                        'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold',
                                        'bg-[#006492] text-white hover:bg-[#00547b]' => $tieneDocumentosExportables,
                                        'pointer-events-none bg-slate-200 text-slate-400 dark:bg-neutral-700' => ! $tieneDocumentosExportables,
                                    ])
                                >
                                    Descargar ZIP individual
                                </a>
                            </div>

                            @unless ($tieneDocumentosExportables)
                                <p class="mt-3 text-sm text-amber-700 dark:text-amber-300">El alumno no tiene ninguno de los tres documentos exportables.</p>
                            @endunless
                        </div>
                    @else
                        <div class="rounded-2xl border border-dashed border-amber-300 bg-amber-50 p-6 text-center dark:border-amber-800 dark:bg-amber-950/20">
                            <p class="font-semibold text-amber-900 dark:text-amber-200">Primero selecciona un alumno</p>
                            <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">Cierra este modal, busca al alumno y vuelve a abrir la descarga.</p>
                        </div>
                    @endif
                @else
                    <div class="grid gap-5 lg:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">Generaciones</p>
                                    <p class="text-xs text-slate-500 dark:text-neutral-400">
                                        {{ $tipoExportacion === 'generacion' ? 'Obligatorio: selecciona una o varias.' : 'Opcional: limita las generaciones.' }}
                                    </p>
                                </div>
                                <div class="flex gap-2 text-xs">
                                    <button type="button" wire:click="seleccionarTodasGeneraciones" class="font-semibold text-[#006492] hover:underline">Todas</button>
                                    <button type="button" wire:click="limpiarGeneracionesExportacion" class="font-semibold text-slate-500 hover:underline">Limpiar</button>
                                </div>
                            </div>
                            <div class="mt-3 max-h-52 space-y-1 overflow-y-auto pr-1">
                                @foreach ($todasGeneraciones as $generacion)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-50 dark:hover:bg-neutral-800">
                                        <input type="checkbox" wire:model="exportGeneraciones" value="{{ $generacion['id'] }}" class="rounded border-slate-300 text-[#006492] focus:ring-[#006492]">
                                        <span class="text-sm text-slate-700 dark:text-neutral-200">{{ $generacion['generacion'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('exportGeneraciones') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            @error('exportGeneraciones.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900 dark:text-white">Licenciaturas</p>
                                    <p class="text-xs text-slate-500 dark:text-neutral-400">
                                        {{ $tipoExportacion === 'licenciatura' ? 'Obligatorio: selecciona una o varias.' : 'Opcional: limita las licenciaturas.' }}
                                    </p>
                                </div>
                                <div class="flex gap-2 text-xs">
                                    <button type="button" wire:click="seleccionarTodasLicenciaturas" class="font-semibold text-[#006492] hover:underline">Todas</button>
                                    <button type="button" wire:click="limpiarLicenciaturasExportacion" class="font-semibold text-slate-500 hover:underline">Limpiar</button>
                                </div>
                            </div>
                            <div class="mt-3 max-h-52 space-y-1 overflow-y-auto pr-1">
                                @foreach ($licenciaturas as $licenciatura)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl px-3 py-2 hover:bg-slate-50 dark:hover:bg-neutral-800">
                                        <input type="checkbox" wire:model="exportLicenciaturas" value="{{ $licenciatura['id'] }}" class="mt-0.5 rounded border-slate-300 text-[#006492] focus:ring-[#006492]">
                                        <span class="text-sm text-slate-700 dark:text-neutral-200">{{ $licenciatura['nombre'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('exportLicenciaturas') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                            @error('exportLicenciaturas.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-neutral-700">
                        <p class="font-semibold text-slate-900 dark:text-white">Estados de alumnos incluidos</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-3">
                            @foreach (['activos' => 'Activos', 'egresados' => 'Egresados', 'bajas' => 'Bajas'] as $valor => $etiqueta)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 dark:border-neutral-700">
                                    <input type="checkbox" wire:model="exportEstados" value="{{ $valor }}" class="rounded border-slate-300 text-[#88AC2E] focus:ring-[#88AC2E]">
                                    <span class="text-sm font-medium text-slate-700 dark:text-neutral-200">{{ $etiqueta }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('exportEstados') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        @error('exportEstados.*') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-2xl border border-[#006492]/20 bg-[#006492]/5 p-4 text-sm text-slate-700 dark:border-sky-900/50 dark:bg-sky-950/20 dark:text-neutral-200">
                        <p class="font-semibold text-slate-900 dark:text-white">Estructura del ZIP</p>
                        <p class="mt-1">Generación → Licenciatura → Alumno, o Licenciatura → Generación → Alumno, según el tipo elegido. Los nombres se guardan en mayúsculas, sin acentos y con guion bajo.</p>
                        <p class="mt-2">Dentro de cada licenciatura también encontrarás los <strong>PDF individuales numerados</strong> (orden por apellidos) y el archivo <span class="font-mono font-semibold">00_TODOS_LOS_EXPEDIENTES_PARA_IMPRIMIR.pdf</span>, con todos los alumnos juntos y listos para impresión. Si la descarga es por licenciatura, el PDF general incluye las generaciones seleccionadas; si es por generación, se genera uno por licenciatura de esa generación.</p>
                        <p class="mt-2">El alumno sin documentos tendrá una hoja informativa en la versión imprimible para mantener el orden de lista. Cada archivo general incluye únicamente a los alumnos del lote descargado (máximo 50). Se conservan las carpetas originales, <span class="font-mono font-semibold">REPORTE_DE_EXPEDIENTES.xlsx</span> y los avisos <span class="font-mono font-semibold">DOCUMENTOS_FALTANTES.txt</span>.</p>
                    </div>

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-100">
                        <strong>Descarga directa por lotes (máximo 50 alumnos por ZIP).</strong>
                        Si hay más de 50 alumnos, el sistema divide automáticamente la selección en archivos de 50.
                        Cada clic genera y descarga un solo lote; repite «Generar ZIP» para el siguiente.
                        @if ($totalLotesExportacion > 1)
                            <p class="mt-2 font-semibold">Próximo lote: {{ $loteExportacion }} de {{ $totalLotesExportacion }}.</p>
                        @endif
                    </div>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <flux:modal.close>
                            <button type="button" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-800">Cerrar</button>
                        </flux:modal.close>
                        <button
                            type="button"
                            wire:click="solicitarExportacion"
                            wire:loading.attr="disabled"
                            wire:target="solicitarExportacion"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#006492] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#00547b] disabled:cursor-wait disabled:opacity-60"
                        >
                            <svg wire:loading wire:target="solicitarExportacion" class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            <span wire:loading.remove wire:target="solicitarExportacion">Generar ZIP</span>
                            <span wire:loading wire:target="solicitarExportacion">Procesando…</span>
                        </button>
                    </div>
                @endif

                <div
                    class="border-t border-slate-200 pt-5 dark:border-neutral-700"
                    @if ($hayExportacionesPendientes) wire:poll.5s="actualizarExportaciones" @endif
                >
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">Exportaciones recientes</p>
                            <p class="text-xs text-slate-500 dark:text-neutral-400">Los ZIP se generan directamente y se descargan automáticamente al terminar. Puedes descargarlos otra vez desde aquí.</p>
                        </div>
                        <button type="button" wire:click="actualizarExportaciones" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-[#006492] hover:bg-[#006492]/10">Actualizar</button>
                    </div>

                    @if ($exportacionesRecientes === [])
                        <div class="mt-3 rounded-xl border border-dashed border-slate-300 px-4 py-5 text-center text-sm text-slate-500 dark:border-neutral-700 dark:text-neutral-400">Todavía no hay exportaciones.</div>
                    @else
                        <div class="mt-3 space-y-3">
                            @foreach ($exportacionesRecientes as $exportacion)
                                @php
                                    $avance = $exportacion['total_alumnos'] > 0
                                        ? min(100, (int) round(($exportacion['alumnos_procesados'] / $exportacion['total_alumnos']) * 100))
                                        : 0;
                                @endphp
                                <div class="rounded-xl border border-slate-200 p-4 dark:border-neutral-700">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                    {{ $exportacion['archivo_nombre'] ?: 'Exportación por '.$exportacion['tipo'] }}
                                                </p>
                                                <span @class([
                                                    'rounded-full px-2.5 py-1 text-[11px] font-bold uppercase',
                                                    'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' => in_array($exportacion['estado'], ['pendiente', 'procesando'], true),
                                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' => $exportacion['estado'] === 'listo',
                                                    'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' => $exportacion['estado'] === 'error',
                                                ])>{{ $exportacion['estado'] }}</span>
                                            </div>
                                            <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">
                                                {{ $exportacion['created_at'] }} · {{ $exportacion['alumnos_procesados'] }}/{{ $exportacion['total_alumnos'] }} alumnos
                                                @if ($exportacion['alumnos_incompletos'] > 0)
                                                    · {{ $exportacion['alumnos_incompletos'] }} expedientes incompletos
                                                @endif
                                            </p>
                                        </div>

                                        @if ($exportacion['url'])
                                            <a href="{{ $exportacion['url'] }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#88AC2E] px-4 py-2 text-sm font-semibold text-white hover:bg-[#769827]">
                                                Descargar ZIP
                                            </a>
                                        @endif
                                    </div>

                                    @if (in_array($exportacion['estado'], ['pendiente', 'procesando'], true))
                                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-200 dark:bg-neutral-700">
                                            <div class="h-full rounded-full bg-gradient-to-r from-[#006492] to-[#88AC2E] transition-all" style="width: {{ $avance }}%"></div>
                                        </div>
                                    @endif

                                    @if ($exportacion['estado'] === 'error')
                                        <p class="mt-2 text-xs text-rose-600 dark:text-rose-300">{{ $exportacion['error'] ?: 'Ocurrió un error al generar el archivo.' }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </flux:modal>
    @endcan
    @script
    <script>
        $wire.on('expedientes-zip-descarga-directa', ({ url }) => {
            if (typeof url !== 'string' || !url.startsWith('/')) return;
            // Navegación a la ruta autenticada de descarga, sin ventana emergente.
            const enlace = document.createElement('a');
            enlace.href = url;
            enlace.download = '';
            enlace.style.display = 'none';
            document.body.appendChild(enlace);
            enlace.click();
            enlace.remove();
        });
    </script>
    @endscript
</div>
