<?php

namespace DanielCHood\BaseballMatchupComparison\Entity;

use JsonSerializable;

readonly class Team implements JsonSerializable {
    public function __construct(
        public int $id,
        public string $name,
        public string $abbreviation,
    ) {

    }

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
        ];
    }
}