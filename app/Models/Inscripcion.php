<?php

namespace App\Models;

use App\Observers\InscripcionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


#[ObservedBy(InscripcionObserver::class)]
class Inscripcion extends Model
{
    protected $table = 'inscripciones';
    /** @use HasFactory<\Database\Factories\InscripcionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricula',
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
        'certificado',
        'acta_nacimiento',
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
    public function estadoNacimiento(){
        return $this->belongsTo(Estado::class, 'estado_nacimiento_id');
    }
    public function ciudadNacimiento(){
        return $this->belongsTo(Ciudad::class, 'ciudad_nacimiento_id');
    }
    public function ciudad(){
        return $this->belongsTo(Ciudad::class);
    }
    public function estado(){
        return $this->belongsTo(Estado::class);
    }







}
