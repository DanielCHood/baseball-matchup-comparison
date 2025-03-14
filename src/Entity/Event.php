<?php

namespace DanielCHood\BaseballMatchupComparison\Entity;

class Event {
    public function __construct(
        public readonly Team $homeTeam,
        public readonly Team $awayTeam,

    ) {

    }
}