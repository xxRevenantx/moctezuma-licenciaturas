<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->nullable()->constrained('inscripciones')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('matricula', 20)->nullable();
            $table->enum('sexo', ['H', 'M'])->nullable();
            $table->foreignId('licenciatura_id')->nullable()->constrained('licenciaturas')->nullOnDelete();
            $table->foreignId('generacion_id')->nullable()->constrained('generaciones')->nullOnDelete();
            $table->foreignId('cuatrimestre_id')->nullable()->constrained('cuatrimestres')->nullOnDelete();
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->string('ciclo_escolar')->nullable();
            $table->enum('status', ['true', 'false'])->default('true');
            $table->enum('egresado', ['true', 'false'])->default('false');
            $table->dateTime('fecha_baja')->nullable();
            $table->string('tipo_movimiento', 40)->default('actualizacion');
            $table->dateTime('fecha_movimiento');
            $table->timestamps();

            $table->index(['ciclo_escolar', 'licenciatura_id'], 'historial_ciclo_licenciatura_idx');
            $table->index(['generacion_id', 'cuatrimestre_id'], 'historial_generacion_cuatrimestre_idx');
            $table->index(['status', 'egresado'], 'historial_estado_idx');
        });

        $this->crearFotografiaInicial();
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_inscripciones');
    }

    private function crearFotografiaInicial(): void
    {
        $ciclos = DB::table('periodos')
            ->select('generacion_id', 'cuatrimestre_id', DB::raw('MAX(ciclo_escolar) AS ciclo_escolar'))
            ->groupBy('generacion_id', 'cuatrimestre_id')
            ->get()
            ->keyBy(fn ($periodo) => $periodo->generacion_id . '-' . $periodo->cuatrimestre_id);

        DB::table('inscripciones')
            ->orderBy('id')
            ->chunkById(300, function ($inscripciones) use ($ciclos): void {
                $ahora = now();
                $filas = [];

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
                        'status' => $inscripcion->status,
                        'egresado' => $inscripcion->egresado,
                        'fecha_baja' => $inscripcion->fecha_baja,
                        'tipo_movimiento' => 'fotografia_inicial',
                        'fecha_movimiento' => $ahora,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                if ($filas !== []) {
                    DB::table('historial_inscripciones')->insert($filas);
                }
            });
    }
};
