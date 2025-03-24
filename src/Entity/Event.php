<?php

namespace DanielCHood\BaseballMatchupComparison\Entity;

use DateTime;

class Event {
    public function __construct(
        public readonly int $id,
        public readonly DateTime $date,
        public readonly Team $homeTeam,
        public readonly Team $awayTeam,
    ) {

    }
}