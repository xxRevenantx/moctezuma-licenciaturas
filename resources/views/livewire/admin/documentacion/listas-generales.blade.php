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


    <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
  <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>
  <span class="sr-only">Info</span>
  <div>
    <span class="font-medium">¡Importante!</span> En cada lista se agrupan los estudiantes (foráneos, escolarizada y semiescolarizada), creando una lista por generación.
  </div>
</div>

{{-- {{ $alumnos }} --}}

<div wire:loading.flex wire:target="consultarListas" class="justify-center items-center py-10">
    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
    </svg>
    <span class="text-blue-600 dark:text-blue-400"></span>
</div>




<div  wire:loading.remove wire:target="consultarListas">

    @if ($licenciatura_id)
    <div class="flex items-center p-4 text-sm text-gray-800 border border-gray-300 rounded-lg bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600" role="alert">

        <div>
            <span class="font-weight uppercase">Licenciatura en {{ $licenciatura_nombre->nombre ?? '' }}</span>
        </div>
        </div>

         <div class="my-4 flex justify-end">
        <flux:input icon="magnifying-glass" wire:model.live="search" placeholder="Buscar..."/>
    </div>

@endif




    @php
    $alumnosPorCuatrimestre = collect($alumnos)->sortBy('cuatrimestre_id')->groupBy('cuatrimestre_id');
@endphp


    @if($alumnosPorCuatrimestre->count() > 0)
        @foreach($alumnosPorCuatrimestre as $cuatrimestreId => $grupoAlumnos)
            <div class="mt-8">

                <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4" role="alert">
                <p class="font-bold"> {{ $grupoAlumnos->first()->cuatrimestre->nombre ?? $cuatrimestreId }}° Cuatrimestre </p>
                </div>
                @php
                    $porGeneracion = $grupoAlumnos->groupBy('generacion_id');
                @endphp
                @foreach($porGeneracion as $generacionId => $alumnosGeneracion)
                    <div class="mb-6">
                        <h3 class="text-md font-semibold text-gray-600 dark:text-gray-300 mb-1">
                            Generación {{ $alumnosGeneracion->first()->generacion->generacion ?? $generacionId }}
                        </h3>
                        @php
                            $porModalidad = $alumnosGeneracion->groupBy('modalidad_id');
                        @endphp
                        @foreach($porModalidad as $modalidadId => $alumnosModalidad)
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 mb-1">
                                    Modalidad: {{ $alumnosModalidad->first()->modalidad->nombre ?? $modalidadId }}
                                </h4>
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
        <table class="min-w-full divide-y divide-gray-200 mt-6">
            <tbody>
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-300">No hay alumnos para mostrar.</td>
                </tr>
            </tbody>
        </table>
    @endif
</div>
</div>
