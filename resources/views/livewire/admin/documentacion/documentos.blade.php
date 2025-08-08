<div class="relative" x-data="{ open: false }" x-cloak>
    <div class="flex flex-col gap-4">

            <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Documentación interna del alumno</h1>
     </div>


         <form action="{{ route('admin.pdf.documentacion.documento_personal') }}" method="GET" target="_blank" class="mb-4">
             <div class="grid  md:grid-cols-4 gap-4 " >
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

                <flux:select name="tipo_documento" required label="Tipo de documento" placeholder="Selecciona un tipo de documento" class="w-full">
                        <flux:select.option value="certificado-de-estudios">Certificado de estudios</flux:select.option>
                        <flux:select.option value="historial-academico">Historial Académico</flux:select.option>
                        <flux:select.option value="diploma">Diploma</flux:select.option>
                        <flux:select.option value="kardex">Kardex</flux:select.option>
                        <flux:select.option value="carta-de-pasante">Carta de pasante</flux:select.option>
                        <flux:select.option value="constancia-de-termino">Constancia de Término</flux:select.option>

                    </flux:select>

                <flux:input required
                    label="Fecha de expedición"
                    name="fecha_expedicion"
                    id="fecha"
                    type="date"
                    placeholder="Selecciona una fecha"
                    class="w-full"
                />
                <flux:button type="submit" class="mt-6" variant="primary">Descargar</flux:button>
                </div>
        </form>




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
    @endif


    <hr class="my-4">

    <div x-data="{ openAccordion: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Identidad(Documentación Personal)</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                        <livewire:admin.documentacion.identidad>

                </div>
     </div>
    <div x-data="{ openAccordion: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Credenciales</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                        <livewire:admin.documentacion.credenciales>

                </div>
     </div>

     <hr class="my-4">
    <div x-data="{ openAccordion: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Etiquetas</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                        <livewire:admin.documentacion.etiquetas>

                </div>
     </div>
     <hr class="my-4">
    <div x-data="{ openAccordion: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Justificantes</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                        <livewire:admin.documentacion.justificantes>

                </div>
     </div>

    <hr class="my-4">

            <div x-data="{ openAccordion: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Oficios de solicitud</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">
                    <!-- Aquí puedes agregar el contenido del acordeón -->
                    <p class="text-gray-700 dark:text-gray-200 my-3">Para la expedición de oficios, selecciona la generación, documento y fecha de expedición.</p>

                       <form action="{{ route('admin.pdf.documentacion.documento_oficios') }}" method="GET" target="_blank" class="mb-4">
                        <div class="grid md:grid-cols-4 gap-4">


                            <flux:select name="generacion" label="Selecciona la generación" class="w-full mb-4" required >
                                <flux:select.option value="">Selecciona una generación</flux:select.option>
                                @foreach($generaciones as $generacion)
                                    <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select name="tipo_documento" label="Selecciona el tipo de documento" class="w-full mb-4" required >
                                <flux:select.option value="">Selecciona un tipo de documento</flux:select.option>
                                <flux:select.option value="matriculas">Matrículas</flux:select.option>
                                <flux:select.option value="kardex">Kardex</flux:select.option>
                                <flux:select.option value="registro-boletos">Registro y boletas</flux:select.option>
                                <flux:select.option value="folios">Folios</flux:select.option>
                                <flux:select.option value="certificados">Certificados</flux:select.option>
                            </flux:select>

                            <flux:input required
                                label="Fecha de expedición"
                                name="fecha_expedicion"
                                id="fecha"
                                type="date"
                                placeholder="Selecciona una fecha"
                                class="w-full mb-4"
                              />

                            <flux:button type="submit" class="mt-6" variant="primary">Descargar</flux:button>
                        </div>
                    </form>



                </div>
            </div>

    <hr class="my-4">

            <div x-data="{ openAccordion: false }" x-cloak class="mb-4">
                <button
                    @click="openAccordion = !openAccordion"
                    class="w-full flex justify-between items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white font-semibold rounded focus:outline-none"
                >
                     <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Expedición de Registros de Escolaridad y Actas de Resultados</h1>
                    <svg :class="{'transform rotate-180': openAccordion}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="openAccordion" x-transition class="p-4 border border-t-0 rounded-b bg-white dark:bg-gray-800">

                    <p class="text-gray-700 dark:text-gray-200 my-3">Para la expedición de Registros de Escolaridad y Actas de Resultados, selecciona la licenciatura, generación y el tipo de documento que deseas descargar.</p>



                    <form action="{{ route('admin.pdf.documentacion.documento_expedicion') }}" method="GET" target="_blank" class="mb-4">
                        <div class="grid md:grid-cols-4 gap-4">

                            <flux:select name="licenciatura" label="Selecciona la licenciatura" class="w-full mb-4" required>
                                <flux:select.option value="">Selecciona una licenciatura</flux:select.option>
                                @foreach($licenciaturas as $licenciatura)
                                    <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
                                @endforeach
                            </flux:select>


                            <flux:select name="generacion" label="Selecciona la generación" class="w-full mb-4" required >
                                <flux:select.option value="">Selecciona una generación</flux:select.option>
                                @foreach($generaciones as $generacion)
                                    <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select name="documento" label="Selecciona el tipo de documento" class="w-full mb-4" required >
                                <flux:select.option value="">Selecciona un tipo de documento</flux:select.option>
                                <flux:select.option value="registro-escolaridad">Registro de Escolaridad</flux:select.option>
                                <flux:select.option value="acta-resultados">Acta de Resultados</flux:select.option>
                            </flux:select>

                            <flux:button label="Descargar" type="submit" variant="primary" class="w-full mt-6">Descargar</flux:button>
                        </div>
                    </form>




                    </div>




                </div>
            </div>

    </div>
