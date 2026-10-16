<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('generador_listas_plantillas')) Schema::create('generador_listas_plantillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->json('configuracion');
            $table->timestamps();
            $table->unique(['user_id', 'nombre']);
        });
        if (! Schema::hasTable('generador_listas_descargas')) Schema::create('generador_listas_descargas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('formato', 20);
            $table->unsignedInteger('total_alumnos');
            $table->unsignedInteger('total_listas');
            $table->json('filtros');
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generador_listas_descargas');
        Schema::dropIfExists('generador_listas_plantillas');
    }
};
