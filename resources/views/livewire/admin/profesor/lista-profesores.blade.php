<div class="space-y-6">

    {{-- Buscar profesor --}}
    <div>
        <flux:input required
            label="Buscar Profesor"
            wire:model.live.debounce.500ms="query"
            name="profesor_id"
            id="query"
            type="text"
            placeholder="Buscar profesor por nombre o apellidos"
            @focus="open = true"
            @input="open = true"
            @blur="setTimeout(() => open = false, 150)"
            wire:keydown.arrow-down="selectIndexDown"
            wire:keydown.arrow-up="selectIndexUp"
            wire:keydown.enter="selectProfesor({{ $selectedIndex }})"
            autocomplete="on"
        />

        {{-- Loader mientras busca profesor --}}
        <div class="mt-2 flex items-center text-sm text-gray-600" wire:loading.delay.shortest wire:target="query">
            <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            Buscando profesores…
        </div>

        @if(!empty($profesores))
            <ul class="mt-2 bg-white border rounded shadow max-h-60 overflow-auto" wire:loading.remove wire:target="query">
                @foreach($profesores as $idx => $p)
                    <li
                        class="px-3 py-2 cursor-pointer {{ $selectedIndex === $idx ? 'bg-indigo-50' : '' }}"
                        wire:click="selectProfesor({{ $idx }})"
                    >
                        {{ $p['nombre'] }} {{ $p['apellido_paterno'] }} {{ $p['apellido_materno'] }}
                        <span class="text-xs text-gray-500">({{ $p['CURP'] }})</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Info profesor seleccionado --}}
    @if($selectedProfesor)
        <div class="p-4 bg-indigo-100 rounded">
            <p class="font-semibold">
                Nombre del profesor: {{ $selectedProfesor['nombre'] }} {{ $selectedProfesor['apellido_paterno'] }} {{ $selectedProfesor['apellido_materno'] }}
            </p>
            <p class="text-sm">CURP: {{ $selectedProfesor['CURP'] }}</p>
        </div>
    @endif

    {{-- Periodo --}}
    @if($selectedProfesor)
        <flux:select label="Periodo" wire:model.live="periodo_id" required>
            <option value="">--Selecciona el periodo---</option>
            <option value="9-12">SEP/DIC</option>
            <option value="1-4">ENE/ABR</option>
            <option value="5-8">MAY-AGO</option>
        </flux:select>
    @endif

    {{-- Materias asignadas --}}
    @if($selectedProfesor)
        <div class="space-y-3">
            <h2 class="text-lg font-semibold">Materias Asignadas</h2>

            <div class="w-full">
                <flux:input icon="magnifying-glass" wire:model.live="buscador_materia" placeholder="Buscar por nombre de materia..."/>
            </div>

            <div class="relative overflow-x-auto bg-white rounded shadow">

                {{-- Loader materias --}}
                <div class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center z-10"
                     wire:loading.flex
                     wire:target="selectProfesor,buscador_materia,cargarMateriasAsignadas">
                    <div class="flex items-center text-gray-700">
                        <svg class="animate-spin h-6 w-6 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Cargando materias…
                    </div>
                </div>

                <div
    x-data="{
        term: @entangle('buscador_materia'),
        dense: false,
        showCols: { modalidad: true, cuatrimestre: true, licenciatura: true },
        highlight(t) {
            if (!this.term) return t;
            // escape regex
            const esc = this.term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return t.replace(new RegExp(esc, 'ig'), (m) => `<mark class='bg-yellow-200 px-0.5 rounded'>${m}</mark>`);
        },
        async copy(text, $el) {
            try {
                await navigator.clipboard.writeText(text);
                $el.innerText = '¡Copiado!';
                setTimeout(() => $el.innerText = 'Copiar', 1200);
            } catch (e) {}
        },
        showShadow: false
    }"
    class="relative bg-white rounded shadow ring-1 ring-gray-200"
    @scroll.passive.window="
        const tbl = $el.querySelector('[data-table-scroll]');
        if (!tbl) return;
        $nextTick(() => { showShadow = tbl.scrollTop > 0; });
    "
