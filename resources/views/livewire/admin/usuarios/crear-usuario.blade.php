<div>
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Usuarios</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear usuarios.</p>
    </div>
<form wire:submit.prevent="guardarUsuarios">
    <flux:field>

    {{-- <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

        <div class="w-120 border-2 border-gray-100 bg-white dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
            <flux:input wire:model.live="imagen" :label="__('Imagen de la licenciatura')" type="file" accept="image/jpeg,image/jpg,image/png" />


            <flux:input wire:model.live="nombre" :label="__('Licenciatura')" type="text" placeholder="Nombre de la licenciatura"  autofocus autocomplete="nombre" />
            <flux:input wire:model.live="slug" readonly :label="__('Url')" type="text" placeholder="Url"  autofocus autocomplete="slug" />
            <flux:input wire:model.live="nombre_corto" :label="__('Nombre corto')" type="text" placeholder="Nombre corto"  autofocus autocomplete="nombre_corto" />
            <flux:input wire:model.live="RVOE" :label="__('RVOE')" type="text" placeholder="RVOE"  autofocus autocomplete="RVOE" />

            <div class="flex items-center gap-4 mt-3">
                <div class="flex items-center">
                    <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                </div>
            </div>
        </div>



    </div> --}}


    </flux:field>

</form>


</div>

