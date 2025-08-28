<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class CalificacionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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

    public function envelope(): Envelope
    {
        return new Envelope(
            // Deja que tome el FROM del .env para evitar conflictos con proveedores:
            subject: 'Calificaciones del ' . $this->cuatrimestre->cuatrimestre . '° Cuatrimestre | ' .
                     $this->inscripcion->nombre . ' ' . $this->inscripcion->apellido_paterno . ' ' . $this->inscripcion->apellido_materno,
        );
    }

    public function content(): Content
    {
        // Las propiedades públicas ya están disponibles en la vista.
        return new Content(
            markdown: 'admin.emails.calificaciones',
            // Si prefieres explícito:
            // with: [
            //     'calificaciones' => $this->calificaciones,
            //     ...
            // ],
        );
    }

    public function attachments(): array
    {
        // Generar PDF (en memoria) desde una vista Blade:
        $pdf = Pdf::loadView('livewire.admin.licenciaturas.submodulo.pdf.boletaCalificacionPDF', [
            'calificaciones' => $this->calificaciones,
            'escuela'        => $this->escuela,
            'inscripcion'    => $this->inscripcion,
            'licenciatura'   => $this->licenciatura,
            'generacion'     => $this->generacion,
            'cuatrimestre'   => $this->cuatrimestre,
            'ciclo_escolar'  => $this->ciclo_escolar,
            'periodo'        => $this->periodo,
        ])->setPaper('letter', 'portrait');

        $nombrePdf = 'CALIFICACIONES_' .
            $this->cuatrimestre->cuatrimestre . '°_CUATRIMESTRE_' .
            $this->inscripcion->nombre . '_' .
            $this->inscripcion->apellido_paterno . '_' .
            $this->inscripcion->apellido_materno . '.pdf';

        return [
            Attachment::fromData(fn () => $pdf->output(), $nombrePdf)
                ->withMime('application/pdf'),
        ];
    }
}
