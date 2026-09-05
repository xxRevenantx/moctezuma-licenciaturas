<?php

namespace App\Services;

use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IdControlInternoService
{
    public const REGEX_PHP = '/^[A-ZÑ&]{4}[0-9]{4}$/u';

    public const REGEX_SQL = '^[A-ZÑ&]{4}[0-9]{4}$';

    public function esValido(?string $id): bool
    {
        $id = $this->normalizar($id);

        return $id !== '' && preg_match(self::REGEX_PHP, $id) === 1;
    }

    public function normalizar(?string $id): string
    {
        return mb_strtoupper(trim((string) $id), 'UTF-8');
    }

    /**
     * Genera el identificador de control interno institucional.
     * Este valor NO es la matrícula oficial SEG.
     */
    public function generarPara(Inscripcion $alumno): string
    {
        return DB::transaction(function () use ($alumno): string {
            // Serializa generadores para evitar colisiones por concurrencia.
            Inscripcion::query()->orderBy('id')->lockForUpdate()->value('id');

            /** @var Inscripcion $bloqueado */
            $bloqueado = Inscripcion::query()->lockForUpdate()->findOrFail($alumno->getKey());
            $actual = $this->normalizar($bloqueado->matricula_interna);

            if ($actual !== '' && $this->esValido($actual)) {
                $unico = ! Inscripcion::query()
                    ->where('id', '!=', $bloqueado->getKey())
                    ->where('matricula_interna', $actual)
                    ->exists();

                if ($unico) {
                    return $actual;
                }
            }

            $nueva = $this->proponer($bloqueado);
            $bloqueado->forceFill(['matricula_interna' => $nueva])->save();

            return $nueva;
        }, 3);
    }

    public function proponer(Inscripcion $alumno): string
    {
        $prefijo = $this->prefijoDesdeCurp($alumno->CURP);
        $secuencia = max(1, (int) ($alumno->orden ?: 1));

        while ($secuencia <= 7999) {
            $numero = 2000 + $secuencia;
            if ($numero > 9999) {
                break;
            }

            $candidato = $prefijo.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);

            $ocupado = Inscripcion::query()
                ->where('id', '!=', $alumno->getKey())
                ->where('matricula_interna', $candidato)
                ->exists();

            if (! $ocupado) {
                return $candidato;
            }

            $secuencia++;
        }

        throw new RuntimeException('No fue posible encontrar un ID de control interno disponible para este alumno.');
    }

    private function prefijoDesdeCurp(?string $curp): string
    {
        $curp = mb_strtoupper(trim((string) $curp), 'UTF-8');

        if (mb_strlen($curp, 'UTF-8') < 4) {
            throw new RuntimeException('La CURP debe tener al menos cuatro caracteres para generar el ID de control interno.');
        }

        $prefijo = mb_substr($curp, 0, 4, 'UTF-8');
        $prefijo = preg_replace('/[^A-ZÑ&]/u', 'X', $prefijo) ?: '';

        if (mb_strlen($prefijo, 'UTF-8') !== 4) {
            throw new RuntimeException('No fue posible obtener un prefijo válido desde la CURP.');
        }

        return $prefijo;
    }
}
