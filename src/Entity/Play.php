<?php

namespace DanielCHood\BaseballMatchupComparison\Entity;

class Play {
    public function __construct(
        public readonly int $eventId,
        public readonly int $velocity,
        public readonly string $pitchType,
        public readonly int $xCoordinate,
        public readonly int $yCoordinate,
        public readonly string $result,
    ) {

    }
}