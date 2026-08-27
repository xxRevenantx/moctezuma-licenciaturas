<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inscripciones') || ! Schema::hasTable('historial_inscripciones')) {
            return;
        }

        $ciclos = DB::table('periodos')
            ->select('generacion_id', 'cuatrimestre_id', DB::raw('MAX(ciclo_escolar) AS ciclo_escolar'))
            ->groupBy('generacion_id', 'cuatrimestre_id')
            ->get()
            ->keyBy(fn ($periodo) => $periodo->generacion_id . '-' . $periodo->cuatrimestre_id);

        DB::table('inscripciones as i')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('historial_inscripciones as h')
                    ->whereColumn('h.inscripcion_id', 'i.id');
            })
            ->select([
                'i.id',
                'i.user_id',
                'i.matricula',
                'i.sexo',
                'i.licenciatura_id',
                'i.generacion_id',
                'i.cuatrimestre_id',
                'i.modalidad_id',
                'i.status',
                'i.egresado',
                'i.fecha_baja',
                'i.created_at',
            ])
            ->orderBy('i.id')
            ->chunk(300, function ($inscripciones) use ($ciclos): void {
                $filas = [];
                $ahora = now();

                foreach ($inscripciones as $inscripcion) {
                    $periodo = $ciclos->get($inscripcion->generacion_id . '-' . $inscripcion->cuatrimestre_id);

                    $filas[] = [
                        'inscripcion_id' => $inscripcion->id,
                        'user_id' => $inscripcion->user_id,
                        'matricula' => $inscripcion->matricula,
                        'sexo' => $inscripcion->sexo,
                        'licenciatura_id' => $inscripcion->licenciatura_id,
                        'generacion_id' => $inscripcion->generacion_id,
                        'cuatrimestre_id' => $inscripcion->cuatrimestre_id,
                        'modalidad_id' => $inscripcion->modalidad_id,
                        'ciclo_escolar' => $periodo?->ciclo_escolar,
                        'status' => $inscripcion->status ?? 'true',
                        'egresado' => $inscripcion->egresado ?? 'false',
                        'fecha_baja' => $inscripcion->fecha_baja,
                        'tipo_movimiento' => 'reparacion_historial',
                        'fecha_movimiento' => $inscripcion->created_at ?? $ahora,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                if ($filas !== []) {
                    DB::table('historial_inscripciones')->insert($filas);
                }
            });
    }

    public function down(): void
    {
        if (! Schema::hasTable('historial_inscripciones')) {
            return;
        }

        DB::table('historial_inscripciones')
            ->where('tipo_movimiento', 'reparacion_historial')
            ->delete();
    }
};
