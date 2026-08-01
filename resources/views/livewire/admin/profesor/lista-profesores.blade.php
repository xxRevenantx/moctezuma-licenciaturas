<div class="space-y-6">
    {{-- Encabezado institucional --}}
    <section class="relative overflow-hidden rounded-3xl bg-[#006492] px-5 py-6 text-white shadow-xl shadow-sky-900/10 sm:px-7">
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-24 right-28 h-52 w-52 rounded-full bg-[#88AC2E]/30"></div>

        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20 backdrop-blur">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                        <path d="M4 6.5A2.5 2.5 0 016.5 4h11A2.5 2.5 0 0120 6.5v11a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 17.5v-11z" stroke="currentColor" stroke-width="1.7" />
                        <path d="M8 8h8M8 12h8M8 16h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                    </svg>
                </div>

                <div>
                    <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.14em] ring-1 ring-white/15">
                        Documentación académica
                    </div>
                    <h1 class="text-xl font-black tracking-tight sm:text-2xl">Listas por profesor</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-sky-50/85">
                        Consulta las materias con horario, abre listas individuales o genera un paquete masivo de asistencia y evaluación en una nueva pestaña.
                    </p>
                </div>
            </div>

            @if ($selectedProfesor)
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur">
                        <div class="text-xl font-black">{{ count($materiasAsignadas) }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-sky-50/75">Materias</div>
                    </div>
                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/15 backdrop-blur">
                        <div class="text-xl font-black">{{ $this->totalLicenciaturas }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-sky-50/75">Licenciaturas</div>
                    </div>
                    <div class="rounded-2xl bg-[#88AC2E] px-4 py-3 shadow-lg shadow-lime-950/15">
                        <div class="text-xl font-black">{{ $this->totalGeneraciones }}</div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-white/85">Generaciones</div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Buscador de profesor --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-neutral-800 dark:bg-neutral-950 sm:p-5">
        <div x-data="{
            abierto: false,
            indice: -1,
            texto: '',
            profesores: @js($profesores),

            iniciar() {
                const seleccionado = this.profesores.find((profesor) => String(profesor.id) === String($wire.query));
                if (seleccionado) this.texto = this.nombreProfesor(seleccionado);
            },

            nombreProfesor(profesor) {
                return `${profesor.apellido_paterno ?? ''} ${profesor.apellido_materno ?? ''} ${profesor.nombre ?? ''}`
                    .replace(/\s+/g, ' ')
                    .trim();
            },

            profesoresFiltrados() {
                const termino = this.texto.toLowerCase().trim();
                if (!termino) return this.profesores.slice(0, 25);

                return this.profesores.filter((profesor) => {
                    const nombre = this.nombreProfesor(profesor).toLowerCase();
                    const correo = String(profesor.email ?? '').toLowerCase();
                    const curp = String(profesor.CURP ?? '').toLowerCase();
                    return nombre.includes(termino) || correo.includes(termino) || curp.includes(termino);
                }).slice(0, 25);
            },

            moverAbajo() {
                const total = this.profesoresFiltrados().length;
                if (!total) return;
                this.abierto = true;
                this.indice = this.indice >= total - 1 ? 0 : this.indice + 1;
            },

            moverArriba() {
                const total = this.profesoresFiltrados().length;
                if (!total) return;
                this.abierto = true;
                this.indice = this.indice <= 0 ? total - 1 : this.indice - 1;
            },

            seleccionarConEnter() {
                const profesor = this.profesoresFiltrados()[this.indice];
                if (profesor) this.seleccionar(profesor);
            },

            seleccionar(profesor) {
                this.texto = this.nombreProfesor(profesor);
                this.abierto = false;
                this.indice = -1;
                $wire.seleccionarProfesor(profesor.id);
            },

            limpiar() {
                this.texto = '';
                this.abierto = false;
                this.indice = -1;
                $wire.limpiarProfesorSeleccionado();
            }
        }" x-init="iniciar()" x-cloak class="relative">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <label class="text-xs font-black uppercase tracking-[0.12em] text-slate-500 dark:text-neutral-400">Profesor</label>
                    <p class="mt-0.5 text-xs text-slate-400">Busca por nombre, CURP o correo electrónico.</p>
                </div>

                @if ($selectedProfesor)
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Profesor seleccionado
                    </span>
                @endif
            </div>

            <div class="relative rounded-2xl border border-slate-200 bg-slate-50 transition focus-within:border-[#006492] focus-within:bg-white focus-within:ring-4 focus-within:ring-sky-100 dark:border-neutral-800 dark:bg-neutral-900 dark:focus-within:ring-sky-950/40">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor" stroke-width="1.8" />
                        <path d="M15.5 15.5L20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </div>

                <input
                    type="text"
                    x-model="texto"
                    x-on:focus="abierto = true"
                    x-on:input="abierto = true; indice = -1"
                    x-on:blur="setTimeout(() => abierto = false, 180)"
                    x-on:keydown.arrow-down.prevent="moverAbajo()"
                    x-on:keydown.arrow-up.prevent="moverArriba()"
                    x-on:keydown.enter.prevent="seleccionarConEnter()"
                    x-on:keydown.escape.prevent="abierto = false"
                    autocomplete="off"
                    placeholder="Escribe el nombre del profesor..."
                    class="w-full rounded-2xl border-0 bg-transparent py-4 pl-12 pr-24 text-sm font-semibold text-slate-800 placeholder:text-slate-400 focus:ring-0 dark:text-neutral-100"
                >

                <div class="absolute inset-y-0 right-0 flex items-center gap-1 pr-3">
                    <button
                        type="button"
                        x-show="texto.length > 0"
                        x-on:mousedown.prevent="limpiar()"
                        class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                        title="Limpiar profesor"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        x-on:mousedown.prevent="abierto = !abierto"
                        class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-200 hover:text-slate-700 dark:hover:bg-neutral-800 dark:hover:text-neutral-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition" :class="abierto ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
            </div>

            <div
                x-show="abierto"
                x-transition.origin.top.duration.150ms
                class="absolute z-50 mt-2 max-h-80 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-300/50 dark:border-neutral-800 dark:bg-neutral-950 dark:shadow-black/40"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5 dark:border-neutral-800">
                    <span class="text-[10px] font-black uppercase tracking-[0.12em] text-slate-400">Resultados</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500 dark:bg-neutral-800 dark:text-neutral-400" x-text="profesoresFiltrados().length"></span>
                </div>

                <div class="max-h-72 overflow-y-auto">
                    <template x-if="profesoresFiltrados().length === 0">
                        <div class="px-5 py-8 text-center">
                            <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-neutral-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.7" />
                                    <path d="M16 16l4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-bold text-slate-700 dark:text-neutral-200">No se encontraron profesores</p>
                            <p class="mt-1 text-xs text-slate-400">Prueba con otro nombre, CURP o correo.</p>
                        </div>
                    </template>

                    <template x-for="(profesor, i) in profesoresFiltrados()" :key="profesor.id">
                        <button
                            type="button"
                            x-on:mousedown.prevent="seleccionar(profesor)"
                            class="flex w-full items-center gap-3 px-4 py-3 text-left transition"
                            :class="indice === i ? 'bg-sky-50 dark:bg-sky-950/35' : 'hover:bg-slate-50 dark:hover:bg-neutral-900'"
                        >
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#006492] text-sm font-black text-white">
                                <span x-text="String(profesor.nombre ?? 'P').substring(0, 1)"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black uppercase text-slate-800 dark:text-neutral-100" x-text="nombreProfesor(profesor)"></p>
                                <div class="mt-1 flex flex-wrap gap-1.5 text-[10px]">
                                    <span x-show="profesor.CURP" class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-500 dark:bg-neutral-800 dark:text-neutral-400" x-text="'CURP: ' + profesor.CURP"></span>
                                    <span x-show="profesor.email" class="rounded-full bg-sky-50 px-2 py-0.5 font-semibold text-[#006492] dark:bg-sky-950/40 dark:text-sky-300" x-text="profesor.email"></span>
                                </div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-300" viewBox="0 0 24 24" fill="none">
                                <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </template>
                </div>
            </div>

            <div wire:loading.flex wire:target="seleccionarProfesor,limpiarProfesorSeleccionado" class="absolute inset-0 z-40 items-center justify-center rounded-2xl bg-white/70 backdrop-blur-sm dark:bg-neutral-950/70">
                <div class="flex items-center gap-3 rounded-2xl bg-white px-4 py-3 text-sm font-bold text-[#006492] shadow-lg ring-1 ring-slate-100 dark:bg-neutral-900 dark:ring-neutral-800">
                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Cargando información…
                </div>
            </div>
        </div>
    </section>

    @if ($selectedProfesor)
        @php
            $iniciales = mb_substr($selectedProfesor['nombre'] ?? '', 0, 1) . mb_substr($selectedProfesor['apellido_paterno'] ?? '', 0, 1);
            $totalFiltradas = $this->materiasFiltradas->count();
            $totalSeleccionadas = count($materiasSeleccionadas);
        @endphp

        <form
            method="POST"
            action="{{ route('admin.profesor.listas.masivas') }}"
            target="_blank"
            wire:key="listas-profesor-form-{{ $selectedProfesor['id'] }}"
            x-data="{
                tipo: 'ambas',
                periodo: @js($periodo_id),
                seleccionadas: @js(array_values($materiasSeleccionadas)),
                todas: @js(array_values($materiasSeleccionadas))
            }"
            class="space-y-6"
        >
            @csrf
            <input type="hidden" name="profesor_id" value="{{ $selectedProfesor['id'] }}">
            <input type="hidden" name="tipo" x-model="tipo">
            <template x-for="asignacionId in seleccionadas" :key="asignacionId">
                <input type="hidden" name="asignaciones[]" :value="asignacionId">
            </template>

            {{-- Profesor y generador masivo --}}
            <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_minmax(440px,0.95fr)]">
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                    <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[70px] bg-[#88AC2E]/10"></div>
                    <div class="relative flex items-start gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[#006492] text-lg font-black uppercase text-white shadow-lg shadow-sky-900/15">
                            {{ $iniciales }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-lg font-black uppercase text-slate-800 dark:text-neutral-100">
                                    {{ $selectedProfesor['nombre'] }} {{ $selectedProfesor['apellido_paterno'] }} {{ $selectedProfesor['apellido_materno'] }}
                                </h2>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900">Activo en consulta</span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                @if (!empty($selectedProfesor['CURP']))
                                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3 py-1.5 font-semibold text-slate-600 dark:bg-neutral-900 dark:text-neutral-300">
                                        <span class="text-[9px] font-black uppercase text-slate-400">CURP</span>
                                        {{ $selectedProfesor['CURP'] }}
                                    </span>
                                @endif
                                @if (!empty($selectedProfesor['user']['email']))
                                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-sky-50 px-3 py-1.5 font-semibold text-[#006492] dark:bg-sky-950/40 dark:text-sky-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                            <rect x="4" y="5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6" />
                                            <path d="M5 7l7 5 7-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        {{ $selectedProfesor['user']['email'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-2xl bg-slate-50 px-3 py-3 text-center dark:bg-neutral-900">
                            <div class="text-lg font-black text-[#006492] dark:text-sky-300">{{ count($materiasAsignadas) }}</div>
                            <div class="text-[9px] font-black uppercase tracking-wide text-slate-400">Materias</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-3 py-3 text-center dark:bg-neutral-900">
                            <div class="text-lg font-black text-[#006492] dark:text-sky-300">{{ $this->totalLicenciaturas }}</div>
                            <div class="text-[9px] font-black uppercase tracking-wide text-slate-400">Licenciaturas</div>
                        </div>
                        <div class="rounded-2xl bg-slate-50 px-3 py-3 text-center dark:bg-neutral-900">
                            <div class="text-lg font-black text-[#006492] dark:text-sky-300">{{ $this->totalGeneraciones }}</div>
                            <div class="text-[9px] font-black uppercase tracking-wide text-slate-400">Generaciones</div>
                        </div>
                        <div class="rounded-2xl bg-[#88AC2E]/10 px-3 py-3 text-center">
                            <div class="text-lg font-black text-[#67851c] dark:text-lime-300" x-text="seleccionadas.length"></div>
                            <div class="text-[9px] font-black uppercase tracking-wide text-[#67851c]/70 dark:text-lime-400">Seleccionadas</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-sky-100 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm dark:border-sky-950 dark:from-sky-950/30 dark:to-neutral-950">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-[#006492]/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-[#006492] dark:text-sky-300">
                                Exportación masiva
                            </div>
                            <h3 class="mt-2 text-lg font-black text-slate-800 dark:text-neutral-100">Generar paquete del profesor</h3>
                            <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-neutral-400">Se abrirá un único PDF con portada, índice y todas las listas seleccionadas.</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#88AC2E] text-white shadow-lg shadow-lime-900/15">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M12 3v12m0 0l-4-4m4 4l4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M5 17v2a2 2 0 002 2h10a2 2 0 002-2v-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-slate-500 dark:text-neutral-400">Periodo</span>
                            <select
                                name="periodo"
                                x-model="periodo"
                                x-on:change="$wire.set('periodo_id', periodo)"
                                required
                                class="w-full rounded-2xl border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-[#006492] focus:ring-[#006492] dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                            >
                                <option value="">Selecciona el periodo</option>
                                <option value="9-12">SEP/DIC</option>
                                <option value="1-4">ENE/ABR</option>
                                <option value="5-8">MAY/AGO</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-slate-500 dark:text-neutral-400">Alumnos</span>
                            <select
                                name="filtro_alumnos"
                                required
                                class="w-full rounded-2xl border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 shadow-sm focus:border-[#006492] focus:ring-[#006492] dark:border-neutral-800 dark:bg-neutral-900 dark:text-neutral-200"
                            >
                                <option value="locales">Solo locales</option>
                                <option value="foraneos">Solo foráneos</option>
                                <option value="todos">Locales y foráneos</option>
                            </select>
                        </label>
                    </div>

                    <div class="mt-4">
                        <span class="mb-1.5 block text-[10px] font-black uppercase tracking-wide text-slate-500 dark:text-neutral-400">Contenido del paquete</span>
                        <div class="grid grid-cols-3 rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-slate-200 dark:bg-neutral-900 dark:ring-neutral-800">
                            <button type="button" x-on:click="tipo = 'asistencia'" class="rounded-xl px-2 py-2 text-xs font-black transition" :class="tipo === 'asistencia' ? 'bg-[#006492] text-white shadow' : 'text-slate-500 hover:bg-slate-50 dark:text-neutral-400 dark:hover:bg-neutral-800'">Asistencia</button>
                            <button type="button" x-on:click="tipo = 'evaluacion'" class="rounded-xl px-2 py-2 text-xs font-black transition" :class="tipo === 'evaluacion' ? 'bg-[#006492] text-white shadow' : 'text-slate-500 hover:bg-slate-50 dark:text-neutral-400 dark:hover:bg-neutral-800'">Evaluación</button>
                            <button type="button" x-on:click="tipo = 'ambas'" class="rounded-xl px-2 py-2 text-xs font-black transition" :class="tipo === 'ambas' ? 'bg-[#88AC2E] text-white shadow' : 'text-slate-500 hover:bg-slate-50 dark:text-neutral-400 dark:hover:bg-neutral-800'">Ambas</button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        x-bind:disabled="!periodo || seleccionadas.length === 0"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#006492] px-4 py-3 text-sm font-black text-white shadow-lg shadow-sky-900/15 transition hover:-translate-y-0.5 hover:bg-[#005379] disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="M14 3h5a2 2 0 012 2v5M10 14L21 3M19 14v5a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Abrir PDF masivo en nueva pestaña
                    </button>

                    <p x-show="!periodo" class="mt-2 text-center text-[10px] font-semibold text-amber-600">Selecciona un periodo para habilitar la exportación.</p>
                    <p x-show="periodo && seleccionadas.length === 0" class="mt-2 text-center text-[10px] font-semibold text-amber-600">Selecciona al menos una materia.</p>
                </div>
            </section>

            {{-- Materias --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                <div class="border-b border-slate-200 bg-slate-50/80 px-5 py-4 dark:border-neutral-800 dark:bg-neutral-900/70">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-black text-slate-800 dark:text-neutral-100">Materias con horario asignado</h3>
                                <span class="rounded-full bg-[#88AC2E]/10 px-2.5 py-1 text-[10px] font-black text-[#67851c] dark:text-lime-300">{{ $totalFiltradas }} visibles</span>
                                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-black text-[#006492] dark:bg-sky-950/40 dark:text-sky-300"><span x-text="seleccionadas.length"></span>&nbsp;seleccionadas</span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">Cada generación vinculada al horario produce una lista independiente dentro del paquete.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                x-on:click="seleccionadas = todas.slice()"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 transition hover:border-[#88AC2E] hover:text-[#67851c] dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-300"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                Seleccionar todas
                            </button>
                            <button
                                type="button"
                                x-on:click="seleccionadas = []"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-500 transition hover:border-rose-200 hover:text-rose-600 dark:border-neutral-700 dark:bg-neutral-900 dark:text-neutral-400"
                            >
                                Limpiar selección
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 max-w-md">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor" stroke-width="1.7" />
                                    <path d="M15.5 15.5L20 20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live.debounce.250ms="buscador_materia"
                                placeholder="Buscar por materia, licenciatura, modalidad o cuatrimestre..."
                                class="w-full rounded-2xl border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#006492] focus:ring-[#006492] dark:border-neutral-700 dark:bg-neutral-950 dark:text-neutral-200"
                            >
                        </div>
                    </div>
                </div>

                <div class="relative overflow-x-auto">
                    <div wire:loading.flex wire:target="buscador_materia" class="absolute inset-0 z-20 items-center justify-center bg-white/65 backdrop-blur-[2px] dark:bg-neutral-950/65">
                        <div class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 text-xs font-black text-[#006492] shadow-lg ring-1 ring-slate-100 dark:bg-neutral-900 dark:ring-neutral-800">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Actualizando materias…
                        </div>
                    </div>

                    <table class="min-w-[1100px] w-full text-left">
                        <thead class="bg-[#006492] text-white">
                            <tr class="text-[10px] font-black uppercase tracking-[0.08em]">
                                <th class="w-14 px-4 py-3 text-center">Incluir</th>
                                <th class="w-12 px-3 py-3 text-center">#</th>
                                <th class="px-4 py-3">Materia y generaciones</th>
                                <th class="w-40 px-4 py-3">Modalidad</th>
                                <th class="w-28 px-4 py-3 text-center">Cuatrimestre</th>
                                <th class="w-64 px-4 py-3">Licenciatura</th>
                                <th class="w-80 px-4 py-3">Listas individuales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-neutral-800">
                            @forelse ($this->materiasFiltradas as $row)
                                @php
                                    $generaciones = collect(explode(',', (string) ($row->generaciones_detalle ?? $row->generaciones)))
                                        ->filter()
                                        ->map(function ($detalle) {
                                            [$id, $nombre] = array_pad(explode('|', $detalle, 2), 2, null);

                                            return [
                                                'id' => (int) $id,
                                                'nombre' => $nombre ?: $id,
                                            ];
                                        })
                                        ->values()
                                        ->all();
                                @endphp
                                <tr class="group transition hover:bg-sky-50/45 dark:hover:bg-sky-950/15">
                                    <td class="px-4 py-4 text-center align-top">
                                        <label class="inline-flex cursor-pointer items-center justify-center">
                                            <input
                                                type="checkbox"
                                                value="{{ (string) $row->asignacion_materia_id }}"
                                                x-model="seleccionadas"
                                                class="h-5 w-5 rounded-md border-slate-300 text-[#88AC2E] focus:ring-[#88AC2E] dark:border-neutral-700 dark:bg-neutral-900"
                                            >
                                        </label>
                                    </td>
                                    <td class="px-3 py-4 text-center align-top text-xs font-black text-slate-400">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-4 align-top">
                                        <p class="text-sm font-black text-slate-800 dark:text-neutral-100">{{ $row->materia }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                            @foreach ($generaciones as $generacion)
                                                <span class="rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-black text-[#006492] ring-1 ring-sky-100 dark:bg-sky-950/40 dark:text-sky-300 dark:ring-sky-900">Gen. {{ $generacion['nombre'] }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 align-top">
                                        <span class="inline-flex rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-violet-700 ring-1 ring-violet-100 dark:bg-violet-950/40 dark:text-violet-300 dark:ring-violet-900">
                                            {{ $row->modalidad }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center align-top">
                                        <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl bg-slate-100 px-2 text-sm font-black text-slate-700 dark:bg-neutral-900 dark:text-neutral-200">{{ $row->cuatrimestre }}°</span>
                                    </td>
                                    <td class="px-4 py-4 align-top text-xs font-semibold leading-5 text-slate-600 dark:text-neutral-300">{{ $row->licenciatura }}</td>
                                    <td class="px-4 py-4 align-top">
                                        @if ($periodo_id)
                                            <div class="space-y-2">
                                                @foreach ($generaciones as $generacion)
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <span class="w-20 text-[10px] font-black text-slate-400">Gen. {{ $generacion['nombre'] }}</span>
                                                        <a
                                                            target="_blank"
                                                            rel="noopener"
                                                            href="{{ route($row->modalidad === 'ESCOLARIZADA' ? 'admin.pdf.documentacion.lista_asistencia_escolarizada' : 'admin.pdf.documentacion.lista_asistencia_semiescolarizada', [
                                                                'asignacion_materia' => $row->asignacion_materia_id,
                                                                'licenciatura_id' => $row->licenciatura_id,
                                                                'cuatrimestre_id' => $row->cuatrimestre_id,
                                                                'generacion_id' => $generacion['id'],
                                                                'modalidad_id' => $row->modalidad_id,
                                                                'periodo' => $periodo_id,
                                                            ]) }}"
                                                            class="inline-flex items-center gap-1 rounded-xl bg-[#006492] px-2.5 py-1.5 text-[10px] font-black text-white transition hover:bg-[#005379]"
                                                        >
                                                            Asistencia
                                                        </a>
                                                        <a
                                                            target="_blank"
                                                            rel="noopener"
                                                            href="{{ route('admin.pdf.documentacion.lista_evaluacion', [
                                                                'asignacion_materia' => $row->asignacion_materia_id,
                                                                'licenciatura_id' => $row->licenciatura_id,
                                                                'cuatrimestre_id' => $row->cuatrimestre_id,
                                                                'generacion_id' => $generacion['id'],
                                                                'modalidad_id' => $row->modalidad_id,
                                                                'periodo' => $periodo_id,
                                                            ]) }}"
                                                            class="inline-flex items-center gap-1 rounded-xl bg-[#88AC2E] px-2.5 py-1.5 text-[10px] font-black text-white transition hover:bg-[#759524]"
                                                        >
                                                            Evaluación
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-3 py-2 text-[10px] font-semibold text-amber-700 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                                                Selecciona un periodo para habilitar las listas individuales.
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-14 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-neutral-900">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none">
                                                <circle cx="10.5" cy="10.5" r="6.5" stroke="currentColor" stroke-width="1.7" />
                                                <path d="M15.5 15.5L20 20" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                                            </svg>
                                        </div>
                                        <p class="mt-3 text-sm font-black text-slate-700 dark:text-neutral-200">No hay materias que coincidan con la búsqueda</p>
                                        <p class="mt-1 text-xs text-slate-400">Limpia el filtro o escribe otro término.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3 text-[10px] font-semibold text-slate-500 dark:border-neutral-800 dark:bg-neutral-900/70 dark:text-neutral-400 sm:flex-row sm:items-center sm:justify-between">
                    <span>Orden: licenciatura → cuatrimestre → materia → generación → asistencia/evaluación.</span>
                    <span>Las listas sin alumnos activos se omitirán y aparecerán en el índice del PDF.</span>
                </div>
            </section>
        </form>
    @else
        <section class="rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center dark:border-neutral-800 dark:bg-neutral-950">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-white text-[#006492] shadow-sm ring-1 ring-slate-100 dark:bg-neutral-900 dark:ring-neutral-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none">
                    <circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.7" />
                    <path d="M3.5 18c.8-3.2 2.7-5 5.5-5s4.7 1.8 5.5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                    <path d="M16 8h5M18.5 5.5v5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" />
                </svg>
            </div>
            <h2 class="mt-4 text-base font-black text-slate-800 dark:text-neutral-100">Selecciona un profesor para comenzar</h2>
            <p class="mx-auto mt-1 max-w-lg text-sm leading-6 text-slate-500 dark:text-neutral-400">
                Después podrás elegir el periodo, seleccionar materias, filtrar alumnos locales o foráneos y abrir todas las listas en un solo PDF.
            </p>
        </section>
    @endif
</div>
