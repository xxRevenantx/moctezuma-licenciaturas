<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Services\CurpService;

use App\Models\Accion;
use App\Models\AsignarGeneracion;
use App\Models\Generacion;
use App\Models\Inscripcion as ModelsInscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\Periodo;
use App\Models\User;
use Livewire\Component;
use Nnjeim\World\World;

class Inscripcion extends Component
{

    public $slug;
    public $modalidad;
    public $licenciatura;

    public $usuarios;

    public $generaciones ;
    public $cuatrimestres = [];


    public $usuario_id;
    public $matricula;
    public $folio;
    public $CURP;
    public $nombre;
    public $apellido_paterno;
    public $apellido_materno;
    public $fecha_nacimiento;
    public $edad;
    public $sexo;
    public $pais;
    public $estado_nacimiento_id;
    public $ciudad_nacimiento_id;
    public $calle;
    public $numero_exterior;
    public $numero_interior;
    public $colonia;
    public $codigo_postal;
    public $municipio;
    public $ciudad_id;
    public $estado_id;
    public $telefono;
    public $celular;
    public $tutor;
    public $bachillerato_procedente;
    public $licenciatura_id;
    public $generacion_id;
    public $cuatrimestre_id;
    public $modalidad_id;
    public $certificado;
    public $acta_nacimiento;
    public $certificado_medico;
    public $fotos_infantiles;
    public $foto;
    public $otros;
    public $foraneo;
    public $status;



    public function mount($licenciatura, $modalidad)
    {
        $this->licenciatura = Licenciatura::where('slug', $licenciatura)->firstOrFail();
        $this->modalidad = Modalidad::where('slug', $modalidad)->firstOrFail();

        $this->usuarios = User::role('Estudiante')->get();

        $this->generaciones = AsignarGeneracion::where('licenciatura_id', $this->licenciatura->id)
            ->where('modalidad_id', $this->modalidad->id)
            ->whereHas('generacion', function ($query) {
            $query->where('activa', "true");
            })
            ->get();
        // $this->generaciones = Generacion::all();
    }

    public function updated($propertyName)
    {
          // Validar solo si el campo tiene valor
        //   $this->validateOnly($propertyName);

            if ($propertyName === 'usuario_id') {
                $this->usuarios = User::role('Estudiante')->get();
            }
            if ($propertyName === 'generacion_id') {
                $this->cuatrimestres = Periodo::where('generacion_id', $this->generacion_id)
                ->limit(1)
                ->orderBy('id', 'desc')
                    ->get();

                // $generation = Generation::find($this->generation_id);
                // $this->generacion_nombre = $generation->anio_inicio . ' - ' . $generation->anio_termino;
            }

    }

