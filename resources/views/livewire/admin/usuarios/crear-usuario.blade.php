<div x-data x-on:usuario-creado.window="$flux.modal('nuevo-usuario').close()">
    <flux:modal.trigger name="nuevo-usuario">
        <flux:button variant="primary" icon="user-plus" class="!bg-sky-700 hover:!bg-sky-800">Nuevo usuario</flux:button>
    </flux:modal.trigger>

    <flux:modal name="nuevo-usuario" class="w-full md:max-w-2xl">
        <form wire:submit="guardarUsuario" class="space-y-6">
            <div class="space-y-1">
                <flux:heading size="lg">Crear cuenta institucional</flux:heading>
                <flux:text>Registra los datos de acceso y el rol. La nueva cuenta quedará inactiva hasta autorizar su acceso.</flux:text>
            </div>
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-900 dark:border-sky-800 dark:bg-sky-900/20 dark:text-sky-100">
                <div class="flex items-start gap-2">
                    <flux:icon.shield-check class="mt-0.5 size-5 shrink-0" />
                    <span>No se usa ninguna contraseña predeterminada. Después de crear la cuenta, envía un enlace de configuración al correo real del usuario y actívala cuando corresponda.</span>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="username" label="Nombre de usuario" badge="Requerido" maxlength="15" autocomplete="off" placeholder="user_ejemplo" />
                <flux:input wire:model="CURP" label="CURP" badge="Requerido" maxlength="18" class="uppercase" placeholder="18 caracteres" autocomplete="off" />
                <div class="sm:col-span-2">
                    <flux:input wire:model="email" type="email" label="Correo electrónico real" badge="Requerido" placeholder="persona@correo.com" autocomplete="off" />
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Nunca se genera un Gmail a partir de la CURP.</p>
                </div>
            </div>
            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:label>Roles autorizados</flux:label>
                    <flux:badge color="sky">Obligatorio</flux:badge>
                </div>
                <flux:checkbox.group wire:model="rol">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                        @foreach ($roles as $role)
                            <label wire:key="new-role-{{ $role->id }}" class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 hover:border-sky-300 hover:bg-sky-50/40 dark:border-zinc-700 dark:hover:bg-sky-900/20">
                                <flux:checkbox value="{{ $role->id }}" />
                                <span class="text-sm font-medium">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:checkbox.group>
                @error('rol')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @error('rol.*')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-wrap justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:modal.close><flux:button variant="ghost" type="button">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="guardarUsuario" class="!bg-sky-700 hover:!bg-sky-800">Crear cuenta segura</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
