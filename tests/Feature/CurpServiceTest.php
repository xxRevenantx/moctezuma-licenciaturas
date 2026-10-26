<?php

use App\Services\CurpService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.curp_api.url', 'https://api.valida-curp.com.mx/curp/obtener_datos/');
    config()->set('services.curp_api.key', 'token-de-prueba');
    config()->set('services.curp_api.timeout', 15);
    config()->set('services.curp_api.connect_timeout', 5);
});

test('consulta la API con el token configurado y obtiene el solicitante', function () {
    Http::fake(['api.valida-curp.com.mx/*' => Http::response([
        'error' => false,
        'response' => ['Solicitante' => [
            'Nombres' => 'ANA', 'ApellidoPaterno' => 'GARCIA', 'ApellidoMaterno' => 'LOPEZ',
        ]],
    ], 200)]);

    $respuesta = app(CurpService::class)->obtenerDatosPorCurp('XAXX010101XAXAXA00');

    expect($respuesta['error'])->toBeFalse();
    expect(data_get($respuesta, 'response.Solicitante.Nombres'))->toBe('ANA');
    Http::assertSent(fn ($request) => $request->method() === 'GET'
        && str_contains($request->url(), 'token=token-de-prueba')
        && str_contains($request->url(), 'curp=XAXX010101XAXAXA00'));
});

test('explica la falta de token sin emitir solicitudes', function () {
    config()->set('services.curp_api.key', null);
    Http::fake();

    $respuesta = app(CurpService::class)->obtenerDatosPorCurp('XAXX010101XAXAXA00');

    expect($respuesta['error'])->toBeTrue();
    expect($respuesta['message'])->toContain('token CURP configurado');
    Http::assertNothingSent();
});

test('interpreta una respuesta de créditos agotados', function () {
    Http::fake(['api.valida-curp.com.mx/*' => Http::response([
        'error' => true,
        'code_error' => 4,
        'error_message' => 'sin consultas',
    ], 400)]);

    $respuesta = app(CurpService::class)->obtenerDatosPorCurp('XAXX010101XAXAXA00');

    expect($respuesta['error'])->toBeTrue();
    expect($respuesta['message'])->toContain('créditos');
    expect($respuesta['code_error'])->toBe(4);
});

test('rechaza respuestas exitosas sin el solicitante', function () {
    Http::fake(['api.valida-curp.com.mx/*' => Http::response(['error' => false, 'response' => []], 200)]);

    $respuesta = app(CurpService::class)->obtenerDatosPorCurp('XAXX010101XAXAXA00');

    expect($respuesta['error'])->toBeTrue();
    expect($respuesta['message'])->toContain('sin los datos del solicitante');
});

test('rechaza estructura inválida sin usar créditos', function () {
    Http::fake();
    $respuesta = app(CurpService::class)->obtenerDatosPorCurp('CURP-CORTA');
    expect($respuesta['error'])->toBeTrue();
    Http::assertNothingSent();
});
