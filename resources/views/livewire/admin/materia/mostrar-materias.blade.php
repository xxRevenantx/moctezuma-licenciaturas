<div>

    <div x-data ="{
        destroyMateria(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: `Esta materia se eliminará de forma permanente`,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('eliminarMateria', id);

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
                    <flux:label>Licenciatura</flux:label>
                    <flux:select wire:model.live="filtrar_licenciatura" >
                        <flux:select.option value="">--Selecciona una licenciatura---</flux:select.option>
                        @foreach($licenciaturas as $licenciatura)
                            <flux:select.option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Cuatrimestre</flux:label>
                    <flux:select wire:model.live="filtrar_cuatrimestre" >
                        <flux:select.option value="">--Selecciona un cuatrimestre---</flux:select.option>
                        @foreach($cuatrimestres as $cuatrimestre)
                            <flux:select.option value="{{ $cuatrimestre->id }}">{{ $cuatrimestre->nombre_cuatrimestre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field>
                    <flux:label>Calificable</flux:label>
                    <flux:select wire:model.live="filtrar_calificable" >
                        <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                        <flux:select.option value="true">Sí</flux:select.option>
                        <flux:select.option value="false">No</flux:select.option>
                    </flux:select>
                </flux:field>


            </div>

            <div class="flex items-center justify-start mt-4 gap-1">
                <h3>Buscar Materia:</h3>
            </div>


            <flux:input type="text" wire:model.live="search" placeholder="Buscar Materia, Clave" class="my-2 p-1 w-full" />

            <div class="flex space-x-4 mb-4 justify-start items-center">

                @if($materias->isNotEmpty())



                <flux:button wire:click="exportarMaterias" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
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
{{--
            <form wire:submit.prevent="importarMaterias" >


                @error('archivo')
                    <div class="text-red-500 text-sm mb-2">{{ $message }}</div>
                @enderror



                <div class="flex gap-3">

                <div class="relative">
                    <label title="Click para importar" for="button2" class="cursor-pointer flex items-center gap-4 px-5 py-2.5 before:border-gray-400/60 hover:before:border-gray-300 group before:bg-gray-100 before:absolute before:inset-0 before:rounded-lg before:border before:border-dashed before:transition-transform before:duration-300 hover:before:scale-105 active:duration-75 active:before:scale-95">
                      <div class="w-max relative">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                          </svg>

                      </div>
                      <div class="relative">
                          <span class="block text-base font-semibold relative text-blue-900 group-hover:text-blue-500">
                              Selecciona un archivo
                          </span>
                          <span class="mt-0.5 block text-sm text-gray-500"></span>
                      </div>
                     </label>
                    <input hidden="" accept=".xlsx,.xls,.csv"  wire:model="archivo" type="file" id="button2">

                </div>

                <flux:button variant="primary" type="submit" >Importar</flux:button>

            </div>
            </form> --}}
           <div class="my-3">
             {{ $materias->links() }}
           </div>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-200 table-striped">
                    <thead>
                        <tr>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700 cursor-pointer">#</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Materia</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Url</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Clave</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Créditos</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Cuatrimestre</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Calificable</th>
                            <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>


                        @if($materias->isEmpty())
                        <tr>
                            <td colspan="8" class="border px-4 py-2 text-center">No hay materias disponibles.</td>
                        </tr>
                        @else
                        @php
                        $grouped = $materias->groupBy(fn($item) => $item->licenciatura->nombre);
                        @endphp

                        @foreach($grouped as $licenciaturaNombre => $items)
                        <tr>
                            <td colspan="8" class="bg-gray-200 text-left font-bold px-4 py-2 dark:bg-gray-500">
                                Licenciatura: <flux:badge color="pink">{{ $licenciaturaNombre }}  </flux:badge>
                            </td>
                        </tr>
                        @foreach($items as $key=>  $materia)
                        <tr>
                            <td class="border px-4 py-2 text-center">{{ $key+1}}</td>
                            <td class="border px-4 py-2 ">{{ $materia->nombre }}</td>
                            <td class="border px-4 py-2 ">{{ $materia->slug }}</td>
                            <td class="border px-4 py-2 text-center">{{ $materia->clave}}</td>
                            <td class="border px-4 py-2 text-center">{{ $materia->creditos}}</td>
                            <td class="border px-4 py-2 text-center">{{ $materia->cuatrimestre->nombre_cuatrimestre}}</td>
                            <td class="border px-4 py-2 text-center">
                                @if($materia->calificable == "true")
                                <flux:badge color="green">Sí</flux:badge>
                                @else
                                <flux:badge color="red">No</flux:badge>
                                @endif
                            </td>


                            <td class="border px-4 py-2">
                                <div class="flex flex-col md:flex-row gap-2">
                                    <flux:button variant="primary" @click="Livewire.dispatch('abrirMateria', { id: {{ $materia->id }} })"
                                        class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer">Editar</flux:button>
                                    <flux:button variant="danger" @click="destroyMateria({{ $materia->id }})"
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
            {{ $materias->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.materia.editar-materia />


</div>
