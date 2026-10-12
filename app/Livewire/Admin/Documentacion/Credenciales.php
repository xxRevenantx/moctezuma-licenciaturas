<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Services\FotografiaEstudianteService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Credenciales extends Component
{
    use WithFileUploads;

    public string $modo = 'alumno';
    public string $query = '';
    public string $licenciaturaId = '';
    public string $generacionId = '';
    public string $cuatrimestreId = '';
    public array $alumnosSeleccionadosIds = [];

    /** @var array<int, mixed> */
    public array $fotosIndividuales = [];

    /** Fotografía recortada desde el editor visual. */
    public $fotoEditor = null;

    /** @var array<int, mixed> */
    public array $fotosMasivas = [];

    /** @var array<int, string> */
    public array $erroresCargaMasiva = [];

    public int $cargaMasivaGuardadas = 0;
    public int $cargaMasivaNoAsignadas = 0;

    public function updatedModo(): void
    {
        $this->query = '';
        $this->licenciaturaId = '';
        $this->generacionId = '';
        $this->cuatrimestreId = '';
        $this->alumnosSeleccionadosIds = [];
        $this->limpiarPendientesFotografia();
    }

    public function updatedLicenciaturaId(): void
    {
        $this->alumnosSeleccionadosIds = [];
        $this->limpiarPendientesFotografia(false);
    }

    public function updatedGeneracionId(): void
    {
        $this->alumnosSeleccionadosIds = [];
        $this->limpiarPendientesFotografia(false);
    }

    public function updatedCuatrimestreId(): void
    {
        $this->alumnosSeleccionadosIds = [];
        $this->limpiarPendientesFotografia(false);
    }

    public function seleccionarAlumno(int $id): void
    {
        if (! in_array($id, $this->alumnosSeleccionadosIds, true)) {
            $this->alumnosSeleccionadosIds[] = $id;
        }

        if ($this->modo === 'alumno') {
            $this->query = '';
        }
    }

    public function alternarAlumno(int $id): void
    {
        if (in_array($id, $this->alumnosSeleccionadosIds, true)) {
            $this->alumnosSeleccionadosIds = array_values(array_filter(
                $this->alumnosSeleccionadosIds,
                fn ($alumnoId) => (int) $alumnoId !== $id
            ));

            return;
        }

        $this->alumnosSeleccionadosIds[] = $id;
    }

    public function eliminarAlumnoSeleccionado(int $id): void
    {
        $this->alumnosSeleccionadosIds = array_values(array_filter(
            $this->alumnosSeleccionadosIds,
            fn ($alumnoId) => (int) $alumnoId !== $id
        ));
    }

    public function seleccionarTodosResultados(): void
    {
        $ids = $this->consultaResultados()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->alumnosSeleccionadosIds = array_values(array_unique([
            ...$this->alumnosSeleccionadosIds,
            ...$ids,
        ]));
    }

    public function limpiarSeleccion(): void
    {
        $this->alumnosSeleccionadosIds = [];
    }

    public function restablecer(): void
    {
        $this->modo = 'alumno';
        $this->query = '';
        $this->licenciaturaId = '';
        $this->generacionId = '';
        $this->cuatrimestreId = '';
        $this->alumnosSeleccionadosIds = [];
        $this->limpiarPendientesFotografia();
    }

    public function guardarFoto(int $alumnoId, FotografiaEstudianteService $imagenes): void
    {
        $this->validate([
            "fotosIndividuales.{$alumnoId}" => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            "fotosIndividuales.{$alumnoId}.required" => 'Selecciona una fotografía.',
            "fotosIndividuales.{$alumnoId}.image" => 'El archivo seleccionado no es una imagen válida.',
            "fotosIndividuales.{$alumnoId}.mimes" => 'La fotografía debe ser JPG, JPEG, PNG o WebP.',
            "fotosIndividuales.{$alumnoId}.max" => 'La fotografía no puede superar 5 MB.',
        ]);

        $alumno = Inscripcion::query()
            ->where('status', 'true')
            ->findOrFail($alumnoId);

        $archivo = $this->fotosIndividuales[$alumnoId];
        $fotoAnterior = $alumno->foto;
        $fotoNueva = null;
        $actualizada = false;

        try {
            $fotoNueva = $imagenes->guardar($archivo);
            $alumno->update(['foto' => $fotoNueva]);
            $actualizada = true;

            if ($fotoAnterior && $fotoAnterior !== $fotoNueva) {
                $imagenes->eliminar($fotoAnterior);
            }

            unset($this->fotosIndividuales[$alumnoId]);
            $this->resetValidation("fotosIndividuales.{$alumnoId}");

            $this->dispatch('swal', [
                'title' => 'Fotografía actualizada',
                'text' => 'Se normalizó a 2.5 × 3 cm y quedó lista para PDF y Word.',
                'icon' => 'success',
            ]);
        } catch (Throwable $e) {
            if ($fotoNueva && ! $actualizada) {
                $imagenes->eliminar($fotoNueva);
            }

            report($e);
            $this->addError("fotosIndividuales.{$alumnoId}", $e->getMessage());
        }
    }

    public function guardarFotoEditor(int $alumnoId, FotografiaEstudianteService $imagenes): void
    {
        $this->validate([
            'fotoEditor' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'fotoEditor.required' => 'Prepara la fotografía antes de guardarla.',
            'fotoEditor.image' => 'La fotografía preparada no es una imagen válida.',
            'fotoEditor.mimes' => 'La fotografía debe ser JPG, JPEG, PNG o WebP.',
            'fotoEditor.max' => 'La fotografía no puede superar 5 MB.',
        ]);

        $alumno = Inscripcion::query()
            ->where('status', 'true')
            ->findOrFail($alumnoId);

        $fotoAnterior = $alumno->foto;
        $fotoNueva = null;
        $actualizada = false;

        try {
            $fotoNueva = $imagenes->guardar($this->fotoEditor);
            $alumno->update(['foto' => $fotoNueva]);
            $actualizada = true;

            if ($fotoAnterior && $fotoAnterior !== $fotoNueva) {
                $imagenes->eliminar($fotoAnterior);
            }

            $this->fotoEditor = null;
            $this->resetValidation('fotoEditor');

            $this->dispatch('swal', [
                'title' => 'Fotografía actualizada',
                'text' => 'Se guardó el encuadre seleccionado en formato institucional 2.5 × 3 cm.',
                'icon' => 'success',
            ]);
        } catch (Throwable $e) {
            if ($fotoNueva && ! $actualizada) {
                $imagenes->eliminar($fotoNueva);
            }

            report($e);
            $this->addError('fotoEditor', $e->getMessage());
            throw $e;
        }
    }

    public function guardarFotosMasivas(FotografiaEstudianteService $imagenes): void
    {
        $this->validate([
            'fotosMasivas' => ['required', 'array', 'min:1', 'max:100'],
            'fotosMasivas.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'fotosMasivas.required' => 'Selecciona al menos una fotografía.',
            'fotosMasivas.max' => 'Puedes procesar hasta 100 fotografías por lote.',
            'fotosMasivas.*.image' => 'Uno de los archivos no es una imagen válida.',
            'fotosMasivas.*.mimes' => 'Las fotografías deben ser JPG, JPEG, PNG o WebP.',
            'fotosMasivas.*.max' => 'Cada fotografía puede pesar como máximo 5 MB.',
        ]);

        $this->erroresCargaMasiva = [];
        $this->cargaMasivaGuardadas = 0;
        $this->cargaMasivaNoAsignadas = 0;

        $alumnos = $this->consultaBase()->get();
        $indice = $this->crearIndiceIdentificadores($alumnos);

        foreach ($this->fotosMasivas as $archivo) {
            $nombreOriginal = (string) $archivo->getClientOriginalName();
            $base = pathinfo($nombreOriginal, PATHINFO_FILENAME);
            $claveArchivo = $this->normalizarIdentificador($base);
            $coincidencias = $this->buscarCoincidenciasArchivo($claveArchivo, $indice);

            if ($coincidencias->isEmpty()) {
                $this->cargaMasivaNoAsignadas++;
                $this->erroresCargaMasiva[] = "{$nombreOriginal}: no coincide con matrícula, matrícula interna o CURP de un alumno activo.";
                continue;
            }

            if ($coincidencias->count() > 1) {
                $this->cargaMasivaNoAsignadas++;
                $this->erroresCargaMasiva[] = "{$nombreOriginal}: la coincidencia es ambigua; renombra el archivo exactamente con la matrícula o CURP.";
                continue;
            }

            /** @var Inscripcion $alumno */
            $alumno = $coincidencias->first();
            $fotoAnterior = $alumno->foto;
            $fotoNueva = null;

            try {
                $fotoNueva = $imagenes->guardar($archivo);
                $alumno->update(['foto' => $fotoNueva]);

                if ($fotoAnterior && $fotoAnterior !== $fotoNueva) {
                    $imagenes->eliminar($fotoAnterior);
                }

                $this->cargaMasivaGuardadas++;
            } catch (Throwable $e) {
                if ($fotoNueva) {
                    $imagenes->eliminar($fotoNueva);
                }

                report($e);
                $this->cargaMasivaNoAsignadas++;
                $this->erroresCargaMasiva[] = "{$nombreOriginal}: {$e->getMessage()}";
            }
        }

        $this->fotosMasivas = [];
        $this->resetValidation('fotosMasivas');

        $texto = "Se guardaron {$this->cargaMasivaGuardadas} fotografía(s).";
        if ($this->cargaMasivaNoAsignadas > 0) {
            $texto .= " {$this->cargaMasivaNoAsignadas} archivo(s) requieren revisión.";
        }

        $this->dispatch('swal', [
            'title' => $this->cargaMasivaNoAsignadas > 0 ? 'Carga masiva finalizada con observaciones' : 'Carga masiva completada',
            'text' => $texto,
            'icon' => $this->cargaMasivaNoAsignadas > 0 ? 'warning' : 'success',
        ]);
    }

    public function limpiarCargaMasiva(): void
    {
        $this->fotosMasivas = [];
        $this->erroresCargaMasiva = [];
        $this->cargaMasivaGuardadas = 0;
        $this->cargaMasivaNoAsignadas = 0;
        $this->resetValidation('fotosMasivas');
    }

    private function consultaBase(): Builder
    {
        return Inscripcion::query()
            ->with(['licenciatura', 'generacion', 'cuatrimestre', 'modalidad'])
            ->where('status', 'true');
    }

    private function consultaResultados(): Builder
    {
        $query = $this->consultaBase();

        switch ($this->modo) {
            case 'licenciatura':
                if ($this->licenciaturaId === '') {
                    return $query->whereRaw('1 = 0');
                }
                $query->where('licenciatura_id', $this->licenciaturaId);
                break;

            case 'generacion':
                if ($this->generacionId === '') {
                    return $query->whereRaw('1 = 0');
                }
                $query->where('generacion_id', $this->generacionId);
                break;

            case 'cuatrimestre':
                if ($this->cuatrimestreId === '') {
                    return $query->whereRaw('1 = 0');
                }
                $query->where('cuatrimestre_id', $this->cuatrimestreId);
                break;

            default:
                if (mb_strlen(trim($this->query)) < 2) {
                    return $query->whereRaw('1 = 0');
                }
                break;
        }

        $busqueda = trim($this->query);
        if ($busqueda !== '') {
            $terminos = preg_split('/\s+/', $busqueda, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            foreach ($terminos as $termino) {
                $query->where(function (Builder $q) use ($termino) {
                    $q->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido_paterno', 'like', "%{$termino}%")
                        ->orWhere('apellido_materno', 'like', "%{$termino}%")
                        ->orWhere('CURP', 'like', "%{$termino}%")
                        ->orWhere('matricula', 'like', "%{$termino}%")
                        ->orWhere('matricula_interna', 'like', "%{$termino}%");
                });
            }
        }

        return $query
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre');
    }

    private function resultados(): Collection
    {
        return $this->consultaResultados()->get();
    }

    private function seleccionados(): Collection
    {
        if (empty($this->alumnosSeleccionadosIds)) {
            return collect();
        }

        $orden = array_flip(array_map('intval', $this->alumnosSeleccionadosIds));

        return $this->consultaBase()
            ->whereIn('id', $this->alumnosSeleccionadosIds)
            ->get()
            ->sortBy(fn (Inscripcion $alumno) => $orden[$alumno->id] ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * @param Collection<int, Inscripcion> $alumnos
     * @return array<string, array<int, Inscripcion>>
     */
    private function crearIndiceIdentificadores(Collection $alumnos): array
    {
        $indice = [];

        foreach ($alumnos as $alumno) {
            foreach ([$alumno->matricula, $alumno->matricula_interna, $alumno->CURP] as $identificador) {
                $clave = $this->normalizarIdentificador((string) $identificador);
                if ($clave === '') {
                    continue;
                }

                $indice[$clave] ??= [];
                if (! collect($indice[$clave])->contains(fn (Inscripcion $item) => $item->id === $alumno->id)) {
                    $indice[$clave][] = $alumno;
                }
            }
        }

        return $indice;
    }

    /** @param array<string, array<int, Inscripcion>> $indice */
    private function buscarCoincidenciasArchivo(string $claveArchivo, array $indice): Collection
    {
        if ($claveArchivo === '') {
            return collect();
        }

        if (isset($indice[$claveArchivo])) {
            return collect($indice[$claveArchivo])->unique('id')->values();
        }

        // También admite nombres como FOTO_MATRICULA o CURP_CREDENCIAL,
        // siempre que exista una sola coincidencia inequívoca.
        $coincidencias = collect();
        foreach ($indice as $identificador => $alumnos) {
            if (mb_strlen($identificador) < 5 || ! str_contains($claveArchivo, $identificador)) {
                continue;
            }

            foreach ($alumnos as $alumno) {
                $coincidencias->push($alumno);
            }
        }

        return $coincidencias->unique('id')->values();
    }

    private function normalizarIdentificador(string $valor): string
    {
        return preg_replace('/[^A-Z0-9]/', '', mb_strtoupper(trim($valor), 'UTF-8')) ?: '';
    }

    private function limpiarPendientesFotografia(bool $masiva = true): void
    {
        $this->fotosIndividuales = [];
        $this->fotoEditor = null;
        if ($masiva) {
            $this->limpiarCargaMasiva();
        }
        $this->resetValidation();
    }

    public function render()
    {
        $licenciaturas = Licenciatura::query()
            ->whereHas('inscripciones', fn (Builder $query) => $query->where('status', 'true'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nombre_corto']);

        $generaciones = Generacion::query()
            ->whereHas('inscripcion', fn (Builder $query) => $query->where('status', 'true'))
            ->orderByDesc('generacion')
            ->get(['id', 'generacion', 'activa']);

        $cuatrimestres = Cuatrimestre::query()
            ->whereHas('inscripciones', fn (Builder $query) => $query->where('status', 'true'))
            ->orderBy('id')
            ->get(['id', 'cuatrimestre', 'nombre_cuatrimestre']);

        $resultados = $this->resultados();

        return view('livewire.admin.documentacion.credenciales', [
            'licenciaturas' => $licenciaturas,
            'generaciones' => $generaciones,
            'cuatrimestres' => $cuatrimestres,
            'resultados' => $resultados,
            'seleccionados' => $this->seleccionados(),
            'resultadosConFoto' => $resultados->filter(fn (Inscripcion $alumno) => $alumno->foto_existe)->count(),
            'resultadosSinFoto' => $resultados->filter(fn (Inscripcion $alumno) => ! $alumno->foto_existe)->count(),
        ]);
    }
}
