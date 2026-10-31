<div class="space-y-5" id="acta-examen-pro">
    <div class="rounded-2xl border border-indigo-200/70 bg-gradient-to-r from-indigo-50 to-sky-50 p-5 dark:border-indigo-800 dark:from-indigo-950/40 dark:to-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-xl font-bold text-slate-900 dark:text-white">Acta de Examen Profesional <span class="text-indigo-600">PRO 2.0</span></h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Captura, revisa y autoriza actas de examen individuales o por lotes, con PDF y Word de dos páginas.</p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200 dark:bg-slate-800 dark:text-indigo-300">Control Escolar · Emisión documentada</span>
        </div>
    </div>

    @if($mensaje)
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200">{{ $mensaje }}</div>
    @endif
    @if($advertencia)
        <div role="alert" class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">{{ $advertencia }}</div>
    @endif
    @if($errors->any())
        <div role="alert" class="rounded-xl border border-red-300 bg-red-50 p-3 text-sm text-red-900">
            <strong>Revisa la información:</strong>
            <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
        <h4 class="font-bold text-slate-900 dark:text-white">1. Seleccionar sustentantes</h4>
        <p class="mt-1 text-xs text-neutral-500">La búsqueda admite nombre, apellidos en cualquier orden, matrícula, CURP o folio. Máximo 50 por lote; para cantidades mayores, descarga por bloques.</p>
        <div class="mt-4 grid gap-3 md:grid-cols-4">
            <flux:select wire:model.live="modo" label="Generar por">
                <flux:select.option value="alumno">Alumno individual</flux:select.option>
                <flux:select.option value="licenciatura">Licenciatura</flux:select.option>
                <flux:select.option value="generacion">Generación</flux:select.option>
                <flux:select.option value="multiple">Selección múltiple</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="licenciaturaId" label="Licenciatura">
                <flux:select.option value="">Todas</flux:select.option>
                @foreach($licenciaturas as $item)<flux:select.option value="{{ $item->id }}">{{ $item->nombre }}</flux:select.option>@endforeach
            </flux:select>
            <flux:select wire:model.live="generacionId" label="Generación">
                <flux:select.option value="">Todas</flux:select.option>
                @foreach($generaciones as $item)<flux:select.option value="{{ $item->id }}">{{ $item->generacion }}</flux:select.option>@endforeach
            </flux:select>
            <flux:input wire:model.live.debounce.350ms="busqueda" label="Buscar estudiante" placeholder="Nombre, matrícula, apellidos, CURP..." clearable />
        </div>
        @if($modo !== 'alumno' || trim($busqueda) !== '' || count($alumnoIds))
            <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-neutral-500">Coincidencias: <strong>{{ $totalAlumnos }}</strong> · Seleccionados: <strong>{{ count($alumnoIds) }}</strong></p>
                @if($modo !== 'alumno')
                    <div class="flex gap-2">
                        <flux:button wire:click="seleccionarVisibles" size="sm" variant="outline">Seleccionar todos (máx. 50)</flux:button>
                        <flux:button wire:click="$set('alumnoIds', [])" size="sm" variant="ghost">Limpiar</flux:button>
                    </div>
                @endif
            </div>
            <div class="mt-2 max-h-64 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full min-w-[700px] text-left text-xs"><thead class="sticky top-0 bg-slate-100 text-slate-700 dark:bg-neutral-800 dark:text-slate-200"><tr><th class="p-2">Elegir</th><th class="p-2">Estudiante</th><th class="p-2">Matrícula</th><th class="p-2">Licenciatura</th><th class="p-2">Generación</th><th class="p-2">Egreso</th></tr></thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($alumnos->take(50) as $alumno)
                <tr wire:key="acta-alumno-{{ $alumno->id }}" class="hover:bg-indigo-50/50 dark:hover:bg-neutral-800">
                    <td class="p-2">
                    @if($modo === 'alumno')
                        <flux:button wire:click="seleccionarAlumno({{ $alumno->id }})" size="xs" variant="outline">Elegir</flux:button>
                    @else
                        <input type="checkbox" wire:model.live="alumnoIds" value="{{ $alumno->id }}" class="rounded border-neutral-300 text-indigo-600" aria-label="Seleccionar {{ $alumno->nombre }} {{ $alumno->apellido_paterno }}">
                    @endif
                    </td>
                    <td class="p-2 font-medium">{{ trim($alumno->apellido_paterno.' '.$alumno->apellido_materno.' '.$alumno->nombre) }}</td>
                    <td class="p-2">{{ $alumno->matricula ?: $alumno->matricula_interna }}</td>
                    <td class="p-2">{{ $alumno->licenciatura?->nombre }}</td>
                    <td class="p-2">{{ $alumno->generacion?->generacion }}</td>
                    <td class="p-2">@if($alumno->egresado === 'true')<span class="text-emerald-700">Egresado</span>@else<span class="text-amber-600">Pendiente</span>@endif</td>
                </tr>
                @endforeach
                </tbody></table>
            </div>
            @if($totalAlumnos > 50)<p class="mt-2 text-xs text-amber-700">Se muestran las primeras 50 coincidencias. Filtra por búsqueda, generación o licenciatura para encontrar otros alumnos.</p>@endif
        @endif
        @if(count($alumnoIds) > 0)
        <div class="mt-3 flex flex-wrap items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50 px-4 py-3 dark:border-indigo-900 dark:bg-indigo-950/30">
            <span class="text-sm font-semibold text-indigo-900 dark:text-indigo-200">{{ count($alumnoIds) }} seleccionado(s)</span>
            @if($actaId)<span class="text-xs text-indigo-700">Editando acta #{{ $actaId }}</span>@endif
        </div>
        @endif
    </section>

    <form wire:submit="guardar" class="space-y-4">
        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
            <h4 class="font-bold text-slate-900 dark:text-white">2. Datos del examen y autorización oficial</h4>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <flux:input wire:model="fechaExamen" type="date" label="Fecha del examen *" />
                <flux:input wire:model="horaExamen" type="time" label="Hora del examen *" />
                <flux:input wire:model="numeroAutorizacion" label="Núm. de autorización del examen" />
                <flux:input wire:model="entidad" label="Entidad federativa" />
                <div class="sm:col-span-2"><flux:input wire:model="titulo" label="Título profesional (individual / reemplazo)" placeholder="En lotes se utilizará el de cada licenciatura" /></div>
                <flux:input wire:model="acuerdoNumero" label="Acuerdo RVOE (individual / reemplazo)" />
                <flux:input wire:model="acuerdoFecha" type="date" label="Fecha del acuerdo" />
                <flux:select wire:model="resultado" label="Resultado del examen">
                    <flux:select.option value="">Pendiente · acta preliminar</flux:select.option>
                    <flux:select.option value="Aprobado">Aprobado</flux:select.option>
                    <flux:select.option value="Aprobado por unanimidad">Aprobado por unanimidad</flux:select.option>
                    <flux:select.option value="Aprobado por mayoría">Aprobado por mayoría</flux:select.option>
                    <flux:select.option value="No aprobado">No aprobado</flux:select.option>
                </flux:select>
            </div>
            <p class="mt-3 text-xs text-slate-500">En generaciones que incluyan distintas licenciaturas, el título y el RVOE se asignan por alumno automáticamente. El número de autorización del examen se registra por separado.</p>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2"><h4 class="font-bold text-slate-900 dark:text-white">3. Sínodo profesional</h4><span class="text-xs text-neutral-500">El orden determina Presidencia, Secretaría y Vocalía.</span></div>
            <div class="mt-3 grid gap-3 md:grid-cols-3">
                @foreach(['Presidente(a)', 'Secretario(a)', 'Vocal'] as $i => $cargo)
                    <div wire:key="sinodo-{{ $i }}" class="rounded-xl border border-slate-200 p-3 dark:border-neutral-700">
                        <div class="mb-2 text-sm font-semibold text-indigo-700 dark:text-indigo-300">{{ $cargo }}</div>
                        <flux:select wire:model.live="sinodo.{{ $i }}.profesor_id" label="Profesor registrado">
                            <flux:select.option value="">Persona externa / captura manual</flux:select.option>
                            @foreach($profesores as $profesor)<flux:select.option value="{{ $profesor->id }}">{{ $profesor->apellido_paterno }} {{ $profesor->apellido_materno }} {{ $profesor->nombre }}</flux:select.option>@endforeach
                        </flux:select>
                        <div class="mt-2"><flux:input wire:model="sinodo.{{ $i }}.nombre" label="Nombre para el acta *" /></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                <flux:input wire:model="nombrePlantilla" label="Nombre de configuración" placeholder="Sínodo generación 2023" />
                <div class="flex items-end"><flux:button type="button" wire:click="guardarPlantilla" variant="outline">Guardar sínodo reutilizable</flux:button></div>
                <div class="flex items-end gap-2">
                    <div class="grow"><flux:select wire:model="plantillaId" label="Plantillas guardadas"><flux:select.option value="">Seleccionar</flux:select.option>@foreach($plantillas as $plantilla)<flux:select.option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</flux:select.option>@endforeach</flux:select></div>
                    <flux:button type="button" wire:click="aplicarPlantilla" variant="outline">Aplicar</flux:button>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
            <h4 class="font-bold text-slate-900 dark:text-white">4. Autoridades, firmas y control administrativo</h4>
            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <flux:input wire:model="rector" label="Rector(a)" />
                <flux:input wire:model="director" label="Director(a)" />
                <flux:input wire:model="revisado" label="Revisado y confrontado por" />
                <flux:input wire:model="jefeRegistro" label="Jefe(a) de registro y certificación" />
                <flux:input wire:model="fechaRevision" label="Fecha de revisión" type="date" />
                <div class="flex items-center pt-4"><flux:checkbox wire:model="incluirFoto" label="Incluir fotografía escolar si existe" /></div>
            </div>
            <p class="mt-3 text-xs text-neutral-500">Las firmas quedan en blanco para firmarse de manera manuscrita. No se insertarán firmas digitales automáticamente.</p>
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-900 dark:bg-amber-950/20">
            <h4 class="font-bold text-amber-900 dark:text-amber-200">5. Excepción de egreso y validaciones</h4>
            <div class="mt-2"><flux:checkbox wire:model.live="permitirExcepcion" label="Autorizo documentar una excepción para alumnos sin egreso acreditado" /></div>
            @if($permitirExcepcion)
                <div class="mt-3"><flux:textarea wire:model="motivoExcepcion" label="Motivo justificado de la excepción (mínimo 10 caracteres)" rows="2" /></div>
            @endif
            <p class="mt-2 text-xs text-amber-800 dark:text-amber-300">No se cambia el estatus académico del estudiante. El usuario y la justificación quedarán registrados en el historial del acta.</p>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900">
            <p class="text-xs text-neutral-500">Guardar crea borradores y no autoriza resultados. Los documentos preliminares se identifican como no autorizados.</p>
            <flux:button type="submit" variant="primary" icon="document-check" wire:loading.attr="disabled" wire:target="guardar" :disabled="count($alumnoIds) === 0 || ($actaActual && $actaActual->estado !== 'borrador')">Guardar acta(s) · borrador</flux:button>
        </div>
    </form>

    @if($actaActual)
    <section class="rounded-2xl border border-sky-200 bg-sky-50/40 p-4 dark:border-sky-900 dark:bg-slate-900 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2"><h4 class="font-bold text-slate-900 dark:text-white">6. Validación y emisión · Acta #{{ $actaActual->id }}</h4><span class="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase text-indigo-700 dark:bg-neutral-800">{{ $actaActual->estado }}</span></div>
        <div class="mt-3 flex flex-wrap gap-2">
            <a class="inline-flex rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white" href="{{ route('admin.actas-examen.pdf', $actaActual) }}" target="_blank" rel="noopener">Ver PDF</a>
            <a class="inline-flex rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white" href="{{ route('admin.actas-examen.word', $actaActual) }}">Descargar Word</a>
            @if($actaActual->estado === 'borrador')<flux:button type="button" wire:click="revision" variant="outline">Enviar a revisión</flux:button>@endif
            @if($actaActual->estado === 'revision')
                <flux:button type="button" wire:click="volverBorrador" variant="outline">Devolver a borrador</flux:button>
                <flux:button type="button" wire:click="autorizar" variant="primary" wire:confirm="¿Confirmas la autorización del acta? El contenido quedará bloqueado.">Autorizar acta definitiva</flux:button>
            @endif
        </div>
        @if($actaActual->estado !== 'cancelada')
        <div class="mt-3 flex flex-wrap items-end gap-3"><div class="w-full max-w-xl"><flux:input wire:model="motivoCancelacion" label="Motivo de cancelación (mín. 10 caracteres)" placeholder="Justificación administrativa" /></div><flux:button type="button" wire:click="cancelar" variant="danger" wire:confirm="¿Cancelar esta acta? Se conservará el historial.">Cancelar</flux:button></div>
        @endif
        <p class="mt-3 text-xs text-neutral-500">Una autorización no equivale a firma digital. La versión emitida requiere las firmas institucionales correspondientes.</p>
        <details class="mt-4 rounded-xl border border-sky-200 bg-white p-3 dark:border-sky-900 dark:bg-neutral-900">
            <summary class="cursor-pointer text-sm font-semibold">Historial de movimientos de esta acta ({{ $actaActual->historial()->count() }})</summary>
            <div class="mt-3 space-y-2">
                @forelse($actaActual->historial()->limit(40)->get() as $movimiento)
                    <div class="flex flex-wrap justify-between gap-2 border-b border-neutral-100 pb-2 text-xs dark:border-neutral-800">
                        <span><strong>{{ str_replace('_', ' ', ucfirst($movimiento->accion)) }}</strong> · usuario #{{ $movimiento->usuario_id ?? 'sistema' }}</span>
                        <span class="text-neutral-500">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</span>
                        @if($movimiento->accion === 'acta_cancelada' && isset($movimiento->detalles['motivo']))<p class="w-full text-rose-700">Motivo: {{ $movimiento->detalles['motivo'] }}</p>@endif
                    </div>
                @empty
                    <p class="text-xs text-neutral-500">Sin movimientos registrados.</p>
                @endforelse
            </div>
        </details>
    </section>
    @endif

    <section class="rounded-2xl border border-neutral-200 bg-white p-4 shadow-sm dark:border-neutral-700 dark:bg-neutral-900 sm:p-5">
        <h4 class="font-bold text-slate-900 dark:text-white">7. Exportación masiva · hasta 50 actas</h4>
        <p class="mt-1 text-xs text-neutral-500">Marca actas guardadas. Las actas no autorizadas aparecerán como preliminares. ZIP agrupado por licenciatura y generación.</p>
        <div class="mt-3 max-w-xl"><flux:input wire:model.live.debounce.400ms="historialBusqueda" label="Buscar actas en el historial" placeholder="Nombre, matrícula o licenciatura..." /></div>
        <form action="{{ route('admin.actas-examen.exportar') }}" method="POST" target="_blank" class="mt-3 space-y-3">
            @csrf
            <div class="max-h-48 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
                <table class="w-full min-w-[650px] text-xs"><thead class="sticky top-0 bg-slate-100 dark:bg-neutral-800"><tr><th class="p-2">Incluir</th><th class="p-2">Acta</th><th class="p-2">Sustentante</th><th class="p-2">Fecha</th><th class="p-2">Estado</th><th class="p-2">Editar</th></tr></thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @foreach($actasRecientes as $acta)
                    <tr wire:key="acta-hist-{{ $acta->id }}">
                        <td class="p-2"><input type="checkbox" name="ids[]" value="{{ $acta->id }}" @checked(in_array($acta->id, $actasExportacion)) class="rounded text-indigo-600"></td>
                        <td class="p-2">#{{ $acta->id }}</td>
                        <td class="p-2">{{ $acta->datos_alumno['apellidos_nombre'] ?? '' }}</td>
                        <td class="p-2">{{ $acta->fecha_examen?->format('d/m/Y') }}</td>
                        <td class="p-2">{{ ucfirst($acta->estado) }}</td>
                        <td class="p-2"><flux:button type="button" size="xs" variant="outline" wire:click="cargarActa({{ $acta->id }})">Abrir</flux:button></td>
                    </tr>
                @endforeach
                </tbody></table>
            </div>
            <div class="grid gap-3 sm:grid-cols-3">
                <div><label class="mb-1 block text-xs font-semibold">Formato</label><select name="formato" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm dark:bg-neutral-800"><option value="ambos">PDF y Word</option><option value="pdf">Solo PDF</option><option value="word">Solo Word</option></select></div>
                <div><label class="mb-1 block text-xs font-semibold">Salida</label><select name="salida" class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm dark:bg-neutral-800"><option value="separados">ZIP por licenciatura y generación</option><option value="consolidado">Archivo consolidado</option></select></div>
                <div class="flex items-end"><flux:button type="submit" variant="primary">Exportar seleccionados</flux:button></div>
            </div>
        </form>
        <div class="mt-4 border-t pt-3 text-xs text-neutral-500">Se muestran hasta 50 actas por búsqueda, ordenadas de la más reciente a la más antigua. La emisión definitiva exige revisión y autorización.</div>
    </section>
</div>
