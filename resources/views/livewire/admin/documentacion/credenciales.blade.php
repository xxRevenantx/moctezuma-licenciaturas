<div class="space-y-5" x-data="{ exportando: false }">
    {{-- Encabezado --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] via-cyan-500 to-[#88AC2E]"></div>

        <div class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="4" y="3" width="16" height="18" rx="2" />
                            <circle cx="12" cy="9" r="2.3" />
                            <path stroke-linecap="round" d="M8 16c1.2-2 2.6-3 4-3s2.8 1 4 3M8 19h8" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs font-black uppercase tracking-[.18em] text-[#88AC2E]">Documentación institucional</p>
                        <h2 class="mt-1 text-xl font-black tracking-tight text-slate-900 dark:text-white sm:text-2xl">Credenciales de estudiantes</h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-zinc-300">
                            Genera credenciales por alumno, licenciatura, generación o cuatrimestre. Revisa la selección antes de exportar y descarga el concentrado en PDF o Word.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-[#006492] dark:bg-sky-950/40 dark:text-sky-300">PDF + Word</span>
                    <button type="button" wire:click="restablecer"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 101.7-5.3M3 4v5h5" />
                        </svg>
                        Restablecer
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Modo --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div class="mb-4">
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#006492] text-xs font-black text-white">1</span>
                <h3 class="font-black text-slate-900 dark:text-white">Selecciona cómo generar las credenciales</h3>
            </div>
            <p class="ml-9 mt-1 text-sm text-slate-500 dark:text-zinc-400">Cada opción filtra únicamente alumnos activos.</p>
        </div>

        @php
            $modos = [
                'alumno' => ['titulo' => 'Por alumno', 'texto' => 'Busca por nombre, matrícula o CURP.', 'color' => 'sky'],
                'licenciatura' => ['titulo' => 'Por licenciatura', 'texto' => 'Incluye todos los alumnos activos de una carrera.', 'color' => 'emerald'],
                'generacion' => ['titulo' => 'Por generación', 'texto' => 'Agrupa alumnos de todas las licenciaturas de una generación.', 'color' => 'violet'],
                'cuatrimestre' => ['titulo' => 'Por cuatrimestre', 'texto' => 'Genera las credenciales del cuatrimestre seleccionado.', 'color' => 'amber'],
            ];
        @endphp

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($modos as $valor => $config)
                <button type="button" wire:click="$set('modo', '{{ $valor }}')"
                    class="group relative rounded-2xl border p-4 text-left transition
                        {{ $modo === $valor
                            ? 'border-[#006492] bg-sky-50 ring-2 ring-[#006492]/15 dark:bg-sky-950/25'
                            : 'border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800' }}">
                    @if ($modo === $valor)
                        <span class="absolute right-3 top-3 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#006492] text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                        </span>
                    @endif

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition group-hover:text-[#006492] dark:bg-zinc-800 dark:text-zinc-300">
                        @if ($valor === 'alumno')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 10-16 0M12 13a5 5 0 100-10 5 5 0 000 10z" /></svg>
                        @elseif ($valor === 'licenciatura')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m2 10 10-5 10 5-10 5L2 10zM6 12.5V17c3 2 9 2 12 0v-4.5" /></svg>
                        @elseif ($valor === 'generacion')
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19h16M6 17V9m4 8V9m4 8V9m4 8V9M3 9l9-5 9 5H3z" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 012 2v15H3V6a2 2 0 012-2z" /></svg>
                        @endif
                    </div>
                    <h4 class="mt-3 font-extrabold text-slate-900 dark:text-white">{{ $config['titulo'] }}</h4>
                    <p class="mt-1 pr-5 text-xs leading-5 text-slate-500 dark:text-zinc-400">{{ $config['texto'] }}</p>
                </button>
            @endforeach
        </div>
    </section>

    {{-- Filtro principal --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#006492] text-xs font-black text-white">2</span>
                    <h3 class="font-black text-slate-900 dark:text-white">Define el grupo de alumnos</h3>
                </div>
                <p class="ml-9 mt-1 text-sm text-slate-500 dark:text-zinc-400">Los resultados se actualizan automáticamente.</p>
            </div>
            @if ($resultados->isNotEmpty())
                <span class="inline-flex w-fit items-center rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                    {{ $resultados->count() }} {{ $resultados->count() === 1 ? 'alumno encontrado' : 'alumnos encontrados' }}
                </span>
            @endif
        </div>

        @if ($modo === 'alumno')
            <div class="max-w-2xl">
                <flux:input
                    label="Buscar alumno"
                    wire:model.live.debounce.300ms="query"
                    type="search"
                    placeholder="Nombre, matrícula, matrícula interna o CURP..."
                    autocomplete="off"
                />
                <p class="mt-2 text-xs text-slate-500 dark:text-zinc-400">Escribe al menos 2 caracteres. Puedes agregar más de un alumno a la misma descarga.</p>
            </div>
        @elseif ($modo === 'licenciatura')
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(280px,.7fr)]">
                <flux:field>
                    <flux:label>Licenciatura</flux:label>
                    <flux:select wire:model.live="licenciaturaId" class="w-full">
                        <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
                        @foreach ($licenciaturas as $licenciatura)
                            <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Solo se muestran licenciaturas con alumnos activos.</flux:description>
                </flux:field>

                @if ($licenciaturaId)
                    <flux:input label="Refinar resultados" wire:model.live.debounce.300ms="query" type="search" placeholder="Nombre, matrícula o CURP..." />
                @endif
            </div>
        @elseif ($modo === 'generacion')
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(280px,.7fr)]">
                <flux:field>
                    <flux:label>Generación</flux:label>
                    <flux:select wire:model.live="generacionId" class="w-full">
                        <flux:select.option value="">Selecciona una generación</flux:select.option>
                        @foreach ($generaciones as $generacion)
                            <flux:select.option value="{{ $generacion->id }}">
                                {{ $generacion->generacion }}{{ $generacion->activa === 'true' ? ' · Activa' : '' }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Incluye todas las licenciaturas que tengan alumnos en esa generación.</flux:description>
                </flux:field>

                @if ($generacionId)
                    <flux:input label="Refinar resultados" wire:model.live.debounce.300ms="query" type="search" placeholder="Nombre, matrícula o CURP..." />
                @endif
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(280px,.7fr)]">
                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="cuatrimestreId" class="w-full">
                        <flux:select.option value="">Selecciona un cuatrimestre</flux:select.option>
                        @foreach ($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->id }}">
                                {{ $cuatrimestre->nombre_cuatrimestre ?: $cuatrimestre->cuatrimestre }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:description>Incluye alumnos activos de todas las licenciaturas en ese cuatrimestre.</flux:description>
                </flux:field>

                @if ($cuatrimestreId)
                    <flux:input label="Refinar resultados" wire:model.live.debounce.300ms="query" type="search" placeholder="Nombre, matrícula o CURP..." />
                @endif
            </div>
        @endif
    </section>

    {{-- Resultados --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#006492] text-xs font-black text-white">3</span>
                    <h3 class="font-black text-slate-900 dark:text-white">Selecciona las credenciales</h3>
                </div>
                <p class="ml-9 mt-1 text-xs text-slate-500 dark:text-zinc-400">Marca alumnos individuales o agrega todos los resultados del filtro.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="seleccionarTodosResultados" @disabled($resultados->isEmpty())
                    class="inline-flex items-center gap-2 rounded-xl bg-[#006492] px-3.5 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-[#00547b] disabled:cursor-not-allowed disabled:opacity-40">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6M5 6h14M5 18h14" /></svg>
                    Seleccionar todos ({{ $resultados->count() }})
                </button>
                <button type="button" wire:click="limpiarSeleccion" @disabled(empty($alumnosSeleccionadosIds))
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700">
                    Limpiar selección
                </button>
            </div>
        </div>

        @if ($resultados->isEmpty())
            <div class="px-5 py-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-4-4" /></svg>
                </div>
                <p class="mt-3 font-bold text-slate-700 dark:text-zinc-200">
                    {{ $modo === 'alumno' && mb_strlen(trim($query)) < 2 ? 'Busca un alumno para comenzar' : 'No hay alumnos para este filtro' }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Solo se consideran inscripciones activas.</p>
            </div>
        @else
            <div class="max-h-[430px] overflow-auto">
                <table class="min-w-[980px] w-full text-left text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-500 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="w-14 px-4 py-3 text-center">Sel.</th>
                            <th class="px-4 py-3">Alumno</th>
                            <th class="px-4 py-3">Matrícula</th>
                            <th class="px-4 py-3">Licenciatura</th>
                            <th class="px-4 py-3">Generación</th>
                            <th class="px-4 py-3">Cuatrimestre</th>
                            <th class="px-4 py-3">Modalidad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                        @foreach ($resultados as $alumno)
                            @php($seleccionado = in_array($alumno->id, $alumnosSeleccionadosIds, true))
                            <tr wire:key="credencial-resultado-{{ $alumno->id }}"
                                class="transition {{ $seleccionado ? 'bg-sky-50/80 dark:bg-sky-950/20' : 'hover:bg-slate-50 dark:hover:bg-zinc-800/60' }}">
                                <td class="px-4 py-3 text-center">
                                    <button type="button" wire:click="alternarAlumno({{ $alumno->id }})"
                                        class="inline-flex h-6 w-6 items-center justify-center rounded-lg border transition {{ $seleccionado ? 'border-[#006492] bg-[#006492] text-white' : 'border-slate-300 bg-white text-transparent hover:border-[#006492] dark:border-zinc-600 dark:bg-zinc-900' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.7"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                    </button>
                                </td>
                                <td class="px-4 py-3">
                                    <button type="button" wire:click="seleccionarAlumno({{ $alumno->id }})" class="text-left">
                                        <span class="block font-extrabold uppercase text-slate-900 dark:text-white">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</span>
                                        <span class="mt-0.5 block text-[11px] text-slate-500 dark:text-zinc-400">{{ $alumno->CURP ?: 'CURP no registrada' }}</span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700 dark:text-zinc-200">{{ $alumno->matricula ?: $alumno->matricula_interna ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-zinc-300">{{ $alumno->licenciatura?->nombre_corto ?: $alumno->licenciatura?->nombre ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-zinc-300">{{ $alumno->generacion?->generacion ?: '—' }}</td>
                                <td class="px-4 py-3 text-slate-600 dark:text-zinc-300">{{ $alumno->cuatrimestre?->nombre_cuatrimestre ?: $alumno->cuatrimestre?->cuatrimestre ?: '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $alumno->modalidad?->nombre ?: '—' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Selección + exportación --}}
    <form method="GET" target="_blank" class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6" @submit="exportando = true; setTimeout(() => exportando = false, 1500)">
        @foreach ($alumnosSeleccionadosIds as $alumnoId)
            <input type="hidden" name="alumnos_ids[]" value="{{ $alumnoId }}">
        @endforeach

        <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#006492] text-xs font-black text-white">4</span>
                    <h3 class="font-black text-slate-900 dark:text-white">Revisa y descarga</h3>
                </div>

                @if ($seleccionados->isEmpty())
                    <div class="ml-9 mt-2 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-500 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-400">
                        Aún no hay alumnos seleccionados. Elige uno o usa <strong>Seleccionar todos</strong>.
                    </div>
                @else
                    <div class="ml-9 mt-3 flex flex-wrap gap-2">
                        @foreach ($seleccionados as $alumno)
                            <span wire:key="credencial-seleccionado-{{ $alumno->id }}" class="inline-flex max-w-full items-center gap-2 rounded-full border border-sky-200 bg-sky-50 py-1.5 pl-3 pr-1.5 text-xs font-bold text-[#006492] dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-300">
                                <span class="truncate">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</span>
                                <button type="button" wire:click="eliminarAlumnoSeleccionado({{ $alumno->id }})" class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-sky-700 hover:bg-sky-100 hover:text-red-600 dark:text-sky-300 dark:hover:bg-sky-900">×</button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="shrink-0 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                <div class="mb-3 flex items-center justify-between gap-6">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-wide text-slate-500 dark:text-zinc-400">Credenciales seleccionadas</p>
                        <p class="mt-1 text-3xl font-black text-[#006492] dark:text-sky-300">{{ count($alumnosSeleccionadosIds) }}</p>
                    </div>
                    <div class="text-right text-xs text-slate-500 dark:text-zinc-400">
                        <p>4 credenciales</p>
                        <p>por página PDF</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <button type="submit" formaction="{{ route('admin.pdf.documentacion.credencial_alumno') }}" @disabled(empty($alumnosSeleccionadosIds))
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v15H6zM14 2v6h6M9 13h6M9 17h6" /></svg>
                        PDF
                    </button>
                    <button type="submit" formaction="{{ route('admin.word.documentacion.credencial_alumno') }}" @disabled(empty($alumnosSeleccionadosIds))
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#00547b] disabled:cursor-not-allowed disabled:opacity-40">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 2h9l5 5v15H6zM14 2v6h6M9 13h6M9 17h6" /></svg>
                        Word
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
