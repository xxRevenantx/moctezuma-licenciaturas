<div>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Nueva Generación</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear un nueva generación.</p>
        </div>

        <form wire:submit.prevent="crearGeneracion" class="space-y-4">
            <flux:field>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">

                    <flux:input wire:model.live="generacion" :label="__('Generación')" type="text" placeholder="2020-2023"  autofocus autocomplete="titulo" />

                    <div class="flex items-center gap-4 md:mt-7">
                        <div class="flex items-center">
                            <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                        </div>
                    </div>
            </div>




            </flux:field>



        </form>
    </div>
</div>
