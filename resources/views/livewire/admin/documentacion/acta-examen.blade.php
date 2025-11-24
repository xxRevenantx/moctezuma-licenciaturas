<div>
    <form wire:submit.prevent="guardarActaExamen" class="w-full mx-auto space-y-6">

        {{-- ENCABEZADO --}}
        <div
            class="relative overflow-hidden rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-7 py-5 sm:py-6">
            <!-- Barrita superior -->
            <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500">
            </div>

            <!-- Fondos decorativos -->
            <div class="pointer-events-none absolute -left-10 top-10 h-24 w-24 rounded-full bg-sky-500/15 blur-3xl">
            </div>
            <div
                class="pointer-events-none absolute -right-10 -bottom-12 h-32 w-32 rounded-full bg-indigo-500/15 blur-3xl">
            </div>

            <div class="relative flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3 sm:gap-4">
                    <span
                        class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 via-indigo-500 to-violet-500 text-white shadow-lg shadow-sky-900/40">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-9H3v9a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                            Acta de examen profesional
                        </h2>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 max-w-xl">
                            Registra la fecha del acto protocolario y a los sínodos que participan en el jurado.
                        </p>
                    </div>
                </div>
                <span
                    class="inline-flex items-center gap-1 rounded-full bg-neutral-900/5 px-2.5 py-1 text-[11px] text-neutral-600 dark:bg-neutral-800 dark:text-neutral-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Configuración del acta
                </span>
            </div>
        </div>

        {{-- BLOQUE: FECHA DEL EXAMEN --}}
        <div
            class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-3">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-9H3v9a2 2 0 002 2z" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-50">Alumno</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Registra el nombre del alumno.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <flux:field>
                    <flux:input label="Fecha" type="datetime-local" name="fecha" wire:model.defer="fecha" />
                    @error('fecha')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </flux:field>
                <flux:field>
                    <flux:input label="Fecha" type="datetime-local" name="fecha" wire:model.defer="fecha" />
                    @error('fecha')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </flux:field>

            </div>
        </div>

        {{-- BLOQUE: SÍNODOS DINÁMICOS (COLLAPSE) --}}
        <div x-data="{ openSinodos: true }" class="mb-6">

            {{-- HEADER DEL COLLAPSE --}}
            <div @click="openSinodos = !openSinodos" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3
                       bg-neutral-100 dark:bg-neutral-800/70
                       text-neutral-800 dark:text-neutral-100
                       ring-1 ring-neutral-200 dark:ring-neutral-700
                       hover:bg-neutral-50 dark:hover:bg-neutral-800
                       transition cursor-pointer">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl
                               bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 10l5-5 5 5M7 14l5 5 5-5" />
                        </svg>
                    </span>
                    <div class="text-left">
                        <h3 class="text-sm font-semibold">Sínodos</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Agrega, ordena o elimina a los docentes que integran el jurado del examen.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">


                    <svg :class="{ 'rotate-180': openSinodos }"
                        class="w-5 h-5 text-neutral-500 dark:text-neutral-300 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            {{-- CONTENIDO DEL COLLAPSE --}}
            <div x-show="openSinodos" x-transition x-cloak class="mt-2 rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl
                       ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-5 sm:px-6 py-5 space-y-4">

                {{-- Botón agregar sínodo en el header (desktop) --}}
                <flux:button wire:click="addSinodo" @click.stop variant="primary" size="sm"
                    class="hidden sm:inline-flex bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500
                               hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                    + Agregar sínodo
                </flux:button>
                {{-- Botón agregar sínodo en móvil --}}


                <div class="flex justify-end mb-1 sm:hidden">
                    <flux:button wire:click="addSinodo" @click.stop variant="primary" size="sm"
                        class="w-full bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500
                               hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                        + Agregar sínodo
                    </flux:button>
                </div>

                <div class="space-y-3">
                    @foreach($sinodos as $index => $sinodo)
                        <div
                            class="rounded-2xl border border-neutral-200/80 dark:border-neutral-800
                                                                           bg-neutral-50/70 dark:bg-neutral-900/70 px-3 sm:px-4 py-3 sm:py-3.5">
                            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 md:gap-4 items-start">
                                {{-- Campo nombre sínodo --}}
                                <flux:field>
                                    <flux:input label="Sínodo {{ $index + 1 }}" type="text" name="sinodos[{{ $index }}]"
                                        wire:model.defer="sinodos.{{ $index }}" placeholder="Nombre completo del sínodo" />
                                    @error("sinodos.$index")
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </flux:field>

                                {{-- Controles: subir / bajar / eliminar --}}
                                <div class="flex items-center justify-end gap-1 mt-4">
                                    {{-- Subir --}}
                                    <flux:button type="button" size="xs" variant="ghost"
                                        wire:click="moveSinodoUp({{ $index }})"
                                        class="rounded-full p-1.5 hover:bg-neutral-200/80 dark:hover:bg-neutral-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18" />
                                        </svg>
                                    </flux:button>

                                    {{-- Bajar --}}
                                    <flux:button type="button" size="xs" variant="ghost"
                                        wire:click="moveSinodoDown({{ $index }})"
                                        class="rounded-full p-1.5 hover:bg-neutral-200/80 dark:hover:bg-neutral-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 17.25 12 21m0 0-3.75-3.75M12 21V3" />
                                        </svg>
                                    </flux:button>

                                    {{-- Eliminar --}}
                                    @if(count($sinodos) > 1)
                                        <flux:button type="button" size="xs" variant="ghost"
                                            wire:click="removeSinodo({{ $index }})"
                                            class="rounded-full p-1.5 hover:bg-rose-100/80 text-rose-600
                                                                                                                                   dark:hover:bg-rose-900/40 dark:text-rose-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                            </svg>
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- BOTÓN GUARDAR --}}
        <div class="flex items-center justify-end pt-2">
            <flux:button type="submit" variant="primary"
                class="rounded-2xl px-5 py-2.5 text-sm font-semibold bg-gradient-to-r from-sky-500 via-indigo-500 to-violet-500 hover:from-sky-600 hover:via-indigo-600 hover:to-violet-600 shadow-md shadow-sky-900/30">
                Guardar acta
            </flux:button>
        </div>
    </form>
</div>