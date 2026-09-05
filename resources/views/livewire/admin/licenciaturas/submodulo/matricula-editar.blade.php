<div x-data="{ show: @entangle('open').live }"
    x-show="show"
    x-cloak
    @keydown.escape.window="show = false; $wire.cerrarModal()"
    @click.self="show = false; $wire.cerrarModal()"
    x-trap.noscroll="show"
    class="fixed inset-0 z-50 flex items-start md:items-center justify-center p-4 md:p-6
           bg-gray-50/70 dark:bg-neutral-800/70 backdrop-blur-sm overflow-hidden">

    <div class="relative w-full max-w-7xl">
        <!-- Botón cerrar fijo (no se mueve al hacer scroll interno) -->
        <button @click="show = false; $wire.cerrarModal()"
                class="absolute z-10 text-2xl top-2 right-2 text-gray-600 hover:text-gray-800
                       dark:text-gray-300 dark:hover:text-gray-100">
            &times;
        </button>

        <form wire:submit.prevent="actualizarEstudiante"
              class="relative rounded-2xl border border-neutral-200 dark:border-neutral-700
                     bg-white dark:bg-neutral-800 shadow-2xl
                     max-h-[85vh] overflow-y-auto overscroll-contain scroll-smooth">

            <h1 class="p-4 text-sm md:text-base">
                EDITAR | ALUMNO -
                <flux:badge color="indigo">{{ $nombre }} {{ $apellido_paterno }} {{ $apellido_materno }}</flux:badge>
                | CURP - {{ $CURP }}
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2 p-4 pt-0">
                <!-- Datos Generales -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
                    <h2 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Generales</h2>
                    <hr class="mb-4">

                    <flux:field>
                        <flux:select badge="Requerido" label="Usuario" wire:model.live="user_id">
                            @foreach($usuarios as $key =>  $usuario)
                                <option value="{{ $usuario->id }}">{{ $key+1 }}.- {{ $usuario->username }} => {{ $usuario->email }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input type="text"  badge="Requerido" label="Matrícula" placeholder="Matrícula" wire:model="matricula"  />
                        <flux:input type="text" label="Folio" placeholder="Folio" wire:model="folio" />
                        <flux:input type="text" badge="Requerido" label="CURP" placeholder="CURP" wire:model.live="CURP" />
                        <flux:input type="text" badge="Requerido" label="Nombre" placeholder="Nombre" wire:model="nombre" />
                        <flux:input type="text" badge="Requerido" label="Apellido Paterno" placeholder="Apellido Paterno" wire:model="apellido_paterno" />
                        <flux:input type="text" badge="Requerido" label="Apellido Materno" placeholder="Apellido Materno" wire:model="apellido_materno" />
                        <flux:input type="date"  variant="filled"  readonly badge="Requerido" label="Fecha de Nacimiento" placeholder="Fecha de Nacimiento" wire:model="fecha_nacimiento" />
                        <flux:input type="number"  variant="filled"  readonly badge="Requerido" label="Edad" placeholder="Edad" wire:model="edad" />

                        <flux:radio.group badge="Requerido" wire:model="sexo" label="Género">
                            <flux:radio label="Hombre" value="H">Hombre</flux:radio>
                            <flux:radio label="Mujer" value="M">Mujer</flux:radio>
                        </flux:radio.group>

                        <flux:input type="text" label="Nacionalidad" placeholder="Nacionalidad" wire:model="pais" />

                        <flux:select label="Estado" wire:model="estado_nacimiento_id">
                            <option value="">--Seleccione un estado--</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select label="Ciudad" wire:model="ciudad_nacimiento_id">
                            <option value="">--Seleccione una ciudad--</option>
                            @foreach($ciudades as $ciudad)
                                <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <!-- Datos de Contacto -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
                    <h2 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos de Contacto</h2>
                    <hr class="mb-4">

                    <flux:field>
                        <flux:input type="text" label="Calle" placeholder="Calle" wire:model="calle" />
                        <flux:input type="text" label="Número Exterior" placeholder="Número Exterior" wire:model="numero_exterior" />
                        <flux:input type="text" label="Número Interior" placeholder="Número Interior" wire:model="numero_interior" />
                        <flux:input type="text" label="Colonia" placeholder="Colonia" wire:model="colonia" />
                        <flux:input type="text" label="Código Postal" placeholder="Código Postal" wire:model="codigo_postal" />
                        <flux:input type="text" label="Municipio" placeholder="Municipio" wire:model="municipio" />

                        <flux:select label="Ciudad" wire:model="ciudad_id">
                            <option value="">--Seleccione una ciudad--</option>
                            @foreach($ciudades as $ciudad)
                                <option value="{{ $ciudad->id }}">{{ $ciudad->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select label="Estado" wire:model="estado_id">
                            <option value="">--Seleccione un estado--</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}">{{ $estado->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input type="text" label="Teléfono" placeholder="Teléfono" wire:model="telefono" />
                        <flux:input type="text" label="Celular" placeholder="Celular" wire:model="celular" />
                        <flux:input type="text" label="Tutor" placeholder="Tutor" wire:model="tutor" />
                        <flux:input type="text" variant="filled" readonly label="Correo electrónico" placeholder="Correo Eletrónico" wire:model="email" />
                    </flux:field>
                </div>

                <!-- Datos Escolares -->
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-5">
                    <h2 class="text-2xl font-bold text-center text-neutral-800 dark:text-neutral-200 uppercase pb-4">Datos Escolares</h2>
                    <hr class="mb-4">

                    <flux:field>
                        <flux:input type="text" label="Bachillerato Procedente" placeholder="Bachillerato Procedente" wire:model="bachillerato_procedente" />
                    </flux:field>

                    <div class="mt-5 overflow-hidden rounded-2xl border border-[#006492]/20 bg-[#006492]/5 dark:border-sky-900/60 dark:bg-sky-950/20">
                        <div class="flex flex-col gap-3 border-b border-[#006492]/10 px-4 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-sky-900/50">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#006492] text-white">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m0 0 4-4m-4 4-4-4M5 7h4m6 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-extrabold text-slate-800 dark:text-white">Contexto académico protegido</p>
                                    <p class="mt-0.5 max-w-2xl text-xs leading-5 text-slate-500 dark:text-neutral-400">
                                        Licenciatura, generación, cuatrimestre y modalidad no se modifican desde la edición personal. Los cambios de contexto se realizan desde <strong>Matrícula → Movimiento académico</strong>, donde se validan materias, calificaciones y auditoría antes de aplicar el cambio.
                                    </p>
                                </div>
                            </div>

                            @if($permitirMovimientoAcademico)
                                <button type="button" wire:click="abrirMovimientoAcademico"
                                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#006492] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition hover:bg-[#005477]">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11m0 0-3-3m3 3-3 3M17 17H6m0 0 3 3m-3-3 3-3"/>
                                    </svg>
                                    Abrir movimiento académico
                                </button>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-xl border border-white/80 bg-white px-3.5 py-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Licenciatura</div>
                                <div class="mt-1 text-sm font-extrabold text-slate-800 dark:text-white">{{ $licenciaturaActual?->nombre ?: 'Sin asignar' }}</div>
                            </div>
                            <div class="rounded-xl border border-white/80 bg-white px-3.5 py-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Generación</div>
                                <div class="mt-1 text-sm font-extrabold text-slate-800 dark:text-white">{{ $generacionActual?->generacion ?: 'Sin asignar' }}</div>
                            </div>
                            <div class="rounded-xl border border-white/80 bg-white px-3.5 py-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Cuatrimestre</div>
                                <div class="mt-1 text-sm font-extrabold text-slate-800 dark:text-white">{{ $cuatrimestreActual?->nombre_cuatrimestre ?: 'Sin asignar' }}</div>
                            </div>
                            <div class="rounded-xl border border-white/80 bg-white px-3.5 py-3 shadow-sm dark:border-neutral-700 dark:bg-neutral-900">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Modalidad</div>
                                <div class="mt-1 text-sm font-extrabold text-[#006492] dark:text-sky-300">{{ $modalidadActual?->nombre ?: 'Sin asignar' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mx-auto border rounded-md p-4 mt-4 shadow-sm">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 border-b pb-2 mb-4">REGISTRO DE DOCUMENTACIÓN</h2>

                        <div class="flex flex-col md:flex-row gap-4">
                            <!-- Lista de documentos -->
                            <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 flex-1">
                                <flux:fieldset>
                                    <div class="space-y-3">
                                        {{-- switches / carga de docs --}}
                                    </div>
                                </flux:fieldset>
                            </div>

                            <!-- Subir foto -->
                            <div class="flex flex-col items-center flex-1 text-center">
                                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">SUBIR FOTO</h3>
                                <flux:input wire:model.live="foto_nueva" type="file" accept="image/jpeg,image/jpg,image/png" />

                                @if ($foto)
                                    <div class="rounded-full flex flex-col items-center justify-center mb-2 mt-2">
                                        <img src="{{ asset('storage/estudiantes/' . $foto) }}" alt="{{ __('Foto') }}" class="w-20 h-20 rounded-full">
                                    </div>
                                @else
                                    <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center mb-2 mt-2">
                                        <svg class="w-10 h-10 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    </div>
                                @endif

                                <div wire:loading>
                                    <flux:badge color="indigo">Cargando foto...</flux:badge>
                                </div>
                                <div wire:loading.remove>
                                    @if ($foto_nueva)
                                        <p class="mt-4 font-semibold">Nueva foto</p>
                                        <img src="{{ $foto_nueva->temporaryUrl() }}" alt="Nueva del alumno" class="w-24 h-24 rounded-full">
                                    @endif
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Peso máximo 1mb · PNG/JPG/JPEG · 2.5cm x 3cm
                                </p>
                            </div>
                        </div>

                        <!-- Otros -->
                        <flux:input type="text" label="Documentos" placeholder="Otros documentos" wire:model="otros" />
                    </div>

                    <flux:fieldset class="mt-4">
                        <flux:legend>Foráneo</flux:legend>
                        <flux:switch label="Foráneo" wire:model="foraneo" align="left" />
                    </flux:fieldset>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-neutral-700 dark:bg-neutral-900/70">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-extrabold text-slate-800 dark:text-white">Estado académico</p>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-neutral-400">El cambio de estado se aplica únicamente al guardar este formulario.</p>
                            </div>
                            <flux:switch label="Alumno activo" wire:model.live="status" align="left" />
                        </div>

                        @if(!$status && $fecha_baja)
                            <div class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-200">
                                Fecha de baja: {{ \Carbon\Carbon::parse($fecha_baja)->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    </div>

                    @if($permitirMovimientoAcademico)
                    <div class="mt-5 overflow-hidden rounded-2xl border border-rose-200 bg-rose-50/70 dark:border-rose-900/60 dark:bg-rose-950/20">
                        <div class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-extrabold text-rose-800 dark:text-rose-200">Zona de peligro</p>
                                <p class="mt-0.5 max-w-2xl text-xs leading-5 text-rose-700/80 dark:text-rose-300/80">
                                    La baja académica conserva expediente y calificaciones. La eliminación permanente es excepcional y puede borrar registros relacionados por cascada.
                                </p>
                            </div>
                            @if(!$eliminacionOpen)
                                <button type="button" wire:click="prepararEliminacionPermanente"
                                        class="inline-flex shrink-0 items-center justify-center rounded-xl border border-rose-300 bg-white px-4 py-2.5 text-xs font-extrabold text-rose-700 transition hover:bg-rose-100 dark:border-rose-800 dark:bg-neutral-900 dark:text-rose-300">
                                    Revisar eliminación permanente
                                </button>
                            @endif
                        </div>

                        @if($eliminacionOpen)
                            <div class="border-t border-rose-200 px-4 py-4 dark:border-rose-900/60">
                                @php($totalImpactoEliminar = array_sum($impactoEliminar))
                                <div class="rounded-xl border border-rose-200 bg-white p-4 dark:border-rose-900/60 dark:bg-neutral-900">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-black text-rose-800 dark:text-rose-200">Impacto detectado</p>
                                            <p class="mt-0.5 text-xs text-slate-500 dark:text-neutral-400">{{ $totalImpactoEliminar }} registro(s) relacionados podrían verse afectados.</p>
                                        </div>
                                        <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-black text-rose-700 dark:bg-rose-900/40 dark:text-rose-200">Irreversible</span>
                                    </div>

                                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Calificaciones</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['calificaciones'] ?? 0 }}</strong></div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Constancias</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['constancias'] ?? 0 }}</strong></div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Justificantes</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['justificantes'] ?? 0 }}</strong></div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Títulos</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['titulos'] ?? 0 }}</strong></div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Docs. identidad</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['documentos_identidad'] ?? 0 }}</strong></div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Fuentes</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['fuentes_documentos'] ?? 0 }}</strong></div>
                                        <div class="rounded-lg bg-slate-50 px-3 py-2 dark:bg-neutral-800"><span class="block text-[10px] uppercase text-slate-400">Organizaciones</span><strong class="text-sm text-slate-800 dark:text-white">{{ $impactoEliminar['organizaciones_documentos'] ?? 0 }}</strong></div>
                                    </div>

                                    <div class="mt-4">
                                        <flux:input label="Confirmación" wire:model.live.debounce.250ms="confirmacionEliminar" placeholder="Escribe {{ $matricula }}" />
                                        <p class="mt-1.5 text-[11px] text-rose-600 dark:text-rose-300">Escribe exactamente la matrícula <strong>{{ $matricula }}</strong> para habilitar la eliminación.</p>
                                        @error('confirmacionEliminar') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                        <button type="button" wire:click="cancelarEliminacionPermanente" class="rounded-xl px-4 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 dark:text-neutral-300 dark:hover:bg-neutral-800">Cancelar</button>
                                        <button type="button" wire:click="eliminarPermanentemente" wire:loading.attr="disabled" wire:target="eliminarPermanentemente"
                                                @disabled(mb_strtoupper(trim($confirmacionEliminar), 'UTF-8') !== mb_strtoupper(trim((string) $matricula), 'UTF-8'))
                                                class="rounded-xl bg-rose-600 px-4 py-2.5 text-xs font-extrabold text-white transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-40">
                                            Eliminar inscripción permanentemente
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <div class="p-4">
                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </div>
</div>
