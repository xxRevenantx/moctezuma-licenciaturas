<div class="w-full  mx-auto      ">
  <!-- ENCABEZADO -->
  <div class="flex flex-col gap-4">
    <div class="overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow">
      <div class="bg-gradient-to-r from-sky-700 to-indigo-700 text-white px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold">Datos de la escuela</h1>
        <span class="hidden sm:inline-flex text-xs opacity-90">Actualiza la ficha institucional</span>
      </div>

      <!-- FORMULARIO -->
      <div class="p-4 sm:p-6">
        <form wire:submit.prevent="guardarEscuela" class="space-y-5" role="form" aria-label="Formulario de datos de la escuela">
          <flux:field>
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
            <div class="flex items-center justify-end gap-3 pt-2">
              <flux:button
                variant="primary"
                type="submit"
                class="min-w-[140px] cursor-pointer"
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
</div>
