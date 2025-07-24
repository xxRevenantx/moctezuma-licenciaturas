<div x-data="{ open: false }">

    @php
    function esColorDark($hexColor) {
        $hexColor = ltrim($hexColor, '#');
        if (strlen($hexColor) == 3) {
            $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
        }
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        $luminancia = (0.299 * $r + 0.587 * $g + 0.114 * $b);
        return $luminancia < 128;
    }
@endphp


   <div>
    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Horario Escolarizada</h2>



            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Licenciatura</label>
                <flux:select wire:model.live="filtroLicenciatura" class="w-full border-gray-300 rounded shadow-sm">
                    <flux:select.option>-- Selecciona --</flux:select.option>
                    @foreach($licenciaturasDisponibles as $lic)
                        <flux:select.option value="{{ $lic->id }}">{{ $lic->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>



             <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Generación</label>
                <flux:select wire:model.live="filtroGeneracion" class="w-full border-gray-300 rounded shadow-sm">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($generacionesDisponibles as $generacion)
                        <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Cuatrimestre</label>
                <flux:select wire:model.live="filtroCuatrimestre" class="w-full border-gray-300 rounded shadow-sm">
                    <flux:select.option value="">-- Selecciona --</flux:select.option>
                    @foreach($cuatrimestresDisponibles as $cuatrimestre)
                        <flux:select.option value="{{ $cuatrimestre }}">{{ $cuatrimestre }}°</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>



        <!-- Loader: se muestra cuando está cargando Livewire -->
    <div wire:loading.flex class="justify-center items-center py-10">
        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <span class="text-blue-600 dark:text-blue-400"></span>
    </div>



    @if($horarios->isEmpty())
       <flux:input
            label="Buscar por profesor o materia"
            wire:model.live="busqueda"
            placeholder="Ej. Matemáticas o Juan Pérez"
            class="w-full my-3"
        />
        <div class="text-center text-gray-500 mt-10">No hay horarios que coincidan con los filtros seleccionados.</div>
    @else

         <flux:input
            label="Buscar por profesor o materia"
            wire:model.live="busqueda"
            placeholder="Ej. Matemáticas o Juan Pérez"
            class="w-full my-3"
        />


                            <div x-data="{
                                                open: false,
                                                pdfUrl: '',
                                                licenciatura_id: '{{ $filtroLicenciatura }}',
                                                modalidad_id: '{{ $modalidadId }}',
                                                filtrar_generacion: '{{ $filtroGeneracion }}',
                                                filtrar_cuatrimestre: '{{ $filtroCuatrimestre }}'
                                            }">

                                            <x-button
                                                x-on:click="
                                                    pdfUrl = '{{ route('admin.pdf.horario-escolarizada') }}'
                                                        + '?licenciatura_id=' + licenciatura_id
                                                        + '&modalidad_id=' + modalidad_id
                                                        + '&filtrar_generacion=' + filtrar_generacion
                                                        + '&filtrar_cuatrimestre=' + filtrar_cuatrimestre;
                                                    open = true;
                                                "
                                                variant="primary"
                                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mb-3"
                                            >
                                                <div class="flex items-center gap-1">
                                                    <flux:icon.download />
                                                    <span>Horario PDF</span>
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



        <div wire:loading.remove>
    <table class="table-auto w-full border border-collapse text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-2 py-1">Hora</th>
                @foreach($columnasUnicas as $col)
                    <th class="border px-2 py-1">{{ $col['etiqueta'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($horasUnicas as $hora)
                <tr>
                    <td class="border px-2 py-1 font-bold">{{ $hora }}</td>
                    @foreach ($columnasUnicas as $col)
                        @php
                            $item = $horarios->first(fn ($h) =>
                                $h->hora === $hora &&
                                $h->dia_id === $col['dia_id']
                            );

                            $materia = optional(optional($item)->asignacionMateria)->materia?->nombre;
                            $profesorObj = optional(optional($item)->asignacionMateria)->profesor;
                            $profesor = $profesorObj
                                ? trim($profesorObj->nombre . ' ' . $profesorObj->apellido_paterno . ' ' . $profesorObj->apellido_materno)
                                : null;
                            $color = $profesorObj?->color ?? '#ffffff';
                            $textColor = esColorDark($color) ? 'white' : 'black';
                        @endphp
                        <td class="border px-2 py-1 text-sm" style="background-color: {{ $color }}; color: {{ $textColor }}">
                            @if ($item)
                                <div class="text-center">{{ $materia }}</div>
                                <div class="text-xs italic text-center font-bold">{{ $profesor }}</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
    @endif
</div>
</div>

