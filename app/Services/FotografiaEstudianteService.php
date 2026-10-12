<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FotografiaEstudianteService
{
    public const ANCHO_PX = 295;
    public const ALTO_PX = 354;
    public const PPP = 300;

    /**
     * Normaliza una fotografía de estudiante al formato institucional 2.5 x 3 cm.
     *
     * - 295 x 354 px (relación 5:6) a 300 ppp.
     * - Recorte centrado sin deformar.
     * - Corrección EXIF para JPEG.
     * - Salida JPEG consistente para credenciales y documentos.
     */
    public function guardar(UploadedFile $archivo, string $directorio = 'estudiantes'): string
    {
        $mime = strtolower((string) $archivo->getMimeType());

        if (! in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'], true)) {
            throw new RuntimeException('La fotografía debe ser JPG, JPEG, PNG o WebP.');
        }

        if (! function_exists('imagejpeg') || ! $this->puedeProcesar($mime)) {
            throw new RuntimeException('El servidor necesita la extensión GD con soporte JPEG para normalizar fotografías.');
        }

        $origen = $this->crearRecurso($archivo->getRealPath(), $mime);

        if (! $origen) {
            throw new RuntimeException('No fue posible leer la fotografía seleccionada.');
        }

        if (in_array($mime, ['image/jpeg', 'image/jpg'], true)) {
            $origen = $this->corregirOrientacionExif($origen, $archivo->getRealPath());
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);

        if ($ancho < 1 || $alto < 1) {
            imagedestroy($origen);
            throw new RuntimeException('La fotografía no tiene dimensiones válidas.');
        }

        $relacionDestino = self::ANCHO_PX / self::ALTO_PX;
        $relacionOrigen = $ancho / $alto;

        if ($relacionOrigen > $relacionDestino) {
            $recorteAlto = $alto;
            $recorteAncho = (int) round($alto * $relacionDestino);
            $origenX = (int) floor(($ancho - $recorteAncho) / 2);
            $origenY = 0;
        } else {
            $recorteAncho = $ancho;
            $recorteAlto = (int) round($ancho / $relacionDestino);
            $origenX = 0;
            $origenY = (int) floor(($alto - $recorteAlto) / 2);
        }

        $destino = imagecreatetruecolor(self::ANCHO_PX, self::ALTO_PX);
        $blanco = imagecolorallocate($destino, 255, 255, 255);
        imagefilledrectangle($destino, 0, 0, self::ANCHO_PX, self::ALTO_PX, $blanco);

        imagecopyresampled(
            $destino,
            $origen,
            0,
            0,
            $origenX,
            $origenY,
            self::ANCHO_PX,
            self::ALTO_PX,
            $recorteAncho,
            $recorteAlto
        );

        if (function_exists('imageresolution')) {
            @imageresolution($destino, self::PPP, self::PPP);
        }

        ob_start();
        $guardado = imagejpeg($destino, null, 90);
        $contenido = ob_get_clean();

        imagedestroy($origen);
        imagedestroy($destino);

        if (! $guardado || ! is_string($contenido) || $contenido === '') {
            throw new RuntimeException('No fue posible generar la fotografía institucional.');
        }

        $nombre = Str::uuid() . '.jpg';
        $ruta = trim($directorio, '/') . '/' . $nombre;
        $disco = (string) config('filesystems.fotos_disk', 'public');

        if (! Storage::disk($disco)->put($ruta, $contenido)) {
            throw new RuntimeException('No fue posible guardar la fotografía institucional.');
        }

        // Se conserva en BD únicamente el nombre para mantener compatibilidad
        // con el campo histórico `inscripciones.foto`.
        return $nombre;
    }

    public function eliminar(?string $foto): void
    {
        if (blank($foto)) {
            return;
        }

        $ruta = $this->ruta((string) $foto);
        $discos = array_values(array_unique([
            (string) config('filesystems.fotos_disk', 'public'),
            'public',
        ]));

        foreach ($discos as $disco) {
            try {
                if (Storage::disk($disco)->exists($ruta)) {
                    Storage::disk($disco)->delete($ruta);
                }
            } catch (\Throwable) {
                // La eliminación de una copia antigua no debe impedir el guardado nuevo.
            }
        }
    }

    public function ruta(string $foto): string
    {
        $foto = ltrim(trim($foto), '/');

        return str_contains($foto, '/') ? $foto : 'estudiantes/' . $foto;
    }

    private function corregirOrientacionExif(mixed $imagen, string $ruta): mixed
    {
        if (! function_exists('exif_read_data')) {
            return $imagen;
        }

        try {
            $exif = @exif_read_data($ruta);
            $orientacion = (int) ($exif['Orientation'] ?? 1);

            $rotada = match ($orientacion) {
                3 => imagerotate($imagen, 180, 0),
                6 => imagerotate($imagen, -90, 0),
                8 => imagerotate($imagen, 90, 0),
                default => false,
            };

            if ($rotada !== false) {
                imagedestroy($imagen);
                return $rotada;
            }
        } catch (\Throwable) {
            // Si EXIF no puede leerse, se conserva la imagen original.
        }

        return $imagen;
    }

    private function puedeProcesar(string $mime): bool
    {
        if (! function_exists('imagecreatetruecolor')) {
            return false;
        }

        return match ($mime) {
            'image/jpeg', 'image/jpg' => function_exists('imagecreatefromjpeg') && function_exists('imagejpeg'),
            'image/png' => function_exists('imagecreatefrompng') && function_exists('imagejpeg'),
            'image/webp' => function_exists('imagecreatefromwebp') && function_exists('imagejpeg'),
            default => false,
        };
    }

    private function crearRecurso(string $ruta, string $mime): mixed
    {
        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($ruta),
            'image/png' => @imagecreatefrompng($ruta),
            'image/webp' => @imagecreatefromwebp($ruta),
            default => false,
        };
    }
}
