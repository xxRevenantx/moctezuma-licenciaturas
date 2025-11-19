<?php

use App\Http\Controllers\Admin\AccionController;
use App\Http\Controllers\Admin\AsignarGeneracionController;
use App\Http\Controllers\Admin\CuatrimestreController;
use App\Http\Controllers\Admin\DirectivoController;
use App\Http\Controllers\Admin\GeneracionController;
use App\Http\Controllers\Admin\GrupoController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\LicenciaturaController;
use App\Http\Controllers\Admin\PeriodoController;
use App\Http\Controllers\Admin\SeleccionarModalidadController;
use App\Http\Controllers\Admin\SubmoduloController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WordController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\ConstanciaController;
use App\Http\Controllers\DocumentacionController;
use App\Http\Controllers\DocumentosUnificadosController;
use App\Http\Controllers\EscuelaController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\EstudianteController;

use App\Http\Controllers\HorarioGeneralController;
use App\Http\Controllers\ListaProfesorController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MesController;
use App\Http\Controllers\PDFController;
use App\Http\Controllers\ProfesorController;
use App\Livewire\Admin\Licenciaturas\SeleccionarModalidad;
use App\Livewire\Admin\Usuarios\MostrarUsuarios;
use App\Models\Generacion;
use Illuminate\Support\Facades\Route;

// Route::get('/admin', function () {
//     return view('admin.dashboard');
// })->middleware(['auth'])->name('admin.dashboard');




