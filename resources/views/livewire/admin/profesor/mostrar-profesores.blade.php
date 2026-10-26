<div class="space-y-5"
    x-data="{
        async confirmarCambio(disponible, cantidad, asignaciones) {
            const texto = disponible
                ? `Se habilitará la disponibilidad de ${cantidad} docente(s), sin activar sus cuentas de acceso.`
                : `Se marcarán ${cantidad} docente(s) como no disponibles. Hay ${asignaciones} asignación(es) vinculadas; se conservarán intactas.`;
            const autorizado = typeof Swal !== 'undefined'
                ? (await Swal.fire({ title: disponible ? '¿Marcar como disponibles?' : '¿Marcar como no disponibles?', text: texto, icon: 'warning', showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar' })).isConfirmed
                : window.confirm(texto);
            if (autorizado) $wire.cambiarDisponibilidad(disponible);
        }
    }">
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @foreach([
            ['label' => 'Profesores encontrados', 'value' => $indicadores['total'], 'icon' => 'users', 'accent' => 'sky'],
            ['label' => 'Disponibles', 'value' => $indicadores['disponibles'], 'icon' => 'user-check', 'accent' => 'emerald'],
            ['label' => 'Con materias', 'value' => $indicadores['con_carga'], 'icon' => 'book-open', 'accent' => 'indigo'],
            ['label' => 'Acceso no activo', 'value' => $indicadores['acceso_pendiente'], 'icon' => 'lock-closed', 'accent' => 'amber'],
        ] as $dato)
            <div class="rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-2">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $dato['label'] }}</span>
                    @if($dato['icon']==='users') <flux:icon.users class="size-5 text-sky-600" />
                    @elseif($dato['icon']==='user-check') <flux:icon.user-check class="size-5 text-emerald-600" />
                    @elseif($dato['icon']==='book-open') <flux:icon.book-open class="size-5 text-indigo-600" />
                    @else <flux:icon.lock-closed class="size-5 text-amber-600" /> @endif
                </div>
                <div class="mt-2 text-2xl font-bold tabular-nums text-zinc-900 dark:text-white">{{ number_format($dato['value']) }}</div>
                <p class="mt-1 text-[11px] text-zinc-400">Según los filtros actuales</p>
            </div>
        @endforeach
    </div>

    <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="h-1 bg-gradient-to-r from-sky-700 via-cyan-500 to-lime-600"></div>
        <div class="space-y-4 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <flux:heading size="lg">Directorio docente</flux:heading>
                    <flux:text class="text-sm">Busca, filtra, edita y administra disponibilidad sin modificar el acceso de las cuentas.</flux:text>
                </div>
                <div class="flex flex-wrap gap-2">
                    <flux:button icon="arrow-down-tray" wire:click="exportarProfesores" wire:loading.attr="disabled" wire:target="exportarProfesores">Exportar Excel</flux:button>
                    <flux:button icon="arrow-path" variant="ghost" wire:click="limpiarFiltros">Limpiar</flux:button>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-6">
                    <flux:input wire:model.live.debounce.350ms="search" icon="magnifying-glass" label="Buscar profesor" placeholder="Nombre, CURP, correo, teléfono o perfil..." />
                </div>
                <div class="md:col-span-3">
                    <flux:select wire:model.live="filtrar_disponibilidad" label="Disponibilidad docente">
                        <option value="">Todas</option><option value="Disponible">Disponible</option><option value="No disponible">No disponible</option>
                    </flux:select>
                </div>
                <div class="md:col-span-3">
                    <flux:select wire:model.live="filtrar_status" label="Acceso al sistema">
                        <option value="">Todos</option><option value="Activo">Activo</option><option value="Inactivo">Inactivo / pendiente</option>
                    </flux:select>
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <div class="text-xs text-zinc-500">{{ $profesores->total() }} resultado(s) · {{ $profesores->firstItem() ?? 0 }}–{{ $profesores->lastItem() ?? 0 }} en pantalla</div>
                @if(count($selected) > 0)
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:badge color="sky">{{ count($selected) }} seleccionados</flux:badge>
                        <flux:button size="sm" variant="ghost" icon="check" @click="confirmarCambio(true, {{ count($selected) }}, {{ $this->impactoSeleccion }})">Disponible</flux:button>
                        <flux:button size="sm" variant="danger" icon="user-minus" @click="confirmarCambio(false, {{ count($selected) }}, {{ $this->impactoSeleccion }})">No disponible</flux:button>
                    </div>
                @else
                    <p class="text-xs text-zinc-500">Selecciona profesores para cambiar su disponibilidad (máximo 100).</p>
                @endif
                @error('selected')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="relative" wire:loading.class="opacity-60" wire:target="search,filtrar_status,filtrar_disponibilidad,cambiarDisponibilidad">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full text-left text-sm">
                    <thead class="border-y border-zinc-200 bg-zinc-50 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300">
                        <tr>
                            <th class="w-10 px-4 py-3"><input type="checkbox" wire:model.live="selectAll" aria-label="Seleccionar la página actual" class="size-4 rounded border-zinc-300 text-sky-700" /></th>
                            <th class="px-3 py-3">Profesor</th>
                            <th class="px-3 py-3">CURP / correo</th>
                            <th class="px-3 py-3">Perfil</th>
                            <th class="px-3 py-3 text-center">Materias</th>
                            <th class="px-3 py-3 text-center">Docencia</th>
                            <th class="px-3 py-3 text-center">Acceso</th>
                            <th class="px-3 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($profesores as $profesor)
                            <tr wire:key="docente-{{ $profesor->id }}" class="hover:bg-sky-50/40 dark:hover:bg-zinc-800/40">
                                <td class="px-4 py-3"><input type="checkbox" wire:model.live="selected" value="{{ $profesor->id }}" class="size-4 rounded border-zinc-300 text-sky-700" aria-label="Seleccionar {{ $profesor->nombre }}" /></td>
                                <td class="px-3 py-3">
                                    <div class="flex min-w-48 items-center gap-3">
                                        @if($profesor->foto)
                                            <button type="button" x-on:click="$dispatch('abrir-foto-docente',{src: @js(asset('storage/profesores/' . basename($profesor->foto))), nombre: @js($profesor->nombre)})" aria-label="Ampliar fotografía" class="shrink-0">
                                                <img src="{{ asset('storage/profesores/' . basename($profesor->foto)) }}" alt="Foto de {{ $profesor->nombre }}" class="size-11 rounded-xl object-cover" />
                                            </button>
                                        @else
                                            <div class="grid size-11 shrink-0 place-items-center rounded-xl bg-sky-100 dark:bg-sky-900/30"><flux:icon.user class="size-5 text-sky-600" /></div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }} {{ $profesor->nombre }}</div>
                                            <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500"><span class="size-2.5 shrink-0 rounded-full border" style="background-color: {{ preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $profesor->color) ? $profesor->color : '#94a3b8' }}"></span>Color de horario</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3"><div class="text-xs text-zinc-700 dark:text-zinc-300">{{ $profesor->user?->CURP ?? '—' }}</div><div class="mt-1 max-w-52 truncate text-xs text-zinc-500" title="{{ $profesor->user?->email }}">{{ $profesor->user?->email ?? '—' }}</div></td>
                                <td class="max-w-52 px-3 py-3 text-xs text-zinc-600 dark:text-zinc-300">{{ $profesor->perfil ?: 'Sin perfil' }}</td>
                                <td class="px-3 py-3 text-center"><flux:badge color="{{ $profesor->asignacion_materias_count > 0 ? 'blue' : 'zinc' }}">{{ $profesor->asignacion_materias_count }}</flux:badge></td>
                                <td class="px-3 py-3 text-center">
                                    @if($profesor->status === 'true') <flux:badge color="green">Disponible</flux:badge>
                                    @else <flux:badge color="amber">No disponible</flux:badge> @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($profesor->user?->status === 'true') <flux:badge color="green">Activo</flux:badge>
                                    @else <flux:badge color="zinc">Pendiente / inactivo</flux:badge> @endif
                                </td>
                                <td class="px-3 py-3 text-right"><flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="$dispatch('abrirProfesor', { id: {{ $profesor->id }} })">Editar</flux:button></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-12 text-center"><div class="font-semibold text-zinc-700 dark:text-zinc-200">No se encontraron profesores</div><p class="mt-1 text-sm text-zinc-500">Prueba con otros filtros o registra un nuevo expediente.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="border-t border-zinc-200 px-4 py-3 dark:border-zinc-700">{{ $profesores->links() }}</div>
    </section>

    <div x-data="{ foto: null, nombre: '' }" @abrir-foto-docente.window="foto = $event.detail.src; nombre = $event.detail.nombre"
         x-show="foto" x-cloak @keydown.escape.window="foto = null" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4" role="dialog" aria-modal="true" aria-label="Fotografía del profesor">
        <div class="relative max-w-xl" @click.outside="foto = null">
            <button type="button" @click="foto = null" class="absolute -right-3 -top-3 rounded-full bg-white p-2 text-zinc-900 shadow-lg" aria-label="Cerrar"><flux:icon.x-mark class="size-5" /></button>
            <img :src="foto" :alt="nombre" class="max-h-[82vh] max-w-full rounded-2xl object-contain" />
            <p class="mt-2 text-center text-sm text-white" x-text="nombre"></p>
        </div>
    </div>

    <livewire:admin.profesor.editar-profesor :wire:key="'editar-profesor-pro-2'" />
</div>
