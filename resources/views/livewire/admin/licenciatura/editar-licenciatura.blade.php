<div
  x-data="{ show: @entangle('open') }"
  x-cloak
  x-effect="document.body.classList.toggle('overflow-hidden', show)"
  @keydown.escape.window="show = false; $wire.cerrarModal()"
  class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 md:p-8"
  role="dialog"
  aria-modal="true"
  x-show="show"
>
  <!-- Overlay con fade/blur -->
  <div
    x-show="show"
    x-transition.opacity.duration.200ms
    class="absolute inset-0 bg-black/40 backdrop-blur-sm"
    @click="show = false; $wire.cerrarModal()"
  ></div>

  <!-- Panel con entrada/salida (scale + translate + blur) -->
  <div
    x-show="show"
    x-transition:enter="transform ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95 blur-sm"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100 blur-0"
    x-transition:leave="transform ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100 blur-0"
    x-transition:leave-end="opacity-0 translate-y-6 sm:translate-y-0 sm:scale-95 blur-sm"
    class="relative w-full max-w-3xl rounded-2xl bg-white dark:bg-neutral-900 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
  >
    <!-- Acento -->
    <div class="h-1 w-full bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>

    <!-- Header -->
    <div class="flex items-start justify-between gap-4 px-5 py-4 sm:px-6 sm:py-5">
      <h2 class="text-lg sm:text-xl font-semibold text-zinc-900 dark:text-zinc-100">
        Editar Licenciatura
        <flux:badge color="indigo" class="align-middle">{{ $nombre }}</flux:badge>
      </h2>

      <button
        @click="show = false; $wire.cerrarModal()"
        type="button"
        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:text-zinc-200 dark:hover:bg-neutral-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
        aria-label="Cerrar"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Contenido -->
    <div class="px-5 sm:px-6 pb-4 sm:pb-6 max-h-[75vh] overflow-y-auto">
      <form wire:submit.prevent="actualizarLicenciatura" class="space-y-6">
        <flux:field>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Col: Imagen -->
            <div class="space-y-4">
              <flux:input
                wire:model.live="imagen_nueva"
                badge="Opcional"
                :label="__('Imagen de la licenciatura')"
                type="file"
                accept="image/jpeg,image/jpg,image/png"
              />

              <div wire:loading wire:target="imagen_nueva" class="flex items-center gap-2 rounded-lg border border-zinc-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 px-3 py-2">
                <svg class="h-5 w-5 animate-spin text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span class="text-sm text-zinc-700 dark:text-zinc-300">Cargando imagen…</span>
              </div>

              <!-- Previews -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 bg-zinc-50 dark:bg-neutral-800/40 p-4 text-center">
                  <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3">Imagen actual</p>
                  @if ($imagen)
                    <img src="{{ asset('storage/licenciaturas/' . $imagen) }}" alt="Imagen de la licenciatura" class="mx-auto h-24 w-24 rounded-xl object-cover shadow">
                  @else
                    <div class="mx-auto h-24 w-24 rounded-xl bg-zinc-200 dark:bg-neutral-700 grid place-items-center text-zinc-500 dark:text-zinc-300 text-xs">Sin imagen</div>
                  @endif
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-neutral-800 bg-zinc-50 dark:bg-neutral-800/40 p-4 text-center">
                  <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3">Nueva imagen</p>
                  @if ($imagen_nueva)
                    <img src="{{ $imagen_nueva->temporaryUrl() }}" alt="Nueva imagen de la licenciatura" class="mx-auto h-24 w-24 rounded-xl object-cover shadow">
                  @else
                    <div class="mx-auto h-24 w-24 rounded-xl bg-zinc-200 dark:bg-neutral-700 grid place-items-center text-zinc-500 dark:text-zinc-300 text-xs">—</div>
                  @endif
                </div>
              </div>
            </div>

            <!-- Col: Campos -->
            <div class="space-y-4">
              <flux:input
                badge="Requerido"
                wire:model.live="nombre"
                :label="__('Licenciatura')"
                type="text"
                placeholder="Nombre de la licenciatura"
                autofocus
                autocomplete="nombre"
              />

              <flux:input
                badge="Requerido"
                wire:model="slug"
                readonly
                :label="__('Url')"
                type="text"
                placeholder="Url"
                autocomplete="slug"
              />

              <flux:input
                badge="Requerido"
                wire:model="nombre_corto"
                :label="__('Nombre corto')"
                type="text"
                placeholder="Nombre corto"
                autocomplete="nombre_corto"
              />

              <flux:input
                badge="Opcional"
                wire:model="RVOE"
                :label="__('RVOE')"
                type="text"
                placeholder="RVOE"
                autocomplete="RVOE"
              />
            </div>
          </div>

          <!-- Footer acciones -->
          <div class="pt-4 border-t border-zinc-200 dark:border-neutral-800 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <flux:button
              @click="show = false; $wire.cerrarModal()"
              type="button"
              class="cursor-pointer"
            >
              {{ __('Cancelar') }}
            </flux:button>

            <flux:button
              variant="primary"
              type="submit"
              class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white"
            >
              {{ __('Actualizar') }}
            </flux:button>
          </div>
        </flux:field>
      </form>
    </div>
  </div>
</div>
