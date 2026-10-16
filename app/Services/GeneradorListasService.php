<?php

namespace App\Services;

use App\Models\Escuela;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Consultas de solo lectura. Nunca actualiza inscripciones ni modalidades. */
class GeneradorListasService
{
    public const CAMPOS = [
        'numero' => 'No.', 'matricula' => 'Matrícula', 'matricula_interna' => 'Matrícula interna',
        'nombre' => 'Nombre(s)', 'apellido_paterno' => 'Apellido paterno', 'apellido_materno' => 'Apellido materno',
        'nombre_completo' => 'Nombre completo', 'CURP' => 'CURP', 'sexo' => 'Sexo',
        'fecha_nacimiento' => 'Nacimiento', 'edad' => 'Edad actual', 'licenciatura' => 'Licenciatura',
        'generacion' => 'Generación', 'cuatrimestre' => 'Cuatrimestre', 'modalidad' => 'Modalidad',
        'procedencia' => 'Procedencia', 'telefono' => 'Teléfono', 'celular' => 'Celular',
        'municipio' => 'Municipio', 'colonia' => 'Colonia', 'tutor' => 'Tutor',
        'estatus' => 'Estatus', 'observaciones' => 'Observaciones',
    ];

    public const DEFAULT_COLUMNAS = [
        'numero', 'matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'CURP', 'modalidad', 'observaciones',
    ];

    public function consulta(array $filtros): Builder
    {
        $historico = ! empty($filtros['ciclo_escolar']);
        $q = DB::table('inscripciones as i');

        if ($historico) {
            // La última fotografía del alumno DENTRO del ciclo solicitado, no su estado actual.
            $ultimos = DB::table('historial_inscripciones')
                ->selectRaw('MAX(id) as id')
                ->where('ciclo_escolar', $filtros['ciclo_escolar'])
                ->whereNotNull('inscripcion_id')
                ->groupBy('inscripcion_id');
            $q = DB::table('inscripciones as i')
                ->join('historial_inscripciones as h', 'h.inscripcion_id', '=', 'i.id')
                ->joinSub($ultimos, 'ult', 'ult.id', '=', 'h.id');
        }

        $c = fn (string $column) => ($historico ? 'h.' : 'i.') . $column;
        $q->leftJoin('licenciaturas as l', 'l.id', '=', $c('licenciatura_id'))
            ->leftJoin('generaciones as g', 'g.id', '=', $c('generacion_id'))
            ->leftJoin('cuatrimestres as ct', 'ct.id', '=', $c('cuatrimestre_id'))
            ->leftJoin('modalidades as m', 'm.id', '=', $c('modalidad_id'));

        $q->select([
            'i.id', 'i.nombre', 'i.apellido_paterno', 'i.apellido_materno', 'i.CURP',
            'i.fecha_nacimiento', 'i.telefono', 'i.celular', 'i.municipio', 'i.colonia',
            'i.tutor', 'i.foraneo',
            DB::raw($c('matricula') . ' AS matricula'),
            DB::raw($c('sexo') . ' AS sexo'),
            DB::raw($c('licenciatura_id') . ' AS licenciatura_id'),
            DB::raw($c('generacion_id') . ' AS generacion_id'),
            DB::raw($c('cuatrimestre_id') . ' AS cuatrimestre_id'),
            DB::raw($c('modalidad_id') . ' AS modalidad_id'),
            DB::raw($c('status') . ' AS status'),
            DB::raw($c('egresado') . ' AS egresado'),
            DB::raw(($historico ? 'h.matricula_interna' : 'i.matricula_interna') . ' AS matricula_interna'),
            'l.nombre as licenciatura_nombre', 'g.generacion as generacion_nombre',
            'ct.nombre_cuatrimestre as cuatrimestre_nombre', 'm.nombre as modalidad_nombre',
        ]);

        foreach (['licenciatura_ids' => 'licenciatura_id', 'generacion_ids' => 'generacion_id', 'cuatrimestre_ids' => 'cuatrimestre_id'] as $filtro => $campo) {
            $ids = array_values(array_filter(array_map('intval', (array) ($filtros[$filtro] ?? []))));
            if ($ids) $q->whereIn($c($campo), $ids);
        }
        if (! empty($filtros['modalidad_id'])) $q->where($c('modalidad_id'), $filtros['modalidad_id']);
        if (in_array($filtros['procedencia'] ?? 'todos', ['true', 'false'], true)) {
            // El historial no almacena procedencia; se usa la actual (indicado en UI).
            $q->where('i.foraneo', $filtros['procedencia']);
        }
        $estado = $filtros['estado'] ?? 'activos';
        if ($estado === 'activos') $q->where($c('status'), 'true')->where($c('egresado'), 'false');
        if ($estado === 'egresados') $q->where($c('egresado'), 'true');
        if ($estado === 'bajas') $q->where($c('status'), 'false')->where($c('egresado'), 'false');

        $busqueda = trim((string) ($filtros['busqueda'] ?? ''));
        if ($busqueda !== '') {
            $q->where(function (Builder $qb) use ($busqueda, $c) {
                $like = '%' . $busqueda . '%';
                $qb->where('i.nombre', 'like', $like)
                    ->orWhere('i.apellido_paterno', 'like', $like)
                    ->orWhere('i.apellido_materno', 'like', $like)
                    ->orWhere('i.CURP', 'like', $like)
                    ->orWhere($c('matricula'), 'like', $like)
                    ->orWhere($c('matricula_interna'), 'like', $like)
                    ->orWhereRaw("CONCAT_WS(' ', i.apellido_paterno, i.apellido_materno, i.nombre) LIKE ?", [$like]);
            });
        }
        $seleccionados = array_values(array_filter(array_map('intval', (array) ($filtros['alumno_ids'] ?? []))));
        if (! ($filtros['todos_filtrados'] ?? true)) {
            $q->whereIn('i.id', $seleccionados ?: [0]);
        }

        $orden = $filtros['orden'] ?? 'apellidos';
        if ($orden === 'matricula') $q->orderBy($c('matricula'));
        elseif ($orden === 'nombre') $q->orderBy('i.nombre')->orderBy('i.apellido_paterno');
        elseif ($orden === 'inscripcion') $q->orderBy('i.created_at');
        else $q->orderBy('i.apellido_paterno')->orderBy('i.apellido_materno')->orderBy('i.nombre');
        return $q->orderBy('i.id');
    }

