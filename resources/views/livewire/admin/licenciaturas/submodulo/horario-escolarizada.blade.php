<div>


    <div class="my-3">
        <form wire:submit.prevent="guardarHora" class="grid grid-cols-5 gap-4 mt-5">
            <div class="col-span-2">
                <flux:label>Agregar Hora</flux:label>
                <flux:select wire:model.live="hora">
                   <flux:select.option value="">--Selecciona una hora--</flux:select.option>
                    @foreach($horasDisponibles as $hora)
                        <option value="{{ $hora }}">{{ $hora }}</option>

                    @endforeach
                </flux:select>
            </div>
            <div class="flex items-end">
                <flux:button variant="primary" class="mt-2" type="submit">Agregar</flux:button>
            </div>
        </form>
    </div>



    <div>
        <div>
            <div class="p-5 border border-b-0 border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                {{-- DESCARGAR HORARIO --}}
                {{--
                <a target="_blank" href="{{route('admin.horario', ["level" => $level_id, "grade" => $grade_id, "group" => $grupo->id] )}}" class="inline-block px-4 py-2 bg-blue-500 text-white font-bold rounded hover:bg-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Descargar horario
                </a>
                --}}

                <table class="table-auto w-full border-collapse border border-gray-300 mt-4">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="border border-gray-300 px-4 py-2">Hora</th>
                            <th class="border border-gray-300 px-4 py-2">Lunes</th>
                            <th class="border border-gray-300 px-4 py-2">Martes</th>
                            <th class="border border-gray-300 px-4 py-2">Miércoles</th>
                            <th class="border border-gray-300 px-4 py-2">Jueves</th>
                            <th class="border border-gray-300 px-4 py-2">Viernes</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @foreach($horario as $hora)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">{{ $hora['hora'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $hora['lunes'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $hora['martes'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $hora['miercoles'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $hora['jueves'] }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $hora['viernes'] }}</td>
                            </tr>
                        @endforeach --}}

                    </tbody>
                </table>


            </div>
        </div>
    </div>
</div>
