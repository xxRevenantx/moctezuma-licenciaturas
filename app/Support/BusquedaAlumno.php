<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/** Búsqueda uniforme por matrícula, CURP, folio y nombres en cualquier orden. */
final class BusquedaAlumno
{
    public static function palabras(string $texto): array
    {
        return array_slice(preg_split('/\s+/u', trim($texto), -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 12);
    }

    public static function coincide(array $alumno, string $texto): bool
    {
        $palabras = self::palabras(mb_strtolower($texto));
        if (!$palabras) {
            return true;
        }

        $campos = array_map(static fn ($campo) => mb_strtolower((string) ($alumno[$campo] ?? '')), [
            'nombre', 'apellido_paterno', 'apellido_materno', 'matricula', 'matricula_interna', 'CURP', 'folio',
        ]);
        return collect($palabras)->every(static fn ($palabra) => collect($campos)
            ->contains(static fn ($campo) => str_contains($campo, $palabra)));
    }

    public static function aplicar(Builder $query, string $texto): Builder
    {
        foreach (self::palabras($texto) as $palabra) {
            $patron = '%'.addcslashes($palabra, '%_\\').'%';
            $query->where(static function (Builder $q) use ($patron): void {
                $q->where('nombre', 'like', $patron)
                    ->orWhere('apellido_paterno', 'like', $patron)
                    ->orWhere('apellido_materno', 'like', $patron)
                    ->orWhere('matricula', 'like', $patron)
                    ->orWhere('matricula_interna', 'like', $patron)
                    ->orWhere('CURP', 'like', $patron)
                    ->orWhere('folio', 'like', $patron);
            });
        }
        return $query;
    }
}
