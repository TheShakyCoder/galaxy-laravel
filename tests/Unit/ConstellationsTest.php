<?php

use App\Support\Constellations;

test('an unused name is one of the constellations not already taken', function () {
    $taken = array_slice(Constellations::NAMES, 0, 87);

    expect(Constellations::unusedName($taken))->toBe(Constellations::NAMES[87]);
});

test('once every constellation is taken the names are numbered', function () {
    $taken = [...Constellations::NAMES, ...array_map(fn (string $name) => "{$name} 2", Constellations::NAMES)];

    expect(Constellations::unusedName($taken))->toEndWith(' 3')
        ->and(Constellations::unusedName(Constellations::NAMES))->toEndWith(' 2');
});
