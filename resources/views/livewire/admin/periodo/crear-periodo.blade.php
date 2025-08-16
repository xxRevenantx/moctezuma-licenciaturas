<style>[x-cloak]{ display:none !important; }</style>

<div class="w-full  mx-auto px-3 ">
  <!-- Encabezado -->
  <div class="mb-2">
    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-white">Crear Periodos Escolares</h1>
    <p class="text-sm text-neutral-600 dark:text-neutral-400">Formulario para crear periodos escolares.</p>
  </div>

  <!-- Contenedor plegable -->
  <div x-data="{ open:false }" class="my-4">
    <!-- Botón toggle -->
    <button
      type="button"
      @click="open = !open"
      :aria-expanded="open"
      class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
    >
      <!-- Icono -->
       <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-white/15">
        <!-- ícono lápiz -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M5 19h4l10-10-4-4L5 15v4m14.7-11.3a1 1 0 000-1.4l-2-2a1 1 0 00-1.4 0l-1.6 1.6 3.4 3.4 1.6-1.6z"/>
        </svg>
      </span>
      {{ __('Nuevo periodo') }}
      <!-- Chevron -->
      <svg class="w-5 h-5 transition-transform duration-200" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 9l6 6 6-6"/>
      </svg>
    </button>

    <!-- Card del formulario -->
    <div
      x-show="open"
      x-cloak
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 -translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 -translate-y-2"
      class="mt-4 rounded-2xl bg-white dark:bg-neutral-800 ring-1 ring-neutral-200 dark:ring-neutral-700 shadow-lg overflow-hidden"
    >
      <!-- Acento superior -->
      <div class="h-1.5 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>



      <form wire:submit.prevent="crearPeriodo" class="p-4 sm:p-6">

                  <!-- Ayuda / notas (opcional) -->
            <div class="lg:col-span-2 mb-3">
              <div class="rounded-xl bg-neutral-50 dark:bg-neutral-900/60 ring-1 ring-neutral-200 dark:ring-neutral-700 p-3">
                <p class="text-xs text-neutral-600 dark:text-neutral-400">
                  Los <span class="font-medium">Meses</span> se rellenan automáticamente según el cuatrimestre seleccionado.
                  Verifica las fechas antes de guardar.
                </p>
              </div>
            </div>

        <flux:field>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <flux:input
              badge="Requerido"
              wire:model="ciclo_escolar"
              :label="__('Ciclo escolar')"
              type="text"
              placeholder="2024-2025"
              autocomplete="titulo"
            />

            <flux:select
              badge="Requerido"
              wire:model.live="cuatrimestre_id"
              :label="__('Cuatrimestre')"
            >
              <flux:select.option value="0">--Selecciona un cuatrimestre--</flux:select.option>
              @foreach ($cuatrimestres as $cuatrimestre)
                <flux:select.option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->cuatrimestre }} ° CUATRIMESTRE</flux:select.option>
              @endforeach
            </flux:select>

            <flux:input
              badge="Requerido* Selecciona un cuatrimestre"
              variant="filled"
              readonly
              type="text"
              wire:model="mesesPeriodo"
              :label="__('Meses')"
              placeholder="—"
            />

            <flux:select
              badge="Requerido"
              wire:model="generacion_id"
              :label="__('Generación')"
            >
              <flux:select.option value="0">--Selecciona una generación--</flux:select.option>
              @foreach ($generaciones as $generacion)
                <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
              @endforeach
            </flux:select>

            <flux:input
              wire:model="inicio_periodo"
              :label="__('Inicio del periodo')"
              type="date"
              autocomplete="fecha_inicio"
            />

            <flux:input
              wire:model="termino_periodo"
              :label="__('Término del periodo')"
              type="date"
              autocomplete="fecha_termino"
            />



            <!-- Acciones -->
            <div class="md:col-span-2 lg:col-span-4">
              <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                <flux:button
                  variant="primary"
                  type="submit"
                  class="w-full sm:w-auto cursor-pointer inline-flex items-center gap-2"
                  wire:loading.attr="disabled"
                  wire:target="crearPeriodo"
                >
                  <span wire:loading wire:target="crearPeriodo" class="inline-block w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                  <span>{{ __('Guardar') }}</span>
                </flux:button>

                <button
                  type="button"
                  @click="open=false"
                  class="w-full sm:w-auto inline-flex items-center justify-center rounded-xl px-4 py-2.5 ring-1 ring-neutral-300 dark:ring-neutral-600 text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition"
                >
                  Cancelar
                </button>
              </div>
            </div>
          </div>
        </flux:field>
      </form>
    </div>
  </div>
</div>
