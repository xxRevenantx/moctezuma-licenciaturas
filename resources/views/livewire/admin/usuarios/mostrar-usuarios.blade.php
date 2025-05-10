<div>
    <h3>Buscar Usuario:</h3>
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
    }">


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

                @endif





            </div>
            <table class="min-w-full border-collapse border border-gray-200 table-striped">
            <thead>
                <tr>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">ID</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Foto</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Username</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Email</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Status</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Roles</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700">Creado</th>
                <th class="border px-4 py-2 bg-gray-100 dark:bg-neutral-700"></th>
                </tr>
            </thead>
            <tbody>
                @if($usuarios->isEmpty())
                <tr>
                    <td colspan="7" class="border px-4 py-2 text-center">No hay Usuarios disponibles.</td>
                </tr>
                @else
                @foreach($usuarios as $key => $usuario)
                    <tr>
                    <td class="border px-4 py-2">{{ $key+1 }}</td>
                    <td class="border px-4 py-2">
                        @if ($usuario->photo)
                            <img src="{{ asset('storage/profile-photos/' . $usuario->photo) }}" alt="{{ $usuario->username }}" class="w-10 h-10 block m-auto">
                        @else
                           --------
                        @endif

                    </td>
                    <td class="border px-4 py-2">{{ $usuario->username }}</td>
                    <td class="border px-4 py-2">{{ $usuario->email }}</td>
                    <td class="border px-4 py-2">
                        @if ($usuario->status == "true")
                            <flux:badge color="green">Activo</flux:badge>
                        @else
                        <flux:badge color="red">Inactivo</flux:badge>
                        @endif
                    </td>


                        <td class="border px-4 py-2 space-y-4">
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



                    <td class="border px-4 py-2">
                        {{ \Carbon\Carbon::parse($usuario->created_at)->format('d/m/Y H:i') }}
                    </td>


                    <td class="border px-4 py-2">

                        <flux:button
                        variant="primary"
                        class="bg-yellow-500 text-white px-4 py-2 rounded cursor-pointer hover:bg-yellow-600"
                        @click="Livewire.dispatch('abrirModal', { id: {{ $usuario->id }} })"
                        >Editar
                    </flux:button>


                        @can('admin.usuarios.acciones')
                        <flux:button variant="danger"
                        @click="destroyUsuario({{ $usuario->id }}, '{{ $usuario->username }}')"
                        class="bg-red-500 text-white px-4 py-2 rounded cursor-pointer">
                        Eliminar
                        </flux:button>
                        @endcan
                    </td>





                    </tr>
                @endforeach
                @endif
            </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $usuarios->links() }}
        </div>
    </div>


    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.usuarios.editar-usuario />


</div>
