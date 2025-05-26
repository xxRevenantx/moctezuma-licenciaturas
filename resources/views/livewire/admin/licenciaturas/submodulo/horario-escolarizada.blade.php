<div>


        <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
        </svg>
       <span>Filtrar por:</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2  p-2 mb-4">

      <flux:field>
        <flux:label>Generación</flux:label>
        <flux:select wire:model.live="filtrar_generacion">
            <flux:select.option value="">--Selecciona una generación---</flux:select.option>
            @foreach($generaciones as $generacion)
                <flux:select.option value="{{ $generacion->generacion_id }}">{{ $generacion->generacion->generacion }}</flux:select.option>
            @endforeach
        </flux:select>
      </flux:field>


      <flux:field>
        <flux:label>Cuatrimestre</flux:label>
        <flux:select wire:model.live="filtrar_cuatrimestre">
            <flux:select.option value="">--Selecciona una cuatrimestre---</flux:select.option>
            @foreach($cuatrimestres as $cuatrimestre)
                <flux:select.option value="{{ $cuatrimestre->cuatrimestre_id }}">{{ $cuatrimestre->cuatrimestre->nombre_cuatrimestre}}</flux:select.option>
            @endforeach
        </flux:select>
      </flux:field>



        <flux:field>
              <flux:label>Filtros</flux:label>
                    <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center ">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>
        </flux:field>


    </div>

    <div>

     <table class="min-w-full border-collapse border border-gray-200 table-striped">
        <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">Hora</th>
                @foreach ($dias as $dia)
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-700 dark:text-gray-200 uppercase tracking-wider border-b">{{ $dia->dia }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">

            @foreach ($horas as $hora)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 border-b">{{ $hora }}</td>
                    @foreach ($dias as $dia)
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200 border-b">

                    <select
                        wire:model="horario.{{ $dia->id }}.{{ $hora }}"
                        wire:change="actualizarHorario('{{ $dia->id }}', '{{ $hora }}', $event.target.value)"
                        class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-2 py-2 text-sm text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                    >
                     <option value="0" class="text-gray-400">--Selecciona una opción--</option>

                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id }}">
                                {{ $materia->materia->nombre }}
                            </option>
                        @endforeach
                    </select>
                    </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

</div>
