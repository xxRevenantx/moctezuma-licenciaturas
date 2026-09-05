<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REGEX_INTERNO_SQL = '^[A-ZÑ&]{4}[0-9]{4}$';

    public function up(): void
    {
        if (! Schema::hasTable('inscripciones')) {
            return;
        }

        $this->validarDuplicadosLegados();

        if (! Schema::hasColumn('inscripciones', 'matricula_interna')) {
            Schema::table('inscripciones', function (Blueprint $table): void {
                $table->string('matricula_interna', 8)->nullable()->after('matricula');
            });
        }

        // La matrícula SEG puede tener una cantidad variable de dígitos y puede
        // permanecer pendiente hasta que la autoridad la asigne.
        DB::statement('ALTER TABLE `inscripciones` MODIFY `matricula` VARCHAR(255) NULL');

        DB::table('inscripciones')
            ->whereRaw("TRIM(COALESCE(matricula, '')) = ''")
            ->update(['matricula' => null]);

        DB::table('inscripciones')
            ->whereRaw("TRIM(COALESCE(matricula_interna, '')) = ''")
            ->update(['matricula_interna' => null]);

        // Los valores con formato ABCD1234 eran el identificador institucional
        // que anteriormente estaba mezclado en la columna `matricula`.
        DB::statement(
            "UPDATE `inscripciones`
             SET `matricula_interna` = UPPER(TRIM(`matricula`)), `matricula` = NULL
             WHERE `matricula` IS NOT NULL
               AND UPPER(TRIM(`matricula`)) REGEXP '".self::REGEX_INTERNO_SQL."'"
        );

        $this->generarIdsInternosFaltantes();
        $this->migrarSnapshotsHistoricos();
        $this->ampliarBitacoraMatriculas();

        if (! $this->indexExists('inscripciones', 'inscripciones_matricula_unique')) {
            Schema::table('inscripciones', function (Blueprint $table): void {
                $table->unique('matricula', 'inscripciones_matricula_unique');
            });
        }

        if (! $this->indexExists('inscripciones', 'inscripciones_matricula_interna_unique')) {
            Schema::table('inscripciones', function (Blueprint $table): void {
                $table->unique('matricula_interna', 'inscripciones_matricula_interna_unique');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('inscripciones')) {
            return;
        }

        if ($this->indexExists('inscripciones', 'inscripciones_matricula_interna_unique')) {
            Schema::table('inscripciones', function (Blueprint $table): void {
                $table->dropUnique('inscripciones_matricula_interna_unique');
            });
        }

        if ($this->indexExists('inscripciones', 'inscripciones_matricula_unique')) {
            Schema::table('inscripciones', function (Blueprint $table): void {
                $table->dropUnique('inscripciones_matricula_unique');
            });
        }

        // Recupera el esquema anterior sólo cuando no existe matrícula SEG.
        // Si un alumno ya tiene ambos identificadores, se conserva la matrícula SEG.
        if (Schema::hasColumn('inscripciones', 'matricula_interna')) {
            DB::statement(
                "UPDATE `inscripciones`
                 SET `matricula` = `matricula_interna`
                 WHERE (`matricula` IS NULL OR TRIM(`matricula`) = '')
                   AND `matricula_interna` IS NOT NULL"
            );

            Schema::table('inscripciones', function (Blueprint $table): void {
                $table->dropColumn('matricula_interna');
            });
        }

        DB::statement("UPDATE `inscripciones` SET `matricula` = '' WHERE `matricula` IS NULL");
        DB::statement('ALTER TABLE `inscripciones` MODIFY `matricula` VARCHAR(8) NOT NULL');

        if (Schema::hasTable('historial_inscripciones') && Schema::hasColumn('historial_inscripciones', 'matricula_interna')) {
            Schema::table('historial_inscripciones', function (Blueprint $table): void {
                $table->dropColumn('matricula_interna');
            });
        }

        if (Schema::hasTable('movimientos_academicos') && Schema::hasColumn('movimientos_academicos', 'matricula_interna_snapshot')) {
            Schema::table('movimientos_academicos', function (Blueprint $table): void {
                $table->dropColumn('matricula_interna_snapshot');
            });
        }
    }

    private function validarDuplicadosLegados(): void
    {
        $duplicadasSeg = DB::table('inscripciones')
            ->selectRaw('TRIM(matricula) AS valor, COUNT(*) AS total')
            ->whereNotNull('matricula')
            ->whereRaw("TRIM(matricula) <> ''")
            ->whereRaw("TRIM(matricula) REGEXP '^[0-9]+$'")
            ->groupByRaw('TRIM(matricula)')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('valor')
            ->all();

        if ($duplicadasSeg !== []) {
            throw new RuntimeException(
                'No se puede separar la matrícula SEG porque existen matrículas numéricas duplicadas: '.implode(', ', $duplicadasSeg)
            );
        }

        $duplicadosInternos = DB::table('inscripciones')
            ->selectRaw('UPPER(TRIM(matricula)) AS valor, COUNT(*) AS total')
            ->whereNotNull('matricula')
            ->whereRaw("UPPER(TRIM(matricula)) REGEXP '".self::REGEX_INTERNO_SQL."'")
            ->groupByRaw('UPPER(TRIM(matricula))')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('valor')
            ->all();

        if ($duplicadosInternos !== []) {
            throw new RuntimeException(
                'No se puede separar el ID de control interno porque existen identificadores duplicados: '.implode(', ', $duplicadosInternos)
            );
        }
    }

    private function generarIdsInternosFaltantes(): void
    {
        $ocupados = DB::table('inscripciones')
            ->whereNotNull('matricula_interna')
            ->whereRaw("TRIM(matricula_interna) <> ''")
            ->pluck('matricula_interna')
            ->map(fn ($valor) => mb_strtoupper(trim((string) $valor), 'UTF-8'))
            ->flip()
            ->all();

        DB::table('inscripciones')
            ->where(function ($query): void {
                $query->whereNull('matricula_interna')
                    ->orWhereRaw("TRIM(matricula_interna) = ''");
            })
            ->orderBy('id')
            ->chunkById(200, function ($alumnos) use (&$ocupados): void {
                foreach ($alumnos as $alumno) {
                    $curp = mb_strtoupper(trim((string) $alumno->CURP), 'UTF-8');
                    if (mb_strlen($curp, 'UTF-8') < 4) {
                        throw new RuntimeException('El alumno #'.$alumno->id.' no tiene una CURP utilizable para generar su ID de control interno.');
                    }

                    $prefijo = mb_substr($curp, 0, 4, 'UTF-8');
                    $prefijo = preg_replace('/[^A-ZÑ&]/u', 'X', $prefijo) ?: '';
                    if (mb_strlen($prefijo, 'UTF-8') !== 4) {
                        throw new RuntimeException('No fue posible generar el prefijo interno del alumno #'.$alumno->id.'.');
                    }

                    $secuencia = max(1, (int) ($alumno->orden ?: 1));
                    $candidato = null;

                    while ($secuencia <= 7999) {
                        $numero = 2000 + $secuencia;
                        if ($numero > 9999) {
                            break;
                        }

                        $posible = $prefijo.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);
                        if (! isset($ocupados[$posible])) {
                            $candidato = $posible;
                            break;
                        }

                        $secuencia++;
                    }

                    if ($candidato === null) {
                        throw new RuntimeException('No fue posible generar un ID de control interno único para el alumno #'.$alumno->id.'.');
                    }

                    DB::table('inscripciones')->where('id', $alumno->id)->update([
                        'matricula_interna' => $candidato,
                    ]);
                    $ocupados[$candidato] = 1;
                }
            }, 'id');
    }

    private function migrarSnapshotsHistoricos(): void
    {
        if (Schema::hasTable('historial_inscripciones')) {
            if (! Schema::hasColumn('historial_inscripciones', 'matricula_interna')) {
                Schema::table('historial_inscripciones', function (Blueprint $table): void {
                    $table->string('matricula_interna', 8)->nullable()->after('matricula');
                });
            }

            DB::statement('ALTER TABLE `historial_inscripciones` MODIFY `matricula` VARCHAR(255) NULL');
            DB::statement(
                "UPDATE `historial_inscripciones`
                 SET `matricula_interna` = UPPER(TRIM(`matricula`)), `matricula` = NULL
                 WHERE `matricula` IS NOT NULL
                   AND UPPER(TRIM(`matricula`)) REGEXP '".self::REGEX_INTERNO_SQL."'"
            );
        }

        if (Schema::hasTable('movimientos_academicos')) {
            if (! Schema::hasColumn('movimientos_academicos', 'matricula_interna_snapshot')) {
                Schema::table('movimientos_academicos', function (Blueprint $table): void {
                    $table->string('matricula_interna_snapshot', 8)->nullable()->after('matricula_snapshot');
                });
            }

            DB::statement('ALTER TABLE `movimientos_academicos` MODIFY `matricula_snapshot` VARCHAR(255) NULL');
            DB::statement(
                "UPDATE `movimientos_academicos`
                 SET `matricula_interna_snapshot` = UPPER(TRIM(`matricula_snapshot`)), `matricula_snapshot` = NULL
                 WHERE `matricula_snapshot` IS NOT NULL
                   AND UPPER(TRIM(`matricula_snapshot`)) REGEXP '".self::REGEX_INTERNO_SQL."'"
            );
        }
    }

    private function ampliarBitacoraMatriculas(): void
    {
        if (! Schema::hasTable('matricula_bitacoras')) {
            return;
        }

        // La matrícula SEG ya no está limitada al antiguo formato de 8 caracteres.
        DB::statement('ALTER TABLE `matricula_bitacoras` MODIFY `valor_anterior` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `matricula_bitacoras` MODIFY `valor_nuevo` VARCHAR(255) NULL');
    }

    private function indexExists(string $table, string $index): bool
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }
};
