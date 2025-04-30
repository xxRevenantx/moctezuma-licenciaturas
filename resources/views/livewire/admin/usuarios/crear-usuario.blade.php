<div>
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Usuarios</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Formulario para crear usuarios.</p>
    </div>
<form wire:submit.prevent="guardarUsuario">
    <flux:field>

    <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

        <div class="w-120 border-2 border-gray-100 bg-white dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">

            <flux:input wire:model.live="username" :label="__('Nombre de usuario')" type="text" placeholder="Nombre de usuario"  autofocus autocomplete="username" />
            <flux:input wire:model.live="matricula"  :label="__('Matrícula')" type="text" placeholder="Matrícula"  autofocus autocomplete="matricula" />
            <flux:input wire:model.live="email" :label="__('Email')" type="email" placeholder="Email"  autofocus autocomplete="email" />

            <flux:checkbox.group wire:model.live="rol" label="Listado de roles">



                            @foreach ($roles as $rol )
                                <flux:checkbox
                                    label="{{ $rol->name }}"
                                    value="{{ $rol->id }}"

                                />
                            @endforeach


                       </flux:checkbox.group>


            <div class="flex items-center gap-4 mt-3">
                <div class="flex items-center">
                    <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                </div>
            </div>
        </div>



    </div>


    </flux:field>

</form>


</div>

