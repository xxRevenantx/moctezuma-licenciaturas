<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Cuatrimestre;
use App\Models\Generacion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Services\GeneradorListasService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GeneradorListas extends Component
{
    public array $licenciatura_ids = [];
    public array $generacion_ids = [];
    public array $cuatrimestre_ids = [];
    public string $modalidad_id = '';
    public string $procedencia = 'todos';
    public string $estado = 'activos';
    public string $ciclo_escolar = '';
    public string $busqueda = '';
    public string $modalidad_texto = 'real';
    public string $agrupar = 'licenciatura_generacion';
    public string $orden = 'apellidos';
    public string $orientacion = 'auto';
    public string $titulo = 'LISTA DE GRUPO';
    public string $subtitulo = '';
    public array $columnas = GeneradorListasService::DEFAULT_COLUMNAS;
    public array $cabeceras = [];
    public string $observacion_general = '';
    public array $observaciones = [];
    public bool $todos_filtrados = true;
    public array $alumno_ids = [];
    public int $pagina = 1;
    public int $total = 0;
    public array $vista_previa = [];
    public bool $consultado = false;
    public string $buscar_campo = '';
    public string $nombre_plantilla = '';
    public string $plantilla_id = '';
    public string $mensaje = '';

    private const POR_PAGINA = 20;
    private const CONFIGURABLES = [
        'licenciatura_ids', 'generacion_ids', 'cuatrimestre_ids', 'modalidad_id',
        'procedencia', 'estado', 'ciclo_escolar', 'modalidad_texto', 'agrupar',
        'orden', 'orientacion', 'titulo', 'subtitulo', 'columnas', 'cabeceras', 'observacion_general',
    ];

    public function filtros(): array
    {
        return [
            'licenciatura_ids' => $this->licenciatura_ids,
            'generacion_ids' => $this->generacion_ids,
            'cuatrimestre_ids' => $this->cuatrimestre_ids,
            'modalidad_id' => $this->modalidad_id ?: null,
            'procedencia' => $this->procedencia,
            'estado' => $this->estado,
            'ciclo_escolar' => $this->ciclo_escolar ?: null,
            'busqueda' => $this->busqueda,
            'todos_filtrados' => true,
        ];
    }

    public function consultar(): void
    {
        $this->validate([
            'busqueda' => 'nullable|string|max:120',
            'ciclo_escolar' => 'nullable|string|max:50',
            'columnas' => 'required|array|min:1',
            'licenciatura_ids' => 'array', 'generacion_ids' => 'array', 'cuatrimestre_ids' => 'array',
        ]);
        $servicio = app(GeneradorListasService::class);
        $this->total = $servicio->consulta($this->filtros())->count();
        $this->pagina = max(1, min($this->pagina, max(1, (int) ceil($this->total / self::POR_PAGINA))));
        $this->vista_previa = $servicio->consulta($this->filtros())
            ->offset(($this->pagina - 1) * self::POR_PAGINA)->limit(self::POR_PAGINA)
            ->get()->map(fn ($a) => [
                'id' => $a->id, 'matricula' => $a->matricula ?: $a->matricula_interna,
                'nombre' => trim($a->apellido_paterno . ' ' . $a->apellido_materno . ' ' . $a->nombre),
                'generacion' => $a->generacion_nombre,
                'licenciatura' => $a->licenciatura_nombre, 'modalidad' => $a->modalidad_nombre,
            ])->all();
        $this->consultado = true;
        $this->mensaje = '';
    }

    public function updated($property): void
    {
        $base = explode('.', $property)[0];
        if (in_array($base, ['licenciatura_ids', 'generacion_ids', 'cuatrimestre_ids', 'modalidad_id', 'procedencia', 'estado', 'ciclo_escolar', 'busqueda'], true)) {
            $this->pagina = 1;
            $this->alumno_ids = [];
            $this->consultado = false;
        }
    }

    public function cambiarPagina(int $pagina): void
    {
        $this->pagina = max(1, $pagina);
        $this->consultar();
    }

    /** Diseños predeterminados que pueden personalizarse y guardarse. */
    public function aplicarPredeterminado(string $nombre): void
    {
        $formatos = [
            'grupo' => ['LISTA DE GRUPO', ['numero', 'matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'CURP', 'modalidad', 'observaciones']],
            'contactos' => ['DIRECTORIO ACADÉMICO', ['numero', 'matricula', 'nombre_completo', 'telefono', 'celular', 'municipio', 'modalidad']],
            'inscripcion' => ['LISTA DE INSCRIPCIÓN', ['numero', 'matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'CURP', 'generacion', 'cuatrimestre', 'modalidad']],
            'observaciones' => ['CONTROL DE OBSERVACIONES', ['numero', 'matricula', 'nombre_completo', 'observaciones']],
        ];
        if (! isset($formatos[$nombre])) return;
        [$this->titulo, $this->columnas] = $formatos[$nombre];
        $this->cabeceras = [];
        $this->mensaje = 'Formato base aplicado; puedes personalizarlo antes de descargar.';
    }

    public function moverColumna(int $indice, int $direccion): void
    {
        $otro = $indice + $direccion;
        if (! isset($this->columnas[$indice], $this->columnas[$otro])) return;
        [$this->columnas[$indice], $this->columnas[$otro]] = [$this->columnas[$otro], $this->columnas[$indice]];
    }

    public function seleccionarPagina(): void
    {
        $this->alumno_ids = array_values(array_unique(array_merge($this->alumno_ids, array_column($this->vista_previa, 'id'))));
        $this->todos_filtrados = false;
    }

    public function limpiarSeleccion(): void
    {
        $this->alumno_ids = [];
        $this->todos_filtrados = true;
    }

    public function guardarPlantilla(): void
    {
        $this->validate(['nombre_plantilla' => 'required|string|min:3|max:100', 'columnas' => 'required|array|min:1']);
        $config = [];
        foreach (self::CONFIGURABLES as $campo) $config[$campo] = $this->{$campo};
        DB::table('generador_listas_plantillas')->updateOrInsert(
            ['user_id' => auth()->id(), 'nombre' => trim($this->nombre_plantilla)],
            ['configuracion' => json_encode($config, JSON_UNESCAPED_UNICODE), 'updated_at' => now(), 'created_at' => now()]
        );
        $this->mensaje = 'Plantilla guardada correctamente.';
    }

    public function cargarPlantilla(): void
    {
        $plantilla = DB::table('generador_listas_plantillas')->where('user_id', auth()->id())
            ->where('id', $this->plantilla_id)->first();
        if (! $plantilla) return;
        $ajustes = json_decode($plantilla->configuracion, true) ?: [];
        foreach (self::CONFIGURABLES as $campo) {
            if (array_key_exists($campo, $ajustes)) $this->{$campo} = $ajustes[$campo];
        }
        // No confiar en campos obsoletos o personalizados de plantillas antiguas.
        $this->columnas = array_values(array_unique(array_intersect($this->columnas, array_keys(GeneradorListasService::CAMPOS))));
        if (! $this->columnas) $this->columnas = GeneradorListasService::DEFAULT_COLUMNAS;
        $this->alumno_ids = [];
        $this->todos_filtrados = true;
        $this->pagina = 1;
        $this->consultado = false;
        $this->mensaje = 'Plantilla cargada. Consulta para actualizar la vista previa.';
    }

    public function eliminarPlantilla(): void
    {
        DB::table('generador_listas_plantillas')->where('user_id', auth()->id())
            ->where('id', $this->plantilla_id)->delete();
        $this->plantilla_id = '';
        $this->mensaje = 'Plantilla eliminada.';
    }

    public function render()
    {
        return view('livewire.admin.documentacion.generador-listas', [
            'licenciaturas' => Licenciatura::query()
                ->where(fn ($query) => $query->whereHas('inscripciones')
                    ->orWhereIn('id', DB::table('historial_inscripciones')->whereNotNull('licenciatura_id')->select('licenciatura_id')))
                ->orderBy('nombre')->get(['id', 'nombre']),
            'generaciones' => Generacion::query()->orderBy('generacion')->get(['id', 'generacion']),
            'cuatrimestres' => Cuatrimestre::query()->orderBy('cuatrimestre')->get(['id', 'nombre_cuatrimestre']),
            'modalidades' => Modalidad::query()->orderBy('nombre')->get(['id', 'nombre']),
            'ciclos' => DB::table('historial_inscripciones')->whereNotNull('ciclo_escolar')->distinct()->orderByDesc('ciclo_escolar')->pluck('ciclo_escolar'),
            'plantillas' => DB::table('generador_listas_plantillas')->where('user_id', auth()->id())->orderBy('nombre')->get(['id', 'nombre']),
            'campos' => GeneradorListasService::CAMPOS,
            'descargas' => DB::table('generador_listas_descargas')->where('user_id', auth()->id())->orderByDesc('id')->limit(5)->get(),
        ]);
    }
}
