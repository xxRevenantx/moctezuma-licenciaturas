<div x-data="{ show: @entangle('open') }"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-50"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-50"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="show = false; $wire.cerrarModal()"
        class="fixed inset-0 bg-gray-50 dark:bg-neutral-800 z-50 flex items-center justify-center ">


       {{-- <div @click.away="show = false;  $wire.cerrarModal()" class="relative"> --}}
       <div class="relative">
           <button @click="show = false" class="absolute text-2xl top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-500">
               &times;
           </button>
           <form wire:submit.prevent="actualizarLicenciatura">
               <flux:field>

               <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                   <div class="w-120 border-2 border-gray-50  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                       <h2 class="text-xl font-bold mb-4 text-center text-accent">Editar Licenciatura <flux:badge color="indigo">{{ $nombre }}</flux:badge></h2>
                       <flux:input wire:model.live="imagen_nueva" :label="__('Imagen de la licenciatura')" type="file" accept="image/jpeg,image/jpg,image/png" />




                          <div class="mt-4 flex flex-col items-center justify-center">
                                <div class="text-center">
                                    @if ($imagen)
                                        <p class="font-semibold">Imagen Actual</p>
                                        <img src="{{ asset('storage/licenciaturas/' . $imagen) }}" alt="Imagen de la licenciatura" class="w-24 h-24  block m-auto ">
                                    @else
                                        <p class="text-gray-500 italic">No hay imagen disponible</p>
                                    @endif

                                    @if ($imagen_nueva)
                                        <p class="mt-4 font-semibold">Nueva Imagen</p>
                                        <img src="{{ $imagen_nueva->temporaryUrl() }}" alt="Nueva imagen de la licenciatura" class="w-24 h-24  block m-auto">
                                    @endif
                                </div>

                            </div>


                       <flux:input wire:model.live="nombre" :label="__('Licenciatura')" type="text" placeholder="Nombre de la licenciatura"  autofocus autocomplete="nombre" />
                       <flux:input wire:model="slug" readonly :label="__('Url')" type="text" placeholder="Url"  autofocus autocomplete="slug" />
                       <flux:input wire:model="nombre_corto" :label="__('Nombre corto')" type="text" placeholder="Nombre corto"  autofocus autocomplete="nombre_corto" />
                       <flux:input wire:model="RVOE" :label="__('RVOE')" type="text" placeholder="RVOE"  autofocus autocomplete="RVOE" />


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
