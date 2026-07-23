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
    public const REGEX_PHP = '/^[A-ZÑ&]{4}[0-9]{4}$/u';

    public const REGEX_SQL = '^[A-ZÑ&]{4}[0-9]{4}$';

    public function esValida(?string $matricula): bool
    {
        $matricula = $this->normalizar($matricula);

        return $matricula !== '' && preg_match(self::REGEX_PHP, $matricula) === 1;
    }

    public function normalizar(?string $matricula): string
    {
        return mb_strtoupper(trim((string) $matricula), 'UTF-8');
    }

    public function generarPara(Inscripcion $alumno): string
    {
        return DB::transaction(function () use ($alumno): string {
            // Todos los generadores toman primero el mismo bloqueo para evitar matrículas repetidas por concurrencia.
            Inscripcion::query()->orderBy('id')->lockForUpdate()->value('id');

            /** @var Inscripcion $bloqueado */
            $bloqueado = Inscripcion::query()->lockForUpdate()->findOrFail($alumno->getKey());
            $anterior = $this->normalizar($bloqueado->matricula);

            $matriculaActualEsUnica = $anterior !== ''
                && $this->esValida($anterior)
                && ! Inscripcion::query()
                    ->where('id', '!=', $bloqueado->getKey())
                    ->whereRaw('UPPER(TRIM(matricula)) = ?', [$anterior])
                    ->exists();

            if ($matriculaActualEsUnica) {
                return $anterior;
            }

            $nueva = $this->proponer($bloqueado);

            if ($anterior === $nueva) {
                return $nueva;
            }

            $bloqueado->forceFill(['matricula' => $nueva])->save();

            $this->registrarCambio(
                $bloqueado,
                'generacion_automatica',
                $anterior !== '' ? $anterior : null,
                $nueva,
                ['origen' => 'modal_matriculas']
            );

            return $nueva;
        }, 3);
    }

    public function proponer(Inscripcion $alumno): string
    {
        $prefijo = $this->prefijoDesdeCurp($alumno->CURP);
        $secuencia = max(1, (int) ($alumno->orden ?: 1));

        while ($secuencia <= 7999) {
            $candidato = $prefijo . str_pad((string) (2000 + $secuencia), 4, '0', STR_PAD_LEFT);

            $ocupada = Inscripcion::query()
                ->where('id', '!=', $alumno->getKey())
                ->whereRaw('UPPER(TRIM(matricula)) = ?', [$candidato])
                ->exists();

            if (! $ocupada) {
                return $candidato;
            }

            $secuencia++;
        }

        throw new RuntimeException('No fue posible encontrar una matrícula disponible para este alumno.');
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

    private function prefijoDesdeCurp(?string $curp): string
    {
        $curp = mb_strtoupper(trim((string) $curp), 'UTF-8');

        if (mb_strlen($curp, 'UTF-8') < 4) {
            throw new RuntimeException('La CURP debe tener al menos cuatro caracteres para generar la matrícula.');
        }

        $prefijo = mb_substr($curp, 0, 4, 'UTF-8');
        $prefijo = preg_replace('/[^A-ZÑ&]/u', 'X', $prefijo) ?: '';

        if (mb_strlen($prefijo, 'UTF-8') !== 4) {
            throw new RuntimeException('No fue posible obtener un prefijo válido desde la CURP.');
        }

        return $prefijo;
    }
}