Route::middleware(['auth'])->group(function () {


    Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

    Route::resource('escuela', EscuelaController::class)->middleware('can:admin.administracion')->names('admin.escuela');


    // DOCUMENTACIÓN
    Route::get('/listas-generales', [DocumentacionController::class, 'listasGenerales'])->middleware('can:admin.administracion')->name('admin.listas-generales');
    Route::get('/constancias', [DocumentacionController::class, 'constancias'])->middleware('can:admin.administracion')->name('admin.constancias');
    Route::get('/documentacion', [DocumentacionController::class, 'documentacion'])->middleware('can:admin.administracion')->name('admin.documentacion');


    ////////
    Route::resource('usuarios', UserController::class)->middleware('can:admin.usuarios')->names('admin.usuarios');

    // Estudiantes
    Route::get('estudiante', [EstudianteController::class, 'index'])->middleware('can:admin.administracion')->name('admin.estudiante');


    Route::resource('acciones', AccionController::class)->middleware('can:admin.administracion')->names('admin.acciones');

    Route::resource('estados', EstadoController::class)->middleware('can:admin.administracion')->names('admin.estados');

    Route::resource('ciudades', CiudadController::class)->middleware('can:admin.administracion')->names('admin.ciudades');

    Route::resource('asignacion-de-licenciaturas', LicenciaturaController::class)->middleware('can:admin.administracion')->names('admin.asignacion.licenciaturas');

    Route::resource('directivos', DirectivoController::class)->middleware('can:admin.administracion')->names('admin.directivos');

    Route::resource('cuatrimestres', CuatrimestreController::class)->middleware('can:admin.administracion')->names('admin.cuatrimestres');

    Route::get('grupos', [GrupoController::class, 'index'])->middleware('can:admin.administracion')->name('admin.grupos.index');

    Route::resource('periodos-escolares', PeriodoController::class)->middleware('can:admin.administracion')->names('admin.periodos');

    Route::resource('crear-generacion', GeneracionController::class)->middleware('can:admin.generaciones')->names('admin.generaciones');

    Route::resource('asignar-generacion', AsignarGeneracionController::class)->middleware('can:admin.asignar.generacion')->names('admin.asignar.generacion');




    Route::get('/profesores', [ProfesorController::class, 'index'])->middleware('can:admin.administracion')->name('admin.profesor.index');
    Route::get('/lista-profesores', [ProfesorController::class, 'lista_profesores'])->middleware('can:admin.administracion')->name('admin.profesor.lista_profesores');
    Route::get('/credencial-profesor', [ProfesorController::class, 'credencial_profesor'])->middleware('can:admin.administracion')->name('admin.profesor.credencial_profesor');



    Route::resource('horario-general', HorarioGeneralController::class)->middleware('can:admin.administracion')->names('admin.horario-general');
    Route::resource('materias', MateriaController::class)->middleware('can:admin.administracion')->names('admin.materia');


    // RUTAS WORD
    Route::get('acta-examen', [WordController::class, 'acta_examen'])->middleware('can:admin.administracion')->name('admin.word.acta-examen');


    // RUTAS PDF

    //DOCUMENTOS UNIFICADOS

    Route::get('/documentos/unificados_identidad/{id}', [DocumentosUnificadosController::class, 'DocumentosUnificadosAlumno'])->middleware('can:admin.administracion')->name('admin.alumnos.documentos.unificar');


    Route::get('/expediente/{id}', [PDFController::class, 'expediente'])->middleware('can:admin.administracion')->name('admin.pdf.expediente');
    Route::get('/matricula', [PDFController::class, 'matricula'])->middleware('can:admin.administracion')->name('admin.pdf.matricula');
    Route::get('/matricula-generacion', [PDFController::class, 'matricula_generacion'])->middleware('can:admin.administracion')->name('admin.pdf.matricula-generacion');
    Route::get('/horario-semiescolarizada', [PDFController::class, 'horario_semiescolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.horario-semiescolarizada');
    Route::get('/horario-escolarizada', [PDFController::class, 'horario_escolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.horario-escolarizada');

    // Horario docente
    Route::get('/horario-docente-semiescolarizada', [PDFController::class, 'horario_docente_semiescolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.horario-docente-semiescolarizada');
    Route::get('/horario-docente-escolarizada', [PDFController::class, 'horario_docente_escolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.horario-docente-escolarizada');

    Route::get('/horario-general-semiescolarizada', [PDFController::class, 'horario_general_semiescolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.horario-general-semiescolarizada');

    Route::get('/documentacion/{generacion}/{documento}', [PDFController::class, 'documento_expedicion'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.documento_expedicion');
    Route::get('/expedicion-documentacion', [PDFController::class, 'documento_expedicion'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.documento_expedicion');
    Route::get('/expedicion-sabanas', [PDFController::class, 'documento_sabanas'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.documento_sabanas');
    Route::get('/documento-personal', [PDFController::class, 'documento_personal'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.documento_personal');
    Route::get('/documento-oficios', [PDFController::class, 'documento_oficios'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.documento_oficios');
    Route::get('/credencial-alumno', [PDFController::class, 'credencial_alumno'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.credencial_alumno');

    Route::get('/credencial-profesor-pdf', [PDFController::class, 'credencial_profesor'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.credencial_profesor');
    Route::get('/credencial-profesor-estudiante-pdf', [PDFController::class, 'credencial_profesor_estudiante'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.credencial_profesor_estudiante');

    Route::get('/constancia', [PDFController::class, 'constancia'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.constancia');
    Route::get('/etiquetas', [PDFController::class, 'etiquetas'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.etiquetas');


    Route::get('/lista-asistencia-escolarizada', [PDFController::class, 'lista_asistencia_escolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.lista_asistencia_escolarizada');
    Route::get('/lista-asistencia-semiescolarizada', [PDFController::class, 'lista_asistencia_semiescolarizada'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.lista_asistencia_semiescolarizada');
    Route::get('/lista-evaluacion', [PDFController::class, 'lista_evaluacion'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.lista_evaluacion');

    // CALIFICACIONES DEL ALUMNO
    Route::get('/calificacion-alumno', [PDFController::class, 'calificacion_alumno'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.calificacion_alumno');
    Route::get('/calificaciones-generales', [PDFController::class, 'calificaciones_generales'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.calificaciones_generales');

    Route::get('/justificantes/{justificante}', [PDFController::class, 'justificante'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.justificantes');

    // PDF DE ALUMNOS QUE NO TIENE DOCUMENTACIÓN
    Route::get('/alumnos-documentacion/{licenciatura}', [PDFController::class, 'alumnos_documentacion'])->middleware('can:admin.administracion')->name('admin.pdf.documentacion.alumnos.documentacion');


    Route::prefix('licenciaturas')->group(function () {
        // Paso 1: Selección de modalidad
        Route::get('{slug_licenciatura}', [SeleccionarModalidadController::class, 'index'])->name('licenciaturas.seleccionar-modalidad');

        Route::get('{slug_licenciatura}/{slug_modalidad}/{submodulo}', [SubmoduloController::class, 'index'])->name('licenciaturas.submodulo');

    });




});


