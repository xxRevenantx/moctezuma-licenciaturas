<?php

namespace App\Livewire\Admin;

use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Generacion;
use App\Services\AcademicMovementService;
use App\Services\AcademicProgressAlertService;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Throwable;

class AvanceAcademico extends Component
{
    public ?string $licenciaturaId = null;
    public ?string $modalidadId = null;
    public ?string $generacionId = null;
    public array $seleccionados = [];
    public array $revision = [];
    public bool $confirmarRevision = false;
    public bool $mostrarJustificados = false;
    public ?int $excepcionAlumnoId = null;
    public string $excepcionMotivo = '';
    public ?string $mensaje = null;

    public function mount(): void
    {
        // Los enlaces desde Matrícula sólo sugieren filtros, nunca conceden permisos.
        foreach (['licenciatura', 'modalidad', 'generacion'] as $campo) {
            $id = request()->query($campo.'_id');
            if (is_scalar($id) && ctype_digit((string) $id) && (int) $id > 0) {
                $this->{$campo.'Id'} = (string) $id;
            }
        }
    }

    public function updatedLicenciaturaId(): void { $this->invalidarRevision(); }
    public function updatedModalidadId(): void { $this->invalidarRevision(); }
    public function updatedGeneracionId(): void { $this->invalidarRevision(); }
    public function updatedSeleccionados(): void { $this->revision = []; $this->confirmarRevision = false; }

    private function invalidarRevision(): void
    {
        $this->seleccionados = [];
        $this->revision = [];
        $this->confirmarRevision = false;
        $this->excepcionAlumnoId = null;
    }

    private function filtros(): array
    {
        return [
            'licenciatura_id' => $this->licenciaturaId,
            'modalidad_id' => $this->modalidadId,
            'generacion_id' => $this->generacionId,
        ];
    }

    private function diagnostico(): array
    {
        return app(AcademicProgressAlertService::class)->evaluar($this->filtros());
    }

    public function seleccionarPendientes(): void
    {
        $this->seleccionados = array_map('strval', array_slice(array_column($this->diagnostico()['pendientes'], 'id'), 0, 100));
        $this->revision = [];
        $this->confirmarRevision = false;
    }

    /** Comprueba selección actual + permisos + reglas de AcademicMovementService. */
    private function construirRevision(array $ids): array
    {
        if (count($ids) === 0 || count($ids) > 100 || count(array_unique($ids)) !== count($ids)) {
            throw new DomainException('Selecciona entre 1 y 100 alumnos diferentes por operación.');
        }
        $candidatos = collect($this->diagnostico()['pendientes'])->keyBy('id');
        $grupos = [];
        $snapshot = [];
        foreach ($ids as $id) {
            $alumno = $candidatos->get($id);
            if (! $alumno) {
                throw new DomainException('La selección contiene alumnos que ya no requieren avance o cambiaron de contexto. Actualiza la revisión.');
            }
            $grupos[$alumno['destino_id']][] = (int) $id;
            $snapshot[$id] = [
                'cuatrimestre_id' => $alumno['cuatrimestre_id'],
                'periodo_id' => $alumno['periodo_id'],
                'destino_id' => $alumno['destino_id'],
            ];
        }
        $resultados = [];
        foreach ($grupos as $destino => $alumnos) {
            $analisis = app(AcademicMovementService::class)->analizarCambioCuatrimestre($alumnos, (int) $destino);
            $resultados[] = [
                'destino_id' => (int) $destino,
                'ids' => $alumnos,
                'valido' => (bool) ($analisis['valido'] ?? false),
                'bloqueos' => $analisis['bloqueos'] ?? [],
                'advertencias' => $analisis['advertencias'] ?? [],
            ];
        }
        return ['ids' => $ids, 'snapshot' => $snapshot, 'grupos' => $resultados,
            'valido' => collect($resultados)->every(fn ($grupo) => $grupo['valido'])];
    }

    public function revisar(): void
    {
        Gate::authorize('admin.administracion');
        $this->mensaje = null;
        $this->confirmarRevision = false;
        $ids = array_map('intval', array_filter($this->seleccionados, fn ($id) => ctype_digit((string) $id) && (int) $id > 0));
        try {
            $this->revision = $this->construirRevision($ids);
        } catch (DomainException $e) {
            $this->revision = [];
            $this->addError('seleccionados', $e->getMessage());
        }
    }

