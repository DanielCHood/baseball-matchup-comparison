<?php

namespace DanielCHood\BaseballMatchupComparison\Prediction;

use DanielCHood\BaseballMatchupComparison\Matchup;

class HomeRunStartingPitcher implements PredictionInterface {
    public function __construct(
        protected readonly Matchup $matchup
    ) {

    }

    public function isValid(): bool {
        return $this->getHomeRunScore() > .15
            && $this->getBatterPitchCount() >= 400
            && $this->getPitcherPitchCount() >= 400
            && $this->getHitScore() > 0.0000
            && $this->matchup->getBatterMoneyline() < 0
            && $this->getVelocityScore() > 2
            && $this->getBatterHomeRunPercentage() > 1.5;
    }

    public function getLabel(): string {
        return 'HomeRunStartingPitcher'
            #. '; home=' . ($this->matchup->homeTeamId === $this->matchup->getBatterStats()->getTeamId() ? 'true' : 'false')
            #. '; favorite=' . ($this->matchup->getBatterMoneyline() > 0 ? 'true' : 'false')
            #. 'ml=' . floor($this->matchup->getBatterMoneyline() / 10)
            #. 'hitScore=' . floor($this->getHitScore()) * 10
            #. '; hrScore=' . round($this->getHomeRunScore() * 10)
            #. '; pitcherPitchCount=' . (floor($this->getPitcherPitchCount() / 100) * 100)
            #. '; batterPitchCount=' . (floor($this->getBatterPitchCount() / 100) * 100)
            #. '; velocity=' . (floor($this->getVelocityScore()))
            #. '; batterHrPercent=' . (floor($this->getBatterHomeRunPercentage()))
            #. '; pitcherHrPercent=' . (floor($this->getPitcherHomeRunPercentage()))
            . '; battingAverage=' . (floor($this->getBattingAverage() * 10))
            ;
    }

    public function win(): bool {
        return $this->matchup->didHomer(true);
    }

    public function getHitScore(): float {
        $score = 0;

        foreach ($this->matchup->getPitcherStats()->getTagged() as $sectionName => $section) {
            $batterSection = $this->matchup->getBatterStats()->getTagged()[$sectionName] ?? null;
            // batter hasn't seen a pitch with this tag yet
            if (!$batterSection) {
                continue;
            }

            $score += ($section['hitPercentWeighted'] * $batterSection['hitPercentWeighted']);
        }

        return $score;
    }

    public function getBatterHomeRunPercentage(): float {
        $tagged = $this->matchup->getBatterStats()->getTagged();
        $homeRunCount = array_sum(array_column($tagged, 'homeRuns'));
        $pitchCount = array_sum(array_column($tagged, 'pitchCount'));

        return $homeRunCount / $pitchCount * 100;
    }

    public function getBattingAverage(): float {
        return $this->matchup->getBatterStats()->battingAverage;
    }

    public function getPitcherHomeRunPercentage(): float {
        $tagged = $this->matchup->getPitcherStats()->getTagged();
        $homeRunCount = array_sum(array_column($tagged, 'homeRuns'));
        $pitchCount = array_sum(array_column($tagged, 'pitchCount'));

        return $homeRunCount / $pitchCount * 100;
    }

    public function getHomeRunScore(): float {
        $score = 0;

        foreach ($this->matchup->getPitcherStats()->getTagged() as $sectionName => $section) {
            $batterSection = $this->matchup->getBatterStats()->getTagged()[$sectionName] ?? null;
            // batter hasn't seen a pitch with this tag yet
            if (!$batterSection) {
                continue;
            }

            $score += ($section['homeRunPercentWeighted'] * $batterSection['homeRunPercentWeighted']);
        }

        return $score;
    }


    public function getVelocityScore(): float {
        $score = 0;

        foreach ($this->matchup->getPitcherStats()->getTagged() as $sectionName => $section) {
            $batterSection = $this->matchup->getBatterStats()->getTagged()[$sectionName] ?? null;
            // batter hasn't seen a pitch with this tag yet
            if (!$batterSection) {
                continue;
            }

            $velocity = $section['velocity'];
            $frequency = $section['pitchCount'] / $this->getPitcherPitchCount();

            $batterAverageSeenVelocity = $batterSection['velocity'] ?? 0;
            if ($batterAverageSeenVelocity === 0) {
                continue;
            }

            $score += ($batterAverageSeenVelocity - $velocity) * $frequency;
        }

        return $score;
    }

    private function getBatterPitchCount(): float {
        return array_sum(array_column($this->matchup->getBatterStats()->getTagged(), 'pitchCount'));
    }

    private function getPitcherPitchCount(): float {
        return array_sum(array_column($this->matchup->getPitcherStats()->getTagged(), 'pitchCount'));
    }
}