>
    <!-- Barra de utilidades -->
    <div class="flex flex-wrap items-center gap-3 p-3 border-b bg-gray-50/70 rounded-t">
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-500">Densidad</span>
            <button
                @click="dense=false"
                :class="dense ? 'bg-white text-gray-600' : 'bg-indigo-600 text-white'"
                class="px-2 py-1 text-xs rounded border border-indigo-200"
            >Cómoda</button>
            <button
                @click="dense=true"
                :class="dense ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600'"
                class="px-2 py-1 text-xs rounded border border-indigo-200"
            >Compacta</button>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <span class="text-xs text-gray-500">Columnas:</span>
            <label class="inline-flex items-center gap-1 text-xs">
                <input type="checkbox" class="rounded" x-model="showCols.modalidad"> Modalidad
            </label>
            <label class="inline-flex items-center gap-1 text-xs">
                <input type="checkbox" class="rounded" x-model="showCols.cuatrimestre"> Cuatrimestre
            </label>
            <label class="inline-flex items-center gap-1 text-xs">
                <input type="checkbox" class="rounded" x-model="showCols.licenciatura"> Licenciatura
            </label>
        </div>
    </div>

    <div class="max-h-[480px] overflow-auto" data-table-scroll>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10"
                   :class="showShadow ? 'shadow-sm shadow-gray-200' : ''">
                <tr>
                    <th class="px-3" :class="dense ? 'py-1.5' : 'py-2.5'">
                        <span class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">#</span>
                    </th>
                    <th class="px-3" :class="dense ? 'py-1.5' : 'py-2.5'">
                        <span class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Materia</span>
                    </th>
                    <th class="px-3" :class="dense ? 'py-1.5' : 'py-2.5'" x-show="showCols.modalidad">
                        <span class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Modalidad</span>
                    </th>
                    <th class="px-3" :class="dense ? 'py-1.5' : 'py-2.5'" x-show="showCols.cuatrimestre">
                        <span class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Cuatrimestre</span>
                    </th>
                    <th class="px-3" :class="dense ? 'py-1.5' : 'py-2.5'" x-show="showCols.licenciatura">
                        <span class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Licenciatura</span>
                    </th>
                    <th class="px-3" :class="dense ? 'py-1.5' : 'py-2.5'">
                        <span class="text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">Lista de</span>
                    </th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($this->materiasFiltradas as $i => $row)
                    <tr class="group hover:bg-indigo-50/40 transition-colors"
                        x-data="{ open:false }">
                        <!-- # -->
                        <td class="px-3 align-top"
                            :class="dense ? 'py-1.5 text-xs' : 'py-2.5 text-sm'">
                            <div class="flex items-start gap-2">

                                <span>{{ $i + 1 }}</span>
                            </div>
                        </td>

                        <!-- Materia (con highlight + copiar) -->
                        <td class="px-3 align-top"
                            :class="dense ? 'py-1.5' : 'py-2.5'">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900 text-center"
                                      x-html="highlight(@js($row->materia))"></span>

                            </div>
                            <div class="mt-1">
                                <!-- badge modalidad -->
                                <template x-if="@js($row->modalidad) === 'SEMIESCOLARIZADA'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-purple-100 text-purple-700">Semiescolarizada</span>
                                </template>
                                <template x-if="@js($row->modalidad) === 'ESCOLARIZADA'">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-100 text-emerald-700">Escolarizada</span>
                                </template>
                            </div>
                        </td>

                        <!-- Modalidad -->
                        <td class="px-3 align-top text-center" x-show="showCols.modalidad"
                            :class="dense ? 'py-1.5 text-xs' : 'py-2.5 text-sm'">
                            <span x-html="highlight(@js($row->modalidad))"></span>
                        </td>

                        <!-- Cuatrimestre -->
                        <td class="px-3 align-top text-center" x-show="showCols.cuatrimestre"
                            :class="dense ? 'py-1.5 text-xs' : 'py-2.5 text-sm'">
                            {{ $row->cuatrimestre }}
                        </td>

                        <!-- Licenciatura -->
                        <td class="px-3 align-top text-center" x-show="showCols.licenciatura"
                            :class="dense ? 'py-1.5 text-xs' : 'py-2.5 text-sm'">
                            <span x-html="highlight(@js($row->licenciatura))"></span>
                        </td>

                        <!-- Acciones -->
                        <td class="px-3 align-top"
                            :class="dense ? 'py-1.5' : 'py-2.5'">
                            @php
                                $firstGen = $row->generaciones ? \Illuminate\Support\Str::of($row->generaciones)->explode(',')->first() : null;
                            @endphp

                            <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_asistencia', [
                                'asignacion_materia' => $row->asignacion_materia_id,
                                'licenciatura_id' => $row->licenciatura_id,
                                'cuatrimestre_id' => $row->cuatrimestre,
                                'generacion_id' => $firstGen,
                                'modalidad_id' => $row->modalidad_id,
                                'periodo' => $periodo_id,
                            ]) }}"
                               class="inline-block px-3 py-1 text-white  font-semibold rounded bg-indigo-500 hover:bg-indigo-600 shadow-sm">
                                Asistencia
                            </a>

                            <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_evaluacion', [
                                'asignacion_materia' => $row->asignacion_materia_id,
                                'licenciatura_id' => $row->licenciatura_id,
                                'cuatrimestre_id' => $row->cuatrimestre,
                                'generacion_id' => $firstGen,
                                'modalidad_id' => $row->modalidad_id,
                                'periodo' => $periodo_id,
                            ]) }}"
                               class="inline-block ml-2 px-3 py-1 text-white font-semibold rounded bg-cyan-500 hover:bg-cyan-600 shadow-sm">
                                Evaluación
                            </a>
                        </td>
                    </tr>

                    <!-- Detalle expandible -->
                    <tr x-show="open" x-collapse>
                        <td colspan="6" class="bg-gray-50 px-6 py-3 text-sm text-gray-700">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-semibold text-gray-500">Generaciones:</span>
                                @php
                                    $gens = $row->generaciones
                                        ? \Illuminate\Support\Str::of($row->generaciones)->explode(',')->filter()->unique()->values()
                                        : collect();
                                @endphp
                                @forelse($gens as $g)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-blue-100 text-blue-700">Gen {{ $g }}</span>
                                @empty
                                    <span class="text-xs text-gray-500">Sin registros de horario por generación.</span>
                                @endforelse
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <!-- Acciones duplicadas en el detalle -->
                                <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_asistencia', [
                                    'asignacion_materia' => $row->asignacion_materia_id,
                                    'licenciatura_id' => $row->licenciatura_id,
                                    'cuatrimestre_id' => $row->cuatrimestre,
                                    'generacion_id' => $firstGen,
                                    'modalidad_id' => $row->modalidad_id,
                                    'periodo' => $periodo_id,
                                ]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1 text-white text-[11px] font-semibold rounded bg-indigo-500 hover:bg-indigo-600 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M13 7H7v6h6V7z"/><path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V8.414A2 2 0 0016.586 7L13 3.414A2 2 0 0011.586 3H5zm8 12H7V7h6v8z" clip-rule="evenodd"/></svg>
                                    Lista de asistencia
                                </a>
                                <a target="_blank" href="{{ route('admin.pdf.documentacion.lista_evaluacion', [
                                    'asignacion_materia' => $row->asignacion_materia_id,
                                    'licenciatura_id' => $row->licenciatura_id,
                                    'cuatrimestre_id' => $row->cuatrimestre,
                                    'generacion_id' => $firstGen,
                                    'modalidad_id' => $row->modalidad_id,
                                    'periodo' => $periodo_id,
                                ]) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1 text-white text-[11px] font-semibold rounded bg-cyan-500 hover:bg-cyan-600 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M9 2a1 1 0 00-1 1v1H6a2 2 0 00-2 2v1h12V6a2 2 0 00-2-2h-2V3a1 1 0 10-2 0v1H9V3a1 1 0 00-1-1z"/><path d="M4 9h12v5a2 2 0 01-2 2H6a2 2 0 01-2-2V9z"/></svg>
                                    Lista de evaluación
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                            No hay materias asignadas (ajusta el periodo o el filtro).
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

            </div>
        </div>
    @else
        <div class="p-4 rounded border border-dashed text-gray-600">
            Selecciona un profesor para habilitar la búsqueda y ver sus materias con horario.
        </div>
    @endif

</div>
