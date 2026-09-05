<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permite que instalaciones que hayan aplicado primero el parche SQL
        // puedan ejecutar después `php artisan migrate` sin colisionar.
        if (Schema::hasTable('movimientos_academicos')) {
            return;
        }

        Schema::create('movimientos_academicos', function (Blueprint $table) {
            $table->id();
            $table->string('lote', 40)->index();
            $table->string('tipo', 40);
            $table->foreignId('inscripcion_id')->nullable()->constrained('inscripciones')->nullOnDelete();
            $table->string('matricula_snapshot', 20)->nullable();
            $table->string('alumno_snapshot', 180)->nullable();
            $table->foreignId('licenciatura_id')->nullable()->constrained('licenciaturas')->nullOnDelete();
            $table->foreignId('generacion_id')->nullable()->constrained('generaciones')->nullOnDelete();
            $table->foreignId('modalidad_origen_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('modalidad_destino_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('cuatrimestre_origen_id')->nullable()->constrained('cuatrimestres')->nullOnDelete();
            $table->foreignId('cuatrimestre_destino_id')->nullable()->constrained('cuatrimestres')->nullOnDelete();
            $table->string('motivo', 60);
            $table->text('observaciones')->nullable();
            $table->unsignedInteger('calificaciones_trasladadas')->default(0);
            $table->string('estado', 20)->default('aplicado');
            $table->foreignId('ejecutado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revertido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revertido_at')->nullable();
            $table->json('detalles')->nullable();
            $table->timestamps();

            $table->index(['inscripcion_id', 'created_at'], 'mov_acad_inscripcion_fecha_idx');
            $table->index(['licenciatura_id', 'generacion_id', 'tipo'], 'mov_acad_contexto_tipo_idx');
            $table->index(['lote', 'estado'], 'mov_acad_lote_estado_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_academicos');
    }
};
