<div class="space-y-5">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Generador de oficios de solicitud</p>
            <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-neutral-400">
                Filtra por generación y selecciona únicamente las licenciaturas con RVOE registrado y alumnos activos.
                La selección se aplica de manera idéntica al PDF y al Word.
            </p>
        </div>
        <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-[#88AC2E]/15 px-3 py-1 text-xs font-semibold text-[#54711a] dark:text-lime-300">
            <span class="h-1.5 w-1.5 rounded-full bg-[#88AC2E]"></span>
            PDF y Word sincronizados
        </span>
    </div>

    <form method="GET" target="_blank" action="{{ route('admin.pdf.documentacion.documento_oficios') }}" class="space-y-4">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <flux:select label="Generación" name="generacion" wire:model.live="generacionId" required>
                <flux:select.option value="">Selecciona una generación</flux:select.option>
                @foreach ($generaciones as $generacion)
                    <flux:select.option value="{{ $generacion->id }}">{{ $generacion->generacion }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select label="Tipo de documento" name="tipo_documento" required>
                <flux:select.option value="">Selecciona un oficio</flux:select.option>
                <flux:select.option value="matriculas">Matrículas</flux:select.option>
                <flux:select.option value="kardex">Kardex</flux:select.option>
                <flux:select.option value="registro-boletos">Registro y boletas</flux:select.option>
                <flux:select.option value="folios">Folios</flux:select.option>
                <flux:select.option value="certificados">Certificados</flux:select.option>
            </flux:select>

            <flux:input label="Fecha de expedición" name="fecha_expedicion" type="date" required />
        </div>

        <section class="rounded-2xl border border-neutral-200 bg-neutral-50/80 p-4 dark:border-neutral-700 dark:bg-neutral-800/50">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                <div>
                    <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Licenciaturas de la generación</p>
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        @if ($generacionId)
                            {{ $licenciaturas->count() }} licenciatura(s) elegible(s) · {{ $licenciaturas->sum('alumnos_activos_count') }} alumno(s) activo(s)
                        @else
                            Selecciona una generación para consultar su oferta con alumnos.
                        @endif
                    </p>
                </div>
                @if ($generacionId && $licenciaturas->isNotEmpty())
                    <span class="w-fit rounded-full bg-[#006492]/10 px-2.5 py-1 text-xs font-semibold text-[#006492] dark:text-sky-300">
                        {{ $alcance === 'todas' ? 'Todas las disponibles' : count($licenciaturaIds) . ' seleccionada(s)' }}
                    </span>
                @endif
            </div>

            @if ($generacionId && $licenciaturas->isNotEmpty())
                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="alcance" value="todas" wire:model.live="alcance" class="peer sr-only">
                        <span class="flex h-full gap-3 rounded-xl border-2 border-neutral-200 bg-white p-3 transition peer-checked:border-[#006492] peer-checked:bg-[#006492]/5 dark:border-neutral-700 dark:bg-neutral-900 dark:peer-checked:border-sky-400">
                            <span class="mt-0.5 h-4 w-4 rounded-full border-4 border-neutral-300 bg-white peer-checked:border-[#006492]"></span>
                            <span><strong class="block text-sm text-neutral-900 dark:text-white">Todas las licenciaturas</strong><span class="text-xs text-neutral-500 dark:text-neutral-400">Generar el oficio con todas las elegibles</span></span>
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="alcance" value="seleccion" wire:model.live="alcance" class="peer sr-only">
                        <span class="flex h-full gap-3 rounded-xl border-2 border-neutral-200 bg-white p-3 transition peer-checked:border-[#006492] peer-checked:bg-[#006492]/5 dark:border-neutral-700 dark:bg-neutral-900 dark:peer-checked:border-sky-400">
                            <span class="mt-0.5 h-4 w-4 rounded-full border-4 border-neutral-300 bg-white"></span>
                            <span><strong class="block text-sm text-neutral-900 dark:text-white">Selección personalizada</strong><span class="text-xs text-neutral-500 dark:text-neutral-400">Una o varias licenciaturas por oficio</span></span>
                        </span>
                    </label>
                </div>

                @if ($alcance === 'seleccion')
                    <div class="mt-3 rounded-xl border border-neutral-200 bg-white p-3 dark:border-neutral-700 dark:bg-neutral-900" wire:key="oficios-lic-{{ $generacionId }}">
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs font-medium text-neutral-600 dark:text-neutral-300">Selecciona las licenciaturas a incluir:</p>
                            <div class="flex gap-3 text-xs font-semibold">
                                <button type="button" wire:click="seleccionarTodas" class="text-[#006492] hover:underline dark:text-sky-300">Marcar todas</button>
                                <button type="button" wire:click="limpiarSeleccion" class="text-neutral-500 hover:underline">Limpiar</button>
                            </div>
                        </div>
                        <div class="grid max-h-64 grid-cols-1 gap-2 overflow-y-auto sm:grid-cols-2">
                            @foreach ($licenciaturas as $licenciatura)
                                <label wire:key="oficio-opcion-{{ $generacionId }}-{{ $licenciatura->id }}" class="flex cursor-pointer items-center gap-3 rounded-xl border border-neutral-200 p-3 transition hover:border-[#006492]/40 hover:bg-[#006492]/5 dark:border-neutral-700 dark:hover:bg-neutral-800">
                                    <input type="checkbox" name="licenciatura_ids[]" value="{{ $licenciatura->id }}" wire:model.live="licenciaturaIds" class="h-4 w-4 shrink-0 accent-[#006492]">
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ $licenciatura->nombre }}</span>
                                        <span class="mt-0.5 block text-xs text-neutral-500 dark:text-neutral-400">{{ $licenciatura->alumnos_activos_count }} alumno(s) activo(s) · RVOE {{ $licenciatura->RVOE }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @if (count($licenciaturaIds) === 0)
                            <p class="mt-3 text-xs font-medium text-amber-700 dark:text-amber-300" role="alert">Elige al menos una licenciatura antes de generar.</p>
                        @endif
                    </div>
                @endif
            @elseif ($generacionId)
                <p class="mt-3 text-sm text-amber-700 dark:text-amber-300" role="status">No hay licenciaturas con alumnos activos y RVOE para esta generación.</p>
            @endif
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-200 pt-4 dark:border-neutral-700">
            <p class="max-w-xl text-xs text-neutral-500 dark:text-neutral-400">
                Solo se incluirán alumnos con estatus activo para determinar la disponibilidad de cada licenciatura.
                Las carreras sin RVOE registrado se excluyen, respetando el criterio del oficio original.
            </p>
            <div class="flex gap-2">
                <button type="submit" formaction="{{ route('admin.pdf.documentacion.documento_oficios') }}"
                    @disabled(!$generacionId || $licenciaturas->isEmpty() || ($alcance === 'seleccion' && empty($licenciaturaIds)))
                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40">
                    Descargar PDF
                </button>
                <button type="submit" formaction="{{ route('admin.word.documentacion.documento_oficios') }}"
                    @disabled(!$generacionId || $licenciaturas->isEmpty() || ($alcance === 'seleccion' && empty($licenciaturaIds)))
                    class="inline-flex items-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#00547b] disabled:cursor-not-allowed disabled:opacity-40">
                    Descargar Word
                </button>
            </div>
        </div>
    </form>
</div>
