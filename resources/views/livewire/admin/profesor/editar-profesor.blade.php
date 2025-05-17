<div x-data="{ show: @entangle('open') }"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="show = false; $wire.cerrarModal()"
        class="fixed inset-0 bg-gray-50 dark:bg-neutral-800 z-50 flex items-center justify-center ">


       {{-- <div @click.away="show = false;  $wire.cerrarModal()" class="relative"> --}}
       <div class="relative">
           <button @click="show = false" class="absolute text-2xl top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-500">
               &times;
           </button>
           <form wire:submit.prevent="actualizarProfesor">

               <flux:field>
                <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                    <div class="w-120 border-2 border-gray-50  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar Profesor <flux:badge color="indigo">{{ $nombre }} {{ $apellido_paterno }} {{ $apellido_materno }} </flux:badge></h2>


                          <flux:input wire:model.live="foto_nueva" :label="__('Foto del profesor')" type="file" accept="image/jpeg,image/jpg,image/png" />

                    <div wire:loading wire:target="foto_nueva" class="flex items-center gap-2 text-blue-600 mt-2 justify-center">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Cargando imagen...</span>
                            </div>


                          <div class="mt-4 flex flex-col items-center justify-center">
                                <div class="text-center">
                                    @if ($foto)
                                        <p class="font-semibold">Imagen Actual</p>
                                        <img src="{{ asset('storage/profesores/' . $foto) }}" alt="Foto del profesor" class="w-24 h-24  block m-auto ">
                                    @else
                                        <p class="text-gray-500 italic">No hay foto disponible</p>
                                    @endif

                                    @if ($foto_nueva)
                                        <p class="mt-4 font-semibold">Nueva Foto</p>
                                        <img src="{{ $foto_nueva->temporaryUrl() }}" alt="Nueva foto del profesor" class="w-24 h-24  block m-auto">
                                    @endif
                                </div>

                            </div>



                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:input type="text" badge="Requerido" label="Nombre" placeholder="Nombre" wire:model.live="nombre" />
                            <flux:input type="text" badge="Requerido" label="Apellido Paterno" placeholder="Apellido Paterno" wire:model="apellido_paterno" />
                            <flux:input type="text" badge="Opcional" label="Apellido Materno" placeholder="Apellido Materno" wire:model="apellido_materno" />
                            <flux:input type="text" badge="Opcional" label="Teléfono" placeholder="Teléfono" wire:model="telefono" />
                            <flux:input type="text" badge="Opcional" label="Perfil" placeholder="Perfil" wire:model="perfil" />
                            <flux:input type="color" badge="Opcional" label="Color" placeholder="Color" wire:model="color" />
                        </div>




                   <div class="mt-6 flex justify-end gap-2">
                              <div class="flex items-center">
                                   <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Actualizar') }}</flux:button>
                            </div>
                             <div class="flex items-center">
                               <flux:button  @click="show = false; $wire.cerrarModal()" class="w-full cursor-pointer">{{ __('Cancelar') }}</flux:button>
                             </div>
                     </div>
                    </div>
                </div>

                    </flux:field>

                    </form>
            </div>








       </div>
   </div>

</div>
