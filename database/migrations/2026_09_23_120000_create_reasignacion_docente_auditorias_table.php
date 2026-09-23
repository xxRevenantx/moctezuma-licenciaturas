<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reasignacion_docente_auditorias')) return;
        Schema::create('reasignacion_docente_auditorias', function (Blueprint $table) {
            $table->id();
            $table->uuid('operacion')->unique();
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario');
            $table->unsignedBigInteger('profesor_anterior_id');
            $table->unsignedBigInteger('profesor_nuevo_id');
            $table->string('profesor_anterior');
            $table->string('profesor_nuevo');
            $table->unsignedInteger('total');
            $table->json('detalle');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reasignacion_docente_auditorias');
    }
};
