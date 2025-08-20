<div
    x-data="{ show: @entangle('open') }"
    x-cloak
    x-trap.noscroll="show"
    x-show="show"
    @keydown.escape.window="show = false; $wire.cerrarModal()"
    class="fixed inset-0 z-50 flex items-center justify-center"
    aria-live="polite"
>
    <!-- Overlay -->
    <div
        class="absolute inset-0 bg-neutral-900/70 backdrop-blur-sm"
        x-show="show"
        x-transition.opacity
        @click.self="show = false; $wire.cerrarModal()"
        aria-hidden="true"
    ></div>

    <!-- Modal (modal-pro) -->
    <div
        class="relative w-[92vw] sm:w-[88vw] md:w-[70vw] max-w-2xl mx-4 sm:mx-6 bg-white dark:bg-neutral-900 rounded-2xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
        role="dialog" aria-modal="true" aria-labelledby="titulo-modal-asignacion"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        wire:ignore.self
    >
        <!-- Top accent -->
        <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-sky-400 to-indigo-600"></div>

        <!-- Header -->
        <div class="px-5 sm:px-6 pt-4 pb-3 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h2 id="titulo-modal-asignacion" class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">
                    Editar Asignación
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Selecciona la licenciatura, generación y modalidad.
                </p>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center rounded-xl p-2 text-neutral-500 hover:text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                @click="show = false; $wire.cerrarModal()"
                aria-label="Cerrar"
            >
                &times;
            </button>
        </div>

        <!-- Body -->
        <form wire:submit.prevent="actualizarAsignacion" class="px-5 sm:px-6 pb-5">
            <flux:field>
                <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900">
                    <div class="p-4 sm:p-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:select badge="Requerido" :label="__('Licenciatura')" wire:model="licenciatura_id" id="licenciatura_id">
                                <flux:select.option value="">--Selecciona una opción--</flux:select.option>
                                @foreach ($licenciaturas as $licenciatura)
                                    <option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select badge="Requerido" :label="__('Generación')" wire:model="generacion_id" id="generacion_id">
                                <flux:select.option value="">--Selecciona una opción--</flux:select.option>
                                @foreach ($generaciones as $generacion)
                                    <option value="{{ $generacion->id }}">{{ $generacion->generacion }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select badge="Requerido" :label="__('Modalidad')" wire:model="modalidad_id" id="modalidad_id" class="md:col-span-2">
                                <flux:select.option value="">--Selecciona una opción--</flux:select.option>
                                @foreach ($modalidades as $modalidad)
                                    <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row justify-end gap-2 pt-1">
                            <flux:button
                                type="button"
                                class="w-full sm:w-auto cursor-pointer"
                                @click="show = false; $wire.cerrarModal()"
                            >
                                {{ __('Cancelar') }}
                            </flux:button>

                            <flux:button
                                variant="primary"
                                type="submit"
                                class="w-full sm:w-auto cursor-pointer"
                                wire:loading.attr="disabled"
                                wire:target="actualizarAsignacion"
                            >
                                {{ __('Actualizar') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            </flux:field>

            <!-- Loader interno al guardar -->
            <div
                wire:loading.flex
                wire:target="actualizarAsignacion"
                class="absolute inset-0 z-20 items-center justify-center bg-white/70 dark:bg-neutral-900/70 backdrop-blur rounded-2xl"
            >
                <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-neutral-200 dark:ring-neutral-800 shadow">
                    <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm text-neutral-800 dark:text-neutral-200">Guardando cambios…</span>
                </div>
            </div>
        </form>
    </div>
</div>
