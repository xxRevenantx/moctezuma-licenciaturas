<!-- modal-pro -->
<div x-data="{ show: @entangle('open') }"
     x-show="show"
     x-cloak
     @keydown.escape.window="show = false; $wire.cerrarModal()"
     class="fixed inset-0 z-50 flex items-center justify-center">

    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-gray-900/50 dark:bg-black/50 backdrop-blur-sm">
    </div>

    <!-- Modal card -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="relative w-full max-w-3xl mx-4 sm:mx-6">

        <form wire:submit.prevent="actualizarProfesor"
              class="relative overflow-hidden rounded-2xl ring-1 ring-gray-200 dark:ring-neutral-700 bg-white dark:bg-neutral-900 shadow-xl">

            <!-- Accent bar -->
            <div class="h-1 w-full bg-gradient-to-r from-indigo-500 via-sky-500 to-emerald-500"></div>

            <!-- Close button -->
            <button type="button"
                    @click="show = false; $wire.cerrarModal()"
                    class="absolute top-3 right-3 inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-neutral-800 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    aria-label="Cerrar">
                &times;
            </button>

            <flux:field>
                <!-- Header -->
                <div class="px-6 pt-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white text-center flex items-center justify-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"/>
                            </svg>
                        </span>
                        Editar Profesor
                        <flux:badge color="indigo" class="ml-1">
                            {{ $nombre }} {{ $apellido_paterno }} {{ $apellido_materno }}
                        </flux:badge>
                    </h2>
                </div>

                <div class="px-6 pb-6 mt-4 space-y-6">

                    <!-- Uploader -->
                    <div class="rounded-xl border border-gray-200 dark:border-neutral-800 p-4 bg-gray-50/70 dark:bg-neutral-800/40">
                        <flux:input
                            wire:model.live="foto_nueva"
                            :label="__('Foto del profesor')"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png" />

                        <div wire:loading wire:target="foto_nueva"
                             class="mt-3 flex items-center justify-center gap-2 text-indigo-600 dark:text-indigo-400">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>Cargando imagen…</span>
                        </div>

                        <!-- Previews -->
                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="rounded-lg p-3 ring-1 ring-gray-200 dark:ring-neutral-700 bg-white dark:bg-neutral-900">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Imagen actual</p>
                                @if ($foto)
                                    <img src="{{ asset('storage/profesores/' . $foto) }}"
                                         alt="Foto del profesor"
                                         class="w-24 h-24 rounded-full object-cover mx-auto">
                                @else
                                    <p class="text-center text-gray-500 dark:text-gray-400 italic">Sin foto</p>
                                @endif
                            </div>

                            <div class="rounded-lg p-3 ring-1 ring-gray-200 dark:ring-neutral-700 bg-white dark:bg-neutral-900">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Nueva foto</p>
                                @if ($foto_nueva)
                                    <img src="{{ $foto_nueva->temporaryUrl() }}"
                                         alt="Nueva foto del profesor"
                                         class="w-24 h-24 rounded-full object-cover mx-auto">
                                @else
                                    <p class="text-center text-gray-500 dark:text-gray-400 italic">Aún no seleccionada</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Form fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input type="text" badge="Requerido" label="Nombre" placeholder="Nombre" wire:model.live="nombre" />
                        <flux:input type="text" badge="Requerido" label="Apellido Paterno" placeholder="Apellido Paterno" wire:model="apellido_paterno" />
                        <flux:input type="text" badge="Opcional" label="Apellido Materno" placeholder="Apellido Materno" wire:model="apellido_materno" />
                        <flux:input type="text" badge="Opcional" label="Teléfono" placeholder="Teléfono" wire:model="telefono" />
                        <flux:input type="text" badge="Opcional" label="Perfil" placeholder="Perfil" wire:model="perfil" />
                        <flux:input type="color" badge="Opcional" label="Color" placeholder="Color" wire:model="color" />
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row sm:justify-end gap-2 pt-2">
                        <flux:button
                            type="button"
                            @click="show = false; $wire.cerrarModal()"
                            class="cursor-pointer"
                        >
                            Cancelar
                        </flux:button>

                        <flux:button
                            variant="primary"
                            type="submit"
                            class="cursor-pointer bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500"
                            wire:loading.attr="disabled"
                            wire:target="actualizarProfesor, foto_nueva"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Actualizar') }}
                            </div>
                        </flux:button>
                    </div>
                </div>
            </flux:field>

            <!-- Submit loading overlay -->
            <div
                wire:loading.delay
                wire:target="actualizarProfesor"
                class="absolute inset-0 grid place-items-center bg-white/70 dark:bg-neutral-900/70 backdrop-blur-sm">
                <div class="flex items-center gap-3 rounded-xl bg-white dark:bg-neutral-900 px-4 py-3 ring-1 ring-gray-200 dark:ring-neutral-800 shadow">
                    <svg class="h-5 w-5 animate-spin text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-sm text-gray-800 dark:text-gray-200">Guardando cambios…</span>
                </div>
            </div>
        </form>
    </div>
</div>
