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

                        <flux:select label="Licenciatura" wire:model.live="licenciatura_id">
                            <option value="">--Seleccione una licenciatura--</option>
                            @foreach($licenciaturas as $licenciatura)
                                <option value="{{ $licenciatura->id }}">{{ $licenciatura->nombre }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select label="Generación" wire:model.live="generacion_id">
                            @foreach($generaciones as $generacion)
                                <option value="{{ $generacion->generacion_id }}">{{ $generacion->generacion->generacion }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select label="Cuatrimestre" wire:model="cuatrimestre_id">
                            @foreach($cuatrimestres as $cuatrimestre)
                                <option value="{{ $cuatrimestre->cuatrimestre_id }}">{{ $cuatrimestre->cuatrimestre->cuatrimestre }}° Cuatrimestre</option>
                            @endforeach
                        </flux:select>

                        <flux:select label="Modalidad" wire:model="modalidad_id">
                            @foreach($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}">{{ $modalidad->nombre }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>

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

                    <flux:fieldset class="mt-4">
                        <flux:legend>Status</flux:legend>
                        <flux:switch label="Status"  wire:model="status"  align="left" />
                    </flux:fieldset>

                    @if(!$status)
                        <p class="text-sm text-red-600 mt-2">
                            Fecha de baja: {{ \Carbon\Carbon::parse($fecha_baja)->format('d/m/Y H:i') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="p-4">
                <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Guardar') }}</flux:button>
            </div>
        </form>
    </div>
</div>
