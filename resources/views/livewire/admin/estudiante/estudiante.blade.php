<div class="w-full  mx-auto">

  <!-- Título -->
  <div class="mb-4">
    <div class="rounded-2xl overflow-hidden shadow-sm ring-1 ring-neutral-200 dark:ring-neutral-700">
      <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>
      <div class="bg-white dark:bg-neutral-800 px-4 sm:px-6 py-4">
        <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-white">Búsqueda Estudiantes</h1>
        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Busca por nombre, matrícula o CURP.</p>
      </div>
    </div>
  </div>

  <!-- Buscador + resultados (mantiene tu lógica Livewire/Alpine) -->
  <div x-data="{ open:false }" class="relative">

    <!-- Input de búsqueda -->
    <div class="relative">
      <flux:input
        required
        label="Buscar alumno"
        wire:model.live.debounce.500ms="query"
        name="alumno_id"
        id="query"
        type="text"
        placeholder="Buscar alumno por nombre, matrícula o CURP:"
        @focus="open = true"
        @input="open = true"
        @blur="setTimeout(() => open = false, 150)"
        wire:keydown.arrow-down="selectIndexDown"
        wire:keydown.arrow-up="selectIndexUp"
        wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
        autocomplete="off"
      />

      <!-- Spinner dentro del input mientras busca -->
      <div class="pointer-events-none absolute right-3 top-[46px] sm:top-[42px] translate-y-[-50%]" wire:loading wire:target="query">
        <span class="inline-block w-4 h-4 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
      </div>
    </div>

    @if (!empty($alumnos))
      <ul
        x-show="open"
        x-transition
        x-cloak
        class="absolute z-[1000] mt-1 w-full max-h-64 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-2xl"
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

  <!-- Loader separado (por si quieres mantenerlo) -->
  <div wire:loading.flex wire:target="query" class="justify-center items-center py-8">
    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
  </div>

  <!-- Contenido cuando hay selección -->
  <div wire:loading.remove wire:target="query">
    @if ($selectedAlumno)

      <!-- Header de detalle -->
      <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <h2 class="text-lg sm:text-xl font-semibold text-neutral-800 dark:text-white">
          Detalles del Alumno(a):
          <span class="font-bold">{{ $selectedAlumno['nombre'] ?? '---' }} {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }}</span>
          <span class="mx-2 text-neutral-400">|</span>
          Matrícula: <span class="font-mono">{{ $selectedAlumno['matricula'] ?? '---' }}</span>
          <span class="mx-2 text-neutral-400">|</span>
          CURP: <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '---' }}</span>
        </h2>

        <div class="flex items-center gap-2">
          <flux:button
            variant="primary"
            square
            @click="Livewire.dispatch('abrirEstudiante', { id: {{ $selectedAlumno['id'] }} })"
            class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 cursor-pointer"
            title="Editar estudiante"
          >
            <flux:icon.pencil-square />
          </flux:button>

          <a target="_blank"
            href="{{ route('admin.pdf.expediente', $selectedAlumno['id']) }}"
            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-xl shadow transition"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            Expediente
          </a>
        </div>
      </div>

      <livewire:admin.licenciaturas.submodulo.matricula-editar />

      <!-- Estado (Egresado/Activo) -->
      @if ($selectedAlumno)
        @if ($this->isEgresado($selectedAlumno))
          <div class="mt-4 rounded-xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-3">
            <div class="flex flex-wrap items-center gap-3">
              <p class="font-semibold text-amber-800 dark:text-amber-200">
                Generación Egresada: {{ $selectedAlumno['generacion']['generacion'] ?? '---' }}
              </p>
              @if ($this->isBaja($selectedAlumno))
                <flux:badge color="red">Dado de baja</flux:badge>
              @endif
              @if(isset($selectedAlumno['foraneo']) && $selectedAlumno['foraneo'] === 'false')
                <flux:badge color="indigo">Local</flux:badge>
              @else
                <flux:badge color="orange">Foráneo</flux:badge>
              @endif
            </div>
          </div>
        @else
          <div class="mt-4 rounded-xl border border-emerald-200 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3">
            <div class="flex flex-wrap items-center gap-3">
              <p class="font-semibold text-emerald-800 dark:text-emerald-200">
                Generación Activa: {{ $selectedAlumno['generacion']['generacion'] ?? '---' }}
              </p>
              @if ($this->isBaja($selectedAlumno))
                <flux:badge color="red">Dado de baja</flux:badge>
              @endif
              @if(isset($selectedAlumno['foraneo']) && $selectedAlumno['foraneo'] === 'false')
                <flux:badge color="indigo">Local</flux:badge>
              @else
                <flux:badge color="orange">Foráneo</flux:badge>
              @endif
            </div>
          </div>
        @endif
      @endif

      <!-- Tarjetas de información -->
      <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        {{-- DATOS GENERALES --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 shadow-sm">
          <h3 class="text-lg font-semibold text-center text-neutral-800 dark:text-neutral-100 uppercase pb-3">Datos Generales</h3>
          <div class="h-px w-full bg-neutral-200 dark:bg-neutral-700 mb-4"></div>

          @if (!empty($selectedAlumno['foto']))
            <div class="mb-4 flex flex-col items-center">
              <img src="{{ asset('storage/estudiantes/' . $selectedAlumno['foto']) }}"
                   alt="Foto del alumno"
                   class="w-28 h-28 sm:w-32 sm:h-32 object-cover rounded-full ring-2 ring-neutral-200 dark:ring-neutral-700 shadow" />
              <span class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">Foto del estudiante</span>
            </div>
          @endif

          <flux:field>
            <flux:input readonly variant="filled" label="Nombre completo"
              value="{{ $selectedAlumno['apellido_paterno'] ?? '---' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}" />
            <flux:input readonly variant="filled" label="Matrícula" value="{{ $selectedAlumno['matricula'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="CURP" value="{{ $selectedAlumno['CURP'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Folio" value="{{ $selectedAlumno['folio'] ?? '---' }}" />
            @php
              $fechaNacimiento = $selectedAlumno['fecha_nacimiento'] ?? null;
              $fechaFormateada = '---';
              if ($fechaNacimiento) {
                  try { $dt = \Carbon\Carbon::parse($fechaNacimiento); $fechaFormateada = $dt->format('d/m/Y'); }
                  catch (\Exception $e) { $fechaFormateada = $fechaNacimiento; }
              }
            @endphp
            <flux:input readonly variant="filled" label="Fecha de Nacimiento" value="{{ $fechaFormateada }}" />
            <flux:input readonly variant="filled" label="Edad" value="{{ $edad ?? '---' }}" />
            <flux:input readonly variant="filled" label="Género" value="{{ $selectedAlumno['sexo'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Nacionalidad" value="{{ $selectedAlumno['pais'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Lugar de Nacimiento" value="{{ $selectedAlumno['ciudad_nacimiento']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Estado de Nacimiento" value="{{ $selectedAlumno['estado_nacimiento']['nombre'] ?? '---' }}" />
          </flux:field>
        </div>

        {{-- DATOS DE CONTACTO --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 shadow-sm">
          <h3 class="text-lg font-semibold text-center text-neutral-800 dark:text-neutral-100 uppercase pb-3">Datos de Contacto</h3>
          <div class="h-px w-full bg-neutral-200 dark:bg-neutral-700 mb-4"></div>

          <flux:field>
            <flux:input readonly variant="filled" label="Calle" value="{{ $selectedAlumno['calle'] ?? '---' }}" class="{{ empty($selectedAlumno['calle']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Número Exterior" value="{{ $selectedAlumno['numero_exterior'] ?? '---' }}" class="{{ empty($selectedAlumno['numero_exterior']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Número Interior" value="{{ $selectedAlumno['numero_interior'] ?? '---' }}" class="{{ empty($selectedAlumno['numero_interior']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Colonia" value="{{ $selectedAlumno['colonia'] ?? '---' }}" class="{{ empty($selectedAlumno['colonia']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Código Postal" value="{{ $selectedAlumno['cp'] ?? '---' }}" class="{{ empty($selectedAlumno['cp']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Municipio" value="{{ $selectedAlumno['municipio'] ?? '---' }}" class="{{ empty($selectedAlumno['municipio']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Ciudad/Localidad" value="{{ $selectedAlumno['ciudad']['nombre'] ?? '---' }}" class="{{ empty($selectedAlumno['ciudad']['nombre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Estado" value="{{ $selectedAlumno['estado']['nombre'] ?? '---' }}" class="{{ empty($selectedAlumno['estado']['nombre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Teléfono" value="{{ empty($selectedAlumno['telefono']) ? '---' : $selectedAlumno['telefono'] }}" class="{{ empty($selectedAlumno['telefono']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Celular" value="{{ $selectedAlumno['celular'] ?? '---' }}" class="{{ empty($selectedAlumno['celular']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Tutor" value="{{ empty($selectedAlumno['tutor']) ? '---' : $selectedAlumno['tutor'] }}" class="{{ empty($selectedAlumno['tutor']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Correo electrónico" value="{{ $selectedAlumno['user']['email'] ?? '---' }}" class="{{ empty($selectedAlumno['user']['email']) ? 'border-red-500' : '' }}" />
          </flux:field>
        </div>

        {{-- DATOS ESCOLARES + Documentos --}}
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 shadow-sm">
          <h3 class="text-lg font-semibold text-center text-neutral-800 dark:text-neutral-100 uppercase pb-3">Datos Escolares</h3>
          <div class="h-px w-full bg-neutral-200 dark:bg-neutral-700 mb-4"></div>

          <flux:field>
            <flux:input readonly variant="filled" label="Bachillerato Procedente" value="{{ $selectedAlumno['bachillerato'] ?? '---' }}" class="{{ empty($selectedAlumno['bachillerato']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Licenciatura" value="{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}" class="{{ empty($selectedAlumno['licenciatura']['nombre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Generación" value="{{ $selectedAlumno['generacion']['generacion'] ?? '---' }}" class="{{ empty($selectedAlumno['generacion']['generacion']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Cuatrimestre" value="{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}° CUATRIMESTRE" class="{{ empty($selectedAlumno['cuatrimestre']['cuatrimestre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Modalidad" value="{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}" class="{{ empty($selectedAlumno['modalidad']['nombre']) ? 'border-red-500' : '' }}" />

            <!-- Documentos Entregados -->
            <div class="mt-4 w-full rounded-xl border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/60 p-4">
              <h4 class="mb-3 text-sm sm:text-base font-semibold text-neutral-900 dark:text-white">Documentos entregados</h4>

              <ul class="space-y-3">
                {{-- CURP --}}
                @php
                  $curpNombre = $selectedAlumno['CURP_documento'] ?? null;
                  $curpUrl    = $curpNombre ? asset('storage/documentos/curp/' . $curpNombre) : null;
                @endphp
                <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                  <div class="flex items-center gap-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-700/40 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    <button type="button" @click="show=true" :disabled="{{ $curpNombre ? 'false' : 'true' }}" class="disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $curpNombre ? 'Ver CURP' : 'Sin archivo' }}">
                      <img src="{{ asset('storage/curp.png') }}" class="h-8 w-8" alt="CURP">
                    </button>
                    <span class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-100">CURP</span>
                    @if ($curpNombre)
                      <flux:badge color="green">Entregado</flux:badge>
                    @else
                      <flux:badge color="red">Pendiente</flux:badge>
                    @endif
                  </div>

                  <!-- Modal CURP -->
                  <div x-cloak x-show="show" @keydown.escape.window="show = false" @click.self="show=false"
                       class="fixed inset-0 z-[10000] bg-black/45 backdrop-blur-sm flex items-center justify-center p-4"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                    <div class="relative w-full max-w-5xl h-[80vh] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-neutral-200 dark:ring-neutral-700">
                      <button type="button" @click="show=false" class="absolute top-3 right-3 p-2 rounded-full bg-white/80 dark:bg-neutral-700/60 hover:bg-white dark:hover:bg-neutral-700 shadow">
                        <span class="sr-only">Cerrar</span>✕
                      </button>
                      @if ($curpUrl)
                        <iframe src="{{ $curpUrl }}" class="w-full h-full" title="CURP PDF"></iframe>
                      @else
                        <div class="w-full h-full flex items-center justify-center p-6">
                          <p class="text-neutral-600 dark:text-neutral-300">No hay archivo de CURP.</p>
                        </div>
                      @endif
                    </div>
                  </div>
                </li>

                {{-- ACTA DE NACIMIENTO --}}
                @php
                  $actaNombre = $selectedAlumno['acta_nacimiento'] ?? null;
                  $actaUrl    = $actaNombre ? asset('storage/documentos/actas/' . $actaNombre) : null;
                @endphp
                <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                  <div class="flex items-center gap-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-700/40 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    <button type="button" @click="show=true" :disabled="{{ $actaNombre ? 'false' : 'true' }}" class="disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $actaNombre ? 'Ver Acta' : 'Sin archivo' }}">
                      <img src="{{ asset('storage/acta_nacimiento.png') }}" class="h-8 w-8" alt="Acta de nacimiento">
                    </button>
                    <span class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-100">ACTA DE NACIMIENTO</span>
                    @if ($actaNombre)
                      <flux:badge color="green">Entregado</flux:badge>
                    @else
                      <flux:badge color="red">Pendiente</flux:badge>
                    @endif
                  </div>

                  <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                       class="fixed inset-0 z-[10000] bg-black/45 backdrop-blur-sm flex items-center justify-center p-4"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                    <div class="relative w-full max-w-5xl h-[80vh] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-neutral-200 dark:ring-neutral-700">
                      <button type="button" @click="show=false" class="absolute top-3 right-3 p-2 rounded-full bg-white/80 dark:bg-neutral-700/60 hover:bg-white dark:hover:bg-neutral-700 shadow">✕</button>
                      @if ($actaUrl)
                        <iframe src="{{ $actaUrl }}" class="w-full h-full" title="Acta de nacimiento PDF"></iframe>
                      @else
                        <div class="w-full h-full flex items-center justify-center p-6"><p class="text-neutral-600 dark:text-neutral-300">No hay Acta de nacimiento.</p></div>
                      @endif
                    </div>
                  </div>
                </li>

                {{-- CERTIFICADO DE ESTUDIOS --}}
                @php
                  $certEstNombre = $selectedAlumno['certificado_estudios'] ?? null;
                  $certEstUrl    = $certEstNombre ? asset('storage/documentos/certificado_estudios/' . $certEstNombre) : null;
                @endphp
                <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                  <div class="flex items-center gap-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-700/40 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    <button type="button" @click="show=true" :disabled="{{ $certEstNombre ? 'false' : 'true' }}" class="disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $certEstNombre ? 'Ver certificado' : 'Sin archivo' }}">
                      <img src="{{ asset('storage/certificado_estudios.png') }}" class="h-8 w-8" alt="Certificado de estudios">
                    </button>
                    <span class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-100">CERTIFICADO DE ESTUDIOS</span>
                    @if ($certEstNombre)
                      <flux:badge color="green">Entregado</flux:badge>
                    @else
                      <flux:badge color="red">Pendiente</flux:badge>
                    @endif
                  </div>

                  <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                       class="fixed inset-0 z-[10000] bg-black/45 backdrop-blur-sm flex items-center justify-center p-4"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                    <div class="relative w-full max-w-5xl h-[80vh] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-neutral-200 dark:ring-neutral-700">
                      <button type="button" @click="show=false" class="absolute top-3 right-3 p-2 rounded-full bg-white/80 dark:bg-neutral-700/60 hover:bg-white dark:hover:bg-neutral-700 shadow">✕</button>
                      @if ($certEstUrl)
                        <iframe src="{{ $certEstUrl }}" class="w-full h-full" title="Certificado de estudios PDF"></iframe>
                      @else
                        <div class="w-full h-full flex items-center justify-center p-6"><p class="text-neutral-600 dark:text-neutral-300">No hay Certificado de estudios.</p></div>
                      @endif
                    </div>
                  </div>
                </li>

                {{-- COMPROBANTE DE DOMICILIO --}}
                @php
                  $compDomNombre = $selectedAlumno['comprobante_domicilio'] ?? null;
                  $compDomUrl    = $compDomNombre ? asset('storage/documentos/comprobante_domicilio/' . $compDomNombre) : null;
                @endphp
                <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                  <div class="flex items-center gap-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-700/40 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    <button type="button" @click="show=true" :disabled="{{ $compDomNombre ? 'false' : 'true' }}" class="disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $compDomNombre ? 'Ver comprobante' : 'Sin archivo' }}">
                      <img src="{{ asset('storage/comprobante_domicilio.png') }}" class="h-8 w-8" alt="Comprobante de domicilio">
                    </button>
                    <span class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-100">COMPROBANTE DE DOMICILIO</span>
                    @if ($compDomNombre)
                      <flux:badge color="green">Entregado</flux:badge>
                    @else
                      <flux:badge color="red">Pendiente</flux:badge>
                    @endif
                  </div>

                  <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                       class="fixed inset-0 z-[10000] bg-black/45 backdrop-blur-sm flex items-center justify-center p-4"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                    <div class="relative w-full max-w-5xl h-[80vh] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-neutral-200 dark:ring-neutral-700">
                      <button type="button" @click="show=false" class="absolute top-3 right-3 p-2 rounded-full bg-white/80 dark:bg-neutral-700/60 hover:bg-white dark:hover:bg-neutral-700 shadow">✕</button>
                      @if ($compDomUrl)
                        <iframe src="{{ $compDomUrl }}" class="w-full h-full" title="Comprobante de domicilio PDF"></iframe>
                      @else
                        <div class="w-full h-full flex items-center justify-center p-6"><p class="text-neutral-600 dark:text-neutral-300">No hay Comprobante de domicilio.</p></div>
                      @endif
                    </div>
                  </div>
                </li>

                {{-- CERTIFICADO MÉDICO --}}
                @php
                  $certMedNombre = $selectedAlumno['certificado_medico'] ?? null;
                  $certMedUrl    = $certMedNombre ? asset('storage/documentos/certificado_medico/' . $certMedNombre) : null;
                @endphp
                <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                  <div class="flex items-center gap-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-700/40 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    <button type="button" @click="show=true" :disabled="{{ $certMedNombre ? 'false' : 'true' }}" class="disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $certMedNombre ? 'Ver certificado médico' : 'Sin archivo' }}">
                      <img src="{{ asset('storage/certificado_medico.png') }}" class="h-8 w-8" alt="Certificado médico">
                    </button>
                    <span class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-100">CERTIFICADO MÉDICO</span>
                    @if ($certMedNombre)
                      <flux:badge color="green">Entregado</flux:badge>
                    @else
                      <flux:badge color="red">Pendiente</flux:badge>
                    @endif
                  </div>

                  <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                       class="fixed inset-0 z-[10000] bg-black/45 backdrop-blur-sm flex items-center justify-center p-4"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                    <div class="relative w-full max-w-5xl h-[80vh] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-neutral-200 dark:ring-neutral-700">
                      <button type="button" @click="show=false" class="absolute top-3 right-3 p-2 rounded-full bg-white/80 dark:bg-neutral-700/60 hover:bg-white dark:hover:bg-neutral-700 shadow">✕</button>
                      @if ($certMedUrl)
                        <iframe src="{{ $certMedUrl }}" class="w-full h-full" title="Certificado médico PDF"></iframe>
                      @else
                        <div class="w-full h-full flex items-center justify-center p-6"><p class="text-neutral-600 dark:text-neutral-300">No hay Certificado médico.</p></div>
                      @endif
                    </div>
                  </div>
                </li>

                {{-- INE --}}
                @php
                  $ineNombre = $selectedAlumno['ine'] ?? null;
                  $ineUrl    = $ineNombre ? asset('storage/documentos/ine/' . $ineNombre) : null;
                @endphp
                <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                  <div class="flex items-center gap-3 p-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-700/40 hover:bg-neutral-50 dark:hover:bg-neutral-700 transition">
                    <button type="button" @click="show=true" :disabled="{{ $ineNombre ? 'false' : 'true' }}" class="disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $ineNombre ? 'Ver INE' : 'Sin archivo' }}">
                      <img src="{{ asset('storage/fotos_infantiles.png') }}" class="h-8 w-8" alt="INE">
                    </button>
                    <span class="flex-1 text-sm font-medium text-neutral-800 dark:text-neutral-100">INE</span>
                    @if ($ineNombre)
                      <flux:badge color="green">Entregado</flux:badge>
                    @else
                      <flux:badge color="red">Pendiente</flux:badge>
                    @endif
                  </div>

                  <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                       class="fixed inset-0 z-[10000] bg-black/45 backdrop-blur-sm flex items-center justify-center p-4"
                       x-transition:enter="transition ease-out duration-200"
                       x-transition:enter-start="opacity-0"
                       x-transition:enter-end="opacity-100"
                       x-transition:leave="transition ease-in duration-150"
                       x-transition:leave-start="opacity-100"
                       x-transition:leave-end="opacity-0">
                    <div class="relative w-full max-w-5xl h-[80vh] bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl overflow-hidden ring-1 ring-neutral-200 dark:ring-neutral-700">
                      <button type="button" @click="show=false" class="absolute top-3 right-3 p-2 rounded-full bg-white/80 dark:bg-neutral-700/60 hover:bg-white dark:hover:bg-neutral-700 shadow">✕</button>
                      @if ($ineUrl)
                        <iframe src="{{ $ineUrl }}" class="w-full h-full" title="INE PDF"></iframe>
                      @else
                        <div class="w-full h-full flex items-center justify-center p-6"><p class="text-neutral-600 dark:text-neutral-300">No hay INE.</p></div>
                      @endif
                    </div>
                  </div>
                </li>

              </ul>
            </div>
          </flux:field>
        </div>
      </div>

    @else
      <!-- Estado vacío -->
      <div class="mt-4 rounded-xl border border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 px-4 py-3">
        <div class="flex items-center gap-3">
          <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-indigo-600 text-white">i</span>
          <p class="text-sm sm:text-base font-medium text-indigo-800 dark:text-indigo-200">Selecciona un alumno para ver sus detalles.</p>
        </div>
      </div>

      <!-- Placeholders de tarjetas -->
      <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @for($i=0;$i<3;$i++)
          <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-5 shadow-sm">
            <div class="h-5 w-48 rounded bg-neutral-200 dark:bg-neutral-700 mb-4"></div>
            <div class="space-y-3">
              @for($j=0;$j<8;$j++)
                <div class="h-9 rounded bg-neutral-100 dark:bg-neutral-700/60"></div>
              @endfor
            </div>
          </div>
        @endfor
      </div>
    @endif
  </div>

</div>
