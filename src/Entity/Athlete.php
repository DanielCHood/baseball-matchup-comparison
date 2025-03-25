<?php

namespace DanielCHood\BaseballMatchupComparison\Entity;

use JsonSerializable;

readonly class Athlete implements JsonSerializable {
    public function __construct(
        public Team $team,
        public int $id,
        public string $name,
        public string $position,
    ) {

    }

    public function jsonSerialize(): array {
        return [
            'team' => $this->team,
            'id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
        ];
    }
}