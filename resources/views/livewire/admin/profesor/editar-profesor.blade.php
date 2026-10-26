<div>
    <flux:modal name="editar-profesor" wire:model="open" variant="flyout" class="w-full md:max-w-xl">
        <form wire:submit="actualizarProfesor" class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="lg">Editar expediente docente</flux:heading>
                <flux:text>{{ $nombre }} {{ $apellido_paterno }} {{ $apellido_materno }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:badge color="sky">Disponibilidad: {{ $estadoDocente }}</flux:badge>
                <flux:badge color="{{ $estadoCuenta === 'Activa' ? 'green' : 'amber' }}">Acceso: {{ $estadoCuenta }}</flux:badge>
            </div>
            <flux:text class="text-xs">La edición no altera cuentas, roles, horarios, materias ni calificaciones.</flux:text>
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:input wire:model="foto_nueva" type="file" label="Cambiar fotografía (máx. 2 MB)" accept="image/jpeg,image/png" />
                @error('foto_nueva')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <div wire:loading wire:target="foto_nueva" class="mt-2 text-sm text-sky-600">Cargando fotografía…</div>
                <div class="mt-3 flex gap-5">
                    <div class="space-y-1 text-center">
                        <p class="text-xs text-zinc-500">Actual</p>
                        @if($foto)
                            <img src="{{ asset('storage/profesores/' . basename($foto)) }}" alt="Foto actual" class="size-24 rounded-xl object-cover" />
                        @else
                            <div class="grid size-24 place-items-center rounded-xl bg-zinc-100 dark:bg-zinc-800"><flux:icon.user class="size-8 text-zinc-400" /></div>
                        @endif
                    </div>
                    <div class="space-y-1 text-center">
                        <p class="text-xs text-zinc-500">Nueva</p>
                        @if($foto_nueva)
                            <img src="{{ $foto_nueva->temporaryUrl() }}" alt="Foto nueva" class="size-24 rounded-xl object-cover" />
                        @else
                            <div class="grid size-24 place-items-center rounded-xl bg-zinc-100 text-xs text-zinc-400 dark:bg-zinc-800">Sin cambios</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="nombre" label="Nombre(s)" badge="Obligatorio" maxlength="255" />
                <flux:input wire:model="apellido_paterno" label="Apellido paterno" badge="Obligatorio" maxlength="255" />
                <flux:input wire:model="apellido_materno" label="Apellido materno" maxlength="255" />
                <flux:input wire:model="telefono" label="Teléfono" maxlength="10" placeholder="10 dígitos" />
                <div class="sm:col-span-2"><flux:input wire:model="perfil" label="Perfil académico" maxlength="255" /></div>
                <div class="sm:col-span-2">
                    <flux:label>Color docente (horarios)</flux:label>
                    <div class="mt-2 flex items-center gap-3 rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
                        <input type="color" wire:model="color" class="h-9 w-12 cursor-pointer" aria-label="Color de horarios" />
                        <span class="text-sm text-zinc-500">{{ $color }}</span>
                    </div>
                    @error('color')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" wire:click="cerrarModal">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled" wire:target="actualizarProfesor,foto_nueva" class="!bg-sky-700 hover:!bg-sky-800">Guardar cambios</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
