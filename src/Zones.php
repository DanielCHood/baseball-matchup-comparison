<?php

namespace DanielCHood\BaseballMatchupComparison;

use InvalidArgumentException;

class Zones {
    private $zones = [
        [
            'xMin' => 77,
            'xMax' => 104,
            'yMin' => 131,
            'yMax' => 155
        ],
        [
            'xMin' => 104,
            'xMax' => 131,
            'yMin' => 131,
            'yMax' => 155
        ],
        [
            'xMin' => 131,
            'xMax' => 157,
            'yMin' => 131,
            'yMax' => 155
        ],
        [
            'xMin' => 77,
            'xMax' => 104,
            'yMin' => 155,
            'yMax' => 179
        ],
        [
            'xMin' => 104,
            'xMax' => 131,
            'yMin' => 155,
            'yMax' => 179
        ],
        [
            'xMin' => 131,
            'xMax' => 157,
            'yMin' => 155,
            'yMax' => 179
        ],
        [
            'xMin' => 77,
            'xMax' => 104,
            'yMin' => 179,
            'yMax' => 204
        ],
        [
            'xMin' => 104,
            'xMax' => 131,
            'yMin' => 179,
            'yMax' => 204
        ],
        [
            'xMin' => 131,
            'xMax' => 157,
            'yMin' => 179,
            'yMax' => 204
        ],
    ];

    public function getZone(int $x, int $y): ?int {
        foreach ($this->zones as $zone => $range) {
            if ($x > $range['xMax']) {
                continue;
            }

            if ($x < $range['xMin']) {
                continue;
            }

            if ($y > $range['yMax']) {
                continue;
            }

            if ($y < $range['yMin']) {
                continue;
            }

            return $zone;
        }

        //"A zone with these coordinates could not be found; x=$x and y=$y."
        return null;
    }
}