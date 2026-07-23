<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('descargas_expedientes_identidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 25);
            $table->string('formato', 10)->default('zip');
            $table->string('estado', 25)->default('pendiente');
            $table->json('filtros')->nullable();
            $table->unsignedInteger('total_alumnos')->default(0);
            $table->unsignedInteger('alumnos_procesados')->default(0);
            $table->unsignedInteger('alumnos_incompletos')->default(0);
            $table->unsignedInteger('documentos_faltantes')->default(0);
            $table->string('archivo_ruta', 500)->nullable();
            $table->string('archivo_nombre', 255)->nullable();
            $table->unsignedBigInteger('archivo_tamano')->nullable();
            $table->text('error')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('solicitado_at')->nullable();
            $table->timestamp('iniciado_at')->nullable();
            $table->timestamp('completado_at')->nullable();
            $table->timestamp('descargado_at')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'estado', 'created_at'], 'descarga_exp_usuario_estado_idx');
            $table->index(['tipo', 'formato', 'created_at'], 'descarga_exp_tipo_formato_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('descargas_expedientes_identidad');
    }
};
