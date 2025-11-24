<div>

  <div class="w-full mx-auto">

    <!-- ENCABEZADO + CONTENEDOR -->
    <div class="flex flex-col gap-5">

      <div class="relative overflow-hidden rounded-3xl border border-neutral-200/80 dark:border-neutral-800 bg-white/90 dark:bg-neutral-900/90 shadow-2xl shadow-sky-900/10">

        <!-- Acento superior -->
        <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>

        <!-- Fondo decorativo -->
        <div class="pointer-events-none absolute -left-16 -bottom-20 h-40 w-40 rounded-full bg-sky-400/15 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-500/10 blur-2xl"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-56 w-56 -translate-x-1/2 -translate-y-1/2 rounded-full bg-blue-500/5 blur-3xl"></div>

        <!-- OVERLAY LOADER al guardar -->
        <div
          wire:loading.flex
          wire:target="guardarEscuela"
          class="absolute inset-0 z-30 flex items-center justify-center bg-white/70 dark:bg-black/60 backdrop-blur-sm"
        >
          <div class="flex flex-col items-center gap-3 text-neutral-800 dark:text-neutral-100">
            <!-- Spinner -->
            <svg class="animate-spin h-9 w-9" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <div class="text-sm font-semibold tracking-tight">Guardando cambios…</div>
            <div class="h-1.5 w-44 rounded-full bg-neutral-200 dark:bg-neutral-800 overflow-hidden">
              <div class="h-full w-1/2 animate-[loader_1.2s_ease-in-out_infinite] bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600"></div>
            </div>
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">
              No cierres esta ventana hasta que el proceso termine.
            </p>
          </div>
        </div>

        <!-- CONTENIDO -->
        <div class="relative px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-7">

          <!-- Barra de título -->
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-5">
            <div class="flex items-start gap-3">
              <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500/90 via-blue-600 to-indigo-600 text-white shadow-lg shadow-indigo-900/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M7 3v4m10-4v4M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z"/>
                </svg>
              </span>
              <div class="space-y-1">
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-neutral-50">
                  Datos de la escuela
                </h1>
                <p class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                  Completa o actualiza la ficha institucional para usarla en reportes, actas y documentos oficiales.
                </p>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 justify-end">
              <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-200 px-3 py-1 text-[11px] font-medium border border-emerald-100 dark:border-emerald-800">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Ficha activa
              </span>
              <span class="hidden sm:inline-flex text-[11px] text-neutral-500 dark:text-neutral-400">
                Última actualización automática tras guardar
              </span>
            </div>
          </div>

          <!-- Nota / ayuda -->
          <div class="mb-6">
            <div class="rounded-2xl bg-neutral-50/90 dark:bg-neutral-900/80 border border-dashed border-neutral-200 dark:border-neutral-700 px-4 py-3 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
              <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                Los campos como <span class="font-semibold text-neutral-800 dark:text-neutral-100">Nombre, CCT</span> y
                <span class="font-semibold text-neutral-800 dark:text-neutral-100">Contacto</span> se usan en documentos oficiales y notificaciones.
              </p>
              <p class="text-[11px] text-neutral-500 dark:text-neutral-500">
                Revisa ortografía y mayúsculas antes de guardar.
              </p>
            </div>
          </div>

          <!-- FORMULARIO -->
          <div>
            <form
              wire:submit.prevent="guardarEscuela"
              class="space-y-7"
              role="form"
              aria-label="Formulario de datos de la escuela"
            >
              <flux:field>

                <!-- Sección: Información general -->
                <div class="space-y-4">
                  <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold tracking-wide text-neutral-700 dark:text-neutral-200 uppercase">
                      Información general
                    </h2>
                    <span class="h-px flex-1 bg-gradient-to-r from-neutral-200/80 via-neutral-200/60 to-transparent dark:from-neutral-700 dark:via-neutral-700/70"></span>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Nombre (más ancho) -->
                    <flux:input
                      wire:model="nombre"
                      :label="__('Nombre')"
                      type="text"
                      placeholder="Nombre oficial de la escuela"
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
                      class="uppercase tracking-[0.18em] text-[13px]"
                    />

                    <flux:input
                      wire:model="pagina_web"
                      :label="__('Página Web')"
                      type="url"
                      placeholder="https://ejemplo.edu.mx"
                      autocomplete="url"
                      class="sm:col-span-2 lg:col-span-2"
                    />
                  </div>
                </div>

                <!-- Sección: Domicilio -->
                <div class="space-y-4 pt-2">
                  <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold tracking-wide text-neutral-700 dark:text-neutral-200 uppercase">
                      Domicilio
                    </h2>
                    <span class="h-px flex-1 bg-gradient-to-r from-neutral-200/80 via-neutral-200/60 to-transparent dark:from-neutral-700 dark:via-neutral-700/70"></span>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
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
                      placeholder="Número exterior"
                      autocomplete="address-line2"
                    />

                    <flux:input
                      wire:model="no_interior"
                      :label="__('No. Interior')"
                      type="text"
                      placeholder="Número interior (opcional)"
                      autocomplete="address-line2"
                    />

                    <!-- Colonia (más ancho) -->
                    <flux:input
                      wire:model="colonia"
                      :label="__('Colonia')"
                      type="text"
                      placeholder="Colonia o barrio"
                      autocomplete="address-level4"
                      class="sm:col-span-2"
                    />

                    <flux:input
                      wire:model="codigo_postal"
                      :label="__('Código Postal')"
                      type="text"
                      inputmode="numeric"
                      placeholder="C.P."
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
                  </div>
                </div>

                <!-- Sección: Contacto -->
                <div class="space-y-4 pt-2">
                  <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-semibold tracking-wide text-neutral-700 dark:text-neutral-200 uppercase">
                      Contacto
                    </h2>
                    <span class="h-px flex-1 bg-gradient-to-r from-neutral-200/80 via-neutral-200/60 to-transparent dark:from-neutral-700 dark:via-neutral-700/70"></span>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <flux:input
                      wire:model="telefono"
                      :label="__('Teléfono')"
                      type="tel"
                      placeholder="Teléfono de contacto"
                      autocomplete="tel"
                    />

                    <flux:input
                      wire:model="correo"
                      :label="__('Correo')"
                      type="email"
                      placeholder="Correo institucional"
                      autocomplete="email"
                      class="sm:col-span-2 lg:col-span-2"
                    />
                  </div>
                </div>

                <!-- Botonera -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-4 border-t border-neutral-200/70 dark:border-neutral-800 mt-4">
                  <p class="text-[11px] text-neutral-500 dark:text-neutral-500">
                    Verifica que los datos coincidan con los documentos oficiales de la institución antes de guardar.
                  </p>

                  <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">

                    <flux:button
                      variant="primary"
                      type="submit"
                      class="w-full sm:w-auto min-w-[160px] cursor-pointer
                             bg-gradient-to-r from-sky-500 via-blue-600 to-indigo-600
                             hover:from-sky-600 hover:via-blue-700 hover:to-indigo-700
                             text-sm font-semibold rounded-2xl shadow-lg hover:shadow-xl shadow-sky-900/30"
                      wire:loading.attr="disabled"
                      wire:target="guardarEscuela"
                    >
                      <span wire:loading.remove wire:target="guardarEscuela">
                        {{ __('Guardar cambios') }}
                      </span>
                      <span wire:loading wire:target="guardarEscuela" class="inline-flex items-center gap-2">
                        <span class="w-4 h-4 rounded-full border-2 border-white/70 border-t-transparent animate-spin"></span>
                        {{ __('Guardando…') }}
                      </span>
                    </flux:button>
                  </div>
                </div>

              </flux:field>
            </form>
          </div>
        </div>
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
