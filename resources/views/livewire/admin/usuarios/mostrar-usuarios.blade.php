<div>
  <style>[x-cloak]{ display:none !important; }</style>

  <div
    x-data="{
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
          if (result.isConfirmed) { @this.call('eliminarUsuario', id); }
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
          if (result.isConfirmed) { @this.call('inactivarUsuarios'); }
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
          if (result.isConfirmed) { @this.call('activarUsuarios'); }
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
          if (result.isConfirmed) { @this.call('cambioRolUsuariosSeleccionados', id); }
        });
      }
    }"
    class="w-full  mx-auto"
  >

    <!-- Filtros -->
    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg overflow-hidden mb-4">
      <div class="h-1.5 w-full bg-gradient-to-r from-indigo-500 via-violet-500 to-fuchsia-500"></div>
      <div class="p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
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

          <!-- Buscar -->
          <flux:field class="sm:col-span-2 lg:col-span-1">
            <flux:label>Buscar Usuario</flux:label>
            <div class="relative">
              <flux:input type="text" wire:model.live="search" placeholder="Buscar Usuarios..." class="pl-10" />
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M15.5 14h-.79l-.28-.27A6.5 6.5 0 1 0 9.5 16a6.47 6.47 0 0 0 4.23-1.57l.27.28v.79L20 21.5 21.5 20l-6-6zM4 9.5C4 6.46 6.46 4 9.5 4S15 6.46 15 9.5 12.54 15 9.5 15 4 12.54 4 9.5z"/>
              </svg>
              <span class="absolute right-3 top-1/2 -translate-y-1/2" wire:loading wire:target="search">
                <span class="w-4 h-4 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent inline-block animate-spin"></span>
              </span>
            </div>
          </flux:field>
        </div>
      </div>
    </div>

    <!-- Acciones -->
    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-lg overflow-hidden mb-4">
      <div class="p-3 sm:p-4">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">

          @if($usuarios->isNotEmpty())
            <!-- Exportar -->
            <flux:button wire:click="exportarUsuarios" variant="primary"
              class="bg-emerald-600 hover:bg-emerald-700 focus:ring-4 dark:text-white"
              wire:loading.attr="disabled" wire:target="exportarUsuarios">
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M19.5 14.25v-2.625A3.375 3.375 0 0016.125 8.25h-1.5A1.125 1.125 0 0113.5 7.125v-1.5A3.375 3.375 0 0010.125 2.25H8.25m.75 12l3 3m0 0l3-3m-3 3v-6M7.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>Exportar</span>
              </div>
            </flux:button>

            <!-- Inactivar -->
            <flux:button @click="inactivarUsuarios" variant="primary"
              class="bg-red-600 hover:bg-red-700 focus:ring-4 dark:text-white">
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                </svg>
                <span>Inactivar</span>
              </div>
            </flux:button>

            <!-- Activar -->
            <flux:button @click="activarUsuarios" variant="primary"
              class="bg-indigo-600 hover:bg-indigo-700 focus:ring-4 dark:text-white">
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Activar</span>
              </div>
            </flux:button>

            <!-- Limpiar filtros -->
            <flux:button wire:click="limpiarFiltros" variant="primary" class="bg-neutral-200 hover:bg-neutral-300 text-neutral-900 dark:text-neutral-900">
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044c0 .422-.168.828-.467 1.127l-5.624 5.624a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <span>Limpiar Filtros</span>
              </div>
            </flux:button>

            <!-- Dropdown cambio de rol (si hay seleccionados) -->
            @if (count($selected) > 0)
              <flux:dropdown>
                <flux:button variant="primary" class="bg-violet-600 hover:bg-violet-700 text-white" icon:trailing="chevron-down">
                  Cambiar de rol ({{ count($selected) }})
                </flux:button>
                <flux:menu>
                  @foreach ($roles as $role)
                    <flux:menu.item @click="confirmarAccionMasiva({{ count($selected) }}, '{{ $role->id }}')" icon="user-group">
                      {{ $role->name }}
                    </flux:menu.item>
                  @endforeach
                </flux:menu>
              </flux:dropdown>
            @endif

            <!-- Modal SuperAdmin -->
            <flux:modal.trigger name="super-admin">
              <flux:button class="bg-neutral-100 hover:bg-neutral-200 text-neutral-800">
                <flux:icon.user /> SuperAdmin
                <flux:badge color="indigo">{{ $contar_super }}</flux:badge>
              </flux:button>
            </flux:modal.trigger>
          @else
            <!-- Estados deshabilitados cuando no hay usuarios -->
            <flux:button disabled variant="primary" class="bg-neutral-200 text-neutral-700">Exportar</flux:button>
            <flux:button disabled variant="primary" class="bg-neutral-200 text-neutral-700">Inactivar</flux:button>
            <flux:button disabled variant="primary" class="bg-neutral-200 text-neutral-700">Activar</flux:button>
            <flux:button wire:click="limpiarFiltros" variant="primary" class="bg-neutral-200 text-neutral-900">Limpiar Filtros</flux:button>
          @endif
        </div>
      </div>
    </div>

    <!-- Modal SuperAdmin -->
    <flux:modal name="super-admin" variant="flyout">
      <div class="space-y-6">
        <div>
          <flux:heading size="lg">Roles del SuperAdmin</flux:heading>
          <flux:text class="mt-2">El SuperAdmin tiene acceso total a todas las funcionalidades del sistema.</flux:text>
        </div>

        <div class="overflow-x-auto rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700">
          <table class="min-w-full text-sm">
            <thead class="bg-neutral-100 dark:bg-neutral-700/60 text-neutral-700 dark:text-neutral-100">
              <tr>
                <th class="px-4 py-2 text-left">#</th>
                <th class="px-4 py-2 text-left">Username</th>
                <th class="px-4 py-2 text-left">CURP</th>
                <th class="px-4 py-2 text-left">Rol</th>
                <th class="px-4 py-2"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
              @foreach ($obtener_super as $key => $role)
                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/60">
                  <td class="px-4 py-2">{{ $key + 1 }}</td>
                  <td class="px-4 py-2">{{ $role->username }}</td>
                  <td class="px-4 py-2">{{ $role->CURP }}</td>
                  <td class="px-4 py-2"><flux:badge color="red">SuperAdmin</flux:badge></td>
                  <td class="px-4 py-2">
                    <flux:button variant="primary" class="bg-yellow-500 hover:bg-yellow-600"
                      @click="Livewire.dispatch('abrirModal', { id: {{ $role->id }} })">
                      Editar
                    </flux:button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </flux:modal>

    <!-- Tabla principal -->
    <div class="overflow-x-auto rounded-2xl ring-1 ring-neutral-200 dark:ring-neutral-700 bg-white dark:bg-neutral-800 shadow-lg">
      @if ($usuarios->count())
        <table class="min-w-full text-sm">
          <thead class="sticky top-0 z-10 bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow">
            <tr>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase w-10">
                <input type="checkbox" wire:model.live="selectAll" class="rounded">
              </th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase w-14">#</th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Nombre de usuario</th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">CURP</th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Email</th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Status</th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Roles</th>
              <th class="px-3 py-3 text-left text-[11px] tracking-wider uppercase">Acciones</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-neutral-100 dark:divide-neutral-700">
            @foreach ($usuarios as $key => $usuario)
              <tr class="group hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors {{ in_array($usuario->id, $selected) ? 'bg-indigo-50/70 dark:bg-indigo-900/20' : '' }}">
                <td class="px-3 py-2 align-middle">
                  <input type="checkbox" wire:model.live="selected" value="{{ $usuario->id }}" class="rounded">
                </td>
                <td class="px-3 py-2 align-middle text-neutral-700 dark:text-neutral-200">{{ $key + 1 }}</td>
                <td class="px-3 py-2 align-middle text-neutral-800 dark:text-neutral-100">{{ $usuario->username }}</td>
                <td class="px-3 py-2 align-middle text-neutral-700 dark:text-neutral-200">{{ $usuario->CURP }}</td>
                <td class="px-3 py-2 align-middle text-neutral-700 dark:text-neutral-200">{{ $usuario->email }}</td>
                <td class="px-3 py-2 align-middle">
                  @if ($usuario->status == 'true')
                    <flux:badge color="green">Activo</flux:badge>
                  @else
                    <flux:badge color="red">Inactivo</flux:badge>
                  @endif
                </td>
                <td class="px-3 py-2 align-middle">
                  <div class="flex flex-wrap gap-1.5">
                    @foreach ($usuario->roles as $role)
                      @php
                        $roleColors = [
                          'SuperAdmin' => 'red', 'Admin' => 'blue', 'Profesor' => 'green',
                          'Estudiante' => 'yellow', 'Invitado' => 'purple',
                        ];
                        $color = $roleColors[$role->name] ?? 'zinc';
                      @endphp
                      @if ($role->name !== 'SuperAdmin' || auth()->user()->hasRole('SuperAdmin'))
                        <flux:badge color="{{ $color }}">{{ $role->name }}</flux:badge>
                      @endif
                    @endforeach
                  </div>
                </td>
                <td class="px-3 py-2 align-middle">
                  <div class="flex items-center gap-2">
                    <flux:button variant="primary"
                      class="bg-yellow-500 hover:bg-yellow-600"
                      @click="Livewire.dispatch('abrirModal', { id: {{ $usuario->id }} })">
                      Editar
                    </flux:button>
                    <!-- Si quieres mostrar eliminar:
                    <flux:button class="bg-red-600 hover:bg-red-700 text-white"
                      @click="destroyUsuario({{ $usuario->id }}, '{{ $usuario->username }}')">
                      Eliminar
                    </flux:button>
                    -->
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <!-- Paginación -->
        <div class="px-3 sm:px-4 py-3 sm:py-4">
          {{ $usuarios->links() }}
        </div>
      @else
        <div class="p-6 text-center text-neutral-600 dark:text-neutral-300">
          No se encontraron usuarios.
        </div>
      @endif

      <!-- Overlay de carga para acciones comunes -->
      <div class="absolute inset-0 pointer-events-none" wire:loading
           wire:target="search,filtrar_roles,filtrar_status,exportarUsuarios,inactivarUsuarios,activarUsuarios,cambioRolUsuariosSeleccionados,eliminarUsuario">
        <div class="w-full h-full bg-white/60 dark:bg-neutral-900/50 backdrop-blur-[1px] flex items-center justify-center rounded-2xl">
          <span class="inline-flex items-center gap-3 px-4 py-2 rounded-xl ring-1 ring-neutral-200 dark:ring-neutral-700 bg-white dark:bg-neutral-800 shadow">
            <span class="w-6 h-6 rounded-full border-2 border-neutral-300 dark:border-neutral-600 border-t-transparent animate-spin"></span>
            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Actualizando…</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Espaciado -->
    <div class="h-4"></div>

    {{-- MODAL PARA EDITAR --}}
    <livewire:admin.usuarios.editar-usuario />
  </div>
</div>