    public function grupos(array $filtros): array
    {
        $alumnos = $this->consulta($filtros)->get();
        $por = $filtros['agrupar'] ?? 'licenciatura_generacion';
        $nombres = [
            'licenciatura' => ['licenciatura_id', 'licenciatura_nombre'],
            'generacion' => ['generacion_id', 'generacion_nombre'],
            'cuatrimestre' => ['cuatrimestre_id', 'cuatrimestre_nombre'],
            'modalidad' => ['modalidad_id', 'modalidad_nombre'],
        ];
        $grupos = [];
        foreach ($alumnos as $alumno) {
            $partes = $por === 'licenciatura_generacion' ? ['licenciatura', 'generacion'] : ($por === 'sin_agrupar' ? [] : [$por]);
            $clave = $partes ? implode('|', array_map(fn ($p) => (string) ($alumno->{$nombres[$p][0]} ?? ''), $partes)) : 'todos';
            if (! isset($grupos[$clave])) {
                $titulo = $partes ? implode(' · ', array_map(fn ($p) => (string) ($alumno->{$nombres[$p][1]} ?? 'Sin asignación'), $partes)) : 'Lista general';
                $grupos[$clave] = ['titulo' => $titulo, 'licenciatura_id' => $alumno->licenciatura_id, 'licenciatura_nombre' => $alumno->licenciatura_nombre, 'generacion_nombre' => $alumno->generacion_nombre, 'alumnos' => []];
            }
            $grupos[$clave]['alumnos'][] = $alumno;
        }
        uasort($grupos, fn ($a, $b) => strnatcasecmp($a['titulo'], $b['titulo']));
        return array_values($grupos);
    }

    public function tabla(array $grupo, array $filtros): array
    {
        $columnas = $filtros['columnas'] ?? self::DEFAULT_COLUMNAS;
        $filas = [];
        foreach ($grupo['alumnos'] as $indice => $alumno) {
            $fila = [];
            foreach ($columnas as $campo) $fila[] = $this->valor($alumno, $campo, $indice + 1, $filtros);
            $filas[] = $fila;
        }
        return $filas;
    }

    public function cabeceras(array $filtros): array
    {
        return array_map(fn ($c) => trim((string) ($filtros['cabeceras'][$c] ?? '')) ?: self::CAMPOS[$c], $filtros['columnas']);
    }

    private function valor(object $a, string $campo, int $indice, array $f): string
    {
        $valor = match ($campo) {
            'numero' => (string) $indice,
            'matricula' => (string) ($a->matricula ?: $a->matricula_interna),
            'licenciatura' => (string) $a->licenciatura_nombre,
            'generacion' => (string) $a->generacion_nombre,
            'cuatrimestre' => (string) $a->cuatrimestre_nombre,
            'modalidad' => match ($f['modalidad_texto'] ?? 'real') {
                'escolarizada' => 'ESCOLARIZADA',
                'semiescolarizada' => 'SEMIESCOLARIZADA',
                default => (string) $a->modalidad_nombre,
            },
            'nombre_completo' => trim($a->apellido_paterno . ' ' . $a->apellido_materno . ' ' . $a->nombre),
            'procedencia' => $a->foraneo === 'true' ? 'FORÁNEO' : 'LOCAL',
            'estatus' => $a->egresado === 'true' ? 'EGRESADO' : ($a->status === 'true' ? 'ACTIVO' : 'BAJA'),
            'observaciones' => (string) (($f['observaciones'] ?? [])[$a->id] ?? ($f['observacion_general'] ?? '')),
            'edad' => $a->fecha_nacimiento ? (string) Carbon::parse($a->fecha_nacimiento)->age : '',
            'fecha_nacimiento' => $a->fecha_nacimiento ? Carbon::parse($a->fecha_nacimiento)->format('d/m/Y') : '',
            'sexo' => $a->sexo === 'H' ? 'HOMBRE' : ($a->sexo === 'M' ? 'MUJER' : ''),
            default => (string) ($a->{$campo} ?? ''),
        };
        return Str::upper(trim($valor));
    }

    public function encabezadoInstitucional(): array
    {
        $escuela = Escuela::query()->first();
        return ['escuela' => $escuela, 'logo' => $this->archivo('letra2.jpg'), 'agua' => $this->archivo('letra.png')];
    }

    public function logoLicenciatura(?int $id): ?string
    {
        $imagen = DB::table('licenciaturas')->where('id', $id)->value('imagen');
        if (! $imagen) return null;
        $nombre = basename(str_replace('\\', '/', $imagen));
        return $this->archivo('licenciaturas/' . $nombre);
    }

    private function archivo(string $ruta): ?string
    {
        foreach ([public_path('storage/' . $ruta), storage_path('app/public/' . $ruta)] as $archivo) {
            if (is_file($archivo)) return $archivo;
        }
        return null;
    }
}
