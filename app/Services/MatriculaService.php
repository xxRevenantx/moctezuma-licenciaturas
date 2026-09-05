<?php

namespace App\Services;

use App\Models\Inscripcion;
use App\Models\MatriculaBitacora;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class MatriculaService
{
    /**
     * La matrícula oficial es asignada por la SEG.
     * Puede tener una cantidad variable de dígitos, pero nunca letras.
     */
    public const REGEX_PHP = '/^[0-9]+$/';

    public const REGEX_SQL = '^[0-9]+$';

    public function esValida(?string $matricula): bool
    {
        $matricula = $this->normalizar($matricula);

        return $matricula !== '' && preg_match(self::REGEX_PHP, $matricula) === 1;
    }

    public function normalizar(?string $matricula): string
    {
        return trim((string) $matricula);
    }

    /**
     * Registra o corrige manualmente la matrícula oficial SEG.
     * El sistema nunca genera una matrícula SEG por su cuenta.
     */
    public function registrarPara(Inscripcion $alumno, string $matricula, string $origen = 'modal_matriculas'): string
    {
        $matricula = $this->normalizar($matricula);

        if (! $this->esValida($matricula)) {
            throw new RuntimeException('La matrícula SEG debe contener únicamente números.');
        }

        return DB::transaction(function () use ($alumno, $matricula, $origen): string {
            /** @var Inscripcion $bloqueado */
            $bloqueado = Inscripcion::query()->lockForUpdate()->findOrFail($alumno->getKey());
            $anterior = $this->normalizar($bloqueado->matricula);

            $duplicada = Inscripcion::query()
                ->where('id', '!=', $bloqueado->getKey())
                ->where('matricula', $matricula)
                ->exists();

            if ($duplicada) {
                throw new RuntimeException('La matrícula SEG ya está registrada para otro alumno.');
            }

            if ($anterior === $matricula) {
                return $matricula;
            }

            $bloqueado->forceFill(['matricula' => $matricula])->save();

            $this->registrarCambio(
                $bloqueado,
                $anterior === '' ? 'registro_seg' : 'correccion_seg',
                $anterior !== '' ? $anterior : null,
                $matricula,
                ['origen' => $origen, 'tipo_identificador' => 'matricula_seg']
            );

            return $matricula;
        }, 3);
    }

    public function registrarCambio(
        Inscripcion $alumno,
        string $accion,
        ?string $valorAnterior,
        ?string $valorNuevo,
        array $detalles = []
    ): ?MatriculaBitacora {
        // La operación principal no debe fallar si la migración aún no ha sido ejecutada.
        if (! Schema::hasTable('matricula_bitacoras')) {
            return null;
        }

        return MatriculaBitacora::query()->create([
            'inscripcion_id' => $alumno->getKey(),
            'user_id' => auth()->id(),
            'accion' => $accion,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'detalles' => $detalles !== [] ? $detalles : null,
            'ip' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 1000, ''),
        ]);
    }
}
