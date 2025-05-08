<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurpService
{
    protected $apiUrl = 'https://api.valida-curp.com.mx/curp/obtener_datos/';

    protected $token;

    public function __construct()
    {
        $this->token = config('services.curp_api.key');
    }

    public function consultar(string $curp)
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ])->post($this->apiUrl, [
            'curp' => $curp,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
