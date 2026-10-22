<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('movimientos_academicos')) {
            throw new \RuntimeException('Primero ejecuta las migraciones de movimientos académicos de septiembre de 2026.');
        }

        // Columnas independientes: generacion_id se conserva por compatibilidad histórica.
        if (! Schema::hasColumn('movimientos_academicos', 'generacion_origen_id')) {
            Schema::table('movimientos_academicos', function (Blueprint $table): void {
                $table->foreignId('generacion_origen_id')->nullable()->constrained('generaciones')->nullOnDelete();
            });
        }
        if (! Schema::hasColumn('movimientos_academicos', 'generacion_destino_id')) {
            Schema::table('movimientos_academicos', function (Blueprint $table): void {
                $table->foreignId('generacion_destino_id')->nullable()->constrained('generaciones')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('movimientos_academicos')) {
            return;
        }
        if (Schema::hasColumn('movimientos_academicos', 'generacion_destino_id')) {
            Schema::table('movimientos_academicos', fn (Blueprint $table) => $table->dropConstrainedForeignId('generacion_destino_id'));
        }
        if (Schema::hasColumn('movimientos_academicos', 'generacion_origen_id')) {
            Schema::table('movimientos_academicos', fn (Blueprint $table) => $table->dropConstrainedForeignId('generacion_origen_id'));
        }
    }
};
