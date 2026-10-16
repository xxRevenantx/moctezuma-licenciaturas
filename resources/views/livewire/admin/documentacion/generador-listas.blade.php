<div class="space-y-6 pb-12">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[#88AC2E]">Centro Universitario Moctezuma <span class="h-1.5 w-1.5 rounded-full bg-[#006492]"></span> Control Escolar</div>
            <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900 dark:text-white">Generador de Listas <span class="text-[#006492]">PRO</span></h1>
            <p class="mt-1 text-sm text-slate-500">Diseña columnas, filtra alumnos y descarga listas oficiales en PDF, Word o ZIP.</p>
        </div>
        <a href="{{ route('admin.listas-generales') }}" wire:navigate class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-white">← Listas Generales anteriores</a>
    </div>

    @if($mensaje)
        <div class="rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm font-medium text-[#006492] dark:border-sky-900 dark:bg-sky-950">{{ $mensaje }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert"><strong>Revisa la configuración:</strong><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
        {{-- Panel de segmentación --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-5 flex items-center justify-between"><h2 class="text-lg font-bold text-slate-900 dark:text-white">01. Seleccionar alumnos</h2><span class="rounded-full bg-[#006492]/10 px-3 py-1 text-xs font-bold text-[#006492]">Filtros combinables</span></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="list-search" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-200">Buscar alumno</label>
                    <input id="list-search" wire:model.live.debounce.400ms="busqueda" type="search" placeholder="Nombre, apellidos, matrícula o CURP" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Licenciaturas <span class="font-normal text-slate-400">(vacío = todas)</span></p>
                    <div class="grid max-h-36 grid-cols-1 gap-2 overflow-y-auto rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-2 dark:border-slate-700 dark:bg-slate-800/60">
                        @foreach($licenciaturas as $lic)
                            <label wire:key="lic-gen-{{ $lic->id }}" class="flex cursor-pointer items-center gap-2 text-xs text-slate-700 dark:text-slate-200"><input type="checkbox" value="{{ $lic->id }}" wire:model.live="licenciatura_ids" class="rounded border-slate-300 text-[#006492]"> <span>{{ $lic->nombre }}</span></label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Generaciones <span class="font-normal text-slate-400">(vacío = todas)</span></p>
                    <div class="max-h-32 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        @foreach($generaciones as $gen)
                            <label wire:key="gen-list-{{ $gen->id }}" class="flex cursor-pointer items-center gap-2 text-xs dark:text-white"><input type="checkbox" value="{{ $gen->id }}" wire:model.live="generacion_ids" class="rounded text-[#006492]"> {{ $gen->generacion }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Cuatrimestres <span class="font-normal text-slate-400">(vacío = todos)</span></p>
                    <div class="max-h-32 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                        @foreach($cuatrimestres as $cuat)
                            <label wire:key="cuat-list-{{ $cuat->id }}" class="flex cursor-pointer items-center gap-2 text-xs dark:text-white"><input type="checkbox" value="{{ $cuat->id }}" wire:model.live="cuatrimestre_ids" class="rounded text-[#006492]"> {{ $cuat->nombre_cuatrimestre }}</label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold dark:text-white">Modalidad real para filtrar</label>
                    <select wire:model.live="modalidad_id" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="">Todas las modalidades</option>@foreach($modalidades as $mod)<option value="{{ $mod->id }}">{{ $mod->nombre }}</option>@endforeach</select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold dark:text-white">Procedencia</label>
                    <select wire:model.live="procedencia" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="todos">Locales y foráneos</option><option value="false">Solo locales</option><option value="true">Solo foráneos</option></select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold dark:text-white">Situación académica</label>
                    <select wire:model.live="estado" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="activos">Activos</option><option value="egresados">Egresados</option><option value="bajas">Bajas</option><option value="todos">Todos los estados</option></select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold dark:text-white">Ciclo escolar histórico</label>
                    <select wire:model.live="ciclo_escolar" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="">Situación actual</option>@foreach($ciclos as $ciclo)<option value="{{ $ciclo }}">{{ $ciclo }}</option>@endforeach</select>
                </div>
            </div>
            @if($ciclo_escolar)
                <p class="mt-4 rounded-lg bg-amber-50 p-3 text-xs leading-relaxed text-amber-800 dark:bg-amber-950 dark:text-amber-200">Se utilizan licenciatura, generación, cuatrimestre, matrícula, sexo y situación del historial de ese ciclo. Los datos personales y la procedencia corresponden al expediente actual porque no existen fotografías históricas de esos campos.</p>
            @endif
            <button type="button" wire:click="consultar" wire:loading.attr="disabled" wire:target="consultar,cambiarPagina" class="mt-5 w-full rounded-xl bg-[#006492] px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-[#004f73] disabled:opacity-60"><span wire:loading.remove wire:target="consultar">Consultar estudiantes</span><span wire:loading wire:target="consultar">Consultando datos...</span></button>
        </section>

        {{-- Configurador de columnas --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="mb-4 flex items-center justify-between gap-2"><h2 class="text-lg font-bold text-slate-900 dark:text-white">02. Diseñar el documento</h2><span class="rounded-full bg-[#88AC2E]/15 px-3 py-1 text-xs font-bold text-[#557419]">{{ count($columnas) }} columnas</span></div>
            <label class="mb-1 block text-sm font-semibold dark:text-white">Texto que se imprimirá en «Modalidad»</label>
            <select wire:model.live="modalidad_texto" class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                <option value="real">Mostrar la modalidad real de cada alumno</option><option value="escolarizada">Mostrar ESCOLARIZADA para todos</option><option value="semiescolarizada">Mostrar SEMIESCOLARIZADA para todos</option>
            </select>
            <p class="mt-1.5 text-xs text-slate-500">Esto modifica exclusivamente el texto del reporte. No cambia la base de datos ni el filtro anterior.</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <button type="button" wire:click="aplicarPredeterminado('grupo')" class="rounded-full bg-sky-50 px-3 py-1.5 font-semibold text-[#006492] hover:bg-sky-100 dark:bg-sky-950">Lista de grupo</button>
                <button type="button" wire:click="aplicarPredeterminado('contactos')" class="rounded-full bg-sky-50 px-3 py-1.5 font-semibold text-[#006492] hover:bg-sky-100 dark:bg-sky-950">Directorio</button>
                <button type="button" wire:click="aplicarPredeterminado('inscripcion')" class="rounded-full bg-sky-50 px-3 py-1.5 font-semibold text-[#006492] hover:bg-sky-100 dark:bg-sky-950">Inscripción</button>
                <button type="button" wire:click="aplicarPredeterminado('observaciones')" class="rounded-full bg-sky-50 px-3 py-1.5 font-semibold text-[#006492] hover:bg-sky-100 dark:bg-sky-950">Observaciones</button>
            </div>
            <div class="mb-2 mt-4 flex flex-wrap items-center justify-between gap-2"><p class="text-sm font-semibold dark:text-white">Campos disponibles</p><input wire:model.live.debounce.250ms="buscar_campo" type="search" placeholder="Buscar una columna..." class="w-48 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            <div class="grid max-h-44 grid-cols-2 gap-x-2 gap-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                @foreach($campos as $campo => $etiqueta)
                    @if($buscar_campo === '' || str_contains(mb_strtolower($etiqueta), mb_strtolower($buscar_campo)))
                    <label wire:key="col-check-{{ $campo }}" class="flex cursor-pointer items-center gap-2 text-xs text-slate-700 dark:text-slate-200"><input type="checkbox" wire:model.live="columnas" value="{{ $campo }}" class="rounded text-[#006492]">{{ $etiqueta }}</label>
                    @endif
                @endforeach
            </div>
            @if(count($columnas) > 12)
                <p class="mt-2 text-xs text-amber-700 dark:text-amber-300">Para mantener las listas legibles, se recomienda utilizar 12 columnas o menos en un mismo documento.</p>
            @endif
            <p class="mb-2 mt-4 text-sm font-semibold dark:text-white">Orden de columnas y encabezados</p>
            <div class="max-h-52 space-y-1.5 overflow-y-auto rounded-xl bg-slate-50 p-2 dark:bg-slate-800/60">
                @forelse($columnas as $i => $campo)
                    <div wire:key="ordered-col-{{ $campo }}" class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white p-1.5 dark:border-slate-700 dark:bg-slate-900">
                        <span class="w-5 text-center text-xs font-bold text-slate-400">{{ $i + 1 }}</span>
                        <input type="text" maxlength="60" wire:model.live.debounce.500ms="cabeceras.{{ $campo }}" placeholder="{{ $campos[$campo] ?? $campo }}" aria-label="Encabezado {{ $campo }}" class="min-w-0 flex-1 rounded-lg border border-slate-200 px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <button type="button" wire:click="moverColumna({{ $i }},-1)" @disabled($i === 0) aria-label="Mover columna arriba" class="rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-100 disabled:opacity-30 dark:border-slate-700">↑</button>
                        <button type="button" wire:click="moverColumna({{ $i }},1)" @disabled($i === count($columnas)-1) aria-label="Mover columna abajo" class="rounded border border-slate-200 px-2 py-1 text-xs hover:bg-slate-100 disabled:opacity-30 dark:border-slate-700">↓</button>
                    </div>
                @empty
                    <p class="p-3 text-sm text-rose-500">Selecciona al menos una columna.</p>
                @endforelse
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div><label class="mb-1 block text-xs font-semibold dark:text-white">Título</label><input wire:model.live.debounce.500ms="titulo" maxlength="100" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="mb-1 block text-xs font-semibold dark:text-white">Subtítulo (opcional)</label><input wire:model.live.debounce.500ms="subtitulo" maxlength="120" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
                <div><label class="mb-1 block text-xs font-semibold dark:text-white">Agrupar documentos por</label><select wire:model.live="agrupar" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="licenciatura_generacion">Licenciatura y generación</option><option value="licenciatura">Licenciatura</option><option value="generacion">Generación</option><option value="cuatrimestre">Cuatrimestre</option><option value="modalidad">Modalidad real</option><option value="sin_agrupar">Una lista única</option></select></div>
                <div><label class="mb-1 block text-xs font-semibold dark:text-white">Ordenar alumnos</label><select wire:model.live="orden" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="apellidos">Apellidos A-Z (recomendado)</option><option value="matricula">Matrícula</option><option value="nombre">Nombres A-Z</option><option value="inscripcion">Fecha de inscripción</option></select></div>
                <div><label class="mb-1 block text-xs font-semibold dark:text-white">Orientación</label><select wire:model.live="orientacion" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="auto">Automática según columnas</option><option value="horizontal">Carta horizontal</option><option value="vertical">Carta vertical</option></select></div>
                <div><label class="mb-1 block text-xs font-semibold dark:text-white">Observación para toda la lista</label><input wire:model.live.debounce.500ms="observacion_general" maxlength="300" placeholder="Dejar en blanco" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"></div>
            </div>
        </section>
    </div>

    {{-- Plantillas de usuario --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-wrap items-center gap-3">
            <div class="mr-auto"><h2 class="font-bold dark:text-white">03. Plantillas reutilizables</h2><p class="text-xs text-slate-500">Guarda filtros, columnas, títulos y estilos del documento. Privadas para tu usuario.</p></div>
            <input wire:model.live.debounce.400ms="nombre_plantilla" maxlength="100" placeholder="Nombre de la nueva plantilla" class="min-w-52 rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
            <button type="button" wire:click="guardarPlantilla" class="rounded-lg bg-[#88AC2E] px-4 py-2 text-sm font-bold text-white hover:bg-[#749327]">Guardar</button>
            <select wire:model.live="plantilla_id" class="min-w-48 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white"><option value="">Mis plantillas...</option>@foreach($plantillas as $p)<option value="{{ $p->id }}">{{ $p->nombre }}</option>@endforeach</select>
            <button type="button" wire:click="cargarPlantilla" @disabled(!$plantilla_id) class="rounded-lg border border-[#006492] px-4 py-2 text-sm font-semibold text-[#006492] disabled:opacity-40">Aplicar</button>
            <button type="button" wire:click="eliminarPlantilla" wire:confirm="¿Eliminar esta plantilla guardada?" @disabled(!$plantilla_id) class="rounded-lg border border-rose-200 px-3 py-2 text-sm text-rose-600 disabled:opacity-40">Eliminar</button>
        </div>
    </section>

    {{-- Vista previa + exportaciones --}}
    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800/60">
            <div><h2 class="text-lg font-bold dark:text-white">04. Previsualizar y descargar</h2><p class="text-sm text-slate-500">Vista previa de alumnos y selección individual. Descargas sin modificar datos.</p></div>
            @if($consultado)<div class="rounded-xl border border-sky-100 bg-white px-4 py-2 text-center dark:border-slate-700 dark:bg-slate-900"><p class="text-2xl font-black text-[#006492]">{{ number_format($total) }}</p><p class="text-xs text-slate-500">Alumnos encontrados</p></div>@endif
        </div>
        @if(!$consultado)
            <div class="p-10 text-center"><div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#006492]/10 text-3xl text-[#006492]">☷</div><p class="font-bold dark:text-white">Consulta los alumnos para generar la vista previa</p><p class="mt-1 text-sm text-slate-500">Puedes configurar primero las columnas y el formato del documento.</p></div>
        @elseif($total === 0)
            <div class="p-10 text-center text-sm text-amber-700">No hay alumnos que coincidan con los filtros. Ajusta tu selección.</div>
        @else
            @if($total > 6000)
                <p class="mx-5 mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">Hay más de 6000 alumnos en la consulta. Acota los filtros para descargar por lotes de hasta 6000.</p>
            @endif
            <div class="flex flex-wrap items-center gap-4 px-5 py-3 text-sm">
                <label class="flex cursor-pointer items-center gap-2 font-medium dark:text-white"><input type="checkbox" wire:model.live="todos_filtrados" class="rounded text-[#006492]"> Exportar los {{ $total }} alumnos filtrados</label>
                @if(!$todos_filtrados)
                    <button type="button" wire:click="seleccionarPagina" class="text-sm font-semibold text-[#006492] underline">Seleccionar esta página</button>
                    <button type="button" wire:click="limpiarSeleccion" class="text-sm font-medium text-slate-500 underline">Restablecer todos</button>
                    <span class="text-xs text-slate-500">{{ count($alumno_ids) }} seleccionados</span>
                @endif
            </div>
            <div class="overflow-x-auto px-5 pb-3"><table class="w-full min-w-[720px] border-collapse text-xs">
                <thead class="bg-[#006492] text-white"><tr><th class="rounded-tl-lg p-2">Elegir</th><th class="p-2 text-left">Matrícula</th><th class="p-2 text-left">Alumno</th><th class="p-2 text-left">Licenciatura</th><th class="p-2 text-left">Generación</th><th class="p-2 text-left">Modalidad impresa</th>@if(in_array('observaciones',$columnas))<th class="rounded-tr-lg p-2 text-left">Observaciones temporales</th>@endif</tr></thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($vista_previa as $a)
                        <tr wire:key="row-gen-{{ $a['id'] }}" class="hover:bg-slate-50 dark:hover:bg-slate-800">
                            <td class="p-2 text-center"><input type="checkbox" value="{{ $a['id'] }}" wire:model.live="alumno_ids" @disabled($todos_filtrados) aria-label="Seleccionar {{ $a['nombre'] }}" class="rounded text-[#006492]"></td>
                            <td class="p-2 font-semibold dark:text-slate-200">{{ $a['matricula'] }}</td><td class="p-2 dark:text-white">{{ $a['nombre'] }}</td><td class="p-2 dark:text-white">{{ $a['licenciatura'] }}</td><td class="p-2 dark:text-white">{{ $a['generacion'] }}</td>
                            <td class="p-2 dark:text-white">{{ match($modalidad_texto) {'escolarizada'=>'ESCOLARIZADA', 'semiescolarizada'=>'SEMIESCOLARIZADA',default=>$a['modalidad']} }}</td>
                            @if(in_array('observaciones',$columnas))<td class="p-2"><input wire:model.live.debounce.500ms="observaciones.{{ $a['id'] }}" maxlength="300" type="text" placeholder="Opcional" aria-label="Observación {{ $a['nombre'] }}" class="w-44 rounded border border-slate-200 px-2 py-1.5 text-xs dark:border-slate-700 dark:bg-slate-800 dark:text-white"></td>@endif
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
            <div class="flex items-center justify-between gap-3 px-5 pb-4 text-xs text-slate-500"><span>Página {{ $pagina }} de {{ (int) ceil($total/20) }} · Se muestran hasta 20 alumnos por página.</span><div class="flex gap-2"><button type="button" wire:click="cambiarPagina({{ $pagina - 1 }})" @disabled($pagina <= 1) class="rounded-lg border border-slate-200 px-3 py-2 disabled:opacity-30 dark:border-slate-700">← Anterior</button><button type="button" wire:click="cambiarPagina({{ $pagina + 1 }})" @disabled($pagina * 20 >= $total) class="rounded-lg border border-slate-200 px-3 py-2 disabled:opacity-30 dark:border-slate-700">Siguiente →</button></div></div>
            <div class="border-t border-slate-200 bg-[#006492]/5 p-5 dark:border-slate-700">
                <div class="mb-3"><h3 class="font-bold dark:text-white">Centro de descargas</h3><p class="text-xs text-slate-500">PDF y Word: un documento con las listas separadas por página. ZIP: archivo independiente por grupo. No se crean listas vacías.</p></div>
                @if(!$todos_filtrados && count($alumno_ids) === 0)<p class="mb-3 text-sm font-medium text-amber-700">Selecciona al menos un alumno o marca «Exportar todos».</p>@endif
                <form action="{{ route('admin.generador-listas.exportar') }}" method="POST" class="flex flex-wrap gap-2">
                    @csrf
                    @foreach($licenciatura_ids as $id)<input type="hidden" name="licenciatura_ids[]" value="{{ $id }}">@endforeach
                    @foreach($generacion_ids as $id)<input type="hidden" name="generacion_ids[]" value="{{ $id }}">@endforeach
                    @foreach($cuatrimestre_ids as $id)<input type="hidden" name="cuatrimestre_ids[]" value="{{ $id }}">@endforeach
                    @foreach($columnas as $campo)<input type="hidden" name="columnas[]" value="{{ $campo }}">@endforeach
                    @foreach($cabeceras as $campo => $etiqueta)@if(in_array($campo,$columnas))<input type="hidden" name="cabeceras[{{ $campo }}]" value="{{ $etiqueta }}">@endif @endforeach
                    @foreach($alumno_ids as $id)<input type="hidden" name="alumno_ids[]" value="{{ $id }}">@endforeach
                    @foreach($observaciones as $id => $nota)@if(trim((string) $nota)!=='')<input type="hidden" name="observaciones[{{ $id }}]" value="{{ $nota }}">@endif @endforeach
                    @foreach(['modalidad_id','procedencia','estado','ciclo_escolar','busqueda','modalidad_texto','agrupar','orden','orientacion','titulo','subtitulo','observacion_general'] as $prop)<input type="hidden" name="{{ $prop }}" value="{{ $this->{$prop} }}">@endforeach
                    <input type="hidden" name="todos_filtrados" value="{{ $todos_filtrados ? '1' : '0' }}">
                    @php($deshabilitado = count($columnas) < 1 || (!$todos_filtrados && count($alumno_ids) === 0))
                    <button name="formato" value="pdf" formtarget="_blank" type="submit" @disabled($deshabilitado) class="rounded-xl bg-[#006492] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#004f73] disabled:opacity-40">Abrir PDF</button>
                    <button name="formato" value="word" type="submit" @disabled($deshabilitado) class="rounded-xl border border-[#006492] bg-white px-4 py-2.5 text-sm font-bold text-[#006492] hover:bg-sky-50 disabled:opacity-40">Descargar Word</button>
                    <button name="formato" value="zip_pdf" type="submit" @disabled($deshabilitado) class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40">ZIP de PDF</button>
                    <button name="formato" value="zip_word" type="submit" @disabled($deshabilitado) class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 disabled:opacity-40">ZIP de Word</button>
                    <button name="formato" value="zip_ambos" type="submit" @disabled($deshabilitado) class="rounded-xl bg-[#88AC2E] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#789b28] disabled:opacity-40">ZIP PDF + Word</button>
                </form>
                <p class="mt-3 text-xs text-slate-500">Las descargas masivas se preparan en el servidor; si seleccionas muchos alumnos, el navegador tardará en recibir el archivo.</p>
            </div>
        @endif
    </section>
    @if($descargas->count())
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900"><h3 class="font-bold dark:text-white">Últimas exportaciones registradas</h3><div class="mt-3 overflow-x-auto"><table class="w-full text-left text-xs"><thead class="text-slate-500"><tr><th class="py-2">Fecha</th><th>Formato</th><th>Alumnos</th><th>Listas</th></tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700">@foreach($descargas as $d)<tr><td class="py-2 dark:text-white">{{ \Illuminate\Support\Carbon::parse($d->created_at)->format('d/m/Y H:i') }}</td><td class="dark:text-white">{{ strtoupper($d->formato) }}</td><td class="dark:text-white">{{ $d->total_alumnos }}</td><td class="dark:text-white">{{ $d->total_listas }}</td></tr>@endforeach</tbody></table></div></section>
    @endif
</div>
