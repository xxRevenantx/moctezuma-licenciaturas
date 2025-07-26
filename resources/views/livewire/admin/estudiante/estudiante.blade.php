<div>
     <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Búsqueda Estudiantes</h1>
    </div>


                <flux:input required
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


    @if (!empty($alumnos))
        <ul
            x-show="open"
            x-transition
            x-cloak
            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto"
            style="display: none"
        >
            @forelse ($alumnos as $index => $alumno)
                <li
                    class="p-2 cursor-pointer {{ $selectedIndex === $index ? 'bg-blue-200' : '' }}"
                    wire:click="selectAlumno({{ $index }})"
                    @mouseenter="open = true"
                >
                    <p class="font-bold text-indigo-600">
                        {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                    </p>
                    <p class="text-gray-700">
                        Matrícula: {{ $alumno['matricula'] ?? '' }} | CURP: {{ $alumno['CURP'] ?? '' }}
                    </p>
                </li>
            @empty
                <li class="p-2">No se encontraron alumnos.</li>
            @endforelse
        </ul>
    @endif

    <div wire:loading.flex wire:target="query" class="justify-center items-center py-10">
    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
    <span class="text-blue-600 dark:text-blue-400"></span>
</div>

<div wire:loading.remove wire:target="query" >

   @if ($selectedAlumno)

{{ $selectedAlumno["fecha_nacimiento"] ?? '---' }}
   <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 mt-5 justify-center dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
    {{-- DATOS GENERALES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Generales</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Nombre completo"
                value="{{ $selectedAlumno['apellido_paterno'] ?? '---' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}" />

            <flux:input readonly variant="filled" label="Matrícula" value="{{ $selectedAlumno['matricula'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="CURP" value="{{ $selectedAlumno['CURP'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Folio" value="{{ $selectedAlumno['folio'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Fecha de Nacimiento" value="{{ $selectedAlumno["fecha_nacimiento"] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Edad" value="{{ $edad ?? '---' }}" />
            <flux:input readonly variant="filled" label="Género" value="{{ $selectedAlumno['sexo'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Nacionalidad" value="{{ $selectedAlumno['pais'] ?? '---' }}" />
        </flux:field>
    </div>

    {{-- DATOS DE CONTACTO --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos de Contacto</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Calle" value="{{ $selectedAlumno['calle'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Número Exterior" value="{{ $selectedAlumno['num_ext'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Número Interior" value="{{ $selectedAlumno['num_int'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Colonia" value="{{ $selectedAlumno['colonia'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Código Postal" value="{{ $selectedAlumno['cp'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Municipio" value="{{ $selectedAlumno['municipio'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Ciudad/Localidad" value="{{ $selectedAlumno['ciudad'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Estado" value="{{ $selectedAlumno['estado'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Teléfono" value="{{ $selectedAlumno['telefono'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Celular" value="{{ $selectedAlumno['celular'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Tutor" value="{{ $selectedAlumno['tutor'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Correo electrónico" value="{{ $selectedAlumno['correo'] ?? '---' }}" />
        </flux:field>
    </div>

    {{-- DATOS ESCOLARES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Escolares</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Bachillerato Procedente" value="{{ $selectedAlumno['bachillerato'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Licenciatura" value="{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Generación" value="{{ $selectedAlumno['generacion']['generacion'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Cuatrimestre" value="{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Foráneo" value="{{ ($selectedAlumno['foraneo'] ?? "false") ? 'Sí' : 'No' }}" />
            <flux:input readonly variant="filled" label="Status" value="{{ ($selectedAlumno['activo'] ?? "false") ? 'Activo' : 'Inactivo' }}" />

            {{-- @php
                $docs = [];
                if (!empty($selectedAlumno['certificado'])) $docs[] = 'Certificado';
                if (!empty($selectedAlumno['acta'])) $docs[] = 'Acta de Nacimiento';
                if (!empty($selectedAlumno['medico'])) $docs[] = 'Certificado Médico';
                if (!empty($selectedAlumno['fotos'])) $docs[] = 'Fotos Infantiles';
                $documentosEntregados = implode(', ', $docs) ?: '---';
            @endphp
            <flux:input readonly variant="filled" label="Documentos entregados" value="{{ $documentosEntregados }}" /> --}}
        </flux:field>
    </div>
</div>

@else


         <div class="bg-indigo-100 border-l-4 border-indigo-500 text-indigo-700 p-3 my-4" role="alert">
                        <div class="flex justify-between items-center">
                            <p class="font-bold text-1xl">
                             Selecciona un alumno para ver sus detalles.
                            </p>

                        </div>
        </div>



         <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 mt-5 justify-center dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
    {{-- DATOS GENERALES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Generales</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Nombre completo"
                value="{{ $selectedAlumno['apellido_paterno'] ?? '---' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}" />

            <flux:input readonly variant="filled" label="Matrícula" value="{{ $selectedAlumno['matricula'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="CURP" value="{{ $selectedAlumno['CURP'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Folio" value="{{ $selectedAlumno['folio'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Fecha de Nacimiento" value="{{ $selectedAlumno["fecha_nacimiento"] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Edad" value="{{ $edad ?? '---' }}" />
            <flux:input readonly variant="filled" label="Género" value="{{ $selectedAlumno['sexo'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Nacionalidad" value="{{ $selectedAlumno['nacionalidad'] ?? '---' }}" />
        </flux:field>
    </div>

    {{-- DATOS DE CONTACTO --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos de Contacto</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Calle" value="{{ $selectedAlumno['calle'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Número Exterior" value="{{ $selectedAlumno['num_ext'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Número Interior" value="{{ $selectedAlumno['num_int'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Colonia" value="{{ $selectedAlumno['colonia'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Código Postal" value="{{ $selectedAlumno['cp'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Municipio" value="{{ $selectedAlumno['municipio'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Ciudad/Localidad" value="{{ $selectedAlumno['ciudad'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Estado" value="{{ $selectedAlumno['estado'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Teléfono" value="{{ $selectedAlumno['telefono'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Celular" value="{{ $selectedAlumno['celular'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Tutor" value="{{ $selectedAlumno['tutor'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Correo electrónico" value="{{ $selectedAlumno['correo'] ?? '---' }}" />
        </flux:field>
    </div>

    {{-- DATOS ESCOLARES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Escolares</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Bachillerato Procedente" value="{{ $selectedAlumno['bachillerato'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Licenciatura" value="{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Generación" value="{{ $selectedAlumno['generacion']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Cuatrimestre" value="{{ $selectedAlumno['cuatrimestre']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Foráneo" value="{{ ($selectedAlumno['foraneo'] ?? false) ? 'Sí' : 'No' }}" />
            <flux:input readonly variant="filled" label="Status" value="{{ ($selectedAlumno['activo'] ?? false) ? 'Activo' : 'Inactivo' }}" />

            @php
                $docs = [];
                if (!empty($selectedAlumno['certificado'])) $docs[] = 'Certificado';
                if (!empty($selectedAlumno['acta'])) $docs[] = 'Acta de Nacimiento';
                if (!empty($selectedAlumno['medico'])) $docs[] = 'Certificado Médico';
                if (!empty($selectedAlumno['fotos'])) $docs[] = 'Fotos Infantiles';
                $documentosEntregados = implode(', ', $docs) ?: '---';
            @endphp
            <flux:input readonly variant="filled" label="Documentos entregados" value="{{ $documentosEntregados }}" />
        </flux:field>
    </div>
</div>

@endif


</div>


</div>
