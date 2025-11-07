<div
    x-data="{ open: false }"
    class="space-y-5"
>
   <!-- Filtros -->
<div class="flex flex-col sm:flex-row gap-4 items-end">
  <div class="flex flex-col sm:flex-row gap-4 flex-1">
    <!-- Licenciatura -->
    <div class="flex-1">
      <flux:select
        label="Licenciatura"
        placeholder="Seleccionar licenciatura"
        wire:model.live="selectedLicenciatura"
        class="w-full"
      >
        <option value="">Todas las licenciaturas</option>
        @foreach ($licenciaturas as $lic)
          <option value="{{ $lic['id'] }}">{{ $lic['nombre'] }}</option>
        @endforeach
      </flux:select>
    </div>

    <!-- Generación (dependiente) -->
    <!-- Generación (dependiente) -->
        <div class="flex-1">
        <flux:select
            label="Generación"
            placeholder="{{ $generaciones ? 'Seleccionar generación' : 'Seleccione una licenciatura primero' }}"
            wire:model.live="selectedGeneracion"
            class="w-full"

        >
            <option value="">Todas las generaciones</option>
            @foreach ($generaciones as $gen)
            <option value="{{ $gen['id'] }}">{{ $gen['generacion'] }}</option>
            @endforeach
        </flux:select>
        </div>

  </div>

  <!-- Botón PDF (usa filtros actuales) -->
  <div class="flex-shrink-0">
    <a
      href="{{ route('admin.pdf.documentacion.alumnos.documentacion', [
            'licenciatura' => $selectedLicenciatura ?: 0,
            'generacion'   => $selectedGeneracion ?: 0,
        ]) }}"
      target="_blank"
      class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition bg-indigo-100 hover:bg-indigo-200 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/30 ring-1 ring-indigo-200 dark:ring-indigo-800"
    >
      <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
      </svg>
      <span>Ver alumnos que tienen documentación</span>
    </a>
  </div>
