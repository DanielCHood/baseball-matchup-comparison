<?php


namespace DanielCHood\BaseballMatchupComparison;

class Matchup
{


    public function __construct(
        private PlayerStats $pitcherStats,
        private PlayerStats $batterStats,
    )
    {

    }

    public function getPitcherStats(): PlayerStats {
        return $this->pitcherStats;
    }

    public function getBatterStats(): PlayerStats {
        return $this->batterStats;
    }
}