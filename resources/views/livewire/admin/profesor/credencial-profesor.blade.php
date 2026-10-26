<div class="space-y-5">
    {{-- Credenciales Docentes PRO: conserva las rutas y las plantillas PDF existentes. --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="h-1.5 bg-gradient-to-r from-[#006492] via-cyan-500 to-[#88AC2E]"></div>
        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
            <div class="flex items-start gap-4">
                <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-sky-50 text-[#006492] dark:bg-sky-900/25 dark:text-sky-300">
                    <flux:icon.identification class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.18em] text-[#75A326]">Control escolar · Documentación docente</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white">Credenciales Docentes PRO</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500 dark:text-zinc-300">
                        Localiza profesores, revisa sus fotografías y prepara credenciales individuales o masivas con vista previa del PDF institucional.
                    </p>
                </div>
            </div>
            <flux:button icon="arrow-path" variant="ghost" wire:click="limpiarFiltros">Restablecer filtros</flux:button>
        </div>
    </section>

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['nombre' => 'Profesores encontrados', 'valor' => $this->indicadores['total'], 'icono' => 'users', 'color' => 'text-sky-600'],
            ['nombre' => 'Disponibles', 'valor' => $this->indicadores['disponibles'], 'icono' => 'user-check', 'color' => 'text-emerald-600'],
            ['nombre' => 'Con fotografía', 'valor' => $this->indicadores['con_foto'], 'icono' => 'photo', 'color' => 'text-indigo-600'],
            ['nombre' => 'Seleccionados', 'valor' => $this->indicadores['seleccionados'], 'icono' => 'check-circle', 'color' => 'text-[#75A326]'],
        ] as $dato)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">{{ $dato['nombre'] }}</p>
                    @if($dato['icono'] === 'users') <flux:icon.users class="size-5 {{ $dato['color'] }}" />
                    @elseif($dato['icono'] === 'user-check') <flux:icon.user-check class="size-5 {{ $dato['color'] }}" />
                    @elseif($dato['icono'] === 'photo') <flux:icon.photo class="size-5 {{ $dato['color'] }}" />
                    @else <flux:icon.check-circle class="size-5 {{ $dato['color'] }}" /> @endif
                </div>
                <div class="mt-2 text-2xl font-black tabular-nums text-slate-900 dark:text-white">{{ number_format($dato['valor']) }}</div>
                <p class="mt-1 text-[11px] text-slate-400">{{ $dato['nombre'] === 'Seleccionados' ? 'Máximo 100 por PDF' : 'Según los filtros actuales' }}</p>
            </div>
        @endforeach
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div class="mb-4">
            <h2 class="text-base font-black text-slate-900 dark:text-white"><span class="mr-2 inline-grid size-7 place-items-center rounded-full bg-[#006492] text-xs text-white">1</span>Tipo de credencial</h2>
            <p class="ml-9 mt-1 text-sm text-slate-500">Selecciona uno de los dos formatos institucionales existentes.</p>
        </div>
        <div class="grid gap-3 md:grid-cols-2">
            <button type="button" wire:click="cambiarTipo('docente')" aria-pressed="{{ $tipo === 'docente' ? 'true' : 'false' }}"
                class="relative rounded-2xl border p-4 text-left transition {{ $tipo === 'docente' ? 'border-sky-600 bg-sky-50 ring-2 ring-sky-100 dark:bg-sky-950/30' : 'border-slate-200 hover:border-sky-300 hover:bg-slate-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-xl bg-[#006492] text-white"><flux:icon.identification class="size-5" /></div>
                    <div><p class="font-bold text-slate-900 dark:text-white">Credencial de profesor</p><p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Identificación docente institucional</p></div>
                    @if($tipo === 'docente')<flux:icon.check-circle class="ml-auto size-5 text-sky-700" />@endif
                </div>
            </button>
            <button type="button" wire:click="cambiarTipo('estudiante')" aria-pressed="{{ $tipo === 'estudiante' ? 'true' : 'false' }}"
                class="relative rounded-2xl border p-4 text-left transition {{ $tipo === 'estudiante' ? 'border-sky-600 bg-sky-50 ring-2 ring-sky-100 dark:bg-sky-950/30' : 'border-slate-200 hover:border-sky-300 hover:bg-slate-50 dark:border-zinc-700 dark:hover:bg-zinc-800' }}">
                <div class="flex items-center gap-3">
                    <div class="grid size-10 place-items-center rounded-xl bg-[#75A326] text-white"><flux:icon.academic-cap class="size-5" /></div>
                    <div><p class="font-bold text-slate-900 dark:text-white">Profesor como estudiante</p><p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">Plantilla especial con licenciatura</p></div>
                    @if($tipo === 'estudiante')<flux:icon.check-circle class="ml-auto size-5 text-sky-700" />@endif
                </div>
            </button>
        </div>
        @if($tipo === 'estudiante')
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-950/25">
                <div class="max-w-xl"><flux:select wire:model.live="licenciaturaId" label="Licenciatura que aparecerá en la credencial" placeholder="Selecciona una licenciatura">
                    <option value="">Selecciona una licenciatura</option>
                    @foreach($licenciaturas as $licenciatura)
                        <option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</option>
                    @endforeach
                </flux:select></div>
                <p class="mt-2 text-xs text-amber-900 dark:text-amber-200">Este formato utiliza la licenciatura que indiques; no verifica ni crea una inscripción académica.</p>
                @error('licenciaturaId')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endif
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-4 p-5 sm:p-6">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white"><span class="mr-2 inline-grid size-7 place-items-center rounded-full bg-[#006492] text-xs text-white">2</span>Seleccionar profesores</h2>
                <p class="ml-9 mt-1 text-sm text-slate-500">Busca por nombre, apellidos o CURP; filtra y selecciona personas individuales o toda la página.</p>
            </div>
            <div class="grid gap-3 md:grid-cols-12">
                <div class="md:col-span-6"><flux:input wire:model.live.debounce.350ms="busqueda" label="Buscar profesor" type="search" icon="magnifying-glass" placeholder="Nombre completo, apellidos o CURP..." /></div>
                <div class="md:col-span-3"><flux:select wire:model.live="disponibilidad" label="Disponibilidad">
                    <option value="">Todos</option><option value="true">Disponible</option><option value="false">No disponible</option>
                </flux:select></div>
                <div class="md:col-span-3"><flux:select wire:model.live="fotografia" label="Fotografía">
                    <option value="">Todas</option><option value="con">Con fotografía</option><option value="sin">Sin fotografía</option>
                </flux:select></div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4 text-xs text-slate-500 dark:border-zinc-800">
                <span>{{ $this->profesores->total() }} resultado(s) · {{ $this->profesores->firstItem() ?? 0 }}–{{ $this->profesores->lastItem() ?? 0 }} en pantalla</span>
                <div class="flex flex-wrap items-center gap-3">
                    @if($this->profesores->total() > 0 && $this->profesores->total() <= 100)
                        <button type="button" wire:click="seleccionarResultados" class="font-semibold text-sky-700 hover:underline dark:text-sky-300">Seleccionar {{ $this->profesores->total() }} resultado(s)</button>
                    @endif
                <label class="flex cursor-pointer items-center gap-2 font-semibold text-slate-700 dark:text-zinc-300">
                    <input type="checkbox" wire:model.live="seleccionarPagina" class="size-4 rounded border-slate-300 text-sky-700" aria-label="Seleccionar únicamente la página actual" />
                    Seleccionar página actual
                </label>
                </div>
            </div>
            @error('seleccionados')<p role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>@enderror
        </div>
        <div wire:loading.class="opacity-50" wire:target="busqueda,disponibilidad,fotografia,seleccionados,seleccionarPagina" class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead class="border-y border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500 dark:border-zinc-700 dark:bg-zinc-800/60">
                    <tr><th class="w-12 px-4 py-3">Sel.</th><th class="px-3 py-3">Profesor</th><th class="px-3 py-3">CURP</th><th class="px-3 py-3">Perfil</th><th class="px-3 py-3">Fotografía</th><th class="px-3 py-3">Disponibilidad</th><th class="px-3 py-3 text-right">Acciones</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-zinc-800">
                    @forelse($this->profesores as $profesor)
                        <tr wire:key="profesor-credencial-{{ $profesor->id }}" class="hover:bg-sky-50/50 dark:hover:bg-zinc-800/40">
                            <td class="px-4 py-3"><input type="checkbox" wire:model.live="seleccionados" value="{{ $profesor->id }}" class="size-4 rounded border-slate-300 text-sky-700" aria-label="Seleccionar a {{ $profesor->nombre }}" /></td>
                            <td class="px-3 py-3">
                                <div class="flex min-w-48 items-center gap-3">
                                    @if($profesor->foto)
                                        <img src="{{ asset('storage/profesores/' . basename($profesor->foto)) }}" class="size-11 rounded-xl border border-slate-200 object-cover" loading="lazy" alt="Foto de {{ $profesor->nombre }}" />
                                    @else
                                        <div class="grid size-11 place-items-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/40"><flux:icon.user class="size-5" /></div>
                                    @endif
                                    <div><p class="font-semibold text-slate-900 dark:text-white">{{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }} {{ $profesor->nombre }}</p><p class="mt-1 text-xs text-slate-400">ID docente: {{ $profesor->id }}</p></div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-xs text-slate-600 dark:text-zinc-300">{{ $profesor->user?->CURP ?? 'Sin CURP' }}</td>
                            <td class="max-w-44 px-3 py-3 text-xs text-slate-600 dark:text-zinc-300">{{ $profesor->perfil ?: 'Sin perfil' }}</td>
                            <td class="px-3 py-3">@if($profesor->foto)<flux:badge color="green">Registrada</flux:badge>@else<flux:badge color="amber">Pendiente</flux:badge>@endif</td>
                            <td class="px-3 py-3">@if($profesor->status === 'true')<flux:badge color="green">Disponible</flux:badge>@else<flux:badge color="zinc">No disponible</flux:badge>@endif</td>
                            <td class="px-3 py-3 text-right"><flux:button size="sm" variant="ghost" icon="eye" wire:click="abrirVistaPrevia({{ $profesor->id }})">Vista previa</flux:button></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-12 text-center"><p class="font-semibold text-slate-700 dark:text-zinc-200">No hay profesores que coincidan</p><p class="mt-1 text-sm text-slate-500">Prueba otros filtros o limpia la búsqueda.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4 dark:border-zinc-800">{{ $this->profesores->links() }}</div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
            <div>
                <h2 class="text-base font-black text-slate-900 dark:text-white"><span class="mr-2 inline-grid size-7 place-items-center rounded-full bg-[#006492] text-xs text-white">3</span>Preparar PDF</h2>
                <p class="ml-9 mt-1 text-sm text-slate-500">{{ count($seleccionados) }} de {{ \App\Livewire\Admin\Profesor\CredencialProfesor::MAX_CREDENCIALES }} profesores seleccionados.</p>
            </div>
            @if(count($seleccionados))<flux:button variant="ghost" icon="x-mark" wire:click="limpiarSeleccion">Vaciar selección</flux:button>@endif
        </div>
        @if(count($seleccionados))
            <div class="mt-4 flex max-h-40 flex-wrap gap-2 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                @foreach($this->seleccionDetalle as $persona)
                    <span wire:key="credencial-chip-{{ $persona->id }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                        {{ $persona->apellido_paterno }} {{ $persona->apellido_materno }} {{ $persona->nombre }}
                        <button type="button" wire:click="quitarSeleccionado({{ $persona->id }})" class="rounded text-red-600 hover:text-red-800" aria-label="Quitar a {{ $persona->nombre }}"><flux:icon.x-mark class="size-4" /></button>
                    </span>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <flux:button icon="eye" variant="outline" wire:click="abrirVistaPrevia" wire:loading.attr="disabled">Vista previa del lote</flux:button>
                <form action="{{ $tipo === 'estudiante' ? route('admin.pdf.documentacion.credencial_profesor_estudiante') : route('admin.pdf.documentacion.credencial_profesor') }}" method="GET" target="_blank">
                    @foreach($seleccionados as $id)<input type="hidden" name="profesores_ids[]" value="{{ (int) $id }}">@endforeach
                    @if($tipo === 'estudiante')<input type="hidden" name="licenciatura_id" value="{{ $licenciaturaId }}">@endif
                    <flux:button type="submit" variant="primary" icon="arrow-down-tray" :disabled="$tipo === 'estudiante' && !$licenciaturaId" class="!bg-[#006492] hover:!bg-[#00547b]">Abrir PDF para imprimir</flux:button>
                </form>
            </div>
            @if($tipo === 'estudiante' && !$licenciaturaId)<p class="mt-2 text-xs font-semibold text-amber-700">Debes elegir la licenciatura antes de generar el PDF especial.</p>@endif
        @else
            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center dark:border-zinc-700 dark:bg-zinc-800/30">
                <flux:icon.identification class="mx-auto size-8 text-slate-400" />
                <p class="mt-2 font-semibold text-slate-700 dark:text-zinc-200">Todavía no has seleccionado profesores</p>
                <p class="mt-1 text-sm text-slate-500">Marca las casillas del directorio para generar un lote de credenciales.</p>
            </div>
        @endif
    </section>

    <flux:modal name="vista-previa-credencial-profesor" wire:model="vistaPrevia" class="w-full md:max-w-5xl">
        <div class="space-y-3">
            <div><flux:heading size="lg">Vista previa de credenciales</flux:heading><flux:text class="text-sm">Esta es la plantilla PDF real utilizada por el sistema; no se alteraron las medidas de impresión.</flux:text></div>
            @if($vistaPrevia && $this->urlPdf)
                <iframe wire:key="pdf-docente-{{ $tipo }}-{{ $previewIndividualId ?? 'lote' }}-{{ md5($this->urlPdf) }}" src="{{ $this->urlPdf }}" class="h-[68vh] min-h-80 w-full rounded-xl border border-slate-200 bg-white" title="Vista previa PDF de credenciales de profesores"></iframe>
                <div class="flex flex-wrap justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cerrarVistaPrevia">Cerrar</flux:button>
                    <flux:button href="{{ $this->urlPdf }}" target="_blank" variant="primary" icon="arrow-top-right-on-square" class="!bg-[#006492]">Abrir PDF en otra pestaña</flux:button>
                </div>
            @endif
        </div>
    </flux:modal>
</div>
