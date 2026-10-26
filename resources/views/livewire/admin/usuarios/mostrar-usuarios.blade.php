<div class="space-y-5">
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Resultados</span><flux:icon.users class="size-5 text-sky-700" /></div>
            <p class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-zinc-500">Cuentas con los filtros actuales</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Activos</span><flux:icon.user-check class="size-5 text-emerald-600" /></div>
            <p class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($stats['activos']) }}</p>
            <p class="text-xs text-zinc-500">Con acceso habilitado</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Inactivos</span><flux:icon.user-minus class="size-5 text-rose-600" /></div>
            <p class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($stats['inactivos']) }}</p>
            <p class="text-xs text-zinc-500">Sin autorización para ingresar</p>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Sin verificar</span><flux:icon.shield-exclamation class="size-5 text-amber-600" /></div>
            <p class="mt-2 text-3xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ number_format($stats['sinVerificar']) }}</p>
            <p class="text-xs text-zinc-500">Correo sin verificar, incluidas cuentas anteriores</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="border-b border-zinc-100 px-4 py-4 dark:border-zinc-800 sm:px-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-bold text-zinc-900 dark:text-white">Directorio institucional</h2>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Filtra, consulta, configura permisos y administra cuentas sin eliminar historiales.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:button icon="arrow-down-tray" wire:click="exportarUsuarios" wire:loading.attr="disabled" wire:target="exportarUsuarios">Excel</flux:button>
                    @if($superAdmins->count())
                        <flux:modal.trigger name="super-admin-usuarios">
                            <flux:button icon="shield-check" variant="ghost">SuperAdmin ({{ $superAdmins->count() }})</flux:button>
                        </flux:modal.trigger>
                    @endif
                </div>
            </div>
            <div class="mt-4 grid grid-cols-1 items-end gap-3 md:grid-cols-12">
                <div class="md:col-span-6">
                    <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" label="Buscar" placeholder="Usuario, nombre, correo, CURP o matrícula..." type="search" />
                </div>
                <div class="md:col-span-3">
                    <flux:select wire:model.live="filtrar_roles" label="Rol">
                        <flux:select.option value="">Todos los roles</flux:select.option>
                        @foreach ($roles as $role)
                            <flux:select.option value="{{ $role->name }}">{{ $role->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="md:col-span-2">
                    <flux:select wire:model.live="filtrar_status" label="Estado">
                        <flux:select.option value="">Todos</flux:select.option>
                        <flux:select.option value="Activo">Activos</flux:select.option>
                        <flux:select.option value="Inactivo">Inactivos</flux:select.option>
                    </flux:select>
                </div>
                <div class="md:col-span-1">
                    <flux:button title="Limpiar filtros" icon="x-mark" wire:click="limpiarFiltros" class="w-full">Limpiar</flux:button>
                </div>
            </div>
        </div>

        @if(count($selected))
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sky-200 bg-sky-50 px-4 py-3 text-sm dark:border-sky-900 dark:bg-sky-950/40 sm:px-5">
                <span class="font-medium text-sky-900 dark:text-sky-100"><flux:icon.check-circle class="mr-1 inline size-4" /> {{ count($selected) }} usuario(s) seleccionado(s)</span>
                <div class="flex flex-wrap gap-2">
                    <flux:button size="sm" variant="ghost" wire:click="limpiarSeleccion">Cancelar selección</flux:button>
                    <flux:button size="sm" icon="user-check" wire:confirm="¿Activar las {{ count($selected) }} cuentas seleccionadas?" wire:click="activarUsuarios" wire:loading.attr="disabled">Activar</flux:button>
                    <flux:button size="sm" icon="user-minus" wire:confirm="¿Desactivar las {{ count($selected) }} cuentas seleccionadas? No se eliminará ningún expediente." wire:click="inactivarUsuarios" wire:loading.attr="disabled">Desactivar</flux:button>
                    <flux:dropdown>
                        <flux:button size="sm" variant="primary" icon:trailing="chevron-down" class="!bg-sky-700">Asignar rol</flux:button>
                        <flux:menu>
                            @foreach($roles->where('name', '!=', 'SuperAdmin') as $role)
                                <flux:menu.item icon="user-group" wire:confirm="¿Reemplazar los roles de {{ count($selected) }} usuarios por {{ $role->name }}?" wire:click="cambioRolUsuariosSeleccionados({{ $role->id }})">{{ $role->name }}</flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        @endif
        @error('selected')<p class="px-5 py-2 text-sm text-rose-600">{{ $message }}</p>@enderror

        <div class="relative overflow-x-auto">
            <table class="w-full min-w-[880px] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] font-bold uppercase tracking-wide text-slate-600 dark:bg-zinc-800/70 dark:text-zinc-300">
                    <tr>
                        <th class="w-10 px-4 py-3"><flux:checkbox wire:model.live="selectAll" aria-label="Seleccionar hasta 500 cuentas filtradas (excluye la propia y SuperAdmin)" /></th>
                        <th class="px-4 py-3">Usuario / Perfil</th>
                        <th class="px-4 py-3">Correo</th>
                        <th class="px-4 py-3">Roles</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Identidad</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($usuarios as $usuario)
                        @php
                            $canSelect = $usuario->id !== auth()->id() && !$usuario->hasRole('SuperAdmin');
                            $personName = $usuario->inscripcion
                                ? trim($usuario->inscripcion->nombre.' '.$usuario->inscripcion->apellido_paterno.' '.$usuario->inscripcion->apellido_materno)
                                : ($usuario->profesor ? trim($usuario->profesor->nombre.' '.$usuario->profesor->apellido_paterno.' '.$usuario->profesor->apellido_materno) : 'Sin perfil académico vinculado');
                        @endphp
                        <tr wire:key="usuario-row-{{ $usuario->id }}" class="transition-colors hover:bg-sky-50/60 dark:hover:bg-zinc-800/60 {{ in_array($usuario->id, $selected) ? 'bg-sky-50 dark:bg-sky-900/10' : '' }}">
                            <td class="px-4 py-3">@if($canSelect)<flux:checkbox wire:model.live="selected" value="{{ $usuario->id }}" aria-label="Seleccionar {{ $usuario->username }}" />@endif</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sky-100 font-semibold text-sky-800 dark:bg-sky-900/50 dark:text-sky-200">{{ strtoupper(substr($usuario->username, 0, 1)) }}</div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-zinc-900 dark:text-white">{{ $usuario->username }}</div>
                                        <div class="max-w-60 truncate text-xs text-zinc-500" title="{{ $personName }}">{{ $personName }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><div class="max-w-60 truncate text-zinc-700 dark:text-zinc-200" title="{{ $usuario->email }}">{{ $usuario->email }}</div>
                                <div class="mt-0.5 text-xs {{ $usuario->email_verified_at ? 'text-emerald-600' : 'text-amber-600' }}">{{ $usuario->email_verified_at ? 'Verificado' : 'Sin verificar' }}</div>
                            </td>
                            <td class="px-4 py-3"><div class="flex max-w-48 flex-wrap gap-1">
                                @forelse ($usuario->roles as $role)
                                    <flux:badge size="sm" color="{{ match($role->name) { 'SuperAdmin' => 'red', 'Admin' => 'blue', 'Profesor' => 'green', 'Estudiante' => 'amber', default => 'zinc' } }}">{{ $role->name }}</flux:badge>
                                @empty <span class="text-xs text-zinc-400">Sin rol</span> @endforelse
                            </div></td>
                            <td class="px-4 py-3">
                                @if ($usuario->status === 'true')<flux:badge color="green" size="sm">Activo</flux:badge>
                                @else<flux:badge color="red" size="sm">Inactivo</flux:badge>@endif
                            </td>
                            <td class="px-4 py-3"><span class="font-mono text-xs text-zinc-500">{{ $usuario->CURP }}</span>
                                @if($usuario->inscripcion?->matricula)<div class="mt-1 text-xs text-sky-700 dark:text-sky-300">{{ $usuario->inscripcion->matricula }}</div>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button size="sm" icon="pencil-square" wire:click="$dispatch('abrirModal', {id: {{ $usuario->id }}})">Editar</flux:button>
                                    <flux:dropdown>
                                        <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" aria-label="Más acciones" />
                                        <flux:menu>
                                            <flux:menu.item icon="envelope" wire:confirm="¿Enviar un enlace de configuración de contraseña al correo registrado?" wire:click="enviarAcceso({{ $usuario->id }})">Enviar enlace de acceso</flux:menu.item>
                                            @if($usuario->status === 'true' && $usuario->id !== auth()->id())
                                                <flux:menu.item icon="user-minus" variant="danger" wire:confirm="¿Desactivar esta cuenta? No se borrarán inscripciones ni calificaciones." wire:click="eliminarUsuario({{ $usuario->id }})">Desactivar cuenta</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center"><flux:icon.users class="mx-auto size-8 text-zinc-300" /><p class="mt-2 font-medium text-zinc-700 dark:text-zinc-200">No hay usuarios con estos filtros</p><p class="text-xs text-zinc-500">Prueba otro criterio de búsqueda.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
            <div wire:loading.delay wire:target="search,filtrar_roles,filtrar_status,cambioRolUsuariosSeleccionados,inactivarUsuarios,activarUsuarios" class="pointer-events-none absolute inset-0 flex items-center justify-center bg-white/60 dark:bg-zinc-900/50">
                <div class="rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-700 shadow-lg dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">Actualizando usuarios…</div>
            </div>
        </div>
        <div class="border-t border-zinc-100 px-4 py-3 dark:border-zinc-800 sm:px-5">
            <div class="mb-2 text-xs text-zinc-500">{{ $usuarios->total() }} registro(s) · Selección masiva de hasta 500 usuarios con los filtros aplicados; excluye cuentas SuperAdmin y la cuenta actual.</div>
            {{ $usuarios->links() }}
        </div>
    </div>

    @if($superAdmins->count())
        <flux:modal name="super-admin-usuarios" variant="flyout" class="w-full md:max-w-lg">
            <div class="space-y-4">
                <flux:heading size="lg">Cuentas SuperAdmin</flux:heading>
                <flux:text>Las cuentas privilegiadas se administran únicamente por otros SuperAdmin autorizados.</flux:text>
                @foreach($superAdmins as $super)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-zinc-200 p-3 dark:border-zinc-700" wire:key="super-{{ $super->id }}">
                        <div><p class="font-semibold">{{ $super->username }}</p><p class="break-all text-xs text-zinc-500">{{ $super->email }}</p></div>
                        <flux:button size="sm" wire:click="$dispatch('abrirModal', {id: {{ $super->id }}})">Editar</flux:button>
                    </div>
                @endforeach
            </div>
        </flux:modal>
    @endif
    <livewire:admin.usuarios.editar-usuario />
</div>
