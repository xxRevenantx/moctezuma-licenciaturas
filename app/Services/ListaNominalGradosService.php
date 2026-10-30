<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Listas de estudiantes por licenciatura y grado; sin modificar los expedientes. */
class ListaNominalGradosService
{
    public function __construct(private readonly EstadisticaGeneralGradosService $estadistica) {}

    public function generar(array $datos = []): array
    {
        $filtros = $this->estadistica->normalizarFiltros($datos);
        $grados = $this->normalizarGrados($datos['grados'] ?? null);
        $renglonesVacios = min(10, max(0, (int) ($datos['renglones_vacios'] ?? 0)));
        $fecha = now()->toDateString();
        $corte = CarbonImmutable::parse($fecha)->startOfDay();
        $grupos = [];
        $totales = ['hombres' => 0, 'mujeres' => 0, 'total' => 0];

        if ($grados !== []) {
            $alumnos = $this->estadistica->consultaMatriculaVigente($filtros)
                ->select([
                    'i.id', 'i.nombre', 'i.apellido_paterno', 'i.apellido_materno',
                    'i.fecha_nacimiento', 'i.sexo', 'l.id as licenciatura_id',
                    'l.nombre as licenciatura', 'c.cuatrimestre',
                ])
                ->orderBy('l.nombre')
                ->orderBy('i.apellido_paterno')
                ->orderBy('i.apellido_materno')
                ->orderBy('i.nombre')
                ->orderBy('i.id')
                ->get();

            foreach ($alumnos as $alumno) {
                $grado = intdiv((int) $alumno->cuatrimestre - 1, 3) + 1;
                if (! in_array($grado, $grados, true)) {
                    continue;
                }
                $clave = $alumno->licenciatura_id.'-'.$grado;
                if (! isset($grupos[$clave])) {
                    $grupos[$clave] = [
                        'licenciatura_id' => (int) $alumno->licenciatura_id,
                        'licenciatura' => $alumno->licenciatura,
                        'grado' => $grado,
                        'alumnos' => [],
                        'totales' => ['hombres' => 0, 'mujeres' => 0, 'total' => 0],
                    ];
                }
                $sexo = strtoupper(trim((string) $alumno->sexo));
                $edad = null;
                if ($alumno->fecha_nacimiento) {
                    try {
                        $nacimiento = CarbonImmutable::parse($alumno->fecha_nacimiento)->startOfDay();
                        if ($nacimiento->lessThanOrEqualTo($corte)) {
                            $edad = (int) floor($nacimiento->diffInYears($corte));
                        }
                    } catch (\Throwable) {
                        // Fechas incompletas de registros históricos: mostrar guion.
                    }
                }
                $grupos[$clave]['alumnos'][] = [
                    'id' => (int) $alumno->id,
                    'apellido_paterno' => $alumno->apellido_paterno,
                    'apellido_materno' => $alumno->apellido_materno ?: '',
                    'nombre' => $alumno->nombre,
                    'edad' => $edad,
                ];
                $grupos[$clave]['totales']['total']++;
                $totales['total']++;
                if ($sexo === 'H' || $sexo === 'M') {
                    $campo = $sexo === 'H' ? 'hombres' : 'mujeres';
                    $grupos[$clave]['totales'][$campo]++;
                    $totales[$campo]++;
                }
            }
        }

        // Orden alfabético estable también en instalaciones con colaciones
        // diferentes (Álvarez antes de Zapata, independientemente de SQLite/MySQL).
        foreach ($grupos as &$grupo) {
            usort($grupo['alumnos'], static function (array $a, array $b): int {
                foreach (['apellido_paterno', 'apellido_materno', 'nombre'] as $campo) {
                    $resultado = strcasecmp(Str::ascii((string) $a[$campo]), Str::ascii((string) $b[$campo]));
                    if ($resultado !== 0) {
                        return $resultado;
                    }
                }
                return $a['id'] <=> $b['id'];
            });
        }
        unset($grupo);

        // Un grupo (licenciatura+grado) equivale a una hoja/página propia.
        $grupos = array_values($grupos);
        usort($grupos, static fn (array $a, array $b): int =>
            (strcmp($a['licenciatura'], $b['licenciatura']) ?: ($a['grado'] <=> $b['grado'])));

        if (filter_var($datos['mostrar_grados_vacios'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $porLicenciatura = [];
            foreach ($grupos as $grupo) {
                $porLicenciatura[$grupo['licenciatura_id']] = $grupo['licenciatura'];
            }
            // Se incorporan grados sin alumnos sólo para licenciaturas con matrícula filtrada.
            foreach ($porLicenciatura as $id => $nombre) {
                foreach ($grados as $grado) {
                    if (! collect($grupos)->contains(fn ($g) => $g['licenciatura_id'] === $id && $g['grado'] === $grado)) {
                        $grupos[] = ['licenciatura_id' => $id, 'licenciatura' => $nombre,
                            'grado' => $grado, 'alumnos' => [],
                            'totales' => ['hombres' => 0, 'mujeres' => 0, 'total' => 0]];
                    }
                }
            }
            usort($grupos, static fn (array $a, array $b): int =>
                (strcmp($a['licenciatura'], $b['licenciatura']) ?: ($a['grado'] <=> $b['grado'])));
        }

        return [
            'fecha_corte' => $fecha,
            'filtros' => $filtros,
            'grados' => $grados,
            'renglones_vacios' => $renglonesVacios,
            'grupos' => $grupos,
            'totales' => $totales,
            'licenciaturas_count' => count(array_unique(array_column($grupos, 'licenciatura_id'))),
            'modalidades' => $this->etiquetas('modalidades', 'nombre', $filtros['modalidad_ids']),
            'generaciones' => $this->etiquetas('generaciones', 'generacion', $filtros['generacion_ids']),
            'alertas_avance' => app(AcademicProgressAlertService::class)->evaluar($filtros),
        ];
    }

    private function normalizarGrados(mixed $input): array
    {
        if ($input === null || $input === '') {
            return [1, 2, 3];
        }
        $valores = is_array($input) ? $input : explode(',', (string) $input);
        $permitidos = [];
        foreach ($valores as $valor) {
            if (is_scalar($valor) && in_array((string) $valor, ['1', '2', '3'], true)) {
                $permitidos[] = (int) $valor;
            }
        }
        return array_values(array_unique($permitidos));
    }

    private function etiquetas(string $tabla, string $campo, ?array $ids): string
    {
        if ($ids === null) {
            return 'Todas';
        }
        if ($ids === []) {
            return 'Ninguna';
        }
        return DB::table($tabla)->whereIn('id', $ids)->orderBy($campo)->pluck($campo)->implode(', ') ?: 'Ninguna';
    }
}
