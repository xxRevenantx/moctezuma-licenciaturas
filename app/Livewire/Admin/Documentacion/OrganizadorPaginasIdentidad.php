<?php

namespace App\Livewire\Admin\Documentacion;

use App\Models\Inscripcion;
use App\Services\DocumentosIdentidad\DocumentoIdentidadService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class OrganizadorPaginasIdentidad extends Component
{
    public bool $abierto = false;

    public int $inscripcionId;

    public ?int $organizacionId = null;

    public ?int $fuenteActivaId = null;

    public array $fuentes = [];

    public array $paginas = [];

    public array $tipos = [];

    public array $rangos = [];

    public array $historial = [];

    public int $paginasSinClasificar = 0;

    public string $mensaje = '';

    public function mount(int $inscripcionId): void
    {
        $this->inscripcionId = $inscripcionId;
        $this->tipos = collect(config('documentos_identidad.types', []))
            ->map(fn (array $config, string $tipo): array => [
                'tipo' => $tipo,
                'label' => $config['label'],
                'obligatorio' => (bool) $config['required'],
            ])
            ->values()
            ->all();
        $this->reiniciarRangos();
    }

    #[On('abrir-organizador-identidad')]
    public function abrir(int $inscripcionId, ?int $fuenteId = null): void
    {
        if ($inscripcionId !== $this->inscripcionId) {
            return;
        }

        $this->autorizarOrganizacion();
        $this->resetErrorBag();
        $this->mensaje = '';
        $this->cargarDatos($fuenteId);
        $this->abierto = true;
    }

    public function cerrar(): void
    {
        $this->abierto = false;
        $this->mensaje = '';
        $this->resetErrorBag();
    }

    public function seleccionarFuente(int $fuenteId): void
    {
        if (! collect($this->fuentes)->contains('id', $fuenteId)) {
            return;
        }

        $this->fuenteActivaId = $fuenteId;
        $this->actualizarRangosDesdeFuente();
    }

    public function actualizarTipo(string $clave, ?string $tipo): void
    {
        $this->autorizarOrganizacion();
        $tipo = $tipo === '' ? null : $tipo;

        if ($tipo !== null && ! collect($this->tipos)->contains('tipo', $tipo)) {
            $this->addError('organizacion', 'El tipo documental seleccionado no es válido.');

            return;
        }

        $indice = $this->indicePagina($clave);
        if ($indice === null) {
            return;
        }

        $this->paginas[$indice]['tipo'] = $tipo;
        $this->paginas[$indice]['orden'] = $tipo
            ? $this->siguienteOrden($tipo, $clave)
            : 0;

        $this->persistirBorrador('Asignación actualizada.');
    }

    public function rotarPagina(string $clave, int $incremento): void
    {
        $this->autorizarOrganizacion();
        $indice = $this->indicePagina($clave);
        if ($indice === null) {
            return;
        }

        $actual = (int) ($this->paginas[$indice]['rotacion'] ?? 0);
        $this->paginas[$indice]['rotacion'] = (($actual + $incremento) % 360 + 360) % 360;
        $this->persistirBorrador('Rotación guardada.');
    }

    public function moverPagina(string $clave, string $direccion): void
    {
        $this->autorizarOrganizacion();
        $indice = $this->indicePagina($clave);
        if ($indice === null) {
            return;
        }

        $tipo = $this->paginas[$indice]['tipo'] ?? null;
        if (! $tipo) {
            return;
        }

        $ordenadas = collect($this->paginas)
            ->where('tipo', $tipo)
            ->sortBy('orden')
            ->values();
        $posicion = $ordenadas->search(fn (array $pagina): bool => $pagina['clave'] === $clave);
        $destino = $direccion === 'arriba' ? $posicion - 1 : $posicion + 1;

        if ($posicion === false || ! isset($ordenadas[$destino])) {
            return;
        }

        $claveDestino = $ordenadas[$destino]['clave'];
        $this->intercambiarOrdenes($clave, $claveDestino);
        $this->persistirBorrador('Orden actualizado.');
    }

    public function reordenarPagina(?string $claveOrigen, string $claveDestino): void
    {
        $this->autorizarOrganizacion();

        if (! $claveOrigen || $claveOrigen === $claveDestino) {
            return;
        }

        $origen = $this->paginaPorClave($claveOrigen);
        $destino = $this->paginaPorClave($claveDestino);

        if (! $origen || ! $destino || ! $origen['tipo'] || $origen['tipo'] !== $destino['tipo']) {
            return;
        }

        $tipo = $origen['tipo'];
        $claves = collect($this->paginas)
            ->where('tipo', $tipo)
            ->sortBy('orden')
            ->pluck('clave')
            ->values()
            ->all();
        $claves = array_values(array_filter($claves, fn (string $clave): bool => $clave !== $claveOrigen));
        $posicionDestino = array_search($claveDestino, $claves, true);

        if ($posicionDestino === false) {
            return;
        }

        array_splice($claves, $posicionDestino, 0, [$claveOrigen]);

        foreach ($claves as $orden => $clave) {
            $indice = $this->indicePagina($clave);
            if ($indice !== null) {
                $this->paginas[$indice]['orden'] = $orden + 1;
            }
        }

        $this->persistirBorrador('Orden actualizado mediante arrastre.');
    }

    public function aplicarRangos(): void
    {
        $this->autorizarOrganizacion();
        $fuente = collect($this->fuentes)->firstWhere('id', $this->fuenteActivaId);

        if (! $fuente) {
            $this->addError('rangos', 'Selecciona un archivo fuente.');

            return;
        }

        try {
            $asignadas = [];
            $porTipo = [];

            foreach ($this->tipos as $tipoConfig) {
                $tipo = $tipoConfig['tipo'];
                $paginas = $this->interpretarRango((string) ($this->rangos[$tipo] ?? ''), (int) $fuente['paginas']);

                foreach ($paginas as $pagina) {
                    if (isset($asignadas[$pagina])) {
                        throw ValidationException::withMessages([
                            'rangos' => "La página {$pagina} está repetida en {$asignadas[$pagina]} y {$tipoConfig['label']}.",
                        ]);
                    }

                    $asignadas[$pagina] = $tipoConfig['label'];
                }

                $porTipo[$tipo] = $paginas;
            }

            // Los rangos representan la clasificación completa del archivo activo.
            // Primero libera sus páginas para que dejar un campo vacío realmente
            // las coloque en “Sin clasificar”, en lugar de conservar asignaciones previas.
            foreach ($this->paginas as $indice => $paginaActual) {
                if ((int) $paginaActual['fuente_id'] !== (int) $this->fuenteActivaId) {
                    continue;
                }

                $this->paginas[$indice]['tipo'] = null;
                $this->paginas[$indice]['orden'] = 0;
            }

            foreach ($porTipo as $tipo => $paginas) {
                $orden = $this->siguienteOrden($tipo);
                foreach ($paginas as $pagina) {
                    $clave = $this->fuenteActivaId . ':' . $pagina;
                    $indice = $this->indicePagina($clave);
                    if ($indice !== null) {
                        $this->paginas[$indice]['tipo'] = $tipo;
                        $this->paginas[$indice]['orden'] = $orden++;
                    }
                }
            }

            $this->persistirBorrador('Rangos aplicados correctamente.');
            $this->actualizarRangosDesdeFuente();
        } catch (ValidationException $e) {
            $this->addError('rangos', $e->validator->errors()->first());
        }
    }

    public function confirmar(): void
    {
        $this->autorizarOrganizacion();
        $this->resetErrorBag();

        try {
            $alumno = Inscripcion::query()->findOrFail($this->inscripcionId);
            $service = app(DocumentoIdentidadService::class);
            $borrador = $service->guardarBorrador(
                $alumno,
                $this->asignacionesParaGuardar(),
                auth()->id(),
                $this->organizacionId
            );
            $service->confirmarOrganizacion($alumno, $borrador->id, auth()->id());

            $this->abierto = false;
            $this->mensaje = '';
            $this->dispatch('organizacion-identidad-confirmada', inscripcionId: $this->inscripcionId);
            $this->dispatch('documento-identidad-actualizado', inscripcionId: $this->inscripcionId);
            $this->dispatch(
                'swal',
                title: 'Organización confirmada',
                text: 'Los documentos se generaron con las páginas y el orden seleccionados.',
                icon: 'success',
                position: 'top'
            );
        } catch (ValidationException $e) {
            $this->addError('organizacion', $e->validator->errors()->first());
        } catch (Throwable $e) {
            report($e);
            $this->addError('organizacion', $e->getMessage());
        }
    }

    protected function cargarDatos(?int $fuentePreferida = null): void
    {
        $alumno = Inscripcion::query()->findOrFail($this->inscripcionId);
        $datos = app(DocumentoIdentidadService::class)->datosOrganizador($alumno, auth()->id());
        $this->organizacionId = $datos['organizacion']->id;
        $this->fuentes = $datos['fuentes']->map(fn ($fuente): array => [
            'id' => $fuente->id,
            'nombre' => $fuente->nombre_original,
            'paginas' => $fuente->paginas,
            'mime' => $fuente->mime_original,
            'fecha' => optional($fuente->created_at)->format('d/m/Y H:i'),
            'original_url' => route('admin.documentos-identidad.fuentes.descargar', $fuente),
        ])->values()->all();
        $this->paginas = collect($datos['paginas'])->map(function (array $pagina): array {
            $pagina['preview_url'] = route('admin.documentos-identidad.fuentes.pagina', [
                'fuente' => $pagina['fuente_id'],
                'pagina' => $pagina['pagina'],
                'rotacion' => $pagina['rotacion'] ?? 0,
            ]);

            return $pagina;
        })->values()->all();
        $this->fuenteActivaId = collect($this->fuentes)->contains('id', $fuentePreferida)
            ? $fuentePreferida
            : ($this->fuenteActivaId && collect($this->fuentes)->contains('id', $this->fuenteActivaId)
                ? $this->fuenteActivaId
                : (collect($this->fuentes)->first()['id'] ?? null));
        $this->paginasSinClasificar = collect($this->paginas)->whereNull('tipo')->count();
        $this->historial = $alumno->organizacionesDocumentosIdentidad()
            ->where('estado', 'confirmado')
            ->latest('version')
            ->limit(6)
            ->get()
            ->map(fn ($item): array => [
                'version' => $item->version,
                'fecha' => optional($item->confirmado_at)->format('d/m/Y H:i'),
                'usuario' => $item->usuarioConfirmacion?->name ?? 'Sistema',
                'paginas' => collect($item->asignaciones ?? [])->whereNotNull('tipo')->count(),
                'sin_clasificar' => collect($item->asignaciones ?? [])->whereNull('tipo')->count(),
            ])
            ->all();
        $this->actualizarRangosDesdeFuente();
    }

    protected function persistirBorrador(string $mensaje): void
    {
        $this->resetErrorBag();

        try {
            $alumno = Inscripcion::query()->findOrFail($this->inscripcionId);
            $organizacion = app(DocumentoIdentidadService::class)->guardarBorrador(
                $alumno,
                $this->asignacionesParaGuardar(),
                auth()->id(),
                $this->organizacionId
            );
            $this->organizacionId = $organizacion->id;
            $this->sincronizarAsignaciones($organizacion->asignaciones ?? []);
            $this->paginasSinClasificar = collect($this->paginas)->whereNull('tipo')->count();
            $this->actualizarRangosDesdeFuente();
            $this->mensaje = $mensaje;
            $this->dispatch('organizacion-identidad-borrador-actualizado', inscripcionId: $this->inscripcionId);
        } catch (ValidationException $e) {
            $this->addError('organizacion', $e->validator->errors()->first());
        } catch (Throwable $e) {
            report($e);
            $this->addError('organizacion', $e->getMessage());
        }
    }

    protected function sincronizarAsignaciones(array $asignaciones): void
    {
        $mapa = collect($asignaciones)->keyBy(
            fn (array $item): string => $item['fuente_id'] . ':' . $item['pagina']
        );

        foreach ($this->paginas as $indice => $pagina) {
            $actualizada = $mapa->get($pagina['clave']);
            if (! $actualizada) {
                continue;
            }

            $this->paginas[$indice] = array_merge($pagina, $actualizada, [
                'preview_url' => route('admin.documentos-identidad.fuentes.pagina', [
                    'fuente' => $actualizada['fuente_id'],
                    'pagina' => $actualizada['pagina'],
                    'rotacion' => $actualizada['rotacion'] ?? 0,
                ]),
            ]);
        }
    }

    protected function asignacionesParaGuardar(): array
    {
        return collect($this->paginas)->map(fn (array $pagina): array => [
            'fuente_id' => (int) $pagina['fuente_id'],
            'pagina' => (int) $pagina['pagina'],
            'tipo' => $pagina['tipo'] ?: null,
            'orden' => (int) ($pagina['orden'] ?? 0),
            'rotacion' => (int) ($pagina['rotacion'] ?? 0),
        ])->values()->all();
    }

    protected function indicePagina(string $clave): ?int
    {
        foreach ($this->paginas as $indice => $pagina) {
            if ($pagina['clave'] === $clave) {
                return $indice;
            }
        }

        return null;
    }

    protected function paginaPorClave(string $clave): ?array
    {
        $indice = $this->indicePagina($clave);

        return $indice === null ? null : $this->paginas[$indice];
    }

    protected function siguienteOrden(string $tipo, ?string $excluirClave = null): int
    {
        return ((int) collect($this->paginas)
            ->filter(fn (array $pagina): bool => $pagina['tipo'] === $tipo && $pagina['clave'] !== $excluirClave)
            ->max('orden')) + 1;
    }

    protected function intercambiarOrdenes(string $primeraClave, string $segundaClave): void
    {
        $primera = $this->indicePagina($primeraClave);
        $segunda = $this->indicePagina($segundaClave);

        if ($primera === null || $segunda === null) {
            return;
        }

        $orden = $this->paginas[$primera]['orden'];
        $this->paginas[$primera]['orden'] = $this->paginas[$segunda]['orden'];
        $this->paginas[$segunda]['orden'] = $orden;
    }

    protected function interpretarRango(string $rango, int $maximo): array
    {
        $rango = trim($rango);
        if ($rango === '') {
            return [];
        }

        $resultado = [];
        foreach (preg_split('/\s*[,;]\s*/', $rango) ?: [] as $segmento) {
            if ($segmento === '') {
                continue;
            }

            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $segmento, $coincidencias)) {
                $inicio = (int) $coincidencias[1];
                $fin = (int) $coincidencias[2];
                if ($inicio > $fin) {
                    [$inicio, $fin] = [$fin, $inicio];
                }

                for ($pagina = $inicio; $pagina <= $fin; $pagina++) {
                    $resultado[] = $pagina;
                }
            } elseif (ctype_digit($segmento)) {
                $resultado[] = (int) $segmento;
            } else {
                throw ValidationException::withMessages([
                    'rangos' => "El rango «{$segmento}» no es válido. Usa ejemplos como 1-2,4,6.",
                ]);
            }
        }

        $resultado = array_values(array_unique($resultado));
        foreach ($resultado as $pagina) {
            if ($pagina < 1 || $pagina > $maximo) {
                throw ValidationException::withMessages([
                    'rangos' => "La página {$pagina} no existe en el archivo seleccionado.",
                ]);
            }
        }

        sort($resultado);

        return $resultado;
    }

    protected function reiniciarRangos(): void
    {
        $this->rangos = collect($this->tipos)->mapWithKeys(
            fn (array $tipo): array => [$tipo['tipo'] => '']
        )->all();
    }

    protected function actualizarRangosDesdeFuente(): void
    {
        $this->reiniciarRangos();

        if (! $this->fuenteActivaId) {
            return;
        }

        foreach ($this->tipos as $tipoConfig) {
            $paginas = collect($this->paginas)
                ->filter(fn (array $pagina): bool =>
                    (int) $pagina['fuente_id'] === (int) $this->fuenteActivaId
                    && ($pagina['tipo'] ?? null) === $tipoConfig['tipo']
                )
                ->pluck('pagina')
                ->map(fn ($pagina): int => (int) $pagina)
                ->sort()
                ->values()
                ->all();

            $this->rangos[$tipoConfig['tipo']] = $this->compactarRango($paginas);
        }
    }

    protected function compactarRango(array $paginas): string
    {
        if ($paginas === []) {
            return '';
        }

        $segmentos = [];
        $inicio = $anterior = (int) array_shift($paginas);

        foreach ($paginas as $pagina) {
            $pagina = (int) $pagina;
            if ($pagina === $anterior + 1) {
                $anterior = $pagina;
                continue;
            }

            $segmentos[] = $inicio === $anterior ? (string) $inicio : "{$inicio}-{$anterior}";
            $inicio = $anterior = $pagina;
        }

        $segmentos[] = $inicio === $anterior ? (string) $inicio : "{$inicio}-{$anterior}";

        return implode(',', $segmentos);
    }

    protected function autorizarOrganizacion(): void
    {
        abort_unless(
            Gate::allows('documentos-identidad.reemplazar') || Gate::allows('documentos-identidad.subir'),
            403
        );
    }

    public function render()
    {
        return view('livewire.admin.documentacion.organizador-paginas-identidad');
    }
}
