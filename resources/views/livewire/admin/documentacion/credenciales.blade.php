<div class="space-y-5" x-data="credencialesFotosPro">
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
                    <button type="button" @click="gestorFotos = !gestorFotos"
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-bold text-emerald-700 shadow-sm transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/35 dark:text-emerald-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
                        Gestionar fotografías
                    </button>
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

    {{-- Gestión de fotografías institucionales --}}
    <section x-show="gestorFotos" x-cloak x-transition.opacity
        class="overflow-hidden rounded-3xl border border-emerald-200 bg-white shadow-sm dark:border-emerald-900/70 dark:bg-zinc-900">
        <div class="border-b border-emerald-100 bg-emerald-50/60 px-5 py-4 dark:border-emerald-900/60 dark:bg-emerald-950/20 sm:px-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-600 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/></svg>
                        </span>
                        <h3 class="font-black text-slate-900 dark:text-white">Fotografías para credenciales</h3>
                    </div>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-zinc-300">
                        Igual que en Moctezuma Básica: las fotos nuevas se convierten a JPG, se corrige orientación EXIF y se recortan al centro a <strong>2.5 × 3 cm (295 × 354 px, 300 ppp)</strong> sin deformarlas.
                    </p>
                </div>

                @if ($resultados->isNotEmpty())
                    <div class="flex gap-2">
                        <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-black text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">{{ $resultadosConFoto }} con foto</span>
                        <span class="rounded-full bg-amber-100 px-3 py-1.5 text-xs font-black text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">{{ $resultadosSinFoto }} sin foto</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div>
                <p class="text-sm font-black text-slate-900 dark:text-white">Carga masiva por matrícula o CURP</p>
                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-zinc-400">
                    Nombra cada archivo con la matrícula, matrícula interna o CURP del alumno; también se aceptan nombres como <strong>FOTO_MATRICULA.jpg</strong>. Se buscan coincidencias únicamente entre alumnos activos.
                </p>

                <label class="mt-4 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/40 px-5 py-7 text-center transition hover:border-emerald-400 hover:bg-emerald-50 dark:border-emerald-900 dark:bg-emerald-950/15">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L7 9m5-5 5 5M5 20h14"/></svg>
                    <span class="mt-2 text-sm font-extrabold text-slate-800 dark:text-zinc-100">Selecciona o arrastra fotografías</span>
                    <span class="mt-1 text-xs text-slate-500 dark:text-zinc-400">JPG, JPEG, PNG o WebP · máximo 5 MB por foto · hasta 100 por lote</span>
                    <input type="file" class="hidden" wire:model="fotosMasivas" multiple accept="image/jpeg,image/jpg,image/png,image/webp">
                </label>

                @error('fotosMasivas') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                @error('fotosMasivas.*') <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p> @enderror

                @if (count($fotosMasivas))
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-sky-50 px-3 py-1.5 text-xs font-bold text-[#006492] dark:bg-sky-950/30 dark:text-sky-300">{{ count($fotosMasivas) }} archivo(s) listo(s)</span>
                        <button type="button" wire:click="guardarFotosMasivas" wire:loading.attr="disabled" wire:target="guardarFotosMasivas,fotosMasivas"
                            class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="guardarFotosMasivas">Procesar fotografías</span>
                            <span wire:loading wire:target="guardarFotosMasivas">Procesando...</span>
                        </button>
                        <button type="button" wire:click="limpiarCargaMasiva" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">Limpiar</button>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                <p class="text-xs font-black uppercase tracking-wide text-slate-500 dark:text-zinc-400">Resultado del último lote</p>
                <div class="mt-3 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-white p-3 dark:bg-zinc-900">
                        <p class="text-2xl font-black text-emerald-600">{{ $cargaMasivaGuardadas }}</p>
                        <p class="text-[11px] font-bold text-slate-500">Guardadas</p>
                    </div>
                    <div class="rounded-xl bg-white p-3 dark:bg-zinc-900">
                        <p class="text-2xl font-black text-amber-600">{{ $cargaMasivaNoAsignadas }}</p>
                        <p class="text-[11px] font-bold text-slate-500">Por revisar</p>
                    </div>
                </div>

                @if (count($erroresCargaMasiva))
                    <div class="mt-3 max-h-40 overflow-auto rounded-xl border border-amber-200 bg-amber-50 p-3 text-[11px] leading-5 text-amber-900 dark:border-amber-900 dark:bg-amber-950/20 dark:text-amber-200">
                        @foreach ($erroresCargaMasiva as $errorCarga)
                            <p>• {{ $errorCarga }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-zinc-400">Si un archivo no coincide o es ambiguo, aquí aparecerá el detalle sin afectar las fotografías válidas.</p>
                @endif
            </div>
        </div>
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
                <table class="min-w-[1240px] w-full text-left text-sm">
                    <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] font-black uppercase tracking-wide text-slate-500 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="w-14 px-4 py-3 text-center">Sel.</th>
                            <th class="w-24 px-4 py-3">Foto</th>
                            <th class="px-4 py-3">Alumno</th>
                            <th class="px-4 py-3">Matrícula</th>
                            <th class="px-4 py-3">Licenciatura</th>
                            <th class="px-4 py-3">Generación</th>
                            <th class="px-4 py-3">Cuatrimestre</th>
                            <th class="px-4 py-3">Modalidad</th>
                            <th class="w-56 px-4 py-3">Fotografía</th>
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
                                    <div class="relative h-[60px] w-[50px] overflow-hidden rounded-xl border border-slate-200 bg-slate-100 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                                        @if ($alumno->foto_existe && $alumno->foto_url)
                                            <img src="{{ $alumno->foto_url }}" alt="Foto de {{ $alumno->nombre }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center px-1 text-center text-[8px] font-black leading-3 text-slate-400">FOTO<br>+ SELLO</div>
                                        @endif
                                    </div>
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
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex shrink-0 rounded-full px-2.5 py-1 text-[10px] font-black {{ $alumno->foto_existe ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                            {{ $alumno->foto_existe ? 'Con foto' : 'Sin foto' }}
                                        </span>

                                        <label class="cursor-pointer rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-[10px] font-black text-slate-600 transition hover:border-[#006492] hover:text-[#006492] dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300">
                                            {{ $alumno->foto_existe ? 'Reemplazar' : 'Subir foto' }}
                                            <input type="file" class="hidden"
                                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                                @change="abrirEditor($event, {{ (int) $alumno->id }}, @js(trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre)))">
                                        </label>

                                        @if ($alumno->foto_existe && $alumno->foto_url)
                                            <button type="button"
                                                @click="abrirEditorUrl(@js($alumno->foto_url), {{ (int) $alumno->id }}, @js(trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre)))"
                                                class="rounded-lg border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-[10px] font-black text-[#006492] transition hover:bg-sky-100 dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-300">
                                                Ajustar
                                            </button>
                                        @endif
                                    </div>
                                    @error('fotoEditor')
                                        <p class="mt-1 max-w-[220px] text-[10px] font-bold leading-4 text-red-600">{{ $message }}</p>
                                    @enderror
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


    {{-- Editor visual de fotografía: encuadre institucional 5:6 --}}
    <div x-cloak x-show="editorOpen" x-transition.opacity
        class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/80 p-3 backdrop-blur-sm"
        @keydown.escape.window="if (!uploadingEditor) cerrarEditor()">
        <div x-show="editorOpen" x-transition.scale.origin.center
            class="max-h-[96vh] w-full max-w-4xl overflow-y-auto rounded-[30px] bg-white shadow-2xl dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-zinc-800">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[.2em] text-[#88AC2E]">Credencial institucional</p>
                    <h3 class="mt-1 text-lg font-black text-slate-900 dark:text-white">Ajustar fotografía</h3>
                    <p class="mt-0.5 text-xs font-semibold text-slate-500" x-text="editorName"></p>
                </div>
                <button type="button" @click="cerrarEditor()" :disabled="uploadingEditor"
                    class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:opacity-50 dark:hover:bg-zinc-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <div class="grid gap-6 p-5 lg:grid-cols-[1fr_300px] lg:p-6">
                <div class="rounded-[26px] bg-slate-100 p-4 dark:bg-zinc-950">
                    <div class="mx-auto w-full max-w-[420px]">
                        <p class="mb-2 text-center text-[11px] font-black uppercase tracking-[.2em] text-slate-400">Vista final · 2.5 × 3 cm</p>
                        <canvas x-ref="cropCanvas" width="295" height="354"
                            class="mx-auto aspect-[5/6] w-full max-w-[295px] touch-none cursor-grab rounded-2xl bg-white shadow-xl ring-1 ring-black/10 active:cursor-grabbing"
                            @pointerdown="iniciarArrastre($event)"
                            @pointermove="moverArrastre($event)"
                            @pointerup="terminarArrastre()"
                            @pointercancel="terminarArrastre()"
                            @pointerleave="terminarArrastre()"></canvas>
                        <p class="mt-3 text-center text-xs text-slate-500">Arrastra la fotografía dentro del marco hasta dejar el rostro centrado.</p>
                    </div>
                </div>

                <aside class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-500">Zoom</span>
                            <span class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-black text-slate-700 dark:bg-zinc-800 dark:text-zinc-200" x-text="Math.round(zoom * 100) + '%'">100%</span>
                        </div>
                        <input type="range" min="1" max="3" step="0.01" x-model.number="zoom" @input="dibujarEditor()"
                            class="mt-3 w-full accent-[#006492]">
                    </div>

                    <div class="rounded-2xl border border-slate-200 p-4 dark:border-zinc-800">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500">Rotación</span>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <button type="button" @click="rotar(-90)" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-200">↶ -90°</button>
                            <button type="button" @click="rotar(90)" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-200">↷ +90°</button>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-xs leading-5 text-sky-800 dark:border-sky-900/60 dark:bg-sky-950/25 dark:text-sky-200">
                        <p class="font-black">Salida final</p>
                        <p class="mt-1">295 × 354 px, relación 5:6, JPG institucional. El servidor vuelve a normalizar la imagen antes de guardarla.</p>
                    </div>

                    <button type="button" @click="reiniciarEditor()" :disabled="uploadingEditor"
                        class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50 disabled:opacity-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        Restablecer encuadre
                    </button>
                </aside>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 px-5 py-4 dark:border-zinc-800 sm:flex-row sm:justify-end">
                <button type="button" @click="cerrarEditor()" :disabled="uploadingEditor"
                    class="rounded-2xl border border-slate-200 px-5 py-2.5 text-sm font-black text-slate-600 disabled:opacity-50 dark:border-zinc-700 dark:text-zinc-300">Cancelar</button>
                <button type="button" @click="exportarEditor()" :disabled="uploadingEditor"
                    class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#006492] px-5 py-2.5 text-sm font-black text-white shadow-lg shadow-sky-900/15 disabled:opacity-60">
                    <template x-if="!uploadingEditor"><span>Guardar fotografía ajustada</span></template>
                    <template x-if="uploadingEditor"><span class="inline-flex items-center gap-2"><span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span> Guardando…</span></template>
                </button>
            </div>
        </div>
    </div>

    @script
        <script>
            Alpine.data('credencialesFotosPro', () => ({
                exportando: false,
                gestorFotos: false,
                editorOpen: false,
                editorStudentId: null,
                editorName: '',
                image: null,
                zoom: 1,
                rotation: 0,
                offsetX: 0,
                offsetY: 0,
                dragging: false,
                lastX: 0,
                lastY: 0,
                uploadingEditor: false,

                abrirEditor(event, id, nombre) {
                    const file = event.target.files?.[0];
                    event.target.value = '';
                    this.abrirEditorArchivo(file, id, nombre);
                },

                abrirEditorArchivo(file, id, nombre) {
                    if (!file) return;

                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                        Swal.fire('Formato no válido', 'Selecciona una fotografía JPG, PNG o WebP.', 'warning');
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire('Fotografía demasiado grande', 'La fotografía puede pesar hasta 5 MB.', 'warning');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = () => this.cargarImagenEditor(reader.result, id, nombre);
                    reader.readAsDataURL(file);
                },

                async abrirEditorUrl(url, id, nombre) {
                    if (!url) return;

                    try {
                        const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
                        if (!response.ok) throw new Error('No fue posible leer la imagen.');
                        const blob = await response.blob();
                        const file = new File([blob], `foto-${id}.jpg`, { type: blob.type || 'image/jpeg' });
                        this.abrirEditorArchivo(file, id, nombre);
                    } catch (error) {
                        Swal.fire('No se pudo abrir la fotografía', 'La imagen existe en el expediente, pero el navegador no pudo leerla. Prueba recargando la página o vuelve a seleccionar el archivo.', 'warning');
                    }
                },

                cargarImagenEditor(src, id, nombre) {
                    const img = new Image();
                    img.onload = () => {
                        this.image = img;
                        this.editorStudentId = Number(id);
                        this.editorName = nombre || 'Alumno';
                        this.zoom = 1;
                        this.rotation = 0;
                        this.offsetX = 0;
                        this.offsetY = 0;
                        this.editorOpen = true;
                        this.$nextTick(() => this.dibujarEditor());
                    };
                    img.onerror = () => Swal.fire('Imagen inválida', 'No fue posible leer la fotografía seleccionada.', 'error');
                    img.src = src;
                },

                dibujarEditor() {
                    const canvas = this.$refs.cropCanvas;
                    if (!canvas || !this.image) return;

                    const ctx = canvas.getContext('2d');
                    const w = canvas.width;
                    const h = canvas.height;
                    ctx.clearRect(0, 0, w, h);
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, w, h);

                    const normalRotation = ((this.rotation % 360) + 360) % 360;
                    const quarter = normalRotation === 90 || normalRotation === 270;
                    const rotatedW = quarter ? this.image.height : this.image.width;
                    const rotatedH = quarter ? this.image.width : this.image.height;
                    const cover = Math.max(w / rotatedW, h / rotatedH) * this.zoom;

                    ctx.save();
                    ctx.translate(w / 2 + this.offsetX, h / 2 + this.offsetY);
                    ctx.rotate(this.rotation * Math.PI / 180);
                    ctx.scale(cover, cover);
                    ctx.drawImage(this.image, -this.image.width / 2, -this.image.height / 2);
                    ctx.restore();
                },

                iniciarArrastre(event) {
                    this.dragging = true;
                    this.lastX = event.clientX;
                    this.lastY = event.clientY;
                    event.currentTarget.setPointerCapture?.(event.pointerId);
                },

                moverArrastre(event) {
                    if (!this.dragging) return;
                    const rect = event.currentTarget.getBoundingClientRect();
                    const scaleX = event.currentTarget.width / rect.width;
                    const scaleY = event.currentTarget.height / rect.height;
                    this.offsetX += (event.clientX - this.lastX) * scaleX;
                    this.offsetY += (event.clientY - this.lastY) * scaleY;
                    this.lastX = event.clientX;
                    this.lastY = event.clientY;
                    this.dibujarEditor();
                },

                terminarArrastre() {
                    this.dragging = false;
                },

                rotar(grados) {
                    this.rotation += grados;
                    this.offsetX = 0;
                    this.offsetY = 0;
                    this.dibujarEditor();
                },

                reiniciarEditor() {
                    this.zoom = 1;
                    this.rotation = 0;
                    this.offsetX = 0;
                    this.offsetY = 0;
                    this.dibujarEditor();
                },

                cerrarEditor() {
                    if (this.uploadingEditor) return;
                    this.editorOpen = false;
                    this.image = null;
                    this.editorStudentId = null;
                },

                exportarEditor() {
                    const canvas = this.$refs.cropCanvas;
                    const alumnoId = this.editorStudentId;
                    if (!canvas || !alumnoId || this.uploadingEditor) return;

                    this.uploadingEditor = true;
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            this.uploadingEditor = false;
                            Swal.fire('No se pudo preparar la fotografía', 'Intenta nuevamente.', 'error');
                            return;
                        }

                        const file = new File([blob], `alumno-${alumnoId}.jpg`, { type: 'image/jpeg' });
                        this.$wire.upload('fotoEditor', file,
                            async () => {
                                try {
                                    await this.$wire.guardarFotoEditor(alumnoId);
                                    this.editorOpen = false;
                                    this.image = null;
                                    this.editorStudentId = null;
                                } catch (error) {
                                    Swal.fire('No se pudo guardar', 'Revisa el archivo e inténtalo nuevamente.', 'error');
                                } finally {
                                    this.uploadingEditor = false;
                                }
                            },
                            () => {
                                this.uploadingEditor = false;
                                Swal.fire('No se pudo cargar la fotografía', 'Revisa el archivo e inténtalo nuevamente.', 'error');
                            }
                        );
                    }, 'image/jpeg', 0.92);
                },
            }));
        </script>
    @endscript
</div>