    public function guardarEstudiante()
    {
        $this->validate([
            'usuario_id' => 'required|exists:users,id|unique:inscripciones,usuario_id',
            'matricula' => 'required|max:8|unique:inscripciones,matricula',
            'folio' => 'nullable|max:10|unique:inscripciones,folio',
            'CURP' => 'required|max:18|unique:inscripciones,CURP',
            'nombre' => 'required|max:50',
            'apellido_paterno' => 'required|max:50',
            'apellido_materno' => 'required|max:50',
            'fecha_nacimiento' => 'required|date',
            'edad' => 'required|integer',
            'sexo' => 'required|in:H,M',
            'pais' => 'nullable|max:100',
            'estado_nacimiento_id' => 'nullable|exists:estados,id',
            'ciudad_nacimiento_id' => 'nullable|exists:ciudades,id',
            'calle' => 'nullable|max:255',
            'numero_exterior' => 'nullable|max:10',
            'numero_interior' => 'nullable|max:10',
            'colonia' => 'nullable|max:255',
            'codigo_postal' => 'nullable|max:10',
            'municipio' => 'nullable|max:255',
            'ciudad_id' => 'nullable|exists:ciudades,id',
            'estado_id' => 'nullable|exists:estados,id',
            'telefono' => 'nullable|max:10',
            'celular' => 'nullable|max:10',
            'tutor' => 'nullable|max:255',
            'bachillerato_procedente' => 'nullable|max:255',
            'licenciatura_id' => 'required|exists:licenciaturas,id',
            'generacion_id' => 'required|exists:asignar_generaciones,id',
            'cuatrimestre_id' => 'required|exists:periodos,id',
            'modalidad_id' => 'required|exists:modalidades,id',
            'status' => 'required',


        ],[
            'usuario_id.required' => 'El usuario es obligatorio.',
            'usuario_id.exists' => 'El usuario seleccionado no existe.',
            'usuario_id.unique' => 'El usuario ya está en uso.',
            'matricula.required' => 'La matrícula es obligatorio.',
            'matricula.unique' => 'La matrícula ya está en uso.',
            'matricula.max' => 'La matrícula no debe exceder 8 caracteres.',
            'folio.max' => 'El folio no debe exceder 10 caracteres.',
            'folio.unique' => 'El folio ya está en uso.',
            'CURP.required' => 'El CURP es obligatorio.',
            'CURP.unique' => 'El CURP ya está en uso.',
            'CURP.max' => 'El CURP no debe exceder 18 caracteres.',
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder 50 caracteres.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'apellido_paterno.max' => 'El apellido paterno no debe exceder 50 caracteres.',
            'apellido_materno.required' => 'El apellido materno es obligatorio.',
            'apellido_materno.max' => 'El apellido materno no debe exceder 50 caracteres.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatorio.',
            'fecha_nacimiento.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'edad.required' => 'La edad es obligatorio.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'sexo.required' => 'El género es obligatorio.',
            'sexo.in' => 'El género debe ser H o M.',
            'pais.max' => 'El país no debe exceder 100 caracteres.',
            'estado_nacimiento_id.exists' => 'El estado de nacimiento seleccionado no existe.',
            'ciudad_nacimiento_id.exists' => 'La ciudad de nacimiento seleccionada no existe.',
            'calle.max' => 'La calle no debe exceder 255 caracteres.',
            'numero_exterior.max' => 'El número exterior no debe exceder 10 caracteres.',
            'numero_interior.max' => 'El número interior no debe exceder 10 caracteres.',
            'colonia.max' => 'La colonia no debe exceder 255 caracteres.',
            'codigo_postal.max' => 'El código postal no debe exceder 10 caracteres.',
            'municipio.max' => 'El municipio no debe exceder 255 caracteres.',
            'ciudad_id.exists' => 'La ciudad seleccionada no existe.',
            'estado_id.exists' => 'El estado seleccionado no existe.',
            'telefono.max' => 'El teléfono no debe exceder 10 caracteres.',
            'celular.max' => 'El celular no debe exceder 10 caracteres.',
            'tutor.max' => 'El tutor no debe exceder 255 caracteres.',
            'bachillerato_procedente.max' => 'El bachillerato procedente no debe exceder 255 caracteres.',
            'licenciatura_id.required' => 'La licenciatura es obligatoria.',
            'licenciatura_id.exists' => 'La licenciatura seleccionada no existe.',
            'generacion_id.required' => 'La generación es obligatoria.',
            'generacion_id.exists' => 'La generación seleccionada no existe.',
            'cuatrimestre_id.required' => 'El cuatrimestre es obligatorio.',
            'cuatrimestre_id.exists' => 'El cuatrimestre seleccionado no existe.',
            'modalidad_id.required' => 'La modalidad es obligatoria.',
            'modalidad_id.exists' => 'La modalidad seleccionada no existe.',
            'status.required' => 'El estado es obligatorio.',





        ]);

        // Aquí puedes guardar la información del estudiante en la base de datos
       ModelsInscripcion::create([
            'user_id' => $this->usuario_id,
            'matricula' => $this->matricula,
            'folio' => $this->folio,
            'CURP' => $this->CURP,
            'nombre' => $this->nombre,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'edad' => $this->edad,
            'sexo' => $this->sexo,
            'pais' => $this->pais,
            'estado_nacimiento_id' => $this->estado_nacimiento_id,
            'ciudad_nacimiento_id' => $this->ciudad_nacimiento_id,
            'calle' => $this->calle,
            'numero_exterior' => $this->numero_exterior,
            'numero_interior' => $this->numero_interior,
            'colonia' => $this->colonia,
            'codigo_postal' => $this->codigo_postal,
            'municipio' => $this->municipio,
            'ciudad_id' => $this->ciudad_id,
            'estado_id' => $this->estado_id,
            'telefono' => $this->telefono,
            'celular' => $this->celular,
            'tutor' => $this->tutor,
            'bachillerato_procedente' => $this->bachillerato_procedente,
            'licenciatura_id' => $this->licenciatura_id,
            'generacion_id' => $this->generacion_id,
            'cuatrimestre_id' => $this->cuatrimestre_id,
            'modalidad_id' => $this->modalidad_id,
        ]);

        // Limpiar los campos después de guardar
        $this->reset([
            'usuario_id',
            'matricula',
            'folio',
            'CURP',
            'nombre',
            'apellido_paterno',
            'apellido_materno',
            'fecha_nacimiento',
            'edad',
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
        ]);
        // Mostrar un mensaje de éxito
        $this->dispatch('swal', [
            'title' => '¡Nuevo alumno creado correctamente!',
            'icon' => 'success',
            'position' => 'top-end',
        ]);




    }

    public function render()
    {


        $acciones = Accion::all();
        return view('livewire.admin.licenciaturas.submodulo.inscripcion', compact('acciones'));
    }
}