</div>


    <!-- Header -->
    <div class="flex flex-col gap-1">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
            Subir Documentos de Identidad
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Busca al alumno, verifica su información y carga sus documentos oficiales.
        </p>
    </div>

    <!-- Card contenedor -->
    <div class="relative  rounded-2xl border border-gray-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow">
        <!-- Top Accent -->
        <div class="h-1 w-full bg-gradient-to-r from-indigo-600 via-violet-500 to-fuchsia-500"></div>

        <!-- Search -->
        <div class="p-4 sm:p-6">
            <div class="relative max-w-3xl">
                <label for="buscar-alumno" class="sr-only">Buscar alumno</label>
                <flux:input
                    id="buscar-alumno"
                    required
                    label="Buscar alumno"
                    wire:model.live.debounce.500ms="query"
                    name="alumno_id"
                    type="text"
                    placeholder="Buscar alumno por nombre, matrícula o CURP…"
                    autocomplete="off"
                    @focus="open = true"
                    @input="open = true"
                    @blur="setTimeout(() => open = false, 150)"
                    wire:keydown.arrow-down="selectIndexDown"
                    wire:keydown.arrow-up="selectIndexUp"
                    wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
                    class="w-full"
                />

                <!-- Resultados -->
                @if (!empty($alumnos))
                    <ul
                        x-show="open"
                        x-transition
                        x-cloak
                        role="listbox"
                        class="absolute z-20 mt-1 w-full max-h-60 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-lg"
                        style="display: none"
                    >
                        @forelse ($alumnos as $index => $alumno)
                            <li
                                class="p-2 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-800 {{ $selectedIndex === $index ? 'bg-indigo-50 dark:bg-neutral-800' : '' }}"
                                wire:click="selectAlumno({{ $index }})"
                                @mouseenter="open = true"
                                role="option"
                            >
                                <p class="font-semibold text-indigo-700 dark:text-indigo-300 truncate">
                                    {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                                </p>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                    Matrícula: <span class="font-mono">{{ $alumno['matricula'] ?? '' }}</span> · CURP: <span class="font-mono">{{ $alumno['CURP'] ?? '' }}</span>
                                </p>
                            </li>
                        @empty
                            <li class="p-3 text-sm text-neutral-600 dark:text-neutral-300">No se encontraron alumnos.</li>
                        @endforelse
                    </ul>
                @endif
            </div>
        </div>

        <!-- Loader al cambiar alumno -->
        <div
            wire:loading.delay
            wire:target="selectAlumno"
            class="px-4 sm:px-6 pb-4"
        >
            <div class="flex items-center gap-2 rounded-xl border border-dashed border-gray-300 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/50 px-4 py-3">
                <svg class="h-5 w-5 animate-spin text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span class="text-sm text-indigo-700 dark:text-indigo-300 font-medium">Cargando documentos…</span>
            </div>
        </div>

        <!-- Info alumno + Acciones -->
        @if ($selectedAlumno)
            <div class="px-4 sm:px-6 pb-6 space-y-4">
                <!-- Tarjeta alumno -->
                <div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900/60 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        <p class="font-semibold text-neutral-900 dark:text-white">
                            {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
                        </p>
                        <p class="text-neutral-700 dark:text-neutral-300">
                            Matrícula: <span class="font-mono">{{ $selectedAlumno['matricula'] ?? '' }}</span>
                        </p>
                        <p class="text-neutral-700 dark:text-neutral-300">
                            CURP: <span class="font-mono">{{ $selectedAlumno['CURP'] ?? '' }}</span>
                        </p>
                        <p class="text-neutral-700 dark:text-neutral-300">
                            Folio: {{ $selectedAlumno['folio'] ?? '----' }}
                        </p>
                        <p class="sm:col-span-2 text-neutral-700 dark:text-neutral-300">
                            Licenciatura: {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}
                        </p>
                    </div>
                </div>

                <!-- Botón descargar documentos unificados -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-sm text-neutral-600 dark:text-neutral-400">
                        Descarga en un solo PDF los documentos ya cargados del alumno.
                    </div>
                    <a
                        target="_blank"
                        href="{{ $tieneDocumentos ? route('admin.alumnos.documentos.unificar', ['id' => $selectedAlumno['id']]) : '#' }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium transition
                               {{ $tieneDocumentos
                                   ? 'bg-neutral-100 hover:bg-neutral-200 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200 dark:hover:bg-neutral-700 ring-1 ring-neutral-200 dark:ring-neutral-700'
                                   : 'cursor-not-allowed bg-neutral-100 text-neutral-400 dark:bg-neutral-800 dark:text-neutral-500 ring-1 ring-neutral-200 dark:ring-neutral-800' }}"
                        @disabled(!$tieneDocumentos)
                    >
                        <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 22 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_4151_63004)">
                                <path d="M5.50085 30.1242C8.53625 30.1242 10.9998 27.8749 10.9998 25.1035V20.0828H5.50085C2.46546 20.0828 0.00195312 22.332 0.00195312 25.1035C0.00195312 27.8749 2.46546 30.1242 5.50085 30.1242Z" fill="#0ACF83"/>
                                <path d="M0.00195312 15.062C0.00195312 12.2905 2.46546 10.0413 5.50085 10.0413H10.9998V20.0827H5.50085C2.46546 20.0827 0.00195312 17.8334 0.00195312 15.062Z" fill="#A259FF"/>
                                <path d="M0.00195312 5.02048C0.00195312 2.24904 2.46546 -0.000244141 5.50085 -0.000244141H10.9998V10.0412H5.50085C2.46546 10.0412 0.00195312 7.79193 0.00195312 5.02048Z" fill="#F24E1E"/>
                                <path d="M11 -0.000244141H16.4989C19.5343 -0.000244141 21.9978 2.24904 21.9978 5.02048C21.9978 7.79193 19.5343 10.0412 16.4989 10.0412H11V-0.000244141Z" fill="#FF7262"/>
                                <path d="M21.9978 15.062C21.9978 17.8334 19.5343 20.0827 16.4989 20.0827C13.4635 20.0827 11 17.8334 11 15.062C11 12.2905 13.4635 10.0413 16.4989 10.0413C19.5343 10.0413 21.9978 12.2905 21.9978 15.062Z" fill="#1ABCFE"/>
                            </g>
                            <defs><clipPath id="clip0_4151_63004"><rect width="22" height="30.1244" fill="white" transform="translate(0 -0.000244141)"/></clipPath></defs>
                        </svg>
                        <span>Descargar documentos unificados</span>
                        <svg class="h-4 w-4 rtl:rotate-180" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                        </svg>
                    </a>
                </div>

                <!-- Grid de carga de documentos -->
                <div class="pt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <livewire:admin.documentacion.carga-documentos
                        :key="$selectedAlumno['id'] . '_curp'"
                        label="CURP"
                        wireId="CURP_documento"
                        rutaGuardado="documentos/curp"
                        :inscripcion-id="$selectedAlumno['id']"
                    />

                    <livewire:admin.documentacion.carga-documentos
                        :key="$selectedAlumno['id'] . '_acta_nacimiento'"
                        label="Acta de Nacimiento"
                        wireId="acta_nacimiento"
                        rutaGuardado="documentos/actas"
                        :inscripcion-id="$selectedAlumno['id']"
                    />

                    <livewire:admin.documentacion.carga-documentos
                        :key="$selectedAlumno['id'] . '_certificado_estudios'"
                        label="Certificado de Estudios"
                        wireId="certificado_estudios"
                        rutaGuardado="documentos/certificado_estudios"
                        :inscripcion-id="$selectedAlumno['id']"
                    />

                    <livewire:admin.documentacion.carga-documentos
                        :key="$selectedAlumno['id'] . '_comprobante_domicilio'"
                        label="Comprobante de Domicilio"
                        wireId="comprobante_domicilio"
                        rutaGuardado="documentos/comprobante_domicilio"
                        :inscripcion-id="$selectedAlumno['id']"
                    />

                    <livewire:admin.documentacion.carga-documentos
                        :key="$selectedAlumno['id'] . '_certificado_medico'"
                        label="Certificado Médico"
                        wireId="certificado_medico"
                        rutaGuardado="documentos/certificado_medico"
                        :inscripcion-id="$selectedAlumno['id']"
                    />

                    <livewire:admin.documentacion.carga-documentos
                        :key="$selectedAlumno['id'] . '_ine'"
                        label="INE"
                        wireId="ine"
                        rutaGuardado="documentos/ine"
                        :inscripcion-id="$selectedAlumno['id']"
                    />
                </div>
            </div>
        @endif
    </div>
</div>
