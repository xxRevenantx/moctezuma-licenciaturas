<div x-data x-on:profesor-creado.window="$flux.modal('nuevo-profesor').close()">
    <flux:modal.trigger name="nuevo-profesor">
        <flux:button variant="primary" icon="user-plus" class="!bg-sky-700 hover:!bg-sky-800">Nuevo profesor</flux:button>
    </flux:modal.trigger>

    <flux:modal name="nuevo-profesor" class="w-full md:max-w-3xl">
        <form wire:submit="guardarProfesor" class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="lg">Nuevo expediente docente</flux:heading>
                <flux:text>Vincula una cuenta existente con rol Profesor. El estado del acceso se administra por separado.</flux:text>
            </div>

            <div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-900 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                <div class="flex items-start gap-2">
                    <flux:icon.shield-check class="mt-0.5 size-5 shrink-0" />
                    <div>Se aceptan cuentas pendientes de activación de Usuarios PRO. Guardar el expediente <strong>no activa</strong> el inicio de sesión, ni modifica las materias o calificaciones.</div>
                </div>
            </div>

            <div class="space-y-3">
                <flux:select wire:model.live="user_id" label="Cuenta de usuario" badge="Obligatorio">
                    <option value="">Selecciona una cuenta con rol Profesor</option>
                    @foreach($usuarios as $usuario)
                        <option wire:key="cuenta-docente-{{ $usuario->id }}" value="{{ $usuario->id }}">
                            {{ $usuario->username }} · {{ $usuario->CURP }} {{ $usuario->status !== 'true' ? '· Acceso pendiente' : '' }}
                        </option>
                    @endforeach
                </flux:select>
                @error('user_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @if($usuarios->isEmpty())
                    <p class="text-sm text-amber-700 dark:text-amber-300">No hay cuentas sin expediente docente. Primero crea una cuenta con rol Profesor en el módulo Usuarios.</p>
                @endif
                @can('admin.usuarios')
                    <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-sky-700 hover:underline dark:text-sky-300">
                        <flux:icon.user-plus class="size-4" /> Crear cuenta en Usuarios PRO <flux:icon.arrow-up-right class="size-3" />
                    </a>
                @endcan
            </div>

            @if($user_id)
                <div class="grid grid-cols-1 gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800/40 sm:grid-cols-2">
                    <div><span class="text-zinc-500 dark:text-zinc-400">CURP:</span> <strong>{{ $CURP ?: 'No capturada' }}</strong></div>
                    <div class="truncate"><span class="text-zinc-500 dark:text-zinc-400">Correo:</span> <strong>{{ $usuario_email ?: 'No capturado' }}</strong></div>
                    @if($cuentaPendiente)
                        <div class="sm:col-span-2"><flux:badge color="amber">Cuenta sin acceso activo; puede vincularse</flux:badge></div>
                    @endif
                    <div class="sm:col-span-2">
                        <flux:button type="button" size="sm" variant="ghost" icon="magnifying-glass" wire:click="consultarCurp" wire:loading.attr="disabled" wire:target="consultarCurp">Consultar CURP (opcional)</flux:button>
                        <p class="mt-1 text-xs text-zinc-500">Si el servicio externo no responde, captura el nombre manualmente.</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="nombre" label="Nombre(s)" badge="Obligatorio" maxlength="255" />
                <flux:input wire:model="apellido_paterno" label="Apellido paterno" badge="Obligatorio" maxlength="255" />
                <flux:input wire:model="apellido_materno" label="Apellido materno" maxlength="255" />
                <flux:input wire:model="telefono" label="Teléfono" inputmode="numeric" maxlength="10" placeholder="10 dígitos" />
                <div class="sm:col-span-2">
                    <flux:input wire:model="perfil" label="Perfil académico / especialidad" maxlength="255" placeholder="Ej. Lic. en Educación" />
                </div>
                <div>
                    <flux:label>Color identificador de horarios</flux:label>
                    <div class="mt-2 flex items-center gap-3 rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                        <input type="color" wire:model="color" class="h-9 w-12 cursor-pointer rounded-md border-0 bg-transparent" aria-label="Color docente" />
                        <span class="text-sm text-zinc-500">{{ $color }}</span>
                    </div>
                    @error('color')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <flux:input type="file" wire:model="foto" accept="image/jpeg,image/png" label="Fotografía (máx. 2 MB)" />
                    @error('foto')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    <div wire:loading wire:target="foto" class="mt-2 text-sm text-sky-600">Cargando fotografía…</div>
                    @if($foto)
                        <img src="{{ $foto->temporaryUrl() }}" alt="Vista previa de fotografía" class="mt-2 size-20 rounded-xl object-cover" />
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:modal.close><flux:button type="button" variant="ghost">Cancelar</flux:button></flux:modal.close>
                <flux:button type="submit" variant="primary" icon="check" class="!bg-sky-700 hover:!bg-sky-800" wire:loading.attr="disabled" wire:target="guardarProfesor,foto">
                    Crear expediente
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
