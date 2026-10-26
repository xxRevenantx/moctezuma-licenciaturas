<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Consulta externa opcional. Nunca registra ni muestra tokens, CURP o respuestas
 * con datos personales en los logs.
 */
class CurpService
{
    public function obtenerDatosPorCurp(string $curp): array
    {
        $curp = mb_strtoupper(trim($curp));

        if (! preg_match('/^[A-ZÑ&0-9]{18}$/u', $curp)) {
            return $this->error('La CURP debe contener 18 caracteres válidos.');
        }

        $token = trim((string) config('services.curp_api.key', ''));
        if ($token === '') {
            return $this->error('No hay un token CURP configurado. Revisa CURP_API_TOKEN en .env y limpia la caché de configuración.');
        }

        $url = (string) config('services.curp_api.url', 'https://api.valida-curp.com.mx/curp/obtener_datos/');
        if (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return $this->error('La URL de la API CURP no es válida (se requiere HTTPS).');
        }

        $timeout = max(3, min(60, (int) config('services.curp_api.timeout', 15)));
        $connectTimeout = max(1, min($timeout, (int) config('services.curp_api.connect_timeout', 5)));

        try {
            $respuesta = Http::acceptJson()
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->get($url, [
                    'token' => $token,
                    'curp' => $curp,
                ]);
        } catch (\Throwable $exception) {
            // No registrar $exception->getMessage(): podría contener la URL con el token.
            Log::warning('CURP externo: error de conexión', ['tipo' => class_basename($exception)]);

            return $this->error('No fue posible conectar con la API CURP. Revisa Internet, SSL y los tiempos de espera.');
        }

        $body = $respuesta->json();
        if (! is_array($body)) {
            Log::warning('CURP externo: respuesta JSON inválida', ['http_status' => $respuesta->status()]);

            return $this->error('La API CURP no devolvió una respuesta JSON válida.', $respuesta->status());
        }

        $codigo = (int) ($body['code_error'] ?? $body['error_code'] ?? 0);
        if (! $respuesta->successful() || ($body['error'] ?? false) === true) {
            // Solo datos técnicos: no incluir respuesta original ni CURP ni token.
            Log::warning('CURP externo: consulta rechazada', [
                'http_status' => $respuesta->status(),
                'api_code' => $codigo,
            ]);

            $mensaje = match ($codigo) {
                1 => 'La API CURP bloqueó el acceso. Revisa la cuenta del proveedor.',
                2 => 'El token de la API CURP es inválido. Comprueba el proyecto y su credencial.',
                3 => 'El proyecto de la API CURP está suspendido.',
                4 => 'La API CURP no tiene consultas disponibles. Revisa tus créditos.',
                200, 300 => 'La fuente RENAPO no está disponible o rechazó la consulta en este momento.',
                default => $respuesta->status() === 401 || $respuesta->status() === 403
                    ? 'La API CURP rechazó las credenciales.'
                    : 'La API CURP no pudo completar la consulta. Revisa el estado del servicio.',
            };

            return $this->error($mensaje, $respuesta->status(), $codigo);
        }

        if (! is_array(data_get($body, 'response.Solicitante'))) {
            Log::warning('CURP externo: respuesta sin solicitante', [
                'http_status' => $respuesta->status(),
            ]);

            return $this->error('La API CURP respondió sin los datos del solicitante.', $respuesta->status());
        }

        return $body;
    }

    private function error(string $mensaje, ?int $status = null, ?int $codigo = null): array
    {
        $resultado = ['error' => true, 'message' => $mensaje];
        if ($status !== null) {
            $resultado['status'] = $status;
        }
        if ($codigo !== null) {
            $resultado['code_error'] = $codigo;
        }

        return $resultado;
    }
}
