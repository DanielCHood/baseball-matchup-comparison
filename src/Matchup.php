<?php


namespace DanielCHood\BaseballMatchupComparison;

class Matchup
{


    public function __construct(
        public readonly int $homeTeamId,
        public readonly int $awayTeamId,
        public readonly int $homeTeamMoneyline,
        public readonly int $awayTeamMoneyline,
        private PlayerStats $pitcherStats,
        private PlayerStats $batterStats,
        private readonly array $matchupPlays,
    )
    {

    }

    public function getPitcherStats(): PlayerStats {
        return $this->pitcherStats;
    }

    public function getBatterStats(): PlayerStats {
        return $this->batterStats;
    }

    public function getBatterMoneyline(): int {
        if ($this->getBatterStats()->getTeamId() === $this->homeTeamId) {
            return $this->homeTeamMoneyline;
        }

        if ($this->getBatterStats()->getTeamId() === $this->awayTeamId) {
            return $this->awayTeamMoneyline;
        }
    }

    public function didHit(bool $starterOnly = true): bool {
        $hitResultTypes = ['single', 'bunt-single', 'triple', 'double', 'bunt-double', 'ground-rule-double', 'home-run', 'batters-fielders-choice-all-runners-safe'];

        return !empty(array_filter($this->matchupPlays, function ($play) use ($starterOnly, $hitResultTypes) {
            return in_array($play['result'], $hitResultTypes)
                && (!$starterOnly || $this->pitcherStats->getId() === (int) $play['pitcherId']);
        }));
    }

    public function didHomer(bool $starterOnly = true): bool {
        $hitResultTypes = ['home-run'];

        return !empty(array_filter($this->matchupPlays, function ($play) use ($starterOnly, $hitResultTypes) {
            return in_array($play['result'], $hitResultTypes)
                && (!$starterOnly || $this->pitcherStats->getId() === (int) $play['pitcherId']);
        }));
    }
}