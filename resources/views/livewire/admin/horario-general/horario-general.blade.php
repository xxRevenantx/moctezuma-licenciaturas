<div>
    <!-- Encabezado -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-neutral-900 dark:text-white">
            Horario General
        </h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-5">
            Horario Escolarizada y SemiEscolarizada
        </p>
    </div>

    <!-- Acordeón: Escolarizada -->
    <div x-data="{ open: true }" x-cloak class="mb-4">
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="group w-full flex items-center justify-between rounded-xl border border-neutral-200 dark:border-neutral-700
                   bg-gradient-to-r from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-900/80
                   px-4 sm:px-5 py-3 sm:py-4 shadow-sm hover:shadow-md transition-all
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
        >
            <div class="flex items-center gap-3 min-w-0">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg
                             bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                    <!-- Icono calendario -->
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M7 2h2v2h6V2h2v2h3a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h3V2Zm15 7v11a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9h20ZM6 13h5v5H6v-5Z"/>
                    </svg>
                </span>
                <h2 class="text-lg sm:text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                    Horarios Escolarizada
                </h2>
            </div>

            <svg class="h-5 w-5 text-neutral-500 dark:text-neutral-400 transition-transform duration-200"
                 :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="mt-2 rounded-xl border border-neutral-200 dark:border-neutral-700
                    bg-white dark:bg-neutral-800 p-4 sm:p-5 shadow-sm"
        >
            <livewire:admin.horario-general.horario-general-escolarizada />
        </div>
    </div>

    <!-- Acordeón: SemiEscolarizada -->
    <div x-data="{ open: true }" x-cloak class="mb-4">
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="group w-full flex items-center justify-between rounded-xl border border-neutral-200 dark:border-neutral-700
                   bg-gradient-to-r from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-900/80
                   px-4 sm:px-5 py-3 sm:py-4 shadow-sm hover:shadow-md transition-all
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
        >
            <div class="flex items-center gap-3 min-w-0">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg
                             bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                    <!-- Icono reloj -->
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm1 11h5v2h-7V7h2Z"/>
                    </svg>
                </span>
                <h2 class="text-lg sm:text-xl font-semibold text-neutral-900 dark:text-neutral-100">
                    Horarios SemiEscolarizada
                </h2>
            </div>

            <svg class="h-5 w-5 text-neutral-500 dark:text-neutral-400 transition-transform duration-200"
                 :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="mt-2 rounded-xl border border-neutral-200 dark:border-neutral-700
                    bg-white dark:bg-neutral-800 p-4 sm:p-5 shadow-sm"
        >
            <livewire:admin.horario-general.horario-general-semiescolarizada />
        </div>
    </div>
</div>
