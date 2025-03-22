<?php

namespace DanielCHood\BaseballMatchupComparison\Prediction;

use DanielCHood\BaseballMatchupComparison\Matchup;

interface PredictionInterface {
    public function __construct(Matchup $matchup);

    public function isValid(): bool;

    public function getLabel(): string;

    public function win(): bool;
}