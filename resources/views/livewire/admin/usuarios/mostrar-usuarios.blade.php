<div>

    <div x-data="{
        destroyUsuario(id, username) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `El usuario '${username}' se eliminará de forma permanente`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('eliminarUsuario', id);
                }
            });
        },
        inactivarUsuarios() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Los roles Estudiante y Profesor se inactivarán',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, inactivar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('inactivarUsuarios');
                }
            });
        },

        activarUsuarios() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Los roles Estudiante y Profesor se activarán',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, activar'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('activarUsuarios');
                }
            });
        },
         confirmarAccionMasiva(cantidad, id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Cambio de rol y se aplicará a ${cantidad} usuario(s).`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('cambioRolUsuariosSeleccionados', id);
            }
        });
    }
    }">


    <div class="flex flex-col md:flex-row gap-4 mb-4">
         <flux:field>
        <flux:label>Roles</flux:label>
        <flux:select wire:model.live="filtrar_roles">
            <flux:select.option value="">--Selecciona el rol---</flux:select.option>
            @foreach ($roles as $role)
                <flux:select.option value="{{ $role->name }}">{{ $role->name }}</flux:select.option>
            @endforeach


        </flux:select>
      </flux:field>
         <flux:field>
        <flux:label>Status</flux:label>
        <flux:select wire:model.live="filtrar_status">
            <flux:select.option value="">--Selecciona una opción---</flux:select.option>
            <flux:select.option value="Activo">Activo</flux:select.option>
            <flux:select.option value="Inactivo">Inactivo</flux:select.option>
        </flux:select>
      </flux:field>
    </div>



    <h3>Buscar Usuario:</h3>
    <flux:input type="text" wire:model.live="search" placeholder="Buscar Usuarios..." class=" p-2 mb-4 w-full" />
        <div class="overflow-x-auto">
            <div class="flex space-x-4 mb-4 p-1">

                @if($usuarios->isNotEmpty())

                     <flux:button wire:click="exportarUsuarios" variant="primary"  class="bg-green-700 hover:bg-green-800 focus:ring-4 dark:text-white">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
                        </div>
                </flux:button>


                <flux:button @click="inactivarUsuarios" variant="primary"  class="bg-red-700 hover:bg-red-800 focus:ring-4 dark:text-white">
                 <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                      </svg>
                     <span> Inactivar</span>
                </div>
                </flux:button>



                <flux:button @click="activarUsuarios" variant="primary"  class="bg-indigo-700 hover:bg-indigo-800 focus:ring-4 dark:text-white">
                 <div class="flex items-center gap-1">
                   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                     <span> Activar</span>
                </div>
                </flux:button>

                 <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>


            @if (count($selected) > 0)
                <div>

                    <flux:dropdown>
                    <flux:button variant="primary" class="bg-indigo-600 text-white px-4 py-2 rounded" icon:trailing="chevron-down"> Cambiar de rol ({{ count($selected) }})</flux:button>
                    <flux:menu>

                        @foreach ($roles as $role)
                            <flux:menu.item
                            @click="confirmarAccionMasiva({{ count($selected) }}, '{{ $role->id }}')" icon="user-group">
                                {{ $role->name }}
                            </flux:menu.item>
                        @endforeach

                    </flux:menu>
                </flux:dropdown>

                </div>

            @endif


            <flux:modal.trigger name="super-admin">
                   <flux:button> <flux:icon.user />  SuperAdmin  <flux:badge color="indigo">{{ $contar_super }}</flux:badge> </flux:button>
                </flux:modal.trigger>
                <flux:modal name="super-admin" variant="flyout">
                    <div class="space-y-6">
                        <div>
                            <flux:heading size="lg">Roles del SuperAdmin</flux:heading>
                            <flux:text class="mt-2">El SuperAdmin tiene acceso total a todas las funcionalidades del sistema.</flux:text>
                        </div>



                    <table class="min-w-full border border-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 bg-gray-100">#</th>
                                <th class="px-4 py-2 bg-gray-100">Username</th>
                                <th class="px-4 py-2 bg-gray-100">CURP</th>
                                <th class="px-4 py-2 bg-gray-100">Rol</th>
                                <th class="px-4 py-2 bg-gray-100"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($obtener_super as $key => $role)
                                <tr>
                                    <td class="px-4 py-2">{{ $key + 1 }}</td>
                                    <td class="px-4 py-2">{{ $role->username }}</td>
                                    <td class="px-4 py-2">{{ $role->CURP }}</td>
                                    <td class="px-4 py-2">{{ $role->email }}</td>
                                    <td class="px-4 py-2">
                                        <flux:button
                                        variant="primary" class="bg-red-500"
                                         @click="Livewire.dispatch('abrirModal', { id: {{ $role->id }} })">
                                            Editar
                                        </flux:button>

                                    </td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    </div>
                </flux:modal>






                @else

                    <flux:button disabled variant="primary"  class="bg-gray-100 hover:bg-gray-200 focus:ring-4 text-black">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                      </svg>

                        <span>Exportar</span>
                        </div>
                </flux:button>


                <flux:button disabled variant="primary"  class="bg-gray-100 hover:bg-gray-200 focus:ring-4 text-black">
                 <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                      </svg>
                     <span> Inactivar</span>
                </div>
                </flux:button>

                 <flux:button disabled variant="primary"  class="bg-gray-100 hover:bg-gray-200 focus:ring-4 text-black">
                 <div class="flex items-center gap-1">
                   <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                     <span> Activar</span>
                </div>
                </flux:button>

                <flux:button wire:click="limpiarFiltros" variant="primary">
                    <div class="flex items-center gap-1">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" />
                        </svg>

                        <span>Limpiar Filtros</span>
                        </div>
                </flux:button>

                @endif
            </div>


          @if ($usuarios->count())
            <table class="min-w-full border border-gray-200 table-striped ">
                <thead>
                    <tr>
                        <th class="px-4 py-2 bg-gray-100 text-center"><input type="checkbox" wire:model.live="selectAll"></th>
                        <th class="px-4 py-2 bg-gray-100">#</th>
                        <th class="px-4 py-2 bg-gray-100">Username</th>
                        <th class="px-4 py-2 bg-gray-100">CURP</th>
                        <th class="px-4 py-2 bg-gray-100">Email</th>
                        <th class="px-4 py-2 bg-gray-100">Status</th>
                        <th class="px-4 py-2 bg-gray-100">Roles</th>
                        <th class="px-4 py-2 bg-gray-100">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usuarios as $key => $usuario)
                        <tr class="{{ in_array($usuario->id, $selected) ? 'bg-blue-100' : '' }}">
                            <td class="px-4 py-2 text-center"><input type="checkbox" wire:model.live="selected" value="{{ $usuario->id }}"></td>
                            <td class="px-4 py-2 text-center">{{ $key + 1 }}</td>
                            <td class="px-4 py-2 text-center">{{ $usuario->username }}</td>
                            <td class="px-4 py-2 text-center">{{ $usuario->CURP }}</td>
                            <td class="px-4 py-2 text-center">{{ $usuario->email }}</td>
                            <td class="px-4 py-2 text-center">
                                @if ($usuario->status == 'true')
                                    <flux:badge color="green">Activo</flux:badge>
                                @else
                                    <flux:badge color="red">Inactivo</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                              @foreach ($usuario->roles as $role)
                                    @php
                                        $roleColors = [
                                            'SuperAdmin' => 'red',
                                            'Admin' => 'blue',
                                            'Profesor' => 'green',
                                            'Estudiante' => 'yellow',
                                            'Invitado' => 'purple',
                                        ];
                                        $color = $roleColors[$role->name] ?? 'gray';
                                    @endphp

                                    @if ($role->name !== 'SuperAdmin' || auth()->user()->hasRole('SuperAdmin'))
                                        <flux:badge color="{{ $color }}">{{ $role->name }}</flux:badge>
                                    @endif
                                @endforeach
                            </td>
                            <td class="px-4 py-2 text-center">
                                <flux:button class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
                                 @click="Livewire.dispatch('abrirModal', { id: {{ $usuario->id }} })">
                                    Editar
                                </flux:button>


                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>


            <div class="mt-4">
                {{ $usuarios->links() }}
            </div>
        @else
            <p class="text-gray-600">No se encontraron usuarios.</p>
        @endif
        </div>

    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.usuarios.editar-usuario />


</div>
