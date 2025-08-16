<div class="w-full mx-auto ">
  <style>[x-cloak]{ display:none !important; }</style>

  <!-- Encabezado -->
  <div class="flex flex-col gap-1">
    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white">Crear Usuarios</h1>
    <p class="text-sm text-neutral-500 dark:text-neutral-400">Formulario para crear usuarios.</p>
  </div>

  <!-- Contenedor plegable -->
  <div x-data="{ open: false }" class="my-4">
    <!-- Botón toggle -->
    <button
      @click="open = !open"
      :aria-expanded="open"
      aria-controls="nuevo-usuario-panel"
      class="group inline-flex items-center gap-2 rounded-2xl px-4 py-2.5
             bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow
             focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 dark:focus:ring-offset-neutral-900"
      type="button"
    >
      <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-white/15">
        <!-- ícono lápiz -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M5 19h4l10-10-4-4L5 15v4m14.7-11.3a1 1 0 000-1.4l-2-2a1 1 0 00-1.4 0l-1.6 1.6 3.4 3.4 1.6-1.6z"/>
        </svg>
      </span>
      <span class="font-medium">{{ __('Nuevo usuario') }}</span>

      <!-- flecha -->
      <span class="ml-1 transition-transform duration-200" :class="open ? 'rotate-180' : 'rotate-0'">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 15.5l-6-6h12l-6 6z"/>
        </svg>
      </span>
    </button>

    <!-- Panel -->
    <div
      id="nuevo-usuario-panel"
      x-cloak
      x-show="open"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 -translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
      class="mt-4"
    >
      <form wire:submit.prevent="guardarUsuario" class="relative">
        <!-- Card -->
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg overflow-hidden">
          <!-- Accento -->
          <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

          <div class="p-4 sm:p-6">
            <flux:field>
              <!-- Grid campos -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="relative">
                  <flux:input
                    badge="Requerido"
                    wire:model.live="username"
                    :label="__('Nombre de usuario')"
                    type="text"
                    placeholder="Nombre de usuario"
                    autocomplete="username"
                  />
                  @error('username')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="relative">
                  <flux:input
                    badge="Requerido"
                    wire:model.live="CURP"
                    :label="__('CURP')"
                    type="text"
                    placeholder="CURP"
                    autocomplete="off"
                    class="uppercase tracking-wide"
                  />
                  @error('CURP')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="relative sm:col-span-2">
                  <flux:input
                    badge="Requerido"
                    wire:model.live="email"
                    :label="__('Email')"
                    type="email"
                    value="{{ $email }}"
                    placeholder="correo@ejemplo.com"
                    autocomplete="email"
                  />
                  @error('email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
              </div>

              <!-- Roles -->
              <div class="mt-5">
                <div class="mb-2 text-sm font-medium text-neutral-700 dark:text-neutral-200">Listado de roles <span class="ml-1 inline-block rounded bg-indigo-100 dark:bg-indigo-900/40 px-1.5 py-0.5 text-[10px] text-indigo-700 dark:text-indigo-300">Requerido</span></div>

                <flux:checkbox.group wire:model.live="rol">
                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach ($roles as $rol)
                      <label class="flex items-center gap-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50/60 dark:bg-neutral-900/30 px-3 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                        <flux:checkbox value="{{ $rol->id }}" />
                        <span class="text-sm text-neutral-800 dark:text-neutral-100">{{ $rol->name }}</span>
                      </label>
                    @endforeach
                  </div>
                </flux:checkbox.group>
                @error('rol')<p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
              </div>

              <!-- Acciones -->
              <div class="mt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2">
                <button
                  type="button"
                  @click="open=false"
                  class="inline-flex justify-center rounded-xl px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-300 dark:focus:ring-offset-neutral-900"
                >
                  Cancelar
                </button>

                <flux:button
                  variant="primary"
                  type="submit"
                  class="min-w-[150px] cursor-pointer"
                  wire:loading.attr="disabled"
                  wire:target="guardarUsuario"
                >
                  <span wire:loading.remove wire:target="guardarUsuario">{{ __('Guardar') }}</span>
                  <span wire:loading wire:target="guardarUsuario" class="inline-flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                    Guardando…
                  </span>
                </flux:button>
              </div>
            </flux:field>
          </div>
        </div>

        <!-- Loader de envío (bloquea la card durante la petición) -->
        <div
          class="absolute inset-0 rounded-2xl bg-white/70 dark:bg-neutral-900/60 backdrop-blur-[2px] flex items-center justify-center z-10"
          wire:loading
          wire:target="guardarUsuario"
        >
          <span class="inline-flex items-center gap-3 px-4 py-2 rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 bg-white dark:bg-neutral-800 shadow">
            <span class="w-6 h-6 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Procesando…</span>
          </span>
        </div>
      </form>
    </div>
  </div>
</div>
