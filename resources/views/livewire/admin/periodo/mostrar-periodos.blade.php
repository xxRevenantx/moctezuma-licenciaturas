<div>

    <div x-data ="{
        destroyPeriodo(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Este periodo se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarPeriodo', id);

                    }
                })
            }
    }">


        <div class="overflow-x-auto">
            <h3 class="mt-5 flex items-center gap-1 text-2xl font-bold text-gray-800 dark:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                </svg>
               <span>Filtrar por:</span>
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2  p-2">

                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="filtrar_cuatrimestre" >
                        <flux:select.option value="">--Selecciona un cuatrimestre---</flux:select.option>
                        @foreach($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->cuatrimestre }}° Cuatrimestre</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

              <flux:field>
                <flux:label>Generación</flux:label>
                <flux:select wire:model.live="filtrar_generacion">
                    <flux:select.option value="">--Selecciona una generación---</flux:select.option>
                    @foreach($generaciones as $generacion)
                        <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                    @endforeach
                </flux:select>
              </flux:field>

                <flux:field>
                    <flux:label>Mes</flux:label>
                    <flux:select wire:model.live="filtrar_mes" >
                        <flux:select.option value="">--Selecciona un mes---</flux:select.option>
                        @foreach($meses as $mes)
                            <flux:select.option value="{{ $mes->id }}">{{ $mes->meses }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                <flux:input wire:model.live="filtar_inicio_periodo" :label="__('Inicio del periodo')" type="date"   autocomplete="fecha_inicio" />
                </flux:field>

                <flux:field>
                <flux:input wire:model.live="filtar_termino_periodo" :label="__('Término del periodo')" type="date"  autocomplete="fecha_termino" />
                </flux:field>






            </div>

            <div class="flex items-center justify-start mt-4 gap-1">
                <h3>Buscar Periodo:</h3>
            </div>


            <flux:input type="text" wire:model.live="search" placeholder="Buscar Periodo por ciclo escolar, generación o mes" class="my-2 p-1 w-full" />

            <div class="flex space-x-4 mb-4 justify-start items-center">

                @if($periodos->isNotEmpty())



                <flux:button wire:click="exportarPeriodos" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
                    <div class="flex items-center gap-3">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
                        </div>
                </flux:button>



                <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center gap-2">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>

                @else
                <flux:button disabled variant="primary"  class="bg-gray-100 hover:bg-gray-200 focus:ring-4 text-black">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
                        </div>
                </flux:button>


                       <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>
                @endif



            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200 table-striped">
                    <thead>
                        <tr>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 cursor-pointer">#</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Ciclo escolar</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Mes</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Inicio del periodo</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Término del periodo</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($periodos->isEmpty())
                        <tr>
                            <td colspan="8" class="border px-4 py-2 text-center">No hay periodos disponibles.</td>
                        </tr>
                        @else
                        @php
                        $grouped = $periodos->groupBy(fn($item) => $item->generacion->generacion);
                        @endphp

                        @foreach($grouped as $generacionNombre => $items)
                        <tr>
                            <td colspan="8" class="bg-gray-200 text-left font-bold px-4 py-2 dark:bg-gray-500">
                                GENERACIÓN: <flux:badge color="pink">{{ $generacionNombre }}  </flux:badge>
                            </td>
                        </tr>
                        @foreach($items as $key=>  $periodo)
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $key+1}}</td>
                            <td class="border px-4 py-2 text-center">{{ $periodo->ciclo_escolar }}</td>
                            <td class="border px-4 py-2 text-center">{{ $periodo->cuatrimestre->cuatrimestre }}° Cuatrimestre</td>
                            <td class="border px-4 py-2 text-center">{{ $periodo->mes->meses }}</td>
                            <td class="border px-4 py-2 text-center">
                                @if($periodo->inicio_periodo)
                                    {{ \Carbon\Carbon::parse($periodo->inicio_periodo)->format('d-m-Y') }}
                                @else
                                    <flux:badge color="red">Fecha pendiente</flux:badge>
                                @endif
                            </td>
                            <td class="border px-4 py-2 text-center">
                                @if($periodo->termino_periodo)
                                    {{ \Carbon\Carbon::parse($periodo->termino_periodo)->format('d-m-Y') }}
                                @else
                                    <flux:badge color="red">Fecha pendiente</flux:badge>
                                @endif
                            </td>
                            <td class="border px-4 py-2">
                                <div class="flex flex-col md:flex-row gap-2">
                                    <flux:button variant="primary" @click="Livewire.dispatch('abrirPeriodo', { id: {{ $periodo->id }} })"
                                        class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>
                                    <flux:button variant="danger" @click="destroyPeriodo({{ $periodo->id }})"
                                        class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">Eliminar</flux:button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endforeach

                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            {{ $periodos->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.periodo.editar-periodo />


</div>
