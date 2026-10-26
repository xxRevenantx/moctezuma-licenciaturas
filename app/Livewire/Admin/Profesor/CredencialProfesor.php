<?php

namespace App\Livewire\Admin\Profesor;

use App\Models\Licenciatura;
use App\Models\Profesor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Credenciales docentes PRO: sólo consulta expedientes y prepara los PDFs históricos.
 * No modifica estados, roles, horarios, inscripciones ni calificaciones.
 */
class CredencialProfesor extends Component
{
    use WithPagination;

    public const MAX_CREDENCIALES = 100;

    public string $tipo = 'docente';
    public string $busqueda = '';
    public string $disponibilidad = '';
    public string $fotografia = '';
    public string $licenciaturaId = '';

    /** @var list<int> */
    public array $seleccionados = [];
    public bool $seleccionarPagina = false;
    public bool $vistaPrevia = false;
    public ?int $previewIndividualId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('admin.administracion'), 403);
    }

    public function updatingPage($page, $pageName = 'page'): void
    {
        $this->seleccionarPagina = false;
        $this->cerrarVistaPrevia();
    }

    public function updated($name, $value): void
    {
        if (in_array($name, ['busqueda', 'disponibilidad', 'fotografia'], true)) {
            $this->resetPage('credenciales_page');
            $this->limpiarSeleccion();
        }
    }

    public function cambiarTipo(string $tipo): void
    {
        abort_unless(in_array($tipo, ['docente', 'estudiante'], true), 422);
        $this->tipo = $tipo;
        $this->cerrarVistaPrevia();
        $this->limpiarSeleccion();
        $this->resetPage('credenciales_page');
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->disponibilidad = '';
        $this->fotografia = '';
        $this->resetPage('credenciales_page');
        $this->limpiarSeleccion();
    }

    public function limpiarSeleccion(): void
    {
        $this->seleccionados = [];
        $this->seleccionarPagina = false;
        $this->cerrarVistaPrevia();
    }

    public function seleccionarResultados(): void
    {
        $consulta = $this->consultaFiltrada();
        $cantidad = (clone $consulta)->count();
        if ($cantidad < 1 || $cantidad > self::MAX_CREDENCIALES) {
            throw ValidationException::withMessages([
                'seleccionados' => 'Filtra entre 1 y '.self::MAX_CREDENCIALES.' profesores para seleccionarlos a todos.',
            ]);
        }

        $this->seleccionados = $consulta->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')->orderBy('nombre')
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->seleccionarPagina = false;
        $this->cerrarVistaPrevia();
    }

    public function updatedSeleccionarPagina($value): void
    {
        $idsPagina = $this->profesores->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $anteriores = array_values(array_unique(array_map('intval', $this->seleccionados)));
        if ($value) {
            $nuevos = array_values(array_unique(array_merge($anteriores, $idsPagina)));
            if (count($nuevos) > self::MAX_CREDENCIALES) {
                $this->seleccionarPagina = false;
                throw ValidationException::withMessages(['seleccionados' => 'Máximo '.self::MAX_CREDENCIALES.' credenciales por PDF.']);
            }
            $this->seleccionados = $nuevos;
        } else {
            $this->seleccionados = array_values(array_diff($anteriores, $idsPagina));
        }
        $this->cerrarVistaPrevia();
    }

    public function updatedSeleccionados(): void
    {
        $this->seleccionados = array_values(array_unique(array_filter(
            array_map('intval', $this->seleccionados), fn (int $id) => $id > 0
        )));
        if (count($this->seleccionados) > self::MAX_CREDENCIALES) {
            throw ValidationException::withMessages(['seleccionados' => 'Máximo '.self::MAX_CREDENCIALES.' credenciales por PDF.']);
        }
        $pagina = $this->profesores->getCollection()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->seleccionarPagina = count($pagina) > 0 && empty(array_diff($pagina, $this->seleccionados));
        $this->cerrarVistaPrevia();
    }

    public function quitarSeleccionado(int $id): void
    {
        $this->seleccionados = array_values(array_diff($this->seleccionados, [$id]));
        $this->seleccionarPagina = false;
        $this->cerrarVistaPrevia();
    }

    public function seleccionarProfesor(int $id): void
    {
        abort_unless(Profesor::query()->whereKey($id)->exists(), 404);
        if (! in_array($id, $this->seleccionados, true)) {
            if (count($this->seleccionados) >= self::MAX_CREDENCIALES) {
                throw ValidationException::withMessages(['seleccionados' => 'Llegaste al máximo permitido.']);
            }
            $this->seleccionados[] = $id;
        }
        $this->cerrarVistaPrevia();
    }

    public function abrirVistaPrevia(?int $profesorId = null): void
    {
        if ($this->tipo === 'estudiante' && ! Licenciatura::query()->whereKey((int) $this->licenciaturaId)->exists()) {
            throw ValidationException::withMessages(['licenciaturaId' => 'Selecciona la licenciatura para esta credencial.']);
        }
        $ids = $profesorId !== null ? [$profesorId] : $this->seleccionados;
        $this->validarIds($ids);
        $this->previewIndividualId = $profesorId;
        $this->vistaPrevia = true;
    }

    public function cerrarVistaPrevia(): void
    {
        $this->vistaPrevia = false;
        $this->previewIndividualId = null;
    }

    private function validarIds(array $ids): void
    {
        $ids = array_map('intval', $ids);
        if (! $ids || count($ids) > self::MAX_CREDENCIALES || count(array_unique($ids)) !== count($ids)
            || min($ids) <= 0 || Profesor::query()->whereIn('id', $ids)->count() !== count($ids)) {
            throw ValidationException::withMessages(['seleccionados' => 'Selecciona entre 1 y 100 profesores válidos para generar las credenciales.']);
        }
    }

    #[Computed]
    public function urlPdf(): ?string
    {
        $ids = $this->previewIndividualId === null ? $this->seleccionados : [$this->previewIndividualId];
        if (! $ids || count($ids) > self::MAX_CREDENCIALES) {
            return null;
        }
        $parametros = ['profesores_ids' => array_values(array_map('intval', $ids))];
        if ($this->tipo === 'estudiante') {
            $parametros['licenciatura_id'] = (int) $this->licenciaturaId;
        }
        $ruta = $this->tipo === 'estudiante'
            ? 'admin.pdf.documentacion.credencial_profesor_estudiante'
            : 'admin.pdf.documentacion.credencial_profesor';
        return route($ruta).'?'.http_build_query($parametros);
    }

    #[Computed]
    public function seleccionDetalle()
    {
        return Profesor::query()->whereIn('id', $this->seleccionados)
            ->with('user:id,CURP')->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')->get();
    }

    private function consultaFiltrada(): Builder
    {
        $consulta = Profesor::query();
        if (in_array($this->disponibilidad, ['true', 'false'], true)) {
            $consulta->where('status', $this->disponibilidad);
        }
        if ($this->fotografia === 'con') {
            $consulta->whereNotNull('foto')->where('foto', '<>', '');
        } elseif ($this->fotografia === 'sin') {
            $consulta->where(fn (Builder $q) => $q->whereNull('foto')->orWhere('foto', ''));
        }
        $texto = trim($this->busqueda);
        if ($texto !== '') {
            $escapado = str_replace(['%', '_'], ['\\%', '\\_'], $texto);
            $consulta->where(function (Builder $q) use ($escapado) {
                $q->where('nombre', 'like', "%{$escapado}%")
                    ->orWhere('apellido_paterno', 'like', "%{$escapado}%")
                    ->orWhere('apellido_materno', 'like', "%{$escapado}%")
                    ->orWhereRaw("CONCAT_WS(' ', apellido_paterno, apellido_materno, nombre) LIKE ?", ["%{$escapado}%"])
                    ->orWhereHas('user', fn (Builder $u) => $u->where('CURP', 'like', "%{$escapado}%"));
            });
        }
        return $consulta;
    }

    #[Computed]
    public function profesores()
    {
        return $this->consultaFiltrada()->with('user:id,CURP,status')
            ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombre')
            ->paginate(12, ['*'], 'credenciales_page');
    }

    #[Computed]
    public function indicadores(): array
    {
        $q = $this->consultaFiltrada();
        return [
            'total' => (clone $q)->count(),
            'disponibles' => (clone $q)->where('status', 'true')->count(),
            'con_foto' => (clone $q)->whereNotNull('foto')->where('foto', '<>', '')->count(),
            'seleccionados' => count($this->seleccionados),
        ];
    }

    public function render()
    {
        return view('livewire.admin.profesor.credencial-profesor', [
            'licenciaturas' => Licenciatura::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }
}
