<?php

namespace DanielCHood\BaseballMatchupComparison\Entity;

class Team {
    public function __construct(
        public readonly int $teamId,
        public readonly string $name,
        public readonly string $abbreviation,
    ) {

    }
}