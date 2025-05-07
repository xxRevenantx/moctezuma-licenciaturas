<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'edad',
        'sexo',
        'estado_nacimiento',
        'ciudad_nacimiento',
        'calle',
        'numero_exterior',
        'colonia',
        'codigo_postal',
        'municipio',
        'ciudad',
        'estado',
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
        'otros',
        'foraneo',
        'status',

    ];


    public function user(){
        return $this->hasOne(User::class);
    }

    public function licenciatura(){
        return $this->hasOne(Licenciatura::class);
    }
    public function generacion(){
        return $this->hasOne(Generacion::class);
    }
    public function cuatrimestre(){
        return $this->hasOne(Cuatrimestre::class);
    }
    public function modalidad(){
        return $this->hasOne(Modalidad::class);
    }






}
