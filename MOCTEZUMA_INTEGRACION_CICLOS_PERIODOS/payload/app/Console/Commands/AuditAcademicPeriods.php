<?php

namespace App\Console\Commands;

use App\Services\AcademicPeriodResolver;
use Illuminate\Console\Command;

class AuditAcademicPeriods extends Command
{
    protected $signature = 'academic-periods:audit {--json : Devuelve el resultado en JSON}';

    protected $description = 'Audita ciclos escolares, periodos y fechas sin modificar la base de datos.';

    public function handle(AcademicPeriodResolver $resolver): int
    {
        $rows = $resolver->audit();

        if ($this->option('json')) {
            $this->line($rows->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $rows->isEmpty() ? self::SUCCESS : self::FAILURE;
        }

        if ($rows->isEmpty()) {
            $this->info('No se detectaron inconsistencias entre ciclo escolar, periodo y fechas.');
            return self::SUCCESS;
        }

        $this->warn('Se detectaron '.$rows->count().' periodo(s) inconsistente(s). No se modificó ningún registro.');

        $this->table(
            ['ID', 'Generación', 'Cuat.', 'Periodo', 'Ciclo', 'Inicio', 'Término', 'Inconsistencias'],
            $rows->map(fn (array $row) => [
                $row['id'],
                $row['generacion'],
                $row['cuatrimestre'],
                $row['periodo'],
                $row['ciclo_escolar'],
                $row['inicio'] ?? '—',
                $row['termino'] ?? '—',
                implode(' | ', $row['issues']),
            ])->all()
        );

        $this->newLine();
        $this->comment('Corrige los registros desde Periodos Escolares y vuelve a ejecutar el comando.');
        $this->comment('Este comando nunca corrige datos automáticamente.');

        return self::FAILURE;
    }
}
