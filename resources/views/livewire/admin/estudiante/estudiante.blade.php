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

   <div class="md:flex justify-between items-center my-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
            Detalles del Alumno(a): {{ $selectedAlumno['nombre'] ?? '---' }} {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} | Matrícula: {{ $selectedAlumno['matricula'] ?? '---' }} | CURP: {{ $selectedAlumno['CURP'] ?? '---' }}
        </h1>

    <div class="flex items-center mt-2 md:mt-0">
        <flux:button variant="primary" square @click="Livewire.dispatch('abrirEstudiante', { id: {{ $selectedAlumno['id'] }} })"
                        class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-yellow-600">
                        <flux:icon.pencil-square />
        </flux:button>

        <a target="_blank" href="{{ route('admin.pdf.expediente', $selectedAlumno['id']) }}"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow transition-colors duration-200 ml-2">
            Expediente
        </a>

    </div>




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
            <flux:input readonly variant="filled" label="Lugar de Nacimiento"
                value="{{ $selectedAlumno['ciudad_nacimiento']['nombre'] ?? '---' }}" />
            <flux:input readonly variant="filled" label="Estado de Nacimiento"
                value="{{ $selectedAlumno['estado_nacimiento']['nombre'] ?? '---' }}" />

        </flux:field>
    </div>



    {{-- DATOS DE CONTACTO --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos de Contacto</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input readonly variant="filled" label="Calle" value="{{ $selectedAlumno['calle'] ?? '---' }}"
                class="{{ empty($selectedAlumno['calle']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Número Exterior" value="{{ $selectedAlumno['numero_exterior'] ?? '---' }}"
                class="{{ empty($selectedAlumno['numero_exterior']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Número Interior" value="{{ $selectedAlumno['numero_interior'] ?? '---' }}"
                class="{{ empty($selectedAlumno['numero_interior']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Colonia" value="{{ $selectedAlumno['colonia'] ?? '---' }}"
                class="{{ empty($selectedAlumno['colonia']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Código Postal" value="{{ $selectedAlumno['cp'] ?? '---' }}"
                class="{{ empty($selectedAlumno['cp']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Municipio" value="{{ $selectedAlumno['municipio'] ?? '---' }}"
                class="{{ empty($selectedAlumno['municipio']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Ciudad/Localidad" value="{{ $selectedAlumno['ciudad']['nombre'] ?? '---' }}"
                class="{{ empty($selectedAlumno['ciudad']['nombre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Estado" value="{{ $selectedAlumno['estado']['nombre'] ?? '---' }}"
                class="{{ empty($selectedAlumno['estado']['nombre']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Teléfono" value="{{ empty($selectedAlumno['telefono']) ? '---' : $selectedAlumno['telefono'] }}"
                class="{{ empty($selectedAlumno['telefono']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Celular" value="{{ $selectedAlumno['celular'] ?? '---' }}"
                class="{{ empty($selectedAlumno['celular']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Tutor" value="{{ empty($selectedAlumno['tutor']) ? '---' : $selectedAlumno['tutor'] }}"
                class="{{ empty($selectedAlumno['tutor']) ? 'border-red-500' : '' }}" />
            <flux:input readonly variant="filled" label="Correo electrónico" value="{{ $selectedAlumno['user']['email'] ?? '---' }}"
                class="{{ empty($selectedAlumno['user']['email']) ? 'border-red-500' : '' }}" />
        </flux:field>
    </div>

    {{-- DATOS ESCOLARES --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
        <h1 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Escolares</h1>
        <hr class="mb-4">
        <flux:field>
            <flux:input
                readonly
                variant="filled"
                label="Bachillerato Procedente"
                value="{{ $selectedAlumno['bachillerato'] ?? '---' }}"
                class="{{ empty($selectedAlumno['bachillerato']) ? 'border-red-500' : '' }}"
            />
            <flux:input
                readonly
                variant="filled"
                label="Licenciatura"
                value="{{ $selectedAlumno['licenciatura']['nombre'] ?? '---' }}"
                class="{{ empty($selectedAlumno['licenciatura']['nombre']) ? 'border-red-500' : '' }}"
            />
            <flux:input
                readonly
                variant="filled"
                label="Generación"
                value="{{ $selectedAlumno['generacion']['generacion'] ?? '---' }}"
                class="{{ empty($selectedAlumno['generacion']['generacion']) ? 'border-red-500' : '' }}"
            />
            <flux:input
                readonly
                variant="filled"
                label="Cuatrimestre"
                value="{{ $selectedAlumno['cuatrimestre']['cuatrimestre'] ?? '---' }}° CUATRIMESTRE"
                class="{{ empty($selectedAlumno['cuatrimestre']['cuatrimestre']) ? 'border-red-500' : '' }}"
            />
            <flux:input
                readonly
                variant="filled"
                label="Modalidad"
                value="{{ $selectedAlumno['modalidad']['nombre'] ?? '---' }}"
                class="{{ empty($selectedAlumno['modalidad']['nombre']) ? 'border-red-500' : '' }}"
            />



                    <div class="w-full p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                    <h5 class="mb-3 text-base font-semibold text-gray-900 md:text-xl dark:text-white">
                    DOCUMENTOS ENTREGADOS
                    </h5>

                    <ul class="my-4 space-y-3">
                        {{-- CURP --}}
                            @php
                            $curpNombre = $selectedAlumno['CURP_documento'] ?? null;
                            $curpUrl    = $curpNombre ? asset('storage/documentos/curp/' . $curpNombre) : null;
                            @endphp
                            <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                            <div class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                <button type="button" @click="show=true" :disabled="{{ $curpNombre ? 'false' : 'true' }}" class="flex items-center disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $curpNombre ? 'Ver CURP' : 'Sin archivo' }}">
                                <img src="{{ asset('storage/curp.png') }}" class="h-8 w-8" alt="CURP">
                                </button>
                                <span class="flex-1 ms-3 whitespace-nowrap">CURP</span>
                                @if ($curpNombre)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-green-600 bg-green-200 rounded-sm dark:bg-green-700 dark:text-gray-100">Entregado</span>
                                @else
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-600 bg-red-200 rounded-sm dark:bg-red-700 dark:text-gray-100">Pendiente</span>
                                @endif
                            </div>

                            {{-- Modal --}}
                            <div x-cloak x-show="show" @keydown.escape.window="show = false" @click.self="show=false"
                                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <div class="relative bg-white w-full max-w-4xl h-[80vh] rounded-lg shadow-lg overflow-hidden">
                                <button type="button" @click="show=false" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl">&times;</button>
                                @if ($curpUrl)
                                    <iframe src="{{ $curpUrl }}" class="w-full h-full" title="CURP PDF"></iframe>
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6"><p class="text-gray-600">No hay archivo de CURP.</p></div>
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
                            <div class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                <button type="button" @click="show=true" :disabled="{{ $actaNombre ? 'false' : 'true' }}" class="flex items-center disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $actaNombre ? 'Ver Acta' : 'Sin archivo' }}">
                                <img src="{{ asset('storage/acta_nacimiento.png') }}" class="h-8 w-8" alt="Acta de nacimiento">
                                </button>
                                <span class="flex-1 ms-3 whitespace-nowrap">ACTA DE NACIMIENTO</span>
                                @if ($actaNombre)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-green-600 bg-green-200 rounded-sm dark:bg-green-700 dark:text-gray-100">Entregado</span>
                                @else
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-600 bg-red-200 rounded-sm dark:bg-red-700 dark:text-gray-100">Pendiente</span>
                                @endif
                            </div>

                            <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <div class="relative bg-white w-full max-w-4xl h-[80vh] rounded-lg shadow-lg overflow-hidden">
                                <button type="button" @click="show=false" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl">&times;</button>
                                @if ($actaUrl)
                                    <iframe src="{{ $actaUrl }}" class="w-full h-full" title="Acta de nacimiento PDF"></iframe>
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6"><p class="text-gray-600">No hay Acta de nacimiento.</p></div>
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
                            <div class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                <button type="button" @click="show=true" :disabled="{{ $certEstNombre ? 'false' : 'true' }}" class="flex items-center disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $certEstNombre ? 'Ver certificado' : 'Sin archivo' }}">
                                <img src="{{ asset('storage/certificado_estudios.png') }}" class="h-8 w-8" alt="Certificado de estudios">
                                </button>
                                <span class="flex-1 ms-3 whitespace-nowrap">CERTIFICADO DE ESTUDIOS</span>
                                @if ($certEstNombre)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-green-600 bg-green-200 rounded-sm dark:bg-green-700 dark:text-gray-100">Entregado</span>
                                @else
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-600 bg-red-200 rounded-sm dark:bg-red-700 dark:text-gray-100">Pendiente</span>
                                @endif
                            </div>

                            <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <div class="relative bg-white w-full max-w-4xl h-[80vh] rounded-lg shadow-lg overflow-hidden">
                                <button type="button" @click="show=false" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl">&times;</button>
                                @if ($certEstUrl)
                                    <iframe src="{{ $certEstUrl }}" class="w-full h-full" title="Certificado de estudios PDF"></iframe>
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6"><p class="text-gray-600">No hay Certificado de estudios.</p></div>
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
                            <div class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                <button type="button" @click="show=true" :disabled="{{ $compDomNombre ? 'false' : 'true' }}" class="flex items-center disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $compDomNombre ? 'Ver comprobante' : 'Sin archivo' }}">
                                <img src="{{ asset('storage/comprobante_domicilio.png') }}" class="h-8 w-8" alt="Comprobante de domicilio">
                                </button>
                                <span class="flex-1 ms-3 whitespace-nowrap">COMPROBANTE DE DOMICILIO</span>
                                @if ($compDomNombre)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-green-600 bg-green-200 rounded-sm dark:bg-green-700 dark:text-gray-100">Entregado</span>
                                @else
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-600 bg-red-200 rounded-sm dark:bg-red-700 dark:text-gray-100">Pendiente</span>
                                @endif
                            </div>

                            <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <div class="relative bg-white w-full max-w-4xl h-[80vh] rounded-lg shadow-lg overflow-hidden">
                                <button type="button" @click="show=false" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl">&times;</button>
                                @if ($compDomUrl)
                                    <iframe src="{{ $compDomUrl }}" class="w-full h-full" title="Comprobante de domicilio PDF"></iframe>
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6"><p class="text-gray-600">No hay Comprobante de domicilio.</p></div>
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
                            <div class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                <button type="button" @click="show=true" :disabled="{{ $certMedNombre ? 'false' : 'true' }}" class="flex items-center disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $certMedNombre ? 'Ver certificado médico' : 'Sin archivo' }}">
                                <img src="{{ asset('storage/certificado_medico.png') }}" class="h-8 w-8" alt="Certificado médico">
                                </button>
                                <span class="flex-1 ms-3 whitespace-nowrap">CERTIFICADO MÉDICO</span>
                                @if ($certMedNombre)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-green-600 bg-green-200 rounded-sm dark:bg-green-700 dark:text-gray-100">Entregado</span>
                                @else
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-600 bg-red-200 rounded-sm dark:bg-red-700 dark:text-gray-100">Pendiente</span>
                                @endif
                            </div>

                            <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <div class="relative bg-white w-full max-w-4xl h-[80vh] rounded-lg shadow-lg overflow-hidden">
                                <button type="button" @click="show=false" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl">&times;</button>
                                @if ($certMedUrl)
                                    <iframe src="{{ $certMedUrl }}" class="w-full h-full" title="Certificado médico PDF"></iframe>
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6"><p class="text-gray-600">No hay Certificado médico.</p></div>
                                @endif
                                </div>
                            </div>
                            </li>

                            {{-- INE (con modal también) --}}
                            @php
                            $ineNombre = $selectedAlumno['ine'] ?? null;
                            $ineUrl    = $ineNombre ? asset('storage/documentos/ine/' . $ineNombre) : null;
                            @endphp
                            <li x-data="{show:false}" x-effect="document.body.style.overflow = show ? 'hidden':'auto'">
                            <div class="flex items-center p-3 text-base font-bold text-gray-900 rounded-lg bg-gray-50 hover:bg-gray-100 group hover:shadow dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-white">
                                <button type="button" @click="show=true" :disabled="{{ $ineNombre ? 'false' : 'true' }}" class="flex items-center disabled:opacity-50 disabled:cursor-not-allowed" title="{{ $ineNombre ? 'Ver INE' : 'Sin archivo' }}">
                                <img src="{{ asset('storage/fotos_infantiles.png') }}" class="h-8 w-8" alt="INE">
                                </button>
                                <span class="flex-1 ms-3 whitespace-nowrap">INE</span>
                                @if ($ineNombre)
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-green-600 bg-green-200 rounded-sm dark:bg-green-700 dark:text-gray-100">Entregado</span>
                                @else
                                <span class="inline-flex items-center justify-center px-2 py-0.5 ms-3 text-xs font-medium text-red-600 bg-red-200 rounded-sm dark:bg-red-700 dark:text-gray-100">Pendiente</span>
                                @endif
                            </div>

                            <div x-cloak x-show="show" @keydown.escape.window="show=false" @click.self="show=false"
                                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95">
                                <div class="relative bg-white w-full max-w-4xl h-[80vh] rounded-lg shadow-lg overflow-hidden">
                                <button type="button" @click="show=false" class="absolute top-2 right-2 text-gray-600 hover:text-red-600 text-2xl">&times;</button>
                                @if ($ineUrl)
                                    <iframe src="{{ $ineUrl }}" class="w-full h-full" title="INE PDF"></iframe>
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6"><p class="text-gray-600">No hay INE.</p></div>
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


            @php
                $docs = [];
                if (!empty($selectedAlumno['certificado_estudios'])) $docs[] = 'Certificado de Estudios';
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
