<div class="w-full  mx-auto">

    <!-- Título -->
    <div class="mb-4">
        <div class="rounded-2xl overflow-hidden shadow-sm ring-1 ring-neutral-200 dark:ring-neutral-700">
            <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>
            <div class="bg-white dark:bg-neutral-800 px-4 sm:px-6 py-4">
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-white">Búsqueda Estudiantes</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Busca por nombre, matrícula o CURP.</p>
            </div>
        </div>
    </div>

    <!-- Buscador de alumnos -->
    <div x-data="{ open: false }" class="relative">

        <div class="relative">
            <flux:input label="Buscar estudiante" wire:model.live.debounce.500ms="query" name="buscar_alumno"
                id="buscar_alumno" type="text" placeholder="Buscar alumno por nombre, matrícula, CURP o folio"
                autocomplete="off" @focus="open = true" @input="open = true" @blur="setTimeout(() => open = false, 180)"
                wire:keydown.arrow-down="selectIndexDown" wire:keydown.arrow-up="selectIndexUp"
                wire:keydown.enter.prevent="selectAlumno({{ $selectedIndex }})" />

            <div class="pointer-events-none absolute right-3 top-[46px] sm:top-[42px] translate-y-[-50%]" wire:loading
                wire:target="query,selectAlumno,limpiarAlumno">
                <span
                    class="inline-block w-4 h-4 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin">
                </span>
            </div>

            @if (!empty($alumnos))
                <ul x-show="open" x-transition x-cloak
                    class="absolute left-0 right-0 z-50 mt-2 max-h-72 overflow-y-auto rounded-2xl border border-neutral-200 bg-white p-2 shadow-2xl ring-1 ring-black/5 dark:border-neutral-700 dark:bg-neutral-900">
                    @foreach ($alumnos as $index => $alumno)
                        <li wire:click="selectAlumno({{ $index }})"
                            class="cursor-pointer rounded-xl px-3 py-3 transition
                            {{ $selectedIndex === $index
                                ? 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-200 dark:ring-indigo-800/60'
                                : 'text-neutral-700 hover:bg-neutral-50 dark:text-neutral-200 dark:hover:bg-neutral-800/80' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold">
                                        {{ $alumno['apellido_paterno'] ?? '' }}
                                        {{ $alumno['apellido_materno'] ?? '' }}
                                        {{ $alumno['nombre'] ?? '' }}
                                    </p>

                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px]">
                                        <span
                                            class="rounded-full bg-neutral-100 px-2 py-0.5 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                            Matrícula:
                                            <span class="font-mono">
                                                {{ $alumno['matricula'] ?? '----' }}
                                            </span>
                                        </span>

                                        <span
                                            class="rounded-full bg-neutral-100 px-2 py-0.5 text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                            CURP:
                                            <span class="font-mono">
                                                {{ $alumno['CURP'] ?? '----' }}
                                            </span>
                                        </span>

                                        <span
                                            class="rounded-full bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                                            {{ $alumno['licenciatura']['nombre'] ?? 'Sin licenciatura' }}
                                        </span>

                                        <span
                                            class="rounded-full bg-emerald-50 px-2 py-0.5 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200">
                                            {{ $alumno['modalidad']['nombre'] ?? 'Sin modalidad' }}
                                        </span>
                                    </div>
                                </div>

                                @if ($selectedIndex === $index)
                                    <span
                                        class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-white">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if ($selectedAlumno)
            <div
                class="mt-3 rounded-2xl border border-indigo-100 bg-indigo-50/70 px-4 py-3 dark:border-indigo-900/50 dark:bg-indigo-900/20">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                            {{ $selectedAlumno['apellido_materno'] ?? '' }}
                            {{ $selectedAlumno['nombre'] ?? '' }}
                        </p>

                        <div class="mt-1 flex flex-wrap gap-2 text-xs text-neutral-600 dark:text-neutral-300">
                            <span>
                                Matrícula:
                                <strong>{{ $selectedAlumno['matricula'] ?? '----' }}</strong>
                            </span>

                            <span>
                                CURP:
                                <strong>{{ $selectedAlumno['CURP'] ?? '----' }}</strong>
                            </span>

                            <span>
                                Licenciatura:
                                <strong>{{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</strong>
                            </span>
                        </div>
                    </div>

                    <button type="button" wire:click="limpiarAlumno"
                        class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-900/50 dark:bg-neutral-900 dark:text-red-300 dark:hover:bg-red-900/20">
                        Limpiar alumno
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Loader separado (por si quieres mantenerlo) -->
    <div wire:loading.flex wire:target="query" class="justify-center items-center py-8">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
    </div>


    <!-- Contenido del alumno -->
    <div class="mt-4" wire:loading.remove wire:target="query">
        @if ($selectedAlumno)

            @php
                $esEgresado = $this->isEgresado($selectedAlumno);
                $esBaja = $this->isBaja($selectedAlumno);
                $local = isset($selectedAlumno['foraneo']) && $selectedAlumno['foraneo'] === 'false';
            @endphp

            <!-- FICHA / HEADER DEL ALUMNO -->
            <div
                class="rounded-3xl border border-neutral-200 dark:border-neutral-800 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600 text-white shadow-lg shadow-indigo-500/30 px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    {{-- Izquierda: avatar + datos principales --}}
                    <div class="flex items-center gap-4">
                        @if (!empty($selectedAlumno['foto']))
                            <img src="{{ asset('storage/estudiantes/' . $selectedAlumno['foto']) }}"
                                alt="Foto del alumno"
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover ring-2 ring-white/70 shadow-md" />
                        @else
                            <div
                                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white/15 backdrop-blur flex items-center justify-center text-2xl font-semibold shadow-md">
                                {{ mb_substr($selectedAlumno['nombre'] ?? 'A', 0, 1) }}
                            </div>
                        @endif

                        <div>
                            <p class="text-xs uppercase tracking-[0.16em] text-white/70 font-semibold">
                                FICHA DEL ESTUDIANTE
                            </p>
                            <h2 class="mt-1 text-xl sm:text-2xl font-bold leading-tight">
                                {{ $selectedAlumno['nombre'] ?? '---' }}
                                {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                                {{ $selectedAlumno['apellido_materno'] ?? '' }}
                            </h2>

                            <div class="mt-1 flex flex-wrap items-center gap-3 text-xs sm:text-sm text-white/90">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/15 backdrop-blur">
                                    <span
                                        class="font-mono text-[11px] sm:text-xs uppercase tracking-wide opacity-80">Matrícula</span>
                                    <span class="font-semibold">{{ $selectedAlumno['matricula'] ?? '---' }}</span>
                                </span>

                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/15 backdrop-blur">
                                    <span
                                        class="font-mono text-[11px] sm:text-xs uppercase tracking-wide opacity-80">CURP</span>
                                    <span class="font-semibold">{{ $selectedAlumno['CURP'] ?? '---' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Derecha: badges de estado + acciones --}}
                    <div class="flex flex-col items-start md:items-end gap-3">

                        <div class="flex flex-wrap gap-2 justify-start md:justify-end">
                            {{-- Egresado / Activo --}}
                            @if ($esEgresado)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100/90 text-amber-900 text-xs font-semibold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Egresado · {{ $selectedAlumno['generacion']['generacion'] ?? '---' }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/90 text-emerald-900 text-xs font-semibold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Activo · {{ $selectedAlumno['generacion']['generacion'] ?? '---' }}
                                </span>
                            @endif

                            {{-- Local / Foráneo --}}
                            @if ($local)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100/90 text-indigo-900 text-xs font-semibold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                    Local
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100/90 text-orange-900 text-xs font-semibold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    Foráneo
                                </span>
                            @endif

                            {{-- Baja --}}
                            @if ($esBaja)
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100/90 text-red-900 text-xs font-semibold shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Dado de baja
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button variant="ghost" size="sm"
                                class="bg-white/15 hover:bg-white/25 text-white border border-white/30 px-3 py-2 rounded-xl"
                                title="Editar estudiante"
                                @click="Livewire.dispatch('abrirEstudiante', { id: {{ $selectedAlumno['id'] }} })">
                                <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                                    <flux:icon.pencil-square class="w-4 h-4" />
                                    <span>Editar ficha</span>
                                </div>
                            </flux:button>

                            <a target="_blank" href="{{ route('admin.pdf.expediente', $selectedAlumno['id']) }}"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-white text-indigo-700 hover:bg-slate-50 text-xs sm:text-sm font-semibold px-3 py-2 shadow-md shadow-black/20 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <path d="M14 2v6h6" />
                                </svg>
                                <span>Ver expediente</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Línea de tiempo académica rápida --}}
            <div
                class="mt-3 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-900/60 px-4 py-3 text-xs sm:text-sm text-neutral-700 dark:text-neutral-200 flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8M4 18h4" />
                        </svg>
                    </span>
                    <span
                        class="font-semibold uppercase tracking-wide text-[11px] text-neutral-500 dark:text-neutral-400">Resumen
                        académico</span>
                </div>

                <div class="flex flex-wrap gap-3 sm:gap-4 ml-0 sm:ml-4">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        <span class="font-medium">Licenciatura:</span>
                        <span>{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                        <span class="font-medium">Cuatrimestre:</span>
                        <span>{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}°</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span class="font-medium">Modalidad:</span>
                        <span>{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}</span>
                    </span>
                </div>
            </div>

            <livewire:admin.licenciaturas.submodulo.matricula-editar />

            <!-- TARJETAS DE INFORMACIÓN -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                {{-- DATOS GENERALES --}}
                <div
                    class="group rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm hover:shadow-md hover:border-indigo-300/80 dark:hover:border-indigo-500/70 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 20.25a8.25 8.25 0 0 1 15 0" />
                                </svg>
                            </span>
                            <h3
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                                Datos generales
                            </h3>
                        </div>
                    </div>

                    <div class="h-px w-full bg-neutral-200 dark:bg-neutral-800 mb-4"></div>

                    @if (!empty($selectedAlumno['foto']))
                        <div class="mb-4 flex flex-col items-center">
                            <img src="{{ asset('storage/estudiantes/' . $selectedAlumno['foto']) }}"
                                alt="Foto del alumno"
                                class="w-24 h-24 sm:w-28 sm:h-28 object-cover rounded-full ring-2 ring-neutral-200 dark:ring-neutral-700 shadow" />
                            <span class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">Foto del
                                estudiante</span>
                        </div>
                    @endif

                    <flux:field>
                        <flux:input readonly variant="filled" label="Nombre completo"
                            value="{{ $selectedAlumno['apellido_paterno'] ?? '---' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}" />
                        <flux:input readonly variant="filled" label="Matrícula"
                            value="{{ $selectedAlumno['matricula'] ?? '---' }}" />
                        <flux:input readonly variant="filled" label="CURP"
                            value="{{ $selectedAlumno['CURP'] ?? '---' }}" />
                        <flux:input readonly variant="filled" label="Folio"
                            value="{{ $selectedAlumno['folio'] ?? '---' }}" />
                        @php
                            $fechaNacimiento = $selectedAlumno['fecha_nacimiento'] ?? null;
                            $fechaFormateada = '---';
                            if ($fechaNacimiento) {
                                try {
                                    $dt = \Carbon\Carbon::parse($fechaNacimiento);
                                    $fechaFormateada = $dt->format('d/m/Y');
                                } catch (\Exception $e) {
                                    $fechaFormateada = $fechaNacimiento;
                                }
                            }
                        @endphp
                        <flux:input readonly variant="filled" label="Fecha de Nacimiento"
                            value="{{ $fechaFormateada }}" />
                        <flux:input readonly variant="filled" label="Edad" value="{{ $edad ?? '---' }}" />
                        <flux:input readonly variant="filled" label="Género"
                            value="{{ $selectedAlumno['sexo'] ?? '---' }}" />
                        <flux:input readonly variant="filled" label="Nacionalidad"
                            value="{{ $selectedAlumno['pais'] ?? '---' }}" />
                        <flux:input readonly variant="filled" label="Lugar de Nacimiento"
                            value="{{ $selectedAlumno['ciudad_nacimiento']['nombre'] ?? '---' }}" />
                        <flux:input readonly variant="filled" label="Estado de Nacimiento"
                            value="{{ $selectedAlumno['estado_nacimiento']['nombre'] ?? '---' }}" />
                    </flux:field>
                </div>

                {{-- DATOS DE CONTACTO --}}
                <div
                    class="group rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm hover:shadow-md hover:border-indigo-300/80 dark:hover:border-indigo-500/70 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 6.75 12 12l9.75-5.25M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5z" />
                                </svg>
                            </span>
                            <h3
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                                Datos de contacto
                            </h3>
                        </div>
                    </div>

                    <div class="h-px w-full bg-neutral-200 dark:bg-neutral-800 mb-4"></div>

                    <flux:field>
                        <flux:input readonly variant="filled" label="Calle"
                            value="{{ $selectedAlumno['calle'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['calle']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Número Exterior"
                            value="{{ $selectedAlumno['numero_exterior'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['numero_exterior']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Número Interior"
                            value="{{ $selectedAlumno['numero_interior'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['numero_interior']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Colonia"
                            value="{{ $selectedAlumno['colonia'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['colonia']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Código Postal"
                            value="{{ $selectedAlumno['cp'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['cp']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Municipio"
                            value="{{ $selectedAlumno['municipio'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['municipio']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Ciudad/Localidad"
                            value="{{ $selectedAlumno['ciudad']['nombre'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['ciudad']['nombre']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Estado"
                            value="{{ $selectedAlumno['estado']['nombre'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['estado']['nombre']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Teléfono"
                            value="{{ empty($selectedAlumno['telefono']) ? '---' : $selectedAlumno['telefono'] }}"
                            class="{{ empty($selectedAlumno['telefono']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Celular"
                            value="{{ $selectedAlumno['celular'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['celular']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Tutor"
                            value="{{ empty($selectedAlumno['tutor']) ? '---' : $selectedAlumno['tutor'] }}"
                            class="{{ empty($selectedAlumno['tutor']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Correo electrónico"
                            value="{{ $selectedAlumno['user']['email'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['user']['email']) ? 'border-red-500' : '' }}" />
                    </flux:field>
                </div>

                {{-- DATOS ESCOLARES + DOCUMENTOS --}}
                <div
                    class="group rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm hover:shadow-md hover:border-indigo-300/80 dark:hover:border-indigo-500/70 transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="1.6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15v15h-15z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                                </svg>
                            </span>
                            <h3
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                                Datos escolares
                            </h3>
                        </div>
                    </div>

                    <div class="h-px w-full bg-neutral-200 dark:bg-neutral-800 mb-4"></div>

                    <flux:field>
                        <flux:input readonly variant="filled" label="Bachillerato Procedente"
                            value="{{ $selectedAlumno['bachillerato'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['bachillerato']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Licenciatura"
                            value="{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['licenciatura']['nombre']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Generación"
                            value="{{ $selectedAlumno['generacion']['generacion'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['generacion']['generacion']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Cuatrimestre"
                            value="{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}° CUATRIMESTRE"
                            class="{{ empty($selectedAlumno['cuatrimestre']['cuatrimestre']) ? 'border-red-500' : '' }}" />
                        <flux:input readonly variant="filled" label="Modalidad"
                            value="{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}"
                            class="{{ empty($selectedAlumno['modalidad']['nombre']) ? 'border-red-500' : '' }}" />

                        {{-- Documentos de identidad almacenados de forma privada --}}
                        @php
                            $documentosFicha = $selectedAlumno['documentos_identidad_ficha'] ?? [];
                            $documentosEntregadosFicha = collect($documentosFicha)->where('entregado', true)->count();
                        @endphp
                        <div class="mt-4 w-full rounded-2xl border border-neutral-200 bg-neutral-50 p-4 dark:border-neutral-800 dark:bg-neutral-900/60">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-neutral-900 dark:text-white">Documentos de identidad</h4>
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Archivos privados y verificados desde el expediente documental.</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="h-2 w-28 overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                                        <div class="h-full rounded-full bg-gradient-to-r from-[#006492] to-[#88AC2E]" style="width: {{ $selectedAlumno['documentos_identidad_porcentaje'] ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-[#006492] dark:text-sky-300">{{ $documentosEntregadosFicha }}/{{ count($documentosFicha) }}</span>
                                </div>
                            </div>

                            <ul class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                                @foreach ($documentosFicha as $tipo => $documentoFicha)
                                    <li x-data="{ visor: false }" class="rounded-xl border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-800/50">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl {{ $documentoFicha['entregado'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5V5.625A3.375 3.375 0 0011.25 2.25h-4.5A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25v-5.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 2.25V6a2.25 2.25 0 002.25 2.25H19.5"/></svg>
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <p class="truncate text-sm font-semibold text-neutral-800 dark:text-neutral-100">{{ $documentoFicha['label'] }}</p>
                                                    @if ($documentoFicha['obligatorio'])
                                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">Obligatorio</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                                    {{ $documentoFicha['entregado'] ? 'Entregado el '.$documentoFicha['fecha'] : 'Pendiente de entrega' }}
                                                </p>
                                            </div>
                                            @if ($documentoFicha['entregado'] && $documentoFicha['url'])
                                                @can('documentos-identidad.ver')
                                                    <button type="button" @click="visor = true" class="rounded-lg px-3 py-2 text-xs font-semibold text-[#006492] hover:bg-sky-50 dark:text-sky-300 dark:hover:bg-neutral-700">Ver</button>
                                                @endcan
                                            @endif
                                        </div>

                                        @if ($documentoFicha['entregado'] && $documentoFicha['url'])
                                            <div x-cloak x-show="visor" @keydown.escape.window="visor = false" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/65 p-4 backdrop-blur-sm">
                                                <div @click.outside="visor = false" class="relative h-[84vh] w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-neutral-900">
                                                    <div class="flex h-14 items-center justify-between border-b border-neutral-200 px-4 dark:border-neutral-700">
                                                        <p class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $documentoFicha['label'] }}</p>
                                                        <button type="button" @click="visor = false" class="rounded-lg p-2 text-neutral-500 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800">✕</button>
                                                    </div>
                                                    <iframe src="{{ $documentoFicha['url'] }}" class="h-[calc(84vh-3.5rem)] w-full" title="{{ $documentoFicha['label'] }}"></iframe>
                                                </div>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </flux:field>
                </div>
            </div>
        @else
            <!-- Estado vacío -->
            <div
                class="mt-4 rounded-2xl border border-dashed border-indigo-300/80 dark:border-indigo-500/70 bg-indigo-50/70 dark:bg-indigo-900/20 px-4 py-4">
                <div class="flex items-start gap-3">
                    <span
                        class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-indigo-600 text-white shadow">
                        i
                    </span>
                    <div>
                        <p class="text-sm sm:text-base font-semibold text-indigo-900 dark:text-indigo-100">
                            Aún no has seleccionado un alumno.
                        </p>
                        <p class="text-xs sm:text-sm text-indigo-900/80 dark:text-indigo-100/80 mt-1">
                            Usa el buscador superior para localizar un estudiante por nombre, matrícula o CURP y ver su
                            ficha detallada.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Placeholders de tarjetas -->
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @for ($i = 0; $i < 3; $i++)
                    <div
                        class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm">
                        <div class="h-5 w-40 rounded bg-neutral-200 dark:bg-neutral-800 mb-4"></div>
                        <div class="space-y-3">
                            @for ($j = 0; $j < 8; $j++)
                                <div class="h-9 rounded bg-neutral-100 dark:bg-neutral-800/60"></div>
                            @endfor
                        </div>
                    </div>
                @endfor
            </div>
        @endif
    </div>


</div>
