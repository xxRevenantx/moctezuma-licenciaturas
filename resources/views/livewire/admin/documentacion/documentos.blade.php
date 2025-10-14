<div class="w-full mx-auto " x-data="{ open:false }" x-cloak>
  <!-- Header -->
  <div class="mb-5">
    <div class="relative overflow-hidden rounded-2xl shadow-sm ring-1 ring-neutral-200 dark:ring-neutral-700">
      <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>
      <div class="bg-white dark:bg-neutral-800 px-4 sm:px-6 py-4">
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-white">
          Documentación interna del alumno
        </h1>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
          Genera constancias y gestiona la documentación por alumno, generación o licenciatura.
        </p>
      </div>
    </div>
  </div>

  <!-- FORM: Documento personal por alumno -->
  <div class="mb-6">
    <form action="{{ route('admin.pdf.documentacion.documento_personal') }}" method="GET" target="_blank"
          class="rounded-2xl bg-white/90 dark:bg-neutral-800/90 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Buscador de alumno -->
        <div class="relative md:col-span-2">
          <flux:input
            required
            label="Buscar alumno"
            wire:model.live.debounce.500ms="query"
            name="alumno_id"
            id="query"
            type="text"
            placeholder="Buscar alumno por nombre, matrícula o CURP"
            @focus="open = true"
            @input="open = true"
            @blur="setTimeout(() => open = false, 150)"
            wire:keydown.arrow-down="selectIndexDown"
            wire:keydown.arrow-up="selectIndexUp"
            wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
            autocomplete="off"
          />
          <!-- Spinner de búsqueda (dentro del input) -->
          <div class="pointer-events-none absolute right-3 top-[50px] sm:top-[46px] -translate-y-1/2" wire:loading wire:target="query">
            <span class="inline-block w-4 h-4 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
          </div>

          <!-- RESULTADOS de búsqueda -->
          @if (!empty($alumnos))
            <ul
              x-show="open"
              x-transition
              x-cloak
              class="absolute z-[2000] mt-1 w-full max-h-64 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-2xl"
              role="listbox"
            >
              @forelse ($alumnos as $index => $alumno)
                <li
                  class="p-3 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-700/50 {{ $selectedIndex === $index ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}"
                  wire:click="selectAlumno({{ $index }})"
                  @mouseenter="open = true"
                  :aria-selected="{{ $selectedIndex === $index ? 'true' : 'false' }}"
                  role="option"
                >
                  <p class="font-medium text-indigo-600 dark:text-indigo-300">
                    {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                  </p>
                  <p class="text-sm text-neutral-700 dark:text-neutral-300">
                    Matrícula: <span class="font-semibold">{{ $alumno['matricula'] ?? '' }}</span>
                    <span class="mx-2 opacity-40">|</span>
                    CURP: <span class="font-mono">{{ $alumno['CURP'] ?? '' }}</span>
                  </p>
                </li>
              @empty
                <li class="p-3 text-sm text-neutral-600 dark:text-neutral-300">No se encontraron alumnos.</li>
              @endforelse
            </ul>
          @endif
        </div>

        <!-- Tipo documento -->
        <flux:select name="tipo_documento" required label="Tipo de documento" placeholder="Selecciona un tipo de documento" class="w-full">
          <flux:select.option value="certificado-de-estudios">Certificado de estudios</flux:select.option>
          <flux:select.option value="historial-academico">Historial Académico</flux:select.option>
          <flux:select.option value="diploma">Diploma</flux:select.option>
          <flux:select.option value="kardex">Kardex</flux:select.option>
          <flux:select.option value="carta-de-pasante">Carta de pasante</flux:select.option>
          <flux:select.option value="constancia-de-termino">Constancia de Término</flux:select.option>
        </flux:select>

        <!-- Fecha -->
        <flux:input
          required
          label="Fecha de expedición"
          name="fecha_expedicion"
          id="fecha"
          type="date"
          placeholder="Selecciona una fecha"
          class="w-full"
        />

        <!-- CTA -->
        <div class="flex md:items-end">
          <flux:button type="submit" class="w-full md:w-auto md:mt-6" variant="primary">
            Descargar
          </flux:button>
        </div>
      </div>
    </form>
  </div>

  <!-- Selección activa -->
  @if ($selectedAlumno)
    <div class="mb-6 rounded-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <p class="font-semibold text-neutral-900 dark:text-neutral-100">
            {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
          </p>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-sm">
            <span class="inline-flex items-center gap-1 rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-100 px-2 py-0.5">
              Matrícula: <span class="font-mono">{{ $selectedAlumno['matricula'] ?? '' }}</span>
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-100 px-2 py-0.5">
              CURP: <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '' }}</span>
            </span>
            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/20 dark:text-indigo-200 dark:ring-indigo-800/60 px-2 py-0.5">
              Folio: {{ $selectedAlumno["folio"] ?? '----' }}
            </span>
          </div>
        </div>
        <div class="text-sm text-neutral-600 dark:text-neutral-300">
          Licenciatura: <span class="font-medium">{{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</span>
        </div>
      </div>
    </div>
  @endif

  <!-- ACCORDIONS -->
  <style>[x-cloak]{ display:none !important; }</style>

  <!-- Identidad -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-4">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Identidad (Documentación Personal)</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <livewire:admin.documentacion.identidad />
    </div>
  </div>

  <!-- Credenciales -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-4">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Credenciales</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <livewire:admin.documentacion.credenciales />
    </div>
  </div>

  <!-- Etiquetas -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-4">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Etiquetas</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <livewire:admin.documentacion.etiquetas />
    </div>
  </div>

  <!-- Justificantes -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-4">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Justificantes</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <livewire:admin.documentacion.justificantes />
    </div>
  </div>

  <!-- Oficios de solicitud -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-4">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Oficios de solicitud</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
        Para la expedición de oficios, selecciona la generación, documento y fecha de expedición.
      </p>

      <form action="{{ route('admin.pdf.documentacion.documento_oficios') }}" method="GET" target="_blank" class="space-y-4">
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

          <flux:input
            required
            label="Fecha de expedición"
            name="fecha_expedicion"
            id="fecha_of"
            type="date"
            placeholder="Selecciona una fecha"
            class="w-full"
          />

          <div class="flex md:items-end">
            <flux:button type="submit" class="w-full md:w-auto md:mt-6" variant="primary">Descargar</flux:button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Expedición de registros / actas -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-6">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Expedición de Registros de Escolaridad y Actas de Resultados</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
        Para la expedición de Registros de Escolaridad y Actas de Resultados, selecciona la licenciatura, generación y el tipo de documento que deseas descargar.
      </p>

      <form action="{{ route('admin.pdf.documentacion.documento_expedicion') }}" method="GET" target="_blank" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <flux:select name="licenciatura" label="Selecciona la licenciatura" class="w-full" required>
            <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
            @foreach($licenciaturas as $licenciatura)
              <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
            @endforeach
          </flux:select>

          <flux:select name="generacion" label="Selecciona la generación" class="w-full" required>
            <flux:select.option value="">Selecciona una generación</flux:select.option>
            @foreach($generaciones as $generacion)
              <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
            @endforeach
          </flux:select>

          <flux:select name="documento" label="Selecciona el tipo de documento" class="w-full" required>
            <flux:select.option value="">Selecciona un tipo de documento</flux:select.option>
            <flux:select.option value="registro-escolaridad">Registro de Escolaridad</flux:select.option>
            <flux:select.option value="acta-resultados">Acta de Resultados</flux:select.option>
          </flux:select>

          <div class="flex md:items-end">
            <flux:button label="Descargar" type="submit" variant="primary" class="w-full md:w-auto md:mt-6">Descargar</flux:button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <!-- Expedición SÁBANAS -->
  <div x-data="{ openAccordion:false }" x-cloak class="mb-6">
    <button
      @click="openAccordion = !openAccordion"
      class="w-full flex items-center justify-between rounded-2xl px-4 sm:px-5 py-3 bg-neutral-100 dark:bg-neutral-800/70 text-neutral-800 dark:text-neutral-100 ring-1 ring-neutral-200 dark:ring-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition"
    >
      <span class="text-base sm:text-lg font-semibold">Expedición de sábanas</span>
      <svg :class="{'rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </button>
    <div x-show="openAccordion" x-transition
         class="rounded-b-2xl bg-white dark:bg-neutral-800 shadow-lg ring-1 ring-neutral-200 dark:ring-neutral-700 p-4 sm:p-5">
      <p class="text-sm sm:text-base text-neutral-700 dark:text-neutral-200 mb-4">
        Para la expedición de sábanas, selecciona la licenciatura y  generación
      </p>

      <livewire:admin.documentacion.sabanas />

    </div>
  </div>

</div>
