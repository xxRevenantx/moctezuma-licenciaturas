<div x-data x-cloak>
  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>

  <div class="w-full mx-auto space-y-6">

    <!-- HEADER -->
    <div class="mb-2">
      <div
        class="relative overflow-hidden rounded-3xl shadow-2xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 bg-white/95 dark:bg-neutral-900/90">

        <!-- Accent bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>

        <!-- Decorative background -->
        <div class="pointer-events-none absolute -left-10 top-6 h-32 w-32 rounded-full bg-violet-500/15 blur-3xl"></div>
        <div
          class="pointer-events-none absolute -right-10 -bottom-10 h-40 w-40 rounded-full bg-fuchsia-500/10 blur-3xl">
        </div>
        <div
          class="pointer-events-none absolute left-1/2 bottom-0 h-44 w-44 -translate-x-1/2 translate-y-1/3 rounded-full bg-indigo-500/10 blur-3xl">
        </div>

        <div class="relative px-4 sm:px-7 py-5 sm:py-6">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-start gap-3 sm:gap-4">
              <span
                class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 via-violet-500 to-fuchsia-500 text-white shadow-lg shadow-fuchsia-900/40">
                <!-- Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
                </svg>
              </span>
              <div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-neutral-900 dark:text-white">
                  Documentación interna del alumno
                </h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400 max-w-2xl">
                  Genera constancias y gestiona la documentación por alumno, generación o licenciatura en un solo lugar.
                </p>
              </div>
            </div>

            <div class="flex flex-wrap gap-2 justify-start lg:justify-end">
              <span
                class="inline-flex items-center gap-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-700 px-2.5 py-1 text-[11px] font-medium dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Módulo activo
              </span>
              <span
                class="inline-flex items-center gap-1 rounded-full bg-neutral-900/5 px-2.5 py-1 text-[11px] text-neutral-600 dark:bg-neutral-700/60 dark:text-neutral-100">
                PDF
              </span>
              <span
                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-100 dark:ring-indigo-800/60">
                Control escolar
              </span>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- FORM: Documento personal por alumno -->
    <div>
      <form action="{{ route('admin.pdf.documentacion.documento_personal') }}" method="GET" target="_blank"
        class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-xl ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-5 sm:py-6 space-y-4">
        <!-- Title row -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div class="space-y-0.5">
            <h2 class="text-sm sm:text-base font-semibold text-neutral-900 dark:text-neutral-50">
              Documento personal por alumno
            </h2>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
              Selecciona un estudiante, el tipo de documento y la fecha de expedición para generar el PDF.
            </p>
          </div>
          <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
            Los datos se obtienen de la ficha del alumno y sus calificaciones.
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-2">

          <!-- Alumno -->
          <div class="md:col-span-2">
            <x-searchable-select label="Selecciona al estudiante" placeholder="--Selecciona al estudiante--"
              name="alumno_id" wire:model.live="query" {{-- query ahora es el ID del alumno --}}>
              @foreach($alumnos as $index => $alumno)
                <x-searchable-select.option value="{{ $alumno['id'] }}">
                  {{ $alumno['nombre'] }} {{ $alumno['apellido_paterno'] }} {{ $alumno['apellido_materno'] }}
                </x-searchable-select.option>
              @endforeach
            </x-searchable-select>
          </div>

          <!-- Tipo documento -->
          <div>
            <flux:select name="tipo_documento" required label="Tipo de documento"
              placeholder="Selecciona un tipo de documento" class="w-full">
              <flux:select.option value="certificado-de-estudios">Certificado de estudios</flux:select.option>
              <flux:select.option value="historial-academico">Historial Académico</flux:select.option>
              <flux:select.option value="diploma">Diploma</flux:select.option>
              <flux:select.option value="kardex">Kardex</flux:select.option>
              <flux:select.option value="carta-de-pasante">Carta de pasante</flux:select.option>
              <flux:select.option value="constancia-de-termino">Constancia de término</flux:select.option>
            </flux:select>
          </div>

          <!-- Fecha -->
          <div>
            <flux:input required label="Fecha de expedición" name="fecha_expedicion" id="fecha" type="date"
              placeholder="Selecciona una fecha" class="w-full" />
          </div>

          <!-- CTA -->
          <div class="flex md:items-end">
            <flux:button type="submit"
              class="w-full md:w-auto md:mt-6 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 hover:from-indigo-600 hover:via-violet-600 hover:to-fuchsia-600 shadow-lg shadow-fuchsia-900/30"
              variant="primary">
              Descargar
            </flux:button>
          </div>
        </div>
      </form>
    </div>

    <!-- Selección activa -->
    @if ($selectedAlumno)
      <div
        class="rounded-3xl bg-white/95 dark:bg-neutral-900/95 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-6 py-4 sm:py-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <p class="font-semibold text-neutral-900 dark:text-neutral-50 text-sm sm:text-base">
              {{ $selectedAlumno['apellido_paterno'] ?? '' }}
              {{ $selectedAlumno['apellido_materno'] ?? '' }}
              {{ $selectedAlumno['nombre'] ?? '' }}
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs sm:text-sm">
              <span
                class="inline-flex items-center gap-1 rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-100 px-2.5 py-0.5">
                <span class="text-[11px] uppercase tracking-wide text-neutral-500 dark:text-neutral-300">Matrícula</span>
                <span class="font-mono text-xs">{{ $selectedAlumno['matricula'] ?? '' }}</span>
              </span>
              <span
                class="inline-flex items-center gap-1 rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-100 px-2.5 py-0.5">
                <span class="text-[11px] uppercase tracking-wide text-neutral-500 dark:text-neutral-300">CURP</span>
                <span class="font-mono text-xs">{{ $selectedAlumno['CURP'] ?? '' }}</span>
              </span>
              <span
                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-200 dark:ring-indigo-800/60 px-2.5 py-0.5">
                <span class="text-[11px] uppercase tracking-wide">Folio</span>
                <span class="font-semibold text-xs">{{ $selectedAlumno["folio"] ?? '----' }}</span>
              </span>
            </div>
          </div>
          <div class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-300">
            Licenciatura:
            <span class="font-medium">
              {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}
            </span>
          </div>
        </div>
      </div>
    @endif

    <!-- ACCORDIONS -->
    <div class="space-y-3">

      <!-- Acta de examen -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_acta_examen') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_acta_examen', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 21h8M12 17l-4-4h8l-4 4zM4 7h16l-2 5H6L4 7z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Acta de examen</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <livewire:admin.documentacion.acta-examen />
        </div>
      </div>

      <!-- Titulación -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_titulacion') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_titulacion', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 21h8M12 17l-4-4h8l-4 4zM4 7h16l-2 5H6L4 7z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Titulación</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <livewire:admin.documentacion.titulacion />
        </div>
      </div>

      <!-- Identidad -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_identidad') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_identidad', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 7h14M5 11h14M5 15h7M12 19h7" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Identidad (Documentación personal)</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <livewire:admin.documentacion.identidad />
        </div>
      </div>

      <!-- Credenciales -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_credenciales') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_credenciales', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h10M7 11h5M7 15h7M10 3h4a2 2 0 012 2v14l-4-2-4 2V5a2 2 0 012-2z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Credenciales</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <livewire:admin.documentacion.credenciales />
        </div>
      </div>

      <!-- Etiquetas -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_etiquetas') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_etiquetas', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h10M7 11h10M7 15h6M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Etiquetas</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <livewire:admin.documentacion.etiquetas />
        </div>
      </div>

      <!-- Justificantes -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_justificantes') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_justificantes', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7h8M7 11h10M9 15h6M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Justificantes</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <livewire:admin.documentacion.justificantes />
        </div>
      </div>

      <!-- Oficios de solicitud -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_oficios') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_oficios', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-fuchsia-500/10 text-fuchsia-600 dark:text-fuchsia-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h10M7 11h7M7 15h6M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Oficios de solicitud</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
            Para la expedición de oficios, selecciona la generación, el tipo de documento y la fecha de expedición.
          </p>

          <form action="{{ route('admin.pdf.documentacion.documento_oficios') }}" method="GET" target="_blank"
            class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <flux:select name="generacion" label="Selecciona la generación" class="w-full" required>
                <flux:select.option value="">Selecciona una generación</flux:select.option>
                @foreach($generaciones as $generacion)
                  <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                @endforeach
              </flux:select>

              <flux:select name="tipo_documento" label="Selecciona el tipo de documento" class="w-full" required>
                <flux:select.option value="">Selecciona un tipo de documento</flux:select.option>
                <flux:select.option value="matriculas">Matrículas</flux:select.option>
                <flux:select.option value="kardex">Kardex</flux:select.option>
                <flux:select.option value="registro-boletos">Registro y boletas</flux:select.option>
                <flux:select.option value="folios">Folios</flux:select.option>
                <flux:select.option value="certificados">Certificados</flux:select.option>
              </flux:select>

              <flux:input required label="Fecha de expedición" name="fecha_expedicion" id="fecha_of" type="date"
                placeholder="Selecciona una fecha" class="w-full" />

              <div class="flex md:items-end">
                <flux:button type="submit"
                  class="w-full md:w-auto md:mt-6 bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500 hover:from-indigo-600 hover:via-violet-600 hover:to-fuchsia-600 shadow-md shadow-fuchsia-900/30"
                  variant="primary">
                  Descargar
                </flux:button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <!-- Expedición registros / actas -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_expedicion_registros') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_expedicion_registros', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6M7 4h7l4 4v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">
              Expedición de registros de escolaridad y actas de resultados
            </span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
            Para la expedición de Registros de Escolaridad y Actas de Resultados, selecciona la licenciatura, generación
            y el tipo de documento que deseas descargar.
          </p>

          <livewire:admin.documentacion.expedicion-documentos />
        </div>
      </div>

      <!-- Expedición sábanas -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_sabanas') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_sabanas', this.openAccordion);
          }
        }">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-600 dark:text-cyan-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h10M7 11h10M7 15h10M5 5l2-2h10l2 2v14l-2 2H7l-2-2V5z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Expedición de sábanas para certificados</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
            Para la expedición de sábanas, selecciona la licenciatura y la generación correspondiente.
          </p>

          <livewire:admin.documentacion.sabanas />
        </div>
      </div>

      <!-- Estadística -->
      <div x-data="{
          openAccordion: localStorage.getItem('doc_estadistica') === 'true',
          toggle() {
            this.openAccordion = !this.openAccordion;
            localStorage.setItem('doc_estadistica', this.openAccordion);
          }
        }" class="mb-1">
        <button @click="toggle()" class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3.5
                 bg-neutral-100/80 dark:bg-neutral-800/80 text-neutral-900 dark:text-neutral-50
                 ring-1 ring-neutral-200/80 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800
                 transition group">
          <div class="flex items-center gap-2 sm:gap-3">
            <span
              class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-300">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 19h4V9H4v10zm6 0h4V5h-4v14zm6 0h4v-7h-4v7z" />
              </svg>
            </span>
            <span class="text-sm sm:text-base font-semibold">Estadística</span>
          </div>
          <svg :class="{'rotate-180': openAccordion}"
            class="w-5 h-5 transition-transform text-neutral-500 group-hover:text-neutral-700 dark:group-hover:text-neutral-200"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div x-show="openAccordion" x-transition
          class="mt-1 rounded-2xl bg-white dark:bg-neutral-900 shadow-lg ring-1 ring-neutral-200/80 dark:ring-neutral-800 px-4 sm:px-5 py-4 sm:py-5">
          <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
            Para la expedición de estadísticas, selecciona la licenciatura, generación o un resumen general.
          </p>

          <livewire:admin.documentacion.estadistica />
        </div>
      </div>

    </div>
  </div>
</div>