<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CalificacionMail extends Mailable
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


    public function __construct($calificaciones, $escuela, $inscripcion, $licenciatura, $generacion, $cuatrimestre, $ciclo_escolar)
    {

        $this->calificaciones = $calificaciones;
        $this->escuela = $escuela;
        $this->inscripcion = $inscripcion;
        $this->licenciatura = $licenciatura;
        $this->generacion = $generacion;
        $this->cuatrimestre = $cuatrimestre;
        $this->ciclo_escolar = $ciclo_escolar;

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
            view: 'admin.emails.calificaciones',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
