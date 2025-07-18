<div x-data="{ show: @entangle('open') }"
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="show = false; $wire.cerrarModal()"
        class="fixed inset-0 bg-gray-50 dark:bg-neutral-800 z-50 flex items-center justify-center ">


       {{-- <div @click.away="show = false;  $wire.cerrarModal()" class="relative"> --}}
       <div class="relative">
           <button @click="show = false" class="absolute text-2xl top-2 right-2 text-gray-500 hover:text-gray-700 dark:text-gray-300 dark:hover:text-gray-500">
               &times;
           </button>
           <form wire:submit.prevent="actualizarJustificante">

               <flux:field>
                <div class="flex flex-col items-center justify-center gap-5 mb-4 ">

                    <div class="w-120 border-2 border-gray-50 bg-white  dark:bg-neutral-800 shadow-md rounded-3xl p-7 space-y-5">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white text-center">Editar Justificante </h2>
                        <p class="text-gray-600 dark:text-gray-300 text-center">Actualiza los datos del justificante seleccionado.</p>
                                            <div class="grid  md:grid-cols-1 gap-3 items-center " >


                                                <flux:input
                                                label="Buscar alumno"
                                                wire:model.live.debounce.500ms="query"
                                                name="alumno_id"
                                                id="query"
                                                type="text"
                                                placeholder="Buscar alumno por nombre, matrícula o CURP:"
                                                @focus="open = true"
                                                @input="open = true"
                                                @blur="setTimeout(() => open = false, 150)"
                                                wire:keydown.arrow-down="selectIndexDown"
                                                wire:keydown.arrow-up="selectIndexUp"
                                                wire:keydown.enter="selectAlumno({{ $selectedIndex }})"
                                                autocomplete="off"
                                            />

                                              @if (!empty($alumnos))
                                        <ul
                                            x-show="open"
                                            x-transition
                                            x-cloak
                                            class="absolute w-full bg-white border mt-1 rounded shadow z-10 max-h-60 overflow-auto"
                                            style="display: none"
                                        >
                                            @forelse ($alumnos as $index => $alumno)
                                                <li
                                                    class="p-2 cursor-pointer {{ $selectedIndex === $index ? 'bg-blue-200' : '' }}"
                                                    wire:click="selectAlumno({{ $index }})"
                                                    @mouseenter="open = true"
                                                >
                                                    <p class="font-bold text-indigo-600">
                                                        {{ $alumno['apellido_paterno'] ?? '' }} {{ $alumno['apellido_materno'] ?? '' }} {{ $alumno['nombre'] ?? '' }}
                                                    </p>
                                                    <p class="text-gray-700">
                                                        Matrícula: {{ $alumno['matricula'] ?? '' }} | CURP: {{ $alumno['CURP'] ?? '' }}
                                                    </p>
                                                </li>
                                            @empty
                                                <li class="p-2">No se encontraron alumnos.</li>
                                            @endforelse
                                        </ul>
                                    @endif

                                     @if ($selectedAlumno)
                                                <div class="mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:text-white">
                                                <p class="font-bold">
                                                    {{ $selectedAlumno['apellido_paterno'] ?? '' }} {{ $selectedAlumno['apellido_materno'] ?? '' }} {{ $selectedAlumno['nombre'] ?? '' }}
                                                </p>
                                                <p>Matrícula: {{ $selectedAlumno['matricula'] ?? '' }}</p>
                                                <p>CURP: {{ $selectedAlumno['CURP'] ?? '' }}</p>
                                                <p>Folio: {{ $selectedAlumno["folio"] ?? '----' }}</p>
                                                <p>Licenciatura: {{ $selectedAlumno['licenciatura']['nombre'] ?? '----' }}</p>
                                            </div>
                                        @else
                                        <div class="mt-4 p-4 border rounded bg-gray-50 dark:bg-gray-800 dark:text-white">

                                                <p class="font-bold">
                                                    {{ $alumno_id['apellido_paterno'] ?? '' }} {{ $alumno_id['apellido_materno'] ?? '' }} {{ $alumno_id['nombre'] ?? '' }}
                                                </p>
                                                <p>Matrícula: {{ $alumno_id['matricula'] ?? '' }}</p>
                                                <p>CURP: {{ $alumno_id['CURP'] ?? '' }}</p>
                                                <p>Folio: {{ $alumno_id["folio"] ?? '----' }}</p>
                                                <p>Licenciatura: {{ $alumno_id['licenciatura']['nombre'] ?? '----' }}</p>
                                            </div>

                                        @endif

                                                <x-input wire:model="fechas_justificacion" label="Selecciona las fechas de justificación" type="text" id="fecha_expedicion" placeholder="Selecciona las fechas de justificación" class="w-full" />

                                            <flux:radio.group label="Justificación" wire:model.live="justificacion">
                                                <flux:radio
                                                    name="justificacion"
                                                    value="Asuntos personales"
                                                    label="Asuntos personales"
                                                />
                                                <flux:radio
                                                    name="justificacion"
                                                    value="Problemas de salud"
                                                    label="Problemas de salud"
                                                />
                                                <flux:radio
                                                    name="justificacion"
                                                    value="Otro"
                                                    label="Otro"
                                                />
                                            </flux:radio.group>

                                            <x-input wire:model="fecha_expedicion" label="Fecha de expedición" type="date" class="w-full" />

                                                </div>





                   <div class="mt-6 flex justify-end gap-2">
                              <div class="flex items-center">
                                   <flux:button variant="primary" type="submit" class="w-full cursor-pointer">{{ __('Actualizar') }}</flux:button>
                            </div>
                             <div class="flex items-center">
                               <flux:button  @click="show = false; $wire.cerrarModal()" class="w-full cursor-pointer">{{ __('Cancelar') }}</flux:button>
                             </div>
                     </div>
                    </div>
                </div>

                    </flux:field>

                    </form>
            </div>

   </div>

