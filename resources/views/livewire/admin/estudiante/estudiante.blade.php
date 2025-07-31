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

   <div class="flex justify-between items-center my-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            Detalles del Alumno(a): {{ $selectedAlumno['nombre'] ?? '---' }} {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} | Matrícula: {{ $selectedAlumno['matricula'] ?? '---' }} | CURP: {{ $selectedAlumno['CURP'] ?? '---' }}
        </h1>

    <flux:button variant="primary" square @click="Livewire.dispatch('abrirEstudiante', { id: {{ $selectedAlumno['id'] }} })"
                    class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-yellow-600">
                    <flux:icon.pencil-square />
    </flux:button>

    <a target="_blank" href="{{ route('admin.pdf.expediente', $selectedAlumno['id']) }}"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors duration-200 ml-2">
         Expediente
    </a>



  <livewire:admin.licenciaturas.submodulo.matricula-editar>
   </div>

       @if ($selectedAlumno)

                {{-- EGRESADO --}}
                @if ($this->isEgresado($selectedAlumno))
                    <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-3 my-4" role="alert">
                        <div class="flex justify-start gap-3 items-center">
                            <p class="font-bold text-1xl">
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

                {{-- ACTIVO --}}
                @else
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 my-4" role="alert">
                        <div class="flex justify-start gap-3 items-center">
                            <p class="font-bold text-1xl">
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







   <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 mt-5 justify-center dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
    {{-- DATOS GENERALES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Generales</h1>
        <hr class="mb-4">

                    @if (!empty($selectedAlumno['foto']))
                <div class="mt-4 flex flex-col items-center">
                    <img src="{{ asset('storage/estudiantes/' . $selectedAlumno['foto']) }}" alt="Foto del alumno" class="w-32 h-32 object-cover rounded-full border border-gray-300 shadow" />
                    <span class="text-sm text-gray-500 mt-2">Foto del estudiante</span>
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
                    try {
                        $dt = \Carbon\Carbon::parse($fechaNacimiento);
                        $fechaFormateada = $dt->format('d/m/Y');
                    } catch (\Exception $e) {
                        $fechaFormateada = $fechaNacimiento;
                    }
                }
            @endphp
            <flux:input readonly variant="filled" label="Fecha de Nacimiento" value="{{ $fechaFormateada }}" />
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
            <flux:input readonly variant="filled" label="Correo electrónico" value="{{ $selectedAlumno['user']['email'] ?? '---' }}" />
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
            <flux:input readonly variant="filled" label="Cuatrimestre" value="{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}° CUATRIMESTRE" />
            <flux:input readonly variant="filled" label="Modalidad" value="{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Foráneo" value="{{ $selectedAlumno['foraneo'] === 'false' ? 'No' : 'Sí' }}" />

            {{-- @php
                $docs = [];
                if (!empty($selectedAlumno['certificado'])) $docs[] = 'Certificado';
                if (!empty($selectedAlumno['acta'])) $docs[] = 'Acta de Nacimiento';
                if (!empty($selectedAlumno['medico'])) $docs[] = 'Certificado Médico';
                if (!empty($selectedAlumno['fotos'])) $docs[] = 'Fotos Infantiles';
                $documentosEntregados = implode(', ', $docs) ?: '---';
            @endphp
            <flux:input readonly variant="filled" label="Documentos entregados" value="{{ $documentosEntregados }}" /> --}}

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
            <flux:input readonly variant="filled" label="Nombre completo" value="---" />
            <flux:input readonly variant="filled" label="Matrícula" value="---" />
            <flux:input readonly variant="filled" label="CURP" value="---" />
            <flux:input readonly variant="filled" label="Folio" value="---" />
            <flux:input readonly variant="filled" label="Fecha de Nacimiento" value="---" />
            <flux:input readonly variant="filled" label="Edad" value="---" />
            <flux:input readonly variant="filled" label="Género" value="---" />
            <flux:input readonly variant="filled" label="Nacionalidad" value="---" />
        </flux:field>
    </div>

    {{-- DATOS DE CONTACTO --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos de Contacto</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Calle" value="---" />
            <flux:input readonly variant="filled" label="Número Exterior" value="---" />
            <flux:input readonly variant="filled" label="Número Interior" value="---" />
            <flux:input readonly variant="filled" label="Colonia" value="---" />
            <flux:input readonly variant="filled" label="Código Postal" value="---" />
            <flux:input readonly variant="filled" label="Municipio" value="---" />
            <flux:input readonly variant="filled" label="Ciudad/Localidad" value="---" />
            <flux:input readonly variant="filled" label="Estado" value="---" />
            <flux:input readonly variant="filled" label="Teléfono" value="---" />
            <flux:input readonly variant="filled" label="Celular" value="---" />
            <flux:input readonly variant="filled" label="Tutor" value="---" />
            <flux:input readonly variant="filled" label="Correo electrónico" value="---" />
        </flux:field>
    </div>

    {{-- DATOS ESCOLARES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Escolares</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Bachillerato Procedente" value="---" />
            <flux:input readonly variant="filled" label="Licenciatura" value="---" />
            <flux:input readonly variant="filled" label="Generación" value="---" />
            <flux:input readonly variant="filled" label="Cuatrimestre" value="---" />
            <flux:input readonly variant="filled" label="Modalidad" value="---" />
            <flux:input readonly variant="filled" label="Foráneo" value="---" />

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
