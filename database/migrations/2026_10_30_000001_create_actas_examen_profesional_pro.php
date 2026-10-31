<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('actas_examen_profesional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->nullable()->constrained('inscripciones')->nullOnDelete();
            $table->date('fecha_examen');
            $table->time('hora_examen');
            $table->string('autorizacion_numero', 80)->nullable();
            $table->string('entidad_federativa', 100)->default('Guerrero');
            $table->string('titulo_profesional', 255);
            $table->string('acuerdo_numero', 90)->nullable();
            $table->date('acuerdo_fecha')->nullable();
            $table->json('sinodo');
            $table->string('resultado', 100)->nullable();
            $table->string('rector', 255)->nullable();
            $table->string('director', 255)->nullable();
            $table->string('revisado_por', 255)->nullable();
            $table->string('jefe_registro', 255)->nullable();
            $table->date('fecha_revision')->nullable();
            $table->boolean('incluir_foto')->default(false);
            $table->string('foto_snapshot_path', 255)->nullable();
            $table->boolean('excepcion_egreso')->default(false);
            $table->text('motivo_excepcion')->nullable();
            $table->foreignId('excepcion_autorizada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->json('datos_alumno'); // Identidad y contexto académico congelados en cada acta
            $table->string('estado', 20)->default('borrador'); // borrador, revision, autorizada, cancelada
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('autorizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('autorizado_en')->nullable();
            $table->timestamps();
            $table->index(['alumno_id', 'fecha_examen']);
            $table->index(['estado', 'fecha_examen']);
        });

        Schema::create('actas_examen_profesional_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('acta_id')->constrained('actas_examen_profesional')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 45);
            $table->json('detalles')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('actas_examen_sinodos_plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->json('integrantes');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas_examen_sinodos_plantillas');
        Schema::dropIfExists('actas_examen_profesional_historial');
        Schema::dropIfExists('actas_examen_profesional');
    }
};
