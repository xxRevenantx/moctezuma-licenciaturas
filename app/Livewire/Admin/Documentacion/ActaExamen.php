<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\ActaExamenProfesional;
use App\Models\ActaExamenSinodoPlantilla;
use App\Models\Directivo;
use App\Models\Escuela;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Profesor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ActaExamen extends Component
{
    public string $modo = 'alumno';
    public string $busqueda = '';
    public string $licenciaturaId = '';
    public string $generacionId = '';
    public array $alumnoIds = [];
    public ?int $actaId = null;
    public string $fechaExamen = '';
    public string $horaExamen = '13:00';
    public string $numeroAutorizacion = '001';
    public string $entidad = 'Guerrero';
    public string $titulo = '';
    public string $acuerdoNumero = '';
    public string $acuerdoFecha = '';
    public string $resultado = '';
    public string $rector = '';
    public string $director = '';
    public string $revisado = '';
    public string $jefeRegistro = '';
    public string $fechaRevision = '';
    public bool $incluirFoto = false;
    public bool $permitirExcepcion = false;
    public string $motivoExcepcion = '';
    public array $sinodo = [
        ['profesor_id' => '', 'nombre' => ''],
        ['profesor_id' => '', 'nombre' => ''],
        ['profesor_id' => '', 'nombre' => ''],
    ];
    public string $nombrePlantilla = '';
    public string $plantillaId = '';
    public array $actasExportacion = [];
    public string $formato = 'ambos';
    public string $salida = 'separados';
    public string $mensaje = '';
    public string $advertencia = '';
    public string $motivoCancelacion = '';
    public string $historialBusqueda = '';

    public function mount(): void
    {
        $this->fechaExamen = now()->toDateString();
        $this->fechaRevision = now()->toDateString();
        $directivos = Directivo::where('status', 'true')->get();
        foreach ($directivos as $directivo) {
            $nombre = trim(implode(' ', array_filter([$directivo->titulo, $directivo->nombre, $directivo->apellido_paterno, $directivo->apellido_materno])));
            $clave = mb_strtolower($directivo->identificador . ' ' . $directivo->cargo);
            if (! $this->rector && str_contains($clave, 'rector')) $this->rector = $nombre;
            if (! $this->director && (str_contains($clave, 'director') || str_contains($clave, 'directora'))) $this->director = $nombre;
            if (! $this->revisado && str_contains($clave, 'revisado')) $this->revisado = $nombre;
            if (! $this->jefeRegistro && str_contains($clave, 'registro')) $this->jefeRegistro = $nombre;
        }
    }

    public function updatedModo(): void
    {
        $this->alumnoIds = [];
        $this->actaId = null;
        $this->advertencia = '';
    }

    public function updatedLicenciaturaId(): void { $this->alumnoIds = []; }
    public function updatedGeneracionId(): void { $this->alumnoIds = []; }

    public function updatedSinodo($value, $key): void
    {
        if (! str_ends_with((string) $key, '.profesor_id') || ! $value) return;
        $index = (int) explode('.', (string) $key)[0];
        $profesor = Profesor::find($value);
        if (! $profesor || ! isset($this->sinodo[$index])) return;
        $this->sinodo[$index]['nombre'] = trim($profesor->nombre . ' ' . $profesor->apellido_paterno . ' ' . $profesor->apellido_materno);
    }

    public function seleccionarAlumno(int $id): void
    {
        $alumno = Inscripcion::with('licenciatura')->findOrFail($id);
        $this->alumnoIds = [$id];
        $this->actaId = null;
        $this->titulo = $alumno->licenciatura?->nombre ?? '';
        $this->acuerdoNumero = (string) ($alumno->licenciatura?->RVOE ?? '');
        $this->acuerdoFecha = $alumno->licenciatura?->fecha_acuerdo?->format('Y-m-d') ?? '';
    }

    public function seleccionarVisibles(): void
    {
        $query = $this->alumnosQuery();
        $cantidad = (clone $query)->count();
        if ($cantidad > 50) {
            $this->advertencia = "Hay {$cantidad} estudiantes. Aplica más filtros o marca manualmente un máximo de 50; no se seleccionarán registros parcialmente.";
            return;
        }
        $this->alumnoIds = $query->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->advertencia = '';
    }

    private function alumnosQuery()
    {
        $q = Inscripcion::query()->with(['licenciatura', 'generacion']);
        // No omite egresados dados de baja; la elegibilidad se valida antes de guardar.
        if ($this->modo === 'licenciatura' || $this->licenciaturaId !== '') {
            if ($this->licenciaturaId === '') return $q->whereRaw('1=0');
            $q->where('licenciatura_id', $this->licenciaturaId);
        }
        if ($this->modo === 'generacion' || $this->generacionId !== '') {
            if ($this->generacionId === '') return $q->whereRaw('1=0');
            $q->where('generacion_id', $this->generacionId);
        }
        if (trim($this->busqueda) !== '') {
            $terminos = preg_split('/\s+/u', trim($this->busqueda));
            foreach (array_slice($terminos, 0, 6) as $termino) {
                $like = '%' . addcslashes($termino, '%_\\') . '%';
                $q->where(function ($sub) use ($like) {
                    $sub->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like)
                        ->orWhere('apellido_materno', 'like', $like)
                        ->orWhere('matricula', 'like', $like)
                        ->orWhere('matricula_interna', 'like', $like)
                        ->orWhere('CURP', 'like', $like)
                        ->orWhere('folio', 'like', $like);
                });
            }
        }
        if ($this->modo === 'alumno' && trim($this->busqueda) === '') {
            return count($this->alumnoIds) === 1 ? $q->whereKey((int) $this->alumnoIds[0]) : $q->whereRaw('1=0');
        }
        return $q->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre');
    }

    private function reglas(): array
    {
        return [
            'fechaExamen' => 'required|date',
            'horaExamen' => 'required|date_format:H:i',
            'numeroAutorizacion' => 'nullable|string|max:80',
            'entidad' => 'required|string|max:100',
            'titulo' => 'nullable|string|max:255',
            'acuerdoNumero' => 'nullable|string|max:90',
            'acuerdoFecha' => 'nullable|date',
            'resultado' => 'nullable|in:Aprobado,Aprobado por unanimidad,Aprobado por mayoría,No aprobado',
            'sinodo' => 'required|array|size:3',
            'sinodo.*.nombre' => 'required|string|max:255',
            'sinodo.*.profesor_id' => 'nullable',
            'rector' => 'nullable|string|max:255',
            'director' => 'nullable|string|max:255',
            'revisado' => 'nullable|string|max:255',
            'jefeRegistro' => 'nullable|string|max:255',
            'fechaRevision' => 'nullable|date',
            'motivoExcepcion' => 'nullable|string|max:1000',
            'alumnoIds' => 'required|array|min:1|max:50',
            'alumnoIds.*' => 'required|integer|distinct|exists:inscripciones,id',
        ];
    }

    public function guardar(): void
    {
        $this->mensaje = '';
        $data = $this->validate($this->reglas());
        $ids = array_map('intval', $data['alumnoIds']);
        if ($this->modo === 'alumno' && count($ids) !== 1) {
            throw ValidationException::withMessages(['alumnoIds' => 'En modo individual selecciona un solo alumno.']);
        }
        // Un filtro de licenciatura/generación nunca puede aceptar IDs de otro contexto.
        $alumnos = $this->alumnosQuery()->whereIn('id', $ids)->get();
        if ($alumnos->count() !== count($ids)) {
            throw ValidationException::withMessages(['alumnoIds' => 'La selección no corresponde con los filtros actuales.']);
        }
        $excepciones = $alumnos->filter(fn ($a) => $a->egresado !== 'true');
        if ($excepciones->isNotEmpty() && (! $this->permitirExcepcion || mb_strlen(trim($this->motivoExcepcion)) < 10)) {
            throw ValidationException::withMessages(['motivoExcepcion' => 'Hay alumnos sin egreso acreditado. Activa la excepción y escribe un motivo de al menos 10 caracteres.']);
        }
        $escuela = Escuela::query()->first();
        $creados = 0;
        $omitidos = 0;
        $seleccion = [];
        DB::transaction(function () use ($alumnos, $escuela, &$creados, &$omitidos, &$seleccion) {
            foreach ($alumnos as $alumno) {
                $registro = $this->actaId ? ActaExamenProfesional::lockForUpdate()->findOrFail($this->actaId) : null;
                if ($registro && ($registro->alumno_id !== $alumno->id || $registro->estado !== 'borrador')) {
                    throw ValidationException::withMessages(['actaId' => 'No se puede editar un acta autorizada, cancelada o de otro estudiante.']);
                }
                if (! $registro) {
                    $registro = ActaExamenProfesional::where('alumno_id', $alumno->id)
                        ->whereDate('fecha_examen', $this->fechaExamen)
                        ->where('estado', '!=', 'cancelada')->lockForUpdate()->first();
                    if ($registro) { $omitidos++; $seleccion[] = $registro->id; continue; }
                    $registro = new ActaExamenProfesional();
                    $registro->creado_por = auth()->id();
                }
                $contexto = [
                    'nombre_completo' => trim(implode(' ', array_filter([$alumno->nombre, $alumno->apellido_paterno, $alumno->apellido_materno]))),
                    'apellidos_nombre' => trim(implode(' ', array_filter([$alumno->apellido_paterno, $alumno->apellido_materno, $alumno->nombre]))),
                    'matricula' => $alumno->matricula ?: $alumno->matricula_interna,
                    'CURP' => $alumno->CURP,
                    'licenciatura' => $alumno->licenciatura?->nombre,
                    'generacion' => $alumno->generacion?->generacion,
                    'licenciatura_id' => $alumno->licenciatura_id,
                    'generacion_id' => $alumno->generacion_id,
                    'egresado_al_guardar' => $alumno->egresado === 'true',
                    'cct' => $escuela?->CCT ?: '12PSU0173I',
                ];
                $registro->fill([
                    'alumno_id' => $alumno->id,
                    'fecha_examen' => $this->fechaExamen,
                    'hora_examen' => $this->horaExamen,
                    'autorizacion_numero' => trim($this->numeroAutorizacion),
                    'entidad_federativa' => trim($this->entidad),
                    'titulo_profesional' => trim(($this->modo === 'alumno' ? $this->titulo : '') ?: ($alumno->licenciatura?->nombre ?? '')),
                    'acuerdo_numero' => ($this->modo === 'alumno' ? $this->acuerdoNumero : '') ?: ($alumno->licenciatura?->RVOE ?? null),
                    'acuerdo_fecha' => ($this->modo === 'alumno' ? $this->acuerdoFecha : '') ?: $alumno->licenciatura?->fecha_acuerdo,
                    'sinodo' => array_values($this->sinodo),
                    'resultado' => $this->resultado ?: null,
                    'rector' => $this->rector,
                    'director' => $this->director,
                    'revisado_por' => $this->revisado,
                    'jefe_registro' => $this->jefeRegistro,
                    'fecha_revision' => $this->fechaRevision ?: null,
                    'incluir_foto' => $this->incluirFoto,
                    'excepcion_egreso' => $alumno->egresado !== 'true',
                    'motivo_excepcion' => $alumno->egresado !== 'true' ? trim($this->motivoExcepcion) : null,
                    'excepcion_autorizada_por' => $alumno->egresado !== 'true' ? auth()->id() : null,
                    'datos_alumno' => $contexto,
                ]);
                if (! $registro->exists) $registro->estado = 'borrador';
                $registro->save();
                // La fotografía opcional se congela: un cambio posterior en la ficha no altera el acta.
                if ($this->incluirFoto) {
                    $foto = $alumno->fotoArchivo();
                    if ($foto) {
                        $extension = match ($foto['mime']) {
                            'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
                            default => 'jpg',
                        };
                        $ruta = 'actas-examen/fotos/acta_' . $registro->id . '.' . $extension;
                        Storage::disk('local')->put($ruta, $foto['content']);
                        $registro->update(['foto_snapshot_path' => $ruta]);
                    }
                }
                $registro->registrar($registro->wasRecentlyCreated ? 'creacion_borrador' : 'edicion_borrador', [
                    'excepcion' => $registro->excepcion_egreso,
                    'campos' => $registro->getChanges(),
                ]);
                $creados++;
                $seleccion[] = $registro->id;
            }
        });
        $this->actasExportacion = array_values(array_unique($seleccion));
        $this->mensaje = "Se guardaron {$creados} acta(s) en borrador. Existentes sin sobrescribir: {$omitidos}.";
        if (count($seleccion) === 1) $this->actaId = (int) $seleccion[0];
    }

    public function cargarActa(int $id): void
    {
        $acta = ActaExamenProfesional::findOrFail($id);
        $this->modo = 'alumno';
        $this->busqueda = '';
        $this->licenciaturaId = '';
        $this->generacionId = '';
        $this->alumnoIds = [(string) $acta->alumno_id];
        $this->actaId = $acta->id;
        $this->fechaExamen = $acta->fecha_examen->format('Y-m-d');
        $this->horaExamen = substr((string) $acta->hora_examen, 0, 5);
        $this->numeroAutorizacion = (string) $acta->autorizacion_numero;
        $this->entidad = (string) $acta->entidad_federativa;
        $this->titulo = (string) $acta->titulo_profesional;
        $this->acuerdoNumero = (string) $acta->acuerdo_numero;
        $this->acuerdoFecha = $acta->acuerdo_fecha?->format('Y-m-d') ?? '';
        $this->sinodo = $acta->sinodo;
        $this->resultado = (string) $acta->resultado;
        $this->rector = (string) $acta->rector;
        $this->director = (string) $acta->director;
        $this->revisado = (string) $acta->revisado_por;
        $this->jefeRegistro = (string) $acta->jefe_registro;
        $this->fechaRevision = $acta->fecha_revision?->format('Y-m-d') ?? '';
        $this->incluirFoto = $acta->incluir_foto;
        $this->permitirExcepcion = $acta->excepcion_egreso;
        $this->motivoExcepcion = (string) $acta->motivo_excepcion;
        $this->mensaje = "Acta #{$acta->id} cargada ({$acta->estado}).";
    }

    public function revision(): void
    {
        $acta = ActaExamenProfesional::findOrFail($this->actaId);
        if ($acta->estado !== 'borrador') return;
        $acta->update(['estado' => 'revision']);
        $acta->registrar('enviada_a_revision');
        $this->mensaje = 'Acta enviada a revisión. Verifica sus datos antes de autorizar.';
    }

    public function autorizar(): void
    {
        DB::transaction(function () {
            $acta = ActaExamenProfesional::lockForUpdate()->findOrFail($this->actaId);
            if ($acta->estado !== 'revision') throw ValidationException::withMessages(['actaId' => 'Solo se pueden autorizar actas en revisión.']);
            if (! $acta->resultado || ! $acta->autorizacion_numero || ! $acta->titulo_profesional || ! $acta->acuerdo_numero || ! $acta->acuerdo_fecha || ! $acta->rector || ! $acta->director) {
                throw ValidationException::withMessages(['actaId' => 'Faltan resultado, autorización, título, RVOE, fecha de acuerdo o autoridades. Regresa a borrador, completa y guarda.']);
            }
            if (count($acta->sinodo ?? []) !== 3 || collect($acta->sinodo)->contains(fn ($p) => empty(trim((string) ($p['nombre'] ?? ''))))) {
                throw ValidationException::withMessages(['actaId' => 'Falta identificar a los tres integrantes del sínodo.']);
            }
            if ($acta->excepcion_egreso && (! $acta->motivo_excepcion || ! $acta->excepcion_autorizada_por)) {
                throw ValidationException::withMessages(['actaId' => 'La excepción de egreso requiere motivo y autorización registrada.']);
            }
            $acta->update(['estado' => 'autorizada', 'autorizado_por' => auth()->id(), 'autorizado_en' => now()]);
            $acta->registrar('acta_autorizada', ['resultado' => $acta->resultado]);
        });
        $this->mensaje = 'Acta autorizada. El contenido se bloqueó para conservar la integridad del documento.';
    }

    public function volverBorrador(): void
    {
        $acta = ActaExamenProfesional::findOrFail($this->actaId);
        if ($acta->estado !== 'revision') return;
        $acta->update(['estado' => 'borrador']);
        $acta->registrar('devuelta_a_borrador');
        $this->mensaje = 'Acta devuelta a borrador para su corrección.';
    }

    public function cancelar(): void
    {
        $this->validate(['motivoCancelacion' => 'required|string|min:10|max:500']);
        $acta = ActaExamenProfesional::findOrFail($this->actaId);
        if ($acta->estado === 'cancelada') return;
        $acta->update(['estado' => 'cancelada']);
        $acta->registrar('acta_cancelada', ['motivo' => $this->motivoCancelacion]);
        $this->mensaje = 'Acta cancelada; el historial permanece disponible.';
    }

    public function guardarPlantilla(): void
    {
        $this->validate([
            'nombrePlantilla' => 'required|string|min:3|max:150',
            'sinodo' => 'array|size:3',
            'sinodo.*.nombre' => 'required|string|max:255',
        ]);
        ActaExamenSinodoPlantilla::create([
            'nombre' => $this->nombrePlantilla,
            'integrantes' => $this->sinodo,
            'creado_por' => auth()->id(),
        ]);
        $this->nombrePlantilla = '';
        $this->mensaje = 'Configuración del sínodo guardada para reutilizarse.';
    }

    public function aplicarPlantilla(): void
    {
        if (! $this->plantillaId) return;
        $this->sinodo = ActaExamenSinodoPlantilla::findOrFail($this->plantillaId)->integrantes;
    }

    public function render()
    {
        return view('livewire.admin.documentacion.acta-examen', [
            'alumnos' => $this->alumnosQuery()->limit(51)->get(),
            'totalAlumnos' => $this->alumnosQuery()->count(),
            'licenciaturas' => Licenciatura::orderBy('nombre')->get(['id', 'nombre']),
            'generaciones' => Generacion::orderByDesc('id')->get(['id', 'generacion']),
            'profesores' => Profesor::where('status', 'true')->orderBy('apellido_paterno')->orderBy('nombre')->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
            'plantillas' => ActaExamenSinodoPlantilla::latest()->get(),
            'actasRecientes' => ActaExamenProfesional::query()
                ->when(trim($this->historialBusqueda) !== '', function ($q) {
                    $texto = '%' . trim($this->historialBusqueda) . '%';
                    $q->where(function ($s) use ($texto) {
                        $s->where('datos_alumno->nombre_completo', 'like', $texto)
                          ->orWhere('datos_alumno->matricula', 'like', $texto)
                          ->orWhere('datos_alumno->licenciatura', 'like', $texto);
                    });
                })->latest()->limit(50)->get(),
            'actaActual' => $this->actaId ? ActaExamenProfesional::find($this->actaId) : null,
        ]);
    }
}
