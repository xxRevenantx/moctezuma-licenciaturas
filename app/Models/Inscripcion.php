<?php

namespace App\Models;

use App\Observers\InscripcionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Throwable;


#[ObservedBy(InscripcionObserver::class)]
class Inscripcion extends Model
{
    protected $table = 'inscripciones';

    /**
     * Mantiene en memoria los mismos valores por defecto definidos en MySQL.
     * Esto es importante porque el evento `created` se ejecuta con la instancia
     * de Eloquent actual y los defaults aplicados por la BD no se recargan solos.
     */
    protected $attributes = [
        'foraneo' => 'false',
        'status' => 'true',
        'egresado' => 'false',
    ];
    /** @use HasFactory<\Database\Factories\InscripcionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricula',
        'matricula_interna',
        'folio',
        'CURP',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'pais',
        'estado_nacimiento_id',
        'ciudad_nacimiento_id',
        'calle',
        'numero_exterior',
        'numero_interior',
        'colonia',
        'codigo_postal',
        'municipio',
        'ciudad_id',
        'estado_id',
        'telefono',
        'celular',
        'tutor',

        'bachillerato_procedente',
        'licenciatura_id',
        'generacion_id',
        'cuatrimestre_id',
        'modalidad_id',

        'ine',
        'CURP_documento',
        'certificado_estudios',
        'acta_nacimiento',
        'comprobante_domicilio',
        'certificado_medico',
        'fotos_infantiles',

        'foto',
        'otros',
        'foraneo',
        'status',
        'fecha_baja',
        'egresado',
        'orden'

    ];


    /**
     * Ruta normalizada de la fotografía institucional del estudiante.
     *
     * Licenciaturas conserva históricamente `foto` como nombre de archivo,
     * aunque algunas instalaciones antiguas guardaron una ruta relativa.
     */
    public function getFotoRutaAttribute(): ?string
    {
        $ubicacion = $this->resolverFotoAlmacenada();

        return $ubicacion['path'] ?? $this->fotoRutasCandidatas()[0] ?? null;
    }

    public function getFotoExisteAttribute(): bool
    {
        return $this->resolverFotoAlmacenada() !== null;
    }

    /**
     * La vista no depende de APP_URL ni de public/storage.
     * La fotografía se sirve mediante una ruta autenticada del sistema, por lo
     * que también funciona cuando el hosting no permite crear el storage:link.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->foto_existe || ! $this->getKey()) {
            return null;
        }

        return route('admin.fotografias.estudiante', [
            'inscripcion' => $this->getKey(),
            'v' => substr(sha1((string) $this->foto . '|' . (string) $this->updated_at), 0, 12),
        ], false);
    }

    public function getFotoDataUriAttribute(): ?string
    {
        $archivo = $this->fotoArchivo();

        if (! $archivo) {
            return null;
        }

        return 'data:' . $archivo['mime'] . ';base64,' . base64_encode($archivo['content']);
    }

    /**
     * Retorna el contenido real de la fotografía para navegador, PDF y Word.
     *
     * @return array{content:string,mime:string,path:string,disk:string}|null
     */
    public function fotoArchivo(): ?array
    {
        $ubicacion = $this->resolverFotoAlmacenada();

        if (! $ubicacion) {
            return null;
        }

        try {
            $storage = Storage::disk($ubicacion['disk']);
            $contenido = $storage->get($ubicacion['path']);
            $mime = $this->fotoMime($ubicacion['path']);

            try {
                $detectado = $storage->mimeType($ubicacion['path']);
                if (is_string($detectado) && str_starts_with($detectado, 'image/')) {
                    $mime = $detectado;
                }
            } catch (Throwable) {
                // La extensión es suficiente como fallback.
            }

            return [
                'content' => $contenido,
                'mime' => $mime,
                'path' => $ubicacion['path'],
                'disk' => $ubicacion['disk'],
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{disk:string,path:string}|null */
    private function resolverFotoAlmacenada(): ?array
    {
        foreach ($this->discosFotoCandidatos() as $disco) {
            try {
                $storage = Storage::disk($disco);

                foreach ($this->fotoRutasCandidatas() as $ruta) {
                    if ($storage->exists($ruta)) {
                        return ['disk' => $disco, 'path' => $ruta];
                    }
                }
            } catch (Throwable) {
                // Se prueba la siguiente combinación disco/ruta.
            }
        }

        return null;
    }

    /** @return array<int, string> */
    private function fotoRutasCandidatas(): array
    {
        if (blank($this->foto)) {
            return [];
        }

        $foto = str_replace('\\', '/', trim((string) $this->foto));
        $foto = ltrim($foto, '/');
        $foto = preg_replace('#^(?:public/|storage/)#i', '', $foto) ?: $foto;
        $nombre = basename($foto);

        $rutas = [$foto];

        // Estructura histórica de Moctezuma Licenciaturas.
        $rutas[] = 'estudiantes/' . $nombre;

        // Compatibilidad con la estructura institucional usada en Básica.
        $rutas[] = 'inscripciones/fotos/' . $nombre;

        return array_values(array_unique(array_filter($rutas)));
    }

    /** @return array<int, string> */
    private function discosFotoCandidatos(): array
    {
        return array_values(array_unique(array_filter([
            (string) config('filesystems.fotos_disk', 'public'),
            'public',
            (string) config('filesystems.default', 'public'),
        ])));
    }

    private function fotoMime(string $ruta): string
    {
        return match (strtolower(pathinfo($ruta, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'application/octet-stream',
        };
    }


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function licenciatura(){
          return $this->belongsTo(Licenciatura::class);
    }
    public function generacion(){
        return $this->belongsTo(Generacion::class);
    }
    public function cuatrimestre(){
        return $this->belongsTo(Cuatrimestre::class);
    }
    public function modalidad(){
        return $this->belongsTo(Modalidad::class);
    }


     public function ciudadNacimiento()
        {
            return $this->belongsTo(Ciudad::class, 'ciudad_nacimiento_id');
        }

         public function estadoNacimiento()
        {
            return $this->belongsTo(Estado::class, 'estado_nacimiento_id');
        }

        public function ciudad()
        {
            return $this->belongsTo(Ciudad::class, 'ciudad_id');
        }

        public function estado()
        {
            return $this->belongsTo(Estado::class, 'estado_id');
        }




    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'alumno_id');
    }

    public function constancias()
    {
        return $this->hasMany(Constancia::class, 'alumno_id');
    }

    public function justificantes()
    {
        return $this->hasMany(Justificante::class, 'alumno_id');
    }

    public function historial()
    {
        return $this->hasMany(HistorialInscripcion::class);
    }


    public function matriculaBitacoras()
    {
        return $this->hasMany(MatriculaBitacora::class);
    }

    public function movimientosAcademicos()
    {
        return $this->hasMany(MovimientoAcademico::class);
    }

    public function documentosIdentidad()
    {
        return $this->hasMany(DocumentoIdentidad::class);
    }

    public function documentosIdentidadActuales()
    {
        return $this->hasMany(DocumentoIdentidad::class)->actual();
    }

    public function fuentesDocumentosIdentidad()
    {
        return $this->hasMany(DocumentoIdentidadFuente::class);
    }

    public function organizacionesDocumentosIdentidad()
    {
        return $this->hasMany(OrganizacionDocumentoIdentidad::class);
    }


}
