<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_identidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->string('tipo', 60);
            $table->string('ruta', 500);
            $table->string('nombre_original', 255);
            $table->string('nombre_almacenado', 255);
            $table->string('mime_type', 100)->default('application/pdf');
            $table->unsignedBigInteger('tamano')->default(0);
            $table->char('hash_sha256', 64);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('es_actual')->default(true);
            $table->enum('estado', ['activo', 'reemplazado', 'eliminado', 'inconsistente'])->default('activo');
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_eliminacion')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->unique(['inscripcion_id', 'tipo', 'version'], 'doc_identidad_version_unique');
            $table->index(['inscripcion_id', 'tipo', 'es_actual', 'estado'], 'doc_identidad_actual_index');
            $table->index('hash_sha256');
        });

        if (Schema::hasTable('permissions') && Schema::hasTable('roles') && Schema::hasTable('role_has_permissions')) {
            $permissions = [
                'documentos-identidad.ver',
                'documentos-identidad.subir',
                'documentos-identidad.reemplazar',
                'documentos-identidad.eliminar',
                'documentos-identidad.descargar',
                'documentos-identidad.auditar',
            ];

            foreach ($permissions as $permission) {
                DB::table('permissions')->insertOrIgnore([
                    'name' => $permission,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $superAdminId = DB::table('roles')->where('name', 'SuperAdmin')->where('guard_name', 'web')->value('id');

            if ($superAdminId) {
                $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->pluck('id');

                foreach ($permissionIds as $permissionId) {
                    DB::table('role_has_permissions')->insertOrIgnore([
                        'permission_id' => $permissionId,
                        'role_id' => $superAdminId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_identidad');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->whereIn('name', [
                'documentos-identidad.ver',
                'documentos-identidad.subir',
                'documentos-identidad.reemplazar',
                'documentos-identidad.eliminar',
                'documentos-identidad.descargar',
                'documentos-identidad.auditar',
            ])->delete();
        }
    }
};
