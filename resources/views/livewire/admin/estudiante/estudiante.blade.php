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

{{-- Contenedor Alpine para que x-cloak funcione --}}
<div x-data x-cloak class="relative">
    <!-- Select de búsqueda -->
    <x-searchable-select
        label="Estudiante"
        placeholder="--Selecciona al estudiante--"
        wire:model.live="query"   {{-- query ahora es el ID del alumno --}}
    >
        @foreach($alumnos as $index => $alumno)
            <x-searchable-select.option value="{{ $alumno['id'] }}">
                {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }} - {{ $alumno['matricula'] ?? '' }}
            </x-searchable-select.option>
        @endforeach
    </x-searchable-select>

    <!-- Spinner dentro del área del select mientras se carga el alumno -->
    <div
        class="pointer-events-none absolute right-3 top-[46px] sm:top-[42px] translate-y-[-50%]"
        wire:loading
        wire:target="query"
    >
        <span class="inline-block w-4 h-4 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
    </div>
</div>



  </div>

  <!-- Loader separado (por si quieres mantenerlo) -->
 <div wire:loading.flex wire:target="query" class="justify-center items-center py-8">
    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
  </div>

  <!-- Contenido del alumno -->

  <!-- Contenido del alumno -->
  <div class="mt-4" wire:loading.remove wire:target="query">
    @if ($selectedAlumno)

      @php
        $esEgresado = $this->isEgresado($selectedAlumno);
        $esBaja     = $this->isBaja($selectedAlumno);
        $local      = isset($selectedAlumno['foraneo']) && $selectedAlumno['foraneo'] === 'false';
      @endphp

      <!-- FICHA / HEADER DEL ALUMNO -->
      <div class="rounded-3xl border border-neutral-200 dark:border-neutral-800 bg-gradient-to-r from-indigo-600 via-violet-600 to-fuchsia-600 text-white shadow-lg shadow-indigo-500/30 px-4 py-4 sm:px-6 sm:py-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

          {{-- Izquierda: avatar + datos principales --}}
          <div class="flex items-center gap-4">
            @if (!empty($selectedAlumno['foto']))
              <img
                src="{{ asset('storage/estudiantes/' . $selectedAlumno['foto']) }}"
                alt="Foto del alumno"
                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover ring-2 ring-white/70 shadow-md"
              />
            @else
              <div
                class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-white/15 backdrop-blur flex items-center justify-center text-2xl font-semibold shadow-md">
                {{ mb_substr($selectedAlumno['nombre'] ?? 'A', 0, 1) }}
              </div>
            @endif

            <div>
              <p class="text-xs uppercase tracking-[0.16em] text-white/70 font-semibold">
                FICHA DEL ESTUDIANTE
              </p>
              <h2 class="mt-1 text-xl sm:text-2xl font-bold leading-tight">
                {{ $selectedAlumno['nombre'] ?? '---' }}
                {{ $selectedAlumno['apellido_paterno'] ?? '' }}
                {{ $selectedAlumno['apellido_materno'] ?? '' }}
              </h2>

              <div class="mt-1 flex flex-wrap items-center gap-3 text-xs sm:text-sm text-white/90">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/15 backdrop-blur">
                  <span class="font-mono text-[11px] sm:text-xs uppercase tracking-wide opacity-80">Matrícula</span>
                  <span class="font-semibold">{{ $selectedAlumno['matricula'] ?? '---' }}</span>
                </span>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/15 backdrop-blur">
                  <span class="font-mono text-[11px] sm:text-xs uppercase tracking-wide opacity-80">CURP</span>
                  <span class="font-semibold">{{ $selectedAlumno['CURP'] ?? '---' }}</span>
                </span>
              </div>
            </div>
          </div>

          {{-- Derecha: badges de estado + acciones --}}
          <div class="flex flex-col items-start md:items-end gap-3">

            <div class="flex flex-wrap gap-2 justify-start md:justify-end">
              {{-- Egresado / Activo --}}
              @if ($esEgresado)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100/90 text-amber-900 text-xs font-semibold shadow-sm">
                  <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                  Egresado · {{ $selectedAlumno['generacion']['generacion'] ?? '---' }}
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/90 text-emerald-900 text-xs font-semibold shadow-sm">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  Activo · {{ $selectedAlumno['generacion']['generacion'] ?? '---' }}
                </span>
              @endif

              {{-- Local / Foráneo --}}
              @if ($local)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-100/90 text-indigo-900 text-xs font-semibold shadow-sm">
                  <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                  Local
                </span>
              @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-orange-100/90 text-orange-900 text-xs font-semibold shadow-sm">
                  <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                  Foráneo
                </span>
              @endif

              {{-- Baja --}}
              @if ($esBaja)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100/90 text-red-900 text-xs font-semibold shadow-sm">
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                  Dado de baja
                </span>
              @endif
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <flux:button
                variant="ghost"
                size="sm"
                class="bg-white/15 hover:bg-white/25 text-white border border-white/30 px-3 py-2 rounded-xl"
                title="Editar estudiante"
                @click="Livewire.dispatch('abrirEstudiante', { id: {{ $selectedAlumno['id'] }} })"
              >
                <div class="flex items-center gap-1.5 text-xs sm:text-sm">
                  <flux:icon.pencil-square class="w-4 h-4" />
                  <span>Editar ficha</span>
                </div>
              </flux:button>

              <a target="_blank"
                 href="{{ route('admin.pdf.expediente', $selectedAlumno['id']) }}"
                 class="inline-flex items-center gap-1.5 rounded-xl bg-white text-indigo-700 hover:bg-slate-50 text-xs sm:text-sm font-semibold px-3 py-2 shadow-md shadow-black/20 transition"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <path d="M14 2v6h6"/>
                </svg>
                <span>Ver expediente</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      {{-- Línea de tiempo académica rápida --}}
      <div class="mt-3 rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50/80 dark:bg-neutral-900/60 px-4 py-3 text-xs sm:text-sm text-neutral-700 dark:text-neutral-200 flex flex-wrap gap-3 items-center">
        <div class="flex items-center gap-2">
          <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h8M4 18h4" />
            </svg>
          </span>
          <span class="font-semibold uppercase tracking-wide text-[11px] text-neutral-500 dark:text-neutral-400">Resumen académico</span>
        </div>

        <div class="flex flex-wrap gap-3 sm:gap-4 ml-0 sm:ml-4">
          <span class="inline-flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
            <span class="font-medium">Licenciatura:</span>
            <span>{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}</span>
          </span>
          <span class="inline-flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
            <span class="font-medium">Cuatrimestre:</span>
            <span>{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}°</span>
          </span>
          <span class="inline-flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
            <span class="font-medium">Modalidad:</span>
            <span>{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}</span>
          </span>
        </div>
      </div>

      <livewire:admin.licenciaturas.submodulo.matricula-editar />

      <!-- TARJETAS DE INFORMACIÓN -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

        {{-- DATOS GENERALES --}}
        <div class="group rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm hover:shadow-md hover:border-indigo-300/80 dark:hover:border-indigo-500/70 transition">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 20.25a8.25 8.25 0 0 1 15 0" />
                </svg>
              </span>
              <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                Datos generales
              </h3>
            </div>
          </div>

          <div class="h-px w-full bg-neutral-200 dark:bg-neutral-800 mb-4"></div>

          @if (!empty($selectedAlumno['foto']))
            <div class="mb-4 flex flex-col items-center">
              <img src="{{ asset('storage/estudiantes/' . $selectedAlumno['foto']) }}"
                   alt="Foto del alumno"
                   class="w-24 h-24 sm:w-28 sm:h-28 object-cover rounded-full ring-2 ring-neutral-200 dark:ring-neutral-700 shadow" />
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
        <div class="group rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm hover:shadow-md hover:border-indigo-300/80 dark:hover:border-indigo-500/70 transition">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75 12 12l9.75-5.25M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5z" />
                </svg>
              </span>
              <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                Datos de contacto
              </h3>
            </div>
          </div>

          <div class="h-px w-full bg-neutral-200 dark:bg-neutral-800 mb-4"></div>

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

        {{-- DATOS ESCOLARES + DOCUMENTOS --}}
        <div class="group rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm hover:shadow-md hover:border-indigo-300/80 dark:hover:border-indigo-500/70 transition">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5h15v15h-15z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h8M8 14h5" />
                </svg>
              </span>
              <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 uppercase tracking-wide">
                Datos escolares
              </h3>
            </div>
          </div>

          <div class="h-px w-full bg-neutral-200 dark:bg-neutral-800 mb-4"></div>

          <flux:field>
            <flux:input readonly variant="filled" label="Bachillerato Procedente" value="{{ $selectedAlumno['bachillerato'] ?? '---' }}" class="{{ empty($selectedAlumno['bachillerato']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Licenciatura" value="{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}" class="{{ empty($selectedAlumno['licenciatura']['nombre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Generación" value="{{ $selectedAlumno['generacion']['generacion'] ?? '---' }}" class="{{ empty($selectedAlumno['generacion']['generacion']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Cuatrimestre" value="{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}° CUATRIMESTRE" class="{{ empty($selectedAlumno['cuatrimestre']['cuatrimestre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Modalidad" value="{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}" class="{{ empty($selectedAlumno['modalidad']['nombre']) ? 'border-red-500' : '' }}" />

            {{-- Documentos entregados (tu misma lógica, solo embebida) --}}
            <div class="mt-4 w-full rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 p-4">
              <h4 class="mb-3 text-sm sm:text-base font-semibold text-neutral-900 dark:text-white flex items-center gap-2">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs">
                  📄
                </span>
                Documentos entregados
              </h4>

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
      <div class="mt-4 rounded-2xl border border-dashed border-indigo-300/80 dark:border-indigo-500/70 bg-indigo-50/70 dark:bg-indigo-900/20 px-4 py-4">
        <div class="flex items-start gap-3">
          <span class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-indigo-600 text-white shadow">
            i
          </span>
          <div>
            <p class="text-sm sm:text-base font-semibold text-indigo-900 dark:text-indigo-100">
              Aún no has seleccionado un alumno.
            </p>
            <p class="text-xs sm:text-sm text-indigo-900/80 dark:text-indigo-100/80 mt-1">
              Usa el buscador superior para localizar un estudiante por nombre, matrícula o CURP y ver su ficha detallada.
            </p>
          </div>
        </div>
      </div>

      <!-- Placeholders de tarjetas -->
      <div class="mt-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @for($i=0;$i<3;$i++)
          <div class="rounded-2xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 shadow-sm">
            <div class="h-5 w-40 rounded bg-neutral-200 dark:bg-neutral-800 mb-4"></div>
            <div class="space-y-3">
              @for($j=0;$j<8;$j++)
                <div class="h-9 rounded bg-neutral-100 dark:bg-neutral-800/60"></div>
              @endfor
            </div>
          </div>
        @endfor
      </div>
    @endif
  </div>


</div>
