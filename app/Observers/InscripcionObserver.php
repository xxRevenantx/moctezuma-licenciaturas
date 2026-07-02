<?php

namespace App\Observers;

use App\Models\HistorialInscripcion;
use App\Models\Inscripcion;
use App\Models\Periodo;
use Illuminate\Support\Facades\Schema;

class InscripcionObserver
{
    public function creating(Inscripcion $inscripcion): void
    {
        $inscripcion->orden = (Inscripcion::max('orden') ?? 0) + 1;
    }

    public function created(Inscripcion $inscripcion): void
    {
        $this->registrarMovimiento($inscripcion, 'alta');
    }

    public function updated(Inscripcion $inscripcion): void
    {
        $camposControlados = [
            'licenciatura_id',
            'generacion_id',
            'cuatrimestre_id',
            'modalidad_id',
            'sexo',
            'status',
            'fecha_baja',
            'egresado',
        ];

        if ($inscripcion->wasChanged($camposControlados)) {
            $this->registrarMovimiento($inscripcion, 'actualizacion');
        }
    }

    public function deleting(Inscripcion $inscripcion): void
    {
        $this->registrarMovimiento($inscripcion, 'eliminacion');
    }

    public function deleted(Inscripcion $inscripcion): void
    {
        Inscripcion::where('orden', '>', $inscripcion->orden)->decrement('orden');
    }

    private function registrarMovimiento(Inscripcion $inscripcion, string $tipo): void
    {
        if (! Schema::hasTable('historial_inscripciones')) {
            return;
        }

        $cicloEscolar = Periodo::query()
            ->where('generacion_id', $inscripcion->generacion_id)
            ->where('cuatrimestre_id', $inscripcion->cuatrimestre_id)
            ->value('ciclo_escolar');

        HistorialInscripcion::create([
            'inscripcion_id' => $inscripcion->id,
            'user_id' => $inscripcion->user_id,
            'matricula' => $inscripcion->matricula,
            'sexo' => $inscripcion->sexo,
            'licenciatura_id' => $inscripcion->licenciatura_id,
            'generacion_id' => $inscripcion->generacion_id,
            'cuatrimestre_id' => $inscripcion->cuatrimestre_id,
            'modalidad_id' => $inscripcion->modalidad_id,
            'ciclo_escolar' => $cicloEscolar,
            'status' => $inscripcion->status,
            'egresado' => $inscripcion->egresado,
            'fecha_baja' => $inscripcion->fecha_baja,
            'tipo_movimiento' => $tipo,
            'fecha_movimiento' => now(),
        ]);
    }
}
