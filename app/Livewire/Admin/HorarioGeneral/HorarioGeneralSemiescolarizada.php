<?php

namespace App\Livewire\Admin\HorarioGeneral;

use App\Models\Horario;
use App\Models\Materia;
use App\Models\AsignacionMateria;
use Livewire\Component;

class HorarioGeneralSemiescolarizada extends Component
{
    public $horarioSemiEscolarizada;
    public $busqueda = '';

    /**
     * Opciones del select por columna: "licenciatura_id|cuatrimestre_id" => [ {id,nombre,clave}, ... ]
     * Solo materias con AsignacionMateria en modalidad = 2.
     *
     * @var array<string, array<int, array{id:int,nombre:string,clave:?string}>>
     */
    public $materiasPorColumna = [];

    /**
     * Cambia la materia y también actualiza el profesor,
     * enlazando la AsignacionMateria de modalidad = 2 que corresponda.
     * Identificador del slot: "$hora|$licenciatura_id|$cuatrimestre_id".
     */
    public function cambiarMateriaProfesor(string $slotKey, $materiaId): void
    {
        $materiaId = (int) $materiaId;
        if ($materiaId <= 0) return;

        [$hora, $licId, $cuatId] = explode('|', $slotKey);
        $licId  = (int) $licId;
        $cuatId = (int) $cuatId;

        // 1) Validar materia para esa lic + cuat y que tenga asignación en modalidad 2
        $materia = Materia::query()
            ->where('id', $materiaId)
            ->where('licenciatura_id', $licId)
            ->where('cuatrimestre_id', $cuatId)
            ->first();

        if (!$materia) {
            $this->dispatch('toast', type: 'error', message: 'La materia no corresponde a la licenciatura/cuatrimestre.');
            return;
        }

        // 2) Buscar Asignación en modalidad 2
        $asignacion = AsignacionMateria::query()
            ->with(['profesor', 'materia'])
            ->where('materia_id', $materiaId)
            ->where('modalidad_id', 2)
            ->orderBy('id')
            ->first();

        if (!$asignacion) {
            $this->dispatch('toast', type: 'error', message: 'No hay asignación (materia-profesor) para Semiescolarizada.');
            return;
        }

        // 3) Obtener o crear el Horario (modalidad 2) y enlazar la asignación
        $horario = Horario::query()
            ->where('modalidad_id', 2)
            ->where('licenciatura_id', $licId)
            ->where('cuatrimestre_id', $cuatId)
            ->where('hora', $hora)
            ->first();

        if (!$horario) {
            // Si tu tabla requiere 'dia_id', añade un valor válido aquí.
            $horario = Horario::create([
                'modalidad_id'          => 2,
                'licenciatura_id'       => $licId,
                'cuatrimestre_id'       => $cuatId,
                'hora'                  => $hora,
                'asignacion_materia_id' => $asignacion->id,
            ]);
        } else {
            $horario->update([
                'asignacion_materia_id' => $asignacion->id,
            ]);
        }

        $this->dispatch('toast', type: 'success', message: 'Materia y profesor actualizados para modalidad 2.');
    }

