<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReconstruirHistorialInscripciones extends Command
{
    protected $signature = 'estadisticas:reconstruir-historial
                            {--aplicar : Guarda en la base de datos los movimientos detectados}';

    protected $description = 'Previsualiza o reconstruye el historial académico a partir de las calificaciones existentes';

    public function handle(): int
    {
        if (! Schema::hasTable('historial_inscripciones')) {
            $this->error('Primero ejecuta las migraciones para crear historial_inscripciones.');

            return self::FAILURE;
        }

        $registros = DB::table('calificaciones as cal')
            ->join('inscripciones as i', 'i.id', '=', 'cal.alumno_id')
            ->leftJoin('periodos as p', function ($join) {
                $join->on('p.generacion_id', '=', 'cal.generacion_id')
                    ->on('p.cuatrimestre_id', '=', 'cal.cuatrimestre_id');
            })
            ->select([
                'i.id as inscripcion_id',
                'i.user_id',
                'i.matricula',
                'i.sexo',
                'cal.licenciatura_id',
                'cal.generacion_id',
                'cal.cuatrimestre_id',
                'cal.modalidad_id',
                DB::raw('MAX(p.ciclo_escolar) AS ciclo_escolar'),
                DB::raw('MIN(cal.created_at) AS primera_evidencia'),
                'i.licenciatura_id as licenciatura_actual',
                'i.generacion_id as generacion_actual',
                'i.cuatrimestre_id as cuatrimestre_actual',
                'i.modalidad_id as modalidad_actual',
                'i.status as status_actual',
                'i.egresado as egresado_actual',
                'i.fecha_baja as fecha_baja_actual',
            ])
            ->groupBy([
                'i.id',
                'i.user_id',
                'i.matricula',
                'i.sexo',
                'cal.licenciatura_id',
                'cal.generacion_id',
                'cal.cuatrimestre_id',
                'cal.modalidad_id',
                'i.licenciatura_id',
                'i.generacion_id',
                'i.cuatrimestre_id',
                'i.modalidad_id',
                'i.status',
                'i.egresado',
                'i.fecha_baja',
            ])
            ->orderBy('i.id')
            ->orderBy('cal.cuatrimestre_id')
            ->get();

        $nuevos = $registros->filter(function ($registro): bool {
            return ! DB::table('historial_inscripciones')
                ->where('inscripcion_id', $registro->inscripcion_id)
                ->where('licenciatura_id', $registro->licenciatura_id)
                ->where('generacion_id', $registro->generacion_id)
                ->where('cuatrimestre_id', $registro->cuatrimestre_id)
                ->where('modalidad_id', $registro->modalidad_id)
                ->exists();
        });

        $this->info('Combinaciones académicas detectadas: ' . $registros->count());
        $this->info('Movimientos nuevos por reconstruir: ' . $nuevos->count());

        $resumen = $nuevos
            ->groupBy(fn ($fila) => $fila->ciclo_escolar ?: 'SIN CICLO')
            ->map(fn ($filas, $ciclo) => [$ciclo, $filas->count()])
            ->values()
            ->all();

        if ($resumen !== []) {
            $this->table(['Ciclo escolar', 'Movimientos detectados'], $resumen);
        }

        if (! $this->option('aplicar')) {
            $this->warn('Vista previa únicamente. Para guardar, ejecuta el mismo comando con --aplicar.');

            return self::SUCCESS;
        }

        $barra = $this->output->createProgressBar($nuevos->count());
        $barra->start();

        foreach ($nuevos as $registro) {
            $esEstadoActual = (int) $registro->licenciatura_id === (int) $registro->licenciatura_actual
                && (int) $registro->generacion_id === (int) $registro->generacion_actual
                && (int) $registro->cuatrimestre_id === (int) $registro->cuatrimestre_actual
                && (int) $registro->modalidad_id === (int) $registro->modalidad_actual;

            DB::table('historial_inscripciones')->insert([
                'inscripcion_id' => $registro->inscripcion_id,
                'user_id' => $registro->user_id,
                'matricula' => $registro->matricula,
                'sexo' => $registro->sexo,
                'licenciatura_id' => $registro->licenciatura_id,
                'generacion_id' => $registro->generacion_id,
                'cuatrimestre_id' => $registro->cuatrimestre_id,
                'modalidad_id' => $registro->modalidad_id,
                'ciclo_escolar' => $registro->ciclo_escolar,
                'status' => $esEstadoActual ? $registro->status_actual : 'true',
                'egresado' => $esEstadoActual ? $registro->egresado_actual : 'false',
                'fecha_baja' => $esEstadoActual ? $registro->fecha_baja_actual : null,
                'tipo_movimiento' => 'reconstruccion',
                'fecha_movimiento' => $registro->primera_evidencia ?: now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $barra->advance();
        }

        $barra->finish();
        $this->newLine(2);
        $this->info('Historial reconstruido correctamente.');

        return self::SUCCESS;
    }
}
