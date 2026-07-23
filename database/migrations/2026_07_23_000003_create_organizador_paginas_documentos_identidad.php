<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_identidad_fuentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->foreignId('documento_identidad_id')->nullable()->constrained('documentos_identidad')->nullOnDelete();
            $table->string('ruta', 500);
            $table->string('ruta_original', 500)->nullable();
            $table->string('nombre_original', 255);
            $table->string('nombre_almacenado', 255);
            $table->string('mime_type', 100)->default('application/pdf');
            $table->string('mime_original', 100)->default('application/pdf');
            $table->unsignedBigInteger('tamano')->default(0);
            $table->char('hash_sha256', 64);
            $table->unsignedInteger('paginas')->default(1);
            $table->enum('estado', ['activo', 'eliminado', 'inconsistente'])->default('activo');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->index(['inscripcion_id', 'estado'], 'doc_identidad_fuentes_alumno_estado');
            $table->index('hash_sha256');
            $table->unique('documento_identidad_id', 'doc_identidad_fuente_documento_unique');
        });

        Schema::create('organizaciones_documentos_identidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->enum('estado', ['borrador', 'confirmado', 'anulado'])->default('borrador');
            $table->json('asignaciones');
            $table->json('fuentes_ids')->nullable();
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmado_at')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->unique(['inscripcion_id', 'version'], 'organizacion_identidad_version_unique');
            $table->index(['inscripcion_id', 'estado'], 'organizacion_identidad_alumno_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizaciones_documentos_identidad');
        Schema::dropIfExists('documentos_identidad_fuentes');
    }
};
