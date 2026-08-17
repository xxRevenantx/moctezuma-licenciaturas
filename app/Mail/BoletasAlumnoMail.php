<?php

namespace App\Mail;

use App\Models\Generacion;
use App\Models\Inscripcion;
use App\Services\Boletas\BoletaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoletasAlumnoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public int $alumnoId,
        public int $licenciaturaId,
        public int $modalidadId,
        public int $generacionId,
        public array $cuatrimestreIds,
        public bool $incluirIncompletas = false,
    ) {
    }

    public function envelope(): Envelope
    {
        $alumno = Inscripcion::query()->find($this->alumnoId);
        $nombre = $alumno
            ? trim("{$alumno->nombre} {$alumno->apellido_paterno} {$alumno->apellido_materno}")
            : 'Alumno';

        return new Envelope(
            subject: "Boletas de calificaciones | {$nombre}",
        );
    }

    public function content(): Content
    {
        $alumno = Inscripcion::query()->find($this->alumnoId);
        $generacion = Generacion::query()->find($this->generacionId);

        return new Content(
            markdown: 'admin.emails.boletas-alumno',
            with: [
                'alumno' => $alumno,
                'generacion' => $generacion,
                'totalBoletas' => count($this->cuatrimestreIds),
            ],
        );
    }

    public function attachments(): array
    {
        $boletas = app(BoletaService::class);
        $adjuntos = [];

        foreach ($this->cuatrimestreIds as $cuatrimestreId) {
            try {
                $dataset = $boletas->datasetBoleta(
                    $this->alumnoId,
                    $this->licenciaturaId,
                    $this->modalidadId,
                    $this->generacionId,
                    (int) $cuatrimestreId,
                    true
                );
            } catch (\Throwable $e) {
                continue;
            }

            if ($dataset['calificaciones']->isEmpty()) {
                continue;
            }

            if (($dataset['estado']['codigo'] ?? null) === 'incompleta' && !$this->incluirIncompletas) {
                continue;
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'livewire.admin.licenciaturas.submodulo.pdf.boletaCalificacionPDF',
                $dataset
            )->setPaper('letter', 'portrait');

            $nombre = $boletas->nombreArchivo($dataset);
            $adjuntos[] = Attachment::fromData(fn () => $pdf->output(), $nombre)
                ->withMime('application/pdf');
        }

        return $adjuntos;
    }
}
