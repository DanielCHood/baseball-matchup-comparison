<?php

namespace DanielCHood\BaseballMatchupComparison\Prediction;

class HomeRunAnyPitcher extends HomeRunStartingPitcher implements PredictionInterface {
    public function getLabel(): string {
        return 'HomeRunAnyPitcher';
    }

    public function win(): bool {
        return $this->matchup->didHomer(false);
    }
}