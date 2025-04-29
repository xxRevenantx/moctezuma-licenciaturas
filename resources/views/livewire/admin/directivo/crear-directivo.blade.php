<div>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Asignación del personal Directivo</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para asignar un nuevo directivo.</p>
        </div>

        <form wire:submit.prevent="crearDirectivo" class="space-y-4">


            <flux:field>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <flux:input wire:model.live="titulo" :label="__('Título')" type="text" placeholder="Ejem: M.C, Lic, Dr, Mtro, Profr, etc."  autofocus autocomplete="titulo" />
                    <flux:input wire:model.live="nombre" :label="__('Nombre')" type="text" placeholder="Nombre del directivo"  autofocus autocomplete="nombre" />
                    <flux:input wire:model.live="apellido_paterno" :label="__('Apellido Paterno')" type="text" placeholder="Apellido Paterno"  autocomplete="apellido_paterno" />
                    <flux:input wire:model.live="apellido_materno" :label="__('Apellido Materno')" type="text" placeholder="Apellido Materno"  autocomplete="apellido_materno" />
                    <flux:input wire:model.live="cargo" :label="__('Cargo')" type="text" placeholder="Cargo" autocomplete="cargo" />
                    <flux:input wire:model.live="telefono" :label="__('Teléfono')" type="text" placeholder="Teléfono"  autocomplete="telefono" />
                    <flux:input wire:model.live="correo" :label="__('Correo Electrónico')" type="email" placeholder="Correo electrónico"  autocomplete="correo" />




                    <div class="flex items-center gap-4 mt-3">
                        <div class="flex items-center">
                            <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                        </div>
                    </div>




            </div>


            </flux:field>



        </form>
    </div>
</div>
