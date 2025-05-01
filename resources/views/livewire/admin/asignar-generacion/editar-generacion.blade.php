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
    class="fixed inset-0 bg-gray-50 bg-opacity-50 dark:bg-neutral-800 dark:bg-opacity-50 z-50 flex items-center justify-center ">

       <div class="relative">
       <button @click="show = false" class="absolute text-2xl top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-500">
           &times;
       </button>
       <form wire:submit.prevent="actualizarAsignacion">

           <flux:field>
        <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

            <div class="w-120 border-2 border-gray-50 bg-white  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar Asignación</h2>
            <div class="mb-4">

                <flux:field>


                <flux:select :label="__('Licenciatura')" wire:model.live="licenciatura_id" id="licenciatura_id" class="w-full">
                    <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                    @foreach ($licenciaturas as $licenciatura)
                    <option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</option>
                    @endforeach
                </flux:select>


                <flux:select :label="__('Generación')" wire:model.live="generacion_id" id="generacion_id" class="w-full">
                    <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                    @foreach ($generaciones as $generacion)
                    <option value="{{ $generacion->id }}">{{ $generacion->generacion }}</option>
                    @endforeach
                </flux:select>

                <flux:select :label="__('Modalidad')" wire:model.live="modalidad_id" id="modalidad_id" class="w-full">
                    <flux:select.option value="">--Selecciona una opción---</flux:select.option>
                    @foreach ($modalidades as $modalidad)
                    <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                    @endforeach
                </flux:select>





                </flux:field>


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


