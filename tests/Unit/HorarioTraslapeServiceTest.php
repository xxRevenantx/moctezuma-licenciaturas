<?php

use App\Services\HorarioTraslapeService;

it('detecta rangos de horario que se traslapan', function () {
    $service = new HorarioTraslapeService();

    expect($service->rangosSeTraslapan('8:00am-9:00am', '8:30am-9:30am'))->toBeTrue()
        ->and($service->rangosSeTraslapan('10:00am-10:30am', '10:15am-11:00am'))->toBeTrue();
});

it('no marca como traslape horarios consecutivos', function () {
    $service = new HorarioTraslapeService();

    expect($service->rangosSeTraslapan('8:00am-9:00am', '9:00am-10:00am'))->toBeFalse()
        ->and($service->rangosSeTraslapan('10:00am-10:30am', '10:30am-11:30am'))->toBeFalse();
});

it('usa igualdad exacta como respaldo si el rango no puede interpretarse', function () {
    $service = new HorarioTraslapeService();

    expect($service->rangosSeTraslapan('BLOQUE-A', 'BLOQUE-A'))->toBeTrue()
        ->and($service->rangosSeTraslapan('BLOQUE-A', 'BLOQUE-B'))->toBeFalse();
});
