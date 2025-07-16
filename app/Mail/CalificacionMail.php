<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CalificacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $calificaciones;
    public $escuela;
    public $inscripcion;
    public $licenciatura;
    public $generacion;
    public $cuatrimestre;
    public $ciclo_escolar;
    public $periodo;


    public function __construct($calificaciones, $escuela, $inscripcion, $licenciatura, $generacion, $cuatrimestre, $ciclo_escolar, $periodo)
    {

        $this->calificaciones = $calificaciones;
        $this->escuela = $escuela;
        $this->inscripcion = $inscripcion;
        $this->licenciatura = $licenciatura;
        $this->generacion = $generacion;
        $this->cuatrimestre = $cuatrimestre;
        $this->ciclo_escolar = $ciclo_escolar;
        $this->periodo = $periodo;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('centrouniversitariomoctezuma@gmail.com', 'Centro Universitario Moctezuma'),
            subject: 'Calificaciones del ' . $this->cuatrimestre->cuatrimestre . '° Cuatrimestre | ' . $this->inscripcion->nombre . ' ' . $this->inscripcion->apellido_paterno . ' ' . $this->inscripcion->apellido_materno,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {



        return new Content(
            markdown: 'admin.emails.calificaciones',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
{
    // // Generar PDF con DomPDF usando una vista Blade
    // $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.boletaCalificacionPDF', [
    //     'calificaciones' => $this->calificaciones,
    //     'escuela' => $this->escuela,
    //     'inscripcion' => $this->inscripcion,
    //     'licenciatura' => $this->licenciatura,
    //     'generacion' => $this->generacion,
    //     'cuatrimestre' => $this->cuatrimestre,
    //     'ciclo_escolar' => $this->ciclo_escolar,
    //     'periodo' => $this->periodo,
    // ])->setPaper('letter', 'portrait');


    // // Guardar el PDF temporalmente
    // $pdfPath = storage_path('app/temp_calificacion_' . $this->inscripcion->id . '.pdf');
    // $pdf->save($pdfPath);

    // // Devolver el PDF como Attachment
    // return [
    //     Attachment::fromPath($pdfPath)
    //         ->as('CALIFICACIONES_' . $this->cuatrimestre->cuatrimestre . '°_CUATRIMESTRE_' . $this->inscripcion->nombre . '_' . $this->inscripcion->apellido_paterno . '_' . $this->inscripcion->apellido_materno . '.pdf')
    //         ->withMime('application/pdf'),
    // ];
}
}
