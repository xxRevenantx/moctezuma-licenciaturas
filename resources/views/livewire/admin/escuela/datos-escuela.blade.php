<div>
    <div class="flex flex-col gap-4">
            <h1 class="text-2xl font-bold text-white dark:text-white bg-[#00659e] p-3 rounded-md">Datos de la escuela</h1>
     </div>





        <div>

            <form wire:submit.prevent="guardarEscuela" class="space-y-4 mt-4 p-1">
                <flux:field>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <flux:input wire:model="nombre" :label="__('Nombre')" type="text" placeholder="Nombre de la escela" autofocus autocomplete="nombre" />
                        <flux:input wire:model="CCT" :label="__('CCT')" type="text" placeholder="Clave de Centro de Trabajo" autocomplete="CCT" />
                        <flux:input wire:model="calle" :label="__('Calle')" type="text" placeholder="Calle" autocomplete="calle" />
                        <flux:input wire:model="no_exterior" :label="__('No. Exterior')" type="text" placeholder="Número Exterior" autocomplete="no_exterior" />
                        <flux:input wire:model="no_interior" :label="__('No. Interior')" type="text" placeholder="Número Interior" autocomplete="no_interior" />
                        <flux:input wire:model="colonia" :label="__('Colonia')" type="text" placeholder="Colonia" autocomplete="colonia" />
                        <flux:input wire:model="codigo_postal" :label="__('Código Postal')" type="text" placeholder="Código Postal" autocomplete="codigo_postal" />
                        <flux:input wire:model="ciudad" :label="__('Ciudad')" type="text" placeholder="Ciudad" autocomplete="ciudad" />
                        <flux:input wire:model="municipio" :label="__('Municipio')" type="text" placeholder="Municipio" autocomplete="municipio" />
                        <flux:input wire:model="estado" :label="__('Estado')" type="text" placeholder="Estado" autocomplete="estado" />
                        <flux:input wire:model="telefono" :label="__('Teléfono')" type="text" placeholder="Teléfono" autocomplete="telefono" />
                        <flux:input wire:model="correo" :label="__('Correo')" type="email" placeholder="Correo Electrónico" autocomplete="correo" />
                        <flux:input wire:model="pagina_web" :label="__('Página Web')" type="url" placeholder="Página Web" autocomplete="pagina_web" />

                    </div>
                            <div class="flex items-center gap-4 mt-3">
                            <div class="flex items-center">
                                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
                            </div>
                        </div>

                </flux:field>
            </form>

        </div>

</div>
