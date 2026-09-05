<?php

namespace App\Livewire\Admin\Licenciaturas\Submodulo;

use App\Models\Ciudad;
use App\Models\Cuatrimestre;
use App\Models\Estado;
use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Models\Licenciatura;
use App\Models\Modalidad;
use App\Models\User;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class MatriculaEditar extends Component
{
    use \Livewire\WithFileUploads;

    public $open = false;
    public $estudianteId;



    public $user_id;
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
    public $email;
    public $bachillerato_procedente;
    public $licenciatura_id;
    public $generacion_id;
    public $cuatrimestre_id;
    public $modalidad_id;
    public $CURP_documento;
    public $certificado_estudios;
    public $acta_nacimiento;
    public $comprobante_domicilio;
    public $certificado_medico;
    public $fotos_infantiles;
    public $foto;
    public $foto_nueva;
    public $otros;
    public $foraneo;
    public $status;
    public $fecha_baja;

    public $fotoUrl;


    public $certificado_estudios_archivo;
    public $ruta_certificado_estudios; // Para mostrar en el modal

    public bool $permitirMovimientoAcademico = false;
    public bool $eliminacionOpen = false;
    public string $confirmacionEliminar = '';
    public array $impactoEliminar = [];

      // Método para abrir el modal con datos
      #[On('abrirEstudiante')]
      public function abrirModal($id)
      {

            $estudiante = Inscripcion::findOrFail($id);
            $this->estudianteId = $estudiante->id;

            if ($estudiante->CURP && strlen($estudiante->CURP) === 18) {
                $fecha = substr($estudiante->CURP, 4, 6); // AAMMDD

                $anio = substr($fecha, 0, 2);
                $mes = substr($fecha, 2, 2);
                $dia = substr($fecha, 4, 2);

                $anio_completo = intval($anio) < 30 ? "20$anio" : "19$anio";

                $fecha_nacimiento = "$anio_completo-$mes-$dia";
                $this->fecha_nacimiento = $fecha_nacimiento;

                // Calcular edad
                try {
                    $nacimiento = new \DateTime($fecha_nacimiento);
                    $hoy = new \DateTime();
                    $edad = $hoy->diff($nacimiento)->y;
                    $this->edad = $edad;
                } catch (\Exception $e) {
                    $this->edad = null; // Si hay error en fecha
                }
            }

            $this->user_id = $estudiante->user_id;
            $this->matricula = $estudiante->matricula;
            $this->folio = $estudiante->folio;
            $this->CURP = $estudiante->CURP;
            $this->nombre = $estudiante->nombre;
            $this->apellido_paterno = $estudiante->apellido_paterno;
            $this->apellido_materno = $estudiante->apellido_materno;
            $this->fecha_nacimiento = $estudiante->fecha_nacimiento;
            $this->sexo = $estudiante->sexo;
            $this->estado_nacimiento_id = $estudiante->estado_nacimiento_id;
            $this->ciudad_nacimiento_id = $estudiante->ciudad_nacimiento_id;
            $this->calle = $estudiante->calle;
            $this->numero_exterior = $estudiante->numero_exterior;
            $this->numero_interior = $estudiante->numero_interior;
            $this->colonia = $estudiante->colonia;
            $this->codigo_postal = $estudiante->codigo_postal;
            $this->municipio = $estudiante->municipio;
            $this->ciudad_id = $estudiante->ciudad_id;
            $this->estado_id = $estudiante->estado_id;
            $this->telefono = $estudiante->telefono;
            $this->celular = $estudiante->celular;
            $this->tutor = $estudiante->tutor;
            $this->email = $estudiante->user->email;
            $this->bachillerato_procedente = $estudiante->bachillerato_procedente;
            $this->licenciatura_id = $estudiante->licenciatura_id;
            $this->generacion_id = $estudiante->generacion_id;
            $this->cuatrimestre_id = $estudiante->cuatrimestre_id;
            $this->modalidad_id = $estudiante->modalidad_id;
            $this->foto = $estudiante->foto;
            $this->foraneo = $estudiante->foraneo == "true" ? true : false;
            $this->pais = $estudiante->pais;
            $this->status = $estudiante->status == "true" ? true : false;
            $this->fecha_baja = $estudiante->fecha_baja;
            $this->eliminacionOpen = false;
            $this->confirmacionEliminar = '';
            $this->impactoEliminar = [];

            $this->open = true;




      }


      public function updated($propertyName)
    {
          // Validar solo si el campo tiene valor
        //   $this->validateOnly($propertyName);

            if ($propertyName === 'user_id') {
            //   $this->usuarios = User::role('Estudiante')
            //     ->orderBy('id', 'desc')
            //     ->get();
            }
            if ($propertyName === 'generacion_id') {
                // $this->cuatrimestres = Periodo::where('generacion_id', $this->generacion_id)
                // ->limit(1)
                // ->orderBy('id', 'desc')
                // ->get();

            }
            if ($propertyName === 'CURP') {
                if (strlen($this->CURP) === 18) {
                    $fecha = substr($this->CURP, 4, 6); // AAMMDD

                    $anio = substr($fecha, 0, 2);
                    $mes = substr($fecha, 2, 2);
                    $dia = substr($fecha, 4, 2);

                    $anio_completo = intval($anio) < 30 ? "20$anio" : "19$anio";

                    $fecha_nacimiento = "$anio_completo-$mes-$dia";
                    $this->fecha_nacimiento = $fecha_nacimiento;

                    // Calcular edad
                    try {
                        $nacimiento = new \DateTime($fecha_nacimiento);
                        $hoy = new \DateTime();
                        $edad = $hoy->diff($nacimiento)->y;
                        $this->edad = $edad;
                    } catch (\Exception $e) {
                        $this->edad = null; // Si hay error en fecha
                    }
                } else {
                    // Resetear valores si el CURP está vacío o no tiene longitud válida
                    $this->fecha_nacimiento = null;
                    $this->edad = null;
                    }
            }

    }

     public function cerrarModal()
      {
          $this->reset(['open', 'estudianteId', 'matricula', 'folio', 'CURP', 'user_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'edad', 'sexo', 'estado_nacimiento_id', 'ciudad_nacimiento_id', 'calle', 'numero_exterior', 'numero_interior', 'colonia', 'codigo_postal', 'municipio', 'ciudad_id', 'estado_id', 'telefono', 'celular', 'tutor', 'bachillerato_procedente', 'generacion_id', 'cuatrimestre_id', 'CURP_documento', 'certificado_estudios', 'acta_nacimiento', 'comprobante_domicilio', 'certificado_medico', 'fotos_infantiles', 'fotoUrl','otros','foraneo','status']);
          $this->eliminacionOpen = false;
          $this->confirmacionEliminar = '';
          $this->impactoEliminar = [];
          $this->resetValidation();
      }

      public function updatedStatus($value): void
      {
          // No persistir el cambio al mover el switch. Se guarda únicamente
          // al confirmar el formulario, evitando bajas/reactivaciones accidentales.
          $this->fecha_baja = $value ? null : ($this->fecha_baja ?: now()->format('Y-m-d H:i:s'));
      }


      public function actualizarEstudiante(MatriculaService $matriculaService){
        $estudiante = Inscripcion::findOrFail($this->estudianteId);
        $matriculaAnterior = $matriculaService->normalizar($estudiante->matricula);
        $this->matricula = $matriculaService->normalizar($this->matricula);

        $this->validate([
            'user_id' => 'required|exists:users,id|unique:inscripciones,user_id,'.$this->estudianteId,
            'matricula' => [
                'required',
                'max:8',
                'unique:inscripciones,matricula,'.$this->estudianteId,
                function (string $attribute, mixed $value, \Closure $fail) use ($matriculaService, $matriculaAnterior): void {
                    $matricula = $matriculaService->normalizar((string) $value);

                    // Permite conservar temporalmente una matrícula heredada, pero toda corrección debe usar el formato institucional.
                    if ($matricula !== $matriculaAnterior && ! $matriculaService->esValida($matricula)) {
                        $fail('La matrícula debe contener cuatro letras y cuatro dígitos, por ejemplo: CAGL2001.');
                    }
                },
            ],
            'folio' => 'nullable|max:10|unique:inscripciones,folio,'.$this->estudianteId,
            'CURP' => 'required|max:18|unique:inscripciones,CURP,'.$this->estudianteId,
            'nombre' => 'required|max:50',
            'apellido_paterno' => 'required|max:50',
            'apellido_materno' => 'nullable|max:50',
            'fecha_nacimiento' => 'required|date',
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
            'foto_nueva' => 'nullable|image|max:2048|mimes:jpeg,jpg,png',
        ],[
            'user_id.required' => 'El campo usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'user_id.unique' => 'El usuario ya está asignado a otro estudiante.',
            'matricula.required' => 'El campo matrícula es obligatorio.',
            'matricula.unique' => 'La matrícula ya está registrada.',
            'folio.unique' => 'El folio ya está registrado.',
            'CURP.required' => 'El campo CURP es obligatorio.',
            'CURP.unique' => 'El CURP ya está registrado.',
            'nombre.required' => 'El campo nombre es obligatorio.',
            'apellido_paterno.required' => 'El campo apellido paterno es obligatorio.',

            'fecha_nacimiento.required' => 'El campo fecha de nacimiento es obligatorio.',
            'sexo.required' => 'El campo sexo es obligatorio.',
            'sexo.in' => 'El campo sexo debe ser H o M.',
            'estado_nacimiento_id.exists' => 'El estado de nacimiento seleccionado no existe.',
            'ciudad_nacimiento_id.exists' => 'La ciudad de nacimiento seleccionada no existe.',
            'ciudad_id.exists' => 'La ciudad seleccionada no existe.',
            'estado_id.exists' => 'El estado seleccionado no existe.',
            'telefono.max' => 'El campo teléfono no debe exceder 10 caracteres.',
            'celular.max' => 'El campo celular no debe exceder 10 caracteres.',
            'bachillerato_procedente.max' => 'El campo bachillerato procedente no debe exceder 255 caracteres.',
            'calle.max' => 'El campo calle no debe exceder 255 caracteres.',
            'numero_exterior.max' => 'El campo número exterior no debe exceder 10 caracteres.',
            'numero_interior.max' => 'El campo número interior no debe exceder 10 caracteres.',
            'colonia.max' => 'El campo colonia no debe exceder 255 caracteres.',
            'codigo_postal.max' => 'El campo código postal no debe exceder 10 caracteres.',
            'pais.max' => 'El campo país no debe exceder 100 caracteres.',
            'municipio.max' => 'El campo municipio no debe exceder 255 caracteres.',
            'ciudad_id.exists' => 'La ciudad seleccionada no existe.',
            'estado_id.exists' => 'El estado seleccionado no existe.',
            'tutor.max' => 'El campo tutor no debe exceder 255 caracteres.',
            'foto_nueva.image' => 'El archivo debe ser una imagen',
            'foto_nueva.max' => 'El archivo no debe pesar más de 2MB',
            'foto_nueva.mimes' => 'El archivo debe ser formato jpeg, jpg o png',


        ]);

         if ($this->foto_nueva) {
            // Eliminar la imagen anterior si existe
            if ($this->foto) {
            Storage::delete('estudiantes/' . $this->foto);
            }

            $foto = $this->foto_nueva->store('estudiantes');
            $datos['foto'] = str_replace('estudiantes/', '', $foto);
        }


        $matriculaNueva = $matriculaService->normalizar($this->matricula);
        $this->fecha_baja = $this->status ? null : ($this->fecha_baja ?: now()->format('Y-m-d H:i:s'));

        if ($estudiante) {
            $estudiante->update([
                'user_id' => $this->user_id,
                'matricula' => $matriculaNueva,
                'folio' => strtoupper(trim($this->folio)),
                'CURP' => strtoupper(trim($this->CURP)),
                'nombre' => strtoupper(trim($this->nombre)),
                'apellido_paterno' => strtoupper(trim($this->apellido_paterno)),
                'apellido_materno' => strtoupper(trim($this->apellido_materno)),
                'fecha_nacimiento' => $this->fecha_nacimiento,
                'sexo' => $this->sexo,
                'pais' => $this->pais,
                'estado_nacimiento_id' => $this->estado_nacimiento_id,
                'ciudad_nacimiento_id' => $this->ciudad_nacimiento_id,
                'calle' => strtoupper(trim($this->calle)),
                'numero_exterior' => trim($this->numero_exterior),
                'numero_interior' => trim($this->numero_interior),
                'colonia' => strtoupper(trim($this->colonia)),
                'codigo_postal' => strtoupper(trim($this->codigo_postal)),
                'municipio' => strtoupper(trim($this->municipio)),
                'ciudad_id' => $this->ciudad_id,
                'estado_id' => $this->estado_id,
                'telefono' => strtoupper(trim($this->telefono)),
                'celular' => strtoupper(trim($this->celular)),
                'tutor' => strtoupper(trim($this->tutor)),
                'bachillerato_procedente' => strtoupper(trim($this->bachillerato_procedente)),
                 'foto' => $this->foto_nueva ? $datos['foto'] : $this->foto,
                'foraneo' => $this->foraneo ? "true" : "false",
                'status' => $this->status ? "true" : "false",
                'fecha_baja' => $this->fecha_baja,


            ]);

            if ($matriculaAnterior !== $matriculaNueva) {
                $matriculaService->registrarCambio(
                    $estudiante,
                    'correccion_manual',
                    $matriculaAnterior !== '' ? $matriculaAnterior : null,
                    $matriculaNueva !== '' ? $matriculaNueva : null,
                    ['origen' => 'edicion_inscripcion']
                );
            }
        }


          $this->dispatch('refreshNavbar');
          $this->dispatch('refreshHeader');

        $this->reset(['open', 'estudianteId', 'matricula', 'folio', 'CURP', 'user_id', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'edad', 'sexo', 'estado_nacimiento_id', 'ciudad_nacimiento_id', 'calle', 'numero_exterior', 'numero_interior', 'colonia', 'codigo_postal', 'municipio', 'ciudad_id', 'estado_id', 'telefono', 'celular', 'tutor', 'bachillerato_procedente','licenciatura_id','generacion_id','cuatrimestre_id','modalidad_id','foto_nueva','foraneo','status', 'fecha_baja']);

         $this->dispatch('swal', [
              'title' => 'Estudiante actualizado correctamente!',
              'icon' => 'success',
              'position' => 'top-end',
          ]);

          $this->dispatch('refreshMatricula');

          $this->cerrarModal();

      }



    public function abrirMovimientoAcademico(): void
    {
        if (! $this->permitirMovimientoAcademico || ! $this->estudianteId) {
            return;
        }

        $id = (int) $this->estudianteId;
        $this->cerrarModal();
        $this->dispatch('solicitarMovimientoAcademico', estudianteId: $id);
    }

    public function prepararEliminacionPermanente(): void
    {
        if (! $this->permitirMovimientoAcademico || ! $this->estudianteId) {
            return;
        }

        $alumno = Inscripcion::query()->findOrFail($this->estudianteId);

        $this->impactoEliminar = [
            'calificaciones' => Schema::hasTable('calificaciones') ? DB::table('calificaciones')->where('alumno_id', $alumno->id)->count() : 0,
            'constancias' => Schema::hasTable('constancias') ? DB::table('constancias')->where('alumno_id', $alumno->id)->count() : 0,
            'justificantes' => Schema::hasTable('justificantes') ? DB::table('justificantes')->where('alumno_id', $alumno->id)->count() : 0,
            'titulos' => Schema::hasTable('titulos') ? DB::table('titulos')->where('alumno_id', $alumno->id)->count() : 0,
            'documentos_identidad' => Schema::hasTable('documentos_identidad') ? DB::table('documentos_identidad')->where('inscripcion_id', $alumno->id)->count() : 0,
            'fuentes_documentos' => Schema::hasTable('documentos_identidad_fuentes') ? DB::table('documentos_identidad_fuentes')->where('inscripcion_id', $alumno->id)->count() : 0,
            'organizaciones_documentos' => Schema::hasTable('organizaciones_documentos_identidad') ? DB::table('organizaciones_documentos_identidad')->where('inscripcion_id', $alumno->id)->count() : 0,
        ];

        $this->confirmacionEliminar = '';
        $this->eliminacionOpen = true;
    }

    public function cancelarEliminacionPermanente(): void
    {
        $this->eliminacionOpen = false;
        $this->confirmacionEliminar = '';
        $this->resetErrorBag('confirmacionEliminar');
    }

    public function eliminarPermanentemente(): void
    {
        if (! $this->permitirMovimientoAcademico || ! $this->estudianteId) {
            return;
        }

        $alumno = Inscripcion::query()->findOrFail($this->estudianteId);

        if (mb_strtoupper(trim($this->confirmacionEliminar), 'UTF-8') !== mb_strtoupper(trim((string) $alumno->matricula), 'UTF-8')) {
            $this->addError('confirmacionEliminar', 'Escribe exactamente la matrícula del alumno para confirmar la eliminación permanente.');
            return;
        }

        try {
            DB::transaction(function () use ($alumno): void {
                $alumno->delete();
            }, 3);
        } catch (Throwable $e) {
            report($e);
            $this->dispatch('swal', [
                'title' => 'No fue posible eliminar la inscripción. No se aplicaron cambios parciales.',
                'icon' => 'error',
                'position' => 'top-end',
            ]);
            return;
        }

        $this->eliminacionOpen = false;
        $this->open = false;
        $this->confirmacionEliminar = '';
        $this->impactoEliminar = [];

        $this->dispatch('refreshMatricula');
        $this->dispatch('refreshNavbar');
        $this->dispatch('refreshHeader');
        $this->dispatch('swal', [
            'title' => 'Inscripción eliminada permanentemente. Se conservaron las bitácoras configuradas con SET NULL.',
            'icon' => 'success',
            'position' => 'top-end',
        ]);
    }


    #[On('refreshMatricula')]
    public function render()
    {
        $estados = Estado::orderBy('nombre')->get();
        $ciudades = Ciudad::orderBy('nombre')->get();
        $usuarios = User::role('Estudiante')
            ->orderBy('id', 'desc')
            ->get();

        // El contexto académico es informativo en este modal. Los cambios de
        // modalidad/cuatrimestre se realizan únicamente mediante el flujo
        // transaccional de Movimiento académico.
        $licenciaturaActual = $this->licenciatura_id ? Licenciatura::query()->find($this->licenciatura_id) : null;
        $generacionActual = $this->generacion_id ? Generacion::query()->find($this->generacion_id) : null;
        $cuatrimestreActual = $this->cuatrimestre_id ? Cuatrimestre::query()->find($this->cuatrimestre_id) : null;
        $modalidadActual = $this->modalidad_id ? Modalidad::query()->find($this->modalidad_id) : null;

        return view('livewire.admin.licenciaturas.submodulo.matricula-editar', compact(
            'estados',
            'ciudades',
            'usuarios',
            'licenciaturaActual',
            'generacionActual',
            'cuatrimestreActual',
            'modalidadActual'
        ));
    }
}