    public function render()
    {
        // ====== TODO filtrado a modalidad 2 ======
        $horarios = Horario::with([
                'asignacionMateria.materia',
                'asignacionMateria.profesor',
                'licenciatura',
                'asignacionMateria.modalidad'
            ])
            ->where('modalidad_id', 2)
            ->when($this->busqueda, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('asignacionMateria.materia', function ($q2) {
                        $q2->where('nombre', 'like', '%' . $this->busqueda . '%');
                    })
                    ->orWhereHas('asignacionMateria.profesor', function ($q2) {
                        $q2->where('nombre', 'like', '%' . $this->busqueda . '%')
                           ->orWhere('apellido_paterno', 'like', '%' . $this->busqueda . '%')
                           ->orWhere('apellido_materno', 'like', '%' . $this->busqueda . '%');
                    });
                });
            })
            ->get();

        $columnasUnicas = $horarios
            ->unique(fn ($item) => $item->cuatrimestre_id . '-' . $item->licenciatura_id)
            ->map(fn ($item) => [
                'cuatrimestre_id' => $item->cuatrimestre_id,
                'licenciatura_id' => $item->licenciatura_id,
                'etiqueta' => "Cuat. {$item->cuatrimestre_id} - Lic. {$item->licenciatura->nombre}"
            ])
            ->sortBy(fn ($col) => sprintf('%03d-%03d', $col['licenciatura_id'], $col['cuatrimestre_id']))
            ->values();

        $horasUnicas = $horarios->pluck('hora')
            ->unique()
            ->sortBy(function ($hora) {
                $inicio = explode('-', $hora)[0];
                return \Carbon\Carbon::parse(trim($inicio))->format('H:i');
            })
            ->values();

        $horasPorDocente = $horarios
            ->groupBy(fn ($h) => optional($h->asignacionMateria->profesor)->id)
            ->map(function ($items) {
                $profesor = optional($items->first()->asignacionMateria->profesor);
                return [
                    'nombre' => $profesor?->nombre ?? 'Sin asignar',
                    'apellido_paterno' => $profesor?->apellido_paterno ?? '',
                    'apellido_materno' => $profesor?->apellido_materno ?? '',
                    'color' => $profesor?->color ?? '#CCCCCC',
                    'total_horas' => $items->count(),
                ];
            })
            ->sortBy('apellido_paterno')
            ->values();

        $totalHoras = $horasPorDocente->sum('total_horas');

        // ===== Opciones del select por columna, SOLO modalidad 2 =====
        // Traemos Asignaciones (mod=2) con su Materia para construir opciones por lic+cuat.
        $licCuatCombos = $columnasUnicas->map(fn($c) => [
            'licenciatura_id' => (int) $c['licenciatura_id'],
            'cuatrimestre_id' => (int) $c['cuatrimestre_id'],
        ]);

        $licIds  = $licCuatCombos->pluck('licenciatura_id')->unique()->values();
        $cuatIds = $licCuatCombos->pluck('cuatrimestre_id')->unique()->values();

        $asignacionesMod2 = AsignacionMateria::query()
            ->with(['materia:id,nombre,clave,licenciatura_id,cuatrimestre_id'])
            ->where('modalidad_id', 2)
            ->whereHas('materia', function ($q) use ($licIds, $cuatIds) {
                $q->whereIn('licenciatura_id', $licIds)
                  ->whereIn('cuatrimestre_id', $cuatIds);
            })
            ->get();

        // Agrupar por lic|cuat y dejar una única opción por materia (puede haber varias asignaciones por la misma materia)
        $materiasPorColumna = [];
        foreach ($licCuatCombos as $c) {
            $k = $c['licenciatura_id'].'|'.$c['cuatrimestre_id'];

            $materias = $asignacionesMod2
                ->filter(function ($a) use ($c) {
                    return $a->materia
                        && (int)$a->materia->licenciatura_id === $c['licenciatura_id']
                        && (int)$a->materia->cuatrimestre_id === $c['cuatrimestre_id'];
                })
                ->pluck('materia')              // Collection<Materia>
                ->unique('id')                  // una sola por materia
                ->sortBy('nombre', SORT_NATURAL|SORT_FLAG_CASE)
                ->map(fn($m) => [
                    'id'     => $m->id,
                    'nombre' => $m->nombre,
                    'clave'  => $m->clave,
                ])
                ->values()
                ->toArray();

            $materiasPorColumna[$k] = $materias;
        }
        $this->materiasPorColumna = $materiasPorColumna;
        // ===========================================

        return view('livewire.admin.horario-general.horario-general-semiescolarizada', [
            'horarios'           => $horarios,
            'columnasUnicas'     => $columnasUnicas,
            'horasUnicas'        => $horasUnicas,
            'horasPorDocente'    => $horasPorDocente,
            'totalHoras'         => $totalHoras,
            'materiasPorColumna' => $this->materiasPorColumna,
        ]);
    }
}
