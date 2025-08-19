<div x-data="{ open: false }" class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Crear Licenciaturas</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Formulario para crear licenciaturas.</p>
    </div>

    <!-- Toggle -->
    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            aria-controls="panel-crear-licenciatura"
                   class="group inline-flex items-center gap-2 rounded-2xl px-4 py-2.5
             bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow
             focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 dark:focus:ring-offset-neutral-900"
        >
            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-white/15">
        <!-- ícono lápiz -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M5 19h4l10-10-4-4L5 15v4m14.7-11.3a1 1 0 000-1.4l-2-2a1 1 0 00-1.4 0l-1.6 1.6 3.4 3.4 1.6-1.6z"/>
        </svg>
      </span>
      <span class="font-medium">{{ __('Nueva licenciatura') }}</span>

      <!-- flecha -->
      <span class="ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : 'rotate-0'">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 15.5l-6-6h12l-6 6z"/>
        </svg>
      </span>
        </button>

        <span class="text-xs text-gray-500 dark:text-gray-400">
            Tip: Puedes adjuntar una imagen cuadrada (PNG/JPG) para el sello de la licenciatura.
        </span>
    </div>

    <!-- Panel -->
    <div
        id="panel-crear-licenciatura"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"
        class="relative"
    >
        <form wire:submit.prevent="guardarLicenciatura" class="group">
            <!-- Card -->
           <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg overflow-hidden">
                <!-- Accent top border -->
               <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

                <!-- Content -->
                <div class="p-5 sm:p-6 lg:p-8">
                    <!-- Title row -->
                    <div class="mb-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 rounded-xl bg-blue-50 dark:bg-blue-900/30 grid place-items-center">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                                          d="M12 6v12m6-6H6"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nueva Licenciatura</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Completa los campos y guarda los cambios.</p>
                            </div>
                        </div>

                        <!-- Submit (top for desktop) -->
                        <div class="hidden md:block">

                        </div>
                    </div>

                    <!-- Grid: responsive -->
                    <flux:field>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            <!-- Col: Imagen -->
                            <div class="lg:col-span-1">
                                <div class="rounded-xl border border-dashed border-gray-300 dark:border-neutral-700
                                            p-4 sm:p-5 bg-white dark:bg-neutral-900">
                                    <flux:input
                                        wire:model.live="imagen"
                                        badge="Opcional"
                                        :label="__('Imagen de la licenciatura')"
                                        type="file"
                                        accept="image/jpeg,image/jpg,image/png"
                                    />
                                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        Recomendado: 512×512px, máx. 1MB (PNG o JPG).
                                    </p>

                                    @if ($imagen)
                                        <div class="mt-4 flex flex-col items-center">
                                            <div class="relative">
                                                <img
                                                    src="{{ $imagen->temporaryUrl() }}"
                                                    alt="{{ __('Vista previa') }}"
                                                    class="h-24 w-24 rounded-xl object-cover ring-1 ring-gray-200 dark:ring-neutral-700"
                                                >
                                                <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-[10px] px-2 py-0.5
                                                             rounded-full bg-gray-900 text-white dark:bg-white dark:text-gray-900">
                                                    Preview
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Col: Campos -->
                            <div class="lg:col-span-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <flux:input
                                            badge="Requerido"
                                            wire:model.live="nombre"
                                            :label="__('Licenciatura')"
                                            type="text"
                                            placeholder="Ej. Ingeniería en Sistemas Computacionales"
                                            autofocus
                                            autocomplete="nombre"
                                        />
                                    </div>

                                    <div>
                                        <flux:input
                                            badge="Requerido"
                                            wire:model="slug"
                                            readonly
                                            :label="__('URL')"
                                            type="text"
                                            placeholder="generado-automaticamente"
                                            autocomplete="slug"
                                        />
                                    </div>

                                    <div>
                                        <flux:input
                                            badge="Requerido"
                                            wire:model="nombre_corto"
                                            :label="__('Nombre corto')"
                                            type="text"
                                            placeholder="Ej. ISC"
                                            autocomplete="nombre_corto"
                                        />
                                    </div>

                                    <div class="sm:col-span-2">
                                        <flux:input
                                            badge="Opcional"
                                            wire:model="RVOE"
                                            :label="__('RVOE')"
                                            type="text"
                                            placeholder="Clave RVOE (si aplica)"
                                            autocomplete="RVOE"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:field>

                    <!-- Divider -->
                    <div class="mt-6 border-t border-gray-200 dark:border-neutral-800"></div>

                    <!-- Actions bottom (mobile-first) -->
                      <div class="mt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="open = false"
                            class="inline-flex justify-center rounded-xl px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-300 dark:focus:ring-offset-neutral-900"
                        >
                            Cancelar
                        </button>


                            <flux:button variant="primary" type="submit" class="w-full sm:w-auto cursor-pointer">
                                {{ __('Guardar') }}
                            </flux:button>

                    </div>
                </div>

                <!-- Loader overlay (submit / imagen) -->
                <div
                    wire:loading.delay
                    wire:target="guardarLicenciatura, imagen"
                    class="pointer-events-none absolute inset-0 grid place-items-center bg-white/60 dark:bg-neutral-900/60"
                >
                    <div class="flex items-center gap-3 rounded-xl bg-white/90 dark:bg-neutral-900/90 px-4 py-3 ring-1 ring-gray-200 dark:ring-neutral-700 shadow">
                        <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Procesando…</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
