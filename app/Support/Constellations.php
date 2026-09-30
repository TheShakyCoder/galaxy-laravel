<?php

namespace App\Support;

/**
 * The 88 constellations recognised by the International Astronomical Union,
 * used to name game servers (Server's default name).
 */
class Constellations
{
    /**
     * @var list<string>
     */
    public const NAMES = [
        'Andromeda', 'Antlia', 'Apus', 'Aquarius', 'Aquila', 'Ara', 'Aries', 'Auriga',
        'Boötes', 'Caelum', 'Camelopardalis', 'Cancer', 'Canes Venatici', 'Canis Major', 'Canis Minor', 'Capricornus',
        'Carina', 'Cassiopeia', 'Centaurus', 'Cepheus', 'Cetus', 'Chamaeleon', 'Circinus', 'Columba',
        'Coma Berenices', 'Corona Australis', 'Corona Borealis', 'Corvus', 'Crater', 'Crux', 'Cygnus', 'Delphinus',
        'Dorado', 'Draco', 'Equuleus', 'Eridanus', 'Fornax', 'Gemini', 'Grus', 'Hercules',
        'Horologium', 'Hydra', 'Hydrus', 'Indus', 'Lacerta', 'Leo', 'Leo Minor', 'Lepus',
        'Libra', 'Lupus', 'Lynx', 'Lyra', 'Mensa', 'Microscopium', 'Monoceros', 'Musca',
        'Norma', 'Octans', 'Ophiuchus', 'Orion', 'Pavo', 'Pegasus', 'Perseus', 'Phoenix',
        'Pictor', 'Pisces', 'Piscis Austrinus', 'Puppis', 'Pyxis', 'Reticulum', 'Sagitta', 'Sagittarius',
        'Scorpius', 'Sculptor', 'Scutum', 'Serpens', 'Sextans', 'Taurus', 'Telescopium', 'Triangulum',
        'Triangulum Australe', 'Tucana', 'Ursa Major', 'Ursa Minor', 'Vela', 'Virgo', 'Volans', 'Vulpecula',
    ];

    /**
     * A random constellation name not in $taken. Once every constellation is
     * taken, a numbered one ("Orion 2", "Orion 3", ...) that isn't.
     *
     * @param  list<string>  $taken
     */
    public static function unusedName(array $taken): string
    {
        $free = array_values(array_diff(self::NAMES, $taken));

        if ($free !== []) {
            return $free[array_rand($free)];
        }

        for ($number = 2; ; $number++) {
            $free = array_values(array_diff(
                array_map(fn (string $name) => "{$name} {$number}", self::NAMES),
                $taken,
            ));

            if ($free !== []) {
                return $free[array_rand($free)];
            }
        }
    }
}