    public function aplicar(AcademicMovementService $movimientos): void
    {
        Gate::authorize('admin.administracion');
        if (! $this->confirmarRevision || ! ($this->revision['valido'] ?? false)) {
            $this->addError('confirmarRevision', 'Primero revisa y confirma un análisis sin bloqueos.');
            return;
        }
        try {
            $anterior = $this->revision;
            $total = DB::transaction(function () use ($anterior, $movimientos): int {
                $nueva = $this->construirRevision($anterior['ids']);
                if (! $nueva['valido'] || $nueva['snapshot'] !== $anterior['snapshot']) {
                    throw new DomainException('La matrícula o el calendario cambiaron desde la revisión; vuelve a analizar antes de continuar.');
                }
                $total = 0;
                foreach ($nueva['grupos'] as $grupo) {
                    $resultado = $movimientos->cambiarCuatrimestre(
                        $grupo['ids'], $grupo['destino_id'], 'ajuste_administrativo',
                        'Regularización supervisada del cuatrimestre según calendario vigente (Control de Avance Académico PRO).'
                    );
                    $total += $resultado['total_alumnos'];
                }
                return $total;
            }, 3);
            $this->invalidarRevision();
            $this->mensaje = "Se regularizaron {$total} alumnos. Los movimientos quedaron auditados y las calificaciones históricas se conservaron.";
        } catch (DomainException $e) {
            $this->revision = [];
            $this->confirmarRevision = false;
            $this->addError('revision', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            $this->addError('revision', 'La operación no se completó. No se aplicaron movimientos parciales.');
        }
    }

    public function abrirExcepcion(int $id): void
    {
        Gate::authorize('admin.administracion');
        if (! collect($this->diagnostico()['pendientes'])->contains('id', $id)) {
            $this->addError('excepcionMotivo', 'Este alumno ya no está pendiente de avance.');
            return;
        }
        $this->excepcionAlumnoId = $id;
        $this->excepcionMotivo = '';
    }

    public function guardarExcepcion(): void
    {
        Gate::authorize('admin.administracion');
        $this->validate(['excepcionAlumnoId' => 'required|integer', 'excepcionMotivo' => 'required|string|min:10|max:1000']);
        if (! Schema::hasTable('avance_academico_excepciones')) {
            $this->addError('excepcionMotivo', 'Ejecuta php artisan migrate para habilitar las excepciones justificadas.');
            return;
        }
        try {
            DB::transaction(function (): void {
            $alumno = collect($this->diagnostico()['pendientes'])->firstWhere('id', $this->excepcionAlumnoId);
            if (! $alumno) {
                throw new DomainException('El alumno ya no tiene una alerta de avance pendiente.');
            }
            DB::table('avance_academico_excepciones')->updateOrInsert(
                ['inscripcion_id' => $alumno['id'], 'periodo_id' => $alumno['periodo_id']],
                ['motivo' => trim($this->excepcionMotivo), 'registrado_por' => auth()->id(),
                    'updated_at' => now(), 'created_at' => now()]
            );
            });
        } catch (DomainException $e) {
            $this->addError('excepcionMotivo', $e->getMessage());
            return;
        }
        $this->excepcionAlumnoId = null;
        $this->excepcionMotivo = '';
        $this->seleccionados = [];
        $this->revision = [];
        $this->mensaje = 'La excepción quedó justificada para el periodo vigente. No se modificó la matrícula.';
    }

    public function quitarExcepcion(int $id): void
    {
        Gate::authorize('admin.administracion');
        $alumno = collect($this->diagnostico()['justificados'])->firstWhere('id', $id);
        if (! $alumno || ! Schema::hasTable('avance_academico_excepciones')) {
            return;
        }
        DB::table('avance_academico_excepciones')
            ->where('inscripcion_id', $id)->where('periodo_id', $alumno['periodo_id'])->delete();
        $this->mensaje = 'Se reactivó la alerta del alumno para revisión.';
    }

    public function render()
    {
        return view('livewire.admin.avance-academico', [
            'diagnostico' => $this->diagnostico(),
            'licenciaturas' => Licenciatura::query()->orderBy('nombre')->get(['id','nombre']),
            'modalidades' => Modalidad::query()->orderBy('nombre')->get(['id','nombre']),
            'generaciones' => Generacion::query()->where('activa', 'true')->orderByDesc('generacion')->get(['id','generacion']),
        ]);
    }
}
