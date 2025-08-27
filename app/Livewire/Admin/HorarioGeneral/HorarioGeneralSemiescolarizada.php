<?php

namespace App\Livewire\Admin\HorarioGeneral;

use App\Models\Horario;
use Livewire\Component;

class HorarioGeneralSemiescolarizada extends Component
{

    public $horarioSemiEscolarizada;

    public $busqueda = '';



public function render()
{
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
                $q2->where('nombre', 'like', '%' . $this->busqueda . '%');
                $q2->orWhere('apellido_paterno', 'like', '%' . $this->busqueda . '%');
                $q2->orWhere('apellido_materno', 'like', '%' . $this->busqueda . '%');
            });
        });
    })
    ->get();

    $columnasUnicas = $horarios
        ->unique(fn ($item) => $item->cuatrimestre_id . '-' . $item->licenciatura_id)
        ->map(fn ($item) => [
            'cuatrimestre_id' => $item->cuatrimestre_id,
            'licenciatura_id' => $item->licenciatura_id,
            'generacion_id' => $item->generacion_id,
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



    return view('livewire.admin.horario-general.horario-general-semiescolarizada', [
        'horarios' => $horarios,
        'columnasUnicas' => $columnasUnicas,
        'horasUnicas' => $horasUnicas,
        'horasPorDocente' => $horasPorDocente,
        'totalHoras' => $totalHoras,
    ]);
}


}
