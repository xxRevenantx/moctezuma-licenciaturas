<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matricula_bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->nullable()->constrained('inscripciones')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 50);
            $table->string('valor_anterior', 20)->nullable();
            $table->string('valor_nuevo', 20)->nullable();
            $table->json('detalles')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['inscripcion_id', 'created_at']);
            $table->index(['accion', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matricula_bitacoras');
    }
};
