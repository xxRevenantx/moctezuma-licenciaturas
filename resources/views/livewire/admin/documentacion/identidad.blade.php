<div>
    <h1 class="text-2xl font-bold mb-4">Subir Documentos de Identidad</h1>

    {{-- Input de búsqueda --}}
    <div class="grid md:grid-cols-1 gap-4">
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
    </div>

    {{-- Resultados de búsqueda --}}
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

    {{-- Info del alumno seleccionado --}}
    @if ($selectedAlumno)
        <div class="mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:text-white">
            <p class="font-bold">
                {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
            </p>
            <p>Matrícula: {{ $selectedAlumno['matricula'] ?? '' }}</p>
            <p>CURP: {{ $selectedAlumno['CURP'] ?? '' }}</p>
            <p>Folio: {{ $selectedAlumno["folio"] ?? '----' }}</p>
            <p>Licenciatura: {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</p>
        </div>

        <div x-data="{ showModal: false, fileUrl: '{{ route('admin.alumnos.documentos.unificar', $selectedAlumno['id']) }}' }" class="mt-6">



        {{-- Carga de documentos --}}
        <div wire:loading.delay wire:target="selectAlumno" class="flex justify-center items-center mt-6 mb-6">

            <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="ml-2 text-indigo-600 font-semibold">Cargando documentos...</span>
                    </div>


                    <a
                        target="_blank"
                        href="{{ $tieneDocumentos ? route('admin.alumnos.documentos.unificar', ['id' => $selectedAlumno['id']]) : '#' }}"
                        class="inline-flex items-center justify-center p-5 text-base font-medium rounded-lg
                            {{ $tieneDocumentos
                                ? 'text-gray-500 bg-gray-200 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:bg-gray-800 dark:hover:bg-gray-700 dark:hover:text-white'
                                : 'opacity-50 cursor-not-allowed bg-gray-200 text-gray-400' }}"
                        @disabled(!$tieneDocumentos)
                    >
                        <svg aria-hidden="true" class="w-5 h-5 me-3" viewBox="0 0 22 31" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_4151_63004)"><path d="M5.50085 30.1242C8.53625 30.1242 10.9998 27.8749 10.9998 25.1035V20.0828H5.50085C2.46546 20.0828 0.00195312 22.332 0.00195312 25.1035C0.00195312 27.8749 2.46546 30.1242 5.50085 30.1242Z" fill="#0ACF83"/><path d="M0.00195312 15.062C0.00195312 12.2905 2.46546 10.0413 5.50085 10.0413H10.9998V20.0827H5.50085C2.46546 20.0827 0.00195312 17.8334 0.00195312 15.062Z" fill="#A259FF"/><path d="M0.00195312 5.02048C0.00195312 2.24904 2.46546 -0.000244141 5.50085 -0.000244141H10.9998V10.0412H5.50085C2.46546 10.0412 0.00195312 7.79193 0.00195312 5.02048Z" fill="#F24E1E"/><path d="M11 -0.000244141H16.4989C19.5343 -0.000244141 21.9978 2.24904 21.9978 5.02048C21.9978 7.79193 19.5343 10.0412 16.4989 10.0412H11V-0.000244141Z" fill="#FF7262"/><path d="M21.9978 15.062C21.9978 17.8334 19.5343 20.0827 16.4989 20.0827C13.4635 20.0827 11 17.8334 11 15.062C11 12.2905 13.4635 10.0413 16.4989 10.0413C19.5343 10.0413 21.9978 12.2905 21.9978 15.062Z" fill="#1ABCFE"/></g><defs><clipPath id="clip0_4151_63004"><rect width="22" height="30.1244" fill="white" transform="translate(0 -0.000244141)"/></clipPath></defs></svg>
                        <span class="w-full">Descargar documentos unificados</span>
                        <svg class="w-4 h-4 ms-2 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9"/>
                        </svg>
                      </a>







        <div wire:loading.remove wire:target="selectAlumno" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">


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
                label="CERTIFICADO DE ESTUDIOS"
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
                :inscripcion-id="$selectedAlumno['id']"
                rutaGuardado="documentos/certificado_medico"
            />

            <livewire:admin.documentacion.carga-documentos
                :key="$selectedAlumno['id'] . '_ine'"
                label="INE"
                wireId="ine"
                rutaGuardado="documentos/ine"
                :inscripcion-id="$selectedAlumno['id']"
            />
        </div>
    @endif
</div>
