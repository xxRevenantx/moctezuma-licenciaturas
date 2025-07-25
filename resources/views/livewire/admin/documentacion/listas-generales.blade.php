<div>
    <div class="flex flex-col gap-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white py-3">Listas Generales</h1>
    </div>

    <form wire:submit.prevent="consultarListas">
        <flux:select wire:model="licenciatura_id" class="w-full" label="Selecciona una licenciatura" >
            <flux:select.option value="">-- Selecciona una licenciatura --</flux:select.option>
            @foreach($licenciaturas as $licenciatura)
                <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:button class="my-4" variant="primary" type="submit">Consultar</flux:button>
    </form>



{{-- {{ $alumnos }} --}}

<div wire:loading.flex wire:target="consultarListas" class="justify-center items-center py-10">
    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
    <span class="text-blue-600 dark:text-blue-400"></span>
</div>

  @php
    $alumnosPorGeneracion = collect($alumnos)->sortBy(['generacion_id', 'cuatrimestre_id'])->groupBy('generacion_id');
@endphp


<div  wire:loading.remove wire:target="consultarListas">

    @if ($licenciatura_id)
    <div class="flex justify-between items-center p-4 text-sm text-gray-800 border border-gray-300 rounded-lg bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600" role="alert">


            <span class="font-weight uppercase">Licenciatura en {{ $licenciatura_nombre->nombre ?? '' }}</span>
            {{-- TOTAL DE ALUMNOS --}}
           <span style="font-size: 20px"> <b> Total: {{ count($alumnos) }} alumnos</b></span>


        </div>

         <div class="my-4 flex justify-end">
        <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Buscar..."/>
    </div>

