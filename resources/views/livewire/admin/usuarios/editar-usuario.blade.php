<div>
    <flux:modal name="editar-usuario" wire:model="open" variant="flyout" class="w-full md:max-w-xl">
        <form wire:submit="actualizarUsuario" class="space-y-6">
            <div class="border-b border-zinc-200 pb-4 dark:border-zinc-700">
                <flux:heading size="lg">Editar cuenta</flux:heading>
                <flux:text class="mt-1">Modifica el acceso sin alterar la inscripción, materias ni calificaciones asociadas.</flux:text>
                @if($username)<flux:badge class="mt-2" color="sky">{{ $username }}</flux:badge>@endif
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="username" label="Usuario" badge="Requerido" maxlength="15" />
                <flux:input wire:model="CURP" label="CURP" badge="Requerido" maxlength="18" class="uppercase" />
                <div class="sm:col-span-2">
                    <flux:input wire:model="email" label="Correo electrónico" type="email" badge="Requerido" />
                    <p class="mt-1 text-xs text-zinc-500">Si cambias el correo, la nueva dirección necesitará verificarse.</p>
                </div>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 dark:border-zinc-700 dark:bg-zinc-800/40">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="font-semibold text-zinc-900 dark:text-white">Acceso a la plataforma</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Desactivar no elimina expedientes.</p>
                    </div>
                    <flux:switch wire:model="status" label="Cuenta activa" />
                </div>
                @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="space-y-3">
                <flux:label>Roles del usuario</flux:label>
                <flux:checkbox.group wire:model="rol">
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach ($roles as $role)
                            <label wire:key="edit-role-{{ $role->id }}" class="flex cursor-pointer items-center gap-2 rounded-xl border border-zinc-200 p-3 hover:border-sky-300 dark:border-zinc-700">
                                <flux:checkbox value="{{ $role->id }}" />
                                <span class="text-sm">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </flux:checkbox.group>
                @error('rol')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @error('rol.*')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @if(str_contains($rol_name, 'SuperAdmin'))
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950 dark:border-amber-900 dark:bg-amber-900/20 dark:text-amber-200">
                    Cuenta privilegiada: el sistema impide eliminar el último SuperAdmin activo y retirar el propio rol SuperAdmin.
                </div>
            @endif
            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" wire:click="cerrarModal">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="actualizarUsuario" class="!bg-sky-700">Guardar cambios</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
