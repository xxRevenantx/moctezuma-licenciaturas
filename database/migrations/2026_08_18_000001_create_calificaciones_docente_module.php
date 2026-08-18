<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $this->validarDuplicadosCalificacionesSinConflicto();

        Schema::create('calificacion_docente_capturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_materia_id')->constrained('asignacion_materias')->cascadeOnDelete();
            $table->foreignId('profesor_id')->constrained('profesores')->cascadeOnDelete();
            $table->foreignId('licenciatura_id')->constrained('licenciaturas')->cascadeOnDelete();
            $table->foreignId('modalidad_id')->constrained('modalidades')->cascadeOnDelete();
            $table->foreignId('generacion_id')->constrained('generaciones')->cascadeOnDelete();
            $table->foreignId('cuatrimestre_id')->constrained('cuatrimestres')->cascadeOnDelete();
            $table->string('estado', 30)->default('sin_captura');
            $table->date('fecha_limite')->nullable();
            $table->timestamp('entregado_at')->nullable();
            $table->timestamp('validado_at')->nullable();
            $table->timestamp('reabierto_at')->nullable();
            $table->foreignId('entregado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reabierto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['asignacion_materia_id', 'licenciatura_id', 'modalidad_id', 'generacion_id', 'cuatrimestre_id'],
                'captura_docente_contexto_unique'
            );
            $table->index(['profesor_id', 'estado'], 'captura_docente_profesor_estado_idx');
        });

        Schema::create('calificacion_auditorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calificacion_id')->nullable()->constrained('calificaciones')->nullOnDelete();
            $table->foreignId('alumno_id')->nullable()->constrained('inscripciones')->nullOnDelete();
            $table->foreignId('asignacion_materia_id')->nullable()->constrained('asignacion_materias')->nullOnDelete();
            $table->foreignId('profesor_id')->nullable()->constrained('profesores')->nullOnDelete();
            $table->foreignId('licenciatura_id')->nullable()->constrained('licenciaturas')->nullOnDelete();
            $table->foreignId('modalidad_id')->nullable()->constrained('modalidades')->nullOnDelete();
            $table->foreignId('generacion_id')->nullable()->constrained('generaciones')->nullOnDelete();
            $table->foreignId('cuatrimestre_id')->nullable()->constrained('cuatrimestres')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('valor_anterior')->nullable();
            $table->string('valor_nuevo')->nullable();
            $table->string('origen', 20)->default('manual');
            $table->string('accion', 20);
            $table->json('detalle')->nullable();
            $table->timestamps();

            $table->index(['alumno_id', 'asignacion_materia_id'], 'calif_audit_alumno_asig_idx');
            $table->index(['asignacion_materia_id', 'generacion_id', 'cuatrimestre_id'], 'calif_audit_contexto_idx');
            $table->index(['user_id', 'created_at'], 'calif_audit_usuario_fecha_idx');
        });

        $this->limpiarDuplicadosCalificaciones();

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->unique(
                ['alumno_id', 'asignacion_materia_id', 'modalidad_id', 'generacion_id', 'licenciatura_id', 'cuatrimestre_id'],
                'calificaciones_contexto_unique'
            );
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->index(
                ['licenciatura_id', 'modalidad_id', 'generacion_id', 'status'],
                'inscripciones_contexto_status_idx'
            );
        });

        Schema::table('horarios', function (Blueprint $table) {
            $table->index(
                ['generacion_id', 'licenciatura_id', 'modalidad_id', 'cuatrimestre_id', 'asignacion_materia_id'],
                'horarios_contexto_asignacion_idx'
            );
        });

        $this->crearPermiso();
        $this->limpiarCachePermisos();
    }

    public function down(): void
    {
        if (Schema::hasTable('calificaciones')) {
            Schema::table('calificaciones', function (Blueprint $table) {
                $table->dropUnique('calificaciones_contexto_unique');
            });
        }

        if (Schema::hasTable('inscripciones')) {
            Schema::table('inscripciones', function (Blueprint $table) {
                $table->dropIndex('inscripciones_contexto_status_idx');
            });
        }

        if (Schema::hasTable('horarios')) {
            Schema::table('horarios', function (Blueprint $table) {
                $table->dropIndex('horarios_contexto_asignacion_idx');
            });
        }

        Schema::dropIfExists('calificacion_auditorias');
        Schema::dropIfExists('calificacion_docente_capturas');

        if (Schema::hasTable('permissions')) {
            $permissionId = DB::table('permissions')
                ->where('name', 'calificaciones-docente.ver')
                ->where('guard_name', 'web')
                ->value('id');

            if ($permissionId) {
                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
                }
                if (Schema::hasTable('model_has_permissions')) {
                    DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
                }
                DB::table('permissions')->where('id', $permissionId)->delete();
            }
        }

        $this->limpiarCachePermisos();
    }

    private function validarDuplicadosCalificacionesSinConflicto(): void
    {
        if (!Schema::hasTable('calificaciones')) {
            return;
        }

        $columnas = [
            'alumno_id',
            'asignacion_materia_id',
            'modalidad_id',
            'generacion_id',
            'licenciatura_id',
            'cuatrimestre_id',
        ];

        $duplicados = DB::table('calificaciones')
            ->select($columnas)
            ->selectRaw('COUNT(*) as total')
            ->groupBy($columnas)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $grupo) {
            $query = DB::table('calificaciones');
            foreach ($columnas as $columna) {
                $valor = $grupo->{$columna};
                $valor === null ? $query->whereNull($columna) : $query->where($columna, $valor);
            }

            $valores = $query->pluck('calificacion')
                ->map(function ($valor) {
                    if ($valor === null || trim((string) $valor) === '') {
                        return null;
                    }
                    $texto = strtoupper(trim(str_replace(',', '.', (string) $valor)));
                    if (is_numeric($texto)) {
                        return rtrim(rtrim(number_format((float) $texto, 2, '.', ''), '0'), '.');
                    }
                    return $texto;
                })
                ->uniqueStrict();

            if ($valores->count() > 1) {
                throw new RuntimeException(
                    'Se detectaron calificaciones duplicadas con valores distintos. '.
                    'Resuelve el conflicto antes de ejecutar esta migración (alumno_id='.(string) $grupo->alumno_id.
                    ', asignacion_materia_id='.(string) $grupo->asignacion_materia_id.').'
                );
            }
        }
    }

    private function limpiarDuplicadosCalificaciones(): void
    {
        if (!Schema::hasTable('calificaciones')) {
            return;
        }

        $columnas = [
            'alumno_id',
            'asignacion_materia_id',
            'modalidad_id',
            'generacion_id',
            'licenciatura_id',
            'cuatrimestre_id',
        ];

        $duplicados = DB::table('calificaciones')
            ->select($columnas)
            ->selectRaw('MAX(id) as conservar_id, COUNT(*) as total')
            ->groupBy($columnas)
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $grupo) {
            $query = DB::table('calificaciones')->where('id', '<>', $grupo->conservar_id);

            foreach ($columnas as $columna) {
                $valor = $grupo->{$columna};
                $valor === null ? $query->whereNull($columna) : $query->where($columna, $valor);
            }

            $query->delete();
        }
    }

    private function crearPermiso(): void
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')
            ->where('name', 'calificaciones-docente.ver')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'calificaciones-docente.ver',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['SuperAdmin', 'Admin', 'Profesor'])
            ->where('guard_name', 'web')
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }
    private function limpiarCachePermisos(): void
    {
        if (class_exists(PermissionRegistrar::class)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

};
