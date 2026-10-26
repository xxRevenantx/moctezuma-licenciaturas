<div class="space-y-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="font-bold text-lg text-[#006492] dark:text-sky-300">Solicitudes de matrículas · SEG</h3>
            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Genera relaciones oficiales en Excel y Word. Las matrículas registradas no se modifican.</p>
        </div>
        <span class="self-start rounded-full bg-[#88AC2E]/15 px-3 py-1 text-xs font-semibold text-[#608020] dark:text-lime-300">Fases 1 y 2 · Expedición</span>
    </div>

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">
            <p class="font-semibold">Revisa la selección antes de expedir:</p>
            <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 dark:bg-sky-950/20 dark:border-sky-900">
            <p class="text-xs text-sky-800 dark:text-sky-200">Alumnos con filtros</p>
            <p class="text-2xl font-black text-[#006492] dark:text-sky-300">{{ $alumnos->count() }}</p>
        </div>
        <div class="rounded-2xl border border-lime-100 bg-lime-50 px-4 py-3 dark:bg-lime-950/20 dark:border-lime-900">
            <p class="text-xs text-lime-800 dark:text-lime-200">Seleccionados</p>
            <p class="text-2xl font-black text-[#638321] dark:text-lime-300">{{ count($alumnoIds) }}</p>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 dark:bg-neutral-800 dark:border-neutral-700">
            <p class="text-xs text-neutral-500">Licenciaturas disponibles</p>
            <p class="text-2xl font-black text-neutral-900 dark:text-white">{{ $licenciaturas->count() }}</p>
        </div>
        <div class="rounded-2xl border border-neutral-200 bg-neutral-50 px-4 py-3 dark:bg-neutral-800 dark:border-neutral-700">
            <p class="text-xs text-neutral-500">CURP repetidas (grupos)</p>
            <p class="text-2xl font-black {{ $curpsRepetidas ? 'text-amber-600' : 'text-neutral-900 dark:text-white' }}">{{ $curpsRepetidas }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:select wire:model.live="generacionId" label="Generación" class="w-full">
            <flux:select.option value="">Selecciona una generación</flux:select.option>
            @foreach ($generaciones as $g)
                <flux:select.option value="{{ $g->id }}">{{ $g->generacion }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="seg" label="Matrícula oficial SEG" class="w-full">
            <flux:select.option value="pendientes">Pendientes (sin SEG)</flux:select.option>
            <flux:select.option value="asignados">Asignadas (con SEG)</flux:select.option>
            <flux:select.option value="todos">Todos</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="estado" label="Situación escolar" class="w-full">
            <flux:select.option value="activos">Activos</flux:select.option>
            <flux:select.option value="bajas">Bajas</flux:select.option>
            <flux:select.option value="todos">Todos</flux:select.option>
        </flux:select>
        <flux:select wire:model.live="modalidadId" label="Modalidad" class="w-full">
            <flux:select.option value="">Todas las modalidades</flux:select.option>
            @foreach ($modalidades as $m)
                <flux:select.option value="{{ $m->id }}">{{ $m->nombre }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($generacionId !== '')
        <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 p-4 space-y-3">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <h4 class="font-semibold text-sm">Licenciaturas de esta generación con alumnos</h4>
                <span class="text-xs text-neutral-500">Selecciona una o varias carreras</span>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($licenciaturas as $lic)
                    <label wire:key="lic-mat-{{ $lic->id }}" class="flex items-start gap-3 rounded-xl border border-neutral-200 p-3 cursor-pointer hover:border-[#88AC2E] dark:border-neutral-700">
                        <input type="checkbox" wire:model.live="licenciaturaIds" value="{{ $lic->id }}" class="mt-1 rounded border-neutral-300 text-[#006492] focus:ring-[#006492]" />
                        <span class="min-w-0"><span class="block text-sm font-medium text-neutral-900 dark:text-white">{{ $lic->nombre }}</span>
                            <span class="block text-xs text-neutral-500">{{ $lic->alumnos_count }} alumnos · {{ $lic->RVOE ? 'RVOE registrado' : 'Sin RVOE registrado' }}</span></span>
                    </label>
                @empty
                    <p class="text-sm text-neutral-500">No hay licenciaturas elegibles para esta generación.</p>
                @endforelse
            </div>
        </div>
    @endif

    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 p-4 space-y-3">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
            <div>
                <h4 class="text-sm font-semibold">Alumnos de la solicitud</h4>
                <p class="text-xs text-neutral-500">Orden alfabético por apellidos. Puedes seleccionar o excluir alumnos de la expedición.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="seleccionarTodos" class="rounded-lg border border-[#006492] px-3 py-2 text-xs font-semibold text-[#006492]">Seleccionar todos</button>
                <button type="button" wire:click="limpiarAlumnos" class="rounded-lg border border-neutral-300 px-3 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300">Limpiar</button>
            </div>
        </div>
        <div class="grid gap-2 sm:grid-cols-2 rounded-xl bg-neutral-50 dark:bg-neutral-800 p-3">
            <div class="flex gap-2 items-end">
                <div class="grow"><flux:select wire:model="mesMasivo" label="Mes masivo (seleccionados)" class="w-full">
                    <flux:select.option value="">Elige un mes</flux:select.option>
                    @foreach ($mesesDisponibles as $mes)
                        <flux:select.option value="{{ $mes }}">{{ $mes }}</flux:select.option>
                    @endforeach
                </flux:select></div>
                <button type="button" wire:click="aplicarMesMasivo" class="rounded-lg bg-[#006492] px-3 py-2 text-xs font-semibold text-white">Aplicar</button>
            </div>
            <div class="flex gap-2 items-end">
                <div class="grow"><flux:input wire:model="observacionMasiva" label="Observación masiva" maxlength="500" placeholder="Texto para seleccionados" /></div>
                <button type="button" wire:click="aplicarObservacionMasiva" class="rounded-lg bg-[#006492] px-3 py-2 text-xs font-semibold text-white">Aplicar</button>
            </div>
        </div>
        <flux:input wire:model.live.debounce.300ms="busqueda" placeholder="Buscar alumno por nombre, apellidos, CURP o matrícula..." icon="magnifying-glass" />
        @if ($curpsRepetidas > 0)
            <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800 dark:bg-amber-950/30 dark:text-amber-200">Atención: hay CURP repetidas entre los alumnos que cumplen los filtros. Revisa los expedientes; no se eliminan registros automáticamente.</p>
        @endif
        @if ($totalVisibles > 100)
            <p class="text-xs text-amber-700">Se muestran los primeros 100 de {{ $totalVisibles }} resultados. La selección y exportación abarcan todos los alumnos que correspondan; utiliza la búsqueda para editar otros alumnos.</p>
        @endif
        <div class="max-h-[400px] overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <table class="min-w-[820px] w-full text-xs">
                <thead class="sticky top-0 z-10 bg-[#006492] text-white">
                    <tr><th class="px-3 py-3 text-left">Incluir</th><th class="px-3 py-3 text-left">Orden</th><th class="px-3 py-3 text-left">Alumno</th><th class="px-3 py-3 text-left">CURP</th><th class="px-3 py-3 text-left">Carrera</th><th class="px-3 py-3 text-left">Mes de ingreso</th><th class="px-3 py-3 text-left">Observaciones</th></tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($alumnosVisibles as $alumno)
                        <tr wire:key="matricula-solicitud-{{ $alumno->id }}" class="hover:bg-neutral-50 dark:hover:bg-neutral-800/60">
                            <td class="px-3 py-2"><input type="checkbox" wire:model.live="alumnoIds" value="{{ $alumno->id }}" class="rounded border-neutral-300 text-[#006492]" /></td>
                            <td class="px-3 py-2 whitespace-nowrap"><button type="button" wire:click="moverAlumno({{ $alumno->id }}, -1)" class="rounded border px-1.5" title="Subir">↑</button><button type="button" wire:click="moverAlumno({{ $alumno->id }}, 1)" class="rounded border px-1.5" title="Bajar">↓</button></td>
                            <td class="px-3 py-2 font-medium">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                            <td class="px-3 py-2 font-mono">{{ $alumno->CURP ?: 'Sin CURP' }}</td>
                            <td class="px-3 py-2">{{ $alumno->licenciatura?->nombre }}</td>
                            <td class="px-3 py-2">
                                <select name="meses[{{ $alumno->id }}]" form="matriculas-export-form" wire:model.change="meses.{{ $alumno->id }}" class="w-full rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-xs text-neutral-900 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white">
                                    @php($mesActual = $meses[$alumno->id] ?? app(\App\Services\Matriculas\ExpedicionMatriculasService::class)->mesSugerido($alumno))
                                    <option value="">Sin definir</option>
                                    @foreach ($mesesDisponibles as $mes)
                                        <option value="{{ $mes }}" @selected($mesActual === $mes)>{{ $mes }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2"><input type="text" maxlength="500" name="observaciones[{{ $alumno->id }}]" form="matriculas-export-form" wire:model.blur="observaciones.{{ $alumno->id }}" placeholder="Observación opcional" class="w-full min-w-[170px] rounded-lg border border-neutral-300 bg-white px-2 py-1.5 text-xs text-neutral-900 dark:border-neutral-600 dark:bg-neutral-800 dark:text-white" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-7 text-center text-sm text-neutral-500">Selecciona una generación o ajusta los filtros para ver estudiantes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="text-xs text-neutral-500">El mes de ingreso se sugiere a partir de la fecha de registro y puede ser diferente al ingreso real. Verifícalo antes de enviar a la SEG. Los cambios de esta tabla solo afectan la descarga.</p>
    </div>

    <div class="rounded-2xl border border-neutral-200 dark:border-neutral-700 p-4 space-y-3">
        <div class="flex flex-wrap justify-between gap-2 items-center">
            <div><h4 class="text-sm font-semibold">Columnas del documento</h4><p class="text-xs text-neutral-500">Activa las columnas y cambia su orden. Plantilla predeterminada: la solicitud SEG de referencia.</p></div>
            <button type="button" wire:click="restaurarColumnas" class="text-xs font-semibold text-[#006492] dark:text-sky-300 underline">Restaurar formato original</button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach ($ordenColumnas as $i => $col)
                <div wire:key="matcol-{{ $col }}" class="flex items-center justify-between gap-2 rounded-lg border border-neutral-200 p-2 dark:border-neutral-700">
                    <label class="flex items-center gap-2 text-xs cursor-pointer min-w-0">
                        <input type="checkbox" wire:model.live="columnasSeleccionadas" value="{{ $col }}" class="rounded border-neutral-300 text-[#006492]" />
                        <span>{{ $columnasDisponibles[$col] }}</span>
                    </label>
                    <div class="flex gap-1 shrink-0">
                        <button type="button" wire:click="moverColumna('{{ $col }}', -1)" title="Subir columna" class="border rounded px-1.5 text-neutral-500">↑</button>
                        <button type="button" wire:click="moverColumna('{{ $col }}', 1)" title="Bajar columna" class="border rounded px-1.5 text-neutral-500">↓</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Formulario HTTP convencional para permitir descargas nativas sin colas ni ventanas emergentes. --}}
    <form id="matriculas-export-form" method="POST" action="{{ route('admin.documentacion.expedicion-matriculas.descargar') }}" class="rounded-2xl border border-[#006492]/20 bg-sky-50/60 p-4 space-y-4 dark:bg-sky-950/20">
        @csrf
        <input type="hidden" name="generacion_id" value="{{ $generacionId }}">
        <input type="hidden" name="seg" value="{{ $seg }}">
        <input type="hidden" name="estado" value="{{ $estado }}">
        <input type="hidden" name="modalidad_id" value="{{ $modalidadId }}">
        @foreach ($licenciaturaIds as $id)
            <input type="hidden" name="licenciaturas[]" value="{{ $id }}">
        @endforeach
        @foreach ($alumnoIds as $id)
            <input type="hidden" name="alumnos[]" value="{{ $id }}">
        @endforeach
        @foreach ($columnasOrdenadas as $col)
            <input type="hidden" name="columnas[]" value="{{ $col }}">
        @endforeach
        @foreach ($meses as $id => $valor)
            @unless ($alumnosVisibles->contains('id', (int) $id))
                <input type="hidden" name="meses[{{ $id }}]" value="{{ $valor }}">
            @endunless
        @endforeach
        @foreach ($observaciones as $id => $valor)
            @unless ($alumnosVisibles->contains('id', (int) $id))
                <input type="hidden" name="observaciones[{{ $id }}]" value="{{ $valor }}">
            @endunless
        @endforeach
        <div class="grid gap-3 sm:grid-cols-2">
            <flux:select wire:model.live="plantilla" label="Plantilla de exportación" class="w-full">
                <flux:select.option value="seg">Original SEG · Tabla clásica</flux:select.option>
                <flux:select.option value="cum">Institucional CUM · Azul y verde</flux:select.option>
                <flux:select.option value="personalizado">Personalizada · Columnas elegidas</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="salida" label="Organización de la descarga" class="w-full">
                <flux:select.option value="consolidado">Consolidada · Un archivo por formato</flux:select.option>
                <flux:select.option value="individual">Individual · Por licenciatura</flux:select.option>
                <flux:select.option value="zip">ZIP · Separado por licenciatura</flux:select.option>
            </flux:select>
            <input type="hidden" name="plantilla" value="{{ $plantilla }}">
            <input type="hidden" name="salida" value="{{ $salida }}">
        </div>
        <div class="flex flex-wrap gap-2 items-center">
            <button type="submit" name="formato" value="excel" @disabled(count($alumnoIds) === 0 || count($columnasOrdenadas) === 0)
                class="rounded-xl bg-[#88AC2E] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#739326] disabled:cursor-not-allowed disabled:opacity-40">Descargar Excel</button>
            <button type="submit" name="formato" value="word" @disabled(count($alumnoIds) === 0 || count($columnasOrdenadas) === 0)
                class="rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#00547B] disabled:cursor-not-allowed disabled:opacity-40">Descargar Word</button>
            <button type="submit" name="formato" value="ambos" @disabled(count($alumnoIds) === 0 || count($columnasOrdenadas) === 0)
                class="rounded-xl bg-neutral-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-700 disabled:cursor-not-allowed disabled:opacity-40">Excel + Word (ZIP)</button>
        </div>
        <p class="text-xs text-neutral-600 dark:text-neutral-300">La descarga es directa y no modifica inscripciones ni matrículas SEG. Si seleccionas varias licenciaturas en el modo individual, se generará un ZIP automáticamente.</p>
    </form>
</div>
