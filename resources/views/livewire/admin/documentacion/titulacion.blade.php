<div class="w-full">

    <form wire:submit.prevent="guardarTitulo" class="w-full mx-auto space-y-6">

        {{-- ENCABEZADO PRINCIPAL --}}
        <div class="relative overflow-hidden rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-7 py-5 sm:py-6">
            <!-- Barrita superior -->
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500"></div>

            <!-- Fondos decorativos -->
            <div class="pointer-events-none absolute -left-10 top-8 h-28 w-28 rounded-full bg-sky-500/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -right-6 -bottom-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-3xl"></div>

            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3 sm:gap-4">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-indigo-500 to-violet-500 text-white shadow-lg shadow-sky-900/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6l7 4-7 4-7-4 7-4zM5 14l7 4 7-4M5 10l7 4 7-4"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                            Registro de Título
                        </h2>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 max-w-xl">
                            Completa la información del título profesional según el formato oficial para su registro y emisión.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col items-start sm:items-end gap-2">
                    <p class="text-[11px] uppercase tracking-wide text-neutral-500 dark:text-neutral-400">
                        Apoyo al llenado
                    </p>
                    <flux:button
                        type="button"
                        href="{{ route('admin.word.acta-examen') }}"
                        target="_blank"
                        variant="primary"
                        size="sm"
                        class="bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500 hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30"
                    >
                        Acta de examen
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- BLOQUE: ALUMNO --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-3.33 0-6 1.34-6 3v1h12v-1c0-1.66-2.67-3-6-3z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Alumno</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Selecciona al egresado al que se le expedirá el título.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <flux:select
                    wire:model.live="form.alumno_id"
                    :label="__('Alumno')"
                    placeholder="Selecciona un alumno"
                    searchable
                >
                    @foreach($alumnos as $al)
                        <flux:select.option value="{{ $al->id }}">
                            {{ $al->matricula }} — {{ $al->nombre }} {{ $al->apellido_paterno }} {{ $al->apellido_materno }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        {{-- BLOQUE: DATOS DEL TÍTULO --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 5v14l7-4 7 4V5z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Datos del título</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Nombre del grado o título que se imprimirá en el documento.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <flux:input
                    wire:model.live="form.grado_titulo"
                    :label="__('Grado/Título')"
                    placeholder="LICENCIATURA EN …"
                />
            </div>
        </div>

        {{-- BLOQUE: FUNDAMENTO LEGAL --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h10M7 11h10M7 15h10M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Fundamento legal</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Datos del acuerdo y fechas que respaldan la expedición del título.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                <flux:input
                    wire:model.live="form.acuerdo_numero"
                    :label="__('Acuerdo No.')"
                    placeholder="Ej. 123/2021"
                />
                <flux:input
                    wire:model.live="form.acuerdo_fecha"
                    type="date"
                    :label="__('Fecha del acuerdo')"
                />
                <flux:input
                    wire:model.live="form.examen_fecha"
                    type="date"
                    :label="__('Fecha del examen')"
                />
            </div>
        </div>

        {{-- BLOQUE: EXPEDICIÓN DEL TÍTULO --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-9H3v9a2 2 0 002 2z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Expedición del título</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Lugar y fecha en que se expide el título profesional.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <flux:input
                    wire:model.live="expedido_en"
                    :label="__('Expedido en')"
                    placeholder="Ciudad Altamirano, Guerrero"
                />
                <flux:input
                    wire:model.live="form.expedicion_fecha"
                    type="date"
                    :label="__('Fecha de expedición')"
                />
            </div>
        </div>

        {{-- BLOQUE: REGISTRO Y CERTIFICACIÓN --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fuchsia-500/10 text-fuchsia-600 dark:text-fuchsia-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5 5v14h14V5H5zm4 4h6M9 13h4"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">
                        Departamento de Registro y Certificación
                    </h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Información de libro, foja y registro ante la autoridad correspondiente.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                <flux:input wire:model.live="form.registro" :label="__('Registro')" />
                <flux:input wire:model.live="form.libro" :label="__('Libro')" />
                <flux:input wire:model.live="form.foja" :label="__('Foja')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    wire:model.live="lugar_registro"
                    :label="__('Lugar de registro')"
                    placeholder="Chilpancingo de los Bravo, Guerrero"
                />
                <flux:input
                    wire:model.live="form.registro_fecha"
                    type="date"
                    :label="__('Fecha de registro')"
                />
            </div>
        </div>

        {{-- BLOQUE: DATOS DE LA ESCUELA --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 10h16M4 14h10M4 18h6"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Datos de la Escuela</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Información del plan de estudios, año de egreso y datos del acta de examen.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <flux:input wire:model.live="form.plan_estudios" :label="__('Plan de estudios')" />
                <flux:input
                    wire:model.live="form.anio_egreso"
                    :label="__('Año de egreso')"
                    type="number"
                    min="1950"
                    max="{{ date('Y') + 1 }}"
                    inputmode="numeric"
                />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:input
                    wire:model.live="form.acta_examen"
                    :label="__('Acta de examen')"
                    placeholder="Tipo/Nombre del acta"
                />
                <flux:input wire:model.live="form.acta_numero" :label="__('Número de acta')" />
                <flux:input wire:model.live="form.acta_fecha" type="date" :label="__('Fecha de acta')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="form.acta_expedida_en" :label="__('Acta expedida en')" />
                <flux:input wire:model.live="form.titulo_numero" :label="__('Título número')" />
            </div>
        </div>

        {{-- BLOQUE: CONTROL Y ESTADO --}}
        <div class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-neutral-900/5 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2h6v2m-7 4h8a2 2 0 002-2v-5H5v5a2 2 0 002 2zM7 9h10l-1-5H8l-1 5z"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Control</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Estado del título y folio de cadena (si ya fue emitido).
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <flux:input
                    wire:model.live="form.folio_cadena"
                    :label="__('Folio (cadena)')"
                    placeholder="Opcional hasta emitir"
                />
                <flux:select wire:model.live="form.estatus" :label="__('Estatus')">
                    <flux:select.option value="borrador">Borrador</flux:select.option>
                    <flux:select.option value="emitido">Emitido</flux:select.option>
                    <flux:select.option value="cancelado">Cancelado</flux:select.option>
                </flux:select>
            </div>
        </div>

        {{-- ACCIONES --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <flux:button
                variant="ghost"
                type="button"
                class="border border-neutral-300/80 dark:border-neutral-700 rounded-2xl px-4 py-2.5 text-sm text-neutral-700 dark:text-neutral-200 bg-white/80 dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800"
            >
                Cancelar
            </flux:button>

            <flux:button
                type="submit"
                class="rounded-2xl px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500 hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30"
            >
                Guardar
            </flux:button>
        </div>
    </form>
</div>
