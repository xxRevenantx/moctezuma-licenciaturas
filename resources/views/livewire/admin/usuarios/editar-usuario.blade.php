<div
  x-data="{ show: @entangle('open') }"
  x-cloak
  x-init="
    const lock = v => document.documentElement.classList.toggle('overflow-hidden', v);
    lock(show);
    $watch('show', v => lock(v));
  "
  x-show="show"
  @keydown.escape.window="show = false; $wire.cerrarModal()"
  class="fixed inset-0 z-[10000] flex items-center justify-center p-3 sm:p-4"
  role="dialog"
  aria-modal="true"
  aria-labelledby="editar-usuario-title"
>

  <!-- Backdrop -->
  <div
    class="absolute inset-0 bg-black/50 backdrop-blur-[1px]"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click.self="show = false; $wire.cerrarModal()"
  ></div>

  <!-- Panel -->
  <div
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
    x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
    class="relative w-full max-w-2xl sm:max-w-3xl bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl ring-1 ring-neutral-200 dark:ring-neutral-700 overflow-hidden"
  >

    <!-- Acento superior -->
    <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

    <!-- Header -->
    <div class="px-4 sm:px-6 py-4 flex items-center justify-between border-b border-neutral-200 dark:border-neutral-700">
      <h2 id="editar-usuario-title" class="text-base sm:text-lg font-semibold text-neutral-900 dark:text-neutral-100">
        Editar Usuario
        <flux:badge color="indigo" class="ml-1 align-middle">{{ $username }}</flux:badge>
      </h2>

      <button
        @click="show = false; $wire.cerrarModal()"
        class="p-2 rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-500 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-400"
        aria-label="Cerrar modal"
      >
        &times;
      </button>
    </div>

    <!-- Formulario -->
    <form wire:submit.prevent="actualizarUsuario" class="relative">
      <flux:field>
        <div class="px-4 sm:px-6 py-5 space-y-6">

          <!-- Campos -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <flux:input
                wire:model.live="username"
                badge="Requerido"
                :label="__('Nombre de usuario')"
                type="text"
                placeholder="Nombre de usuario"
                autocomplete="username"
              />
              @error('username')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div>
              <flux:input
                wire:model.live="CURP"
                badge="Requerido"
                :label="__('CURP')"
                type="text"
                placeholder="CURP"
                autocomplete="off"
                class="uppercase tracking-wide"
              />
              @error('CURP')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
              <flux:input
                wire:model.live="email"
                badge="Requerido"
                :label="__('Email')"
                type="email"
                placeholder="correo@ejemplo.com"
                autocomplete="email"
              />
              @error('email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
          </div>

          <!-- Status -->
          <div class="space-y-2">
            <flux:label>Status</flux:label>

            @if($rol_name == "SuperAdmin")
              <div class="flex items-center gap-3">
                @if($toggle === true)
                  <flux:switch wire:model.live="status" badge="Requerido" />
                @else
                  <flux:switch variant="filled" disabled wire:model.live="status" />
                @endif

                <flux:button
                  wire:click="toggleStatus"
                  variant="primary"
                  class="cursor-pointer"
                  wire:loading.attr="disabled"
                  wire:target="toggleStatus"
                >
                  <span wire:loading.remove wire:target="toggleStatus">Activar status</span>
                  <span wire:loading wire:target="toggleStatus" class="inline-flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                    Procesando…
                  </span>
                </flux:button>
              </div>
            @else
              <flux:switch wire:model.live="status" />
            @endif
          </div>

          <!-- Roles -->
          <div class="space-y-2">
            <flux:checkbox.group wire:model.live="rol" badge="Requerido" label="Listado de roles">
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach ($roles as $rol)
                  <label class="flex items-center gap-2 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50/50 dark:bg-neutral-900/30 px-3 py-2 hover:bg-neutral-100 dark:hover:bg-neutral-800 cursor-pointer">
                    <flux:checkbox value="{{ $rol->id }}" />
                    <span class="text-sm text-neutral-800 dark:text-neutral-100">{{ $rol->name }}</span>
                  </label>
                @endforeach
              </div>
            </flux:checkbox.group>
            @error('rol')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
          </div>

          <!-- Acciones -->
          <div class="mt-2 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2">
            <button
              type="button"
              @click="show = false; $wire.cerrarModal()"
              class="inline-flex justify-center rounded-xl px-4 py-2.5 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-100 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-300 dark:focus:ring-offset-neutral-900"
            >
              {{ __('Cancelar') }}
            </button>

            <flux:button
              variant="primary"
              type="submit"
              class="min-w-[150px] cursor-pointer"
              wire:loading.attr="disabled"
              wire:target="actualizarUsuario"
            >
              <span wire:loading.remove wire:target="actualizarUsuario">{{ __('Actualizar') }}</span>
              <span wire:loading wire:target="actualizarUsuario" class="inline-flex items-center gap-2">
                <span class="w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                Guardando…
              </span>
            </flux:button>
          </div>
        </div>
      </flux:field>

      <!-- Loader/overlay durante acciones -->
      <div
        class="absolute inset-0 bg-white/70 dark:bg-neutral-900/60 backdrop-blur-[1px] flex items-center justify-center"
        wire:loading
        wire:target="actualizarUsuario,toggleStatus"
        aria-live="assertive"
      >
        <span class="inline-flex items-center gap-3 px-4 py-2 rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 bg-white dark:bg-neutral-800 shadow">
          <span class="w-6 h-6 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
          <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Procesando…</span>
        </span>
      </div>
    </form>
  </div>
</div>
