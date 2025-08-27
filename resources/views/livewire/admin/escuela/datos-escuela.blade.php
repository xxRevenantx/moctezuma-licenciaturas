<div class="w-full  mx-auto px-3">
  <!-- ENCABEZADO -->
  <div class="flex flex-col gap-4">
    <div class="relative overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 shadow-xl">

      <!-- Acento superior -->
      <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>

      <!-- Fondo decorativo sutil -->
      <div class="pointer-events-none absolute -left-10 -bottom-10 h-28 w-28 rounded-full bg-sky-400/10 blur-2xl"></div>
      <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-indigo-500/10 blur-2xl"></div>

      <!-- Barra de título -->
      <div class="bg-gradient-to-r from-sky-700 to-indigo-700 text-white px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M7 3v4m10-4v4M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"/>
            </svg>
          </span>
          <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">Datos de la escuela</h1>
        </div>
        <span class="hidden sm:inline-flex text-xs opacity-90">Actualiza la ficha institucional</span>
      </div>

      <!-- OVERLAY LOADER al guardar -->
      <div wire:loading.flex wire:target="guardarEscuela"
           class="absolute inset-0 z-30 items-center justify-center bg-white/70 dark:bg-black/50 backdrop-blur-sm">
        <div class="flex flex-col items-center gap-3 text-neutral-700 dark:text-neutral-200">
          <!-- Spinner -->
          <svg class="animate-spin h-9 w-9" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <div class="text-sm font-medium">Guardando cambios…</div>
          <div class="h-1 w-40 rounded-full bg-neutral-200 dark:bg-neutral-700 overflow-hidden">
            <div class="h-full w-1/2 animate-[loader_1.2s_ease-in-out_infinite] bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>
          </div>
        </div>
      </div>

      <!-- FORMULARIO -->
      <div class="p-4 sm:p-6">
        <form wire:submit.prevent="guardarEscuela"
              class="space-y-6"
              role="form"
              aria-label="Formulario de datos de la escuela">
          <flux:field>

            <!-- Nota / ayuda -->
            <div class="rounded-xl bg-neutral-50 dark:bg-neutral-800/70 border border-neutral-200 dark:border-neutral-700 p-3">
              <p class="text-xs text-neutral-600 dark:text-neutral-400">
                Completa la información institucional. Los campos clave como <span class="font-medium">CCT</span> y <span class="font-medium">Contacto</span> son importantes para notificaciones y reportes.
              </p>
            </div>

            <!-- Grid responsive: 1 / 2 / 3 / 4 columnas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

              <!-- Nombre (más ancho) -->
              <flux:input
                wire:model="nombre"
                :label="__('Nombre')"
                type="text"
                placeholder="Nombre de la escuela"
                autofocus
                autocomplete="organization"
                class="sm:col-span-2 lg:col-span-2"
              />

              <flux:input
                wire:model="CCT"
                :label="__('CCT')"
                type="text"
                placeholder="Clave de Centro de Trabajo"
                autocomplete="off"
                class="uppercase tracking-wider"
              />

              <!-- Calle (más ancho) -->
              <flux:input
                wire:model="calle"
                :label="__('Calle')"
                type="text"
                placeholder="Calle"
                autocomplete="address-line1"
                class="sm:col-span-2 lg:col-span-2"
              />

              <flux:input
                wire:model="no_exterior"
                :label="__('No. Exterior')"
                type="text"
                placeholder="Número Exterior"
                autocomplete="address-line2"
              />
              <flux:input
                wire:model="no_interior"
                :label="__('No. Interior')"
                type="text"
                placeholder="Número Interior"
                autocomplete="address-line2"
              />

              <!-- Colonia (más ancho) -->
              <flux:input
                wire:model="colonia"
                :label="__('Colonia')"
                type="text"
                placeholder="Colonia"
                autocomplete="address-level4"
                class="sm:col-span-2"
              />

              <flux:input
                wire:model="codigo_postal"
                :label="__('Código Postal')"
                type="text"
                inputmode="numeric"
                placeholder="Código Postal"
                autocomplete="postal-code"
              />

              <flux:input
                wire:model="ciudad"
                :label="__('Ciudad')"
                type="text"
                placeholder="Ciudad"
                autocomplete="address-level2"
              />
              <flux:input
                wire:model="municipio"
                :label="__('Municipio')"
                type="text"
                placeholder="Municipio"
                autocomplete="address-level2"
              />
              <flux:input
                wire:model="estado"
                :label="__('Estado')"
                type="text"
                placeholder="Estado"
                autocomplete="address-level1"
              />

              <flux:input
                wire:model="telefono"
                :label="__('Teléfono')"
                type="tel"
                placeholder="Teléfono"
                autocomplete="tel"
              />
              <flux:input
                wire:model="correo"
                :label="__('Correo')"
                type="email"
                placeholder="Correo electrónico"
                autocomplete="email"
              />

              <!-- Página Web (más ancho) -->
              <flux:input
                wire:model="pagina_web"
                :label="__('Página Web')"
                type="url"
                placeholder="https://ejemplo.edu.mx"
                autocomplete="url"
                class="sm:col-span-2 lg:col-span-2"
              />
            </div>

            <!-- Botonera -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 pt-2">
              <button
                type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl px-4 py-2.5
                       border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-200
                       bg-white dark:bg-neutral-900 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                Cancelar
              </button>

              <flux:button
                variant="primary"
                type="submit"
                class="w-full sm:w-auto min-w-[150px] cursor-pointer
                       bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                       hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700
                       shadow-lg hover:shadow-xl"
                wire:loading.attr="disabled"
                wire:target="guardarEscuela"
              >
                <span wire:loading.remove wire:target="guardarEscuela">{{ __('Guardar') }}</span>
                <span wire:loading wire:target="guardarEscuela" class="inline-flex items-center gap-2">
                  <span class="w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                  {{ __('Guardando…') }}
                </span>
              </flux:button>
            </div>
          </flux:field>
        </form>
      </div>
    </div>
  </div>




<!-- Animación barrita del loader -->
<style>
@keyframes loader {
  0%   { transform: translateX(-50%); }
  50%  { transform: translateX(60%); }
  100% { transform: translateX(160%); }
}
.animate-\[loader_1\.2s_ease-in-out_infinite]{
  animation: loader 1.2s ease-in-out infinite;
}
</style>

</div>