@endif

    @if($alumnosPorGeneracion->count() > 0)
    @foreach($alumnosPorGeneracion as $generacionId => $grupoGeneracion)
        <div class="mt-8">

            <div class="bg-indigo-100 border-l-4 border-indigo-500 text-indigo-700 p-3 mb-2" role="alert">
                <h3 class="flex justify-between items-center text-2xl font-semibold text-gray-600 dark:text-gray-300 mb-1">
                   <span> Generación {{ $grupoGeneracion->first()->generacion->generacion ?? $generacionId }}</span>
                    <span style="font-size: 20px"><b>Total: {{ $grupoGeneracion->count() }} alumnos</b></span>

                                        <div x-data="{
                                            open: false,
                                            pdfUrl: '',
                                            licenciatura_id: '{{ $licenciatura_id }}',
                                            generacion_id: '{{ $generacionId }}',
                                            }"
                                            x-on:keydown.escape.window="open = false"
                                        >
                                            <x-button
                                            x-on:click="
                                                pdfUrl = '{{ route('admin.pdf.matricula-generacion') }}'
                                                + '?licenciatura_id=' + licenciatura_id
                                                + '&generacion_id=' + generacion_id;
                                                open = true;
                                            "
                                            variant="primary"
                                            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 mb-3"
                                            >
                                            <div class="flex items-center gap-1">
                                                <flux:icon.download />
                                                <span>Lista General por Generación</span>
                                            </div>
                                            </x-button>

                                            <div
                                            x-show="open"
                                            x-transition
                                            x-cloak
                                            class="fixed inset-0 z-50 bg-gray-200 bg-opacity-40 flex justify-center items-center"
                                            style="display: none;"
                                            >
                                            <div class="bg-white rounded p-4 w-full max-w-7xl shadow-lg relative">
                                                <iframe
                                                :src="pdfUrl"
                                                class="w-full h-[800px] rounded"
                                                ></iframe>
                                                <button x-on:click="open = false" class="absolute top-0 right-0 bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">
                                                Cerrar
                                                </button>
                                            </div>
                                            </div>
                                        </div>

                </h3>
                En esta lista se agrupan los estudiantes (foráneos, locales, semiescolarizados y escolarizados), creando una lista por generación.

            </div>

            @php
                $cuatrimestresGrupo = $grupoGeneracion->groupBy('cuatrimestre_id');
            @endphp

            @foreach($cuatrimestresGrupo as $cuatrimestreId => $alumnosCuatrimestre)
                <div class="mb-6">
                    <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-3 mb-2" role="alert">
                        <div class="flex justify-between items-center">
                            <p class="font-bold text-1xl">
                                {{ $alumnosCuatrimestre->first()->cuatrimestre->nombre ?? $cuatrimestreId }}° Cuatrimestre
                            </p>

                        </div>
                    </div>

                    @php
                        $porModalidad = $alumnosCuatrimestre->groupBy('modalidad_id');
                    @endphp

                    @foreach($porModalidad as $modalidadId => $alumnosModalidad)
                        <div class="mb-4">
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3" role="alert">
                                <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 ">
                                    <div class="flex items-center justify-between">
                                        <span>Modalidad: {{ $alumnosModalidad->first()->modalidad->nombre ?? $modalidadId }}</span>
                                        <span style="font-size: 18px"><b>Total: {{ $alumnosModalidad->count() }} alumnos</b></span>


                                        <div x-data="{
                                            open: false,
                                            pdfUrl: '',
                                            licenciatura_id: '{{ $licenciatura_id }}',
                                            filtrar_generacion: '{{ $generacionId }}',
                                            modalidad_id: '{{ $alumnosModalidad->first()->modalidad->id ?? $modalidadId }}',
                                            filtar_foraneo: '{{ $filtrar_foraneo }}',
                                            }"
                                            x-on:keydown.escape.window="open = false"
                                        >
                                            <x-button
                                            x-on:click="
                                                pdfUrl = '{{ route('admin.pdf.matricula') }}'
                                                + '?licenciatura_id=' + licenciatura_id
                                                + '&modalidad_id=' + modalidad_id
                                                + '&filtrar_generacion=' + filtrar_generacion
                                                + '&filtar_foraneo=' + filtar_foraneo;
                                                open = true;
                                            "
                                            variant="primary"
                                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-3"
                                            >
                                            <div class="flex items-center gap-1">
                                                <flux:icon.download />
                                                <span>Lista por modalidad</span>
                                            </div>
                                            </x-button>

                                            <div
                                            x-show="open"
                                            x-transition
                                            x-cloak
                                            class="fixed inset-0 z-50 bg-gray-200 bg-opacity-40 flex justify-center items-center"
                                            style="display: none;"
                                            >
                                            <div class="bg-white rounded p-4 w-full max-w-7xl shadow-lg relative">
                                                <iframe
                                                :src="pdfUrl"
                                                class="w-full h-[800px] rounded"
                                                ></iframe>
                                                <button x-on:click="open = false" class="absolute top-0 right-0 bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1">
                                                Cerrar
                                                </button>
                                            </div>
                                            </div>
                                        </div>




                                    </div>
                                </h4>
                                     En esta lista se agrupan los estudiantes (foráneos y locales), creando una lista por modalidad.
                            </div>

                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Apellido Paterno</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Apellido Materno</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Matrícula</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Generación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Modalidad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                                            LOCAL O FORANEO
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                    @foreach($alumnosModalidad as $index => $alumno)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $alumno->nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $alumno->apellido_paterno }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $alumno->apellido_materno }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $alumno->matricula }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $alumno->generacion->generacion ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $alumno->modalidad->nombre ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($alumno->foraneo == 'true')
                                                    <flux:badge color="red">Foráneo</flux:badge>
                                                @else
                                                    <flux:badge color="purple">Local</flux:badge>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <flux:button variant="primary" square @click="Livewire.dispatch('abrirEstudiante', { id: {{ $alumno->id }} })"
                                                    class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-yellow-600">
                                                    <flux:icon.pencil-square />
                                                </flux:button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                </div>
            @endforeach

        </div>
    @endforeach
@else
    @if ($licenciatura_id)
        <table class="min-w-full divide-y divide-gray-200 mt-6">
            <tbody>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-300">
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-2" role="alert">
                            @if (strlen(trim($search)) > 0)
                                <p class="font-bold text-1xl">
                                    NO SE ENCONTRÓ EL ALUMNO <b><i>"{{ $search }}"</i></b> EN ESTA LICENCIATURA
                                </p>
                            @else
                                <p class="font-bold text-1xl">
                                    NO HAY ALUMNOS EN ESTA LICENCIATURA
                                </p>
                            @endif
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    @endif
@endif


</div>
<livewire:admin.licenciaturas.submodulo.matricula-editar>
</div>
